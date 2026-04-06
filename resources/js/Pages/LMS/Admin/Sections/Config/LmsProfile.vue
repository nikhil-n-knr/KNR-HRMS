<template>
    <div class="space-y-12 animate-fade-in font-sans pb-24">
        <!-- Conditional View Rendering -->
        <div v-if="activeTab === 'meta' || !activeTab" class="space-y-12">
            <!-- Strategic Header -->
            <div class="flex items-end justify-between border-b border-emerald-100 pb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">SYSTEM META-IDENTITY</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Master organizational branding and core system governance</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="px-8 py-4 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all flex items-center gap-3 transform active:scale-95">
                        <i class="fas fa-save font-light"></i> Commit Global Changes
                    </button>
                </div>
            </div>

            <!-- Identity Forge Content -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
                <div class="xl:col-span-2 space-y-10">
                    <!-- Branding Protocol Card -->
                    <div class="bg-white p-12 rounded-[4rem] border border-gray-100 shadow-sm space-y-12 group hover:border-emerald-200 transition-all hover:shadow-2xl hover:shadow-emerald-900/5">
                        <div class="flex items-center justify-between">
                             <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest px-2 font-sans italic border-l-4 border-emerald-500 pl-4">Digital Identity Matrix</h4>
                             <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:rotate-12 transition-all">
                                 <i class="fas fa-fingerprint"></i>
                             </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-4">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-4 font-sans">Institute Alpha Name</label>
                                <input type="text" v-model="settings.name" class="w-full px-8 py-5 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-100 group-hover:bg-white transition-all">
                            </div>
                            <div class="space-y-4">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-4 font-sans">Primary Portal UI Color</label>
                                <div class="flex items-center gap-4">
                                     <div class="w-14 h-14 rounded-2xl bg-emerald-600 shadow-lg border-4 border-white"></div>
                                     <input type="text" v-model="settings.primary_color" class="flex-1 px-8 py-5 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-100 group-hover:bg-white transition-all tabular-nums">
                                </div>
                            </div>
                            <div class="space-y-4 md:col-span-2">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-4 font-sans">Institute Core Mission (Public Tagline)</label>
                                <textarea v-model="settings.tagline" rows="3" class="w-full px-8 py-5 bg-gray-50 border-none rounded-3xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-100 group-hover:bg-white transition-all italic leading-relaxed"></textarea>
                            </div>
                        </div>

                        <!-- Brand Assets -->
                        <div class="grid grid-cols-2 gap-8 pt-8 border-t border-gray-50">
                             <div class="h-48 rounded-[3rem] bg-gray-50 border-2 border-dashed border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-emerald-50 hover:border-emerald-200 transition-all cursor-pointer group/upload">
                                 <i class="fas fa-cloud-upload text-gray-300 group-hover/upload:text-emerald-500 text-2xl"></i>
                                 <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest group-hover/upload:text-emerald-700">Upload Portal Logo</p>
                             </div>
                             <div class="h-48 rounded-[3rem] bg-gray-50 border-2 border-dashed border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-emerald-50 hover:border-emerald-200 transition-all cursor-pointer group/upload">
                                 <i class="fas fa-image text-gray-300 group-hover/upload:text-emerald-500 text-2xl"></i>
                                 <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest group-hover/upload:text-emerald-700">Upload Hero Splash</p>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-10">
                    <!-- Integrity Shield Card -->
                    <div class="bg-gray-900 p-10 rounded-[3.5rem] text-white shadow-2xl relative overflow-hidden group">
                        <div class="relative z-10">
                            <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 italic mb-8 flex items-center justify-between">
                                Integrity Protection Hub
                                <i class="fas fa-shield-halved text-emerald-500"></i>
                            </h4>
                            <div class="space-y-8">
                                <div v-for="opt in logicOptions" :key="opt.label" class="flex items-center justify-between group/opt cursor-pointer">
                                    <div class="space-y-1">
                                         <p class="text-[9px] font-black uppercase group-hover/opt:text-emerald-400 transition-colors">{{ opt.label }}</p>
                                         <p class="text-[8px] font-medium text-white/30 uppercase tracking-widest">{{ opt.desc }}</p>
                                    </div>
                                    <div class="w-12 h-6 bg-white/10 rounded-full relative p-1 group-hover/opt:bg-emerald-600/20 transition-all">
                                        <div class="w-4 h-4 bg-emerald-500 rounded-full translate-x-6 shadow-lg shadow-emerald-500/50 transition-all"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- BG Glow -->
                        <div class="absolute -right-20 -top-20 w-[300px] h-[300px] bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none"></div>
                    </div>

                    <!-- Domain Sync Card -->
                    <div class="bg-white p-10 rounded-[3.5rem] border border-gray-100 shadow-sm space-y-6">
                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest italic">Core Domain Alpha</p>
                        <div class="p-6 bg-emerald-50 rounded-3xl flex items-center justify-between border border-emerald-100">
                             <div class="flex items-center gap-4">
                                 <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-600 shadow-sm">
                                     <i class="fas fa-globe"></i>
                                 </div>
                                 <div class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">hub.lms_alpha_core</div>
                             </div>
                             <i class="fas fa-check-circle text-emerald-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logic Thresholds Section -->
        <div v-else-if="activeTab === 'logic'" class="space-y-12">
            <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-8 border-l-4 border-emerald-500 pl-6 text-gray-900">Core Operational Thresholds</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="t in thresholds" :key="t.name" class="p-10 bg-white rounded-[4rem] border border-gray-100 shadow-md hover:shadow-2xl transition-all group overflow-hidden relative">
                    <div class="relative z-10 flex flex-col items-center justify-center gap-6">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-xl text-gray-300 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                             <i :class="t.icon"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 italic">{{ t.name }}</p>
                            <div class="text-4xl font-black italic tracking-tighter tabular-nums group-hover:text-emerald-600 transition-colors">{{ t.val }}</div>
                        </div>
                    </div>
                    <!-- Hover Glow -->
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-50 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </div>
        </div>

        <!-- Access Matrix Section -->
        <div v-else-if="activeTab === 'roles'" class="bg-white p-20 rounded-[4rem] border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-10 relative overflow-hidden group">
            <i class="fas fa-users-cog text-6xl text-emerald-400 animate-pulse transition-all group-hover:rotate-12"></i>
            <div class="text-center space-y-4 relative z-10">
                <h3 class="text-3xl font-black italic tracking-tighter uppercase leading-none border-b-2 border-emerald-500 pb-4">Global Access Matrix</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] font-sans">Role-Based Entitlement Architecture v4.2 Active</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 w-full max-w-4xl pt-10 border-t border-gray-50">
                 <div v-for="role in ['Admin', 'Syllabus Architect', 'Finance Controller', 'Student Hub']" :key="role" class="p-6 bg-gray-50 rounded-3xl text-center hover:bg-emerald-600 hover:text-white transition-all group/role cursor-pointer shadow-sm">
                     <p class="text-[9px] font-black uppercase tracking-widest italic group-hover/role:text-white">{{ role }}</p>
                 </div>
            </div>
            <!-- Abstract Ornament -->
            <div class="absolute -right-20 -top-20 w-[400px] h-[400px] bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none"></div>
        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    activeTab: String
});

const settings = ref({
    name: 'Nikhil Infotec Academy',
    primary_color: '#10B981',
    tagline: 'Empowering enterprise minds through high-fidelity intelligence immersive learning protocols and modules.',
});

const logicOptions = [
    { label: 'Neural Search Indexing', desc: 'Real-time AI-optimized discovery' },
    { label: 'Blockchain Verification', desc: 'Secure credential ledger node' },
    { label: 'Lazy Hydration', desc: 'Lighthouse-grade performance sync' },
    { label: 'Omni-Channel Auth', desc: 'Universal single sign-on security' },
    { label: 'ERP Mirroring', desc: 'Bidirectional enterprise data sync' },
];

const thresholds = [
    { name: 'Max File Payload', val: '5.0GB', icon: 'fas fa-cloud-upload' },
    { name: 'Concurrent Minds', val: '10,000+', icon: 'fas fa-users' },
    { name: 'Stream Bitrate', val: 'Premium', icon: 'fas fa-gauge-high' },
    { name: 'Session Horizon', val: '96h', icon: 'fas fa-infinity' },
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>

