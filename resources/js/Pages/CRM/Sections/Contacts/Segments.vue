<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print text-left font-sans">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-72 bg-gray-900 text-white text-sm p-5 rounded-2xl shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-widest uppercase mb-2 text-indigo-400 border-b border-indigo-500/20 pb-2 text-left">Audience Orchestration</div>
                <p class="text-gray-300 leading-relaxed mb-3 text-left">Segments are live filters that automatically group contacts based on intent, behavior, or profile attributes. Power your marketing and sales flows with dynamic audience logic.</p>
                <div class="space-y-1.5 pt-2 text-left text-sm font-bold">
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div> <span>Dynamic Logic Chains</span></div>
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> <span>Real-time Member Processing</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0 font-sans">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left">Intelligence Segments</h2>
                        <div class="flex items-center mt-3 text-left">
                            <i class="fas fa-layer-group text-indigo-500 mr-3 text-left"></i>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em] text-left">{{ segments.length }} active audience definitions</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left">
                        <button @click="showCreateModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left">
                            <i class="fas fa-plus mr-2 text-xs group-hover:scale-125 transition-transform text-left"></i>
                            New Audience Block
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Toolbar -->
            <div class="px-8 py-5 border-b border-gray-200 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-40 no-print text-left">
                <div class="flex items-center gap-4 flex-1 w-full max-w-3xl text-left">
                    <div class="relative w-full max-w-sm group text-left">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left" 
                            placeholder="Search segments by name or rule..."
                        >
                    </div>
                    <button @click="searchQuery = ''" class="text-sm font-black text-gray-400 hover:text-indigo-600 tracking-widest uppercase transition-colors px-4 text-left">Reset</button>
                </div>

                <!-- Export/Print Actions -->
                <div class="flex items-center gap-2 pr-8 text-left">
                    <button @click="printView" class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-print text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Print Index</div>
                    </button>
                </div>
            </div>

            <!-- Segments Grid Container -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 text-left">
                    <div v-for="segment in filteredSegments" :key="segment.id" 
                         class="bg-white rounded-[32px] shadow-sm border border-gray-100 p-8 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all relative group flex flex-col justify-between min-h-[360px] text-left border-l-4 border-l-transparent hover:border-l-indigo-600">
                        
                        <div class="absolute -right-6 -top-6 opacity-[0.03] group-hover:opacity-[0.06] transition-all pointer-events-none text-left">
                            <i class="fas fa-microchip text-[120px] text-left"></i>
                        </div>

                        <div class="text-left">
                            <div class="flex justify-between items-start mb-6 text-left">
                                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-[22px] flex items-center justify-center text-white shadow-lg shadow-indigo-100 border-2 border-white group-hover:scale-110 transition-transform text-left">
                                    <i class="fas fa-users-cog text-2xl text-left"></i>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity text-left">
                                    <button class="w-9 h-9 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-900 hover:text-white flex items-center justify-center shadow-sm border border-gray-100 transition-all group/btn relative">
                                        <i class="fas fa-pen text-sm"></i>
                                        <div class="absolute -top-10 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Edit Rule</div>
                                    </button>
                                </div>
                            </div>
                            
                            <h3 class="text-2xl font-black text-gray-900 mb-2 leading-tight group-hover:text-indigo-600 transition-colors text-left">{{ segment.name }}</h3>
                            <p class="text-base font-bold text-gray-500 mb-6 leading-relaxed line-clamp-3 text-left">{{ segment.description || 'Dynamic orchestration logic enabled for this stakeholder block.' }}</p>
                            
                            <!-- Logic Tags -->
                            <div class="flex flex-wrap gap-2 text-left">
                                <span v-for="(rule, idx) in (segment.criteria || [])" :key="idx" class="px-3 py-1 bg-gray-50 text-sm font-black text-gray-400 uppercase tracking-widest rounded-lg border border-gray-100 text-left">
                                    {{ rule.field }} {{ rule.operator }} {{ rule.value }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-8 border-t border-gray-50 mt-auto text-left">
                            <div class="flex items-center text-left">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center mr-4 shadow-inner text-left">
                                    <i class="fas fa-user-friends text-indigo-500 text-xs text-left"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-lg font-black text-gray-900 leading-none text-left">{{ formatNumber(segment.count) }}</span>
                                    <span class="text-sm font-black text-gray-400 uppercase tracking-widest mt-1 text-left">Entities</span>
                                </div>
                            </div>
                            <div class="flex items-center px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-black uppercase tracking-widest border border-emerald-100 text-left shadow-sm">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2 animate-pulse text-left"></span>
                                Synced
                            </div>
                        </div>
                    </div>

                    <!-- Create New Card -->
                    <button @click="showCreateModal = true" class="bg-gray-50/50 border-4 border-dashed border-gray-100 rounded-[40px] p-8 flex flex-col items-center justify-center text-gray-300 hover:border-indigo-400 hover:text-indigo-600 hover:bg-white transition-all min-h-[360px] group/new text-left shadow-inner">
                        <div class="w-24 h-24 rounded-[32px] bg-white shadow-sm border border-gray-50 flex items-center justify-center mb-8 group-hover/new:rotate-12 group-hover/new:scale-110 transition-all text-left">
                            <i class="fas fa-plus text-4xl group-hover/new:text-indigo-500 transition-colors text-left"></i>
                        </div>
                        <span class="text-xl font-black tracking-tight text-gray-900 text-left group-hover/new:text-indigo-600">Architect New Segment</span>
                        <span class="text-sm font-bold uppercase tracking-[0.2em] mt-3 opacity-40 text-left">Expand your audience reach</span>
                    </button>
                </div>

                <!-- Empty State for Search -->
                <div v-if="filteredSegments.length === 0" class="flex flex-col items-center justify-center py-32 text-gray-300 text-left">
                     <i class="fas fa-layer-group text-6xl opacity-20 mb-6 text-left"></i>
                     <h3 class="text-xl font-black text-gray-900 text-left">No Logic Blocks Found</h3>
                     <p class="text-sm font-bold uppercase tracking-widest mt-2 text-left">Adjust your search parameters or build a new one.</p>
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="flex items-center text-left">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left">
                            <i class="fas fa-layer-group text-xl text-left"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left">Architect Segment</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left">Rule-Based Audience Deployment</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-10 space-y-10 text-left">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                        <div class="space-y-3 text-left">
                            <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Block Designation</label>
                            <input v-model="form.name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left" placeholder="e.g. Inbound Enterprise Q4">
                        </div>
                        
                        <div class="space-y-3 text-left">
                            <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Brief Intelligence</label>
                            <input v-model="form.description" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left" placeholder="Defining high-intent leads...">
                        </div>
                    </div>

                    <!-- Criteria Builder -->
                    <div class="space-y-6 text-left">
                        <div class="flex justify-between items-center px-2 text-left">
                            <label class="text-base font-black text-gray-400 uppercase tracking-widest text-left font-sans">Logic Gates & Operators</label>
                            <button type="button" @click="addCriterion" class="px-5 py-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm border border-indigo-100 text-left">
                                <i class="fas fa-plus-circle mr-2 text-left"></i> APPEND RULE
                            </button>
                        </div>
                        
                        <div class="bg-gray-50/50 p-8 rounded-[32px] border border-gray-100 space-y-5 text-left shadow-inner">
                            <div v-for="(criterion, index) in form.criteria" :key="index" class="flex items-center gap-4 animate-in slide-in-from-bottom-2 duration-300 text-left">
                                <div class="flex-1 grid grid-cols-3 gap-4 text-left">
                                    <select v-model="criterion.field" class="bg-white border-gray-100 rounded-xl text-xs font-black uppercase tracking-widest py-3 px-4 shadow-sm focus:ring-4 focus:ring-indigo-500/10 transition-all text-left cursor-pointer">
                                        <option value="first_name">First Name</option>
                                        <option value="last_name">Last Name</option>
                                        <option value="email">Email</option>
                                        <option value="city">City</option>
                                        <option value="state">State</option>
                                        <option value="country">Country</option>
                                        <option value="title">Job Title</option>
                                        <option value="industry">Industry</option>
                                    </select>
                                    
                                    <select v-model="criterion.operator" class="bg-white border-gray-100 rounded-xl text-xs font-black uppercase tracking-widest py-3 px-4 shadow-sm focus:ring-4 focus:ring-indigo-500/10 transition-all text-left cursor-pointer">
                                        <option value="=">Equals</option>
                                        <option value="!=">Not Equals</option>
                                        <option value="contains">Contains</option>
                                        <option value="starts_with">Starts With</option>
                                        <option value="is_empty">Is Empty</option>
                                        <option value="is_not_empty">Is Not Empty</option>
                                    </select>
                                    
                                    <input v-if="!['is_empty', 'is_not_empty'].includes(criterion.operator)" 
                                           v-model="criterion.value" 
                                           type="text" 
                                           class="bg-white border-gray-100 rounded-xl text-xs font-bold py-3 px-4 shadow-sm focus:ring-4 focus:ring-indigo-500/10 transition-all text-left" 
                                           placeholder="Logic Value...">
                                </div>
                                
                                <button type="button" @click="removeCriterion(index)" class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-rose-300 hover:text-rose-500 flex items-center justify-center transition-all shadow-sm hover:rotate-12">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                            
                            <div v-if="form.criteria.length === 0" class="text-center py-10 text-left">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-50 shadow-sm text-left">
                                     <i class="fas fa-globe-americas text-indigo-100 text-2xl text-left"></i>
                                </div>
                                <p class="text-sm font-black text-gray-300 uppercase tracking-[0.2em] text-left">Global Audience Logic (Includes All Active Records)</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-between text-left">
                         <div class="flex items-center text-left">
                            <div class="flex items-center cursor-pointer group text-left" @click="form.is_public = !form.is_public">
                                <div :class="['w-10 h-6 rounded-full border-2 p-1 transition-all flex items-center', form.is_public ? 'bg-indigo-600 border-indigo-600' : 'bg-gray-100 border-gray-200']">
                                    <div :class="['w-3 h-3 rounded-full bg-white transition-all transform shadow-sm', form.is_public ? 'translate-x-4' : 'translate-x-0']"></div>
                                </div>
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest ml-3 group-hover:text-gray-900 transition-colors text-left font-sans">Team Visibility Protocol</span>
                            </div>
                         </div>
                        <div class="flex items-center gap-8 text-left">
                            <button type="button" @click="showCreateModal = false" class="text-base font-black text-gray-400 hover:text-gray-900 transition-colors text-left">DISCARD</button>
                            <button type="submit" 
                                    :disabled="form.processing"
                                    class="bg-indigo-600 text-white px-12 py-5 rounded-[24px] font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 disabled:opacity-50 min-w-[220px] active:scale-95 text-left">
                                {{ form.processing ? 'ARCHITECTING...' : 'SAVE LOGIC BLOCK' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    segments: {
        type: Array,
        default: () => []
    }
});

const showCreateModal = ref(false);
const searchQuery = ref('');

const form = useForm({
    name: '',
    description: '',
    criteria: [
        { field: 'industry', operator: '=', value: '' }
    ],
    is_public: true
});

const filteredSegments = computed(() => {
    let list = props.segments || [];
    if (!searchQuery.value) return list;
    
    const term = searchQuery.value.toLowerCase();
    return list.filter(s => 
        s.name?.toLowerCase().includes(term) ||
        s.description?.toLowerCase().includes(term) ||
        s.criteria?.some(c => c.value?.toLowerCase().includes(term))
    );
});

const addCriterion = () => {
    form.criteria.push({ field: 'city', operator: '=', value: '' });
};

const removeCriterion = (index) => {
    form.criteria.splice(index, 1);
};

const submit = () => {
    form.post(route('crm.segments.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const printView = () => window.print();
const formatNumber = (num) => new Intl.NumberFormat('en-US').format(num || 0);
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

@keyframes slide-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-in {
    animation: slide-in 0.3s ease-out forwards;
}
</style>
