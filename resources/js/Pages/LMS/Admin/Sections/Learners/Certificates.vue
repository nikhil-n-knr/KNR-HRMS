<template>
    <div class="space-y-12 animate-fade-in font-sans">
        <!-- Conditional View Rendering -->
        <!-- Tab 3: Issued Ledger -->
        <div v-if="activeTab === 'ledger' || activeTab === 'overview' || !activeTab" class="space-y-12">
            <!-- Strategic Header -->
            <div class="flex items-center justify-between border-b border-emerald-100 pb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">ISSUED LEDGER</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global verified inventory of credentials</p>
                </div>
            </div>

            <!-- Metric Insight Grid -->
            <!-- ... (Keeping existing metrics) ... -->
            
            <!-- Activity Ledger -->
            <div class="bg-white rounded-[4rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-10 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-xl font-black text-gray-900 uppercase italic">Issued Credentials</h3>
                    <!-- ... -->
                </div>
                <!-- Table with Actions: View PDF, Revoke, Re-issue, Audit Trail -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-10 py-6 text-[9px] font-black text-gray-400 uppercase tracking-widest">Recipient Protocol</th>
                                <th class="px-10 py-6 text-[9px] font-black text-gray-400 uppercase tracking-widest">Mastery Hub</th>
                                <th class="px-10 py-6 text-[9px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-10 py-6 text-right text-[9px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="i in 3" :key="i" class="group hover:bg-emerald-50/20 transition-all">
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-5">
                                        <div class="w-10 h-10 rounded-2xl bg-gray-900 flex items-center justify-center text-white text-[10px] font-black italic shadow-lg shadow-emerald-100">JD</div>
                                        <div><p class="text-xs font-black text-gray-900 group-hover:text-emerald-700">Learner #{{ 4000 + i }}</p></div>
                                    </div>
                                </td>
                                <td class="px-10 py-8"><p class="text-xs font-black text-gray-700 uppercase italic">Advanced EV Logic</p></td>
                                <td class="px-10 py-8"><span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100">Verified</span></td>
                                <td class="px-10 py-8 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View PDF"><i class="fas fa-file-pdf text-[10px]"></i></button>
                                        <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Revoke"><i class="fas fa-ban text-[10px]"></i></button>
                                        <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Re-issue"><i class="fas fa-sync-alt text-[10px]"></i></button>
                                        <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Audit Trail"><i class="fas fa-history text-[10px]"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 1: Templates -->
        <div v-else-if="activeTab === 'templates'" class="space-y-12 animate-fade-in">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">CERTIFICATE TEMPLATES</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Designing the visual identity of mastery</p>
                </div>
                <button class="px-8 py-3 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all flex items-center gap-3">
                    <i class="fas fa-plus"></i> New Template
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div v-for="i in 4" :key="i" class="bg-white p-8 rounded-[3.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-emerald-200 transition-all group overflow-hidden relative">
                    <div class="h-40 bg-gray-50 rounded-3xl mb-6 relative overflow-hidden border border-gray-100">
                         <div class="absolute inset-0 flex items-center justify-center text-gray-200 text-5xl opacity-20"><i class="fas fa-certificate"></i></div>
                    </div>
                    <div class="space-y-2 mb-8">
                        <h4 class="text-sm font-black text-gray-900 uppercase italic">Tier-Alpha Protocol</h4>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest italic">Format: A4 Vector • Multi-signature enabled</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <button class="py-2 bg-gray-50 text-[8px] font-black text-gray-400 uppercase rounded-lg hover:bg-emerald-600 hover:text-white transition-colors" title="Edit Template">Edit</button>
                        <button class="py-2 bg-gray-50 text-[8px] font-black text-gray-400 uppercase rounded-lg hover:bg-emerald-600 hover:text-white transition-colors" title="Clone Template">Clone</button>
                        <button class="py-2 bg-gray-50 text-[8px] font-black text-gray-400 uppercase rounded-lg hover:bg-red-500 hover:text-white transition-colors" title="Archive Template">Archive</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Certificate Rules -->
        <div v-else-if="activeTab === 'rules'" class="bg-white p-20 rounded-[4rem] border border-gray-100 shadow-sm space-y-12 animate-fade-in">
            <div class="flex items-center justify-between border-b border-gray-50 pb-10">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 uppercase italic">CERTIFICATE RULES</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Attach credentials to learning vectors</p>
                </div>
            </div>
            <div class="bg-gray-50 p-12 rounded-[3rem] text-center border-2 border-dashed border-gray-200">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No active ruleset selected. Select Course to bind.</p>
            </div>
        </div>

        <!-- Verification Protocol Section -->
        <div v-else-if="activeTab === 'verify'" class="bg-gray-900 p-20 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
            <div class="flex flex-col items-center text-center space-y-10 relative z-10">
                <div class="w-24 h-24 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/20 flex items-center justify-center text-white text-4xl animate-pulse">
                    <i class="fas fa-shield-check"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black italic tracking-tighter uppercase mb-2 leading-none border-b-2 border-emerald-500 pb-4">Verification Intelligence v2</h3>
                    <p class="text-sm font-medium text-white/50 lowercase tracking-widest italic font-sans max-w-xl mx-auto">Initializing audit sequence for global public-facing keys and blockchain validation ports. Latency: 0.12ms</p>
                </div>
                <div class="w-64 h-2 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 w-[80%]"></div>
                </div>
            </div>
            <!-- Abstract Ornament -->
            <div class="absolute -right-20 -top-20 w-[4000px] h-[4000px] bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none"></div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    activeTab: String
});

const certStats = [
    { label: 'Blockchain Keys', value: '4,2k', icon: 'fas fa-key', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Issued Credits', value: '24k+', icon: 'fas fa-medal', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Revocation Rate', value: '0.04%', icon: 'fas fa-ban', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Verification Load', value: '1.2/m', icon: 'fas fa-microchip', bg: 'bg-emerald-50 text-emerald-600' },
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>
