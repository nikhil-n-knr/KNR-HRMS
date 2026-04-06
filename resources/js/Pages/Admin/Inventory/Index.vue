<template>
    <MainLayout>
        <Head title="Inventory Matrix" />
        
        <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Resource Command Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-emerald-50 rounded-full blur-2xl group-hover:bg-emerald-100 transition-colors duration-1000"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:rotate-12 transition-transform">
                        <ArchiveBoxIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                            Resource Depository
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">Enterprise Store</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 flex items-center gap-2 leading-none">
                            <QrCodeIcon class="w-4 h-4 text-emerald-500" />
                            Tactical logistics & real-time stockpile synchronization
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 relative z-10 w-full lg:w-auto">
                    <Link :href="route().has('admin.inventory.scanner') ? route('admin.inventory.scanner') : '#'" class="h-14 w-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-95 group/scan">
                        <QrCodeIcon class="w-6 h-6 group-hover/scan:scale-110 transition-transform" />
                    </Link>
                    <button class="flex-1 lg:flex-none h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-slate-300 hover:bg-emerald-600 transition-all active:scale-95 flex items-center justify-center gap-4 group">
                        <PlusIcon class="w-5 h-5 group-hover:scale-125 transition-transform" />
                        Initialize Item
                    </button>
                </div>
            </div>

            <!-- Stock Intelligence Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in [
                    { label: 'Total SKU Nodes', value: items.data.length, icon: RectangleGroupIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                    { label: 'Low Pulse Nodes', value: items.data.filter(i => i.current_stock <= i.min_stock_level).length, icon: ExclamationTriangleIcon, color: 'text-rose-600', bg: 'bg-rose-50' },
                    { label: 'Active Circulation', value: '84%', icon: ArrowPathIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                    { label: 'Audit Compliance', value: '100%', icon: ShieldCheckIcon, color: 'text-amber-600', bg: 'bg-amber-50' }
                ]" :key="stat.label" class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all">
                    <div class="flex items-center gap-4">
                        <div :class="[stat.bg, stat.color]" class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm group-hover:rotate-12 transition-transform">
                            <component :is="stat.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1 leading-none">{{ stat.label }}</span>
                            <span class="text-xl font-black text-slate-900 tabular-nums">{{ stat.value }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource Matrix Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                <div class="absolute right-0 top-0 w-64 h-64 bg-slate-50/50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                
                <div class="overflow-x-auto relative z-10">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">SKU Identifier</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Unit Metric</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Quantum Stock</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Critical Floor</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Vital Status</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em] text-right">Command Array</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="item in items.data" :key="item.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover/row:bg-slate-900 group-hover/row:text-emerald-400 transition-all shadow-inner border border-slate-100">
                                            <CubeIcon class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover/row:text-emerald-700 transition-colors">{{ item.name }}</div>
                                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-60">ID: {{ String(item.id).padStart(5, '0') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-widest px-3 py-1 bg-slate-100 rounded-lg group-hover/row:bg-white transition-colors border border-transparent group-hover/row:border-slate-200">
                                        {{ item.unit || 'UNIT' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-lg font-black text-slate-900 tabular-nums transition-transform group-hover/row:scale-110 origin-left" :class="item.current_stock <= item.min_stock_level ? 'text-rose-600' : 'text-slate-900'">
                                            {{ item.current_stock }}
                                        </span>
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">In_Stock</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-base font-black text-slate-400 uppercase tracking-tighter tabular-nums italic">
                                        {{ item.min_stock_level }} Threshold
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div v-if="item.current_stock <= item.min_stock_level" class="px-4 py-1.5 bg-rose-50 text-rose-600 rounded-full border border-rose-100 text-xs font-black uppercase tracking-[0.2em] shadow-sm flex items-center gap-2 w-fit">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                                        CRITICAL_VOX
                                    </div>
                                    <div v-else class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 text-xs font-black uppercase tracking-[0.2em] flex items-center gap-2 w-fit">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                        OPTIMAL_LOAD
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-3 scale-90 origin-right transition-transform group-hover/row:scale-100 opacity-0 group-hover/row:opacity-100">
                                        <button class="h-10 px-4 bg-white text-emerald-600 border border-emerald-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-90">Inject_Stock</button>
                                        <button class="h-10 px-4 bg-white text-amber-600 border border-amber-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-amber-600 hover:text-white transition-all shadow-sm active:scale-90">Consume_Quantum</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="items.data.length === 0">
                                <td colspan="6" class="px-8 py-32 text-center grayscale opacity-30">
                                    <ArchiveBoxIcon class="w-20 h-20 mx-auto text-slate-300 mb-6 animate-pulse" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Matrix empty. Zero resources detected.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ArchiveBoxIcon, 
    PlusIcon, 
    QrCodeIcon, 
    RectangleGroupIcon, 
    ExclamationTriangleIcon, 
    ArrowPathIcon, 
    ShieldCheckIcon,
    CubeIcon
} from '@heroicons/vue/24/outline';

defineProps({
    items: Object
});
</script>

<style scoped>
/* Any custom layout tweaks */
</style>
