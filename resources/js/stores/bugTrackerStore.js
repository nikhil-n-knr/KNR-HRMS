import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useBugTrackerStore = defineStore('bugTracker', () => {
    // 1. View Mode: internal, client, audit
    const viewMode = ref(localStorage.getItem('bug_tracker_view_mode') || 'internal');

    // 2. Context Filters
    const selectedProject = ref(localStorage.getItem('bug_tracker_project_id') || null);
    const selectedModule = ref(localStorage.getItem('bug_tracker_module_id') || null);

    // 3. Time Frame
    const timeFrame = ref(localStorage.getItem('bug_tracker_timeframe') || 'sprint'); // sprint, 30d, custom
    const dateRange = ref({
        start: localStorage.getItem('bug_tracker_date_start') || null,
        end: localStorage.getItem('bug_tracker_date_end') || null
    });

    // Subscriptions to persist
    watch(viewMode, (val) => localStorage.setItem('bug_tracker_view_mode', val));
    watch(selectedProject, (val) => localStorage.setItem('bug_tracker_project_id', val || ''));
    watch(selectedModule, (val) => localStorage.setItem('bug_tracker_module_id', val || ''));
    watch(timeFrame, (val) => localStorage.setItem('bug_tracker_timeframe', val));
    watch(dateRange, (val) => {
        localStorage.setItem('bug_tracker_date_start', val.start || '');
        localStorage.setItem('bug_tracker_date_end', val.end || '');
    }, { deep: true });

    return {
        viewMode,
        selectedProject,
        selectedModule,
        timeFrame,
        dateRange
    };
});
