<template>
    <div class="px-4 md:px-8 py-3 md:py-4 bg-white border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/80 backdrop-blur-md sticky top-0 z-30 shadow-sm">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/20">
                {{ project?.name?.charAt(0) || 'P' }}
            </div>
            <div class="min-w-0">
                <h1 class="text-lg md:text-xl font-black text-gray-900 leading-tight truncate">{{ project?.name }}</h1>
                <p class="text-xs text-gray-500 flex items-center gap-2 overflow-hidden">
                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono text-[10px]">{{ project?.code }}</span>
                        <span v-if="project?.client" class="truncate font-medium">{{ project?.client?.name }}</span>
                </p>
            </div>
        </div>

        <!-- Scrollable Tabs -->
        <nav class="w-full md:w-auto flex space-x-1 bg-gray-100/80 p-1 rounded-xl overflow-x-auto scroll-smooth snap-x">
            <Link 
                :href="route('projects.index', { view: 'mine' })" 
                    class="flex-shrink-0 px-4 py-2 text-sm font-bold rounded-lg transition-colors text-gray-500 hover:text-gray-700 hover:bg-white/50 snap-start"
            >
                ← <span class="hidden md:inline ml-1">Back</span>
            </Link>
            
            <Link 
                v-for="tab in tabs" 
                :key="tab.name"
                :href="route(tab.route, project?.id ? { project: project.id } : {})"
                :class="[
                    isActive(tab)
                        ? 'bg-white text-indigo-700 shadow-md shadow-indigo-500/10'
                        : 'text-gray-500 hover:text-gray-800 hover:bg-white/40',
                    'flex-shrink-0 px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 whitespace-nowrap snap-center'
                ]"
            >
                {{ tab.name }}
            </Link>
            <!-- Spacer to ensure last item isn't clipped when scrolled -->
            <div class="flex-shrink-0 w-8 md:hidden"></div>
        </nav>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    project: Object
});

const tabs = [
    { name: 'Overview', route: 'projects.show', componentPrefix: 'Project/Show' },
    { name: 'Board', route: 'projects.board', componentPrefix: 'Project/Board' },
    { name: 'List', route: 'projects.tasks.index', componentPrefix: 'Project/Task' }, 
    { name: 'Modules', route: 'projects.modules.index', componentPrefix: 'Project/Module' }, 
    { name: 'Visual Planner', route: 'planner.index', componentPrefix: 'Project/Planner' }, 
    { name: 'Bug Tracker', route: 'bugs.index', componentPrefix: 'Project/BugTracker' },
    { name: 'Documents', route: 'projects.files', componentPrefix: 'Project/File' },
    { name: 'Reports', route: 'projects.reports', componentPrefix: 'Project/Reports' }, 
    { name: 'Templates', route: 'projects.templates.index', componentPrefix: 'Project/Template' }, 
    { name: 'War Room', route: 'projects.warroom', componentPrefix: 'Project/WarRoom' }, 
];

const isActive = (tab) => {
    const page = usePage();
    // Special handling for Planner which shares route but has query param
    if (tab.name.includes('Planner')) {
        // If we are on Planner route AND we have a project param (which is required for this view anyway)
        return page.component.startsWith('Project/Planner');
    }
    return page.component.startsWith(tab.componentPrefix);
};
</script>
