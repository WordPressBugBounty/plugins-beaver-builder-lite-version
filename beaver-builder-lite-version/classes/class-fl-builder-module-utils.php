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
			return sprintf( '<span class="sr-only">%s</span>', __( '(opens in new tab)', 'fl-builder' ) );
		}
		return '';
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
