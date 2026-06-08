<template>
    <Head title="Personal Hub" />

    <!-- ░░ Outer Page Shell ░░ -->
    <div class="min-h-screen bg-[#f4f5fa]">

        <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
        <div class="relative overflow-hidden sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-6
                    bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

            <div class="relative z-10 px-6 sm:px-10 py-8
                        flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left -->
                <div>
                    <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">Employee Self-Service</p>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Personal Hub
                    </h1>
                    <p class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
                        Manage your digital identity, resources, and career footprint — all in one place.
                    </p>
                </div>
                <!-- Right: stat pills -->
                <div class="flex flex-wrap gap-3 shrink-0">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Quick Links</p>
                        <p class="text-3xl font-extrabold text-white leading-none">6</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Active Module</p>
                        <p class="text-2xl font-extrabold text-white leading-none">Hub</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
        <div class="mx-0 sm:mx-6 mt-5 pb-12 space-y-6">

            <!-- Quick Action Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

                <!-- Digital Identity -->
                <Link :href="auth.profileUrl"
                    class="group relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-indigo-200 transition-all overflow-hidden block">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-indigo-50 rounded-full -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 mb-0.5">Digital Identity</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">Profile &amp; Stats</p>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">Review your employment details, bank records, and personal dossier.</p>
                        <div class="flex items-center gap-1.5 text-indigo-600 text-xs font-bold">
                            Explore
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Talent Bridge -->
                <Link v-if="route().has('employee.referrals.index')" :href="route('employee.referrals.index')"
                    class="group relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 transition-all overflow-hidden block">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-blue-50 rounded-full -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 mb-0.5">Talent Bridge</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">My Referrals</p>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">Refer top talent and track their recruitment milestones in real time.</p>
                        <div class="flex items-center gap-1.5 text-blue-600 text-xs font-bold">
                            Connect
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Resource Vault -->
                <Link :href="route('employee.assets.index')"
                    class="group relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-amber-200 transition-all overflow-hidden block">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-amber-50 rounded-full -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 mb-0.5">Resource Vault</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">My Assets</p>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">Manage hardware, software licenses, and assigned resource inventory.</p>
                        <div class="flex items-center gap-1.5 text-amber-600 text-xs font-bold">
                            Inspect
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Request Desk -->
                <Link v-if="route().has('employee.requests.index')" :href="route('employee.requests.index')"
                    class="group relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-rose-200 transition-all overflow-hidden block">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-rose-50 rounded-full -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:bg-rose-500 group-hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 mb-0.5">Request Desk</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">Forms &amp; Status</p>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">Create service requests and monitor the resolution of pending items.</p>
                        <div class="flex items-center gap-1.5 text-rose-600 text-xs font-bold">
                            Apply
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Execution Desk -->
                <Link v-if="route().has('employee.work.index')" :href="route('employee.work.index')"
                    class="group relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-teal-200 transition-all overflow-hidden block">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-teal-50 rounded-full -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-4 group-hover:bg-teal-500 group-hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 mb-0.5">Execution Desk</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">My Tasks &amp; Bugs</p>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">Review assigned work, checklists, and track current delivery status.</p>
                        <div class="flex items-center gap-1.5 text-teal-600 text-xs font-bold">
                            Open
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    </div>
                </Link>

            </div>

            <!-- Internal Resources -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-base font-extrabold text-slate-900 mb-5">
                    Internal <span class="text-indigo-600">Resources</span>
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">

                    <Link :href="route('employee.payslips.index')"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 group transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-indigo-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Finance Records</p>
                            <p class="text-xs text-slate-400 font-medium">Payslips &amp; Tax</p>
                        </div>
                    </Link>

                    <Link :href="route('leave.dashboard')"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 group transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-emerald-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Time Off Hub</p>
                            <p class="text-xs text-slate-400 font-medium">Leaves &amp; Holidays</p>
                        </div>
                    </Link>

                    <Link :href="route('attendance.requests.index')"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 group transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-amber-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 group-hover:text-amber-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Timeline Log</p>
                            <p class="text-xs text-slate-400 font-medium">Attendance Corrections</p>
                        </div>
                    </Link>

                    <Link :href="route('employee.benefits.index')"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 group transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-rose-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 group-hover:text-rose-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Benefits</p>
                            <p class="text-xs text-slate-400 font-medium">Insurance &amp; Perks</p>
                        </div>
                    </Link>

                    <Link :href="route('employee.learning.index')"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 group transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-purple-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 group-hover:text-purple-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Learning Hub</p>
                            <p class="text-xs text-slate-400 font-medium">Courses &amp; Certs</p>
                        </div>
                    </Link>

                    <Link :href="route('employee.policies.index')"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 group transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-teal-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 group-hover:text-teal-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Policy Docs</p>
                            <p class="text-xs text-slate-400 font-medium">HR &amp; Compliance</p>
                        </div>
                    </Link>

                </div>
            </div>

        </div><!-- /inner body -->
    </div><!-- /outer shell -->
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    employee: Object,
    auth: Object
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>