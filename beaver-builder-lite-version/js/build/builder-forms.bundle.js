/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/classnames/index.js":
/*!******************************************!*\
  !*** ./node_modules/classnames/index.js ***!
  \******************************************/
/***/ ((module, exports) => {

eval("{var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!\n\tCopyright (c) 2018 Jed Watson.\n\tLicensed under the MIT License (MIT), see\n\thttp://jedwatson.github.io/classnames\n*/\n/* global define */\n\n(function () {\n\t'use strict';\n\n\tvar hasOwn = {}.hasOwnProperty;\n\n\tfunction classNames () {\n\t\tvar classes = '';\n\n\t\tfor (var i = 0; i < arguments.length; i++) {\n\t\t\tvar arg = arguments[i];\n\t\t\tif (arg) {\n\t\t\t\tclasses = appendClass(classes, parseValue(arg));\n\t\t\t}\n\t\t}\n\n\t\treturn classes;\n\t}\n\n\tfunction parseValue (arg) {\n\t\tif (typeof arg === 'string' || typeof arg === 'number') {\n\t\t\treturn arg;\n\t\t}\n\n\t\tif (typeof arg !== 'object') {\n\t\t\treturn '';\n\t\t}\n\n\t\tif (Array.isArray(arg)) {\n\t\t\treturn classNames.apply(null, arg);\n\t\t}\n\n\t\tif (arg.toString !== Object.prototype.toString && !arg.toString.toString().includes('[native code]')) {\n\t\t\treturn arg.toString();\n\t\t}\n\n\t\tvar classes = '';\n\n\t\tfor (var key in arg) {\n\t\t\tif (hasOwn.call(arg, key) && arg[key]) {\n\t\t\t\tclasses = appendClass(classes, key);\n\t\t\t}\n\t\t}\n\n\t\treturn classes;\n\t}\n\n\tfunction appendClass (value, newClass) {\n\t\tif (!newClass) {\n\t\t\treturn value;\n\t\t}\n\t\n\t\tif (value) {\n\t\t\treturn value + ' ' + newClass;\n\t\t}\n\t\n\t\treturn value + newClass;\n\t}\n\n\tif ( true && module.exports) {\n\t\tclassNames.default = classNames;\n\t\tmodule.exports = classNames;\n\t} else if (true) {\n\t\t// register as 'classnames', consistent with npm package name\n\t\t!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {\n\t\t\treturn classNames;\n\t\t}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),\n\t\t__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));\n\t} else // removed by dead control flow\n{}\n}());\n\n\n//# sourceURL=webpack://bb-plugin/./node_modules/classnames/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/classes/class-setting-field.js":
/*!**********************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/classes/class-setting-field.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/**\n * Controller class for working with fields in the current form.\n */\nclass FLBuilderSettingField {\n  rootName = '';\n  form = null;\n  field = null;\n  inputs = {};\n  constructor(rootName, config = {}) {\n    this.rootName = rootName;\n    const selector = `form[data-form-id=\"${config.id}\"]`;\n    this.form = jQuery(selector).get(0);\n    this.field = this.form?.querySelector(`.fl-field#fl-field-${rootName}`);\n    if (!this.field) {\n      return;\n    }\n    this.inputs = this.getInputs(this.rootName);\n  }\n  getInputs() {\n    const name = this.rootName;\n    const modes = ['default', 'large', 'medium', 'responsive'];\n    const inputs = {};\n    if (this.field) {\n      modes.map(mode => {\n        const key = 'default' !== mode ? `${name}_${mode}` : name;\n        inputs[mode] = this.field?.querySelector(`[name=\"${key}\"]`);\n      });\n    }\n    return inputs;\n  }\n  getValues() {\n    let values = {};\n    for (const key in this.inputs) {\n      values[key] = this.inputs[key] ? this.inputs[key].value : null;\n    }\n    return values;\n  }\n  isResponsive() {\n    return !!this.field?.querySelector('.fl-field-responsive-setting');\n  }\n  getInheritedValue(mode = '') {\n    const isDefaultMode = 'default' === mode || '' === mode;\n    const values = this.getValues();\n    if (!this.isResponsive() || isDefaultMode) {\n      return values.default;\n    } else {\n      // Check for upstream values from the current breakpoint\n      // responsive -> medium -> large -> default\n\n      if ('large' === mode) {\n        if ('' !== values.default) {\n          return values.default;\n        }\n      } else if ('medium' === mode) {\n        if ('' !== values.large) {\n          return values.large;\n        } else if ('' !== values.default) {\n          return values.default;\n        }\n      } else {\n        if ('' !== values.medium) {\n          return values.medium;\n        } else if ('' !== values.large) {\n          return values.large;\n        } else if ('' !== values.default) {\n          return values.default;\n        }\n      }\n    }\n  }\n  setValue(value, mode = '') {\n    const input = this.inputs[mode];\n    if (input) {\n      this.setInputAndTrigger(input, value);\n    }\n  }\n  setSubValue(subKeys, value, mode = '') {\n    const key = 'default' !== mode && '' !== mode ? `${this.rootName}_${mode}` : this.rootName;\n    const inputs = this.field?.querySelectorAll(`[name=\"${key}${subKeys}\"]`);\n    if (inputs && 0 < inputs.length) {\n      inputs.forEach(input => {\n        this.setInputAndTrigger(input, value);\n      });\n    }\n  }\n  setInputAndTrigger(input, value) {\n    if ('radio' === input.getAttribute('type')) {\n      if (value === input.value) {\n        input.setAttribute('checked', '');\n        jQuery(input).trigger('change');\n        input.dispatchEvent(new Event('change'));\n      } else {\n        input.removeAttribute('checked');\n      }\n    } else {\n      input.value = value;\n      jQuery(input).trigger('change');\n      input.dispatchEvent(new Event('change'));\n    }\n  }\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FLBuilderSettingField);\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/classes/class-setting-field.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/classes/index.js":
/*!********************************************************!*\
  !*** ./src/FL/Builder/settings-forms/classes/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   FieldController: () => (/* reexport safe */ _class_setting_field_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _class_setting_field_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./class-setting-field.js */ \"./src/FL/Builder/settings-forms/classes/class-setting-field.js\");\n\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/classes/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/index.js":
/*!************************************************!*\
  !*** ./src/FL/Builder/settings-forms/index.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   state: () => (/* binding */ state)\n/* harmony export */ });\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! fl-controls */ \"fl-controls\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(fl_controls__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _classes__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./classes */ \"./src/FL/Builder/settings-forms/classes/index.js\");\n/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ui */ \"./src/FL/Builder/settings-forms/ui/index.js\");\n\n\n\n\n// Form State API Object\nconst state = (0,fl_controls__WEBPACK_IMPORTED_MODULE_0__.createFormStore)();\n\n/**\n * Settings Form API\n */\nObject.getPrototypeOf(FL.Builder).settingsForms = {\n  FieldController: _classes__WEBPACK_IMPORTED_MODULE_1__.FieldController,\n  state,\n  ..._ui__WEBPACK_IMPORTED_MODULE_2__\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/events/button-groups/index.js":
/*!************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/events/button-groups/index.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   init: () => (/* binding */ init)\n/* harmony export */ });\n/* harmony import */ var _forms__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../forms */ \"./src/FL/Builder/settings-forms/ui/forms/index.js\");\n\n\n/**\n * Initializes all button group fields within a settings form.\n * Replaces FLBuilder._initButtonGroupFields\n *\n * @since 2.2\n * @access private\n * @method init\n */\nconst init = scope => {\n  (0,_forms__WEBPACK_IMPORTED_MODULE_0__.ensureForm)(scope).find('.fl-button-group-field').each(initField);\n};\n\n/**\n * Initializes a button group field within a settings form.\n * Replaces FLBuilder._initButtonGroupField\n *\n * @since 2.2\n * @access private\n * @method _initButtonGroupField\n */\nconst initField = function () {\n  var wrap = jQuery(this),\n    options = wrap.find('.fl-button-group-field-option'),\n    multiple = wrap.data('multiple'),\n    min = wrap.data('min'),\n    max = wrap.data('max'),\n    input = wrap.find('input:not(.fl-preview-ignore)'),\n    allowEmpty = !!wrap.data('allowEmpty'),\n    value = function (format) {\n      var val = [];\n      options.each(function (i, option) {\n        if ('1' === jQuery(option).attr('data-selected')) {\n          val.push(jQuery(option).attr('data-value'));\n        }\n      });\n      if ('array' == format) {\n        return val;\n      }\n      return val.join(',');\n    };\n  options.on('click', function (e) {\n    e.preventDefault();\n    var option = jQuery(this),\n      length = value('array').length,\n      isSelected = '1' === option.attr('data-selected');\n    if (!allowEmpty && isSelected) {\n      return;\n    }\n    if (isSelected) {\n      if (false == min) {\n        option.attr('data-selected', '0');\n      } else {\n        if (length - 1 >= min) {\n          option.attr('data-selected', '0');\n        }\n      }\n    } else {\n      // Unset other options\n      if (true !== multiple) {\n        options.attr('data-selected', '0');\n      }\n      if (false == max) {\n        option.attr('data-selected', '1');\n      } else {\n        if (length + 1 <= max) {\n          option.attr('data-selected', '1');\n        }\n      }\n    }\n    input.val(value('')).trigger('change');\n  });\n\n  // Handle value being changed externally\n  input.on('change', function () {\n    var value = input.val().split(',');\n\n    // Unset other options\n    if (true !== multiple) {\n      options.attr('data-selected', '0');\n    }\n    jQuery.each(value, function (i, val) {\n      var option = options.filter('[data-value=\"' + val + '\"]');\n\n      // Set the matching one.\n      option.attr('data-selected', '1');\n    });\n  });\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/events/button-groups/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/events/compound-fields/index.js":
/*!**************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/events/compound-fields/index.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   init: () => (/* binding */ init)\n/* harmony export */ });\n/* harmony import */ var _forms__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../forms */ \"./src/FL/Builder/settings-forms/ui/forms/index.js\");\n\n\n/**\n * Initializes all compound fields within a settings form.\n *\n * @since 2.2\n * @access private\n * @method _initCompoundFields\n */\nconst init = scope => {\n  (0,_forms__WEBPACK_IMPORTED_MODULE_0__.ensureForm)(scope).find('.fl-compound-field').each(initField);\n};\n\n/**\n * Initializes a compound field within a settings form.\n *\n * @since 2.2\n * @access private\n * @method _initCompoundField\n */\nconst initField = function () {\n  var wrap = jQuery(this),\n    sections = wrap.find('.fl-compound-field-section'),\n    toggles = wrap.find('.fl-compound-field-section-toggle'),\n    dimensions = wrap.find('.fl-compound-field-setting').has('.fl-dimension-field-units');\n  sections.each(function () {\n    var section = jQuery(this);\n    if (!section.find('.fl-compound-field-section-toggle').length) {\n      section.addClass('fl-compound-field-section-visible');\n    }\n  });\n  toggles.on('click', function () {\n    var toggle = jQuery(this),\n      field = toggle.closest('.fl-field'),\n      section = toggle.closest('.fl-compound-field-section'),\n      className = '.' + section.attr('class').split(' ').join('.');\n    field.find(className).toggleClass('fl-compound-field-section-visible');\n  });\n\n  // Init linking for compound dimension fields.\n  dimensions.each(function () {\n    var field = jQuery(this),\n      label = field.find('.fl-compound-field-label'),\n      icon = '<i class=\"fl-dimension-field-link fl-tip dashicons dashicons-admin-links\" title=\"Link Values\"></i>';\n    if (!label.length || field.find('.fl-shadow-field').length) {\n      return;\n    }\n    label.append(icon);\n  });\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/events/compound-fields/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/events/index.js":
/*!**********************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/events/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   initEvents: () => (/* binding */ initEvents)\n/* harmony export */ });\n/* harmony import */ var _forms__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../forms */ \"./src/FL/Builder/settings-forms/ui/forms/index.js\");\n/* harmony import */ var _sections__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./sections */ \"./src/FL/Builder/settings-forms/ui/events/sections/index.js\");\n/* harmony import */ var _button_groups__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./button-groups */ \"./src/FL/Builder/settings-forms/ui/events/button-groups/index.js\");\n/* harmony import */ var _compound_fields__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./compound-fields */ \"./src/FL/Builder/settings-forms/ui/events/compound-fields/index.js\");\n\n\n\n\nconst initEvents = _form => {\n  const form = (0,_forms__WEBPACK_IMPORTED_MODULE_0__.ensureForm)(_form);\n  (0,_sections__WEBPACK_IMPORTED_MODULE_1__.init)(form);\n  (0,_button_groups__WEBPACK_IMPORTED_MODULE_2__.init)(form);\n  (0,_compound_fields__WEBPACK_IMPORTED_MODULE_3__.init)(form);\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/events/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/events/sections/index.js":
/*!*******************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/events/sections/index.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   init: () => (/* binding */ init)\n/* harmony export */ });\n/* harmony import */ var _forms__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../forms */ \"./src/FL/Builder/settings-forms/ui/forms/index.js\");\n\n\n/**\n * Setup section toggling for all sections\n * Replaces FLBuilder._initSettingsSections\n *\n * @since 2.2\n * @access private\n * @method _initSettingsSections\n * @return void\n */\nconst init = scope => {\n  (0,_forms__WEBPACK_IMPORTED_MODULE_0__.ensureForm)(scope).find('.fl-builder-settings-section').each(initSection);\n};\n\n/**\n * Setup section toggling\n * not specified as arrow function on purpose (because of $(this) )\n * Replaces FLBuilder._initSection\n *\n * @since 2.2\n * @access private\n * @method _initSection\n * @return void\n */\nconst initSection = function () {\n  const wrap = jQuery(this);\n  const form = wrap.closest('form.fl-builder-settings');\n  const tab = wrap.closest('.fl-builder-settings-tab');\n  const tabId = tab.attr('id').replace('fl-builder-settings-tab-', '');\n  const button = wrap.find('.fl-builder-settings-section-header');\n  const sectionId = wrap.attr('id').replace('fl-builder-settings-section-', '');\n  let formGroup = form.attr('data-form-group');\n  let formId = form.attr('data-form-id');\n  if ('fl-builder-settings-tab-advanced' === tab.attr('id')) {\n    formGroup = 'general';\n    formId = 'advanced';\n  }\n  const collapsed = getSectionToggleCache(formGroup, formId, tabId, sectionId);\n  if (null !== collapsed) {\n    if (collapsed) {\n      wrap.addClass('fl-builder-settings-section-collapsed');\n    } else {\n      wrap.removeClass('fl-builder-settings-section-collapsed');\n    }\n  }\n  button.on('click', function () {\n    if (wrap.hasClass('fl-builder-settings-section-collapsed')) {\n      wrap.removeClass('fl-builder-settings-section-collapsed');\n      setSectionToggleCache(formGroup, formId, tabId, sectionId, false);\n    } else {\n      wrap.addClass('fl-builder-settings-section-collapsed');\n      setSectionToggleCache(formGroup, formId, tabId, sectionId, true);\n    }\n  });\n};\nconst getSectionToggleCache = (formGroup, formId, tabId, sectionId) => {\n  let cache = localStorage.getItem('fl-builder-settings-sections');\n  if (!cache) {\n    return null;\n  } else {\n    cache = JSON.parse(cache);\n  }\n  if (cache?.[formGroup]?.[formId]?.[tabId]?.[sectionId] !== undefined) {\n    return cache[formGroup][formId][tabId][sectionId];\n  }\n  return null;\n};\nconst setSectionToggleCache = (formGroup, formId, tabId, sectionId, value) => {\n  let cache = localStorage.getItem('fl-builder-settings-sections');\n  if (!cache) {\n    cache = {};\n  } else {\n    cache = JSON.parse(cache);\n  }\n  cache[formGroup] ??= {};\n  cache[formGroup][formId] ??= {};\n  cache[formGroup][formId][tabId] ??= {};\n  cache[formGroup][formId][tabId][sectionId] = value;\n  localStorage.setItem('fl-builder-settings-sections', JSON.stringify(cache));\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/events/sections/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/field-types/children/index.js":
/*!************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/field-types/children/index.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _forms__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../forms */ \"./src/FL/Builder/settings-forms/ui/forms/index.js\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! fl-controls */ \"fl-controls\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(fl_controls__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\nconst {\n  forEach\n} = FL.Builder.utils.objects;\n\n/**\n * Children Field\n *\n * This field type represents a container node's immediate child nodes for easy manipulation.\n * This field doesn't store any state on the node.\n *\n * Goals\n * - [x] Display nodes in proper order\n * - [x] Reorder nodes in the store and layout accurately\n * - [ ] Display a node title and icon similar to the outline panel (see <Series.TitleCard title={ ... } /> )\n * - [x] Delete a node from the store and layout\n * - [x] Duplicate a node in the store and layout\n * - [x] Doubleclick an item to open its settings\n * - [ ] Create new nodes via the + button. This will need to map to accepted types for the module.\n *       (I may need to rework the series menu for this).\n * Issues\n * - [ ] Seems like it doesn't always track with node updates on the canvas. Try reordering directly.\n */\n\nconst defs = {\n  node: {\n    label: 'Child Element',\n    content: /*#__PURE__*/(0,react__WEBPACK_IMPORTED_MODULE_0__.forwardRef)(({\n      state,\n      style\n    }, ref) => {\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_2__.Series.TitleCard, {\n        ref: ref,\n        style: {\n          paddingLeft: 20,\n          ...style\n        },\n        title: state.settings.type,\n        thumbProps: {\n          style: {}\n        },\n        onDoubleClick: () => {\n          const {\n            displaySettings\n          } = FL.Builder.data.getLayoutActions();\n          displaySettings(state.node);\n        }\n      });\n    })\n  }\n};\nconst ChildrenField = /*#__PURE__*/(0,react__WEBPACK_IMPORTED_MODULE_0__.memo)(() => {\n  const {\n    config\n  } = (0,_forms__WEBPACK_IMPORTED_MODULE_1__.useSettingsForm)();\n  const {\n    id,\n    type,\n    isNode,\n    nodeId\n  } = config;\n  const {\n    getLayoutActions,\n    getLayoutHooks,\n    getSystemConfig\n  } = FL.Builder.data;\n  const {\n    deleteNode,\n    copyNode\n  } = FL.Builder.getActions();\n  const {\n    reorderChildren\n  } = getLayoutActions();\n  const {\n    useNodeChildren\n  } = getLayoutHooks();\n\n  // Map node data from layout store to Series item format\n  const items = useNodeChildren(config.nodeId).map(child => ({\n    id: child.node,\n    type: 'node',\n    state: child\n  }));\n  if (!isNode) {\n    return null;\n  }\n\n  // When series items get sorted, pass array of ids to layout store\n  const setItems = items => reorderChildren(config.nodeId, items.map(item => item.id));\n  const CustomMenu = ({\n    closeMenu\n  }) => {\n    //We need to struct a (short) list of suggested child nodes to add\n    let items = {\n      button: {\n        label: 'Button',\n        nodeType: 'button'\n      },\n      photo: {\n        label: 'Photo',\n        nodeType: 'photo'\n      }\n    };\n    if ('module' === type) {\n      const buidlerConfig = getSystemConfig();\n      const moduleType = buidlerConfig.contentItems.module.find(module => module.slug === id);\n      if ('all' === moduleType.accepts) {\n\n        // We need a suggestedChildren prop something here\n      } else {\n\n        // Use the types it lists\n        //buidlerConfig.contentItems.module.find( module => module.slug === id )\n      }\n    }\n\n    // We may want to come back and add support for Row and Column nodes here at some point\n\n    return forEach(items, (key, {\n      label,\n      nodeType\n    }) => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"li\", {\n      key: key\n    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"button\", {\n      onClick: () => {\n        console.log(`create ${nodeType} child of ${nodeId}`);\n        closeMenu();\n      }\n    }, label)));\n  };\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_2__.Series, {\n    definitions: defs,\n    items: items,\n    setItems: setItems,\n    deleteItem: deleteNode,\n    cloneItem: copyNode,\n    canReset: false,\n    customAddMenu: CustomMenu\n  }));\n}, () => true);\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  name: 'children',\n  label: 'Children',\n  content: ChildrenField\n});\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/field-types/children/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/field-types/field-editor.scss":
/*!************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/field-types/field-editor.scss ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/field-types/field-editor.scss?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/field-types/field-shadow.scss":
/*!************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/field-types/field-shadow.scss ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/field-types/field-shadow.scss?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/field-types/field-time.scss":
/*!**********************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/field-types/field-time.scss ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/field-types/field-time.scss?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/field-types/field-typography.scss":
/*!****************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/field-types/field-typography.scss ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/field-types/field-typography.scss?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/field-types/index.js":
/*!***************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/field-types/index.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   canDeferField: () => (/* binding */ canDeferField)\n/* harmony export */ });\n/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/hooks */ \"@wordpress/hooks\");\n/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! fl-controls */ \"fl-controls\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(fl_controls__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _field_editor_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./field-editor.scss */ \"./src/FL/Builder/settings-forms/ui/field-types/field-editor.scss\");\n/* harmony import */ var _field_shadow_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./field-shadow.scss */ \"./src/FL/Builder/settings-forms/ui/field-types/field-shadow.scss\");\n/* harmony import */ var _field_time_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./field-time.scss */ \"./src/FL/Builder/settings-forms/ui/field-types/field-time.scss\");\n/* harmony import */ var _field_typography_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./field-typography.scss */ \"./src/FL/Builder/settings-forms/ui/field-types/field-typography.scss\");\n/* harmony import */ var _children__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./children */ \"./src/FL/Builder/settings-forms/ui/field-types/children/index.js\");\n\n\n\n// Include CSS for wp.template field types\n\n\n\n\n\n// Combine field types from fl-controls with any local components\n\nconst _fieldTypes = {\n  ...fl_controls__WEBPACK_IMPORTED_MODULE_1__.fieldTypes,\n  children: _children__WEBPACK_IMPORTED_MODULE_6__[\"default\"]\n};\n\n/**\n * Return a component for field types that we have controls for.\n */\n(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('fl_builder_settings_field_type', 'fl-builder', (Component, type) => {\n  // Publicly Available Field Types\n  if (Object.keys(_fieldTypes).includes(type)) {\n    return _fieldTypes[type].content;\n  }\n  return Component;\n});\n\n/**\n * Can a field be rendered via react?\n */\nconst canDeferField = field => Object.keys(_fieldTypes).includes(field.type);\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/field-types/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/context/index.js":
/*!*****************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/context/index.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   SettingsFormContext: () => (/* binding */ SettingsFormContext),\n/* harmony export */   defaultSettingsFormContext: () => (/* binding */ defaultSettingsFormContext),\n/* harmony export */   useSettingsForm: () => (/* binding */ useSettingsForm)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n\nconst defaultSettingsFormContext = {};\nconst SettingsFormContext = /*#__PURE__*/(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(defaultSettingsFormContext);\nconst useSettingsForm = () => (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(SettingsFormContext);\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/context/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/form-content/compound-field-controls/index.js":
/*!**********************************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/form-content/compound-field-controls/index.js ***!
  \**********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   CompoundFieldColorPicker: () => (/* binding */ CompoundFieldColorPicker)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! fl-controls */ \"fl-controls\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(fl_controls__WEBPACK_IMPORTED_MODULE_1__);\n\n\nconst CompoundFieldColorPicker = ({\n  name,\n  value,\n  setValue,\n  showAlpha,\n  showReset,\n  inputElement,\n  supportsConnections = false\n}) => {\n  const picker = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)();\n  const processValue = value => {\n    // if its a hex without the # add it\n    if (!CSS.supports('color', value) && CSS.supports('color', '#' + value)) {\n      return '#' + value;\n    }\n    return value;\n  };\n  const _value = processValue(value);\n  const onConnect = ({\n    uid,\n    isGlobalColor\n  }) => {\n    if (undefined !== FLThemeBuilderFieldConnections) {\n      const property = isGlobalColor ? 'global_color_' + uid : 'theme_color_' + uid;\n      const label = FLBuilderConfig.globalColorLabels[property];\n      FLThemeBuilderFieldConnections._connectField(jQuery(inputElement), label, {\n        property: property,\n        object: 'site',\n        field: name,\n        settings: null\n      });\n      picker.current.close();\n    }\n  };\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    const handler = function (e, data) {\n      if (data?.source === 'removeConnection') {\n        setValue(this.value);\n      }\n    };\n    const $el = jQuery(inputElement);\n    $el.on('change', handler);\n    return () => $el.off('change', handler);\n  }, [inputElement, setValue]);\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_1__.Color.Picker, {\n    ref: picker,\n    value: _value,\n    onChange: setValue,\n    showAlpha: showAlpha,\n    showReset: showReset,\n    buttonProps: {\n      style: {\n        flexGrow: 1\n      }\n    },\n    onConnect: supportsConnections ? onConnect : null\n  });\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/form-content/compound-field-controls/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/form-content/field.js":
/*!**********************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/form-content/field.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   Fields: () => (/* binding */ Fields)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ \"react-dom\");\n/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var ui_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ui/i18n */ \"./src/FL/Builder/system/ui/i18n/index.js\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! fl-controls */ \"fl-controls\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(fl_controls__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var _compound_field_controls__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./compound-field-controls */ \"./src/FL/Builder/settings-forms/ui/forms/form-content/compound-field-controls/index.js\");\n/* harmony import */ var _field_types__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../../field-types */ \"./src/FL/Builder/settings-forms/ui/field-types/index.js\");\n/* harmony import */ var _context__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../context */ \"./src/FL/Builder/settings-forms/ui/forms/context/index.js\");\nfunction _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }\n\n\n\n\n\n\n\nconst {\n  forEach\n} = FL.Builder.utils.objects;\n\n/**\n *\n * Renders a Toggle Icon for Dynamic Field selection.\n *\n */\nconst DynamicFieldIcons = ({\n  fieldData\n}) => {\n  const {\n    config\n  } = (0,_context__WEBPACK_IMPORTED_MODULE_6__.useSettingsForm)();\n  let dynamicFields = [];\n  let dynamicFieldIcons = '';\n  let icon = null;\n  let dynamicEditingTitle = FLBuilderStrings.enableComponentEditing;\n  if (config.global && config.dynamicEditing) {\n    dynamicFieldIcons = 'dashicons-admin-plugins';\n    if ('object' === typeof config.settings.dynamic_fields && config.settings.dynamic_fields?.fields) {\n      dynamicFields = config.settings.dynamic_fields.fields;\n      if (dynamicFields.includes(fieldData.rootName)) {\n        dynamicFieldIcons += ' fl-dynamic-node-field-enabled';\n        dynamicEditingTitle = FLBuilderStrings.disableComponentEditing;\n      }\n    }\n    if ('form' === fieldData.field.type) {\n      dynamicFieldIcons = '';\n      dynamicEditingTitle = null;\n    }\n    icon = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", {\n      className: `fl-dynamic-node-field dashicons fl-tip ${dynamicFieldIcons}`,\n      title: dynamicEditingTitle,\n      \"data-target-field\": fieldData.name,\n      \"data-target-field-type\": fieldData.field.type\n    });\n  }\n  return icon;\n};\n\n/**\n * Render fields for a section.\n * Matches logic in FLBuilderSettingsForms.renderFields()\n */\nconst Fields = ({\n  renderMode,\n  fields,\n  tabId,\n  sectionId,\n  filterFieldData = data => data\n}) => {\n  const {\n    uuid,\n    getFieldElement,\n    getFormElement,\n    settings,\n    setSetting,\n    config\n  } = (0,_context__WEBPACK_IMPORTED_MODULE_6__.useSettingsForm)();\n  const {\n    responsiveFields,\n    global: globalSettings\n  } = FLBuilderConfig;\n  return forEach(fields, (name, field) => {\n    if (!field || !field.type) {\n      return null;\n    }\n\n    // Is this a repeater field\n    const isMultiple = !!field.multiple;\n\n    // Responsive Setting Support\n    const supportsResponsive = responsiveFields.includes(field.type);\n    let responsive = null;\n    if (field.responsive && globalSettings.responsive_enabled && !isMultiple && supportsResponsive) {\n      responsive = field.responsive;\n    }\n\n    // Data matches fl-builder-settings-row template\n    let data = filterFieldData({\n      tabId,\n      sectionId,\n      field,\n      name,\n      rootName: name,\n      value: undefined !== settings[name] ? settings[name] : field.default,\n      preview: field.preview ? field.preview : {\n        type: 'refresh'\n      },\n      responsive,\n      rowClass: field.row_class ? ' ' + field.row_class : '',\n      isMultiple,\n      supportsMultiple: 'editor' !== field.type && 'service' !== field.type,\n      settings,\n      globalSettings,\n      node: {\n        type: config.id\n      },\n      setSetting,\n      setValue: newValue => setSetting(name, newValue)\n    });\n\n    // Things that really shouldn't be filtered\n    data = {\n      ...data,\n      getFieldElement,\n      getFormElement,\n      devices: ['default', 'large', 'medium', 'responsive']\n    };\n    const key = uuid + tabId + sectionId + name;\n    if (!(0,_field_types__WEBPACK_IMPORTED_MODULE_5__.canDeferField)(data.field, data)) {\n      const compoundFields = ['typography', 'border', 'gradient', 'fl-price-feature', 'global-color'];\n      if (compoundFields.includes(field.type)) {\n        return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(WPTemplateField, _extends({\n          key: key\n        }, data));\n      }\n      return null;\n    }\n    if ('portals' === renderMode) {\n      const dom = getFieldElement(name);\n      if (!dom) {\n        return null;\n      }\n      return /*#__PURE__*/(0,react_dom__WEBPACK_IMPORTED_MODULE_1__.createPortal)(/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(FieldRow, data), dom, key);\n    } else {\n      /**\n       * Allow for custom tabs and sections to render fields via react without portals\n       */\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(FieldRow, data);\n    }\n  });\n};\n\n/**\n * Render a single row in the fields table\n * Matches fl-builder-settings-row template except that we're mounting a portal\n * onto either the <tbody> or <tr> element that's already been rendered.\n */\nconst FieldRow = data => {\n  const {\n    addField\n  } = FLBuilderStrings;\n  if (data.isMultiple && data.supportsMultiple) {\n    let origValues = data.value;\n    let values = origValues;\n    if (undefined === origValues.length) {\n      let tempValues = [];\n      for (let index in origValues) {\n        tempValues.push(origValues[index]);\n      }\n      values = tempValues;\n    }\n    data.name += '[]';\n    return values.map((value, i) => {\n      data.index = i;\n      data.value = value;\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_3__.Error.Boundary, {\n        key: i\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"tr\", {\n        className: \"fl-builder-field-multiple\",\n        \"data-field\": data.rootName\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Field, data), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", {\n        className: \"fl-builder-field-actions\"\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", {\n        className: \"fl-builder-field-move fas fa-arrows-alt\"\n      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", {\n        className: \"fl-builder-field-copy far fa-copy\"\n      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", {\n        className: \"fl-builder-field-delete fas fa-times\"\n      }))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"tr\", null, !data.field.label && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", {\n        colSpan: \"2\"\n      }), data.field.label && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", null, \"\\xA0\"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"a\", {\n        onClick: e => e.preventDefault(),\n        className: \"fl-builder-field-add fl-builder-button\",\n        \"data-field\": data.rootName\n      }, addField.replace('%s', data.field.label))))));\n    });\n  } else {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_3__.Error.Boundary, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Field, data));\n  }\n};\n\n/**\n * Matches logic for fl-builder-field template\n */\nconst Field = data => {\n  const FieldType = wp.hooks.applyFilters('fl_builder_settings_field_type', () => {}, data.field.type, data);\n  const showDynamicEditingIcon = ['color', 'background'].includes(data.field.type);\n  const responsiveToggle = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"i\", {\n    className: \"fl-field-responsive-toggle dashicons dashicons-desktop\",\n    \"data-mode\": \"default\"\n  });\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, data.field.label && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"th\", {\n    className: \"fl-field-label\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"label\", {\n    htmlFor: data.name\n  }, 'button' === data.field.type ? '\\u00A0' : data.field.label, showDynamicEditingIcon && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(DynamicFieldIcons, {\n    fieldData: data\n  }), undefined !== data.index && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"fl-builder-field-index\"\n  }, parseInt(data.index) + 1), data.responsive && responsiveToggle, data.field.help && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"fl-help-tooltip\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"fl-help-tooltip-icon\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"svg\", {\n    width: \"12\",\n    height: \"12\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"use\", {\n    href: \"#fl-question-mark\"\n  }))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"fl-help-tooltip-text\"\n  }, data.field.help)))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", {\n    className: \"fl-field-control\",\n    colSpan: !data.field.label ? 2 : null\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"fl-field-control-wrapper\"\n  }, data.responsive && responsiveToggle, data.devices.map(device => {\n    // For non-responsive fields we only want to render the default state\n    if ('default' !== device && !data.responsive) {\n      return null;\n    }\n    if (data.responsive) {\n      // Ensure new object\n      let _data = {\n        ...data\n      };\n      _data.name = 'default' === device ? _data.rootName : _data.rootName + '_' + device;\n      _data.value = _data.settings[_data.name] ? _data.settings[_data.name] : '';\n      _data.setValue = value => _data.setSetting(_data.name, value);\n      if ('object' === typeof _data.responsive) {\n        for (let key in _data.responsive) {\n          if ('object' === typeof _data.responsive[key] && undefined !== _data.responsive[key][device]) {\n            _data.field[key] = _data.responsive[key][device];\n          }\n        }\n      }\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n        key: device,\n        className: `fl-field-responsive-setting fl-field-responsive-setting-${device}`,\n        \"data-device\": device\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_3__.Error.Boundary, {\n        alternate: FieldError\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(FieldType, _data)));\n    } else {\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_3__.Error.Boundary, {\n        key: device,\n        alternate: FieldError\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(FieldType, data));\n    }\n  }), data.field.description && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"fl-field-description\"\n  }, data.field.description))));\n};\nconst FieldError = ({\n  ...rest\n}) => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_3__.Error.DefaultError, _extends({\n  title: (0,ui_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Field Type Error', 'fl-builder'),\n  style: {\n    boxShadow: '0 0 0 1px #ffc5c5',\n    background: 'rgb(255 243 243)',\n    color: '#cd0000',\n    padding: 16,\n    borderRadius: 'var(--fl-builder-radius)'\n  }\n}, rest));\nconst WPTemplateField = props => {\n  const {\n    field,\n    name: rootName,\n    getFieldElement,\n    devices,\n    responsive,\n    settings,\n    setSetting\n  } = props;\n  const fieldRoot = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);\n  fieldRoot.current = getFieldElement(rootName);\n\n  /**\n   * Special handling for color pickers within compound fields\n   */\n  const compoundColorTypes = {\n    'typography': {\n      ['[][text_shadow][][color]']: {\n        showAlpha: true,\n        showReset: true,\n        getValue: setting => setting.text_shadow?.color,\n        mapValue: (value, setting) => ({\n          ...setting,\n          ['text_shadow']: {\n            ...setting.text_shadow,\n            color: value\n          }\n        })\n      }\n    },\n    'border': {\n      ['[][color]']: {\n        showAlpha: true,\n        showReset: true,\n        getValue: setting => setting.color,\n        mapValue: (value, setting) => ({\n          ...setting,\n          color: value\n        })\n      },\n      ['[][shadow][][color]']: {\n        showAlpha: true,\n        showReset: true,\n        getValue: setting => setting.shadow?.color,\n        mapValue: (value, setting) => ({\n          ...setting,\n          shadow: {\n            ...setting.shadow,\n            color: value\n          }\n        })\n      }\n    },\n    'gradient': {\n      ['[][colors][0]']: {\n        showAlpha: true,\n        showReset: true,\n        getValue: setting => setting.colors?.[0] ? setting.colors?.[0] : '',\n        mapValue: (value, setting) => ({\n          ...setting,\n          colors: [value, setting.colors?.[1]]\n        })\n      },\n      ['[][colors][1]']: {\n        showAlpha: true,\n        showReset: true,\n        getValue: setting => setting.colors?.[1] ? setting.colors?.[1] : '',\n        mapValue: (value, setting) => ({\n          ...setting,\n          colors: [setting.colors?.[0], value]\n        })\n      }\n    },\n    'fl-price-feature': {},\n    'global-color': {}\n  };\n  if (Object.keys(compoundColorTypes).includes(field.type)) {\n    return devices.map(device => {\n      if ('default' !== device && !responsive) {\n        return null;\n      }\n      const name = 'default' === device ? rootName : `${rootName}_${device}`;\n      const fieldValue = settings[name] ? settings[name] : '';\n      let subPickerConfigs = compoundColorTypes[field.type];\n\n      // Special handling for repeater fields\n      const repeaters = ['fl-price-feature', 'global-color'];\n      if (repeaters.includes(field.type)) {\n        const items = Array.from(fieldRoot.current?.children ?? []);\n\n        // Loop over Rows\n        items.map((item, i) => {\n          if (item.matches('.fl-builder-field-multiple')) {\n            // Find the input for the existing color picker\n            const input = item.querySelector('input.fl-color-picker-value');\n            if (input) {\n              const inputName = input.getAttribute('name');\n              const extension = inputName.replace(name, '');\n              const key = 'fl-price-feature' === field.type ? 'icon_color' : 'color';\n\n              // Create a config object\n              subPickerConfigs[extension] = {\n                showAlpha: true,\n                showReset: true,\n                getValue: setting => setting[i][key],\n                mapValue: (value, _setting) => {\n                  let setting = _setting;\n\n                  // Check if its an object rather than an array\n                  if (!Array.isArray(setting)) {\n                    setting = Object.keys(_setting).map(i => _setting[i]);\n                  }\n                  return setting.toSpliced(i, 1, {\n                    ...setting[i],\n                    [key]: value\n                  });\n                }\n              };\n            }\n          }\n        });\n      }\n      return Object.entries(subPickerConfigs).map(([nameExtension, config]) => {\n        const {\n          showAlpha,\n          showReset,\n          getValue,\n          mapValue\n        } = config;\n        const input = fieldRoot.current?.querySelector(`input[name=\"${name + nameExtension}\"]`);\n        if (!input) {\n          return;\n        }\n        const wrap = input.parentElement;\n        const mount = wrap.querySelector('.picker-mount');\n        if (mount) {\n          return /*#__PURE__*/(0,react_dom__WEBPACK_IMPORTED_MODULE_1__.createPortal)(/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_compound_field_controls__WEBPACK_IMPORTED_MODULE_4__.CompoundFieldColorPicker, {\n            key: name + nameExtension,\n            value: getValue(fieldValue),\n            setValue: pickerValue => {\n              setSetting(name, mapValue(pickerValue, fieldValue));\n              input.value = pickerValue;\n              jQuery(input).trigger('change');\n            },\n            showAlpha: showAlpha,\n            showReset: showReset,\n            name: name,\n            inputElement: input,\n            supportsConnections: 'global-color' !== field.type\n          }), mount, name + nameExtension);\n        }\n      });\n    });\n  }\n\n  // All other field types do nothing\n  return null;\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/form-content/field.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/form-content/index.js":
/*!**********************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/form-content/index.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   Fields: () => (/* reexport safe */ _field__WEBPACK_IMPORTED_MODULE_6__.Fields),\n/* harmony export */   FormContents: () => (/* binding */ FormContents),\n/* harmony export */   canDeferTab: () => (/* binding */ canDeferTab)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ \"react-dom\");\n/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/hooks */ \"@wordpress/hooks\");\n/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! fl-controls */ \"fl-controls\");\n/* harmony import */ var fl_controls__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(fl_controls__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var _context__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../context */ \"./src/FL/Builder/settings-forms/ui/forms/context/index.js\");\n/* harmony import */ var _tab__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./tab */ \"./src/FL/Builder/settings-forms/ui/forms/form-content/tab.js\");\n/* harmony import */ var _field__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./field */ \"./src/FL/Builder/settings-forms/ui/forms/form-content/field.js\");\nfunction _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }\n\n\n\n\n\n\n\n\nconst {\n  forEach\n} = FL.Builder.utils.objects;\nconst reactBasedTabs = ['auto_style'];\nconst canDeferTab = tabId => {\n  const canDefer = reactBasedTabs.includes(tabId);\n  return (0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.applyFilters)('fl_builder_settings_can_defer_tab_render', canDefer, tabId);\n};\nconst FormContents = () => {\n  const {\n    config,\n    getTabElement\n  } = (0,_context__WEBPACK_IMPORTED_MODULE_4__.useSettingsForm)();\n\n  // Tabs\n  return forEach(config.tabs, (tabId, tab) => {\n    if (canDeferTab(tabId)) {\n      // Render react-based dom for this tab\n      const Tab = (0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.applyFilters)('fl_builder_settings_tab_component', _tab__WEBPACK_IMPORTED_MODULE_5__.TabContent, tabId, tab, config);\n      const dom = getTabElement(tabId);\n      if (!dom) {\n        console.warn('Could not find dom element for Tab', tabId);\n        return null;\n      }\n\n      /**\n       * Portal onto the root tab div and render contents\n       */\n      return /*#__PURE__*/(0,react_dom__WEBPACK_IMPORTED_MODULE_1__.createPortal)(/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_controls__WEBPACK_IMPORTED_MODULE_3__.Error.Boundary, {\n        key: tabId\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Tab, _extends({\n        tabId: tabId\n      }, tab))), dom);\n    } else {\n      // Render Virtual Tab\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(VirtualTab, _extends({\n        key: tabId,\n        tabId: tabId\n      }, tab));\n    }\n  });\n};\nconst VirtualTab = ({\n  tabId,\n  sections = {}\n}) => {\n  return forEach(sections, (sectionId, section) => {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(VirtualSection, _extends({\n      key: tabId + sectionId,\n      tabId: tabId,\n      sectionId: sectionId\n    }, section));\n  });\n};\nconst VirtualSection = ({\n  tabId,\n  sectionId,\n  fields\n}) => {\n  const {\n    uuid\n  } = (0,_context__WEBPACK_IMPORTED_MODULE_4__.useSettingsForm)();\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_field__WEBPACK_IMPORTED_MODULE_6__.Fields, {\n    key: uuid + tabId + sectionId,\n    renderMode: \"portals\",\n    fields: fields,\n    tabId: tabId,\n    sectionId: sectionId\n  });\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/form-content/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/form-content/sections.js":
/*!*************************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/form-content/sections.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   Section: () => (/* binding */ Section),\n/* harmony export */   Sections: () => (/* binding */ Sections)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ \"./node_modules/classnames/index.js\");\n/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var fl_symbols__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! fl-symbols */ \"fl-symbols\");\n/* harmony import */ var fl_symbols__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(fl_symbols__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var _field__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./field */ \"./src/FL/Builder/settings-forms/ui/forms/form-content/field.js\");\nfunction _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }\n\n\n\n\nconst {\n  forEach\n} = FL.Builder.utils.objects;\nconst Sections = ({\n  tabId,\n  sections,\n  filterSection = section => section,\n  sectionComponent: Component = Section,\n  filterFieldData = field => field\n}) => {\n  return forEach(sections, (sectionId, section) => {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Component, _extends({\n      tabId: tabId,\n      sectionId: sectionId,\n      filterFieldData: filterFieldData\n    }, filterSection(section, sectionId, tabId)));\n  });\n};\nconst Section = ({\n  tabId,\n  sectionId,\n  title,\n  description,\n  collapsed,\n  fields,\n  className,\n  filterFieldData = field => field\n}) => {\n  const hasTitle = undefined !== title && '' !== title;\n  let isCollapsed = undefined !== collapsed ? collapsed : false;\n  if (hasTitle && true === FLBuilderConfig.collapseSectionsDefault) {\n    isCollapsed = true;\n  }\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    id: `fl-builder-settings-section-${sectionId}`,\n    className: classnames__WEBPACK_IMPORTED_MODULE_1___default()({\n      'fl-builder-settings-section': true,\n      'fl-builder-settings-section-collapsed': isCollapsed\n    }, className)\n  }, hasTitle && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"fl-builder-settings-section-header\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"button\", {\n    className: \"fl-builder-settings-title\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_symbols__WEBPACK_IMPORTED_MODULE_2__.Caret, null), title)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"fl-builder-settings-section-content\"\n  }, description && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"p\", {\n    className: \"fl-builder-settings-description\"\n  }, description), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"table\", {\n    className: \"fl-form-table\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_field__WEBPACK_IMPORTED_MODULE_3__.Fields, {\n    tabId: tabId,\n    sectionId: sectionId,\n    fields: fields,\n    filterFieldData: filterFieldData\n  }))));\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/form-content/sections.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/form-content/tab.js":
/*!********************************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/form-content/tab.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   TabContent: () => (/* binding */ TabContent)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _sections__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./sections */ \"./src/FL/Builder/settings-forms/ui/forms/form-content/sections.js\");\n\n\n\n/**\n * Meant to render within a portal.\n * Outer wrapper element is not included because that's our mount point.\n */\nconst TabContent = ({\n  tabId,\n  description,\n  sections\n}) => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, description && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"p\", {\n  className: \"fl-builder-settings-tab-description\"\n}, description), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_sections__WEBPACK_IMPORTED_MODULE_1__.Sections, {\n  tabId: tabId,\n  sections: sections\n}));\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/form-content/tab.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/index.js":
/*!*********************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   FormsManager: () => (/* binding */ FormsManager),\n/* harmony export */   canDeferTab: () => (/* reexport safe */ _form_content__WEBPACK_IMPORTED_MODULE_4__.canDeferTab),\n/* harmony export */   ensureForm: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_5__.ensureForm),\n/* harmony export */   focusFirstSettingsControl: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_5__.focusFirstSettingsControl),\n/* harmony export */   getFormElement: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_5__.getFormElement),\n/* harmony export */   useSettingsForm: () => (/* reexport safe */ _context__WEBPACK_IMPORTED_MODULE_3__.useSettingsForm)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"react\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var fl_symbols__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! fl-symbols */ \"fl-symbols\");\n/* harmony import */ var fl_symbols__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(fl_symbols__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../ */ \"./src/FL/Builder/settings-forms/index.js\");\n/* harmony import */ var _context__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./context */ \"./src/FL/Builder/settings-forms/ui/forms/context/index.js\");\n/* harmony import */ var _form_content__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./form-content */ \"./src/FL/Builder/settings-forms/ui/forms/form-content/index.js\");\n/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./utils */ \"./src/FL/Builder/settings-forms/ui/forms/utils.js\");\n\n\n\n\n\n\n\n\n/**\n * Root component for handling react-based rendering of forms and partial tabs/sections/fields\n */\nconst FormsManager = ({\n  onSetSetting = () => {}\n}) => {\n  const [configs, setConfigs] = ___WEBPACK_IMPORTED_MODULE_2__.state.useFormState();\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), null, configs.map((config, i) => {\n    const uuid = config.nodeId ? config.nodeId + config.lightboxId : config.type + config.lightboxId;\n    const setConfig = newConfig => setConfigs(configs.toSpliced(i, 1, newConfig));\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Form, {\n      key: uuid,\n      uuid: uuid,\n      config: config,\n      settings: config.settings,\n      onSetSettings: settings => setConfig({\n        ...config,\n        settings\n      }),\n      onSetSetting: onSetSetting\n    });\n  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(fl_symbols__WEBPACK_IMPORTED_MODULE_1__.SymbolLibrary, null));\n};\nconst Form = ({\n  uuid,\n  config,\n  settings,\n  onSetSettings: setSettings = () => {},\n  onSetSetting = () => {}\n}) => {\n  const initialSettings = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(settings);\n  const _getFormElement = () => (0,_utils__WEBPACK_IMPORTED_MODULE_5__.getFormElement)(config.lightboxId).get(0);\n  const api = {\n    uuid,\n    config,\n    settings,\n    initialSettings,\n    setSettings,\n    setSetting: (key, value) => {\n      setSettings({\n        ...settings,\n        [key]: value\n      });\n      onSetSetting(key, value);\n    },\n    resetSettings: () => setSettings({\n      ...initialSettings\n    }),\n    getFormElement: _getFormElement,\n    getTabElement: name => _getFormElement()?.querySelector(`#fl-builder-settings-tab-${name}`),\n    getFieldElement: name => _getFormElement()?.querySelector(`#fl-field-${name}`)\n  };\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_context__WEBPACK_IMPORTED_MODULE_3__.SettingsFormContext.Provider, {\n    key: api.uuid,\n    value: api\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_form_content__WEBPACK_IMPORTED_MODULE_4__.FormContents, null));\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/forms/utils.js":
/*!*********************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/forms/utils.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   ensureForm: () => (/* binding */ ensureForm),\n/* harmony export */   focusFirstSettingsControl: () => (/* binding */ focusFirstSettingsControl),\n/* harmony export */   getFormElement: () => (/* binding */ getFormElement)\n/* harmony export */ });\n/**\n * Get a jQuery reference to a form element\n *\n * @param String optional id of form\n * @return {jQuery}\n */\nconst getFormElement = id => {\n  let selector = '.fl-builder-settings:visible';\n  if (id) {\n    selector = `.fl-builder-settings[data-instance-id=\"${id}\"]`;\n  }\n  return jQuery(selector, window.parent.document);\n};\nconst ensureForm = el => undefined !== el ? el : getFormElement();\n\n/**\n * Focus the first visible control in a settings panel\n *\n * @since 2.0\n */\nconst focusFirstSettingsControl = () => {\n  var form = jQuery('.fl-builder-settings:visible', window.parent.document),\n    tab = form.find('.fl-builder-settings-tab.fl-active'),\n    nodeId = form.data('node'),\n    field = tab.find('.fl-field').first(),\n    input = field.find('input:not([type=\"hidden\"]), textarea, select, button, a, .fl-editor-field').first(),\n    id = input.find('textarea.wp-editor-area').attr('id');\n\n  // Don't focus in the block editor.\n  if (FL.Builder.utils.isBlockEditor()) {\n    return;\n  }\n\n  // Don't focus fields that have an inline editor.\n  if (nodeId && jQuery('.fl-node-' + nodeId + ' .fl-inline-editor').length) {\n    return;\n  }\n  if ('undefined' !== typeof window.parent.tinyMCE && input.hasClass('fl-editor-field')) {\n    // TinyMCE fields\n    window.parent.tinyMCE.get(id).focus();\n  } else {\n    // Everybody else\n    setTimeout(function () {\n      input.trigger('focus').css('animation-name', 'fl-grab-attention');\n    }, 300);\n  }\n\n  // Grab attention\n  field.css('animation-name', 'fl-grab-attention');\n  field.on('animationend', function () {\n    field.css('animation-name', '');\n  });\n};\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/forms/utils.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/index.js":
/*!***************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/index.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   FormsManager: () => (/* reexport safe */ _forms__WEBPACK_IMPORTED_MODULE_0__.FormsManager),\n/* harmony export */   canDeferField: () => (/* reexport safe */ _field_types__WEBPACK_IMPORTED_MODULE_2__.canDeferField),\n/* harmony export */   canDeferTab: () => (/* reexport safe */ _forms__WEBPACK_IMPORTED_MODULE_0__.canDeferTab),\n/* harmony export */   focusFirstSettingsControl: () => (/* reexport safe */ _forms__WEBPACK_IMPORTED_MODULE_0__.focusFirstSettingsControl),\n/* harmony export */   getFormElement: () => (/* reexport safe */ _forms__WEBPACK_IMPORTED_MODULE_0__.getFormElement),\n/* harmony export */   initEvents: () => (/* reexport safe */ _events__WEBPACK_IMPORTED_MODULE_1__.initEvents)\n/* harmony export */ });\n/* harmony import */ var _forms__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./forms */ \"./src/FL/Builder/settings-forms/ui/forms/index.js\");\n/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./events */ \"./src/FL/Builder/settings-forms/ui/events/index.js\");\n/* harmony import */ var _field_types__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./field-types */ \"./src/FL/Builder/settings-forms/ui/field-types/index.js\");\n/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./style.scss */ \"./src/FL/Builder/settings-forms/ui/style.scss\");\n\n\n\n\n\n// Public API\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/index.js?\n}");

