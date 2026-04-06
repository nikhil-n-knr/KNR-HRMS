<template>
    <div class="min-h-screen bg-slate-50 font-inter text-slate-900">
        <!-- Navigation -->
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <Link :href="route('portal.dashboard')" class="flex items-center gap-2 sm:gap-3 text-slate-500 hover:text-emerald-600 transition-all group">
                        <div class="h-10 w-10 bg-slate-50 group-hover:bg-emerald-50 rounded-xl flex items-center justify-center transition-colors">
                            <ArrowLeftIcon class="w-5 h-5" />
                        </div>
                        <span class="font-black text-sm sm:text-xs uppercase tracking-widest hidden xs:inline">Back to Hub</span>
                    </Link>
                    <div class="flex flex-col items-end overflow-hidden max-w-[200px] sm:max-w-none">
                        <h1 class="text-lg sm:text-xl font-black tracking-tighter text-slate-900 leading-none truncate">Intelligence Feed</h1>
                        <span class="text-sm sm:text-sm font-black text-emerald-500 uppercase tracking-widest mt-1">Issue Reporting</span>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-3xl w-full mx-auto px-4 py-6 sm:py-12">
            <div class="bg-white rounded-[1.5rem] sm:rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.03)] border border-slate-100 overflow-hidden">
                <div class="bg-emerald-600 p-6 sm:p-10 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight mb-2">Issue Submission</h2>
                        <p class="text-emerald-100 text-xs sm:text-sm font-medium">Provide precise details for expedited priority resolution.</p>
                    </div>
                    <div class="absolute -right-10 -bottom-10 h-40 w-40 bg-white/10 rounded-full blur-2xl"></div>
                </div>

                <form @submit.prevent="submit" class="p-6 sm:p-10 space-y-6 sm:space-y-8">
                    <!-- Project & Module -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Target Project</label>
                            <div class="relative">
                                <select v-model="form.project_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-4 py-4 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                                    <option :value="null" disabled>Select active project...</option>
                                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                                    <ChevronDownIcon class="h-4 w-4 text-slate-400" />
                                </div>
                            </div>
                            <p v-if="form.errors.project_id" class="text-rose-500 text-sm sm:text-sm mt-2 font-black uppercase tracking-tight">{{ form.errors.project_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Sub-Module / Stage (Optional)</label>
                            <div class="relative">
                                <select v-model="form.module_id" :disabled="!form.project_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-4 py-4 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all disabled:opacity-50 appearance-none cursor-pointer">
                                    <option :value="null">-- General / Not Sure --</option>
                                    <option v-for="m in activeModules" :key="m.id" :value="m.id">{{ m.name }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                                    <ChevronDownIcon class="h-4 w-4 text-slate-400" />
                                </div>
                            </div>
                            <p v-if="form.errors.module_id" class="text-rose-500 text-sm sm:text-sm mt-2 font-black uppercase tracking-tight">{{ form.errors.module_id }}</p>
                        </div>
                    </div>

                    <!-- Severity -->
                    <div>
                         <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4">Criticality Level</label>
                         <div class="flex flex-wrap gap-2 sm:gap-3">
                            <label v-for="sev in ['low', 'medium', 'high', 'critical']" :key="sev" 
                                   :class="[
                                        'flex-1 text-center min-w-[70px] sm:min-w-[100px] px-3 sm:px-5 py-3 rounded-2xl text-sm sm:text-sm font-black uppercase tracking-widest cursor-pointer transition-all border-2',
                                        form.severity === sev 
                                            ? (sev === 'critical' ? 'bg-rose-600 border-rose-600 text-white shadow-lg shadow-rose-100' : 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-100')
                                            : 'bg-white border-slate-100 text-slate-500 hover:border-emerald-200 hover:text-emerald-600'
                                   ]">
                                <input type="radio" v-model="form.severity" :value="sev" class="hidden" />
                                {{ sev }}
                            </label>
                         </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Brief Identifier</label>
                        <input v-model="form.subject" type="text" class="w-full bg-slate-50 border-transparent rounded-2xl px-4 py-4 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all placeholder-slate-300" placeholder="e.g. Core Engine Performance Degradation" />
                         <p v-if="form.errors.subject" class="text-rose-500 text-sm sm:text-sm mt-2 font-black uppercase tracking-tight">{{ form.errors.subject }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Root Analysis & Reproduction</label>
                        <textarea v-model="form.description" rows="5" class="w-full bg-slate-50 border-transparent rounded-2xl px-4 py-4 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all placeholder-slate-300 resize-none" placeholder="Provide reproduction steps and behavior metrics..."></textarea>
                         <p v-if="form.errors.description" class="text-rose-500 text-sm sm:text-sm mt-2 font-black uppercase tracking-tight">{{ form.errors.description }}</p>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4 sm:pt-6">
                        <button type="submit" :disabled="form.processing" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 sm:py-5 rounded-[1.25rem] sm:rounded-[1.5rem] font-black text-sm sm:text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 transition-all transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50">
                            {{ form.processing ? 'Transmitting Data...' : 'Dispatch Issue Report' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    projects: Array
});

const form = useForm({
    project_id: null,
    module_id: null,
    subject: '',
    description: '',
    severity: 'medium',
    attachments: [] 
});

const activeModules = computed(() => {
    if (!form.project_id) return [];
    return props.projects.find(p => p.id === form.project_id)?.modules || [];
});

const submit = () => {
    form.post(route('portal.tickets.store'));
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }
</style>
