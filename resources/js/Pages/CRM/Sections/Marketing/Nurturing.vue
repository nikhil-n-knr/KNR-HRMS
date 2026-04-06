<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Nurture Automation Engine</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Lifecycle Core orchestrates autonomous engagement sequences. Define trigger-based logic chains to maintain persistent stakeholder resonance through the revenue funnel.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Autonomous Sequence Logic</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Real-Time Cohort Enrollment</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Lifecycle Automation</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                            <i class="fas fa-sitemap text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Autonomous Orchestrator: Online</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                        <button @click="showModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans">
                            <i class="fas fa-plus mr-2 text-xs group-hover:rotate-90 transition-transform text-left font-sans"></i>
                            Engineer New Workflow
                        </button>
                    </div>
                </div>
            </div>

            <!-- Workflows Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden text-left font-sans">
                     <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/20 text-left font-sans">
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans">Sequence Inventory</h3>
                        <div class="flex items-center gap-4 text-left font-sans">
                             <div class="relative w-64 group text-left font-sans">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans"></i>
                                <input type="text" placeholder="Filter sequences..." class="w-full pl-11 pr-4 py-3 bg-white border-gray-100 border rounded-xl text-sm font-black uppercase focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans">
                            </div>
                        </div>
                     </div>
                     
                     <table class="min-w-full divide-y divide-gray-100 text-left font-sans">
                        <thead class="bg-white text-left font-sans">
                            <tr>
                                <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans font-sans">Sequence Configuration</th>
                                <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans font-sans">Trigger Protocol</th>
                                <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans font-sans">Cohort Load</th>
                                <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans font-sans">State</th>
                                <th class="px-10 py-6 text-right text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans font-sans">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50 text-left font-sans">
                            <tr v-for="flow in workflows" :key="flow.id" class="hover:bg-indigo-50/10 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans">
                                <td class="px-10 py-8 whitespace-nowrap text-left font-sans font-sans">
                                    <div class="flex items-center text-left font-sans">
                                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all text-left font-sans">
                                            <i class="fas fa-magic text-left font-sans"></i>
                                        </div>
                                        <div class="ml-5 text-left font-sans">
                                            <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors text-left font-sans">{{ flow.name }}</div>
                                            <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1 text-left font-sans">{{ flow.steps_count }} Integrated Logic Nodes</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-left font-sans font-sans">
                                    <div class="flex items-center text-sm font-black text-gray-700 text-left font-sans font-sans">
                                        <i class="fas fa-bolt mr-2 text-amber-500 text-left font-sans"></i>
                                        {{ flow.trigger_type.toUpperCase() }}
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-center text-left font-sans font-sans">
                                    <span class="text-sm font-black text-gray-900 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100 shadow-inner text-left font-sans">{{ flow.active_executions_count }}</span>
                                    <div class="text-xs font-black text-gray-300 uppercase mt-2 text-left font-sans">Active Enrollments</div>
                                </td>
                                <td class="px-10 py-8 text-center text-left font-sans font-sans">
                                    <button @click="toggleWorkflow(flow)" :class="[flow.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-100/30' : 'bg-gray-100 text-gray-400 border-gray-200', 'px-4 py-2 rounded-xl text-sm font-black uppercase tracking-widest border transition-all active:scale-95']">
                                        {{ flow.is_active ? 'FUNCTIONAL' : 'PAUSED' }}
                                    </button>
                                </td>
                                <td class="px-10 py-8 text-right whitespace-nowrap text-left font-sans font-sans">
                                    <div class="flex justify-end gap-3 text-left font-sans">
                                        <button @click="openStepBuilder(flow)" class="w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 flex items-center justify-center transition-all shadow-sm group/btn relative text-left font-sans">
                                            <i class="fas fa-project-diagram text-xs text-left font-sans text-left font-sans"></i>
                                            <div class="absolute -top-10 right-0 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover/btn:opacity-100 transition-opacity whitespace-nowrap text-left font-sans">Architect Logic</div>
                                        </button>
                                        <button class="w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-rose-500 hover:border-rose-100 flex items-center justify-center transition-all shadow-sm text-left font-sans">
                                            <i class="fas fa-trash text-xs text-left font-sans"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                     </table>

                     <!-- Empty State -->
                     <div v-if="workflows.length === 0" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm border border-gray-100 flex items-center justify-center mb-8 text-left font-sans text-left font-sans">
                             <i class="fas fa-network-wired text-3xl opacity-20 text-left font-sans text-left font-sans text-left font-sans"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans text-left font-sans">Null Automation Matrix</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans text-left font-sans text-left font-sans">No autonomous sequences active. Initialize a cycle to automate growth.</p>
                     </div>
                </div>
            </div>
        </div>

        <!-- NEW CYCLE MODAL -->
        <div v-if="showModal" @click.self="showModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
             <div class="relative bg-white rounded-[45px] shadow-2xl max-w-md w-full overflow-hidden border border-white text-left p-12 animate-in zoom-in-95 duration-300">
                 <div class="flex justify-between items-start mb-10 text-left font-sans">
                     <div class="w-16 h-16 bg-indigo-600 text-white rounded-3xl flex items-center justify-center shadow-xl shadow-indigo-100 text-left font-sans">
                         <i class="fas fa-brain text-2xl text-left font-sans text-left font-sans"></i>
                     </div>
                     <button @click="showModal = false" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-gray-900 flex items-center justify-center transition-all hover:rotate-90 text-left font-sans">
                         <i class="fas fa-times text-left font-sans"></i>
                     </button>
                 </div>
                 <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight text-left font-sans">Cycle Architect</h3>
                 <p class="text-xs text-gray-500 font-medium leading-relaxed mb-10 text-left font-sans text-left font-sans">Define the strategic entry point for this autonomous resonance sequence.</p>
                 
                 <div class="space-y-8 text-left font-sans">
                    <div class="space-y-3 text-left font-sans">
                        <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Sequence Identifier</label>
                        <input v-model="flowForm.name" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-6 text-sm font-semibold shadow-inner text-left font-sans" placeholder="e.g. Enterprise Welcome Series">
                    </div>
                    <div class="space-y-3 text-left font-sans">
                        <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Trigger Protocol</label>
                        <select v-model="flowForm.trigger_type" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-6 text-sm font-bold shadow-inner cursor-pointer appearance-none text-left font-sans">
                            <option value="contact_created">Identity Genesis (Created)</option>
                            <option value="tag_added">Signal Detected (Tag Applied)</option>
                            <option value="form_submitted">Data Sink Input (Form)</option>
                        </select>
                    </div>
                    <button @click="createWorkflow" :disabled="loading" class="w-full bg-gray-900 text-white py-5 rounded-[28px] font-black text-base tracking-widest uppercase shadow-xl hover:bg-indigo-600 transition-all flex items-center justify-center active:scale-95 disabled:opacity-50 text-left font-sans">
                        <i class="fas fa-microchip mr-3 text-left font-sans"></i> 
                        <span v-if="loading">INITIALIZING PROTOCOL...</span>
                        <span v-else>ACTIVATE SEQUENCE CORE</span>
                    </button>
                 </div>
             </div>
        </div>

        <!-- LOGIC ARCHITECT MODAL -->
        <div v-if="showStepBuilder" @click.self="showStepBuilder = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
             <div class="relative bg-white rounded-[45px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white text-left animate-in slide-in-from-bottom-5 duration-300">
                <div class="bg-gray-50/50 p-12 border-b border-gray-100 text-left font-sans">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans">Logic Orchestrator</h3>
                    <p class="text-sm font-black text-indigo-600 uppercase tracking-[0.2em] mt-2 text-left font-sans">ACTIVE CORE: {{ activeFlow?.name }}</p>
                </div>
                
                <div class="p-12 space-y-10 text-left font-sans overflow-y-auto max-h-[60vh] text-left font-sans">
                    <!-- Steps Chain -->
                    <div class="space-y-4 text-left font-sans">
                        <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Integrated Logic Nodes</label>
                        <div v-if="activeFlow?.steps?.length" class="space-y-4 text-left font-sans">
                            <div v-for="(step, sIdx) in activeFlow.steps" :key="step.id" class="flex items-center gap-6 bg-white p-6 rounded-[32px] border border-gray-100 group hover:shadow-xl transition-all border-l-4 border-l-indigo-600 text-left font-sans">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-base font-black shadow-inner border border-indigo-100 text-indigo-600 text-left font-sans">{{ sIdx + 1 }}</div>
                                <div class="flex-1 text-left font-sans">
                                    <div class="text-base font-black text-gray-900 uppercase tracking-widest text-left font-sans">{{ step.type.replace('_', ' ') }}</div>
                                    <div class="text-sm font-bold text-gray-400 mt-1 text-left font-sans">Interval: {{ step.delay_days }} Days Latency</div>
                                </div>
                                <button class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-300 hover:text-rose-500 text-left font-sans">
                                    <i class="fas fa-trash-alt text-left font-sans text-left font-sans"></i>
                                </button>
                            </div>
                        </div>
                        <div v-else class="py-16 text-center border-3 border-dashed border-gray-100 rounded-[40px] text-left font-sans">
                            <p class="text-sm font-black text-gray-300 uppercase tracking-[0.2em] text-left font-sans">Logic Void: Insert nodes to define sequence resonance.</p>
                        </div>
                    </div>

                    <!-- New Node Injection -->
                    <div class="bg-indigo-900 p-10 rounded-[40px] shadow-2xl text-white space-y-6 text-left font-sans">
                        <div class="flex items-center gap-3 mb-2 text-left font-sans">
                            <i class="fas fa-plus-circle text-indigo-400 text-left font-sans"></i>
                            <label class="block text-sm font-black uppercase tracking-widest text-indigo-200 text-left font-sans">Inject Strategic Logic Node</label>
                        </div>
                        <div class="grid grid-cols-2 gap-6 text-left font-sans">
                            <div class="text-left font-sans">
                                <label class="block text-xs font-black uppercase tracking-widest text-indigo-400 mb-2 ml-1 text-left font-sans">Node Protocol</label>
                                <select v-model="stepForm.type" class="w-full bg-white/10 border-white/20 rounded-2xl text-xs font-bold py-3.5 px-5 shadow-inner focus:ring-4 focus:ring-white/10 transition-all text-white appearance-none cursor-pointer text-left font-sans">
                                    <option value="send_email" class="text-gray-900">TRANSMIT EMAIL</option>
                                    <option value="wait" class="text-gray-900">LATENCY / WAIT</option>
                                    <option value="update_contact" class="text-gray-900">IDENTITY UPDATE</option>
                                </select>
                            </div>
                            <div class="text-left font-sans">
                                <label class="block text-xs font-black uppercase tracking-widest text-indigo-400 mb-2 ml-1 text-left font-sans">Interval Delta (Days)</label>
                                <input v-model="stepForm.delay_days" type="number" class="w-full bg-white/10 border-white/20 rounded-2xl text-xs font-bold py-3.5 px-5 shadow-inner focus:ring-4 focus:ring-white/10 transition-all text-white text-left font-sans">
                            </div>
                        </div>
                        <button @click="addStep" :disabled="loading" class="w-full bg-white text-indigo-900 py-4 rounded-[24px] font-black text-sm uppercase tracking-widest hover:bg-emerald-400 hover:text-white transition-all shadow-xl active:scale-95 text-left font-sans">
                             INTEGRATE NODE INTO CHAIN
                        </button>
                    </div>
                </div>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const showModal = ref(false);
