<template>
    <MainLayout title="Talent Acquisition">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Talent Acquisition
                </h2>
                <div class="flex space-x-2">
                    <slot name="actions" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Module Tabs -->
                <div class="mb-6 border-b border-gray-200 -mx-4 sm:mx-0 overflow-x-auto no-scrollbar">
                    <nav class="-mb-px flex space-x-6 px-4 sm:px-0" aria-label="Tabs">
                        <Link :href="route('talent.hub')" 
                              :class="[route().current('talent.hub') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Dashboard
                        </Link>
                        <Link :href="route('talent.jobs.index')" 
                              :class="[route().current('talent.jobs.*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Job Openings
                        </Link>
                        <Link :href="route('talent.candidates.index')" 
                              :class="[route().current('talent.candidates.*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Pipeline
                        </Link>
                        <Link :href="route('talent.screening-templates.index')" 
                              :class="[route().current('talent.screening-templates.*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Assessments
                        </Link>
                        <Link :href="route('talent.interviews.index')" 
                              :class="[route().current('talent.interviews.*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Interviews
                        </Link>
                        <Link :href="route('talent.offers.index')" 
                              :class="[route().current('talent.offers.*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Offers
                        </Link>
                        <Link :href="route('talent.onboard.index')" 
                              :class="[route().current('talent.onboard.*') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-[10px] uppercase tracking-[0.15em] transition-all']">
                            Onboarding
                        </Link>
                    </nav>
                </div>

                <!-- Content -->
                <slot />
                <AppFooter />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';
import AppFooter from '@/Components/UI/AppFooter.vue';
</script>
