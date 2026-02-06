(() => {
  FLBuilder.registerModuleHelper('star-rating', {
    rules: {
      rating: {
        required: true,
        number: true,
        min: 0
      },
      total: {
        required: true,
        number: true,
        min: 1,
        max: 10
      }
    },
    init: function() {
      const form = jQuery(this.getForm());
      form.find( 'input[name=total]' ).on( 'keydown', ( event ) => this.preventDecimal( event ) );
      form.find( 'input[name=total]' ).on( 'input', ( event ) => this.setTotal( event ) );
      form.find( 'input[name=rating]' ).on( 'input', ( event ) => this.setRating( event ) );
      form.find( 'input[name=icon]' ).on( 'change', ( event ) => this.setIcon( event ) );
      form.find( 'input[name=align]' ).on( 'change', ( event ) => this.adjustAlignment( event ) );
      form.find( 'input[name^=size]' ).on( 'input', () => this.calculate_step() );
      form.find( 'input[name^=space]' ).on( 'input', () => this.calculate_step() );
      form.find( 'input[name=stroke]' ).on( 'input', () => this.refreshStroke() );
      //this.adjustSlider( form.find( 'input[name=total]' ).val() );
    },
    calculate_step: function() {
      const form = this.getForm();
      const rating = parseFloat( form.rating.value );
      const number = Math.floor( rating );
      const breakpoint = FLBuilderResponsiveEditing._mode === 'default' ? '' : '_' + FLBuilderResponsiveEditing._mode;
      const size = parseFloat( form['size' + breakpoint].value || form.size.value ) * parseFloat( form.ratio.value );
      const unit = number * ( parseFloat( form['space' + breakpoint].value || form.space.value ) + size );
      const fraction = ( rating - number ) * size;
      this.getNode().style.setProperty( '--step', ( unit + fraction ) + 'px' );
    },
    preventDecimal: function( event ) {
      if ( event.key === '.' ) {
        event.preventDefault();
      }
    },
    adjustSlider: function( max ) {
      const input = this.getForm().rating;
      const slider = input.nextElementSibling;
      const parameters = JSON.parse( slider.getAttribute( 'data-slider' ) );
      parameters.max = String( max );
      slider.setAttribute( 'data-slider', JSON.stringify( parameters ) );
      if ( parseFloat( input.value ) > max ) {
        input.value = max;
      }
    },
    setTotal: function( event ) {
      const value = parseInt( event.target.value );
      const total = Math.max( 1, Math.min( value, 10 ) );
      const icon = this.getNode().dataset.content.slice( 0, 1 );
      if ( total !== value ) {
        event.target.value = total;
      }
      this.getNode().dataset.content = icon.repeat( total );
      this.adjustSlider( total );
      this.calculate_step();
    },
    setRating: function( event ) {
      const value = parseFloat( event.target.value );
      const total = this.getNode().dataset.content.length;
      const rating = Math.max( 0, Math.min( event.target.value, total ) );
      if ( rating !== value ) {
        event.target.value = rating;
      }
      this.getNode().dataset.rating = rating;
      this.calculate_step();
    },
    setIcon: function( event ) {
      const parameters = { unicode: '★', font: 'times', ratio: 1 };
      const value = event.target.value;
      const node = this.getNode();
      const form = this.getForm();
      if ( node.dataset.icon?.length > 0 ) {
        node.classList.remove( ...node.dataset.icon.split(' ') );
      }
      if ( value.length !== 0 ) {
        node.classList.remove( 'fl-module' );
        node.style.setProperty( '--display', 'inline-block' );
        node.classList.add( ...value.split(' ') );
        const rules = getComputedStyle( node, '::before' );
        parameters.unicode = rules.content.slice( 1, -1 );
        parameters.font = rules.fontFamily;
        parameters.ratio = parseFloat( rules.width ) / parseFloat( form.size.value );
        node.style.removeProperty( '--display' );
        node.classList.add( 'fl-module' );
        node.dataset.icon = value;
      }
      node.dataset.content = parameters.unicode.repeat( node.dataset.content.length );
      node.style.setProperty( '--font', parameters.font );
      form.unicode.value = parameters.unicode;
      form.font.value = parameters.font;
      form.ratio.value = parameters.ratio;
      this.calculate_step();
    },
    refreshStroke: function() {
      const node = this.getNode();
      node.style.setProperty( 'display', 'none' );
      setTimeout( () => node.style.removeProperty( 'display' ) );
    },
    adjustAlignment: function( event ) {
      const node = this.getNode();
      if ( event.target.value === 'auto 0' ) {
        node.style.setProperty( '--offset', 'calc(-1 * var(--space))' );
      } else {
        node.style.setProperty( '--offset', 'auto' );
      }
    }
  });
})();
