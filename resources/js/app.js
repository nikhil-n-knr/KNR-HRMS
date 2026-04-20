import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import vCan from './Directives/v-can';
import RestrictInput from './Directives/RestrictInput';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import relativeTime from 'dayjs/plugin/relativeTime';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'HRMS';
const defaultTimeZone = 'Asia/Kolkata';
const defaultDateLocale = 'en-IN';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(relativeTime);
dayjs.tz.setDefault(defaultTimeZone);

const patchDateLocaleMethods = () => {
    if (window.__hrmsIstDatePatched) {
        return;
    }

    ['toLocaleString', 'toLocaleDateString', 'toLocaleTimeString'].forEach((methodName) => {
        const original = Date.prototype[methodName];

        Date.prototype[methodName] = function patchedDateLocaleMethod(locales, options) {
            const normalizedLocales = locales ?? defaultDateLocale;
            const normalizedOptions = { ...(options || {}) };

            if (!normalizedOptions.timeZone) {
                normalizedOptions.timeZone = defaultTimeZone;
            }

            return original.call(this, normalizedLocales, normalizedOptions);
        };
    });

    window.__hrmsIstDatePatched = true;
};

const patchDayjsDisplayMethods = () => {
    if (window.__hrmsIstDayjsPatched) {
        return;
    }

    const originalFormat = dayjs.prototype.format;
    const originalFromNow = dayjs.prototype.fromNow;

    dayjs.prototype.format = function patchedFormat(...args) {
        return originalFormat.call(this.tz(defaultTimeZone), ...args);
    };

    dayjs.prototype.fromNow = function patchedFromNow(...args) {
        return originalFromNow.call(this.tz(defaultTimeZone), ...args);
    };

    window.__hrmsIstDayjsPatched = true;
};

patchDateLocaleMethods();
patchDayjsDisplayMethods();
window.HRMS_DEFAULT_TIMEZONE = defaultTimeZone;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(ZiggyVue, Ziggy)
            .directive('can', vCan)
            .directive('restrict', RestrictInput);

        app.mount(el);
    },
    progress: {
        color: '#10B981',
    },
});

// Refresh trigger
