/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/canvas/api/index.js":
/*!*********************************!*\
  !*** ./src/canvas/api/index.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getActions: () => (/* binding */ getActions),
/* harmony export */   getConfig: () => (/* binding */ getConfig)
/* harmony export */ });
/* harmony import */ var _nodes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./nodes */ "./src/canvas/api/nodes.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }

var getConfig = function getConfig() {
  return window.FLBuilderCanvasConfig;
};
var getActions = function getActions() {
  return _objectSpread({}, _nodes__WEBPACK_IMPORTED_MODULE_0__);
};

/***/ }),

/***/ "./src/canvas/api/nodes.js":
/*!*********************************!*\
  !*** ./src/canvas/api/nodes.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   moveNode: () => (/* binding */ moveNode)
/* harmony export */ });
/* harmony import */ var _dom__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../dom */ "./src/canvas/dom/index.js");

var moveNode = function moveNode(id) {
  var position = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  var parent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var nodeElement = (0,_dom__WEBPACK_IMPORTED_MODULE_0__.getNodeElement)(id);
  var parentElement = null;
  var contentElement = null;
  var isColumnGroup = false;
  var isContainerModule = false;
  var previousParentElement = nodeElement.parentElement.closest('[data-node]');

  // Move within the same parent
  if (!parent) {
    parentElement = nodeElement.parentElement;
    contentElement = parentElement;
  }

  // Move to a different parent
  if (parent) {
    parentElement = (0,_dom__WEBPACK_IMPORTED_MODULE_0__.getNodeElement)(parent);
    contentElement = parentElement.querySelector('.fl-node-content');
    isColumnGroup = parentElement.classList.contains('fl-col-group');
    isContainerModule = parentElement.classList.contains('fl-module');
    if (isColumnGroup) {
      contentElement = parentElement;
    }
    if (isContainerModule && !parentElement.querySelector(':scope > .fl-node-content')) {
      contentElement = parentElement;
    }
  }

  // Only move if the element isn't already in position
  if (nodeElement !== contentElement.children[position]) {
    nodeElement.remove();
    if (position > contentElement.children.length - 1) {
      contentElement.appendChild(nodeElement);
    } else {
      contentElement.insertBefore(nodeElement, contentElement.children[position]);
    }

    // Reset col widths when reparenting to a new column group
    if (isColumnGroup && parent) {
      FLBuilder._resetColumnWidths(parentElement);
      FLBuilder._resetColumnWidths(previousParentElement);
    }
  }
  FLBuilder._highlightEmptyCols();
};

/***/ }),

/***/ "./src/canvas/dom/index.js":
/*!*********************************!*\
  !*** ./src/canvas/dom/index.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getLayoutRoot: () => (/* binding */ getLayoutRoot),
/* harmony export */   getNodeElement: () => (/* binding */ getNodeElement),
/* harmony export */   scrollToNode: () => (/* binding */ scrollToNode)
/* harmony export */ });
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../api */ "./src/canvas/api/index.js");


/**
 * Get the root layout element.
 *
 * @param string postId
 * @return HTMLElement | null
 */
var getLayoutRoot = function getLayoutRoot(postId) {
  if (!postId) {
    return null;
  }
  return document.querySelector(".fl-builder-content-".concat(postId));
};

/**
 * Get a reference to a node's dom element from an id
 *
 * @param string id
 * @return HTMLElement | null
 */
var getNodeElement = function getNodeElement(id) {
  var _getConfig = (0,_api__WEBPACK_IMPORTED_MODULE_0__.getConfig)(),
    postId = _getConfig.postId;
  var root = getLayoutRoot(postId);
  if (!root) {
    return null;
  }
  return root.querySelector("[data-node=\"".concat(id, "\"]"));
};

/**
 * Scroll the root element of a particular node onto screen if it is not.
 *
 * @param string id
 * @return void
 */
var scrollToNode = function scrollToNode(id) {
  var el = getNodeElement(id);
  if (el) {
    el.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    });
  }
};

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
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*****************************!*\
  !*** ./src/canvas/index.js ***!
  \*****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./api */ "./src/canvas/api/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }


// Setup public API - window.FL.Builder.__canvas
var api = window.FL || {};
var existing = api.Builder || {};
var Builder = _objectSpread(_objectSpread({}, existing), {}, {
  /**
   * Canvas API is what will ultimately be the FL.Builder public API __INSIDE__ the iframe canvas.
   */
  __canvas: _objectSpread({}, _api__WEBPACK_IMPORTED_MODULE_0__)
});
window.FL = _objectSpread(_objectSpread({}, api), {}, {
  Builder: Builder
});
})();

/******/ })()
;
