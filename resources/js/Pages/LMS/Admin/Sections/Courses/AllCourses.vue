<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Section Header with Filters -->
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-black text-gray-900 tracking-tight mb-1">Deep Inventory</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest tracking-[0.2em] leading-none mb-1.5">Manage Every Aspect of Your Courses</p>
                <div class="flex items-center gap-4 mt-6">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Published</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                        <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Draft</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 relative z-10">
                <button class="px-6 py-3 bg-gray-50 text-gray-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-100">
                    <i class="fas fa-filter mr-2"></i> Advanced Filter
                </button>
                <button class="px-8 py-3 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl flex items-center gap-3">
                    <i class="fas fa-plus-circle text-emerald-400"></i>
                    Initiate Course Build
                </button>
            </div>
            
            <!-- Abstract Background Ornament -->
            <div class="absolute -right-16 -top-16 w-32 h-32 bg-indigo-50 rounded-full opacity-50"></div>
        </div>

        <!-- Course List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            <div 
                v-for="course in courses.data" 
                :key="course.id" 
                class="group flex flex-col bg-white border border-gray-100 rounded-[2.5rem] shadow-sm hover:shadow-2xl hover:border-indigo-100 transition-all duration-500 relative overflow-hidden h-[420px]"
            >
                <!-- Course Header/Image Area -->
                <div class="h-40 relative group-hover:h-32 transition-all duration-500">
                    <div class="absolute inset-0 bg-gray-900 overflow-hidden">
                        <img v-if="course.thumbnail" :src="course.thumbnail" class="w-full h-full object-cover opacity-60 group-hover:scale-110 transition-transform duration-700" alt="Thumbnail" />
                        <div v-else class="w-full h-full bg-gradient-to-br from-indigo-600 to-blue-800 opacity-40"></div>
                        <!-- Glass Badge -->
                        <div class="absolute top-4 left-4 px-3 py-1 bg-white/20 backdrop-blur-md border border-white/20 rounded-lg text-[9px] font-black text-white uppercase tracking-widest">
                            {{ course.category?.name || 'Uncategorized' }}
                        </div>
                    </div>
                    <!-- Rapid Growth Tag -->
                    <div v-if="course.enrolled_count > 50" class="absolute -right-8 top-6 bg-emerald-500 text-white py-1 px-10 rotate-45 text-[9px] font-black uppercase tracking-widest shadow-lg">
                        Trending
                    </div>
                </div>

                <!-- Content Area -->
                <div class="p-8 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span 
                                class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full"
                                :class="course.is_published ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400'"
                            >
                                {{ course.is_published ? 'Live' : 'Draft' }}
                            </span>
                            <span v-if="course.is_public" class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                                <i class="fas fa-globe mr-1"></i> Public Storefront
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-gray-900 tracking-tight line-clamp-2 leading-tight group-hover:text-indigo-600 transition-colors uppercase italic mb-4">
                            {{ course.title }}
                        </h4>
                    </div>

                    <!-- Metrics -->
                    <div class="grid grid-cols-2 gap-6 border-t border-gray-50 pt-6 group-hover:pt-4 transition-all duration-500">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">Total Learners</span>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-gray-900 tabular-nums tracking-tighter">{{ course.enrollments_count }}</span>
                                <span class="text-[10px] text-emerald-500 font-bold bg-emerald-50 px-1.5 rounded">+12%</span>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">Content Density</span>
                            <span class="text-lg font-black text-gray-900 tabular-nums tracking-tighter">{{ course.modules_count }} Modules</span>
                        </div>
                    </div>
                </div>

                <!-- Action Drawer -->
                <div class="px-8 pb-8 flex items-center gap-3 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 delay-75">
                    <button class="flex-1 py-3 bg-indigo-50 text-indigo-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                        Deep Edit
                    </button>
                    <button class="w-12 h-12 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>

                <!-- Progress Bar at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-50">
                    <div class="h-full bg-indigo-600 transition-all duration-1000 origin-left scale-x-0 group-hover:scale-x-100"></div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="courses.links" class="mt-12 flex justify-center pb-12">
            <nav class="flex items-center gap-2 p-2 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <Link 
                    v-for="link in courses.links" 
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-6 py-2.5 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all"
                    :class="[
                        link.active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-900',
                        !link.url ? 'opacity-30 cursor-not-allowed' : ''
                    ]"
                ></Link>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    courses: Object
});
</script>
