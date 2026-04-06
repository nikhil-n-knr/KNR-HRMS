import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        permissions: [],
        // Sanctum uses cookies, so we don't strictly need a stored token for auth, 
        // but we might use a flag to know if we are logged in.
        isLoggedIn: localStorage.getItem('isLoggedIn') === 'true'
    }),

    getters: {
        isAuthenticated: (state) => state.isLoggedIn,
    },

    actions: {
        can(permission) {
            // Super Admin Bypass
            if (this.user?.is_super_admin) return true;
            if (this.user?.role?.name === 'Super Admin') return true; // Fallback

            if (Array.isArray(this.permissions)) {
                if (this.permissions.includes('*')) return true;
                return this.permissions.includes(permission);
            }
            return false;
        },

        async login(credentials) {
            // 1. Get CSRF Cookie
            await axios.get('/sanctum/csrf-cookie');

            // 2. Login
            await axios.post('/login', credentials);

            // 3. Get User
            await this.fetchUser();

            this.isLoggedIn = true;
            localStorage.setItem('isLoggedIn', 'true');
        },

        async fetchUser() {
            try {
                const response = await axios.get('/api/user');
                this.user = response.data;
                this.isLoggedIn = true;
                // Capabilities are now in response.data.capabilities
                // We can store them for easy access
                this.permissions = response.data.capabilities || [];
                localStorage.setItem('isLoggedIn', 'true');
            } catch (error) {
                this.user = null;
                this.isLoggedIn = false;
                localStorage.removeItem('isLoggedIn');
                throw error;
            }
        },

        async logout() {
            try {
                await axios.post('/logout');
            } catch (e) {
                console.error('Logout failed', e);
            } finally {
                this.user = null;
                this.isLoggedIn = false;
                localStorage.removeItem('isLoggedIn');
                // Force reload to clear any memory states if needed, or just let router handle
            }
        }
    }
});
