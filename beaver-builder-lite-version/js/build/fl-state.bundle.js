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

/***/ "./node_modules/camelcase/index.js":
/*!*****************************************!*\
  !*** ./node_modules/camelcase/index.js ***!
  \*****************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ camelCase)\n/* harmony export */ });\nconst UPPERCASE = /[\\p{Lu}]/u;\nconst LOWERCASE = /[\\p{Ll}]/u;\nconst LEADING_CAPITAL = /^[\\p{Lu}](?![\\p{Lu}])/gu;\nconst IDENTIFIER = /([\\p{Alpha}\\p{N}_]|$)/u;\nconst SEPARATORS = /[_.\\- ]+/;\n\nconst LEADING_SEPARATORS = new RegExp('^' + SEPARATORS.source);\nconst SEPARATORS_AND_IDENTIFIER = new RegExp(SEPARATORS.source + IDENTIFIER.source, 'gu');\nconst NUMBERS_AND_IDENTIFIER = new RegExp('\\\\d+' + IDENTIFIER.source, 'gu');\n\nconst preserveCamelCase = (string, toLowerCase, toUpperCase, preserveConsecutiveUppercase) => {\n\tlet isLastCharLower = false;\n\tlet isLastCharUpper = false;\n\tlet isLastLastCharUpper = false;\n\tlet isLastLastCharPreserved = false;\n\n\tfor (let index = 0; index < string.length; index++) {\n\t\tconst character = string[index];\n\t\tisLastLastCharPreserved = index > 2 ? string[index - 3] === '-' : true;\n\n\t\tif (isLastCharLower && UPPERCASE.test(character)) {\n\t\t\tstring = string.slice(0, index) + '-' + string.slice(index);\n\t\t\tisLastCharLower = false;\n\t\t\tisLastLastCharUpper = isLastCharUpper;\n\t\t\tisLastCharUpper = true;\n\t\t\tindex++;\n\t\t} else if (isLastCharUpper && isLastLastCharUpper && LOWERCASE.test(character) && (!isLastLastCharPreserved || preserveConsecutiveUppercase)) {\n\t\t\tstring = string.slice(0, index - 1) + '-' + string.slice(index - 1);\n\t\t\tisLastLastCharUpper = isLastCharUpper;\n\t\t\tisLastCharUpper = false;\n\t\t\tisLastCharLower = true;\n\t\t} else {\n\t\t\tisLastCharLower = toLowerCase(character) === character && toUpperCase(character) !== character;\n\t\t\tisLastLastCharUpper = isLastCharUpper;\n\t\t\tisLastCharUpper = toUpperCase(character) === character && toLowerCase(character) !== character;\n\t\t}\n\t}\n\n\treturn string;\n};\n\nconst preserveConsecutiveUppercase = (input, toLowerCase) => {\n\tLEADING_CAPITAL.lastIndex = 0;\n\n\treturn input.replaceAll(LEADING_CAPITAL, match => toLowerCase(match));\n};\n\nconst postProcess = (input, toUpperCase) => {\n\tSEPARATORS_AND_IDENTIFIER.lastIndex = 0;\n\tNUMBERS_AND_IDENTIFIER.lastIndex = 0;\n\n\treturn input\n\t\t.replaceAll(NUMBERS_AND_IDENTIFIER, (match, pattern, offset) => ['_', '-'].includes(input.charAt(offset + match.length)) ? match : toUpperCase(match))\n\t\t.replaceAll(SEPARATORS_AND_IDENTIFIER, (_, identifier) => toUpperCase(identifier));\n};\n\nfunction camelCase(input, options) {\n\tif (!(typeof input === 'string' || Array.isArray(input))) {\n\t\tthrow new TypeError('Expected the input to be `string | string[]`');\n\t}\n\n\toptions = {\n\t\tpascalCase: false,\n\t\tpreserveConsecutiveUppercase: false,\n\t\t...options,\n\t};\n\n\tif (Array.isArray(input)) {\n\t\tinput = input.map(x => x.trim())\n\t\t\t.filter(x => x.length)\n\t\t\t.join('-');\n\t} else {\n\t\tinput = input.trim();\n\t}\n\n\tif (input.length === 0) {\n\t\treturn '';\n\t}\n\n\tconst toLowerCase = options.locale === false\n\t\t? string => string.toLowerCase()\n\t\t: string => string.toLocaleLowerCase(options.locale);\n\n\tconst toUpperCase = options.locale === false\n\t\t? string => string.toUpperCase()\n\t\t: string => string.toLocaleUpperCase(options.locale);\n\n\tif (input.length === 1) {\n\t\tif (SEPARATORS.test(input)) {\n\t\t\treturn '';\n\t\t}\n\n\t\treturn options.pascalCase ? toUpperCase(input) : toLowerCase(input);\n\t}\n\n\tconst hasUpperCase = input !== toLowerCase(input);\n\n\tif (hasUpperCase) {\n\t\tinput = preserveCamelCase(input, toLowerCase, toUpperCase, options.preserveConsecutiveUppercase);\n\t}\n\n\tinput = input.replace(LEADING_SEPARATORS, '');\n\tinput = options.preserveConsecutiveUppercase ? preserveConsecutiveUppercase(input, toLowerCase) : toLowerCase(input);\n\n\tif (options.pascalCase) {\n\t\tinput = toUpperCase(input.charAt(0)) + input.slice(1);\n\t}\n\n\treturn postProcess(input, toUpperCase);\n}\n\n\n//# sourceURL=webpack://bb-plugin/./node_modules/camelcase/index.js?\n}");

