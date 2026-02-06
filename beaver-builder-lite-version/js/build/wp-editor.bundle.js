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

/***/ "./src/wp/wp-editor/index.js":
/*!***********************************!*\
  !*** ./src/wp/wp-editor/index.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _wordpress__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./wordpress */ \"./src/wp/wp-editor/wordpress/index.js\");\n/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./store */ \"./src/wp/wp-editor/store/index.js\");\n/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_store__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _layout_block__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./layout-block */ \"./src/wp/wp-editor/layout-block/index.js\");\n/* harmony import */ var _more_menu__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./more-menu */ \"./src/wp/wp-editor/more-menu/index.js\");\n\n\n\n\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/index.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/layout-block/edit-pre-5-3.js":
/*!*******************************************************!*\
  !*** ./src/wp/wp-editor/layout-block/edit-pre-5-3.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   LayoutBlockEditConnectedPre_5_3: () => (/* binding */ LayoutBlockEditConnectedPre_5_3)\n/* harmony export */ });\nconst {\n  builder,\n  strings,\n  urls\n} = FLBuilderConfig;\nconst {\n  rawHandler,\n  serialize\n} = wp.blocks;\nconst {\n  Button,\n  Placeholder,\n  Spinner\n} = wp.components;\nconst {\n  compose\n} = wp.compose;\nconst {\n  subscribe,\n  withDispatch,\n  withSelect\n} = wp.data;\nconst {\n  Component\n} = wp.element;\n\n/**\n * Edit component for WordPress versions before 5.3.\n */\nclass LayoutBlockEditPre_5_3 extends Component {\n  constructor() {\n    super(...arguments);\n    this.unsubscribe = subscribe(this.storeDidUpdate.bind(this));\n  }\n  storeDidUpdate() {\n    const {\n      isLaunching,\n      isSavingPost\n    } = this.props;\n    if (isLaunching && !isSavingPost) {\n      this.unsubscribe();\n      this.redirectToBuilder();\n    }\n  }\n  componentDidMount() {\n    const {\n      blockCount\n    } = this.props;\n    if (1 === blockCount) {\n      this.toggleEditor('disable');\n    }\n  }\n  componentWillUnmount() {\n    this.unsubscribe();\n    this.toggleEditor('enable');\n  }\n  render() {\n    const {\n      blockCount,\n      onReplace,\n      isLaunching\n    } = this.props;\n    let label, callback, description;\n    if (1 === blockCount) {\n      label = builder.access ? strings.launch : strings.view;\n      callback = this.launchBuilder.bind(this);\n    } else {\n      label = strings.convert;\n      callback = this.convertToBuilder.bind(this);\n    }\n    if (builder.enabled) {\n      description = strings.active;\n    } else {\n      description = strings.description;\n    }\n    if (false === builder.showui) {\n      return '';\n    }\n    return /*#__PURE__*/React.createElement(Placeholder, {\n      key: \"placeholder\",\n      instructions: description,\n      icon: \"welcome-widgets-menus\",\n      label: strings.title,\n      className: \"fl-builder-layout-launch-view\"\n    }, isLaunching && /*#__PURE__*/React.createElement(Spinner, null), !isLaunching && /*#__PURE__*/React.createElement(Button, {\n      isLarge: true,\n      isPrimary: true,\n      type: \"submit\",\n      onClick: callback\n    }, label), !isLaunching && /*#__PURE__*/React.createElement(Button, {\n      isLarge: true,\n      type: \"submit\",\n      onClick: this.convertToBlocks.bind(this)\n    }, strings.editor));\n  }\n  toggleEditor(method = 'enable') {\n    const {\n      classList\n    } = document.body;\n    const enabledClass = 'fl-builder-layout-enabled';\n    if ('enable' === method) {\n      if (classList.contains(enabledClass)) {\n        classList.remove(enabledClass);\n      }\n    } else {\n      if (!classList.contains(enabledClass)) {\n        classList.add(enabledClass);\n      }\n    }\n  }\n  redirectToBuilder() {\n    window.location.href = builder.access ? urls.edit : urls.view;\n  }\n  launchBuilder() {\n    const {\n      savePost,\n      setLaunching\n    } = this.props;\n    setLaunching(true);\n    savePost();\n  }\n  convertToBuilder() {\n    const {\n      clientId,\n      blocks,\n      setAttributes,\n      removeBlocks\n    } = this.props;\n    const content = serialize(blocks);\n    const clientIds = blocks.map(block => block.clientId).filter(id => id !== clientId);\n    setAttributes({\n      content: content.replace(/<!--(.*?)-->/g, '')\n    });\n    removeBlocks(clientIds);\n    this.launchBuilder();\n  }\n  convertToBlocks() {\n    const {\n      attributes,\n      clientId,\n      replaceBlocks,\n      onReplace\n    } = this.props;\n    if (attributes.content && !confirm(strings.warning)) {\n      return;\n    } else if (attributes.content) {\n      replaceBlocks([clientId], rawHandler({\n        HTML: attributes.content,\n        mode: 'BLOCKS'\n      }));\n    } else {\n      onReplace([]);\n    }\n  }\n}\n\n/**\n * Connect the edit component to editor data.\n */\nconst LayoutBlockEditConnectedPre_5_3 = compose(withDispatch((dispatch, ownProps) => {\n  const editor = dispatch('core/editor');\n  const builder = dispatch('fl-builder');\n  return {\n    savePost: editor.savePost,\n    removeBlocks: editor.removeBlocks,\n    replaceBlocks: editor.replaceBlocks,\n    setLaunching: builder.setLaunching\n  };\n}), withSelect(select => {\n  const editor = select('core/editor');\n  const builder = select('fl-builder');\n  return {\n    blockCount: editor.getBlockCount(),\n    blocks: editor.getBlocks(),\n    isSavingPost: editor.isSavingPost(),\n    isLaunching: builder.isLaunching()\n  };\n}))(LayoutBlockEditPre_5_3);\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/layout-block/edit-pre-5-3.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/layout-block/edit.js":
/*!***********************************************!*\
  !*** ./src/wp/wp-editor/layout-block/edit.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   LayoutBlockEditConnected: () => (/* binding */ LayoutBlockEditConnected)\n/* harmony export */ });\nconst {\n  builder,\n  strings,\n  urls\n} = FLBuilderConfig;\nconst {\n  rawHandler,\n  serialize\n} = wp.blocks;\nconst {\n  Button,\n  Placeholder,\n  Spinner\n} = wp.components;\nconst {\n  compose\n} = wp.compose;\nconst {\n  withDispatch,\n  withSelect\n} = wp.data;\nconst {\n  Component\n} = wp.element;\n\n/**\n * Edit Component\n */\nclass LayoutBlockEdit extends Component {\n  constructor() {\n    super(...arguments);\n  }\n  componentDidMount() {\n    const {\n      blockCount\n    } = this.props;\n    if (1 === blockCount) {\n      this.toggleEditor('disable');\n    }\n  }\n  componentWillUnmount() {\n    this.toggleEditor('enable');\n  }\n  render() {\n    const {\n      blockCount,\n      onReplace,\n      isLaunching\n    } = this.props;\n    let label, callback, description;\n    if (1 === blockCount) {\n      label = builder.access ? strings.launch : strings.view;\n      callback = this.launchBuilder.bind(this);\n    } else {\n      label = strings.convert;\n      callback = this.convertToBuilder.bind(this);\n    }\n    if (builder.enabled) {\n      description = strings.active;\n    } else {\n      description = strings.description;\n    }\n    if (false === builder.showui) {\n      return '';\n    }\n    return /*#__PURE__*/React.createElement(Placeholder, {\n      key: \"placeholder\",\n      instructions: description,\n      label: strings.title,\n      className: \"fl-builder-layout-launch-view\"\n    }, isLaunching && /*#__PURE__*/React.createElement(Spinner, null), !isLaunching && /*#__PURE__*/React.createElement(Button, {\n      isLarge: true,\n      isPrimary: true,\n      type: \"submit\",\n      onClick: callback\n    }, label), !isLaunching && /*#__PURE__*/React.createElement(Button, {\n      isLarge: true,\n      type: \"submit\",\n      onClick: this.convertToBlocks.bind(this)\n    }, strings.editor));\n  }\n  toggleEditor(method = 'enable') {\n    const {\n      classList\n    } = document.body;\n    const enabledClass = 'fl-builder-layout-enabled';\n    if ('enable' === method) {\n      if (classList.contains(enabledClass)) {\n        classList.remove(enabledClass);\n      }\n    } else {\n      if (!classList.contains(enabledClass)) {\n        classList.add(enabledClass);\n      }\n    }\n  }\n  launchBuilder() {\n    const {\n      savePost,\n      setLaunching\n    } = this.props;\n    setLaunching(true);\n    /**\n     * WP 6.4 will NOT save a post with no title.\n     */\n    const title = wp.data.select(\"core/editor\").getEditedPostAttribute('title');\n    if (!title) {\n      wp.data.dispatch('core/editor').editPost({\n        title: wp.i18n.__('(no title)')\n      });\n    }\n    savePost().then(() => {\n      setTimeout(function () {\n        window.top.location.href = builder.access ? urls.edit : urls.view;\n      }, 2000);\n    });\n  }\n  convertToBuilder() {\n    const {\n      clientId,\n      blocks,\n      setAttributes,\n      removeBlocks\n    } = this.props;\n    const content = serialize(blocks);\n    const clientIds = blocks.map(block => block.clientId).filter(id => id !== clientId);\n    setAttributes({\n      content: content.replace(/<!--(.*?)-->/g, '')\n    });\n    removeBlocks(clientIds);\n    this.launchBuilder();\n  }\n  convertToBlocks() {\n    const {\n      attributes,\n      clientId,\n      replaceBlocks,\n      onReplace\n    } = this.props;\n    if (attributes.content && !confirm(strings.warning)) {\n      return;\n    } else if (attributes.content) {\n      replaceBlocks([clientId], rawHandler({\n        HTML: attributes.content,\n        mode: 'BLOCKS'\n      }));\n    } else {\n      onReplace([]);\n    }\n  }\n}\n\n/**\n * Connect the edit component to editor data.\n */\nconst LayoutBlockEditConnected = compose(withDispatch((dispatch, ownProps) => {\n  const blockEditor = dispatch('core/block-editor');\n  const editor = dispatch('core/editor');\n  const builder = dispatch('fl-builder');\n  return {\n    removeBlocks: blockEditor.removeBlocks,\n    replaceBlocks: blockEditor.replaceBlocks,\n    savePost: editor.savePost,\n    setLaunching: builder.setLaunching\n  };\n}), withSelect(select => {\n  const blockEditor = select('core/block-editor');\n  const editor = select('core/editor');\n  const builder = select('fl-builder');\n  return {\n    blockCount: blockEditor.getBlockCount(),\n    blocks: blockEditor.getBlocks(),\n    isLaunching: builder.isLaunching()\n  };\n}))(LayoutBlockEdit);\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/layout-block/edit.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/layout-block/index.js":
/*!************************************************!*\
  !*** ./src/wp/wp-editor/layout-block/index.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./edit */ \"./src/wp/wp-editor/layout-block/edit.js\");\n/* harmony import */ var _edit_pre_5_3__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit-pre-5-3 */ \"./src/wp/wp-editor/layout-block/edit-pre-5-3.js\");\n/* harmony import */ var _index_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./index.scss */ \"./src/wp/wp-editor/layout-block/index.scss\");\n\n\n\nconst {\n  builder,\n  strings\n} = FLBuilderConfig;\nconst {\n  version\n} = FLBuilderConfig.wp;\nconst {\n  registerBlockType\n} = wp.blocks;\nconst {\n  RawHTML\n} = wp.element;\nconst getBlockEdit = () => {\n  if (parseFloat(version) < 5.3) {\n    return _edit_pre_5_3__WEBPACK_IMPORTED_MODULE_1__.LayoutBlockEditConnectedPre_5_3;\n  }\n  return _edit__WEBPACK_IMPORTED_MODULE_0__.LayoutBlockEditConnected;\n};\n\n/**\n * Register the block.\n */\nif (builder.access && builder.unrestricted || builder.enabled) {\n  registerBlockType('fl-builder/layout', {\n    title: strings.title,\n    description: strings.description,\n    icon: 'welcome-widgets-menus',\n    category: 'layout',\n    useOnce: true,\n    supports: {\n      customClassName: false,\n      className: false,\n      html: false\n    },\n    attributes: {\n      content: {\n        type: 'string',\n        source: 'html'\n      }\n    },\n    edit: getBlockEdit(),\n    save({\n      attributes\n    }) {\n      return /*#__PURE__*/React.createElement(RawHTML, null, attributes.content);\n    }\n  });\n}\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/layout-block/index.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/layout-block/index.scss":
/*!**************************************************!*\
  !*** ./src/wp/wp-editor/layout-block/index.scss ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/layout-block/index.scss?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/more-menu/index.js":
/*!*********************************************!*\
  !*** ./src/wp/wp-editor/more-menu/index.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _menu_item__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./menu-item */ \"./src/wp/wp-editor/more-menu/menu-item.js\");\n/* harmony import */ var _menu_item_pre_5_3__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./menu-item-pre-5-3 */ \"./src/wp/wp-editor/more-menu/menu-item-pre-5-3.js\");\n\n\nconst {\n  version\n} = FLBuilderConfig.wp;\nconst {\n  registerPlugin\n} = wp.plugins;\nconst getMenuItemComponent = () => {\n  if (parseFloat(version) < 5.3) {\n    return _menu_item_pre_5_3__WEBPACK_IMPORTED_MODULE_1__.BuilderMoreMenuItemConnectedPre_5_3;\n  }\n  return _menu_item__WEBPACK_IMPORTED_MODULE_0__.BuilderMoreMenuItemConnected;\n};\n\n/**\n * Register the builder more menu plugin.\n */\nregisterPlugin('fl-builder-plugin-sidebar', {\n  icon: 'welcome-widgets-menus',\n  render: getMenuItemComponent()\n});\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/more-menu/index.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/more-menu/menu-item-pre-5-3.js":
/*!*********************************************************!*\
  !*** ./src/wp/wp-editor/more-menu/menu-item-pre-5-3.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   BuilderMoreMenuItemConnectedPre_5_3: () => (/* binding */ BuilderMoreMenuItemConnectedPre_5_3)\n/* harmony export */ });\n/* harmony import */ var _menu_item_pre_5_3_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./menu-item-pre-5-3.scss */ \"./src/wp/wp-editor/more-menu/menu-item-pre-5-3.scss\");\n\nconst {\n  strings\n} = FLBuilderConfig;\nconst {\n  createBlock,\n  serialize\n} = wp.blocks;\nconst {\n  Button\n} = wp.components;\nconst {\n  compose\n} = wp.compose;\nconst {\n  withDispatch,\n  withSelect\n} = wp.data;\nconst {\n  PluginSidebarMoreMenuItem\n} = wp.editPost;\nconst {\n  Component\n} = wp.element;\n\n/**\n * Builder menu item for the more menu pre WordPress 5.3.\n *\n * More menu items currently only support opening a sidebar.\n * However, we need a click event. For now, that is done in a\n * hacky manner with an absolute div that contains the event.\n * This should be reworked in the future when API supports it.\n */\nclass BuilderMoreMenuItemPre_5_3 extends Component {\n  render() {\n    return /*#__PURE__*/React.createElement(PluginSidebarMoreMenuItem, null, /*#__PURE__*/React.createElement(\"div\", {\n      className: \"fl-builder-plugin-sidebar-button\",\n      onClick: this.menuItemClicked.bind(this)\n    }), this.hasBuilderBlock() ? strings.launch : strings.convert);\n  }\n  hasBuilderBlock() {\n    const {\n      blocks\n    } = this.props;\n    const builder = blocks.filter(block => 'fl-builder/layout' === block.name);\n    return !!builder.length;\n  }\n  menuItemClicked() {\n    const {\n      closeGeneralSidebar\n    } = this.props;\n    if (this.hasBuilderBlock()) {\n      this.launchBuilder();\n    } else {\n      this.convertToBuilder();\n    }\n\n    // Another hack because we can't have click events yet :(\n    setTimeout(closeGeneralSidebar, 100);\n  }\n  convertToBuilder() {\n    const {\n      blocks,\n      insertBlock,\n      removeBlocks\n    } = this.props;\n    const clientIds = blocks.map(block => block.clientId);\n    const content = serialize(blocks).replace(/<!--(.*?)-->/g, '');\n    const block = createBlock('fl-builder/layout', {\n      content\n    });\n    insertBlock(block, 0);\n    removeBlocks(clientIds);\n  }\n  launchBuilder() {\n    const {\n      savePost,\n      setLaunching\n    } = this.props;\n    setLaunching(true);\n    savePost();\n  }\n}\n\n/**\n * Connect the menu item to editor data.\n */\nconst BuilderMoreMenuItemConnectedPre_5_3 = compose(withDispatch((dispatch, ownProps) => {\n  const editor = dispatch('core/editor');\n  const editPost = dispatch('core/edit-post');\n  const builder = dispatch('fl-builder');\n  return {\n    savePost: editor.savePost,\n    insertBlock: editor.insertBlock,\n    removeBlocks: editor.removeBlocks,\n    closeGeneralSidebar: editPost.closeGeneralSidebar,\n    setLaunching: builder.setLaunching\n  };\n}), withSelect(select => {\n  const editor = select('core/editor');\n  return {\n    blocks: editor.getBlocks()\n  };\n}))(BuilderMoreMenuItemPre_5_3);\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/more-menu/menu-item-pre-5-3.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/more-menu/menu-item-pre-5-3.scss":
/*!***********************************************************!*\
  !*** ./src/wp/wp-editor/more-menu/menu-item-pre-5-3.scss ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/more-menu/menu-item-pre-5-3.scss?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/more-menu/menu-item.js":
/*!*************************************************!*\
  !*** ./src/wp/wp-editor/more-menu/menu-item.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   BuilderMoreMenuItemConnected: () => (/* binding */ BuilderMoreMenuItemConnected)\n/* harmony export */ });\nconst {\n  builder,\n  strings,\n  urls\n} = FLBuilderConfig;\nconst {\n  createBlock,\n  serialize\n} = wp.blocks;\nconst {\n  Button\n} = wp.components;\nconst {\n  compose\n} = wp.compose;\nconst {\n  withDispatch,\n  withSelect\n} = wp.data;\nconst {\n  PluginMoreMenuItem\n} = wp.editPost;\nconst {\n  Component\n} = wp.element;\n\n/**\n * Builder menu item for the more menu.\n */\nclass BuilderMoreMenuItem extends Component {\n  render() {\n    if (this.hasBuilderBlock()) {\n      jQuery('body').addClass('fl-builder-blocks');\n      jQuery(document).trigger('fl-builder-fix-blocks');\n    }\n    return /*#__PURE__*/React.createElement(PluginMoreMenuItem, {\n      onClick: this.menuItemClicked.bind(this)\n    }, this.hasBuilderBlock() ? strings.launch : strings.convert);\n  }\n  hasBuilderBlock() {\n    const {\n      blocks\n    } = this.props;\n    const builder = blocks.filter(block => 'fl-builder/layout' === block.name);\n    return !!builder.length;\n  }\n  menuItemClicked() {\n    if (this.hasBuilderBlock()) {\n      this.launchBuilder();\n    } else {\n      this.convertToBuilder();\n    }\n  }\n  convertToBuilder() {\n    const {\n      blocks,\n      insertBlock,\n      removeBlocks\n    } = this.props;\n    const clientIds = blocks.map(block => block.clientId);\n    const content = serialize(blocks).replace(/<!--(.*?)-->/g, '');\n    const block = createBlock('fl-builder/layout', {\n      content\n    });\n    insertBlock(block, 0);\n    removeBlocks(clientIds);\n  }\n  launchBuilder() {\n    const {\n      savePost,\n      setLaunching\n    } = this.props;\n    setLaunching(true);\n    /**\n     * WP 6.4 will NOT save a post with no title.\n     */\n    const title = wp.data.select(\"core/editor\").getEditedPostAttribute('title');\n    if (!title) {\n      wp.data.dispatch('core/editor').editPost({\n        title: wp.i18n.__('(no title)')\n      });\n    }\n    savePost().then(() => {\n      setTimeout(function () {\n        window.location.href = builder.access ? urls.edit : urls.view;\n      }, 2000);\n    });\n  }\n}\n\n/**\n * Connect the menu item to editor data.\n */\nconst BuilderMoreMenuItemConnected = compose(withDispatch((dispatch, ownProps) => {\n  const blockEditor = dispatch('core/block-editor');\n  const editor = dispatch('core/editor');\n  const builder = dispatch('fl-builder');\n  return {\n    insertBlock: blockEditor.insertBlock,\n    removeBlocks: blockEditor.removeBlocks,\n    savePost: editor.savePost,\n    setLaunching: builder.setLaunching\n  };\n}), withSelect(select => {\n  const blockEditor = select('core/block-editor');\n  return {\n    blocks: blockEditor.getBlocks()\n  };\n}))(BuilderMoreMenuItem);\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/more-menu/menu-item.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/store/index.js":
/*!*****************************************!*\
  !*** ./src/wp/wp-editor/store/index.js ***!
  \*****************************************/
/***/ (() => {

eval("{const {\n  registerStore\n} = wp.data;\nconst DEFAULT_STATE = {\n  launching: false\n};\nconst actions = {\n  setLaunching(launching) {\n    return {\n      type: 'SET_LAUNCHING',\n      launching\n    };\n  }\n};\nconst selectors = {\n  isLaunching(state) {\n    return state.launching;\n  }\n};\nregisterStore('fl-builder', {\n  reducer(state = DEFAULT_STATE, action) {\n    switch (action.type) {\n      case 'SET_LAUNCHING':\n        state.launching = action.launching;\n    }\n    return state;\n  },\n  actions,\n  selectors\n});\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/store/index.js?\n}");

/***/ }),

/***/ "./src/wp/wp-editor/wordpress/index.js":
/*!*********************************************!*\
  !*** ./src/wp/wp-editor/wordpress/index.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n// FUNCTION: Recover block\nconst recoverBlock = (block = null, autoSave = false) => {\n  // DECONSTRUCT: WP object\n  const {\n    wp = {}\n  } = window || {};\n  const {\n    data = {},\n    blocks = {}\n  } = wp;\n  const {\n    dispatch,\n    select\n  } = data;\n  const {\n    createBlock\n  } = blocks;\n  const {\n    replaceBlock\n  } = dispatch('core/block-editor');\n  const wpRecoverBlock = ({\n    name = '',\n    attributes = {},\n    innerBlocks = []\n  }) => createBlock(name, attributes, innerBlocks);\n\n  // DEFINE: Validation variables\n  const blockIsValid = block !== null && typeof block === 'object' && block.clientId !== null && typeof block.clientId === 'string';\n\n  // IF: Block is not valid\n  if (blockIsValid !== true) {\n    return false;\n  }\n\n  // GET: Block based on ID, to make sure it exists\n  const currentBlock = select('core/block-editor').getBlock(block.clientId);\n\n  // IF: Block was found\n  if (!currentBlock !== true) {\n    // DECONSTRUCT: Block\n    const {\n      clientId: blockId = '',\n      isValid: blockIsValid = true,\n      innerBlocks: blockInnerBlocks = []\n    } = currentBlock;\n\n    // DEFINE: Validation variables\n    const blockInnerBlocksHasLength = blockInnerBlocks !== null && Array.isArray(blockInnerBlocks) && blockInnerBlocks.length >= 1;\n\n    // IF: Block is not valid\n    if (blockIsValid !== true) {\n      // DEFINE: New recovered block\n      const recoveredBlock = wpRecoverBlock(currentBlock);\n\n      // REPLACE: Broke block\n      replaceBlock(blockId, recoveredBlock);\n\n      // IF: Auto save post\n      if (autoSave === true) {\n        wp.data.dispatch(\"core/editor\").savePost();\n      }\n    }\n\n    // IF: Inner blocks has length\n    if (blockInnerBlocksHasLength) {\n      blockInnerBlocks.forEach((innerBlock = {}) => {\n        recoverBlock(innerBlock, autoSave);\n      });\n    }\n  }\n\n  // RETURN\n  return false;\n};\n\n// FUNCTION: Attempt to recover broken blocks\nconst autoRecoverBlocks = (autoSave = false) => {\n  // DECONSTRUCT: WP object\n  const {\n    wp = {}\n  } = window || {};\n  const {\n    domReady,\n    data = {}\n  } = wp;\n  const {\n    select\n  } = data;\n\n  // AWAIT: For dom to get ready\n  domReady(function () {\n    setTimeout(function () {\n      // DEFINE: Basic variables\n      const blocksArray = select('core/block-editor').getBlocks();\n      const blocksArrayHasLength = Array.isArray(blocksArray) && blocksArray.length >= 1;\n\n      // IF: Blocks array has length\n      if (blocksArrayHasLength === true) {\n        blocksArray.forEach((element = {}) => {\n          recoverBlock(element, autoSave);\n        });\n      }\n    }, 1);\n  });\n};\n\n// EXPORT\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (autoRecoverBlocks);\n\n// DECONSTRUCT: WP\nconst {\n  wp = {}\n} = window || {};\nconst {\n  domReady,\n  data\n} = wp;\n\n// AWAIT: jQuery to get ready\njQuery(document).on('fl-builder-fix-blocks', function () {\n  // DEFINE: Validation variables\n  const hasGutenbergClasses = jQuery('body').hasClass('post-php') === true && jQuery('.block-editor').length >= 1 && jQuery('body').hasClass('fl-builder-blocks');\n  const gutenbergHasObject = domReady !== undefined && data !== undefined;\n  const gutenbergIsPresent = hasGutenbergClasses === true && gutenbergHasObject === true;\n\n  // IF: Gutenberg editor is present\n  if (gutenbergIsPresent === true) {\n    autoRecoverBlocks(false);\n  }\n});\n\n//# sourceURL=webpack://bb-plugin/./src/wp/wp-editor/wordpress/index.js?\n}");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./src/wp/wp-editor/index.js");
/******/ 	
/******/ })()
;