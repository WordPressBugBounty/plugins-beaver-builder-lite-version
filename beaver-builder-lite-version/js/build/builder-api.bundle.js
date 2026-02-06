/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/FL/Builder/api/arrays/index.js":
/*!********************************************!*\
  !*** ./src/FL/Builder/api/arrays/index.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   isLast: () => (/* binding */ isLast),\n/* harmony export */   updateItemWhere: () => (/* binding */ updateItemWhere),\n/* harmony export */   updateLastItem: () => (/* binding */ updateLastItem),\n/* harmony export */   withoutLastItem: () => (/* binding */ withoutLastItem)\n/* harmony export */ });\n/**\n * Check if a given index is the last item in the array\n *\n * @param Array\n * @param Int\n * @return bool\n */\nconst isLast = (arr, i) => i === arr.length - 1;\n\n/**\n * Get an array without the last item\n * AKA pop(), but useful and functional.\n *\n * @param Array\n * @return Array\n */\nconst withoutLastItem = arr => {\n  return arr.filter((_, i) => !isLast(arr, i));\n};\nconst updateItemWhere = (arr, isItem = () => false, callback = v => v) => {\n  return arr.map((item, i) => {\n    if (isItem(item, i)) {\n      return callback(item, i);\n    }\n    return item;\n  });\n};\n\n/**\n * Modify the last item in an array and return a new array\n *\n * @param Array arr\n * @param Function callback - function to be run on the last item only\n * @return Array\n */\nconst updateLastItem = (arr, callback = () => {}) => {\n  const isItem = (item, i) => isLast(arr, i);\n  return updateItemWhere(arr, isItem, callback);\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/arrays/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/color-scheme/index.js":
/*!**************************************************!*\
  !*** ./src/FL/Builder/api/color-scheme/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   getComputedColorScheme: () => (/* binding */ getComputedColorScheme),\n/* harmony export */   init: () => (/* binding */ init),\n/* harmony export */   isSystemDark: () => (/* binding */ isSystemDark),\n/* harmony export */   setBodyClasses: () => (/* binding */ setBodyClasses),\n/* harmony export */   systemColorSchemeChanged: () => (/* binding */ systemColorSchemeChanged)\n/* harmony export */ });\nconst darkMediaQuery = '( prefers-color-scheme: dark )';\n\n/**\n * Check if the operating system is set to dark mode.\n *\n * @since 2.6\n * @return bool\n */\nconst isSystemDark = () => {\n  return window.matchMedia && window.matchMedia(darkMediaQuery).matches;\n};\n\n/**\n * Get the static color scheme value even if matching the operating system.\n *\n * @since 2.6\n * @return string (light|dark)\n */\nconst getComputedColorScheme = () => {\n  const colorScheme = FL.Builder.data?.getSystemState().colorScheme;\n  if ('auto' === colorScheme) {\n    return isSystemDark() ? 'dark' : 'light';\n  }\n  return colorScheme;\n};\n\n/**\n * Setup color scheme handling\n *\n * @since 2.6\n * @param {string} key\n * @param {any} data\n * @return void\n */\nconst init = () => {\n  setBodyClasses(getComputedColorScheme());\n\n  // Listen for system color scheme changes\n  window.matchMedia(darkMediaQuery).addEventListener('change', systemColorSchemeChanged);\n};\n\n/**\n * Listen for changes to the operating system color scheme value.\n *\n * @since 2.6\n * @param MediaQueryListEvent e\n * @param {any} data\n * @return void\n */\nconst systemColorSchemeChanged = e => {\n  const colorScheme = FL.Builder.data?.getSystemState().colorScheme;\n  if ('auto' !== colorScheme) {\n    return;\n  }\n  setBodyClasses(e.matches ? 'dark' : 'light');\n};\n\n/**\n * Add/Remove appropriate color scheme body classes.\n *\n * @since 2.6\n * @param String name (light|dark|auto)\n * @return void\n */\nconst setBodyClasses = name => {\n  const parentClasses = window.parent.document.body.classList;\n  const childClasses = document.body.classList;\n  let add = name;\n\n  // Handle 'auto' value\n  if ('auto' === name) {\n    add = getComputedColorScheme();\n  }\n  const remove = 'dark' === add ? 'light' : 'dark';\n  parentClasses.remove(`fl-builder-ui-skin--${remove}`, `fluid-color-scheme-${remove}`);\n  childClasses.remove(`fl-builder-ui-skin--${remove}`, `fluid-color-scheme-${remove}`);\n  parentClasses.add(`fl-builder-ui-skin--${add}`, `fluid-color-scheme-${add}`);\n  childClasses.add(`fl-builder-ui-skin--${add}`, `fluid-color-scheme-${add}`);\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/color-scheme/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/color/index.js":
/*!*******************************************!*\
  !*** ./src/FL/Builder/api/color/index.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   rgbToHSL: () => (/* binding */ rgbToHSL)\n/* harmony export */ });\nconst rgbToHSL = (r, g, b) => {\n  // Make r, g, and b fractions of 1\n  r /= 255;\n  g /= 255;\n  b /= 255;\n\n  // Find greatest and smallest channel values\n  let cmin = Math.min(r, g, b);\n  let cmax = Math.max(r, g, b);\n  let delta = cmax - cmin;\n  let h = 0;\n  let s = 0;\n  let l = 0;\n\n  // Calculate hue\n  if (0 == delta) {\n    // No difference\n    h = 0;\n  } else if (cmax == r) {\n    // Red is max\n    h = (g - b) / delta % 6;\n  } else if (cmax == g) {\n    // Green is max\n    h = (b - r) / delta + 2;\n  } else {\n    // Blue is max\n    h = (r - g) / delta + 4;\n  }\n  h = Math.round(h * 60);\n\n  // Make negative hues positive behind 360°\n  if (0 > h) {\n    h += 360;\n  }\n\n  // Calculate lightness\n  l = (cmax + cmin) / 2;\n\n  // Calculate saturation\n  s = 0 == delta ? 0 : delta / (1 - Math.abs(2 * l - 1));\n\n  // Multiply l and s by 100\n  s = +(s * 100).toFixed(1);\n  l = +(l * 100).toFixed(1);\n  return [h, s, l];\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/color/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/conditionals/index.js":
/*!**************************************************!*\
  !*** ./src/FL/Builder/api/conditionals/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   isBlockEditor: () => (/* binding */ isBlockEditor),\n/* harmony export */   isBoolean: () => (/* binding */ isBoolean),\n/* harmony export */   isUndefined: () => (/* binding */ isUndefined)\n/* harmony export */ });\n/**\n * Are we in the block editor?\n * Replaces FLBuilder.isBlockEditor\n *\n * @since 2.9\n * @return {bool}\n */\nconst isBlockEditor = () => 'undefined' !== typeof FLBuilderModuleBlocksConfig;\n\n/**\n * Helper taken from lodash\n * Replaces FLBuilder.isUndefined\n * @since 2.2.2\n */\nconst isUndefined = obj => obj === void 0;\n\n/**\n * Helper taken from lodash\n * Replaces FLBuilder.isBoolean\n * @since 2.2.2\n */\nconst isBoolean = value => true === value || false === value;\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/conditionals/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/css/index.js":
