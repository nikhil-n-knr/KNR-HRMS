<script setup>
import { defineProps } from 'vue';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import ProjectOverview from './Components/ProjectOverview.vue';

const props = defineProps({
    project: Object,
    stats: Object,
    members: Array,
    contributors: Array,
    activity: Array,
    activeSprint: Object
});
</script>

<template>
    <ProjectLayout :project="project">
        <ProjectOverview v-bind="props" />
    </ProjectLayout>
</template>
