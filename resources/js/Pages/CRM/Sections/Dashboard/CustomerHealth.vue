<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Health Heatmap -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="md:col-span-3 bg-white/60 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl shadow-emerald-500/5">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-black tracking-tight">Customer Health Heatmap</h2>
                        <p class="text-gray-400 text-xs font-bold mt-1 uppercase tracking-widest">Active Accounts Sentiment Matrix</p>
                    </div>
                </div>

                <div class="grid grid-cols-5 md:grid-cols-8 lg:grid-cols-12 gap-2">
                    <div v-for="acc in displayAccounts" :key="acc.id" 
                         class="aspect-square rounded-lg transition-all duration-300 transform hover:scale-125 hover:z-50 cursor-pointer shadow-sm shadow-black/5"
                         :class="[
                             (acc.score || 0) < 40 ? 'bg-gradient-to-br from-rose-500 to-rose-400 shadow-rose-200' :
                             (acc.score || 0) < 70 ? 'bg-gradient-to-br from-amber-500 to-amber-400 shadow-amber-200' :
                             'bg-gradient-to-br from-emerald-500 to-emerald-400 shadow-emerald-200'
                         ]"
                    ></div>
                </div>

                <div class="flex items-center gap-6 mt-8 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-emerald-500"></div><span class="text-sm font-black tracking-widest uppercase text-gray-500">Healthy ({{ healthCounts.healthy }})</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-amber-500"></div><span class="text-sm font-black tracking-widest uppercase text-gray-500">Neutral ({{ healthCounts.neutral }})</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-rose-500"></div><span class="text-sm font-black tracking-widest uppercase text-gray-500">At Risk ({{ healthCounts.atRisk }})</span></div>
                </div>
            </div>

            <div class="bg-gray-900 rounded-[2.5rem] p-8 text-white relative flex flex-col justify-between overflow-hidden shadow-2xl">
                <!-- Abstract Glow -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-rose-500/30 rounded-full blur-3xl animate-pulse"></div>

                <div class="relative z-10">
                    <h3 class="text-lg font-black tracking-tight">Churn Timeline</h3>
                    <p class="text-gray-400 text-sm font-bold mt-1 uppercase tracking-widest">Prediction Trend</p>
                    
                    <div class="mt-8 space-y-6">
                        <div v-for="risk in displayRisks" :key="risk.name" class="flex gap-4 items-center">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-rose-500 border border-white/10">
                                <i class="fas fa-user-slash text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black">{{ risk.name }}</h4>
                                <p class="text-sm text-gray-400 font-bold uppercase">{{ risk.reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="mt-8 px-6 py-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-black transition-all shadow-xl shadow-orange-500/30">
                    Fire Preventative Playbook
                </button>
            </div>
        </div>

        <!-- Churn Prevention Area -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="p in displayPlaybooks" :key="p.title" 
                class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-white shadow-xl flex items-center justify-between group hover:border-emerald-200 transition-all cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-all">
                        <i :class="['fas', p.icon]"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-gray-900">{{ p.title }}</h4>
                        <p class="text-sm text-gray-500 font-bold mt-0.5">{{ p.subtitle }}</p>
                    </div>
                </div>
                <button class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-emerald-500 group-hover:border-emerald-500 transition-all">
                    <i class="fas fa-play text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    accounts: { type: Array, default: () => [] },
    risks: { type: Array, default: () => [] },
    playbooks: { type: Array, default: () => [] }
});

const displayAccounts = computed(() => {
    if (props.accounts.length > 0) return props.accounts;
    // Fallback mock
    return Array.from({ length: 96 }, (_, i) => ({ id: i, name: `Account ${i}`, score: Math.floor(Math.random() * 100) }));
});

const displayRisks = computed(() => props.risks.length > 0 ? props.risks : [
    { name: 'HealthScale Inc', reason: 'Declining API Usage (22%)' },
    { name: 'FinLeap Solutions', reason: 'Unresolved High Priority Tickets' },
    { name: 'EdTechX Hub', reason: 'Overdue Subscription Invoice' },
]);

const displayPlaybooks = computed(() => props.playbooks.length > 0 ? props.playbooks : [
    { title: 'At-Risk Alert Auto-Sync', subtitle: 'Trigger for < 60 Health', icon: 'fa-bell' },
    { title: 'Success Playbook v2', subtitle: 'Onboarding & Renewal Path', icon: 'fa-chess-king' },
    { title: 'Churn Prevention Flow', subtitle: 'Retention Macro Sequences', icon: 'fa-user-shield' },
]);

const healthCounts = computed(() => {
    const accs = displayAccounts.value;
    return {
        healthy: accs.filter(a => (a.score || 0) >= 70).length,
        neutral: accs.filter(a => (a.score || 0) >= 40 && (a.score || 0) < 70).length,
        atRisk: accs.filter(a => (a.score || 0) < 40).length,
    };
});
</script>
