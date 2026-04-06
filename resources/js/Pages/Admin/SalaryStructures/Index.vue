<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CreditCardIcon, 
    PlusIcon, 
    PencilSquareIcon, 
    ChartBarIcon, 
    ShieldCheckIcon,
    Bars3CenterLeftIcon,
    CurrencyRupeeIcon,
    AcademicCapIcon
} from '@heroicons/vue/24/outline';

defineProps({
    structures: Object
});
</script>

<template>
    <Head title="Salary Architect Console" />
    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-12 font-outfit px-4 md:px-8 pb-20">
            <!-- Global Architecture Header -->
            <div class="py-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 border-b border-slate-100">
                <div class="flex items-center gap-6">
                    <div class="p-5 bg-slate-900 border border-slate-800 rounded-[2rem] text-indigo-400 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <CreditCardIcon class="w-10 h-10 relative z-10" />
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-4">
                            Structure Architect
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest shadow-sm">Core Ledger</span>
                        </h1>
                        <p class="text-base font-black text-slate-400 uppercase tracking-[0.3em] mt-2 block">Define global salary templates & compensation blueprints</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <Link :href="route('admin.salary-structures.create')" class="flex-1 lg:flex-none h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-3 group">
                        <PlusIcon class="w-5 h-5 text-indigo-400 group-hover:rotate-90 transition-transform" />
                        <span>Deploy New Structure</span>
                    </Link>
                </div>
            </div>

            <!-- Strategic Metrics Strip -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                 <div v-for="stat in [
                    { label: 'Active Templates', val: structures.data?.filter(s => s.is_active).length || 0, sub: 'Operational Blueprints', icon: ShieldCheckIcon, color: 'emerald' },
                    { label: 'Global Delta', val: '12.4%', sub: 'Avg Variance Node', icon: ChartBarIcon, color: 'indigo' },
                    { label: 'Pending Updates', val: '0', sub: 'Lifecycle Sync Status', icon: AcademicCapIcon, color: 'amber' }
                 ]" :key="stat.label" class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-6">
                    <div :class="`w-14 h-14 rounded-2xl bg-${stat.color}-50 text-${stat.color}-500 flex items-center justify-center shrink-0 shadow-sm`">
                        <component :is="stat.icon" class="w-7 h-7" />
                    </div>
                    <div>
                         <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mb-1">{{ stat.label }}</div>
                         <div class="text-2xl font-black text-slate-900 tracking-tighter">{{ stat.val }}</div>
                         <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-0.5 opacity-60">{{ stat.sub }}</div>
                    </div>
                 </div>
            </div>

            <!-- Blueprint Registry -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[500px]">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                        <Bars3CenterLeftIcon class="w-5 h-5 text-indigo-500" />
                        Template Manifest
                    </h3>
                    <div class="flex items-center gap-4">
                         <span class="text-sm font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100 uppercase tracking-widest">Live Sync Enabled</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800 text-left">
                                <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Structure ID & Alias</th>
                                <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Component Matrix</th>
                                <th class="px-8 py-5 text-center text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Protocol State</th>
                                <th class="px-8 py-5 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">System Controls</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="structure in structures.data" :key="structure.id" class="group hover:bg-slate-50/80 transition-all duration-300">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 group-hover:bg-slate-900 group-hover:text-indigo-400 flex items-center justify-center transition-all duration-500 shadow-inner">
                                            <CurrencyRupeeIcon class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <div class="text-[14px] font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ structure.name }}</div>
                                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1 italic opacity-75">" {{ structure.description || 'NO_ARCHITECTURAL_NOTES' }} "</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <div class="flex flex-col gap-2">
                                        <div class="text-base font-black text-slate-700 uppercase tracking-tighter flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                            {{ structure.components ? structure.components.length : 0 }} LOGIC_NODES
                                        </div>
                                        <div class="flex gap-4">
                                            <div class="text-sm font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                                EARN: {{ structure.components?.filter(c => c.type === 'earning').length || 0 }}
                                            </div>
                                            <div class="text-sm font-black text-rose-500 uppercase tracking-widest bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                                                DED: {{ structure.components?.filter(c => c.type === 'deduction').length || 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-center">
                                    <span class="px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-[0.2em] shadow-sm transform group-hover:scale-105 transition-all inline-block" 
                                        :class="structure.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'"
                                    >
                                        {{ structure.is_active ? 'OPERATIONAL' : 'OFFLINE' }}
                                    </span>
                                </td>
                                <td class="px-8 py-7 text-right">
                                    <Link :href="route('admin.salary-structures.edit', structure.id)" class="inline-flex h-11 px-6 bg-white border-2 border-slate-100 text-slate-400 rounded-xl text-sm font-black uppercase tracking-[0.2em] hover:bg-slate-900 hover:text-indigo-400 hover:border-slate-900 transition-all items-center gap-3 shadow-sm active:scale-95 group/btn">
                                        <PencilSquareIcon class="w-4 h-4 group-hover/btn:rotate-12 transition-transform" />
                                        Edit Blueprint
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="structures.data.length === 0">
                                <td colspan="4" class="px-8 py-32 text-center grayscale opacity-20 animate-pulse">
                                    <AcademicCapIcon class="h-24 w-24 mx-auto mb-6" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Zero salary blueprints detected in architect vault</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary Strip -->
                 <div class="px-8 py-6 bg-slate-900 border-t border-slate-800 flex justify-between items-center relative overflow-hidden rounded-b-[2.5rem]">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/5 to-transparent"></div>
                    <div class="text-sm font-black text-slate-500 uppercase tracking-[0.3em] flex items-center gap-4 relative z-10">
                        <ShieldCheckIcon class="w-5 h-5 text-indigo-400" />
                        Compensation Compliance Engine: v4.2.0_STABLE
                    </div>
                    <div class="text-sm font-black text-slate-400 uppercase tracking-widest relative z-10 italic">
                        Authorized Personnel Only • Audit Logging Active
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Any custom typography or animations */
</style>
