/* eslint-disable no-undef */
(function ($) {

    $(function () {

        const PopupBuilder = {

            // Cached popup instance that is opened in the builder.
            popup: null,

            // Actions that trigger the popup to close when the builder is refreshed.
            actions: ['render_layout', 'render_node', 'delete_node', 'save_settings', 'apply_module_alias'],

            setupBuilder: () => {
                FLBuilder.addHook('didInitDrag', PopupBuilder.checkDragging);
                FLBuilder.addHook('didBeginAJAX', PopupBuilder.prepareClosing);
                FLBuilder.addHook('didDeleteModule', PopupBuilder.clearWrapper);
                FLBuilder.addHook('settings-form-init', PopupBuilder.settingsOpened);
                FLBuilder.addHook('renderPreviewComplete', PopupBuilder.popupRendered);
                FLBuilder.addHook('didRenderLayoutComplete', PopupBuilder.popupRendered);
            },

            maskWrapper: (toggle) => {
                const element = PopupBuilder.popup.settings.element;
                const wrapper = element.parentElement;
                const node = element.dataset.node;
                // Removes classes on the wrapper element to prevent overlay menu & partial render duplication.
                wrapper.classList.toggle('fl-module', !toggle);
                wrapper.classList.toggle(`fl-node-${node}`, !toggle);
            },

            queryElement: (node) => {
                const element = document.querySelector(`.fl-node-${node}:not(.fl-popup-wrapper)`);
                if (!element) return undefined;
                if (element.classList.contains('fl-popup')) return element;
                return element.closest('.fl-popup');
            },

            detectNavigation: () => {
                const element = PopupBuilder.popup.settings.element;
                const loop = element.closest('.fl-module-loop');
                const navigation = element.querySelector('.fl-popup-nav');
                if (!loop || !navigation) return;
                PopupBuilder.popup.navigationEnable('next');
            },

            processNode: (node) => {
                const element = PopupBuilder.queryElement(node);
                // Element is not rendered yet if it is not found in the DOM.
                if (element === undefined) return;
                const popup = PopupBuilder.popup;
                const connected = popup && popup.settings.element.isConnected;
                const matched = popup && element && popup.settings.element.id === element.id;
                // Close current if a different popup will be opened and the cached popup is not stale due to render refresh.
                if (!matched && connected) PopupBuilder.closePopup();
                // No popups in the opened settings chain for the current element.
                if (!element) return;
                // Create if a different popup opened or cached popup is null or its element is staled due to render refresh.
                if (!matched || !popup || !connected) PopupBuilder.popup = new FLPopup({ id: element.id, element });
                PopupBuilder.detectNavigation();
                PopupBuilder.openPopup();
            },

            checkDragging: (_, { target }) => {
                const type = target.data('type');
                const themer = 'popup' === FLBuilderConfig.themerLayoutType;
                const current = PopupBuilder.popup && PopupBuilder.popup.checkOpened();
                const nesting = current && type === 'popup';
                const empty = !current && type !== 'popup' && themer;
                // Disable dragging popups into other popups or other modules in empty popup themer layouts.
                if (nesting || empty) {
                    const sortables = document.querySelectorAll('.ui-sortable');
                    sortables.forEach(sortable => sortable.classList.add('fl-sortable-disabled'));
                }
            },

            prepareClosing: (event, { action, node_id, node_preview_id }) => {
                if (!PopupBuilder.actions.includes(action) || !PopupBuilder.popup) return;
                const node = PopupBuilder.popup.settings.element.dataset.node;
                if (![node_id, node_preview_id].includes(node)) return;
                PopupBuilder.closePopup(event);
            },

            clearWrapper: (_, { moduleType, nodeId }) => {
                if (moduleType !== 'popup') return;
                document.querySelector(`.fl-node-${nodeId}`)?.remove();
            },

            settingsOpened: () => {
                const form = $('.fl-builder-settings:visible')[0];
                PopupBuilder.processNode(form.dataset.node);
            },

            popupRendered: (_, { moduleType, nodeId }) => {
                if (moduleType !== 'popup') return;
                PopupBuilder.processNode(nodeId);
            },

            openPopup: () => {
                if (PopupBuilder.popup.checkOpened()) return;
                const { id, element } = PopupBuilder.popup.settings;
                element.querySelector('.fl-popup-close')?.addEventListener('click', PopupBuilder.closePopup);
                FLBuilder.setSortableRootClass(`#${id} .fl-popup-content`);
                FLBuilder._removeModuleOverlays();
                PopupBuilder.maskWrapper(true);
                PopupBuilder.popup.manualToggle('show');
                // Allow dimension-dependent child modules (e.g. slideshows) to recalculate after the popup becomes visible.
                requestAnimationFrame(() => dispatchEvent(new Event('resize')));
            },

            closePopup: (event = null) => {
                if (!PopupBuilder.popup.checkOpened()) return;
                if (event && event.type === 'click') {
                    // Avoid triggering builder click events which reopens the popup instead of closing it.
                    event.stopImmediatePropagation();
                    FLBuilder._lightbox.close();
                }
                PopupBuilder.maskWrapper(false);
                FLBuilder.resetSortableRootClass();
                // Do not close the popup during render refresh events as it closes automatically when reloaded.
                if (event && event.type !== 'click') return;
                FLBuilder._removeModuleOverlays();
                const element = PopupBuilder.popup.settings.element;
                element.querySelector('.fl-popup-close')?.removeEventListener('click', PopupBuilder.closePopup);
                PopupBuilder.popup.manualToggle('hide');
                PopupBuilder.popup = null;
            },
        }

        PopupBuilder.setupBuilder();
    });

})(jQuery);
