<?php

/**
 * Module helper methods.
 *
 * @since 2.11
 * @access public
 * @package FLBuilder
 * @subpackage Classes
 * @author Mahammad
 */
final class FLBuilderModuleUtils {

	/**
	 * Build the HTML string for frontend output.
	 *
	 * @since 2.11
	 * @access public
	 * @method join_html_attributes
	 * @param array $attributes The HTML attributes to process & join.
	 * @return string The HTML attributes as a string for frontend output.
	 */
	public static function join_html_attributes( array $attributes ): string {
		$output = [];
		foreach ( $attributes as $key => $value ) {
			$value = is_array( $value ) ? join( ' ', $value ) : $value;
			// Mirror FLBuilder::render_node_attributes(): drop empty values including
			// integer 0, keep only the string '0'. Otherwise attrs the legacy echo path
			// suppressed (e.g. data-dynamic-editing => (int) 0 on static global nodes) get
			// rendered as ="0", which the builder overlay JS reads as truthy and
			// misclassifies the node as a dynamic global.
			if ( empty( $value ) && '0' !== $value ) {
				continue;
			}
			if ( 'booleans' === $key ) {
				$output[] = esc_attr( $value );
			} else {
				$output[] = $key . '="' . esc_attr( $value ) . '"';
			}
		}
		return join( ' ', $output );
	}

	/**
	 * Return the link element relevant attributes based on the module settings and extra attributes.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_link_attributes
	 * @param object $settings The module settings of the link element.
	 * @param string $key The key identifier for link-related properties in the module.
	 * @param array $extra Extra attributes for the link element.
	 * @param bool $joined Whether to join the attributes into a single string.
	 * @return string|array The link element attributes as a string for frontend output or an array of attributes if joining is disabled.
	 */
	public static function get_link_attributes( object $settings, string $key, array $extra = [], bool $joined = true )/*after PHP8 wide support: string|array*/ {
		$attributes = [];
		if ( ! empty( $settings->{ $key } ) ) {
			$attributes['href'] = esc_url( do_shortcode( $settings->{ $key } ) );
		}
		if ( ! empty( $settings->{ $key . '_target' } ) ) {
			$attributes['target'] = $settings->{ $key . '_target' };
		}
		$attributes['rel'] = self::get_link_relationship( $settings, $key );
		if ( 'yes' === ( $settings->{ $key . '_download' } ?? '' ) ) {
			$attributes['booleans'][] = 'download';
		}
		if ( ! empty( $extra['booleans'] ) && is_array( $extra['booleans'] ) ) {
			$attributes['booleans'] = array_merge( $attributes['booleans'] ?? [], $extra['booleans'] );
			unset( $extra['booleans'] );
		}
		$attributes = array_merge( $attributes, $extra );
		return $joined ? self::join_html_attributes( $attributes ) : $attributes;
	}

	/**
	 * Return the link element relationship attribute values based on the module settings.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_link_relationship
	 * @param object $settings The module settings of the link element.
	 * @param string $key The key identifier for link-related properties in the module.
	 * @param array $allowed An array of allowed relationship values to return.
	 * @return string The relationship attribute values as a space-separated string based on the settings and allowed values.
	 */
	public static function get_link_relationship( object $settings, string $key, array $allowed = [ 'noopener', 'nofollow' ] ): string {
		$mapping      = [
			'noopener' => '_blank' === ( $settings->{ $key . '_target' } ?? '' ),
			'nofollow' => 'yes' === ( $settings->{ $key . '_nofollow' } ?? '' ),
		];
		$relationship = [];
		foreach ( $mapping as $property => $condition ) {
			if ( $condition && in_array( $property, $allowed, true ) ) {
				$relationship[] = $property;
			}
		}
		return join( ' ', $relationship );
	}

	/**
	 * Return a screen reader notice text for link elements opening a new tab.
	 *
	 * No longer called by bundled module templates as of 2.12 — baking this
	 * into rendered HTML persists it into saved content (`post_content`),
	 * where it can surface as visible text in contexts that don't load the
	 * `.sr-only` CSS (archives, RSS). `FLBuilderLayout._initLinkNotices()`
	 * (`js/fl-builder-layout.js`) now adds the same notice client-side,
	 * only against genuinely rendered pages. Left in place for third-party
	 * modules that may already call it directly. The returned span carries
	 * a dedicated `fl-new-tab-notice` class (in addition to `sr-only`) so
	 * `strip_stale_link_notices()` can identify it precisely, rather than
	 * matching any `sr-only` span.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_link_notice
	 * @param object $settings The module settings of the link element.
	 * @param string $key The key identifier for link-related properties in the module.
	 * @return string The notice text for the link if it opens in a new tab.
	 */
	public static function get_link_notice( object $settings, string $key ): string {
		$url    = $settings->{ $key } ?? '';
		$target = $settings->{ $key . '_target' } ?? '';
		if ( $url && '_blank' === $target ) {
			return sprintf( '<span class="fl-new-tab-notice sr-only">%s</span>', __( '(opens in new tab)', 'fl-builder' ) );
		}
		return '';
	}

