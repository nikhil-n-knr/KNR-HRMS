<template>
  <MainLayout>
    <Head title="LMS Employer Transcript" />

    <div class="min-h-[calc(100vh-80px)] bg-white p-8 lg:p-12 font-sans overflow-y-auto">
        <div class="max-w-4xl mx-auto space-y-16">
            <!-- Professional Header -->
            <div class="flex items-center justify-between border-b-2 border-slate-900 pb-10">
                <div class="space-y-2">
                    <h1 class="text-4xl font-black text-slate-900 uppercase italic tracking-tighter leading-none">Official Digital Transcript</h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] font-sans">Emerald Light LMS Suite • Institutional Verification v4.0</p>
                </div>
                <div class="text-right">
                    <div class="w-16 h-16 bg-slate-900 text-white rounded-2xl flex items-center justify-center mx-auto mb-2 text-2xl shadow-xl">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <p class="text-[8px] font-black text-slate-900 uppercase tracking-widest">Verified Badge</p>
                </div>
            </div>

            <!-- Employee Info Matrix -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 border-b border-dashed border-slate-200 pb-10">
                <div v-for="(val, label) in employeeInfo" :key="label">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ label }}</p>
                    <p class="text-xs font-black text-slate-800 uppercase italic">{{ val }}</p>
                </div>
            </div>

            <!-- Certification Ledger -->
            <div class="space-y-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 uppercase italic border-l-4 border-slate-900 pl-4 tracking-tighter">Credential Registry</h3>
                    <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">Live Verification Active</span>
                </div>

                <div class="space-y-4">
                    <div v-for="cert in certificates" :key="cert.id" class="p-8 bg-slate-50 border border-slate-100 rounded-[2.5rem] flex items-center justify-between group hover:border-slate-900 transition-all">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-12 bg-white rounded-2xl border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-award text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-md font-black text-slate-900 uppercase italic leading-tight mb-1">{{ cert.title }}</h4>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">ID: {{ cert.id }} • Issued: {{ cert.issued_at }}</p>
                            </div>
                        </div>
                        <div class="hidden md:flex flex-col items-end">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Validity</p>
                            <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest leading-none">Until {{ cert.valid_until }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills Pulse -->
            <div class="bg-slate-900 p-12 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
                 <div class="relative z-10 flex flex-col md:flex-row items-center gap-12">
                     <div class="flex-1 space-y-6 text-center md:text-left">
                         <h4 class="text-2xl font-black italic uppercase tracking-tighter leading-none">Competency Signature</h4>
                         <p class="text-xs opacity-50 leading-relaxed font-bold border-b border-white/5 pb-6">Verified cross-domain technical capabilities based on neural assessment scores and laboratory task completion metrics.</p>
                         <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                             <span v-for="skill in ['EV Systems', 'Thermal Logic', 'HV Distribution']" :key="skill" class="px-4 py-2 bg-white/5 border border-white/10 rounded-full text-[9px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-colors cursor-default">
                                 {{ skill }}
                             </span>
                         </div>
                     </div>
                     <div class="w-48 h-48 rounded-full border-[10px] border-white/5 flex items-center justify-center text-emerald-400 text-4xl font-black group-hover:border-emerald-500/20 transition-all duration-1000 relative">
                         98.2
                         <div class="absolute inset-0 rounded-full border-[10px] border-emerald-500 border-t-transparent animate-spin-slow"></div>
                     </div>
                 </div>
                 <!-- Decorative elements -->
                 <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px] pointer-events-none"></div>
            </div>

            <!-- Footer Compliance -->
            <div class="flex items-center justify-between text-[9px] font-black text-slate-300 uppercase tracking-widest pt-10">
                <p>© 2026 Emerald Light Verification Protocol</p>
                <p>Hash-Verified: 8A2F...3B9C</p>
            </div>
        </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';

const props = defineProps({
    employee: Object,
    certificates: Array
});

const employeeInfo = computed(() => ({
    'Identity': props.employee.name,
    'Node ID': props.employee.id,
    'Deployment': props.employee.department,
    'Verified On': new Date().toLocaleDateString()
}));
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }

.animate-spin-slow {
    animation: spin 6s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
