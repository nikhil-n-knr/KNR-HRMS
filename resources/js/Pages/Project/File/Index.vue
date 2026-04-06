<template>
    <div class="h-full bg-white">
         <!-- Document Component -->
         <ProjectDocuments :projectId="project.id" class="h-full" />
    </div>
</template>

<script setup>
import { defineProps } from 'vue';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import { Link } from '@inertiajs/vue3';
import ProjectDocuments from '../Planner/Views/ProjectDocuments.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    }
});

defineOptions({ layout: ProjectLayout });
</script>