/***/ }),

/***/ "./src/FL/Builder/settings-forms/ui/style.scss":
/*!*****************************************************!*\
  !*** ./src/FL/Builder/settings-forms/ui/style.scss ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/settings-forms/ui/style.scss?\n}");

/***/ }),

/***/ "./src/FL/Builder/system/ui/i18n/index.js":
/*!************************************************!*\
  !*** ./src/FL/Builder/system/ui/i18n/index.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   __: () => (/* binding */ __)\n/* harmony export */ });\n/**\n * @since 2.8\n * @param {String} string\n * @return {String}\n */\nfunction __(string) {\n  if (typeof window.parent.FLBuilderStrings === 'undefined') {\n    return string;\n  }\n  var strings = window.parent.FLBuilderStrings.i18n;\n  if (typeof strings[string] !== 'undefined') {\n    return strings[string];\n  } else {\n    console.warn('No translation found for \"' + string + '\" Please add string to FLBuilderStrings.i18n object in includes/ui-js-config.php');\n    return string;\n  }\n}\n\n//# sourceURL=webpack://bb-plugin/./src/FL/Builder/system/ui/i18n/index.js?\n}");

/***/ }),

/***/ "@wordpress/hooks":
/*!***************************!*\
  !*** external "wp.hooks" ***!
  \***************************/
/***/ ((module) => {

"use strict";
module.exports = wp.hooks;

/***/ }),

/***/ "fl-controls":
/*!******************************!*\
  !*** external "FL.controls" ***!
  \******************************/
/***/ ((module) => {

"use strict";
module.exports = FL.controls;

/***/ }),

/***/ "fl-symbols":
/*!*****************************!*\
  !*** external "FL.symbols" ***!
  \*****************************/
/***/ ((module) => {

"use strict";
module.exports = FL.symbols;

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

"use strict";
module.exports = React;

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/***/ ((module) => {

"use strict";
module.exports = ReactDOM;

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
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./src/FL/Builder/settings-forms/index.js");
/******/ 	
/******/ })()
;