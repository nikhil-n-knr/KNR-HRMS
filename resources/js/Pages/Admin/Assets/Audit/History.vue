<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { DocumentTextIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    sessions: Object
});
</script>

<template>
    <Head title="Audit History" />

    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative text-left">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Strategic Header Terminal -->
        <div class="bg-white px-10 py-8 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-10 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.assets.audit.run')" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 shrink-0">
                    <CheckBadgeIcon class="w-6 h-6 text-emerald-600" />
                </Link>
                <div class="text-left">
                    <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Audit History</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none px-1">Physical Verification Logs</p>
                </div>
            </div>

            <div class="flex items-center gap-6 z-10">
                <Link :href="route('admin.assets.audit.run')" class="h-14 px-8 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center gap-4 active:scale-95 border border-slate-800">
                    <BoltIcon class="w-4 h-4 text-indigo-400" />
                    New Audit Scanner
                </Link>
            </div>
        </div>

        <!-- History Matrix -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-12 relative z-10">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="min-w-full text-left text-sm border-collapse">
                    <thead class="bg-slate-50 text-slate-400 uppercase border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Audit ID</th>
                            <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Location</th>
                            <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Auditor</th>
                            <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Accuracy</th>
                            <th class="px-8 py-5 text-[10px] font-bold tracking-widest">Verification Date</th>
                            <th class="px-8 py-5 text-[10px] font-bold tracking-widest text-right">Registry Report</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="session in sessions.data" :key="session.id" class="group hover:bg-slate-50/50 transition-all duration-300">
                            <td class="px-8 py-6 font-mono font-black text-indigo-600">#{{ session.id }}</td>
                            <td class="px-8 py-6 font-bold text-slate-700 uppercase tracking-tight">{{ session.location?.name }}</td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 font-black text-[10px] uppercase">
                                        {{ session.auditor?.name.substring(0, 2) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 uppercase">{{ session.auditor?.name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex-1 h-1.5 w-24 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000" 
                                            :class="session.stats.accuracy >= 100 ? 'bg-emerald-500' : 'bg-amber-500'"
                                            :style="{ width: session.stats.accuracy + '%' }"></div>
                                    </div>
                                    <span class="text-[10px] font-black tabular-nums transition-colors"
                                        :class="session.stats.accuracy >= 100 ? 'text-emerald-600' : 'text-amber-600'">
                                        {{ session.stats.accuracy }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ new Date(session.created_at).toLocaleDateString() }}</td>
                            <td class="px-8 py-6 text-right">
                                <a :href="route('admin.assets.audit.report', session.id)" target="_blank" class="inline-flex items-center gap-3 px-4 py-2 bg-white border border-slate-200 text-slate-400 rounded-xl text-[9px] font-bold uppercase tracking-widest hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-95 group/pdf">
                                    <DocumentTextIcon class="w-4 h-4 text-indigo-400 group-hover/pdf:scale-110 transition-transform" />
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        <tr v-if="sessions.data.length === 0">
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-4 opacity-30">
                                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-300">
                                        <ArchiveBoxIcon class="w-8 h-8" />
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No audit history found in local registry.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
