import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy as ZiggyRoutes } from './ziggy';
import vCan from './Directives/v-can';
import RestrictInput from './Directives/RestrictInput';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'HRMS';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const ziggy = (window.Ziggy && window.Ziggy.routes) ? window.Ziggy : ZiggyRoutes;
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(ZiggyVue, ziggy)
            .directive('can', vCan)
            .directive('restrict', RestrictInput);

        app.mount(el);
    },
    progress: {
        color: '#10B981',
    },
});

// Refresh trigger
