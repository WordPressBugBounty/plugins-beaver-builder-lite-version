<?php

final class FL_Debug {

	static private $tests = array();

	public static function init() {
		if ( isset( $_GET['fldebug'] ) && get_transient( 'fl_debug_mode', false ) === $_GET['fldebug'] ) {
			if ( isset( $_GET['info'] ) ) {
				phpinfo();
				exit;
			}
			add_action( 'init', array( 'FL_Debug', 'display_tests' ) );
		}

		if ( get_option( 'fl_debug_mode', false ) ) {
			if ( get_transient( 'fl_debug_mode' ) ) {
				self::enable_logging();
				add_filter( 'fl_is_debug', '__return_true' );
			}
		}
	}


	public static function enable_logging() {
		if ( isset( $_GET['showerrors'] ) ) {
			@ini_set( 'display_errors', 1 ); // @codingStandardsIgnoreLine
			@ini_set( 'display_startup_errors', 1 ); // @codingStandardsIgnoreLine
			@error_reporting( E_ALL ); // @codingStandardsIgnoreLine
		}
	}

	public static function display_tests() {

		self::prepare_tests();

		header( 'Content-Type:text/html; charset=utf-8' );

		$sections   = self::get_sections();
		$plain_text = self::get_plain_text();
		$token      = isset( $_GET['fldebug'] ) ? sanitize_text_field( $_GET['fldebug'] ) : '';

		self::render_html( $sections, $plain_text, $token );
		die();
	}

	/**
	 * Format a single test as plain text (used for Copy All).
	 */
	private static function display( $test ) {

		if ( is_array( $test['data'] ) ) {
			$lines = array();
			foreach ( $test['data'] as $item ) {
				$lines[] = is_array( $item ) && isset( $item['label'] ) ? $item['label'] : (string) $item;
			}
			$test['data'] = implode( "\n", $lines );
		}
		return isset( $test['name'] ) ? sprintf( "%s\n%s\n\n", $test['name'], $test['data'] ) : sprintf( "%s\n\n", $test['data'] );
	}

	/**
	 * Group registered tests into sections split by dividers.
	 */
	private static function get_sections() {
		$sections = array();
		$current  = array(
			'title' => 'General',
			'rows'  => array(),
		);

		foreach ( (array) self::$tests as $test ) {
			// A divider marks the start of a new section.
			if ( isset( $test['data'] ) && self::divider() === $test['data'] ) {
				// Save previous section if it has rows.
				if ( ! empty( $current['rows'] ) ) {
					$sections[] = $current;
				}
				$current = array(
					'title' => isset( $test['name'] ) ? $test['name'] : 'Info',
					'rows'  => array(),
				);
				continue;
			}

			$current['rows'][] = $test;
		}

		// Don't forget the last section.
		if ( ! empty( $current['rows'] ) ) {
			$sections[] = $current;
		}

		return $sections;
	}

	/**
	 * Build the full plain text output (original format) for clipboard copy.
	 */
	private static function get_plain_text() {
		$output = '';
		foreach ( (array) self::$tests as $test ) {
			$output .= self::display( $test );
		}
		return $output;
	}

	/**
	 * Return a CSS class suffix for Yes/No style values.
	 */
	private static function get_value_class( $value ) {
		$v = strtolower( trim( (string) $value ) );
		if ( in_array( $v, array( 'yes', 'active', 'enabled', 'true' ), true ) ) {
			return ' fl-debug-positive';
		}
		if ( in_array( $v, array( 'no', 'not active', 'disabled', 'false', 'not active/installed.' ), true ) ) {
			return ' fl-debug-negative';
		}
		if ( false !== strpos( $v, 'error' ) || false !== strpos( $v, '*** no' ) ) {
			return ' fl-debug-negative';
		}
		return '';
	}

