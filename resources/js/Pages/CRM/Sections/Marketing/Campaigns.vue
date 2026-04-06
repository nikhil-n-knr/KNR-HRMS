<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Marketing Command Console</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left">The Growth Engine orchestrates multi-channel initiatives. Orchestrate conceptual hooks with automated deployment cycles to maximize stakeholder engagement.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Growth Cycle Optimization</span></div>
                    <div class="flex items-center gap-3 text-left"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Engagement Analytics Matrix</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left">Initiative Matrix</h2>
                        <div class="flex items-center mt-3 text-left">
                            <i class="fas fa-bullhorn text-indigo-500 mr-3 text-left"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left">Growth Engine: Functional</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                         <div class="relative w-64 group text-left font-sans">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans"></i>
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans" 
                                placeholder="Find Initiative..."
                            >
                        </div>
                        <button @click="showCreateModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans">
                            <i class="fas fa-rocket mr-2 text-xs group-hover:rotate-12 transition-transform text-left font-sans"></i>
                            Deploy New Campaign
                        </button>
                    </div>
                </div>
            </div>

            <!-- Campaigns Grid -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 text-left font-sans">
                    <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl transition-all group flex flex-col border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans">
                        <div class="p-8 flex-1 text-left font-sans">
                            <div class="flex justify-between items-start mb-6 text-left font-sans">
                                <span :class="['px-4 py-2 rounded-xl text-sm font-black uppercase tracking-widest border shadow-sm transition-all', getStatusClass(campaign.status)]">
                                    {{ campaign.status }}
                                </span>
                                <div class="flex gap-2 text-left font-sans">
                                     <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-white border border-transparent hover:border-indigo-100 flex items-center justify-center transition-all shadow-sm text-left font-sans">
                                        <i class="fas fa-eye text-xs text-left font-sans"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <h3 class="text-xl font-black text-gray-900 mb-3 leading-tight group-hover:text-indigo-600 transition-colors text-left font-sans">{{ campaign.name }}</h3>
                            <p class="text-base font-medium text-gray-500 mb-8 leading-relaxed line-clamp-2 italic text-left font-sans">"{{ campaign.subject || 'Strategic Growth Initiative Protocol' }}"</p>
                            
                            <div class="grid grid-cols-3 gap-6 border-t border-gray-50 pt-8 text-left font-sans">
                                <div class="text-left font-sans">
                                    <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2 text-left font-sans">Volume</div>
                                    <div class="text-sm font-black text-gray-900 text-left font-sans">{{ formatNumber(campaign.stats?.sent || 0) }}</div>
                                </div>
                                <div class="text-left font-sans px-4 border-l border-r border-gray-50 text-left font-sans text-left font-sans text-left font-sans">
                                    <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2 text-left font-sans">Impact</div>
                                    <div class="text-sm font-black text-indigo-600 text-left font-sans">{{ campaign.stats?.sent ? Math.round((campaign.stats.opened / campaign.stats.sent) * 100) : 0 }}%</div>
                                </div>
                                <div class="text-left font-sans text-left font-sans text-left font-sans text-left font-sans text-left font-sans">
                                    <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2 text-left font-sans text-left font-sans">Conversion</div>
                                    <div class="text-sm font-black text-emerald-500 text-left font-sans">{{ campaign.stats?.opened ? Math.round((campaign.stats.clicked / campaign.stats.opened) * 100) : 0 }}%</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50/50 px-8 py-5 border-t border-gray-50 flex justify-between items-center text-left font-sans">
                            <div class="flex items-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">
                                <i class="fas fa-calendar-day mr-3 text-indigo-400 text-left font-sans"></i>
                                {{ campaign.scheduled_at ? new Date(campaign.scheduled_at).toLocaleDateString() : 'Auto-Activation' }}
                            </div>
                            
                            <button 
                                v-if="['draft', 'scheduled'].includes(campaign.status?.toLowerCase())"
                                @click="sendCampaign(campaign.id)"
                                class="bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-lg active:scale-95 text-left font-sans"
                            >
                                FIRE ANALYTICS
                            </button>
                            <span v-else class="text-sm font-black text-indigo-400 uppercase tracking-widest text-left font-sans px-3 py-1 bg-white rounded-lg shadow-inner">{{ campaign.type || 'MULTI-CHAN' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredCampaigns.length === 0" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans">
                    <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm border border-gray-100 flex items-center justify-center mb-8 text-left font-sans">
                         <i class="fas fa-satellite-dish text-3xl opacity-20 text-left font-sans"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans">No Frequency Detected</h3>
                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans">Initiate your first growth loop to generate market frequency.</p>
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative bg-white rounded-[45px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left font-sans">
                    <div class="flex items-center text-left font-sans">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left font-sans">
                            <i class="fas fa-bullhorn text-xl text-left font-sans"></i>
                        </div>
                        <div class="text-left font-sans">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans">Deploy Strategic Hub</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left font-sans">Growth Transmission Protocol</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90 text-left font-sans">
                        <i class="fas fa-times text-left font-sans"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-10 space-y-10 text-left font-sans">
                    <div class="space-y-3 text-left font-sans">
                         <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Campaign Designation</label>
                         <input v-model="form.name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left font-sans" placeholder="e.g. Q4 Ecosystem Expansion Plan">
                    </div>
                    <div class="space-y-3 text-left font-sans">
                         <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Strategic Hook / Narrative</label>
                         <input v-model="form.subject" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left font-sans" placeholder="Redefining the standard of enterprise integration... ">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-8 text-left font-sans">
                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Visual Asset Protocol</label>
                             <select v-model="form.template_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-bold shadow-inner cursor-pointer appearance-none text-left font-sans">
                                <option :value="null">DYNAMIC CONCEPT</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                             </select>
                        </div>
                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Target Persona Hub</label>
                             <select v-model="form.segment_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-bold shadow-inner cursor-pointer appearance-none text-left font-sans">
                                <option :value="null">GLOBAL ECOSYSTEM</option>
                                <option v-for="s in segments" :key="s.id" :value="s.id">{{ s.name }}</option>
                             </select>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-between text-left font-sans">
                        <button type="button" @click="showCreateModal = false" class="text-base font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest text-left font-sans">Abort Concept</button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-12 py-5 rounded-[24px] font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 min-w-[240px] flex items-center justify-center active:scale-95 disabled:opacity-50 text-left font-sans">
                            <i class="fas fa-rocket mr-3 text-left font-sans"></i>
                            {{ form.processing ? 'ENGINEERING...' : 'INITIALIZE GROWTH HUB' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    campaigns: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
    segments: { type: Array, default: () => [] }
});

const showCreateModal = ref(false);
const searchQuery = ref('');

const filteredCampaigns = computed(() => {
    let list = props.campaigns || [];
    if (searchQuery.value) {
        const term = searchQuery.value.toLowerCase();
        list = list.filter(c => c.name?.toLowerCase().includes(term) || c.subject?.toLowerCase().includes(term));
    }
    return list;
});

const form = useForm({
    name: '',
    subject: '',
    template_id: null,
    segment_id: null,
    status: 'draft',
    scheduled_at: null,
    type: 'email'
});

const submit = () => {
    form.post(route('crm.marketing.campaigns.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const sendCampaign = (id) => {
    if (confirm('Initiate immediate deployment?')) {
        router.post(route('crm.marketing.campaigns.send', id), {}, {
            preserveScroll: true
        });
    }
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    switch(s) {
        case 'active':
        case 'completed': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-100/50';
        case 'scheduled': return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-100/50';
        case 'sending': return 'bg-purple-50 text-purple-600 border-purple-100 animate-pulse';
        case 'draft': return 'bg-gray-50 text-gray-400 border-gray-100';
        default: return 'bg-gray-50 text-gray-400 border-gray-100';
    }
};

const formatNumber = (num) => new Intl.NumberFormat('en-US').format(num || 0);
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
