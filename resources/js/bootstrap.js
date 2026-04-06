/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
import { useUiStore } from '@/stores/ui'; // Import the store

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Global Interceptors
window.axios.interceptors.request.use(
    config => {
        // We need to ensure Pinia is active. Since requests happen after app mount, this is safe.
        // However, to be extra safe against early calls, we try-catch or check active pinia.
        try {
            const ui = useUiStore();
            if (ui) ui.startLoading();
        } catch (e) {
            // Pinia not ready yet, ignore
        }
        return config;
    },
    error => {
        try {
            const ui = useUiStore();
            if (ui) ui.stopLoading();
        } catch (e) { }
        return Promise.reject(error);
    }
);

window.axios.interceptors.response.use(
    response => {
        try {
            const ui = useUiStore();
            if (ui) ui.stopLoading();
        } catch (e) { }
        return response;
    },
    error => {
        try {
            const ui = useUiStore();
            if (ui) {
                ui.stopLoading();

                // Handle specific errors
                if (error.response) {
                    if (error.response.status === 401) {
                        if (window.location.pathname !== '/login') {
                            window.location.href = '/login';
                        }
                    } else if (error.response.status >= 500) {
                        ui.setError('Server Error: Please try again later.');
                    } else {
                        ui.setError(error.response.data.message || 'An error occurred.');
                    }
                } else {
                    ui.setError('Network Error: Please check your connection.');
                }
            }
        } catch (e) { }
        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
