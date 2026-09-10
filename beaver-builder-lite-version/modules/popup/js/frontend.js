/* eslint-disable no-undef */
(function () {

    // Date formats for scheduling triggers.
    const FORMATS = {
        start: 'T00:00:00',
        end: 'T23:59:59',
    };

    // Toggling JS functions & HTML commands for different popup types.
    const ACTIONS = {
        popover: {
            show: { function: 'showPopover', command: 'show-popover' },
            hide: { function: 'hidePopover', command: 'hide-popover' }
        },
        modal: {
            show: { function: 'showModal', command: 'show-modal' },
            hide: { function: 'close', command: 'close' }
        },
    };

    FLPopup = function (settings) {

        this.settings = {
            id: settings.id,
            element: settings.element,
            type: null,
            invoker: null,
            announcer: null,
            once: settings.once ?? null,
            triggers: settings.triggers ?? null,
            schedule: settings.schedule ?? null,
            loop: settings.loop ?? null,
        };
        this.setupPopup();
    }

    FLPopup.prototype = {

        setupPopup: function () {
            const { id, element } = this.settings;
            if (!id || !element) return;
            this.settings.type = this.detectType();
            if (!this.settings.type || 'FLBuilder' in window || this.themerViewer()) return;
            this.attachListeners();
            this.addAnnouncer();
            this.applyLabel();
            this.triggerOnce();
            this.triggerDelay();
            this.triggerExit();
            this.triggerScroll();
            this.connectLoop();
        },

        safariFix: function () {
            // Manual approach for Safari and any other browser not supporting the `closedBy` attribute in the dialog element only.
            // It should be safely removed in the future when Safari fully supports it. It has only one reference in the `openPopup` method.
            const { element, type } = this.settings;
            const supported = 'closedBy' in HTMLDialogElement.prototype;
            const behavior = element.getAttribute('closedby');
            if (type !== 'modal' || supported || behavior === 'none') return;
            const clicking = (event) => {
                if (!this.checkOpened() || event.target.closest('.fl-popup-close, .fl-popup-nav')) return;
                const { top, left, right, bottom } = element.getBoundingClientRect();
                const outside = event.clientY < top || event.clientX < left || event.clientX > right || event.clientY > bottom;
                if (outside) dismissing();
            };
            const escaping = (event) => {
                if (this.checkOpened() && event.key === 'Escape') dismissing();
            };
            const dismissing = () => {
                this.manualToggle('hide');
                this.settings.invoker.focus();
                document.removeEventListener('click', clicking);
                document.removeEventListener('keydown', escaping);
            }
            document.addEventListener('click', clicking);
            document.addEventListener('keydown', escaping);
        },

        detectType: function () {
            const element = this.settings.element;
            if (element.hasAttribute('popover')) return 'popover';
            if (element.hasAttribute('closedby')) return 'modal';
            return null;
        },

        themerViewer: function () {
            const wrapper = this.settings.element.closest('.fl-popup-wrapper');
            if (!wrapper) return false;
            // Configure for Themer layout viewing when the builder is not active and the wrapper is present.
            wrapper.addEventListener('click', this.manualToggle.bind(this, 'show'));
            this.settings.element.style.cursor = 'auto';
            wrapper.style.cursor = 'pointer';
            this.manualToggle('show');
            return true;
        },

        attachListeners: function () {
            this.buttonsHandler();
            this.linksHandler();
            const { element, type } = this.settings;
            const event = type === 'popover' ? 'toggle' : 'close';
            element.addEventListener(event, this.closeHandler.bind(this));
            element.addEventListener('command', this.openHandler.bind(this));
        },

        buttonsHandler: function () {
            const { id, type } = this.settings;
            // Find all buttons targeting this popup and ignore the close buttons with hide command.
            const invokers = `button[commandfor="${id}"]:not([command="${ACTIONS[type].hide.command}"])`;
            document.querySelectorAll(invokers).forEach((button) => {
                // Default to an invoker if not already set by a previous command event.
                button.setAttribute('command', ACTIONS[type].show.command);
                this.settings.invoker ??= button;
            });
        },

        linksHandler: function () {
            const { id, type } = this.settings;
            document.querySelectorAll(`[href="#${id}"]`).forEach((link) => {
                this.settings.invoker ??= link;
                link.setAttribute('role', 'button');
                link.setAttribute('aria-expanded', false);
                link.setAttribute('aria-controls', id);
                link.addEventListener('click', this.openHandler.bind(this));
                if (type === 'modal') link.setAttribute('aria-haspopup', 'dialog');
                // Only non-button links as all button modules already have keyboard accessibility.
                if (link.classList.contains('fl-button')) return;
                link.addEventListener('keydown', (event) => {
                    if (event.key === ' ') {
                        event.preventDefault();
                        link.click();
                    }
                });
            });
        },

        closeHandler: function (event) {
            if (event.type === 'toggle' && event.newState !== 'closed') return;
            const { invoker, announcer } = this.settings;
            if (invoker && invoker.hasAttribute('aria-expanded')) invoker.setAttribute('aria-expanded', false);
            if (announcer && announcer.textContent) announcer.textContent = '';
            document.removeEventListener('keydown', this.keyboardListener);
        },

        openHandler: function (event) {
            switch (event.type) {
                case 'command':
                    if (event.command !== ACTIONS[this.settings.type].show.command) return;
                    this.settings.invoker = event.source;
                    break;
                case 'click':
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    this.settings.invoker = event.currentTarget;
                    this.settings.invoker.setAttribute('aria-expanded', true);
                    this.manualToggle('show');
                    break;
                case 'auto':
                    if (this.settings.announcer) this.settings.announcer.textContent = 'A popup appeared on the page.';
                    this.manualToggle('show');
                    break;
                default:
                    return;
            }
            this.markShown();
            this.safariFix();
            this.keyboardListener = this.navigationKeys.bind(this);
            document.addEventListener('keydown', this.keyboardListener);
            // Allow dimension-dependent child modules (e.g. slideshows) to recalculate after the popup becomes visible.
            requestAnimationFrame(() => dispatchEvent(new Event('resize')));
        },

        manualToggle: function (action) {
            const { type, element } = this.settings;
            element[ACTIONS[type][action].function]();
        },

        applyLabel: function () {
            const { element, invoker, type } = this.settings;
            const label = element.getAttribute('aria-labelledby') || element.getAttribute('aria-label');
            if (label && label !== 'Popup') return;
            const heading = element.querySelector('h1, h2, h3, h4, h5, h6')?.textContent.trim();
            const button = invoker?.textContent.trim();
            const fallback = type === 'modal' ? 'Dialog' : 'Popover';
            element.setAttribute('aria-label', heading || button || fallback);
        },

        addAnnouncer: function () {
            if (this.settings.invoker || this.settings.type === 'modal') return;
            this.settings.announcer = document.querySelector('#fl-popup-announcer');
            if (this.settings.announcer) return;
            // Inject a live region for screen readers to announce the popup when no invoker is present.
            this.settings.announcer = document.createElement('div');
            this.settings.announcer.id = 'fl-popup-announcer';
            this.settings.announcer.className = 'sr-only';
            this.settings.announcer.ariaLive = 'polite';
            document.body.appendChild(this.settings.announcer);
        },

        checkOpened: function () {
            const { type, element } = this.settings;
            switch (type) {
                case 'popover':
                    return element.matches(':popover-open');
                case 'modal':
                    return element.open;
                default:
                    return false;
            }
        },

        hasCookie: function (key) {
            return document.cookie.split('; ').some(row => row.startsWith(`${key}=`));
        },

        hasSession: function (key) {
            return sessionStorage.getItem(key) !== null;
        },

        markShown: function () {
            const { mode, key, shown } = this.settings.once;
            if (!mode || shown) return;
            const year = 60 * 60 * 24 * 365;
            switch (mode) {
                case 'per_user':
                    document.cookie = `${key}=1; path=/; max-age=${year}`;
                    break;
                case 'per_session':
                    sessionStorage.setItem(key, '1');
                    break;
                default:
                    return;
            }
        },

        onceMode: function () {
            return !this.settings.once.mode || !this.settings.once.shown;
        },

        formatDate: function (key) {
            const string = this.settings.schedule[key];
            if (!string) return null;
            return new Date(string + FORMATS[key]);
        },

        validRange: function () {
            const now = new Date();
            const start = this.formatDate('start');
            const end = this.formatDate('end');
            if (start && now < start) return false;
            if (end && now > end) return false;
            return true;
        },

        canTrigger: function () {
            return this.onceMode() && this.validRange();
        },

        fireTrigger: function () {
            this.settings.invoker ? this.settings.invoker.click() : this.openHandler(new Event('auto'));
        },

        triggerOnce: function () {
            const { mode, key } = this.settings.once;
            switch (mode) {
                case 'per_user':
                    this.settings.once.shown = this.hasCookie(key);
                    break;
                case 'per_session':
                    this.settings.once.shown = this.hasSession(key);
                    break;
                default:
                    return;
            }
        },

        triggerDelay: function () {
            const delay = this.settings.triggers.delay;
            if (!delay || !this.canTrigger()) return;
            const seconds = parseInt(delay, 10);
            setTimeout(() => {
                if (this.canTrigger()) this.fireTrigger();
            }, seconds * 1000);
        },

        triggerExit: function () {
            const exit = this.settings.triggers.exit;
            if (!exit || !this.canTrigger()) return;
            const onExit = (event) => {
                if (event.clientY <= 10 && this.canTrigger()) {
                    this.fireTrigger();
                    document.removeEventListener('mouseout', onExit);
                }
            };
            document.addEventListener('mouseout', onExit);
        },

        triggerScroll: function () {
            const scroll = this.settings.triggers.scroll;
            const target = parseFloat(scroll);
            if (!scroll || target === 0 || !this.canTrigger()) return;
            const onScroll = () => {
                const scrollable = Math.max(1, document.documentElement.scrollHeight - innerHeight);
                const scrolled = (scrollY / scrollable) * 100;
                if (scrolled >= target && this.canTrigger()) {
                    this.fireTrigger();
                    removeEventListener('scroll', onScroll);
                }
            };
            addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        },

        connectLoop: function () {
            const element = this.settings.element;
            const loop = element.closest('.fl-module-loop');
            const navigation = element.querySelector('.fl-popup-nav');
            if (!navigation || !loop) return;
            element.addEventListener('click', this.navigationListener.bind(this));
            const popup = this.settings.loop.prev;
            if (!popup) return;
            this.navigationEnable('prev');
            popup.navigationEnable('next');
            popup.settings.loop.next = this;
        },

        navigationEnable: function (direction) {
            this.settings.element.querySelector(`.fl-popup-nav-${direction}`).disabled = false;
        },

        navigationListener: function (event) {
            const button = event.target.closest('.fl-popup-nav');
            if (!button) return;
            const direction = button.classList.contains('fl-popup-nav-next') ? 'next' : 'prev';
            this.navigationHandler(direction);
        },

        navigationKeys: function (event) {
            const directions = { ArrowLeft: 'prev', ArrowRight: 'next' };
            const direction = directions[event.key];
            if (!direction || !this.checkOpened()) return;
            // Prevent jumping if the key event is a long press.
            requestAnimationFrame(() => this.navigationHandler(direction));
        },

        navigationHandler: function (direction) {
            const popup = this.settings.loop[direction];
            if (!popup) return;
            this.manualToggle('hide');
            popup.fireTrigger();
            popup.settings.element.querySelector(`.fl-popup-nav-${direction}`).focus();
        },
    }

})();
