<?php

/**
 * Helper class for injecting custom HTML attributes to the rendered nodes.
 *
 * @since 2.11
 */
final class FLBuilderCustomAttributes {

	/**
	 * The HTML tag processor instance.
	 *
	 * @since 2.11
	 * @access private
	 * @var object $processor
	 */
	private static $processor = null;

	/**
	 * The position inside the selector.
	 *
	 * @since 2.11
	 * @access private
	 * @var integer $position
	 */
	private static $position = 0;

	/**
	 * The rendered HTML output.
	 *
	 * @since 2.11
	 * @access private
	 * @var string $html
	 */
	private static $html = '';

	/**
	 * The current parsed tag element name.
	 *
	 * @since 2.11
	 * @access private
	 * @var string $tag
	 */
	private static $tag = '';

	/**
	 * The CSS rule selector.
	 *
	 * @since 2.11
	 * @access private
	 * @var array $selector
	 */
	private static $selector = array();

	/**
	 * The nested tags history.
	 *
	 * @since 2.11
	 * @access private
	 * @var array $nested
	 */
	private static $nested = array();

	/**
	 * The nested levels of matched tags.
	 *
	 * @since 2.11
	 * @access private
	 * @var array $matched
	 */
	private static $matched = array();

	/**
	 * List of void self-closing elements.
	 *
	 * @since 2.11
	 * @access private
	 * @var array VOID
	 */
	private const VOID = array(
		'area',
		'base',
		'br',
		'col',
		'embed',
		'hr',
		'img',
		'input',
		'link',
		'meta',
		'source',
		'track',
		'wbr',
	);

	/**
	 * Adds accessibility attributes if not set & applicable to the node wrapper.
	 *
	 * @since 2.11
	 * @access public
	 * @method wrapper_custom_attributes
	 * @param object $node
	 * @param array $defaults
	 * @return array
	 */
	public static function wrapper_custom_attributes( $node, $defaults ) {
		$attributes = array_merge( $defaults, self::extract_custom_attributes( $node->settings->custom_attributes ) );
		if ( isset( $attributes['role'] ) ) {
			$attributes['role'] = strtolower( $attributes['role'] );
		}
		if ( 'module' === $node->type ) {
			$applicable = array(
				'content-slider',
				'post-carousel',
				'testimonials',
				'post-slider',
				'slideshow',
			);
			if ( ! isset( $attributes['role'] ) && in_array( $node->slug, $applicable ) && 'section' !== $node->settings->container_element ) {
				$attributes['role'] = 'region';
			}
			if ( isset( $attributes['aria-label'] ) ) {
				$attributes['aria-roledescription'] = $attributes['aria-roledescription'] ?? $node->name;
			} else {
				$attributes['aria-label'] = $node->name;
			}
		}
		return $attributes;
	}

	/**
	 * Extracts the attributes added by the user if they exist.
	 *
	 * @since 2.11
	 * @access private
	 * @method extract_custom_attributes
	 * @param array $attributes
	 * @param string $target
	 * @return array
	 */
	private static function extract_custom_attributes( $attributes, $target = 'wrapper' ) {
		$extracts = array();
		foreach ( $attributes as $attribute ) {
			// Skip invalid attribute keys for HTML attribute naming conventions
			if ( ! preg_match( '/^[a-z]{1}[a-z0-9_.:-]*$/', $attribute->key ) ) {
				continue;
			}
			$attribute->value    = esc_html( $attribute->value );
			$attribute->selector = trim( $attribute->selector );
			if ( ! $attribute->key || ! $attribute->value || $target !== $attribute->target ) {
				continue;
			}
			if ( 'wrapper' === $target ) {
				$extracts[ $attribute->key ] = $attribute->value;
			} elseif ( 'custom' === $target && $attribute->selector && preg_match( '/^[a-zA-Z0-9_\-\*\s\.>]+$/', $attribute->selector ) ) {
				$extracts[] = $attribute;
			}
		}
		return $extracts;
	}

