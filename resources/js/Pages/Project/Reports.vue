<template>
    <ProjectLayout :project="project">
        <div class="h-full flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">Project Reports</h2>
                <div class="flex gap-2">
                     <button class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50">Export PDF</button>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                <ProjectReports :initialProjectId="project.id" :projects="[project]" />
            </div>
        </div>
    </ProjectLayout>
</template>

<script setup>
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import ProjectReports from '@/Pages/Project/Planner/Views/ProjectReports.vue';

defineProps({
    project: Object
});
</script>
