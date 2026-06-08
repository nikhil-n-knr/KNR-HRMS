<template>
    <transition name="fade">
        <div v-if="show" class="fixed inset-0 z-[300] bg-slate-900/60 backdrop-blur-xl flex items-center justify-center p-6 sm:p-12">
            <div class="bg-white w-full max-w-6xl h-full max-h-[900px] rounded-[3.5rem] shadow-2xl flex flex-col overflow-hidden animate-in zoom-in duration-500 relative border border-white/50">
                <!-- Header -->
                <header class="p-12 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
                    <div class="space-y-1">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 bg-indigo-900 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-900/20">
                                <BookOpenIcon class="h-7 w-7 text-indigo-400" />
                            </div>
                            <h2 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic -skew-x-6">Knowledge Library</h2>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-16">Verified Corporate Intel & Operational Documentation Ecosystem</p>
                    </div>
                    <button @click="$emit('close')" class="h-14 w-14 bg-slate-50 hover:bg-rose-50 hover:text-rose-600 rounded-[1.5rem] flex items-center justify-center text-slate-400 transition-all group border border-slate-100">
                        <XMarkIcon class="h-7 w-7 group-hover:rotate-90 transition-transform" />
                    </button>
                </header>

                <!-- Dynamic Search Hub -->
                <div class="px-12 py-8 bg-slate-50/50 border-b border-slate-100 flex flex-col md:flex-row gap-6 shrink-0 items-center">
                    <div class="flex-1 relative group w-full">
                        <MagnifyingGlassIcon class="absolute left-6 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within:text-indigo-600 transition-colors" />
                        <input type="text" v-model="search" placeholder="Query strategy docsets, release telemetry, or integration signatures..." 
                               class="w-full pl-16 pr-6 py-4 bg-white border border-slate-200 rounded-2xl text-[13px] font-bold focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all placeholder:text-slate-300 placeholder:italic">
                    </div>
                </div>

                <!-- Articles Grid -->
                <div class="flex-1 overflow-y-auto p-12 custom-scrollbar bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        <!-- Use kb_articles from props if available -->
                        <div v-for="item in filteredLibrary" :key="item.id" 
                             class="bg-white border border-slate-100 rounded-[2.5rem] p-10 space-y-8 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all group cursor-pointer relative overflow-hidden flex flex-col">
                             <div class="flex justify-between items-start relative z-10">
                                <div class="h-14 w-14 rounded-2xl bg-slate-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-inner">
                                    <component :is="getIcon(item.icon)" class="h-7 w-7" />
                                </div>
                                <span class="px-4 py-1.5 bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest rounded-lg shadow-lg shadow-slate-900/10">{{ item.category }}</span>
                             </div>
                             <div class="space-y-4 relative z-10 flex-1">
                                <h3 class="text-xl font-black text-slate-900 tracking-tighter leading-snug group-hover:text-indigo-600 transition-colors italic">{{ item.title }}</h3>
                                <p class="text-xs font-medium text-slate-400 line-clamp-4 leading-relaxed italic">{{ item.summary }}</p>
                             </div>
                             <div class="pt-6 border-t border-slate-50 flex items-center justify-between relative z-10">
                                <div class="flex items-center gap-2">
                                    <ClockIcon class="h-4 w-4 text-slate-300" />
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ item.read_time }} MIN READ</span>
                                </div>
                                <button class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                    <ArrowRightIcon class="h-5 w-5" />
                                </button>
                             </div>
                             <!-- Decorative Hex-Grid Background -->
                             <div class="absolute inset-0 opacity-[0.02] pointer-events-none bg-[radial-gradient(#4f46e5_1px,transparent_1px)] [background-size:20px_20px] group-hover:opacity-[0.05] transition-opacity"></div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredLibrary.length === 0" class="h-full flex flex-col items-center justify-center text-center p-20 opacity-30 grayscale">
                        <InboxIcon class="h-20 w-20 text-slate-200 mb-8" />
                        <h3 class="text-2xl font-black text-slate-400 uppercase tracking-tighter italic">Intelligence Query Returned Null</h3>
                        <p class="text-xs font-bold text-slate-300 uppercase tracking-widest mt-3">Refine your signature or request manual doc-gen</p>
                    </div>
                </div>

                <!-- Encryption Footer -->
                <footer class="p-10 bg-slate-950 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20">
                            <ShieldCheckIcon class="h-5 w-5 text-emerald-400" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-white uppercase tracking-widest leading-none">Security Status: Active</p>
                            <p class="text-[9px] font-bold text-emerald-400/60 uppercase tracking-widest mt-1">End-to-End Encrypted Knowledge Stream</p>
                        </div>
                    </div>
                    <button class="px-8 py-3 bg-white/5 border border-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:bg-white hover:text-slate-900 transition-all shadow-xl">Contact Knowledge Architect</button>
                </footer>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, computed } from 'vue';
import * as Icons from '@heroicons/vue/24/outline';
const { 
    XMarkIcon, 
    BookOpenIcon, 
    MagnifyingGlassIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    CommandLineIcon,
    ScaleIcon,
    DocumentTextIcon,
    CpuChipIcon,
    InboxIcon,
    ClockIcon
} = Icons;

const props = defineProps(['show', 'kb_articles']);
const search = ref('');

const getIcon = (iconName) => {
    return Icons[iconName] || BookOpenIcon;
};

const filteredLibrary = computed(() => {
    const list = props.kb_articles?.length ? props.kb_articles : [];
    if (!search.value) return list;
    const s = search.value.toLowerCase();
    return list.filter(i => 
        i.title.toLowerCase().includes(s) || 
        i.summary.toLowerCase().includes(s) || 
        i.category.toLowerCase().includes(s)
    );
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