/*!*****************************************!*\
  !*** ./src/FL/Builder/api/css/index.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   getFormattedSelector: () => (/* binding */ getFormattedSelector)\n/* harmony export */ });\nconst getFormattedSelector = (prefix, selector = '', nodeId) => {\n  let formatted = '';\n  const pattern = /,(?![^()]*\\))/;\n  const parts = selector.split(pattern);\n  let i = 0;\n  for (; i < parts.length; i++) {\n    if (-1 < parts[i].indexOf('{node}')) {\n      formatted += parts[i].replace('{node}', prefix);\n    } else if (-1 < parts[i].indexOf('{node_id}')) {\n      formatted += parts[i].replace(/{node_id}/g, nodeId);\n    } else {\n      formatted += prefix + ' ' + parts[i];\n    }\n    if (i != parts.length - 1) {\n      formatted += ', ';\n    }\n  }\n  return formatted;\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/css/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/hooks/index.js":
/*!*******************************************!*\
  !*** ./src/FL/Builder/api/hooks/index.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   add: () => (/* binding */ add),\n/* harmony export */   remove: () => (/* binding */ remove),\n/* harmony export */   trigger: () => (/* binding */ trigger)\n/* harmony export */ });\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ \"jquery\");\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);\n\n\n/**\n * Trigger a hook.\n *\n * @since 1.8\n * @method triggerHook\n * @param {String} hook The hook to trigger.\n * @param {Array} args An array of args to pass to the hook.\n * @return void\n */\nconst trigger = (hook, args) => {\n  jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').trigger('fl-builder.' + hook, args);\n};\n\n/**\n * Add a hook.\n *\n * @since 1.8\n * @method addHook\n * @param {String} hook The hook to add.\n * @param {Function} callback A function to call when the hook is triggered.\n * @return {Function} a removeHook callback\n */\nconst add = (hook, callback) => {\n  jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').on('fl-builder.' + hook, callback);\n  return () => remove(hook, callback);\n};\n\n/**\n * Remove a hook.\n *\n * @since 1.8\n * @method removeHook\n * @param {String} hook The hook to remove.\n * @param {Function} callback The callback function to remove.\n */\nconst remove = (hook, callback) => {\n  jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').off('fl-builder.' + hook, callback);\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/hooks/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/index.js":
