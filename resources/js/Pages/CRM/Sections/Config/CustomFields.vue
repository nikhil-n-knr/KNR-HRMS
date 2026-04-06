<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print text-left font-sans">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-left font-sans">
                <i class="fas fa-info text-xs text-left font-sans"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans text-left font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left font-sans text-left font-sans">Schema Extension Engine</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans text-left font-sans">Custom Fields allow you to extend the core data models of your sales ecosystem. Inject specialized attributes into Leads, Contacts, and Deals to capture high-fidelity intelligence.</p>
                <div class="space-y-2 text-left font-sans text-left font-sans">
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50 text-left font-sans"></div> <span>Dynamic Model Extension</span></div>
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50 text-left font-sans"></div> <span>Multi-Type Attribute Matrix</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0 text-left font-sans">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left font-sans">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans text-left font-sans">
                    <div class="text-left font-sans text-left font-sans text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Model Extensions</h2>
                        <div class="flex items-center mt-3 text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-wrench text-indigo-500 mr-3 text-left font-sans text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Schema Architect: Online</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans text-left font-sans">
                         <button @click="openCreateModal" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-plus mr-2 text-xs group-hover:rotate-90 transition-transform text-left font-sans text-left font-sans"></i>
                            Inject New Attribute
                        </button>
                        <button class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-file-excel text-sm text-left font-sans text-left font-sans"></i>
                        </button>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toolbar & Entity Select -->
            <div class="px-8 py-5 border-b border-gray-100 bg-white flex items-center justify-between text-left font-sans text-left font-sans">
                <div class="flex gap-4 p-2 bg-indigo-50/50 rounded-2xl text-left font-sans text-left font-sans">
                    <button v-for="e in entities" :key="e.id"
                            @click="activeEntity = e.id"
                            :class="['px-7 py-3 rounded-xl text-sm font-black uppercase tracking-widest transition-all text-left font-sans', activeEntity === e.id ? 'bg-white text-indigo-600 shadow-xl shadow-indigo-500/10' : 'text-gray-400 hover:text-indigo-400 hover:bg-white/50']">
                        {{ e.label }} Core
                    </button>
                </div>
                
                <div class="relative w-64 group text-left font-sans text-left font-sans">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans text-left font-sans text-left font-sans"></i>
                    <input type="text" placeholder="Filter attributes..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans text-left font-sans text-left font-sans">
                </div>
            </div>

            <!-- Matrix Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans text-left font-sans" id="print-area">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden text-left font-sans text-left font-sans">
                     <table class="min-w-full divide-y divide-gray-100 text-left font-sans text-left font-sans">
                        <thead class="bg-gray-50/20 text-left font-sans text-left font-sans">
                            <tr>
                                <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Attribute Identity</th>
                                <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Primitive Type</th>
                                <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Validation</th>
                                <th class="px-10 py-6 text-right text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-left font-sans text-left font-sans">
                            <tr v-for="field in filteredFields" :key="field.id" class="hover:bg-indigo-50/10 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans text-left font-sans">
                                <td class="px-10 py-8 text-left font-sans text-left font-sans">
                                    <div class="text-sm font-black text-gray-900 text-left font-sans text-left font-sans">{{ field.label }}</div>
                                    <div class="text-sm font-black text-indigo-400 uppercase tracking-tighter mt-1 text-left font-sans text-left font-sans">SYSTEM_KEY: {{ field.name.toUpperCase() }}</div>
                                </td>
                                <td class="px-10 py-8 text-center text-left font-sans text-left font-sans">
                                    <span class="px-4 py-2 bg-gray-50 rounded-xl text-sm font-black text-gray-600 uppercase tracking-widest border border-gray-100 shadow-inner text-left font-sans text-left font-sans">{{ field.type.toUpperCase() }}</span>
                                </td>
                                <td class="px-10 py-8 text-center text-left font-sans text-left font-sans">
                                     <span v-if="field.is_required" class="text-sm font-black text-rose-500 border border-rose-100 bg-rose-50 px-3 py-1.5 rounded-xl uppercase tracking-widest flex items-center justify-center gap-2 text-left font-sans text-left font-sans">
                                        <i class="fas fa-shield-alt text-xs text-left font-sans"></i> Mandatory
                                     </span>
                                     <span v-else class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans text-left font-sans">Optional Node</span>
                                </td>
                                <td class="px-10 py-8 text-right text-left font-sans text-left font-sans">
                                    <div class="flex justify-end gap-3 text-left font-sans text-left font-sans">
                                        <button @click="editField(field)" class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 flex items-center justify-center transition-all shadow-sm active:scale-95 text-left font-sans text-left font-sans">
                                            <i class="fas fa-pen text-xs text-left font-sans text-left font-sans"></i>
                                        </button>
                                        <button @click="deleteField(field.id)" class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-rose-600 hover:border-rose-100 flex items-center justify-center transition-all shadow-sm active:scale-95 text-left font-sans text-left font-sans text-left font-sans">
                                            <i class="fas fa-trash text-xs text-left font-sans text-left font-sans"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                     </table>
                     <div v-if="filteredFields.length === 0" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans text-left font-sans">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left font-sans text-left font-sans">
                             <i class="fas fa-atom text-3xl opacity-20 text-left font-sans text-left font-sans"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Schema Void</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans text-left font-sans">No custom attributes defined for {{ activeEntity.toUpperCase() }} models.</p>
                     </div>
                </div>
            </div>
        </div>

        <!-- ATTRIBUTE CONFIGURATION MODAL -->
        <div v-if="showModal" @click.self="closeModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 text-left font-sans text-left font-sans">
            <div class="relative bg-white rounded-[45px] shadow-2xl max-w-xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300 text-left font-sans">
                <div class="bg-gray-50/50 px-12 py-10 border-b border-gray-100 flex justify-between items-center text-left font-sans text-left font-sans">
                    <div class="flex items-center text-left font-sans text-left font-sans">
                        <div class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left font-sans text-left font-sans">
                            <i class="fas fa-puzzle-piece text-xl text-left font-sans text-left font-sans"></i>
                        </div>
                        <div class="text-left font-sans text-left font-sans">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">{{ isEditing ? 'Refine Attribute' : 'Inject Attribute' }}</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left font-sans text-left font-sans">Schema Extension Tool</p>
                        </div>
                    </div>
                    <button @click="closeModal" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90 text-left font-sans text-left font-sans">
                        <i class="fas fa-times text-left font-sans text-left font-sans"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-12 space-y-8 text-left font-sans text-left font-sans">
                    <div class="grid grid-cols-2 gap-8 text-left font-sans text-left font-sans">
                        <div class="space-y-3 col-span-2 text-left font-sans text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Global Entity Map</label>
                             <select v-model="form.entity_type" required class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-black shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans appearance-none cursor-pointer">
                                <option value="lead">LEAD PROTOCOL CORE</option>
                                <option value="contact">CONTACT PROFILE CORE</option>
                                <option value="account">ENTERPRISE ACCOUNT CORE</option>
                                <option value="deal">REVENUE OPPORTUNITY CORE</option>
                             </select>
                        </div>

                        <div class="space-y-3 text-left font-sans text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Visible Attribute Label</label>
                             <input v-model="form.label" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-semibold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans" placeholder="e.g. Technology Stack">
                        </div>

                        <div class="space-y-3 text-left font-sans text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Internal System Key</label>
                             <input v-model="form.name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-mono font-bold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans" placeholder="tech_stack">
                        </div>

                        <div class="space-y-3 text-left font-sans text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Primitive Data Logic</label>
                             <select v-model="form.type" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-bold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans appearance-none cursor-pointer">
                                <option value="text">ALPHANUMERIC (TEXT)</option>
                                <option value="number">SCALAR (NUMERIC)</option>
                                <option value="select">SELECTION MATRIX</option>
                                <option value="date">TEMPORAL (DATE)</option>
                                <option value="boolean">LOGIC (BOOLEAN)</option>
                             </select>
                        </div>

                        <div class="space-y-3 text-left font-sans text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Functional Constraints</label>
                             <div class="flex items-center gap-4 p-4.5 bg-indigo-50 rounded-2xl border border-indigo-100 text-left font-sans text-left font-sans">
                                <input v-model="form.is_required" type="checkbox" id="field_req" class="w-6 h-6 text-indigo-600 border-indigo-200 rounded-lg focus:ring-indigo-500/10 text-left font-sans text-left font-sans">
                                <label for="field_req" class="text-sm font-black text-indigo-900 uppercase tracking-widest cursor-pointer text-left font-sans text-left font-sans">Mandatory Data Injection</label>
                             </div>
                        </div>
                    </div>

                    <div v-if="form.type === 'select'" class="space-y-3 text-left font-sans text-left font-sans animate-in slide-in-from-top-2 duration-300">
                         <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans text-left font-sans">Coordinate Options (Comma Separated Matrix)</label>
                         <textarea v-model="rawOptions" class="w-full bg-gray-50 border-gray-100 rounded-[30px] py-4.5 px-6 text-sm font-medium shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans" placeholder="Option Alpha, Option Beta, Option Gamma"></textarea>
                    </div>

                    <div class="pt-8 flex items-center justify-between text-left font-sans text-left font-sans">
                        <button type="button" @click="closeModal" class="text-base font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest text-left font-sans text-left font-sans">Discard Extension</button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-12 py-5 rounded-[28px] font-black text-base tracking-widest uppercase hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 flex items-center justify-center min-w-[240px] active:scale-95 text-left font-sans text-left font-sans">
                            <i class="fas fa-save mr-3 text-left font-sans text-left font-sans"></i>
                            {{ isEditing ? 'COMMIT EXTENSION' : 'DEPLOY SCHEMA EXTENSION' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    custom_fields: { type: Array, default: () => [] }
});

