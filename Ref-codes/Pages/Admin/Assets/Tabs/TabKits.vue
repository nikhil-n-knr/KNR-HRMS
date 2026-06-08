<script setup>
import { ref } from 'vue';
import { 
    BriefcaseIcon, 
    ChevronRightIcon, 
    CubeIcon, 
    UserIcon, 
    CalendarIcon,
    SparklesIcon,
    ArrowPathIcon,
    CheckBadgeIcon,
    ShieldCheckIcon,
    BoltIcon,
    ArrowRightIcon,
    FingerPrintIcon,
    ArchiveBoxIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    kits: Array
});
</script>

<template>
    <div class="space-y-8 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700 pb-20">
        
        <!-- Kits Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div v-if="kits && kits.length > 0" class="flex flex-col gap-6">
                    <div v-for="kit in kits" :key="kit.id" 
                        class="group relative bg-white rounded-3xl border border-slate-200 overflow-hidden hover:border-indigo-400 hover:shadow-xl transition-all duration-500">
                        
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/20 via-transparent to-transparent pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="p-8 flex flex-col xl:flex-row xl:items-center gap-8 relative z-10">
                            <!-- Kit Summary -->
                            <div class="flex items-center gap-6 xl:w-1/3 shrink-0">
                                <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                                    <BriefcaseIcon class="w-10 h-10" />
                                </div>
                                <div class="text-left">
                                    <p class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest mb-1.5">Asset Bundle</p>
                                    <h4 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">{{ kit.name }}</h4>
                                    <div class="flex items-center gap-2 mt-3">
                                         <FingerPrintIcon class="w-3.5 h-3.5 text-slate-300" />
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">{{ kit.kit_code }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Included Assets -->
                            <div class="flex-1 bg-slate-50/50 rounded-2xl border border-slate-100 p-6 transition-colors group-hover:bg-white group-hover:border-indigo-100">
                                <div class="flex items-center justify-between mb-5">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bundle Composition</span>
                                    <span class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest">{{ (kit.assets || []).length }} Assets Linked</span>
                                </div>
                                <div class="flex flex-wrap gap-2.5">
                                    <div v-for="asset in (kit.assets || []).slice(0, 4)" :key="asset.id" 
                                        class="flex items-center gap-2.5 bg-white border border-slate-100 px-4 py-2 rounded-xl shadow-sm group-hover:border-indigo-100">
                                        <CubeIcon class="w-3.5 h-3.5 text-indigo-500" />
                                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-tight">{{ asset.name }}</span>
                                    </div>
                                    <div v-if="(kit.assets || []).length > 4" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-bold uppercase tracking-widest">
                                        +{{ kit.assets.length - 4 }} MORE
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3 xl:w-fit shrink-0 justify-end">
                                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm active:scale-90 group/btn">
                                    <InformationCircleIcon class="w-6 h-6" />
                                </Link>
                                <Link :href="route('admin.assets.bulk-assign')" class="h-12 px-6 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest flex items-center gap-3 hover:bg-indigo-700 transition-all shadow-md active:scale-95 group/alloc">
                                    <BoltIcon class="w-4 h-4 text-white" />
                                    Allocate
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-16 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                     <div class="absolute inset-0 bg-slate-50/50 pointer-events-none"></div>
                     <div class="w-32 h-32 bg-white border border-slate-200 rounded-3xl flex items-center justify-center text-slate-200 mb-8 transition-all duration-700 group-hover:scale-105 relative z-10">
                         <ArchiveBoxIcon class="w-16 h-16" />
                     </div>
                     <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight relative z-10 leading-none">No Bundles Yet</h3>
                     <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-5 max-w-sm relative z-10 leading-relaxed underline decoration-slate-200 underline-offset-4">Create your first asset bundle to group related items.</p>
                     
                     <div class="mt-10 relative z-10">
                         <Link :href="route('admin.assets.create')" class="h-14 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-4 group/create active:scale-95">
                             <SparklesIcon class="w-5 h-5 text-indigo-200" />
                             Create Bundle
                         </Link>
                     </div>
                </div>
            </div>

            <!-- Kits Sidebar -->
            <div class="space-y-8">
                <div class="bg-slate-900 rounded-[2.5rem] p-8 shadow-xl border border-slate-800 relative overflow-hidden group/sidebar">
                     <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/10 via-transparent to-transparent pointer-events-none opacity-30"></div>
                     
                     <h3 class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.4em] mb-10 flex items-center gap-3 border-b border-white/5 pb-6">
                         <ShieldCheckIcon class="w-5 h-5 text-indigo-400" />
                         KIT GUIDE
                     </h3>
 
                     <div class="space-y-6 relative z-10 text-left">
                         <div class="p-6 bg-white/5 border border-white/10 rounded-3xl group/card hover:bg-white/10 transition-all">
                             <div class="flex items-center gap-4 mb-4">
                                 <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400">
                                     <CubeIcon class="w-5 h-5" />
                                 </div>
                                 <div>
                                     <p class="text-[8px] font-bold text-indigo-500 uppercase tracking-widest leading-none">Bundle Management</p>
                                     <h5 class="text-base font-black text-white uppercase tracking-tight mt-1">Atomic Kits</h5>
                                 </div>
                             </div>
                             <p class="text-[10px] font-medium text-slate-500 leading-relaxed uppercase tracking-widest">Group related assets into one assignable bundle.</p>
                         </div>
 
                         <div class="p-6 bg-white/5 border border-white/10 rounded-3xl group/card hover:bg-white/10 transition-all">
                             <div class="flex items-center gap-4 mb-4">
                                 <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                                     <BoltIcon class="w-5 h-5" />
                                 </div>
                                 <div>
                                     <p class="text-[8px] font-bold text-emerald-500 uppercase tracking-widest leading-none">Bulk Assignment</p>
                                     <h5 class="text-base font-black text-white uppercase tracking-tight mt-1">One-Click Ops</h5>
                                 </div>
                             </div>
                             <p class="text-[10px] font-medium text-slate-500 leading-relaxed uppercase tracking-widest">Assign all assets in a bundle in a single action.</p>
                         </div>
                     </div>
 
                     <div class="mt-8 p-6 bg-indigo-600 rounded-3xl text-center shadow-lg relative overflow-hidden group/promo cursor-pointer active:scale-95 transition-all border border-indigo-500/30">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <SparklesIcon class="w-16 h-16 text-white/10 absolute -right-4 -bottom-4 group-hover:scale-125 transition-transform duration-1000" />
                        <h4 class="text-lg font-black text-white uppercase tracking-tight relative z-10">Kit Registry</h4>
                        <p class="text-[8px] font-bold text-white/60 uppercase tracking-widest mt-2 relative z-10">Configure new asset bundles</p>
                     </div>
                </div>

                    <!-- Kit Stats -->
                <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 shadow-sm group">
                    <div class="space-y-6 text-left">
                         <div v-for="stat in [
                            { label: 'Active Bundles', value: (kits || []).length, color: 'text-indigo-600', bg: 'bg-indigo-500' },
                                     { label: 'Total Assets', value: (kits || []).reduce((acc, k) => acc + (k.assets || []).length, 0), color: 'text-emerald-600', bg: 'bg-emerald-500' }
                         ]" :key="stat.label" class="flex items-center justify-between">
                            <div class="flex items-center gap-4 font-bold">
                                <div class="w-1.5 h-1.5 rounded-full" :class="stat.bg"></div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ stat.label }}</span>
                            </div>
                            <span class="text-xl font-black tracking-tight" :class="stat.color">{{ stat.value }}</span>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.05);
    border-radius: 20px;
}
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.1);
}
</style>
