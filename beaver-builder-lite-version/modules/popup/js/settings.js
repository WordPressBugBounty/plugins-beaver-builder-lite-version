(function () {

    FLBuilder.registerModuleHelper('popup', {

        init: function () {
            this.form = document.querySelector('.fl-builder-settings');
            const frame = FLBuilder.UIIFrame.getIFrameWindow();
            this.node = frame.document.querySelector(`.fl-node-${this.form.dataset.node}`);
            const fields = this.form.elements;
            fields.popup_position.addEventListener('change', () => this.previewClose(fields));
            fields.close_button_position.addEventListener('change', () => this.previewClose(fields));
            this.form.querySelector('#fl-field-container_element')?.remove();
            this.syncFields(fields.id, fields.trigger_id);
            this.removeNavigation();
            this.loopMessage();
        },

        syncFields: function (first, second) {
            first.addEventListener('input', (event) => second.value = event.target.value);
            second.addEventListener('input', (event) => first.value = event.target.value);
        },

        removeNavigation: function () {
            if (this.node.closest('.fl-module-loop')) return;
            this.form.querySelector('#fl-builder-settings-section-navigation')?.remove();
        },

        loopMessage: function () {
            const looped = this.node.closest('.fl-module-loop');
            const section = this.form.querySelector('#fl-builder-settings-section-trigger .fl-builder-settings-section-content');
            const prepended = section.querySelector('.fl-builder-settings-description');
            if (!looped || prepended) return;
            const message = document.createElement('p');
            message.classList.add('fl-builder-settings-description');
            message.innerHTML = 'Popups within loops must include the current post ID in the popup ID to ensure it is unique.<br>Example: my-popup-[wpbb post:id]';
            section.prepend(message);
        },

        previewClose: function (fields) {
            if (fields.close_button_position.value === 'fixed') return;
            const breakpoint = FLBuilderResponsiveEditing._mode === 'default' ? '' : '_' + FLBuilderResponsiveEditing._mode;
            const [top, right, bottom, left] = ['top', 'right', 'bottom', 'left'].map(side => {
                const key = `close_button_inset_${side}`;
                return fields[key + breakpoint] || fields[key];
            });
            const positions = {
                '0 auto auto 0': [right, bottom],
                '0 auto auto auto': [right, bottom],
                '0 0 auto auto': [left, bottom],
                'auto auto auto 0': [top, right],
                'auto auto auto auto': [top, right],
                'auto auto 0 0': [top, right],
                'auto auto 0 auto': [top, right],
                'auto 0 auto auto': [top, left],
                'auto 0 0 auto': [top, left],
            };
            const filled = positions[fields.popup_position.value] ?? [];
            const width = this.node.querySelector('.fl-popup-close')?.offsetWidth ?? 40;
            const offset = -(width / 2);
            [top, right, bottom, left].forEach(field => {
                field.value = filled.includes(field) ? offset : '';
                field.dispatchEvent(new Event('input'));
            });
        },
    });

})();
