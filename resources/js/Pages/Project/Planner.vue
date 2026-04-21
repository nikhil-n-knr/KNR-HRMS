<template>
    <div class="min-h-screen bg-slate-50 py-6">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Page Header -->
            <section class="bg-white border border-slate-200/70 shadow-sm rounded-3xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                        <div class="space-y-3">
                            <p class="text-sm uppercase tracking-[0.35em] text-indigo-100/80">Projects</p>
                            <h1 class="text-3xl lg:text-4xl font-black tracking-tight">Resource Planner</h1>
                            <p class="max-w-2xl text-sm text-indigo-100/85 leading-6">Visualize project timelines, manage resource allocation, and ensure optimal team capacity across all initiatives.</p>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">View Mode</p>
                            <p class="mt-2 text-3xl font-black capitalize">{{ currentView }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- View Controls -->
            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-5 sm:px-8 sm:py-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Display Options</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">Select Timeline View</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                @click="currentView = 'day'"
                                :class="currentView === 'day' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                class="rounded-3xl px-5 py-3 text-sm font-semibold transition"
                            >
                                Day
                            </button>
                            <button
                                @click="currentView = 'week'"
                                :class="currentView === 'week' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                class="rounded-3xl px-5 py-3 text-sm font-semibold transition"
                            >
                                Week
                            </button>
                            <button
                                @click="currentView = 'month'"
                                :class="currentView === 'month' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                class="rounded-3xl px-5 py-3 text-sm font-semibold transition"
                            >
                                Month
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gantt/Planner Area -->
            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden min-h-[520px]">
                <div class="flex items-center justify-center h-full p-8">
                    <div class="text-center space-y-6">
                        <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                            <svg class="h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Advanced Gantt Chart</h3>
                            <p class="mt-2 max-w-md mx-auto text-sm text-slate-500 leading-6">The "God Mode" Resource Planner with intelligent drag-and-drop task assignment, resource capacity visualization, and timeline conflict detection is scheduled for Phase 6.2.</p>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
                            <span class="inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-orange-500"></span>
                            Coming Soon
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
defineOptions({ layout: MainLayout });

const currentView = ref('week');
</script>
