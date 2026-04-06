<script setup>
import { 
    BriefcaseIcon, 
    PlusIcon, 
    PencilSquareIcon, 
    CursorArrowRaysIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    kits: Array,
    categories: Array
});
</script>

<template>
    <div class="space-y-8 font-outfit">
        <!-- Structural Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="kit in kits" :key="kit.id" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-8 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 group relative overflow-hidden">
                <!-- Premium Accents -->
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-50 rounded-full opacity-30 group-hover:scale-150 transition-transform duration-700"></div>
                
                <div class="relative z-10 flex flex-col h-full">
                    <!-- Kit Header -->
                    <div class="flex justify-between items-start mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg group-hover:bg-indigo-600 transition-all border-2 border-white">
                                <BriefcaseIcon class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ kit.name }}</h3>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">{{ kit.items?.length || 0 }} Items Bundled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Manifest Terminal -->
                    <div class="bg-slate-50/50 p-5 rounded-3xl border border-slate-100/50 mb-8 flex-1 group-hover:bg-white transition-colors shadow-inner">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block mb-4 border-b border-slate-200/50 pb-2">Resource Manifest</span>
                        <ul class="space-y-3">
                            <li v-for="item in kit.items" :key="item.id" class="flex justify-between items-center group/item transition-all">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 group-hover/item:scale-150 transition-transform"></div>
                                    <span class="text-sm font-black text-slate-600 uppercase tracking-tight group-hover/item:text-slate-900">{{ item.category?.name || 'GENERIC_CLASS' }}</span>
                                </div>
                                <span class="text-base font-black text-slate-900 bg-white px-2 py-0.5 rounded-lg border border-slate-100 shadow-sm tabular-nums group-hover/item:border-indigo-200 transition-colors">x{{ item.quantity }}</span>
                            </li>
                            <li v-if="!kit.items || kit.items.length === 0" class="text-sm font-black text-slate-300 italic py-2">NO_MANIFEST_DATA</li>
                        </ul>
                    </div>

                     <!-- Control Terminal -->
                     <div class="flex gap-3 relative z-10">
                        <button class="flex-[2.5] h-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] flex items-center justify-center gap-2 shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 group/btn overflow-hidden">
                            <span>Assign Bundle</span>
                            <CursorArrowRaysIcon class="h-4 w-4 text-indigo-400 group-hover/btn:translate-x-1 transition-transform" />
                        </button>
                        <button class="flex-1 h-12 bg-white border-2 border-slate-100 rounded-2xl text-slate-400 flex items-center justify-center hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                            <PencilSquareIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
             
             <!-- Strategic Injection Interface -->
             <div class="bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 p-8 flex flex-col items-center justify-center text-slate-300 hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all group cursor-pointer min-h-[340px] shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="w-20 h-20 rounded-full border-2 border-dashed border-slate-200 flex items-center justify-center mb-6 group-hover:rotate-90 group-hover:border-indigo-500 transition-all group-hover:scale-110 relative z-10">
                    <PlusIcon class="w-10 h-10 group-hover:text-indigo-600" />
                </div>
                <div class="text-center relative z-10">
                    <span class="block text-xs font-black uppercase tracking-[0.3em]">Initialize Bundle node</span>
                    <span class="block text-sm font-black text-slate-400 uppercase tracking-widest mt-2 opacity-60">Architect New Resource Sets</span>
                </div>
                
                <ChevronRightIcon class="absolute bottom-8 right-8 w-6 h-6 opacity-0 group-hover:opacity-100 translate-x--2 group-hover:translate-x-0 transition-all" />
             </div>
        </div>
    </div>
</template>

<style scoped>
.group:hover ul li {
    transform: translateX(4px);
}
</style>