const showStepBuilder = ref(false);
const activeFlow = ref(null);
const loading = ref(false);
const workflows = ref([]);

const flowForm = ref({
    name: '',
    trigger_type: 'contact_created',
    description: ''
});

const stepForm = ref({
    type: 'send_email',
    delay_days: 1,
    template_id: null
});

const fetchWorkflows = async () => {
    try {
        const response = await axios.get(route('crm.marketing.automations.index'));
        workflows.value = response.data;
    } catch (error) {
        console.error('Failed to fetch automations', error);
    }
};

const createWorkflow = async () => {
    if (!flowForm.value.name) return;
    loading.value = true;
    try {
        await axios.post(route('crm.marketing.automations.store'), flowForm.value);
        flowForm.value = { name: '', trigger_type: 'contact_created', description: '' };
        showModal.value = false;
        await fetchWorkflows();
    } catch (error) {
        alert('Failed to initialize sequence');
    } finally {
        loading.value = false;
    }
};

const toggleWorkflow = async (flow) => {
    try {
        await axios.post(route('crm.marketing.automations.toggle', flow.id));
        flow.is_active = !flow.is_active;
    } catch (error) {
        alert('Failed to synchronize state');
    }
};

const openStepBuilder = (flow) => {
    activeFlow.value = flow;
    showStepBuilder.value = true;
};

const addStep = async () => {
    loading.value = true;
    try {
        await axios.post(route('crm.marketing.automations.steps.store', activeFlow.value.id), stepForm.value);
        await fetchWorkflows();
        showStepBuilder.value = false;
    } catch (error) {
        alert('Failed to integrate node');
    } finally {
        loading.value = false;
    }
};

const printView = () => window.print();

onMounted(() => {
    fetchWorkflows();
});
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