/***/ }),

/***/ "./src/FL/state/index.js":
/*!*******************************!*\
  !*** ./src/FL/state/index.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _registry__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./registry */ \"./src/FL/state/registry/index.js\");\n\nwindow.FL = window.FL ?? {};\nFL.state = {\n  createStoreRegistry: _registry__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/index.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/actions.js":
/*!******************************************!*\
  !*** ./src/FL/state/registry/actions.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   createActions: () => (/* binding */ createActions)\n/* harmony export */ });\n/* harmony import */ var camelcase__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! camelcase */ \"./node_modules/camelcase/index.js\");\n\n\n/**\n * Creates default actions for state keys that do not\n * have a reducer.\n */\nconst createActions = (actions, reducers, state) => {\n  Object.entries(state).map(([key]) => {\n    if (!reducers[key]) {\n      const type = `SET_${key.toUpperCase()}`;\n      const action = (0,camelcase__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(`set_${key}`);\n      actions[action] = value => ({\n        type,\n        value\n      });\n    }\n  });\n  return actions;\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/actions.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/hooks.js":
/*!****************************************!*\
  !*** ./src/FL/state/registry/hooks.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _should_update__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./should-update */ \"./src/FL/state/registry/should-update.js\");\n\n\nconst capitalize = string => string.charAt(0).toUpperCase() + string.slice(1);\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((store, actions) => {\n  const state = store.getState();\n  const hooks = {};\n  Object.keys(state).map(key => {\n    const name = `use${capitalize(key)}`;\n\n    // Create a hook function named use{KeyName}()\n    hooks[name] = (needsRender = true) => {\n      const [value, setValue] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(store.getState()[key]);\n      const prevValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(store.getState()[key]);\n      (0,react__WEBPACK_IMPORTED_MODULE_0__.useLayoutEffect)(() => {\n        // Set initial value from store - overrides default value\n        setValue(store.getState()[key]);\n        prevValue.current = store.getState()[key];\n        return store.subscribe(() => {\n          const state = store.getState();\n          if ((0,_should_update__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(needsRender, value, prevValue.current)) {\n            setValue(state[key]);\n          }\n          prevValue.current = state[key];\n        });\n      }, []);\n      const actionName = `set${capitalize(key)}`;\n      let action = actions[actionName];\n      return [value, action];\n    };\n  });\n  return hooks;\n});\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/hooks.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/index.js":
