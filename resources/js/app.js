import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Navigation, Pagination } from 'swiper/modules';

const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

// Remember ad campaign tags for the whole visit, so a lead sent from another page still carries them.
(() => {
    const params = new URLSearchParams(window.location.search);
    const utm = Object.fromEntries(UTM_KEYS.filter((key) => params.has(key)).map((key) => [key, params.get(key)]));

    if (Object.keys(utm).length) {
        try {
            sessionStorage.setItem('utm', JSON.stringify(utm));
        } catch {}
    }
})();

function storedUtm() {
    try {
        return JSON.parse(sessionStorage.getItem('utm') || '{}');
    } catch {
        return {};
    }
}

function trackGoal(name) {
    if (typeof window.ym === 'function' && window.ymId) {
        window.ym(window.ymId, 'reachGoal', name);
    }

    if (typeof window.gtag === 'function') {
        window.gtag('event', 'generate_lead', { lead_type: name });
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('modal', {
        name: null,
        payload: {},

        open(name, payload = {}) {
            this.name = name;
            this.payload = payload;
            document.documentElement.classList.add('overflow-hidden');
        },

        close() {
            this.name = null;
            this.payload = {};
            document.documentElement.classList.remove('overflow-hidden');
        },
    });

    Alpine.data('leadForm', (type) => ({
        type,
        sending: false,
        sent: false,
        errors: {},
        failure: '',

        init() {
            // Preselect the model when the modal is opened from a car page or card.
            this.$watch('$store.modal.name', (name) => {
                if (name !== this.type) {
                    return;
                }

                this.sent = false;
                const carId = this.$store.modal.payload.car_id;

                if (carId && this.$refs.car) {
                    this.$refs.car.value = carId;
                }
            });
        },

        error(field) {
            return this.errors[field]?.[0] ?? '';
        },

        async submit() {
            if (this.sending) {
                return;
            }

            this.sending = true;
            this.errors = {};
            this.failure = '';

            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.querySelector('form');
            const data = new FormData(form);
            data.set('page_url', window.location.href);
            Object.entries(storedUtm()).forEach(([key, value]) => data.set(key, value));

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: data,
                });

                if (response.ok) {
                    this.sent = true;
                    form.reset();
                    trackGoal(this.type);
                } else if (response.status === 422) {
                    this.errors = (await response.json()).errors ?? {};
                } else if (response.status === 429) {
                    this.failure = form.dataset.tooMany;
                } else {
                    this.failure = form.dataset.failure;
                }
            } catch {
                this.failure = form.dataset.failure;
            } finally {
                this.sending = false;
            }
        },
    }));

    Alpine.data('heroSlider', () => ({
        init() {
            new Swiper(this.$refs.swiper, {
                modules: [Autoplay, EffectFade, Pagination, A11y],
                effect: 'fade',
                fadeEffect: { crossFade: true },
                loop: this.$refs.swiper.querySelectorAll('.swiper-slide').length > 1,
                speed: 900,
                autoplay: { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
                pagination: { el: this.$refs.pagination, clickable: true },
            });
        },
    }));

    Alpine.data('carousel', (options = {}) => ({
        swiper: null,

        init() {
            this.swiper = new Swiper(this.$refs.swiper, {
                modules: [Navigation, Pagination, A11y],
                slidesPerView: 1.15,
                spaceBetween: 16,
                navigation: { prevEl: this.$refs.prev, nextEl: this.$refs.next },
                pagination: this.$refs.pagination ? { el: this.$refs.pagination, clickable: true } : false,
                breakpoints: {
                    640: { slidesPerView: 2, spaceBetween: 20 },
                    1024: { slidesPerView: options.desktop ?? 3, spaceBetween: 24 },
                },
                ...options.swiper,
            });
        },
    }));

    Alpine.data('header', (overlay = false) => ({
        overlay,
        scrolled: false,
        hidden: false,
        menu: false,
        lastY: 0,

        // Over a dark hero the header is transparent until the page is scrolled.
        get solid() {
            return !this.overlay || this.scrolled || this.menu;
        },

        init() {
            const onScroll = () => {
                const y = window.scrollY;
                this.scrolled = y > 24;
                this.hidden = !this.menu && y > 400 && y > this.lastY;
                this.lastY = y;
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            this.$watch('menu', (open) => document.documentElement.classList.toggle('overflow-hidden', open));
        },
    }));
});

Alpine.plugin(collapse);
Alpine.plugin(focus);
window.Alpine = Alpine;
Alpine.start();
