<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Marketing Intelligence</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Attribution Engine synthesizes multi-channel behavior into actionable engagement signals. Identify high-velocity segments and optimize conversion pathways.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Real-Time Engagement Mapping</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-amber-500 shadow-lg shadow-amber-500/50"></div> <span>Persona-Based Velocity Matrix</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Marketing Attribution</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                            <i class="fas fa-brain text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">AI Engagement Predictor: Active</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                         <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Analytics Surface -->
            <div class="flex-1 overflow-auto p-8 space-y-8 text-left font-sans" id="print-area">
                <!-- Intelligence Multi-Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-left font-sans">
                    <div v-for="stat in smartStats" :key="stat.label" 
                         class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 group hover:shadow-2xl transition-all relative overflow-hidden text-left border-l-4"
                         :style="`border-left-color: ${stat.hexColor}`">
                        <div class="absolute -right-4 -top-4 w-28 h-28 rounded-full opacity-0 group-hover:opacity-10 group-hover:scale-150 transition-transform duration-700" :style="`background-color: ${stat.hexColor}`"></div>
                        <div class="relative text-left font-sans">
                            <div class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4 text-left font-sans">{{ stat.label }}</div>
                            <div class="text-4xl font-black text-gray-900 tracking-tighter text-left font-sans">{{ stat.value }}</div>
                            <div :class="['mt-6 flex items-center text-sm font-black uppercase tracking-widest text-left font-sans', stat.trendUp ? 'text-emerald-500' : 'text-rose-400']">
                                <i :class="['fas mr-2 text-left font-sans', stat.trendUp ? 'fa-chart-line' : 'fa-chart-bar']"></i>
                                {{ stat.trend }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversion Funnel & Engagement Matrix -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-left font-sans">
                    <div class="bg-indigo-900 rounded-[50px] p-12 text-white shadow-2xl relative overflow-hidden group text-left font-sans border-l-8 border-l-indigo-400">
                         <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700 text-left font-sans"></div>
                         <div class="relative space-y-10 text-left font-sans">
                            <div class="flex justify-between items-center text-left font-sans">
                                <h3 class="text-2xl font-black tracking-tight text-left font-sans">Engagement Matrix</h3>
                                <div class="flex gap-3 text-left font-sans">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse text-left font-sans"></span>
                                    <span class="text-sm font-black uppercase tracking-widest opacity-60 text-left font-sans">Smart Forecast Active</span>
                                </div>
                            </div>
                            
                            <div class="space-y-8 text-left font-sans">
                                <div v-for="i in 4" :key="i" class="space-y-3 text-left font-sans group/bar">
                                    <div class="flex justify-between text-base font-black uppercase tracking-widest opacity-70 group-hover/bar:opacity-100 transition-opacity text-left font-sans">
                                        <span class="text-left font-sans">Segment Velocity P{{ i }}</span>
                                        <span class="text-left font-sans">{{ 100 - i * 15 }}% Match</span>
                                    </div>
                                    <div class="h-2.5 bg-white/10 rounded-full overflow-hidden shadow-inner text-left font-sans">
                                        <div class="h-full bg-gradient-to-r from-emerald-400 to-indigo-400 rounded-full transition-all duration-1000 group-hover/bar:shadow-lg group-hover/bar:shadow-emerald-500/20 text-left font-sans" :style="`width: ${100 - i * 15}%`"></div>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full py-5 bg-white/10 border border-white/20 rounded-[28px] font-black text-base uppercase tracking-widest hover:bg-white text-indigo-900 transition-all shadow-xl active:scale-95 text-left font-sans">GENERATE DEEP DIVE REPORT</button>
                         </div>
                    </div>

                    <div class="bg-white rounded-[50px] p-12 shadow-sm border border-gray-100 flex flex-col justify-between relative group overflow-hidden text-left font-sans border-r-8 border-r-indigo-100">
                        <i class="fas fa-microchip absolute -right-4 -top-4 text-[150px] text-gray-50 opacity-0 group-hover:opacity-100 transition-opacity duration-700 text-left font-sans"></i>
                        <div class="relative text-left font-sans">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-10 text-left font-sans text-left font-sans">Growth Distribution</h3>
                            <div class="flex items-center justify-center py-10 text-left font-sans">
                                 <div class="w-56 h-56 rounded-full border-[12px] border-indigo-50 flex items-center justify-center relative shadow-inner text-left font-sans">
                                    <div class="text-center text-left font-sans">
                                        <span class="text-4xl font-black text-indigo-600 block text-left font-sans text-left font-sans">82.4%</span>
                                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-2 text-left font-sans text-left font-sans">Mobile Persona</p>
                                    </div>
                                    <div class="absolute top-2 right-6 w-5 h-5 bg-indigo-500 rounded-full border-4 border-white shadow-lg text-left font-sans"></div>
                                 </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6 mt-10 text-left font-sans">
                                <div class="p-6 bg-gray-50 rounded-[32px] border border-gray-100 text-left font-sans">
                                     <span class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-2 text-left font-sans">Desktop Reach</span>
                                     <p class="text-2xl font-black text-gray-800 text-left font-sans">17.6%</p>
                                </div>
                                <div class="p-6 bg-indigo-50 rounded-[32px] border border-indigo-100 text-left font-sans">
                                     <span class="text-sm font-black text-indigo-400 uppercase tracking-widest block mb-2 text-left font-sans">Signal Velocity</span>
                                     <p class="text-2xl font-black text-emerald-500 text-left font-sans">+12.8%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signal Feed Table -->
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden text-left font-sans">
                     <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/20 text-left font-sans">
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans">Interactive Signal Feed</h3>
                        <div class="flex items-center gap-4 text-left font-sans">
                             <span class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-indigo-100 text-left font-sans">LIVE RADAR</span>
                        </div>
                     </div>
                     <table class="min-w-full divide-y divide-gray-100 text-left font-sans">
                        <thead class="bg-white text-left font-sans">
                            <tr>
                                <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Timestamp</th>
                                <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Subject Persona</th>
                                <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Interaction Event</th>
                                <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Attribution Source</th>
                                <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Identity Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-left font-sans">
                            <tr v-for="event in campaign_events" :key="event.id" class="hover:bg-indigo-50/10 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans">
                                <td class="px-10 py-6 whitespace-nowrap text-xs font-bold text-gray-500 text-left font-sans">{{ formatDate(event.created_at) }}</td>
                                <td class="px-10 py-6 text-left font-sans">
                                    <div class="text-sm font-black text-gray-900 text-left font-sans">{{ event.contact?.first_name || 'Anonymous' }} {{ event.contact?.last_name || 'User' }}</div>
                                    <div class="text-sm font-bold text-gray-400 opacity-60 text-left font-sans">{{ event.contact?.email || 'unidentified-signal@node.io' }}</div>
                                </td>
                                <td class="px-10 py-6 text-left font-sans">
                                    <span :class="[
                                        event.event_type === 'click' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'px-4 py-1.5 rounded-xl text-sm font-black uppercase border tracking-widest shadow-sm'
                                    ]">
                                        {{ event.event_type }}
                                    </span>
                                </td>
                                <td class="px-10 py-6 text-xs font-black text-gray-700 italic text-left font-sans">"{{ event.campaign?.title || 'Global Broadcast' }}"</td>
                                <td class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans">{{ event.ip_address || '0.0.0.0' }}</td>
                            </tr>
                        </tbody>
                     </table>
                     <div v-if="campaign_events.length === 0" class="p-32 text-center text-left font-sans">
                         <div class="w-20 h-20 bg-gray-50 rounded-[30px] flex items-center justify-center mx-auto mb-6 text-left font-sans">
                             <i class="fas fa-satellite text-3xl text-gray-200 text-left font-sans"></i>
                         </div>
                         <p class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans">Scanning for Market Frequency... No signals in current buffer.</p>
                     </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    aggregate_stats: { type: Object, default: () => ({ total: 0, opened: 0, clicked: 0 }) },
    campaign_events: { type: Array, default: () => [] }
});

const openRate = computed(() => {
    if (!props.aggregate_stats.total) return 0;
    return ((props.aggregate_stats.opened / props.aggregate_stats.total) * 100).toFixed(1);
});

const clickRate = computed(() => {
    if (!props.aggregate_stats.opened) return 0;
    return ((props.aggregate_stats.clicked / props.aggregate_stats.opened) * 100).toFixed(1);
});

const smartStats = computed(() => [
    { label: 'Outreach Reach', value: props.aggregate_stats.total?.toLocaleString() || '0', hexColor: '#6366f1', trend: 'Global Lifetime', trendUp: true },
    { label: 'Engagement Power', value: `${openRate.value}%`, hexColor: '#10b981', trend: 'Network Resonance', trendUp: true },
    { label: 'Interaction Density', value: `${clickRate.value}%`, hexColor: '#f59e0b', trend: 'Signal Strength', trendUp: true },
    { label: 'Conversion Delta', value: props.aggregate_stats.clicked?.toLocaleString() || '0', hexColor: '#8b5cf6', trend: 'Nodes Captured', trendUp: true },
]);

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
