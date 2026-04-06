<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 selection:bg-emerald-100 selection:text-emerald-900">
        <!-- Minimal Nav -->
        <nav class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-6 md:px-12 sticky top-0 z-50 backdrop-blur-xl">
             <Link :href="route('lms.store.catalog')" class="flex items-center gap-3 group">
                <i class="fas fa-arrow-left text-xs bg-gray-50 p-2 rounded-lg text-gray-400 group-hover:text-emerald-600 group-hover:-translate-x-1 transition-all"></i>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-gray-900">Back to Discovery</span>
             </Link>
             <div class="flex items-center gap-6">
                  <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest hidden md:block">SHARE THIS INTELLIGENCE</span>
                  <button class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 hover:text-emerald-600 transition-all">
                      <i class="fab fa-linkedin-in text-xs"></i>
                  </button>
             </div>
        </nav>

        <!-- Dynamic Hero Section -->
        <section class="bg-gray-900 text-white pt-24 pb-32 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 md:px-12 grid grid-cols-1 lg:grid-cols-2 gap-20 relative z-10">
                <div class="space-y-10">
                    <div class="flex items-center gap-4 animate-in fade-in slide-in-from-left-4 duration-1000">
                        <span class="px-4 py-1.5 bg-emerald-600 rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20">
                            {{ course.category?.name || 'SOPHISTICATED CORE' }}
                        </span>
                        <div class="flex items-center gap-1 text-orange-400 text-xs text-sm">
                            <i class="fas fa-star" v-for="i in 5" :key="i"></i>
                            <span class="ml-2 text-white/60 font-black">4.9 (2.4k Enrolled)</span>
                        </div>
                    </div>
                    
                    <h1 class="text-5xl md:text-7xl font-black tracking-tighter italic leading-[0.9] border-l-8 border-emerald-600 pl-8 uppercase">
                        {{ course.title }}
                    </h1>
                    
                    <p class="text-xl font-medium text-white/50 max-w-xl leading-relaxed font-sans">
                        {{ course.description || 'Master the deep architecture and strategic frameworks required to dominate your field in the age of intelligence.' }}
                    </p>

                    <div class="flex flex-wrap items-center gap-6 pt-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10">
                                <i class="fas fa-user-tie text-emerald-400"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-widest">Master Architect</p>
                                <p class="text-sm font-black italic tracking-wide">{{ course.creator?.name || 'System Expert' }}</p>
                            </div>
                        </div>
                        <div class="h-10 w-px bg-white/10 hidden md:block"></div>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10">
                                <i class="fas fa-language text-teal-400"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-widest">Protocol Language</p>
                                <p class="text-sm font-black tracking-wide">English (Standard)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Enrollment Card (Visual only on high screen) -->
                <div class="relative group hidden lg:block">
                     <div class="w-full bg-white rounded-[3rem] p-10 shadow-2xl space-y-8 text-gray-900 border border-white/20 transform group-hover:-translate-y-2 transition-transform duration-700">
                         <div class="aspect-video bg-gray-900 rounded-[2rem] overflow-hidden relative shadow-2xl">
                             <img v-if="course.thumbnail" :src="course.thumbnail" class="w-full h-full object-cover opacity-80" />
                             <div class="absolute inset-0 flex items-center justify-center">
                                 <div class="w-20 h-20 rounded-full bg-emerald-600 blur-sm absolute animate-pulse"></div>
                                 <button class="w-20 h-20 rounded-full bg-white text-emerald-600 flex items-center justify-center text-2xl relative shadow-xl hover:scale-110 transition-transform">
                                     <i class="fas fa-play ml-1"></i>
                                 </button>
                             </div>
                         </div>
                         
                         <div class="space-y-6 text-center">
                             <h4 class="text-4xl font-black tracking-tighter tabular-nums underline decoration-emerald-200 decoration-8 italic uppercase">FREE <span class="text-gray-300 text-lg align-middle line-through ml-2">₹19,999</span></h4>
                             <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 py-1.5 rounded-xl">7-Day Access Included with Free Tier</p>
                             
                             <div class="space-y-3">
                                 <Link :href="route('lms.store.checkout', { course: course.id })" method="post" as="button" class="w-full py-5 bg-emerald-600 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100 block text-center">
                                     Initiate Enrollment
                                 </Link>
                             </div>
                             
                             <div class="pt-4 border-t border-gray-50 grid grid-cols-2 gap-4">
                                 <div class="text-left space-y-1">
                                     <p class="text-[9px] font-black text-gray-400 tracking-widest uppercase">Content Density</p>
                                     <p class="text-xs font-black">{{ course.modules?.length }} Modules</p>
                                 </div>
                                 <div class="text-left space-y-1">
                                     <p class="text-[9px] font-black text-gray-400 tracking-widest uppercase">Integrity Score</p>
                                     <p class="text-xs font-black">98.4% Verified</p>
                                 </div>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
            
            <!-- Abstract Grid BG -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 30px 30px;"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-emerald-600/20 rounded-full blur-[150px]"></div>
        </section>

        <!-- Main Content Matrix -->
        <main class="max-w-7xl mx-auto px-6 md:px-12 py-32 grid grid-cols-1 lg:grid-cols-3 gap-24 font-sans">
            <div class="lg:col-span-2 space-y-24">
                <!-- Syllabus Section -->
                <section class="space-y-12">
                     <div class="flex items-end justify-between border-b border-gray-100 pb-8">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">Deep Syllabus</h3>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mastery sequence architecture</p>
                        </div>
                        <div class="hidden md:flex gap-4">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ course.modules?.length }} Modules</span>
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">48+ Lectures</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div 
                            v-for="(module, mIdx) in course.modules" 
                            :key="module.id"
                            class="group bg-gray-50 rounded-[2.5rem] border border-gray-100 hover:bg-white hover:border-emerald-100 hover:shadow-2xl transition-all duration-500 overflow-hidden"
                        >
                            <div @click="toggleModule(module.id)" class="p-8 flex items-center justify-between cursor-pointer">
                                <div class="flex items-center gap-6">
                                    <div class="w-12 h-12 rounded-2xl bg-white border border-gray-100 flex items-center justify-center font-black text-emerald-600 shadow-sm transition-all duration-500"
                                        :class="expandedModules[module.id] ? 'bg-emerald-600 text-white' : 'group-hover:bg-emerald-600 group-hover:text-white'">
                                        {{ mIdx + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="text-md font-black italic uppercase tracking-tight transition-colors"
                                            :class="expandedModules[module.id] ? 'text-emerald-700' : 'group-hover:text-emerald-600'">
                                            {{ module.title }}
                                        </h4>
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1">{{ module.chapters?.length || 0 }} Chapters / Intensive Core</p>
                                    </div>
                                </div>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                                    :class="expandedModules[module.id] ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400 group-hover:bg-emerald-50 group-hover:text-emerald-600'">
                                    <i :class="expandedModules[module.id] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                                </div>
                            </div>
                            
                            <!-- Accordion Expansion Content -->
                            <div v-show="expandedModules[module.id]" class="px-8 pb-8 pt-6 border-t border-gray-100 bg-white">
                                <div v-if="module.chapters?.length" class="space-y-8">
                                    <div v-for="(chapter, cIdx) in module.chapters" :key="chapter.id" class="pl-6 border-l-2 border-emerald-100 space-y-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-[9px] font-black bg-emerald-50 text-emerald-600 px-2 py-1 rounded">CH-{{ cIdx + 1 }}</span>
                                            <h5 class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ chapter.title }}</h5>
                                        </div>
                                        
                                        <ul v-if="chapter.concepts?.length" class="space-y-3 pl-4">
                                            <li v-for="concept in chapter.concepts" :key="concept.id" class="flex items-start gap-4 group/concept cursor-pointer">
                                                <div class="mt-0.5">
                                                    <i class="far fa-play-circle text-emerald-400 text-[12px] group-hover/concept:text-emerald-600 transition-colors"></i>
                                                </div>
                                                <div>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest leading-relaxed group-hover/concept:text-emerald-700 hover:underline transition-all block">
                                                        {{ concept.title }}
                                                    </span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div v-else class="text-[10px] font-black text-gray-400 uppercase tracking-widest py-4 text-center">
                                    No deep content deployed yet for this sequence.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Instructor Section -->
                <section class="bg-gray-50 p-12 rounded-[3.5rem] border border-gray-100 relative overflow-hidden group">
                     <div class="flex flex-col md:flex-row items-center gap-12 relative z-10">
                         <div class="w-40 h-40 rounded-[2.5rem] bg-gray-200 overflow-hidden border-4 border-white shadow-2xl transform group-hover:rotate-6 transition-transform duration-700">
                               <img v-if="course.creator?.avatar" :src="course.creator.avatar" class="w-full h-full object-cover" />
                               <div v-else class="w-full h-full bg-emerald-600 flex items-center justify-center text-white text-4xl font-black">
                                   {{ course.creator?.name?.charAt(0) || 'E' }}
                               </div>
                         </div>
                         <div class="flex-1 space-y-4 text-center md:text-left">
                             <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Your Master Architect</p>
                             <h4 class="text-3xl font-black tracking-tighter italic uppercase underline decoration-emerald-200 decoration-8 underline-offset-[-2px]">{{ course.creator?.name || 'Academic Intelligence' }}</h4>
                             <p class="text-xs font-bold text-gray-500 leading-relaxed max-w-lg">
                                 Specialized in advanced cognitive learning systems and enterprise strategy for over 12 years. Has trained 50,000+ engineers globally in high-stakes technology environments.
                             </p>
                             <div class="pt-4 flex items-center justify-center md:justify-start gap-4">
                                 <div class="px-4 py-2 bg-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm">2.4k Students</div>
                                 <div class="px-4 py-2 bg-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm">12 Courses</div>
                             </div>
                         </div>
                     </div>
                </section>
            </div>

            <!-- Sidebars / Requirements / FAQs (Optional) -->
            <div class="space-y-12 h-fit lg:sticky lg:top-32 font-sans">
                 <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-8 text-slate-800">
                     <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-900 border-b border-gray-50 pb-4">Knowledge Prerequisities</h4>
                     <ul class="space-y-4">
                         <li v-for="item in ['High-level Abstract Reasoning', 'Basic Logic Fundamentals', 'Deep Learning Commitment']" :key="item" class="flex items-start gap-4">
                             <i class="fas fa-check-circle text-emerald-500 text-xs mt-0.5"></i>
                             <p class="text-[10px] font-black text-gray-500 uppercase tracking-wider">{{ item }}</p>
                         </li>
                     </ul>
                 </div>

                 <div class="bg-emerald-600 p-10 rounded-[2.5rem] text-white space-y-6 shadow-2xl shadow-emerald-100">
                     <h4 class="text-[10px] font-black uppercase tracking-widest">Enterprise Solution</h4>
                     <p class="text-[11px] font-medium text-white/70 leading-relaxed uppercase tracking-wide">Training your entire team? Get a localized dedicated instance for your organization.</p>
                     <button class="w-full py-4 bg-white text-emerald-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-50 transition-all shadow-sm">
                         Corporate Inquiry
                     </button>
                 </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    course: Object
});

const expandedModules = ref({});

const toggleModule = (id) => {
    expandedModules.value[id] = !expandedModules.value[id];
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}
</style>