	/**
	 * Check if a string value is long enough to need a <pre> block.
	 */
	private static function is_preformatted( $value ) {
		if ( ! is_string( $value ) ) {
			return false;
		}
		$lines = substr_count( $value, "\n" );
		if ( $lines > 4 ) {
			return true;
		}
		$patterns = array( '<?php', 'RewriteRule', 'RewriteEngine', 'function ', 'require', 'include' );
		foreach ( $patterns as $p ) {
			if ( false !== strpos( $value, $p ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Auto-linkify URLs within an escaped HTML string.
	 */
	private static function linkify( $text ) {
		return preg_replace_callback(
			'#(https?://[^\s<]+)#i',
			function ( $matches ) {
				return '<a href="' . esc_url( $matches[1] ) . '" target="_blank" rel="noopener">' . $matches[1] . '</a>';
			},
			$text
		);
	}

	/**
	 * Render a single data value as HTML.
	 */
	private static function render_value( $data ) {
		if ( is_array( $data ) ) {
			if ( empty( $data ) ) {
				return '<span class="fl-debug-negative">None</span>';
			}
			// Check if items follow "Label: Value" pattern (e.g. Advanced Options).
			$is_kv = true;
			foreach ( $data as $item ) {
				if ( is_array( $item ) ) {
					$is_kv = false;
					break;
				}
				if ( false === strpos( (string) $item, ': ' ) ) {
					$is_kv = false;
					break;
				}
			}
			if ( $is_kv ) {
				$html = '<div class="fl-debug-kv-list">';
				foreach ( $data as $item ) {
					$parts = explode( ': ', $item, 2 );
					$label = esc_html( $parts[0] );
					$val   = esc_html( $parts[1] );
					$class = self::get_value_class( $parts[1] );
					$html .= '<div class="fl-debug-kv-row">';
					$html .= '<span class="fl-debug-kv-label">' . $label . '</span>';
					$html .= '<span class="fl-debug-kv-val' . $class . '">' . $val . '</span>';
					$html .= '</div>';
				}
				$html .= '</div>';
				return $html;
			}
			$html = '<div class="fl-debug-list fl-debug-list-bordered">';
			foreach ( $data as $item ) {
				// Structured item with label + url (plugins/themes).
				if ( is_array( $item ) && isset( $item['label'] ) ) {
					$label = esc_html( $item['label'] );
					if ( ! empty( $item['url'] ) ) {
						// Split on " - " to separate name from version info.
						$name_end = strpos( $item['label'], ' - ' );
						if ( false !== $name_end ) {
							$name  = substr( $item['label'], 0, $name_end );
							$rest  = substr( $item['label'], $name_end );
							$label = '<a href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $name ) . '</a>' . esc_html( $rest );
						}
					}
					$html .= '<div class="fl-debug-list-item">' . $label . '</div>';
				} else {
					$html .= '<div class="fl-debug-list-item">' . self::linkify( esc_html( (string) $item ) ) . '</div>';
				}
			}
			$html .= '</div>';
			return $html;
		}

		$str = (string) $data;

		if ( self::is_preformatted( $str ) ) {
			return '<pre class="fl-debug-pre">' . esc_html( $str ) . '</pre>';
		}

		$class = self::get_value_class( $str );
		return '<span class="fl-debug-value' . $class . '">' . self::linkify( esc_html( $str ) ) . '</span>';
	}

	/**
	 * Render the full styled HTML page.
	 */
	private static function render_html( $sections, $plain_text, $token ) {
		$bb_version  = defined( 'FL_BUILDER_VERSION' ) ? FL_BUILDER_VERSION : '';
		$branding    = FLBuilderModel::get_branding();
		$info_url    = $token ? esc_url( add_query_arg( array(
			'fldebug' => $token,
			'info'    => '1',
		), site_url() ) ) : '';
		$expiry_text = '';
		$expire_opt  = get_option( '_transient_timeout_fl_debug_mode' );
		if ( $expire_opt ) {
			$now         = new DateTime( 'now' );
			$expires     = new DateTime( gmdate( 'Y-m-d H:i:s', $expire_opt ) );
			$interval    = $now->diff( $expires );
			$expiry_text = $interval->format( '%d days %h hours %i minutes' );
		}
		?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( $branding ); ?> &mdash; System Information</title>
<style>
html{scroll-behavior:smooth}
*,*::before,*::after{box-sizing:border-box}
body{
	margin:0;padding:40px 20px;
	background:#f0f0f1;
	font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
	font-size:13px;color:#1d2327;line-height:1.5;
}
.fl-debug-wrap{max-width:900px;margin:0 auto}
.fl-debug-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.fl-debug-brand{font-size:22px;font-weight:700;color:#0E5A71;letter-spacing:-0.3px}
.fl-debug-brand span{color:#EE521F}
.fl-debug-subtitle{font-size:14px;color:#646970;margin-top:2px}
.fl-debug-expiry{font-size:12px;color:#9ca0a4;margin-top:4px}
.fl-debug-copy{
	background:#0E5A71;color:#fff;border:none;border-radius:6px;
	padding:8px 16px;font-size:13px;font-weight:500;cursor:pointer;
	transition:background 0.15s ease;
}
.fl-debug-copy:hover{background:#094a5e}
.fl-debug-copy.copied{background:#00a32a}
.fl-debug-header-actions{display:flex;gap:8px;align-items:center}
.fl-debug-raw-btn{
	background:#fff;color:#1d2327;border:1px solid #e0e0e0;border-radius:6px;
	padding:8px 16px;font-size:13px;font-weight:500;cursor:pointer;
	transition:all 0.15s ease;
}
.fl-debug-raw-btn:hover{border-color:#0E5A71;color:#0E5A71}
.fl-debug-raw-btn.active{background:#0E5A71;color:#fff;border-color:#0E5A71}
.fl-debug-download-btn{
	background:#EE521F;color:#fff;border:none;border-radius:6px;
	padding:8px 16px;font-size:13px;font-weight:500;cursor:pointer;
	transition:background 0.15s ease;
}
.fl-debug-download-btn:hover{background:#d4471a}
.fl-debug-raw{display:none}
.fl-debug-raw.visible{display:block}
.fl-debug-wrap.raw-mode .fl-debug-styled{display:none}
.fl-debug-raw pre{
	background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:24px;
	font-size:12px;line-height:1.6;font-family:Menlo,Consolas,"Courier New",monospace;
	overflow:auto;max-height:600px;white-space:pre;tab-size:4;margin:0 0 16px;
}
.fl-debug-card{
	background:#fff;border:1px solid #e0e0e0;border-radius:10px;
	padding:0;margin-bottom:16px;overflow:hidden;
}
.fl-debug-card-title{
	font-size:14px;font-weight:600;color:#1d2327;
	padding:16px 24px;margin:0;border-bottom:1px solid #f0f0f1;
	background:#fafafa;
}
.fl-debug-row{
	display:flex;align-items:baseline;padding:10px 24px;
	border-bottom:1px solid #f0f0f1;gap:16px;
}
.fl-debug-row:last-child{border-bottom:none}
.fl-debug-row-name{
	flex:0 0 220px;font-weight:500;color:#1d2327;font-size:13px;
}
.fl-debug-row-data{flex:1;min-width:0;color:#646970;font-size:13px;word-break:break-word}
.fl-debug-row.fl-debug-row-solo{display:block}
.fl-debug-row.fl-debug-row-solo .fl-debug-row-data{color:#1d2327}
.fl-debug-positive{color:#00a32a;font-weight:500}
.fl-debug-negative{color:#d63638;font-weight:500}
.fl-debug-list{display:flex;flex-direction:column;gap:0}
.fl-debug-list-item{padding:6px 0}
.fl-debug-list-bordered .fl-debug-list-item{border-bottom:1px solid #f0f0f1;padding:8px 0}
.fl-debug-list-bordered .fl-debug-list-item:last-child{border-bottom:none}
.fl-debug-list-item a{color:#0E5A71;text-decoration:none}
.fl-debug-list-item a:hover{text-decoration:underline}
.fl-debug-value a{color:#0E5A71;text-decoration:none}
.fl-debug-value a:hover{text-decoration:underline}
.fl-debug-kv-list{width:100%}
.fl-debug-kv-row{
	display:flex;align-items:center;justify-content:space-between;
	padding:8px 0;border-bottom:1px solid #f0f0f1;gap:12px;
}
.fl-debug-kv-row:last-child{border-bottom:none}
.fl-debug-kv-label{font-weight:500;color:#1d2327;font-size:13px}
.fl-debug-kv-val{color:#646970;font-size:13px}
.fl-debug-kv-val.fl-debug-positive{color:#00a32a;font-weight:500}
.fl-debug-kv-val.fl-debug-negative{color:#d63638;font-weight:500}
.fl-debug-pre{
	background:#f6f7f7;border:1px solid #e0e0e0;border-radius:6px;padding:14px 16px;
	font-size:12px;line-height:1.6;font-family:Menlo,Consolas,"Courier New",monospace;
	overflow-x:auto;overflow-y:auto;max-height:300px;
	margin:4px 0 0;white-space:pre;tab-size:4;
}
.fl-debug-footer{
	text-align:center;padding:24px 0 8px;color:#787c82;font-size:12px;
}
.fl-debug-footer a{color:#0E5A71;text-decoration:none}
.fl-debug-footer a:hover{text-decoration:underline}
.fl-debug-card[id]{scroll-margin-top:20px}
.fl-debug-toc-list{list-style:none;margin:0;padding:0}
.fl-debug-toc-list li{padding:8px 24px;border-bottom:1px solid #f0f0f1;font-size:13px}
.fl-debug-toc-list li:last-child{border-bottom:none}
.fl-debug-toc-list a{color:#0E5A71;text-decoration:none}
.fl-debug-toc-list a:hover{text-decoration:underline}
.fl-debug-toc-toggle{
	display:flex;align-items:center;justify-content:space-between;cursor:pointer;
	padding:16px 24px;margin:0;border-bottom:1px solid #f0f0f1;background:#fafafa;
	font-size:14px;font-weight:600;color:#1d2327;user-select:none;
}
.fl-debug-toc-toggle:hover{background:#f6f7f7}
.fl-debug-toc-toggle .fl-debug-toc-arrow{
	display:inline-block;width:8px;height:8px;
	border-right:2px solid #787c82;border-bottom:2px solid #787c82;
	transform:rotate(45deg);transition:transform 0.2s ease;
}
.fl-debug-toc.collapsed .fl-debug-toc-toggle .fl-debug-toc-arrow{transform:rotate(-45deg)}
.fl-debug-toc.collapsed .fl-debug-toc-list{display:none}
.fl-debug-back-top{
	display:inline-flex;align-items:center;gap:4px;
	font-size:12px;color:#787c82;text-decoration:none;
	padding:8px 24px;
}
.fl-debug-back-top:hover{color:#0E5A71}
@media(max-width:600px){
	body{padding:20px 12px}
	.fl-debug-row{flex-direction:column;gap:2px}
	.fl-debug-row-name{flex:none;font-size:12px;color:#787c82}
	.fl-debug-card-title{padding:12px 16px}
	.fl-debug-row{padding:10px 16px}
}
</style>
</head>
<body>
<div class="fl-debug-wrap" id="top">
	<div class="fl-debug-header">
		<div>
			<div class="fl-debug-brand"><?php echo esc_html( $branding ); ?><span>.</span></div>
			<div class="fl-debug-subtitle">System Information</div>
			<?php if ( $expiry_text ) : ?>
			<div class="fl-debug-expiry">Debug mode expires in <?php echo esc_html( $expiry_text ); ?></div>
			<?php endif; ?>
		</div>
		<div class="fl-debug-header-actions">
			<button class="fl-debug-raw-btn" onclick="toggleRaw(this)">Plain Text</button>
			<button class="fl-debug-copy" onclick="copyAll(this)">Copy All</button>
			<button class="fl-debug-download-btn" onclick="downloadDebug()">Download</button>
		</div>
	</div>
	<div class="fl-debug-raw" id="fl-debug-raw">
		<pre><?php echo esc_html( $plain_text ); ?></pre>
	</div>
	<div class="fl-debug-styled">
	<div class="fl-debug-card fl-debug-toc" id="toc">
		<div class="fl-debug-toc-toggle" onclick="this.parentElement.classList.toggle('collapsed')">
			<span>Contents</span>
			<span class="fl-debug-toc-arrow"></span>
		</div>
		<ul class="fl-debug-toc-list">
			<?php
			foreach ( $sections as $toc_section ) :
				$toc_slug = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', $toc_section['title'] ) );
				$toc_slug = trim( $toc_slug, '-' );
				?>
			<li><a href="#<?php echo esc_attr( $toc_slug ); ?>"><?php echo esc_html( $toc_section['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
		<?php
		foreach ( $sections as $section ) :
			$section_slug = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', $section['title'] ) );
			$section_slug = trim( $section_slug, '-' );
			?>
	<div class="fl-debug-card" id="<?php echo esc_attr( $section_slug ); ?>">
		<h2 class="fl-debug-card-title"><?php echo esc_html( $section['title'] ); ?></h2>
			<?php
			foreach ( $section['rows'] as $row ) :
				$has_name = isset( $row['name'] ) && '' !== $row['name'];
				?>
		<div class="fl-debug-row<?php echo $has_name ? '' : ' fl-debug-row-solo'; ?>">
				<?php if ( $has_name ) : ?>
			<div class="fl-debug-row-name"><?php echo esc_html( $row['name'] ); ?></div>
			<?php endif; ?>
			<div class="fl-debug-row-data"><?php echo self::render_value( $row['data'] ); ?></div>
		</div>
			<?php endforeach; ?>
	</div>
	<a href="#top" class="fl-debug-back-top">&uarr; Back to top</a>
		<?php endforeach; ?>
	</div>
	<div class="fl-debug-footer">
		<?php echo esc_html( $branding ); ?> <?php echo esc_html( $bb_version ); ?>
		<?php if ( $info_url ) : ?>
		&nbsp;&middot;&nbsp; <a href="<?php echo esc_url( $info_url ); ?>">View PHP Info</a>
		<?php endif; ?>
	</div>
</div>
<script>
function copyAll(btn){
	var text = <?php echo wp_json_encode( $plain_text ); ?>;
	if(navigator.clipboard){
		navigator.clipboard.writeText(text).then(function(){done(btn)});
	}else{
		var ta=document.createElement('textarea');
		ta.value=text;ta.style.position='fixed';ta.style.opacity='0';
		document.body.appendChild(ta);ta.select();
		document.execCommand('copy');document.body.removeChild(ta);
		done(btn);
	}
}
function toggleRaw(btn){
	var el=document.getElementById('fl-debug-raw');
	var wrap=document.querySelector('.fl-debug-wrap');
	el.classList.toggle('visible');
	wrap.classList.toggle('raw-mode');
	btn.classList.toggle('active');
	btn.textContent=el.classList.contains('visible')?'Styled View':'Plain Text';
}
function downloadDebug(){
	var text = <?php echo wp_json_encode( $plain_text ); ?>;
	var blob = new Blob([text],{type:'text/plain'});
	var a = document.createElement('a');
	a.href = URL.createObjectURL(blob);
	a.download = 'beaver-builder-debug.txt';
	document.body.appendChild(a);
	a.click();
	document.body.removeChild(a);
	URL.revokeObjectURL(a.href);
}
function done(btn){
	btn.textContent='Copied!';btn.classList.add('copied');
	setTimeout(function(){btn.textContent='Copy All';btn.classList.remove('copied')},2000);
}
</script>
</body>
</html>
		<?php
	}

	private static function register( $slug, $args ) {
		self::$tests[ $slug ] = $args;
	}

	private static function get_plugins() {

		$plugins = array();
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/update.php';

		$plugins_data = get_plugins();

		foreach ( $plugins_data as $plugin_path => $plugin ) {
			$item = array(
				'label' => sprintf( '%s - version %s by %s.', $plugin['Name'], $plugin['Version'], $plugin['Author'] ),
				'url'   => ! empty( $plugin['PluginURI'] ) ? $plugin['PluginURI'] : '',
			);
			if ( is_plugin_active( $plugin_path ) ) {
				$plugins['active'][] = $item;
			} else {
				$plugins['inactive'][] = $item;
			}
		}
		return $plugins;
	}

	private static function get_mu_plugins() {
		$plugins_data = get_mu_plugins();
		$plugins      = array();

		foreach ( $plugins_data as $plugin_path => $plugin ) {
			$plugins[] = sprintf( '%s version %s by %s', $plugin['Name'], $plugin['Version'], $plugin['Author'] );
		}
		return $plugins;
	}

	public static function safe_ini_get( $ini ) {
		return @ini_get( $ini ); // @codingStandardsIgnoreLine
	}

	private static function divider() {
		return '----------------------------------------------';
	}

	/**
	 * Returns core table names whose integer primary key has lost AUTO_INCREMENT.
	 *
	 * A faulty database-optimization plugin can strip AUTO_INCREMENT when it rebuilds
	 * tables, after which every new-row insert fails ("Duplicate entry '0' for PRIMARY")
	 * and options/posts silently stop saving. Only WordPress core tables with a known
	 * integer primary key are checked, so plugin tables with string keys never false-flag.
	 *
	 * @return array
	 */
	private static function get_autoincrement_issues() {
		global $wpdb;

		$tables = array(
			$wpdb->options,
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->termmeta,
			$wpdb->links,
		);

		$placeholders = implode( ',', array_fill( 0, count( $tables ), '%s' ) );
		$rows         = $wpdb->get_results(
			$wpdb->prepare(
				// EXTRA is exactly 'auto_increment' for an auto-increment column.
				"SELECT TABLE_NAME AS t, SUM( EXTRA = 'auto_increment' ) AS ai FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME IN ( {$placeholders} ) GROUP BY TABLE_NAME", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $placeholders is a list of %s placeholders, not data.
				array_merge( array( DB_NAME ), $tables )
			),
			OBJECT_K
		);

		$issues = array();
		foreach ( $tables as $table ) {
			// Absent from results = table doesn't exist; skip rather than false-flag.
			if ( isset( $rows[ $table ] ) && 0 === (int) $rows[ $table ]->ai ) {
				$issues[] = $table;
			}
		}
		return $issues;
	}


	private static function prepare_tests() {

		global $wpdb, $wp_version, $wp_json;

		$args = array(
			'name' => 'WordPress',
			'data' => self::divider(),
		);
		self::register( 'wp', $args );

		$args = array(
			'name' => 'WordPress Address',
			'data' => get_option( 'siteurl' ),
		);
		self::register( 'wp_url', $args );

		$args = array(
			'name' => 'Site Address',
			'data' => get_option( 'home' ),
		);
		self::register( 'site_url', $args );

		$args = array(
			'name' => 'IP',
			'data' => $_SERVER['SERVER_ADDR'],
		);
		self::register( 'wp_ip', $args );

		$args = array(
			'name' => 'WP Version',
			'data' => $wp_version,
		);
		self::register( 'wp_version', $args );

		$args = array(
			'name' => 'WP Debug',
			'data' => defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Yes' : 'No',
		);
		self::register( 'wp_debug', $args );

		$args = array(
			'name' => 'FL Debug',
			'data' => FLBuilder::is_debug() ? 'Yes' : 'No',
		);
		self::register( 'fl_debug', $args );

		$args = array(
			'name' => 'FL Modsec Fix',
			'data' => FLBuilderUtils::is_modsec_fix_enabled() ? 'Yes' : 'No',
		);
		self::register( 'fl_modsec', $args );

		$args = array(
			'name' => 'SSL Enabled',
			'data' => is_ssl() ? 'Yes' : 'No',
		);
		self::register( 'wp_ssl', $args );

		$args = array(
			'name' => 'Language',
			'data' => get_locale(),
		);
		self::register( 'lang', $args );

		$args = array(
			'name' => 'Multisite',
			'data' => is_multisite() ? 'Yes' : 'No',
		);
		self::register( 'is_multi', $args );

		$args = array(
			'name' => 'WordPress memory limit',
			'data' => WP_MAX_MEMORY_LIMIT,
		);
		self::register( 'wp_max_mem', $args );

		if ( get_option( 'upload_path' ) != 'wp-content/uploads' && get_option( 'upload_path' ) ) {
			$args = array(
				'name' => 'Possible Issue: upload_path is set, can lead to cache dir issues and css not loading. Check Settings -> Media for custom path.',
				'data' => get_option( 'upload_path' ),
			);
			self::register( 'wp_media_upload_path', $args );
		}

		if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) {
			$args = array(
				'name' => 'Unfiltered HTML is globally disabled! ( DISALLOW_UNFILTERED_HTML )',
				'data' => 'Yes',
			);
			self::register( 'is_multi', $args );
		}

		$args = array(
			'name' => 'Database',
			'data' => self::divider(),
		);
		self::register( 'database', $args );

		$ai_issues = self::get_autoincrement_issues();
		$args      = array(
			'name' => 'Table AUTO_INCREMENT',
			'data' => empty( $ai_issues )
				? 'OK'
				: 'Possible Issue: missing AUTO_INCREMENT on ' . implode( ', ', $ai_issues ) . '. New rows cannot be saved (options/posts silently stop persisting), usually caused by a database-optimization plugin rebuilding tables. Restore AUTO_INCREMENT on the primary key.',
		);
		self::register( 'db_autoincrement', $args );

		$args = array(
			'name' => 'Post Counts',
			'data' => self::divider(),
		);
		self::register( 'post_counts', $args );

		$templates = wp_count_posts( 'fl-builder-template' );

		$post_types = get_post_types( null, 'object' );

		foreach ( $post_types as $type => $type_object ) {

			if ( in_array( $type, array( 'wp_block', 'user_request', 'oembed_cache', 'customize_changeset', 'custom_css', 'nav_menu_item' ) ) ) {
				continue;
			}

			$count = wp_count_posts( $type );

			$args = array(
				'name' => ( 'fl-builder-template' == $type ) ? 'Builder Templates' : 'WordPress ' . $type_object->label,
				'data' => ( $count->inherit > 0 ) ? $count->inherit : $count->publish,
			);
			self::register( 'wp_type_count_' . $type, $args );

			if ( 'fl-builder-history' === $type ) {
				$result = $wpdb->get_results( $wpdb->prepare( "select count(post_id) as total,post_id as ID from $wpdb->postmeta where meta_key LIKE %s group by post_id", '_fl_builder_history_state_%' ) );
				if ( ! empty( $result ) ) {
					$total = count( $result );
					$all   = 0;
					foreach ( $result as $post ) {
						$all += $post->total;
					}
					$avg     = $all / $total;
					$history = sprintf( "\n%s History states across %s Layouts, Average %s", $all, $total, number_format( $avg ) );
				}
				$args = array(
					'name' => 'History States',
					'data' => $history,
				);
				self::register( 'wp_type_count_history_' . $type, $args );

			}
		}

		$args = array(
			'name' => 'BB Layout Data',
			'data' => self::divider(),
		);
		self::register( 'bb_layout_data', $args );

		$layout_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT p.ID, p.post_title, p.post_type, SUM(LENGTH(pm.meta_value)) AS total_size
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key IN ('_fl_builder_data', '_fl_builder_draft')
			AND p.post_type != 'revision'
			AND p.post_status != 'trash'
			GROUP BY pm.post_id
			ORDER BY total_size DESC"
		);

		if ( empty( $layout_rows ) ) {
			$args = array( 'data' => 'No BB layout data found.' );
			self::register( 'bb_layout_data_empty', $args );
		} else {
			$post_count  = count( $layout_rows );
			$total_bytes = array_sum( array_column( $layout_rows, 'total_size' ) );
			$avg_bytes   = $post_count > 0 ? $total_bytes / $post_count : 0;

			$args = array(
				'name' => 'Total Layout Data Size',
				'data' => FLBuilderUtils::formatbytes( $total_bytes ),
			);
			self::register( 'bb_layout_data_total', $args );

			$args = array(
				'name' => 'Posts With BB Layouts',
				'data' => $post_count,
			);
			self::register( 'bb_layout_data_posts', $args );

			$args = array(
				'name' => 'Average Layout Data Size Per Post',
				'data' => FLBuilderUtils::formatbytes( $avg_bytes ),
			);
			self::register( 'bb_layout_data_avg', $args );

			$alert_threshold = 1024 * 1024;
			$alerts          = array();

			foreach ( $layout_rows as $row ) {
				if ( $row->total_size >= $alert_threshold ) {
					$alerts[] = sprintf(
						'Post ID %d [%s] (%s) - %s',
						$row->ID,
						$row->post_type,
						$row->post_title ? $row->post_title : 'no title',
						FLBuilderUtils::formatbytes( $row->total_size )
					);
				}
			}

			if ( ! empty( $alerts ) ) {
				array_unshift( $alerts, 'WARNING: The following posts have large layout data (>=1MB):' );
				$args = array( 'data' => $alerts );
				self::register( 'bb_layout_data_alerts', $args );

				if ( ! get_option( '_fl_builder_small_data_enabled', 0 ) ) {
					$args = array(
						'name' => 'Suggestion',
						// Enabling Small Data Mode typically reduces layout data size by ~40% (tested across 155 posts).
						'data' => 'Small Data Mode is not enabled. Enabling it in Settings > Advanced > Small Data Mode can reduce layout data size by approximately 40%, however data is only optimized when a layout is next published so existing pages will not be affected until edited and saved. Once all layouts have been re-published the total size could reduce to around ' . FLBuilderUtils::formatbytes( $total_bytes * 0.6 ) . '.',
					);
					self::register( 'bb_layout_data_suggestion', $args );
				}
			}
		}

		$args = array(
			'name' => 'Menus',
			'data' => self::divider(),
		);
		self::register( 'menus', $args );

		$menu_data = wp_get_nav_menus();
		$menus     = array();

		$args = array(
			'name' => 'Total Menus',
			'data' => count( $menu_data ),
		);
		self::register( 'menu_count', $args );

		foreach ( (array) $menu_data as $menu ) {
			$args                    = array(
				'name' => $menu->name,
				'data' => $menu->count,
			);
			$menus[ $menu->term_id ] = $menu->name;
			self::register( 'menu_count_' . $menu->slug, $args );
		}

		$args = array(
			'name' => 'Theme Locations',
			'data' => self::divider(),
		);
		self::register( 'themes_location', $args );

		$locations = get_nav_menu_locations();

		foreach ( (array) $locations as $k => $location ) {
			$args = array(
				'name' => ucfirst( $k ),
				'data' => isset( $menus[ $location ] ) ? 'Menu - ' . $menus[ $location ] : 'No Menu Set',
			);
			self::register( 'menu_location_' . $k, $args );
		}

		$args = array(
			'name' => 'Themes',
			'data' => self::divider(),
		);
		self::register( 'themes', $args );

		$theme = wp_get_theme();
		$args  = array(
			'name' => 'Active Theme',
			'data' => array(
				array(
					'label' => sprintf( '%s - v%s', $theme->get( 'Name' ), $theme->get( 'Version' ) ),
					'url'   => $theme->get( 'ThemeURI' ) ? $theme->get( 'ThemeURI' ) : '',
				),
				sprintf( 'Parent Theme: %s', ( $theme->get( 'Template' ) ) ? $theme->get( 'Template' ) : 'Not a child theme' ),
			),
		);
		self::register( 'active_theme', $args );

		if ( 'bb-theme' === $theme->get( 'Template' ) ) {
			if ( is_dir( trailingslashit( get_stylesheet_directory() ) . 'includes' ) ) {
				$args = array(
					'name' => 'Child Theme includes folder detected.',
					'data' => trailingslashit( get_stylesheet_directory() ) . 'includes/',
				);
				self::register( 'child_includes', $args );
			}

			if ( is_dir( trailingslashit( get_stylesheet_directory() ) . 'fl-builder/modules' ) ) {
				$modules = glob( trailingslashit( get_stylesheet_directory() ) . 'fl-builder/modules/*' );
				if ( ! empty( $modules ) ) {
					$args = array(
						'name' => 'Child Theme builder modules folder detected.',
						'data' => implode( "\n", $modules ),
					);
					self::register( 'child_bb_modules', $args );
				}
			}
		}

		// child theme functions
		if ( $theme->get( 'Template' ) ) {
			$functions_file = trailingslashit( get_stylesheet_directory() ) . 'functions.php';
			$contents       = file_exists( $functions_file ) ? file_get_contents( $functions_file ) : 'No functions.php found.';

			$args = array(
				'name' => 'Child Theme Functions',
				'data' => $contents,
			);
			self::register( 'child_funcs', $args );
		}

		$args = array(
			'name' => 'Plugins',
			'data' => self::divider(),
		);
		self::register( 'plugins', $args );

		$args = array(
			'name' => 'Plugins',
			'data' => self::divider(),
		);
		self::register( 'wp_plugins', $args );

		$defaults = array(
			'active'   => array(),
			'inactive' => array(),
		);

		$plugins = wp_parse_args( self::get_plugins(), $defaults );
		$args    = array(
			'name' => 'Active Plugins',
			'data' => $plugins['active'],
		);
		self::register( 'wp_plugins', $args );

		$args = array(
			'name' => 'Inactive Plugins',
			'data' => $plugins['inactive'],
		);
		self::register( 'wp_plugins_inactive', $args );

		$args = array(
			'name' => 'Must-Use Plugins',
			'data' => self::get_mu_plugins(),
		);
		self::register( 'mu_plugins', $args );

		$args = array(
			'name' => 'PHP',
			'data' => self::divider(),
		);
		self::register( 'php', $args );

		$args = array(
			'name' => 'PHP SAPI',
			'data' => php_sapi_name(),
		);
		self::register( 'php_sapi', $args );

		$args = array(
			'name' => 'PHP JSON Support',
			'data' => ( $wp_json instanceof Services_JSON ) ? '*** NO JSON MODULE ***' : 'yes',
		);
		self::register( 'php_json', $args );

		$args = array(
			'name' => 'PHP Memory Limit',
			'data' => self::safe_ini_get( 'memory_limit' ),
		);
		self::register( 'php_mem_limit', $args );

		$args = array(
			'name' => 'PHP Version',
			'data' => phpversion(),
		);
		self::register( 'php_ver', $args );

		$args = array(
			'name' => 'Post Max Size',
			'data' => self::safe_ini_get( 'post_max_size' ),
		);
		self::register( 'post_max', $args );

		$args = array(
			'name' => 'Max Upload Size',
			'data' => FLBuilderUtils::formatbytes( wp_max_upload_size() ),
		);
		self::register( 'post_max_upload', $args );

		$args = array(
			'name' => 'PHP Max Input Vars',
			'data' => self::safe_ini_get( 'max_input_vars' ),
		);
		self::register( 'post_max_input', $args );

		$args = array(
			'name' => 'PHP Max Execution Time',
			'data' => self::safe_ini_get( 'max_execution_time' ),
		);
		self::register( 'post_max_time', $args );

		$args = array(
			'name' => 'Display Errors',
			'data' => self::safe_ini_get( 'display_errors' ) ? 'Enabled' : 'Disabled',
		);
		self::register( 'display_errors', $args );

		$curl = ( function_exists( 'curl_version' ) ) ? curl_version() : false;
		$args = array(
			'name' => 'Curl',
			'data' => ( $curl ) ? sprintf( '%s - %s', $curl['version'], $curl['ssl_version'] ) : 'Not Enabled.',
		);
		self::register( 'curl', $args );

		$args = array(
			'name' => 'PCRE Backtrack Limit ( default 1000000 )',
			'data' => self::safe_ini_get( 'pcre.backtrack_limit' ),
		);
		self::register( 'backtrack', $args );

		$args = array(
			'name' => 'PCRE Recursion Limit ( default 100000 )',
			'data' => self::safe_ini_get( 'pcre.recursion_limit' ),
		);
		self::register( 'recursion', $args );

		$zlib = self::safe_ini_get( 'zlib.output_compression' );

		if ( $zlib ) {
			$args = array(
				'name' => 'ZLIB Output Compression',
				'data' => $zlib,
			);
			self::register( 'zlib', $args );
		}

		$zlib_handler = self::safe_ini_get( 'zlib.output_handler' );

		if ( $zlib_handler ) {
			$args = array(
				'name' => 'ZLIB Handler',
				'data' => $zlib,
			);
			self::register( 'zlib_handler', $zlib_handler );
		}

		$args = array(
			'name' => 'BB Products',
			'data' => self::divider(),
		);
		self::register( 'bb', $args );

		$info = get_site_option( '_fl_builder_update_info', array() );
		$from = '';
		if ( isset( $info['from'] ) && ! empty( $info['from'] ) ) {
			$from = ' - Previous ' . $info['from'];
		}
		$args = array(
			'name' => 'Beaver Builder',
			'data' => FL_BUILDER_VERSION . $from,
		);
		self::register( 'bb_version', $args );

		$args = array(
			'name' => 'Beaver Themer',
			'data' => ( defined( 'FL_THEME_BUILDER_VERSION' ) ) ? FL_THEME_BUILDER_VERSION : 'Not active/installed.',
		);
		self::register( 'themer_version', $args );

		$args = array(
			'name' => 'Beaver Theme',
			'data' => ( defined( 'FL_THEME_VERSION' ) ) ? FL_THEME_VERSION : 'Not active/installed.',
		);
		self::register( 'theme_version', $args );

		$args = array(
			'name' => 'Assistant',
			'data' => ( defined( 'FL_ASSISTANT_VERSION' ) ) ? FL_ASSISTANT_VERSION : 'Not active/installed.',
		);
		if ( defined( 'FL_ASSISTANT_BB_EXTENSION' ) && FL_ASSISTANT_BB_EXTENSION ) {
			$args['data'] .= ' (bundled)';
		}
		self::register( 'assistant_version', $args );

		$args = array(
			'name' => 'Modules',
			'data' => self::divider(),
		);
		self::register( 'modules', $args );

		$enabled_modules = FLBuilderModel::get_enabled_modules();
		sort( $enabled_modules );

		$all_modules      = FLBuilderModel::get_uncategorized_modules( true );
		$all_modules_list = array();

		foreach ( $all_modules as $module ) {
			if ( isset( $module->slug ) ) {
				array_push( $all_modules_list, $module->slug );
			}
		}

		$disabled_modules = array_filter( array_diff( $all_modules_list, $enabled_modules ) );

		$args = array(
			'name' => 'Disabled Modules',
			'data' => $disabled_modules,
		);
		self::register( 'disabled_modules', $args );

		$args = array(
			'name' => 'Enabled Modules',
			'data' => $enabled_modules,
		);
		self::register( 'enabled_modules', $args );

		$args = array(
			'name' => 'Cache Folders',
			'data' => self::divider(),
		);
		self::register( 'cache_folders', $args );

		$cache = FLBuilderModel::get_cache_dir();

		$args = array(
			'name' => 'Beaver Builder Cache Path',
			'data' => $cache['path'],
		);
		self::register( 'bb_cache_path', $args );

		$args = array(
			'name' => 'Beaver Builder Path writable',
			'data' => ( fl_builder_filesystem()->is_writable( $cache['path'] ) ) ? 'Yes' : 'No',
		);
		self::register( 'bb_cache_path_writable', $args );

		if ( class_exists( 'FLCustomizer' ) ) {
			$cache = FLCustomizer::get_cache_dir();

			$args = array(
				'name' => 'Beaver Theme Cache Path',
				'data' => $cache['path'],
			);
			self::register( 'bb_theme_cache_path', $args );

			$args = array(
				'name' => 'Beaver Theme Path writable',
				'data' => ( fl_builder_filesystem()->is_writable( $cache['path'] ) ) ? 'Yes' : 'No',
			);
			self::register( 'bb_theme_cache_path_writable', $args );
		}

		$args = array(
			'name' => 'WordPress Content Path',
			'data' => WP_CONTENT_DIR,
		);
		self::register( 'bb_content_path', $args );

		$args = array(
			'name' => 'License',
			'data' => self::divider(),
		);
		self::register( 'license', $args );

		if ( true === FL_BUILDER_LITE ) {
			$args = array(
				'name' => 'Beaver Builder License',
				'data' => 'Lite version detected',
			);
			self::register( 'bb_sub_lite', $args );

		} elseif ( class_exists( 'FLUpdater' ) ) {
			$subscription = FLUpdater::get_subscription_info();
			$args         = array(
				'name' => 'Beaver Builder License',
				'data' => ( isset( $subscription->active ) && ! isset( $subscription->error ) ) ? 'Active' : 'Not Active',
			);
			self::register( 'bb_sub', $args );

			if ( isset( $subscription->error ) ) {
				$args = array(
					'name' => 'License Error',
					'data' => $subscription->error,
				);
				self::register( 'bb_sub_err', $args );
			}

			if ( isset( $subscription->domain ) ) {
				$args = array(
					'name' => 'Domain Active',
					'data' => ( '1' == $subscription->domain->active ) ? 'Yes' : 'No',
				);
				self::register( 'bb_sub_domain', $args );
			}
			if ( isset( $subscription->downloads ) && is_array( $subscription->downloads ) && ! empty( $subscription->downloads ) ) {
				$args = array(
					'name' => 'Available Downloads',
					'data' => array_values( $subscription->downloads ),
				);
				self::register( 'av_downloads', $args );
			}
		}

		self::register( 'adv', array(
			'name' => 'Advanced Options',
			'data' => self::divider(),
		) );

		$adv_opts = FLBuilderAdminAdvanced::get_settings();
		$opts     = array();
		foreach ( $adv_opts as $key => $opt ) {
			$option = get_option( "_fl_builder_{$key}", $opt['default'] ) ? 'Enabled' : 'Disabled';
			$opts[] = sprintf( '%s: %s', $opt['label'], $option );
		}
		$args = array(
			'data' => $opts,
		);
		self::register( 'adv_settings', $args );

		$args = array(
			'name' => 'Server',
			'data' => self::divider(),
		);
		self::register( 'serv', $args );

		$args = array(
			'name' => 'MySQL Version',
			'data' => ( ! empty( $wpdb->is_mysql ) ? $wpdb->db_version() : 'Unknown' ),
		);
		self::register( 'mysql_version', $args );

		$results = (array) $wpdb->get_results( 'SHOW VARIABLES' );

		foreach ( $results as $k => $result ) {
			if ( 'max_allowed_packet' === $result->Variable_name ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$args = array(
					'name' => 'MySQL Max Allowed Packet',
					'data' => number_format( $result->Value / 1048576 ) . 'MB', // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				);
				self::register( 'mysql_packet', $args );
			}
		}

		$db_bytes = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT SUM(data_length + index_length) FROM information_schema.TABLES where table_schema = %s GROUP BY table_schema;',
				DB_NAME
			)
		);

		if ( is_numeric( $db_bytes ) ) {
			$args = array(
				'name' => 'MySQL Database Size',
				'data' => number_format( $db_bytes / 1048576 ) . 'MB',
			);
			self::register( 'mysql_size', $args );
		}

		$collation = $wpdb->get_var( "SELECT TABLE_COLLATION FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$wpdb->postmeta}';" );
		$args      = array(
			'name' => 'PostMeta DB Collation',
			'data' => $collation,
		);
		self::register( 'collationdb', $args );

		$args = array(
			'name' => 'DB_CHARSET',
			'data' => defined( 'DB_CHARSET' ) && DB_CHARSET ? DB_CHARSET : 'Undefined',
		);
		self::register( 'charset', $args );

		$args = array(
			'name' => 'DB_COLLATE',
			'data' => defined( 'DB_COLLATE' ) && DB_COLLATE ? DB_COLLATE : 'Undefined',
		);
		self::register( 'collation', $args );

		$args = array(
			'name' => 'Server Info',
			'data' => $_SERVER['SERVER_SOFTWARE'],
		);
		self::register( 'server', $args );

		$args = array(
			'name' => 'htaccess files',
			'data' => self::divider(),
		);
		self::register( 'up_htaccess', $args );

		// detect uploads folder .htaccess file and display it if found.
		$uploads          = wp_upload_dir( null, false );
		$uploads_htaccess = trailingslashit( $uploads['basedir'] ) . '.htaccess';
		$root_htaccess    = trailingslashit( ABSPATH ) . '.htaccess';

		if ( file_exists( $root_htaccess ) ) {
			ob_start();
			readfile( $root_htaccess );
			$htaccess = ob_get_clean();
			$args     = array(
				'name' => $root_htaccess . "\n",
				'data' => $htaccess,
			);
			self::register( 'up_htaccess_root', $args );
		}
		if ( file_exists( $uploads_htaccess ) ) {
			ob_start();
			readfile( $uploads_htaccess );
			$htaccess = ob_get_clean();
			$args     = array(
				'name' => $uploads_htaccess . "\n",
				'data' => $htaccess,
			);
			self::register( 'up_htaccess_uploads', $args );
		}
	}
}
add_action( 'plugins_loaded', array( 'FL_Debug', 'init' ) );
