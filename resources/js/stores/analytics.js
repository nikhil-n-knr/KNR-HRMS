import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAnalyticsStore = defineStore('analytics', () => {
    // Default to false to keep interface clean
    const showStats = ref(localStorage.getItem('opscore_show_stats') === 'true');

    function toggle() {
        showStats.value = !showStats.value;
        localStorage.setItem('opscore_show_stats', showStats.value);
    }

    function set(value) {
        showStats.value = value;
        localStorage.setItem('opscore_show_stats', value);
    }

    return { showStats, toggle, set };
});
