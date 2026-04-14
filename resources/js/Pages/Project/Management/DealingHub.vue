<template>
    <MainLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 py-2">
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-none group">
                        Dealing <span class="text-emerald-600 group-hover:text-teal-500 transition-colors">Vault</span>
                    </h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 flex items-center gap-2">
                        <i class="fas fa-gavel text-emerald-500"></i>
                        Corporate Governance • Executive Oversight hub
                    </p>
                </div>

                <div class="flex items-center gap-3">
                     <!-- Global Portfolio Metrics -->
                    <div class="hidden md:flex items-center gap-6 px-6 py-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                        <div class="text-center">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Compliance</p>
                            <h4 class="text-sm font-black text-emerald-600">{{ stats.compliance_rate }}%</h4>
                        </div>
                        <div class="w-px h-6 bg-slate-100"></div>
                        <div class="text-center">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Avg Health</p>
                            <h4 class="text-sm font-black text-slate-800">{{ stats.portfolio_health }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="bg-slate-50/50 min-h-screen py-8 pb-32">
            <div class="max-w-7xl mx-auto px-4 lg:px-8 space-y-6">
                
                <!-- Quick Search & Actions -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/80 backdrop-blur-xl p-4 rounded-3xl border border-white/60 shadow-sm">
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <div class="flex-1 md:w-80 bg-slate-50 border border-slate-100 rounded-2xl px-4 flex items-center gap-3 transition-all focus-within:border-emerald-200">
                            <i class="fas fa-search text-slate-300 text-xs text-xs"></i>
                            <input v-model="search" type="text" placeholder="Scan Portfolio (Name, Code, Client)..." 
                                class="bg-transparent border-none focus:ring-0 text-xs py-3 w-full font-bold placeholder:text-slate-300">
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button @click="exportPortfolio" class="px-5 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-xl flex items-center gap-2 group">
                             <i class="fas fa-download text-emerald-400 group-hover:text-white transition-colors"></i> Export Intelligence
                        </button>
                    </div>
                </div>

                <!-- Dealing Matrix -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[40px] border border-white/60 shadow-sm overflow-hidden">
                    <BaseDataTable :rows="filteredProjects" :columns="columns" selectable>
                        <template #cell-name="{ row }">
                            <div class="flex items-center gap-4 group cursor-pointer" @click="viewProjectDetails(row.id)">
                                <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center border border-slate-100 text-slate-400 group-hover:text-emerald-500 group-hover:bg-white transition-all">
                                    <i class="fas fa-folder-open text-xl"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800 group-hover:text-emerald-600 transition-colors">{{ row.name }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ row.code }} • {{ row.client }}</span>
                                </div>
                            </div>
                        </template>

                        <template #cell-governance="{ row }">
                            <div class="flex items-center gap-4">
                                <!-- BRD Progress -->
                                <div class="flex flex-col gap-1 w-24">
                                     <div class="flex justify-between items-center text-[8px] font-black uppercase tracking-tighter">
                                        <span class="text-slate-400">BRD Signed</span>
                                        <span :class="row.brd_signed ? 'text-emerald-600' : 'text-rose-500'">{{ row.brd_signed ? 'Verified' : 'Pending' }}</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div :class="row.brd_signed ? 'bg-emerald-500' : 'bg-slate-300'" 
                                            class="h-full transition-all duration-1000" 
                                            :style="{ width: row.brd_signed ? '100%' : '20%' }"></div>
                                    </div>
                                </div>
                                <span class="text-[9px] font-black text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">{{ row.brd_version }}</span>
                            </div>
                        </template>

                        <template #cell-health="{ row }">
                             <div class="flex items-center gap-3">
                                <div class="w-10 h-10 shrink-0">
                                     <apexchart type="radialBar" height="60" :options="getMiniChartOptions(row.health)" :series="[row.health]" />
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-[9px] font-black uppercase tracking-widest" :class="getHealthColor(row.health)">{{ row.health }}% Health</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Execution Index</p>
                                </div>
                             </div>
                        </template>

                        <template #cell-audit="{ row }">
                            <div v-if="row.last_audit" class="flex flex-col group/audit relative">
                                <span class="text-[10px] font-black text-slate-800 tracking-tight leading-none">Changed by {{ row.last_audit.by }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter mt-1">{{ row.last_audit.at }}</span>
                                
                                <!-- Tooltip on Hover -->
                                <div class="absolute bottom-full left-0 mb-2 w-48 p-3 bg-slate-900 text-white rounded-2xl text-[9px] font-bold opacity-0 group-hover/audit:opacity-100 transition-opacity pointer-events-none z-20 shadow-2xl">
                                    <p class="text-emerald-400 uppercase tracking-widest mb-1">Reason Logged:</p>
                                    <p class="leading-relaxed">"{{ row.last_audit.reason }}"</p>
                                    <div class="absolute -bottom-1 left-4 w-2 h-2 bg-slate-900 rotate-45"></div>
                                </div>
                            </div>
                            <span v-else class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">No Overrides Logged</span>
                        </template>

                        <template #cell-actions="{ row }">
                             <div class="flex items-center justify-end gap-2">
                                <button @click="openSummaryPDF(row.id)" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-white transition-all flex items-center justify-center border border-slate-100 shadow-sm active:scale-90" title="Export Summary">
                                    <i class="fas fa-file-pdf text-[10px]"></i>
                                </button>
                                <button @click="viewProjectDetails(row.id)" class="px-4 py-2 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100 shadow-sm">
                                    Deep Dive
                                </button>
                             </div>
                        </template>
                    </BaseDataTable>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';

const props = defineProps(['projects', 'stats']);

const search = ref('');

const columns = [
    { key: 'name', label: 'Dealing Identity', sortable: true },
    { key: 'governance', label: 'Compliance Protocol' },
    { key: 'health', label: 'Portfolio Health' },
    { key: 'audit', label: 'Latest Audit Artifact' },
    { key: 'actions', label: 'Executive Tools', class: 'text-right' }
];

const filteredProjects = computed(() => {
    if (!search.value) return props.projects;
    const s = search.value.toLowerCase();
    return props.projects.filter(p => 
        p.name.toLowerCase().includes(s) || 
        p.code.toLowerCase().includes(s) || 
        p.client.toLowerCase().includes(s)
    );
});

const getHealthColor = (h) => {
    if (h >= 80) return 'text-emerald-600';
    if (h >= 40) return 'text-amber-500';
    return 'text-rose-500';
};

const getMiniChartOptions = (health) => {
    const color = health >= 80 ? '#10b981' : (health >= 40 ? '#f59e0b' : '#ef4444');
    return {
        chart: { type: 'radialBar', sparkline: { enabled: true } },
        plotOptions: {
            radialBar: {
                hollow: { size: '30%' },
                dataLabels: { show: false },
                track: { background: '#f1f5f9' },
            }
        },
        colors: [color],
        stroke: { lineCap: 'round' }
    };
};

const viewProjectDetails = (id) => {
    router.get(route('projects.show', id));
};

const openSummaryPDF = (id) => {
    window.open(route('projects.portal.summary-pdf', { project: id }), '_blank');
};

const exportPortfolio = () => {
    alert('Full Portfolio CSV Export Protocol Initiated...');
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