	/**
	 * Injects custom attributes into the HTML output.
	 *
	 * @since 2.11
	 * @access public
	 * @method inject_custom_attributes
	 * @param object $attributes
	 * @param string $html
	 * @return string
	 */
	public static function inject_custom_attributes( $attributes, $html ) {
		$attributes = self::extract_custom_attributes( $attributes, 'custom' );
		self::$html = $html;
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				self::$selector  = explode( ' ', trim( $attribute->selector ) );
				self::$processor = new WP_HTML_Tag_Processor( self::$html );
				self::iterate_html_tags( $attribute );
			}
		}
		return self::$html;
	}

	/**
	 * Iterates through the HTML tags and injects custom attributes.
	 *
	 * @since 2.11
	 * @access private
	 * @method iterate_html_tags
	 * @param object $attribute
	 * @return void
	 */
	private static function iterate_html_tags( $attribute ) {
		while ( self::$processor->next_token() ) {
			// Only process HTML tags
			if ( '#tag' !== self::$processor->get_token_type() ) {
				continue;
			}
			self::$tag = strtolower( self::$processor->get_tag() );
			self::$processor->is_tag_closer() ? self::process_closing_tags() : self::process_opening_tags( $attribute );
		}
		self::$html = self::$processor->get_updated_html();
	}

	/**
	 * Process closing tags and adjust the position as needed.
	 *
	 * @since 2.11
	 * @access private
	 * @method process_closing_tags
	 * @return void
	 */
	private static function process_closing_tags() {
		// Check if the current cleared tag was a matching tag
		if ( ! empty( self::$matched ) && count( self::$nested ) === self::$matched[ count( self::$matched ) - 1 ] ) {
			array_pop( self::$matched );
			// Check if the current cleared tag was a parent tag in the selector and adjust position if a child combinator is used
			if ( self::$position - self::child_combinator_offset() > count( self::$matched ) ) {
				--self::$position;
			}
			// Move back the position when current position has a child combinator
			if ( '>' === self::$selector[ self::$position ] ) {
				--self::$position;
			}
		}
		// Remove the cleared tag from the nested history
		array_pop( self::$nested );
	}

	/**
	 * Calculates the offset for child combinators in the selector if present.
	 *
	 * @since 2.11
	 * @access private
	 * @method child_combinator_offset
	 * @return integer
	 */
	private static function child_combinator_offset() {
		// No offset if the current position is a child combinator
		if ( '>' === self::$selector[ self::$position ] ) {
			return 0;
		}
		// Count the number of child combinators before the current position
		$current     = array_slice( self::$selector, 0, self::$position + 1 );
		$combinators = array_filter( $current, function ( $item ) {
			return '>' === $item;
		} );
		return count( $combinators );
	}

	/**
	 * Process new opening tags and update the nesting/matching history.
	 *
	 * @since 2.11
	 * @access private
	 * @method process_opening_tags
	 * @param object $attribute
	 * @return void
	 */
	private static function process_opening_tags( $attribute ) {
		// Only keep track of non-void tags
		if ( ! in_array( self::$tag, self::VOID ) ) {
			self::$nested[] = self::$tag;
		}
		// Check if the current tag matches the selector at the current position
		if ( self::match_selector_position() && self::match_child_combinator() ) {
			// Keep track of the matched tags and their nested level
			self::$matched[] = count( self::$nested );
			self::apply_tag_attribute( $attribute );
		}
	}

	/**
	 * Match selector through (ID/CLASS/TAG) at the current position.
	 *
	 * @since 2.11
	 * @access private
	 * @method match_selector_position
	 * @return bool
	 */
	private static function match_selector_position() {
		$selector = self::$selector[ self::$position ];
		// In case of special symbols
		if ( in_array( $selector, [ '*', '>' ] ) ) {
			return self::handle_special_symbols( $selector );
		} elseif ( false === strpos( $selector, '.' ) && false === strpos( $selector, '#' ) ) {
			// Only tag selector matching
			return strtolower( $selector ) === self::$tag;
		} else {
			// Match TAG if present
			preg_match( '/^([a-zA-Z]*)?/', $selector, $match );
			if ( ! empty( $match[1] ) && strtolower( $match[1] ) !== self::$tag ) {
				return false;
			}
			// Match ID if present
			preg_match( '/#([\w-]+)/', $selector, $match );
			if ( ! empty( $match[1] ) && $match[1] !== self::$processor->get_attribute( 'id' ) ) {
				return false;
			}
			// Match CLASS if present
			preg_match_all( '/\.([\w-]+)/', $selector, $matches );
			foreach ( $matches[1] as $class ) {
				if ( ! self::$processor->has_class( $class ) ) {
					return false;
				}
			}
			return true;
		}
	}

	/**
	 * Checks if the current selector uses a child combinator.
	 *
	 * @since 2.11
	 * @access private
	 * @method match_child_combinator
	 * @return bool
	 */
	private static function match_child_combinator() {
		// Check if the previous matched selector is a child combinator
		if ( 1 < self::$position && '>' === self::$selector[ self::$position - 1 ] ) {
			// Ensure the current tag is a direct child of the last matched tag
			if ( ! empty( self::$matched ) && count( self::$nested ) - 1 === self::$matched[ count( self::$matched ) - 1 ] ) {
				return true;
			} else {
				return false;
			}
		}
		// Always true if no child combinator is present
		return true;
	}

	/**
	 * Handle special operators.
	 *
	 * @since 2.11
	 * @access private
	 * @method handle_special_symbols
	 * @param string $symbol
	 * @return bool
	 */
	private static function handle_special_symbols( $symbol ) {
		if ( '*' === $symbol ) {
			// Always match if the selector is a wildcard
			return true;
		} elseif ( '>' === $symbol ) {
			// Move the selector position to the following child selector
			self::$position++;
			return self::match_selector_position();
		}
		return false;
	}

	/**
	 * Validates if the current tag matches the selector at the current position.
	 *
	 * @since 2.11
	 * @access private
	 * @method apply_tag_attribute
	 * @param object $attribute
	 * @return void
	 */
	private static function apply_tag_attribute( $attribute ) {
		// The target tag is found when the last position of the selector is reached or matched in a nested level
		if ( count( self::$selector ) - 1 === self::$position && count( self::$nested ) === self::$matched[ count( self::$matched ) - 1 ] ) {
			self::$processor->set_attribute( $attribute->key, $attribute->value );
			// Remove the last matched tag from the history if it's a void tag since it won't have a closing tag to trigger the position reset
			if ( in_array( self::$tag, self::VOID ) ) {
				array_pop( self::$matched );
			}
		} elseif ( ! in_array( self::$tag, self::VOID ) && self::$position < count( self::$selector ) - 1 ) {
			// Move to the next position if the tag is not a void tag
			self::$position++;
		}
	}
}
