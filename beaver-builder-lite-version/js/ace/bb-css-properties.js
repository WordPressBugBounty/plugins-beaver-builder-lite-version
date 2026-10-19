/**
 * BB-curated CSS properties for the Ace CSS linter.
 *
 * Ace's CSS validation runs in a Web Worker (js/ace/worker-css.js) using a
 * bundled copy of CSSLint/parserlib. parserlib's known-property dictionary is
 * effectively frozen (no meaningful release since ~2018), so any CSS property
 * that shipped in browsers after that date is reported as an "unknown property"
 * warning. Verified against the latest Ace release (1.44.0): the same gap
 * exists upstream, so updating Ace does not fix it. See issue #5291.
 *
 * There is no runtime API to extend the dictionary: it is a browserify-internal
 * module export inside the worker bundle, runs in a separate thread, and exposes
 * no "add property" message. The only place the list can be extended is the
 * worker bundle itself.
 *
 * This file is the human-maintained source of truth for the additions. It is
 * NOT loaded at runtime. The actual fix is mirrored into worker-css.js as
 * `"<prop>":1` entries (value `1` = "recognize the property name, do not
 * strictly validate its value"). The unknown-property check stays ON, so genuine
 * typos (e.g. `colr: red`) are still flagged -- only these known-good modern
 * properties are added to the accepted set.
 *
 * To add a property: add it here, then add `"<prop>":1,` to the property map in
 * worker-css.js (search for `"aspect-ratio":1,` -- the BB additions follow it,
 * ending just before `azimuth:"<azimuth>"`).
 */
export const BB_CSS_PROPERTIES = [
	// CSS aspect-ratio (the property from issue #5291).
	'aspect-ratio',

	// Logical / flow-relative box model.
	'inset', 'inset-block', 'inset-inline',
	'margin-block', 'margin-inline',
	'padding-block', 'padding-inline',
	'border-block', 'border-inline',
	'block-size', 'inline-size',
	'min-block-size', 'max-block-size',
	'min-inline-size', 'max-inline-size',

	// Scroll / scroll snap.
	'scroll-behavior', 'scroll-snap-type', 'scroll-snap-align',
	'scroll-margin', 'scroll-padding', 'overscroll-behavior',

	// Containment / container queries.
	'contain', 'container', 'container-type', 'container-name', 'content-visibility',

	// Visual effects.
	'backdrop-filter', '-webkit-backdrop-filter', 'background-blend-mode', 'mask-image', 'isolation',

	// Individual transform properties.
	'translate', 'rotate', 'scale', 'transform-box',

	// Box alignment shorthands.
	'place-items', 'place-content', 'place-self',

	// Text decoration / layout.
	'text-decoration-thickness', 'text-underline-offset', 'text-orientation',
	'line-clamp', '-webkit-line-clamp',

	// Color / color scheme.
	'accent-color', 'caret-color', 'color-scheme',

	// Misc.
	'row-gap', 'shape-outside', 'overflow-anchor',
	'forced-color-adjust', 'print-color-adjust', '-webkit-print-color-adjust',
];