const entities = [
    { id: 'lead', label: 'Leads' },
    { id: 'contact', label: 'Contacts' },
    { id: 'account', label: 'Accounts' },
    { id: 'deal', label: 'Deals' },
];

const activeEntity = ref('lead');
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const rawOptions = ref('');

const form = useForm({
    entity_type: 'lead',
    label: '',
    name: '',
    type: 'text',
    options: null,
    is_required: false,
    order: 0
});

const filteredFields = computed(() => {
    return (props.custom_fields || []).filter(f => f.entity_type === activeEntity.value);
});

watch(rawOptions, (val) => {
    form.options = val.split(',').map(o => o.trim()).filter(o => o);
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.entity_type = activeEntity.value;
    rawOptions.value = '';
    showModal.value = true;
};

const editField = (field) => {
    isEditing.value = true;
    editId.value = field.id;
    form.entity_type = field.entity_type;
    form.label = field.label;
    form.name = field.name;
    form.type = field.type;
    form.options = field.options;
    form.is_required = field.is_required;
    rawOptions.value = field.options ? field.options.join(', ') : '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('crm.custom-fields.update', editId.value), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('crm.custom-fields.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteField = (id) => {
    if (confirm('Critical Alert: Deleting this attribute will decommission historical data points. Are you sure?')) {
        router.delete(route('crm.custom-fields.destroy', id));
    }
};

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
