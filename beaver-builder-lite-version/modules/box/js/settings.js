(() => {

  FLBuilder.registerModuleHelper('box', {
    init: function () {
      const form = jQuery('.fl-builder-settings:visible');
      const layout = form.find('input[name=layout]');
      layout.on('change', (event) => this.check(event.target.value));
      form.find('input[name="grid_tracks[rows_css]"]').on('change', () => this.clear('rows'));
      form.find('input[name="grid_tracks[columns_css]"]').on('change', () => this.clear('columns'));
      this.addGridGuideIcon();

      FLBuilder.removeHook('didStopDrag', this.togglePlacementFields);
      FLBuilder.addHook('didStopDrag', this.togglePlacementFields);
      this.togglePlacementFields();
    },
    check: function (layout) {
      if (layout === 'grid') {
        this.toggle(false);
        const node = this.getNode();
        node.style.gridTemplateRows = node.style.gridTemplateColumns = '';
        const styling = getComputedStyle(node);
        if (styling.gridTemplateRows === 'none' && styling.gridTemplateColumns === 'none') {
          const inputs = document.querySelector('.fl-builder-settings').elements;
          node.style.gridTemplateRows = `${inputs['grid_tracks[rows_css]'].value}`;
          node.style.gridTemplateColumns = `${inputs['grid_tracks[columns_css]'].value}`;
        }
      } else {
        const icon = document.querySelector('.fl-builder-settings i.fl-field-overlay-toggle');
        icon.removeAttribute('style');
        icon.dataset.toggled = false;
        FLBuilder.triggerHook('toggleGridOverlay', false);
        if (layout === 'flex') {
          this.toggle(true);
        } else {
          this.toggle(false);
        }
      }
    },
    overlay: function (icon) {
      let toggled = null;
      if (icon.dataset.toggled === 'true') {
        toggled = false;
        icon.removeAttribute('style');
      } else {
        toggled = true;
        icon.style.color = 'var(--fl-builder-selected-color)';
      }
      icon.dataset.toggled = toggled;
      FLBuilder.triggerHook('toggleGridOverlay', toggled);
    },
    clear: function (track) {
      const node = this.getNode();
      if (track === 'rows') {
        node.style.gridTemplateRows = '';
      } else if (track === 'columns') {
        node.style.gridTemplateColumns = '';
      }
    },
    toggle: function (all) {
      const groups = document.querySelectorAll('#fl-field-place_content .fl-button-group-field-options');
      groups.forEach((group) => {
        const buttons = group.querySelectorAll('.fl-button-group-tooltip-wrap');
        for (let index = 4; index < buttons.length; index++) {
          if (all) {
            buttons[index].removeAttribute('style');
          } else {
            buttons[index].style.display = 'none';
          }
        }
      });
    },
    togglePlacementFields: function () {
      requestAnimationFrame(() => {
        const form = jQuery('.fl-builder-settings:visible');
        const nodeId = form.attr('data-node');
        const doc = FLBuilder.UIIFrame.getIFrameWindow().document;
        const element = jQuery( `.fl-node-${ nodeId }`, doc );

        if (element.parent().css('display') !== 'grid') {
          jQuery( '#fl-field-grid_col' ).hide();
          jQuery( '#fl-field-grid_row' ).hide();
        } else {
          jQuery( '#fl-field-grid_col' ).show();
          jQuery( '#fl-field-grid_row' ).show();
        }

        if (element.parent().css('display') !== 'flex') {
          jQuery( '#fl-field-flex' ).hide();
        } else {
          jQuery( '#fl-field-flex' ).show();
        }
      });
    },
    addGridGuideIcon: function() {
      const form = jQuery('.fl-builder-settings:visible');
      const label = document.querySelector('#fl-field-grid_tracks label[for=grid_tracks]');
      const layout = form.find('input[name=layout]');
      let icon = label.querySelector('.fl-field-overlay-toggle');

      if ( icon ) {
        return;
      }

      icon = document.createElement('i');
      icon.classList.add('fl-field-overlay-toggle', 'dashicons', 'dashicons-grid-view');
      icon.dataset.toggled = false;
      icon.addEventListener('click', (event) => this.overlay(icon) );
      label.appendChild(icon);

      FLBuilder._moduleHelpers.box.check(layout.val());
      FLBuilder.triggerHook('setupGridOverlay', form[0].dataset.node);
    },
  });
})();