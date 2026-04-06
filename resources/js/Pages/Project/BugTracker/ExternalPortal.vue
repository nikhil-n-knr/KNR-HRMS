<template>
    <MainLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 py-2">
                <div class="flex flex-col">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight leading-none">Bug Tracker</h2>
                    <span class="text-sm font-black text-emerald-500 uppercase tracking-widest mt-2">External Managed Portal</span>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden lg:flex items-center gap-4 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                        <Link :href="route('profile.edit')" class="text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors">Workspace Settings</Link>
                        <span class="h-3 w-px bg-slate-200"></span>
                        <Link :href="route('profile.edit')" class="text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors">Access Logic</Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="bg-slate-50 min-h-screen">
            <ExternalPortalPartial 
                :bugs="bugs" 
                :projects="projects" 
                :open_critical_count="open_critical_count" 
                :stages="stages"
            />
        </div>

    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';
import ExternalPortalPartial from './Components/ExternalPortalPartial.vue';

defineProps(['bugs', 'projects', 'open_critical_count', 'stages']);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }
</style>