/*!*************************************!*\
  !*** ./src/FL/Builder/api/index.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _arrays__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrays */ \"./src/FL/Builder/api/arrays/index.js\");\n/* harmony import */ var _color__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./color */ \"./src/FL/Builder/api/color/index.js\");\n/* harmony import */ var _color_scheme__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./color-scheme */ \"./src/FL/Builder/api/color-scheme/index.js\");\n/* harmony import */ var _conditionals__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./conditionals */ \"./src/FL/Builder/api/conditionals/index.js\");\n/* harmony import */ var _css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./css */ \"./src/FL/Builder/api/css/index.js\");\n/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./hooks */ \"./src/FL/Builder/api/hooks/index.js\");\n/* harmony import */ var _objects__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./objects */ \"./src/FL/Builder/api/objects/index.js\");\n\n\n\n\n\n\n\nconst utils = {\n  arrays: _arrays__WEBPACK_IMPORTED_MODULE_0__,\n  color: _color__WEBPACK_IMPORTED_MODULE_1__,\n  colorScheme: _color_scheme__WEBPACK_IMPORTED_MODULE_2__,\n  css: _css__WEBPACK_IMPORTED_MODULE_4__,\n  hooks: _hooks__WEBPACK_IMPORTED_MODULE_5__,\n  objects: _objects__WEBPACK_IMPORTED_MODULE_6__,\n  ..._conditionals__WEBPACK_IMPORTED_MODULE_3__\n};\nwindow.FL = window.FL ?? {};\nconst privateAPI = {\n  utils\n};\nconst publicAPI = {};\nFL.Builder = Object.setPrototypeOf(publicAPI, privateAPI);\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/api/objects/index.js":
/*!*********************************************!*\
  !*** ./src/FL/Builder/api/objects/index.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   forEach: () => (/* binding */ forEach),\n/* harmony export */   objectMap: () => (/* binding */ objectMap)\n/* harmony export */ });\n/**\n * Loop over an object's keys\n *\n * @param Object\n * @param Function - callback to run on each key/value pair\n * @return Array - array of all results from the callback function\n */\nconst forEach = (obj = {}, callback = () => {}) => {\n  return Object.entries(obj).map(([key, value], i) => {\n    return callback(key, value, i);\n  });\n};\n\n/**\n * Loop over an object's keys and perform a callback on each value\n */\nconst objectMap = (obj = {}, callback = () => {}) => {\n  let newObj = {};\n  for (let key in obj) {\n    newObj[key] = callback(key, obj[key]);\n  }\n  return newObj;\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/api/objects/index.js?\n}");

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ ((module) => {

module.exports = jQuery;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/FL/Builder/api/index.js");
/******/ 	
/******/ })()
;