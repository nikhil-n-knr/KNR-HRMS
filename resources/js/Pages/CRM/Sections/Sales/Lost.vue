<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Churn Intelligence</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left">The Post-Mortem Engine identifies systemic friction points. Each lost opportunity is a dataset for refining our strategic approach and preventing future revenue leakage.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left"><div class="w-2 h-2 rounded-full bg-rose-500 shadow-lg shadow-rose-500/50"></div> <span>Churn Pattern Analysis</span></div>
                    <div class="flex items-center gap-3 text-left"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Strategic Gap Identification</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left text-left">Post-Mortem Engine</h2>
                        <div class="flex items-center mt-3 text-left text-left">
                            <i class="fas fa-shield-alt text-rose-500 mr-3 text-left"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left text-left">{{ deals.length }} churn events analyzed in current cycle</p>
                        </div>
                    </div>

                    <div class="flex gap-3 text-left text-left">
                         <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative">
                            <i class="fas fa-print text-sm text-left"></i>
                            <div class="absolute -top-12 bg-gray-900 text-white text-sm font-black px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50 text-left">Print Analysis</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Analytics Area -->
            <div class="px-8 py-10 no-print bg-white/50 border-b border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Revenue Churn Highlight -->
                    <div class="col-span-1 md:col-span-2 bg-gradient-to-br from-gray-800 via-gray-900 to-black p-10 rounded-[40px] shadow-2xl shadow-gray-200 text-white relative overflow-hidden group">
                        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700 text-left"></div>
                        <div class="relative text-left text-left">
                            <div class="text-sm font-black uppercase tracking-[0.3em] text-gray-400 mb-4 text-left text-left">Aggregate Revenue Leakage</div>
                            <div class="text-5xl font-black tracking-tighter text-left text-left">${{ formatNumber(totalLostValue) }}</div>
                            <div class="mt-8 flex items-center bg-white/10 px-4 py-2 rounded-2xl w-fit backdrop-blur-sm border border-white/10 text-left text-left text-left">
                                <span class="w-2 h-2 bg-rose-500 rounded-full mr-3 animate-pulse text-left"></span>
                                <span class="text-sm font-black uppercase tracking-widest text-left text-left text-left">Post-Mortem Protocol Engaged</span>
                            </div>
                        </div>
                    </div>

                    <!-- Volume Card -->
                    <div class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-2xl hover:shadow-gray-500/10 transition-all group overflow-hidden relative border-l-4 border-l-rose-500">
                        <div class="text-left relative text-left">
                            <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4 text-left text-left text-left">Churn Frequency</div>
                            <div class="text-4xl font-black text-gray-900 tracking-tighter text-left text-left text-left">{{ deals.length }}</div>
                        </div>
                        <div class="mt-8 text-sm font-black text-rose-500 uppercase tracking-widest flex items-center gap-2 text-left text-left text-left">
                            <i class="fas fa-chart-pie text-left"></i>
                            Negative Delta Identification
                        </div>
                    </div>

                    <!-- Retention Insight -->
                    <div class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-2xl hover:shadow-gray-500/10 transition-all group overflow-hidden relative border-l-4 border-l-indigo-500">
                        <div class="text-left relative text-left">
                            <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4 text-left text-left text-left text-left">Average Lost Yield</div>
                            <div class="text-4xl font-black text-gray-900 tracking-tighter text-left text-left text-left text-left">${{ formatNumber(deals.length ? totalLostValue / deals.length : 0) }}</div>
                        </div>
                        <div class="mt-8 text-sm font-black text-indigo-500 uppercase tracking-widest flex items-center gap-2 text-left text-left text-left text-left text-left">
                            <i class="fas fa-shield-alt text-left"></i>
                            Retention Strategy Required
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="flex-1 overflow-auto p-8 text-left" id="print-area">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden text-left">
                     <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/20 text-left">
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left">Churn Analysis Ledger</h3>
                        <div class="flex items-center gap-4 text-left">
                             <div class="relative w-64 group text-left">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-rose-500 transition-colors text-left text-left"></i>
                                <input type="text" placeholder="Search churn events..." class="w-full pl-11 pr-4 py-3 bg-white border-gray-100 border rounded-xl text-xs font-black focus:ring-4 focus:ring-rose-500/10 transition-all placeholder-gray-400 shadow-inner text-left text-left">
                            </div>
                        </div>
                     </div>
                     
                     <table class="min-w-full divide-y divide-gray-50 text-left">
                        <thead class="bg-white text-left font-sans text-left">
                            <tr>
                                <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans text-left">Opportunistic Target</th>
                                <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans text-left text-left">Lost Valuation</th>
                                <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans text-left text-left">Cycle Breakpoint</th>
                                <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans text-left text-left">Primary Friction Reason</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50 text-left font-sans text-left">
                            <tr v-for="deal in deals" :key="deal.id" class="hover:bg-rose-50/10 transition-all group border-l-4 border-l-transparent hover:border-l-rose-600 text-left font-sans text-left text-left">
                                <td class="px-10 py-6 text-left font-sans text-left text-left">
                                    <div class="flex items-center text-left font-sans text-left text-left">
                                         <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 shadow-inner group-hover:bg-rose-600 group-hover:text-white transition-all text-left font-sans text-left text-left text-left text-left">
                                             <i class="fas fa-times-circle text-xs text-left"></i>
                                         </div>
                                         <div class="ml-4 text-left font-sans text-left text-left">
                                            <div class="text-sm font-black text-gray-900 text-left font-sans text-left text-left">{{ deal.title }}</div>
                                            <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1 text-left font-sans text-left text-left text-left">Archived on {{ formatDate(deal.updated_at) }}</div>
                                         </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-center text-left font-sans text-left text-left text-left">
                                    <div class="text-lg font-black text-gray-700 text-left font-sans text-left text-left text-left">${{ formatNumber(deal.value) }}</div>
                                    <div class="text-sm font-black text-rose-500 uppercase tracking-widest mt-1 text-left font-sans text-left text-left text-left">LIQUIDATED VALUE</div>
                                </td>
                                <td class="px-10 py-6 text-center text-left font-sans text-left text-left text-left text-left">
                                     <div class="px-4 py-1.5 bg-gray-50 text-gray-500 rounded-xl border border-gray-100 text-sm font-black uppercase tracking-widest inline-block text-left font-sans text-left text-left text-left text-left">
                                        {{ deal.stage || 'Discovery Phase' }}
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-left font-sans text-left text-left text-left text-left">
                                    <div class="flex items-center gap-3 text-left font-sans text-left text-left text-left text-left">
                                        <div class="w-2 h-2 rounded-full bg-rose-400 text-left"></div>
                                        <span class="text-xs font-bold text-gray-500 italic text-left font-sans text-left text-left text-left text-left">{{ deal.loss_reason || 'Strategic Alignment Gap' }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                     </table>

                     <!-- Empty State -->
                     <div v-if="deals.length === 0" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans text-left text-left">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm border border-gray-100 flex items-center justify-center mb-8 text-left font-sans text-left text-left">
                             <i class="fas fa-shield-virus text-3xl opacity-20 text-left font-sans text-left text-left"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans text-left text-left text-left text-left">Infinite Retention</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans text-left text-left text-left text-left text-left">No churn events recorded. Zero-loss protocol remains active.</p>
                     </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    deals: { type: Array, default: () => [] }
});

const totalLostValue = computed(() => props.deals.reduce((sum, deal) => sum + Number(deal.value || 0), 0));
const formatNumber = (num) => new Intl.NumberFormat('en-US').format(num || 0);
const formatDate = (date) => new Date(date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>