	/**
	 * Filters a rendered module's HTML to strip any sr-only "opens in new
	 * tab" notice baked into saved content by the removed TinyMCE
	 * content-events script or a module's now-removed get_link_notice()
	 * call.
	 *
	 * Two passes:
	 *  1. Any span carrying the dedicated `fl-new-tab-notice` class is
	 *     stripped unconditionally, regardless of position — only BB ever
	 *     writes this class, so no further scoping is needed.
	 *  2. A narrow legacy fallback for content saved before the class
	 *     existed: a bare `sr-only` span is stripped only when it sits
	 *     inside a `target="_blank"` anchor AND its text matches the known
	 *     notice string (current-locale translation or the English
	 *     literal). This can't match unrelated sr-only content — e.g. the
	 *     icon module's `sr_text` field or the post-grid/tabs/accordion
	 *     "Read More" context span — since neither the anchor scope nor
	 *     the text will match.
	 *
	 * Hooked once onto `fl_builder_render_module_content` rather than
	 * called from every module's own template.
	 *
	 * @since 2.12
	 * @access public
	 * @method strip_stale_link_notices
	 * @param string $content The rendered module HTML.
	 * @return string
	 */
	public static function strip_stale_link_notices( string $content ): string {
		// Pass 1: our own dedicated class, anywhere, unconditionally. Linear, but
		// guarded anyway so a PCRE failure can never fatal a page render.
		$stripped = preg_replace( '/<span class="[^"]*\bfl-new-tab-notice\b[^"]*">[^<]*<\/span>/i', '', $content );

		if ( null !== $stripped ) {
			$content = $stripped;
		}

		// Pass 2: legacy bare sr-only notices, scoped to target="_blank" anchors
		// and known notice text only.
		return self::strip_legacy_link_notices( $content );
	}

	/**
	 * Strips pre-2.12 bare `sr-only` notices sitting immediately before the
	 * `</a>` of a `target="_blank"` anchor.
	 *
	 * Runs as a single linear scan rather than one anchor-scoped regex. A pattern
	 * that walks from the opening `<a>` to the notice has to cross an unbounded
	 * run of markup; on a module holding a large document with unbalanced anchor
	 * tags that exhausted the PCRE JIT stack, so preg_replace() returned null and
	 * the string return type raised a TypeError mid-render (#5689). Every branch
	 * of the scan pattern is linear, so cost stays proportional to content size.
	 *
	 * @since 2.12
	 * @access private
	 * @method strip_legacy_link_notices
	 * @param string $content The rendered module HTML.
	 * @return string
	 */
	private static function strip_legacy_link_notices( string $content ): string {
		$notice_texts = array_unique( array_filter( array(
			__( '(opens in new tab)', 'fl-builder' ),
			'(opens in new tab)',
		) ) );

		// Anchor opens, anchor closes, or a notice span directly before `</a>`.
		$pattern = sprintf(
			'/<a\b[^>]*>|<\/a\s*>|<span class="sr-only">(?:%s)<\/span>(?=\s*<\/a>)/is',
			implode( '|', array_map( function ( $text ) {
				return preg_quote( $text, '/' );
			}, $notice_texts ) )
		);

		if ( ! preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			return $content;
		}

		return self::remove_notices_in_new_tab_links( $content, $matches[0] );
	}

	/**
	 * Walks the scan results once, tracking which anchor is currently open, and
	 * drops every notice found inside a `target="_blank"` one.
	 *
	 * @since 2.12
	 * @access private
	 * @method remove_notices_in_new_tab_links
	 * @param string $content The rendered module HTML.
	 * @param array  $matches Offset-captured matches from the scan pattern.
	 * @return string
	 */
	private static function remove_notices_in_new_tab_links( string $content, array $matches ): string {
		$open_tag = '';
		$removals = array();

		foreach ( $matches as $match ) {
			list( $text, $offset ) = $match;

			if ( 0 === stripos( $text, '</a' ) ) {
				$open_tag = '';
			} elseif ( 0 === stripos( $text, '<a' ) ) {
				$open_tag = $text;
			} elseif ( preg_match( '/\btarget=["\']_blank["\']/i', $open_tag ) ) {
				$removals[] = array( $offset, strlen( $text ) );
			}
		}

		// Back to front, so each removal leaves the earlier offsets valid.
		foreach ( array_reverse( $removals ) as $removal ) {
			$content = substr_replace( $content, '', $removal[0], $removal[1] );
		}

		return $content;
	}

	/**
	 * Return the classes for an icon element based on the module settings.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_icon_classes
	 * @param object $settings The module settings for the icon.
	 * @return string The joined CSS classes for the icon.
	 */
	public static function get_icon_classes( object $settings, string $prefix = '' ): string {
		$classes    = [];
		$properties = [ 'icon', 'icon_extra' ];
		foreach ( $properties as $property ) {
			$name = $prefix . $property;
			if ( ! empty( $settings->{$name} ) ) {
				$classes[] = trim( $settings->{$name} );
			}
		}
		return join( ' ', $classes );
	}
}