/*!****************************************!*\
  !*** ./src/FL/state/registry/index.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var redux__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! redux */ \"redux\");\n/* harmony import */ var redux__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(redux__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _should_update__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./should-update */ \"./src/FL/state/registry/should-update.js\");\n/* harmony import */ var _state__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./state */ \"./src/FL/state/registry/state.js\");\n/* harmony import */ var _actions__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./actions */ \"./src/FL/state/registry/actions.js\");\n/* harmony import */ var _reducers__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./reducers */ \"./src/FL/state/registry/reducers.js\");\n/* harmony import */ var _selectors__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./selectors */ \"./src/FL/state/registry/selectors.js\");\n/* harmony import */ var _middleware__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./middleware */ \"./src/FL/state/registry/middleware.js\");\n/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./hooks */ \"./src/FL/state/registry/hooks.js\");\n\n\n\n\n\n\n\n\n\nconst createStoreRegistry = () => {\n  /**\n   * The main registry object. Holds all stores\n   * that have been registered.\n   */\n  const registry = {};\n\n  /**\n    * Functions for working with the registry.\n    */\n  return {\n    /**\n      * Adds a new store to the registry.\n      *\n      * Actions and reducers are optional! If you do not provide\n      * an action or reducer for a state key, a setter will be\n      * generated for you.\n      */\n    registerStore: (key, {\n      state = {},\n      cache = [],\n      actions = {},\n      reducers = {},\n      selectors = {},\n      effects = {}\n    }) => {\n      if (!key) {\n        throw new Error('Missing key required for registerStore.');\n      } else if (registry[key]) {\n        throw new Error(`A store with the key '${key}' already exists.`);\n      }\n      const cachedState = (0,_state__WEBPACK_IMPORTED_MODULE_3__.createCachedState)(key, state, cache);\n      registry[key] = {\n        actions: (0,_actions__WEBPACK_IMPORTED_MODULE_4__.createActions)({\n          ...actions\n        }, reducers, cachedState),\n        store: (0,redux__WEBPACK_IMPORTED_MODULE_1__.createStore)((0,_reducers__WEBPACK_IMPORTED_MODULE_5__.createReducers)({\n          ...reducers\n        }, cachedState), cachedState, (0,_middleware__WEBPACK_IMPORTED_MODULE_7__.createEnhancers)(key, effects))\n      };\n      registry[key].selectors = (0,_selectors__WEBPACK_IMPORTED_MODULE_6__.createSelectors)({\n        ...selectors\n      }, registry[key].store);\n      (0,_state__WEBPACK_IMPORTED_MODULE_3__.setupStateCaching)(key, registry[key].store, cache);\n    },\n    /**\n     * Custom hook for subscribing local state to changes\n     * in a registry store. Returns the store's state object.\n     */\n    useStore: (key, needsRender = true) => {\n      const {\n        store\n      } = registry[key];\n      const initial = store.getState();\n      const prevState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(initial);\n      const [state, setState] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(initial);\n      (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n        setState(store.getState());\n        return store.subscribe(() => {\n          const newState = store.getState();\n          if ((0,_should_update__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(needsRender, prevState.current, newState)) {\n            setState({\n              ...newState\n            });\n          }\n          prevState.current = newState;\n        });\n      }, []);\n      return state;\n    },\n    /**\n     * Returns the main object for a store in the registry.\n     */\n    getStore: key => registry[key].store,\n    /**\n     * Returns an object with all actions bound to dispatch\n     * for a store in the registry.\n     */\n    getDispatch: key => {\n      const {\n        actions,\n        store\n      } = registry[key];\n      const dispatch = {};\n      Object.entries(actions).map(([name, callback]) => {\n        dispatch[name] = (...args) => {\n          return new Promise(resolve => {\n            const result = store.dispatch(callback(...args));\n            resolve(result);\n          });\n        };\n      });\n      return dispatch;\n    },\n    /**\n     * Returns all selectors for a store in the registry.\n     */\n    getSelectors: key => registry[key].selectors,\n    /**\n     * return all generated hooks for a store in the registry.\n     */\n    getHooks: key => {\n      const {\n        actions,\n        store\n      } = registry[key];\n      const actionCreators = (0,redux__WEBPACK_IMPORTED_MODULE_1__.bindActionCreators)(actions, store.dispatch);\n      return (0,_hooks__WEBPACK_IMPORTED_MODULE_8__[\"default\"])(store, actionCreators);\n    }\n  };\n};\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createStoreRegistry);\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/index.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/middleware.js":
/*!*********************************************!*\
  !*** ./src/FL/state/registry/middleware.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   applyEffects: () => (/* binding */ applyEffects),\n/* harmony export */   createEnhancers: () => (/* binding */ createEnhancers)\n/* harmony export */ });\n/* harmony import */ var redux__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! redux */ \"redux\");\n/* harmony import */ var redux__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(redux__WEBPACK_IMPORTED_MODULE_0__);\n\n\n/**\n * Creates all enhancers for a new store with support\n * for redux dev tools.\n */\nconst createEnhancers = (name, effects) => {\n  const devToolsCompose = 'undefined' === typeof window ? null : window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__;\n  const composeEnhancers = devToolsCompose ? devToolsCompose({\n    name\n  }) : redux__WEBPACK_IMPORTED_MODULE_0__.compose;\n  return composeEnhancers((0,redux__WEBPACK_IMPORTED_MODULE_0__.applyMiddleware)(applyEffects(effects)));\n};\n\n/**\n * Applies before and after effects to store actions.\n */\nconst applyEffects = effects => {\n  const {\n    before,\n    after\n  } = effects;\n  return store => {\n    return next => action => {\n      if (before && before[action.type]) {\n        before[action.type](action, store);\n      }\n      const result = next(action);\n      if (after && after[action.type]) {\n        after[action.type](action, store);\n      }\n      return result;\n    };\n  };\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/middleware.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/reducers.js":
