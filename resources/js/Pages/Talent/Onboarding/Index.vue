<template>
    <TalentLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Onboarding Pipeline
                </h2>
            </div>
        </template>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl border border-gray-100 shadow-2xl p-6 md:p-8 mt-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
                <div>
                    <h3 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">Onboarding Requests</h3>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Convert hired candidates to employees</p>
                </div>
                <div class="w-full md:w-72">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Search candidates..." 
                        class="w-full bg-slate-50 border-gray-200 rounded-2xl focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm font-bold h-12 px-4 shadow-sm"
                    >
                </div>
            </div>

            <div v-if="candidates.data.length > 0" class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Candidate</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Designation</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Joined On</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="candidate in candidates.data" :key="candidate.id" class="group hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black">
                                        {{ candidate.first_name[0] }}{{ candidate.last_name[0] }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900">{{ candidate.first_name }} {{ candidate.last_name }}</div>
                                        <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ candidate.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span v-if="candidate.applications[0]?.job?.title" class="text-sm font-bold text-slate-700">
                                    {{ candidate.applications[0].job.title }}
                                </span>
                                <span v-else class="text-xs text-slate-400 italic">Not Assigned</span>
                            </td>
                            <td class="px-6 py-5">
                                <span :class="[
                                    candidate.applications[0]?.status === 'Joined' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 
                                    candidate.applications[0]?.status === 'Hired' ? 'bg-amber-50 text-amber-700 border-amber-100' : 
                                    'bg-indigo-50 text-indigo-700 border-indigo-100'
                                ]" class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full border shadow-sm">
                                    {{ candidate.applications[0]?.status }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-500">
                                    {{ candidate.applications[0]?.offer_letter?.joining_date || 'TBD' }}
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <Link 
                                    v-if="candidate.applications[0]?.status !== 'Joined'"
                                    :href="route('talent.candidates.onboard.create', candidate.id)" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-indigo-100"
                                >
                                    Initiate Setup
                                </Link>
                                <span v-else class="text-xs font-black text-emerald-600 uppercase tracking-widest">
                                    Employee Created
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="p-12 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                <div class="h-16 w-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h4 class="text-lg font-black text-slate-800">No Candidates Ready</h4>
                <p class="text-sm font-bold text-slate-400 mt-1 uppercase tracking-widest">Wait for accepted offers to appear here</p>
            </div>
            
            <!-- Pagination (if many) -->
            <div v-if="candidates.links.length > 3" class="mt-8">
                 <!-- Simple pagination links -->
            </div>
        </div>
    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    candidates: Object,
    filters: Object
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('talent.onboard.index'), { search: value }, { preserveState: true, replace: true });
}, 300));
</script>
