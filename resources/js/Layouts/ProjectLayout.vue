<template>
    <MainLayout>
        <div v-if="project" class="flex flex-col min-h-screen md:h-[calc(100vh-4rem)]">
            <!-- Project Header -->
            <ProjectHeader :project="project" />

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50/50 p-4 md:p-6 relative">
                <slot />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ProjectHeader from '@/Components/Project/ProjectHeader.vue';

const props = defineProps({
    project: Object
});
</script>