/*!*******************************************!*\
  !*** ./src/FL/state/registry/reducers.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   createReducers: () => (/* binding */ createReducers)\n/* harmony export */ });\n/* harmony import */ var redux__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! redux */ \"redux\");\n/* harmony import */ var redux__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(redux__WEBPACK_IMPORTED_MODULE_0__);\n\n\n/**\n * Creates default reducers for state keys that do not\n * have a reducer. Returns all reducers using combineReducers.\n */\nconst createReducers = (reducers, state) => {\n  /**\n   * Return a simple reducer if we don't have reducers and state.\n   * If this isn't done, Redux will throw an error.\n   */\n  if (!Object.keys(reducers).length && !Object.keys(state).length) {\n    return state => state;\n  }\n  Object.entries(state).map(([key, value]) => {\n    if (!reducers[key]) {\n      reducers[key] = (state = value, action) => {\n        switch (action.type) {\n          case `SET_${key.toUpperCase()}`:\n            return action.value;\n          default:\n            return state;\n        }\n      };\n    }\n  });\n  return (0,redux__WEBPACK_IMPORTED_MODULE_0__.combineReducers)(reducers);\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/reducers.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/selectors.js":
/*!********************************************!*\
  !*** ./src/FL/state/registry/selectors.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   createSelectors: () => (/* binding */ createSelectors)\n/* harmony export */ });\n/* harmony import */ var camelcase__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! camelcase */ \"./node_modules/camelcase/index.js\");\n\n\n/**\n * Wraps all user defined selectors in a function that passes\n * in the current state as the first argument. Creates default\n * selectors for state keys without one.\n */\nconst createSelectors = (selectors, store) => {\n  const wrapped = {};\n  const state = store.getState();\n  Object.entries(state).map(([key]) => {\n    const name = (0,camelcase__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(`get_${key}`);\n    if (!selectors[name]) {\n      selectors[name] = currentState => currentState[key];\n    }\n  });\n  Object.entries(selectors).map(([key, selector]) => {\n    wrapped[key] = (...args) => selector(store.getState(), ...args);\n  });\n  return wrapped;\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/selectors.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/should-update.js":
/*!************************************************!*\
  !*** ./src/FL/state/registry/should-update.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\nconst isEqual = (a, b) => {\n  if (typeof a !== typeof b) {\n    return false;\n  }\n  if ('string' === typeof a || 'number' === typeof a) {\n    return a === b;\n  } else {\n    return JSON.stringify(a) === JSON.stringify(b);\n  }\n};\nconst shouldUpdate = (needsRender, a, b) => {\n  // Handle bool\n  if ('boolean' === typeof needsRender) {\n    return needsRender;\n  }\n\n  // Handle Function\n  if ('function' === typeof needsRender) {\n    return needsRender(a, b);\n  }\n\n  // Handle String as property key\n  if ('string' === typeof needsRender) {\n    return !isEqual(a[needsRender], b[needsRender]);\n  }\n\n  // Handle as Array of properties\n  if (Array.isArray(needsRender)) {\n    return needsRender.some(key => !isEqual(a[key], b[key]));\n  }\n  return false;\n};\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (shouldUpdate);\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/should-update.js?\n}");

/***/ }),

/***/ "./src/FL/state/registry/state.js":
/*!****************************************!*\
  !*** ./src/FL/state/registry/state.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   createCachedState: () => (/* binding */ createCachedState),\n/* harmony export */   setupStateCaching: () => (/* binding */ setupStateCaching)\n/* harmony export */ });\nconst createCachedState = (storeKey, initialState, cacheKeys) => {\n  if (cacheKeys.length && 'undefined' !== typeof localStorage) {\n    const cache = localStorage.getItem(storeKey);\n    if (cache) {\n      const parsed = JSON.parse(cache);\n      const data = {};\n      cacheKeys.map(key => {\n        if (parsed[key]) {\n          data[key] = parsed[key];\n        }\n      });\n      return {\n        ...initialState,\n        ...data\n      };\n    }\n  }\n  return initialState;\n};\nconst setupStateCaching = (storeKey, store, cacheKeys) => {\n  if (!cacheKeys.length || 'undefined' === typeof localStorage) {\n    return;\n  }\n  store.subscribe(() => {\n    const state = store.getState();\n    const cache = {};\n    cacheKeys.map(key => {\n      cache[key] = state[key];\n    });\n    localStorage.setItem(storeKey, JSON.stringify(cache));\n  });\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/state/registry/state.js?\n}");

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = React;

/***/ }),

/***/ "redux":
/*!************************!*\
  !*** external "Redux" ***!
  \************************/
/***/ ((module) => {

module.exports = Redux;

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
/******/ 	var __webpack_exports__ = __webpack_require__("./src/FL/state/index.js");
/******/ 	
/******/ })()
;