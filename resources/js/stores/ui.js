import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUiStore = defineStore('ui', () => {
    const isLoading = ref(false);
    const globalError = ref(null);
    const activeRequests = ref(0);

    const startLoading = () => {
        activeRequests.value++;
        isLoading.value = true;
    };

    const stopLoading = () => {
        activeRequests.value--;
        if (activeRequests.value <= 0) {
            activeRequests.value = 0;
            isLoading.value = false;
        }
    };

    const setError = (message) => {
        globalError.value = message;
        // Auto-clear after 5 seconds
        setTimeout(() => {
            globalError.value = null;
        }, 5000);
    };

    return {
        isLoading,
        globalError,
        startLoading,
        stopLoading,
        setError
    };
});
