<template>
    <StudentLayout>
        <Head title="Public Learner Matrix" />

        <main class="max-w-5xl mx-auto px-6 py-16 md:py-24">
            <!-- Profile Header Card -->
            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl shadow-emerald-900/5 p-12 overflow-hidden relative">
                <!-- Decorative Glow -->
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-100/50 rounded-full blur-[80px]"></div>

                <div class="flex flex-col md:flex-row items-center md:items-start gap-12 relative z-10 text-center md:text-left">
                    <!-- Avatar -->
                    <div class="w-32 h-32 md:w-48 md:h-48 rounded-[3rem] bg-gradient-to-br from-emerald-500 to-teal-600 p-1.5 shadow-2xl shadow-emerald-200">
                        <div class="w-full h-full bg-white rounded-[2.8rem] overflow-hidden flex items-center justify-center text-4xl font-black text-emerald-600 border border-white/50">
                            <img v-if="student.avatar" :src="student.avatar" class="w-full h-full object-cover" />
                            <span v-else>{{ student.name.charAt(0) }}</span>
                        </div>
                    </div>

                    <div class="flex-1 space-y-6">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 rounded-full border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Verified Learner Matrix</span>
                            </div>
                            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tighter italic uppercase leading-none">{{ student.name }}</h1>
                            <p class="text-gray-400 font-medium tracking-tight">Active since {{ student.joined_at }} • Specialized in Advanced Modules</p>
                        </div>

                        <!-- Mini Stats Grid -->
                        <div class="grid grid-cols-3 gap-6 pt-4">
                            <div v-for="(val, label) in stats" :key="label" class="space-y-1">
                                <p class="text-2xl font-black text-emerald-600 tabular-nums">{{ val }}</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ label }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4 pt-4 justify-center md:justify-start">
                            <button class="px-6 py-3 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2">
                                <i class="fas fa-share-nodes"></i>
                                Share Portfolio
                            </button>
                            <button class="px-6 py-3 bg-white border border-gray-100 text-gray-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:border-emerald-200 hover:text-emerald-600 transition-all">
                                Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credentials Section -->
            <div class="mt-20 space-y-10">
                <div class="flex items-end justify-between border-b border-gray-100 pb-6">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-1">Earned Credentials</h3>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Verified blockchain-backed certificates</p>
                    </div>
                </div>

                <div v-if="certificates.length" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="cert in certificates" :key="cert.id" class="group bg-white p-8 rounded-[2.5rem] border border-gray-50 hover:border-emerald-100 hover:shadow-xl hover:shadow-emerald-900/5 transition-all duration-500">
                        <div class="flex gap-6">
                            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                                <i class="fas fa-award text-2xl"></i>
                            </div>
                            <div class="flex-1 space-y-2">
                                <h4 class="text-md font-black text-gray-900 tracking-tight uppercase italic leading-tight group-hover:text-emerald-600 transition-colors">{{ cert.course?.title }}</h4>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Issued on {{ cert.issued_at }}</p>
                                <div class="pt-4">
                                    <Link :href="route('lms.verify.certificate', { code: cert.unique_code })" class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.2em] underline underline-offset-4 decoration-emerald-200">Verify Authenticity</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-20 bg-gray-50/50 rounded-[3rem] border-2 border-dashed border-gray-100 flex flex-col items-center justify-center gap-4">
                    <div class="text-center">
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest italic">No public credentials available yet</p>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Subtle Footer -->
        <footer class="py-12 border-t border-gray-100 text-center">
             <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.4em]">Integrated Student Lifecycle Protocol v4.0</p>
        </footer>
    </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StudentLayout from '../../../Layouts/StudentLayout.vue';

defineProps({
    student: Object,
    certificates: Array,
    stats: Object
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
</style>
