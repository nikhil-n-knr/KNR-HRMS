<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import { 
    CalculatorIcon, 
    ShieldCheckIcon, 
    LockClosedIcon, 
    ArrowPathIcon, 
    DocumentTextIcon, 
    ChevronDownIcon, 
    CloudArrowUpIcon,
    TrashIcon,
    CurrencyRupeeIcon,
    BuildingOfficeIcon,
    BriefcaseIcon,
    IdentificationIcon,
    CheckBadgeIcon,
    InformationCircleIcon,
    ArrowRightIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps(['employee']);
const toast = useToastStore();

// State
const loading = ref(true);
const fiscalYear = ref('');
const currentRegime = ref('New');
const locked = ref(false);
const currentStep = ref('regime'); 
const openSection = ref(null);

const sections = ref([]);
const declarationsMap = reactive({});
const proofsMap = reactive({});
const fileInputs = ref({});

// Previous Employment Form
const prevForm = reactive({
    previous_gross_income: 0,
    previous_tds_paid: 0,
    previous_pf_deducted: 0,
    previous_pt_paid: 0
});

// HRA Form
const hraForm = reactive({
    rent_monthly: 0,
    landlord_name: '',
    landlord_pan: '',
    rented_address: '',
    is_metro_city: false
});

// Computed Slices
const deductionSections = computed(() => sections.value.filter(s => !['10(13A)', '24(b)', 'iOS'].includes(s.section_code)));
const otherSections = computed(() => sections.value.filter(s => ['24(b)', 'iOS'].includes(s.section_code)));

const steps = computed(() => [
    { id: 'previous', name: 'Legacy Employment', idx: '01', icon: BriefcaseIcon, disabled: false },
    { id: 'regime', name: 'Regime Selector', idx: '02', icon: ShieldCheckIcon, disabled: false },
    { id: 'hra', name: 'HRA Hub', idx: '03', icon: BuildingOfficeIcon, disabled: currentRegime.value === 'New' },
    { id: 'deductions', name: 'Strategic Saving', idx: '04', icon: CalculatorIcon, disabled: currentRegime.value === 'New' },
    { id: 'other', name: 'External Income', idx: '05', icon: CurrencyRupeeIcon, disabled: false },
]);

// Methods
const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val || 0);

const toggleAccordion = (id) => openSection.value = openSection.value === id ? null : id;

const totalDeductions = computed(() => {
    return deductionSections.value.reduce((sum, s) => sum + (declarationsMap[s.id]?.declared_amount || 0), 0);
});

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('employee.tax.index'));
        const d = res.data;
        fiscalYear.value = d.fiscal_year;
        currentRegime.value = d.regime.regime;
        locked.value = d.locked;
        sections.value = d.sections;

        // Init Previous Income
        if (d.regime) {
             Object.assign(prevForm, {
                previous_gross_income: parseFloat(d.regime.previous_gross_income || 0),
                previous_tds_paid: parseFloat(d.regime.previous_tds_paid || 0),
                previous_pf_deducted: parseFloat(d.regime.previous_pf_deducted || 0),
                previous_pt_paid: parseFloat(d.regime.previous_pt_paid || 0),
             });
        }

        // Init Declarations
        d.sections.forEach(s => {
            const decl = d.declarations.find(pd => pd.tax_section_id === s.id);
            declarationsMap[s.id] = { declared_amount: decl ? parseFloat(decl.declared_amount) : 0 };
            proofsMap[s.id] = decl ? decl.proofs : [];
        });

        // Init HRA
        if (d.hra) {
            Object.assign(hraForm, {
                rent_monthly: parseFloat(d.hra.rent_monthly),
                landlord_name: d.hra.landlord_name,
                landlord_pan: d.hra.landlord_pan,
                rented_address: d.hra.rented_address,
                is_metro_city: Boolean(d.hra.is_metro_city)
            });
        }

        // HRA Proofs (Using code 10(13A))
        const hraSec = d.sections.find(s => s.section_code === '10(13A)');
        if (hraSec) {
             const hraDecl = d.declarations.find(pd => pd.tax_section_id === hraSec.id);
             proofsMap['HRA'] = hraDecl ? hraDecl.proofs : [];
        }

    } catch (e) {
        console.error(e);
        toast.error('Failed to load tax data');
    } finally {
        loading.value = false;
    }
};

const savePrevious = async () => {
    try {
        await axios.post(route('employee.tax.regime'), { 
            ...prevForm, 
            regime: currentRegime.value, 
            fiscal_year: fiscalYear.value 
        });
        toast.success("Previous Employment Details Saved");
        currentStep.value = 'regime'; 
    } catch { toast.error("Save failed"); }
};

const updateRegime = async (regime) => {
    try {
        await axios.post(route('employee.tax.regime'), { 
            regime, 
            fiscal_year: fiscalYear.value,
            ...prevForm 
        });
        currentRegime.value = regime;
        toast.success(`Switched to ${regime} Regime`);
        if (regime === 'Old') currentStep.value = 'hra';
        else currentStep.value = 'other';
    } catch { toast.error("Update failed"); }
};

const saveHra = async () => {
    try {
        await axios.post(route('employee.tax.hra.store'), { ...hraForm, fiscal_year: fiscalYear.value });
        toast.success("HRA Details Saved");
        currentStep.value = 'deductions'; 
    } catch(error) { 
         if (error.response && error.response.status === 422) {
             toast.error(error.response.data.message || "Validation Error");
         } else {
             toast.error("Save failed"); 
         }
    }
};

const saveDeclarations = async () => {
    try {
        const payload = {
            fiscal_year: fiscalYear.value,
            declarations: sections.value.map(s => ({
                tax_section_id: s.id,
                declared_amount: declarationsMap[s.id]?.declared_amount || 0
            }))
        };
        await axios.post(route('employee.tax.declarations.store'), payload);
        toast.success("Declarations Saved");
        if (currentStep.value === 'deductions') currentStep.value = 'other'; 
    } catch { toast.error("Save failed"); }
};

const triggerUpload = (id) => fileInputs.value[id]?.click();

const handleFileUpload = async (event, sectionId) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('fiscal_year', fiscalYear.value);
    
    if (sectionId === 'HRA') {
        formData.append('section_code', 'HRA');
    } else {
        formData.append('tax_section_id', sectionId);
    }

    try {
        const res = await axios.post(route('employee.tax.proofs.store'), formData);
        const key = sectionId;
        if (!proofsMap[key]) proofsMap[key] = [];
        proofsMap[key].push(res.data.proof);
        toast.success("Uploaded successfully");
    } catch { toast.error("Upload failed"); }
};

const getProofs = (key) => proofsMap[key] || [];

const deleteProof = async (proof, sectionId) => {
    if (!confirm("Delete this proof?")) return;
    try {
        await axios.delete(route('employee.tax.proofs.destroy', proof.id));
        proofsMap[sectionId] = proofsMap[sectionId].filter(p => p.id !== proof.id);
        toast.success("Deleted");
    } catch { toast.error("Delete failed"); }
};

onMounted(fetchData);
</script>

<template>
    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700 font-outfit pb-20">
        <!-- Global Sync Loading -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-32 grayscale opacity-40">
             <ArrowPathIcon class="w-12 h-12 text-slate-400 animate-spin mb-6" />
             <p class="text-xs font-black uppercase tracking-[0.4em] italic">Synchronizing Tax Ledger...</p>
        </div>

        <div v-else class="space-y-12">
            <!-- Strategic Header Terminal -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-slate-50 rounded-full blur-2xl group-hover:bg-indigo-50 transition-colors"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-6 transition-transform">
                        <CalculatorIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                            Tax Compliance Console
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest shadow-sm">FY {{ fiscalYear }}</span>
                        </h2>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 flex items-center gap-2">
                            <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
                            Active lifecycle management & strategic asset protection
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 relative z-10 w-full lg:w-auto">
                    <!-- Global Lock Status -->
                    <div v-if="locked" class="flex-1 lg:flex-none px-6 py-3 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] border border-slate-800 flex items-center gap-3 shadow-xl">
                        <LockClosedIcon class="w-4 h-4 text-amber-500" />
                        Declarations Locked
                    </div>
                    <div v-else class="flex-1 lg:flex-none px-6 py-3 bg-emerald-50 text-emerald-700 rounded-2xl text-xs font-black uppercase tracking-[0.2em] border border-emerald-100 flex items-center gap-3 shadow-sm">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        Open for Submission
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="route('employee.tax.documents.form16')" class="w-12 h-12 bg-white border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-indigo-400 transition-all shadow-sm group/btn" target="_blank" title="Download Form 16">
                            <DocumentTextIcon class="w-5 h-5 group-hover/btn:scale-110 transition-transform" />
                        </a>
                        <a :href="route('employee.tax.documents.form12bb')" class="w-12 h-12 bg-white border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm group/btn" target="_blank" title="Download Form 12BB">
                            <CheckBadgeIcon class="w-5 h-5 group-hover/btn:scale-110 transition-transform" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Lifecycle Stepper Interface -->
            <div class="flex flex-wrap gap-3 bg-slate-100/50 p-2 rounded-[2rem] border border-slate-200/50 w-full lg:w-fit backdrop-blur-sm">
                <button 
                    v-for="tab in steps" 
                    :key="tab.id"
                    @click="currentStep = tab.id"
                    :disabled="tab.disabled"
                    class="group relative px-6 py-4 rounded-[1.5rem] transition-all duration-500 flex items-center gap-4 overflow-hidden"
                    :class="[
                        currentStep === tab.id 
                        ? 'bg-slate-900 text-white shadow-2xl shadow-slate-400 scale-[1.05] z-10' 
                        : 'text-slate-500 hover:bg-white hover:text-slate-900',
                        tab.disabled ? 'opacity-30 cursor-not-allowed grayscale' : ''
                    ]"
                >
                    <div class="relative z-10 flex items-center gap-3">
                         <span class="text-xs font-black tracking-widest opacity-50">{{ tab.idx }}</span>
                         <component :is="tab.icon" class="w-4 h-4 transition-transform group-hover:rotate-6" :class="currentStep === tab.id ? 'text-indigo-400' : 'text-slate-400'" />
                         <span class="text-xs font-black uppercase tracking-[0.2em] whitespace-nowrap">{{ tab.name }}</span>
                    </div>
                    <div v-if="currentStep === tab.id" class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-transparent"></div>
                </button>
            </div>

            <!-- Tab Content Area -->
            <Transition
                enter-active-class="transition duration-500 cubic-bezier(0.16, 1, 0.3, 1)"
                enter-from-class="transform translate-y-8 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-300 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-8 opacity-0"
                mode="out-in"
            >
                <div :key="currentStep">
                <!-- SEGMENT 01: PREVIOUS EMPLOYMENT -->
                <div v-if="currentStep === 'previous'" :key="'prev'" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-10 relative overflow-hidden group">
                    <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50"></div>
                    
                    <div class="flex justify-between items-start mb-12 relative z-10">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Previous Employment Details</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2">Income details from your previous employer in this financial year (Form 12B)</p>
                        </div>
                        <InformationCircleIcon class="w-10 h-10 text-slate-100" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10">
                        <div v-for="field in [
                            { label: 'Previous Gross Salary', model: 'previous_gross_income' },
                            { label: 'TDS Deducted', model: 'previous_tds_paid' },
                            { label: 'PF Deducted', model: 'previous_pf_deducted' },
                            { label: 'Professional Tax Paid', model: 'previous_pt_paid' },
                        ]" :key="field.label" class="space-y-3">
                            <label class="px-2 text-xs font-black text-slate-500 uppercase tracking-[0.2em] flex items-center justify-between">
                                {{ field.label }}
                                <CurrencyRupeeIcon class="w-3.5 h-3.5 opacity-40" />
                            </label>
                            <div class="relative group/input">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 font-black text-[14px] group-focus-within/input:text-indigo-500">₹</span>
                                <input type="number" v-model.number="prevForm[field.model]" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] pl-12 pr-6 text-[14px] font-black text-slate-900 focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all placeholder:text-slate-200" :disabled="locked">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-12 pt-10 border-t border-slate-50 flex justify-end relative z-10">
                        <button @click="savePrevious" v-if="!locked" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.3em] shadow-2xl shadow-slate-300 hover:bg-indigo-600 transition-all active:scale-95 flex items-center gap-4">
                            <span>Save & Continue</span>
                            <ArrowRightIcon class="w-4 h-4" />
                        </button>
                        <button v-else @click="currentStep = 'regime'" class="h-14 px-12 bg-slate-100 text-slate-400 rounded-2xl text-xs font-black uppercase tracking-[0.3em] flex items-center gap-4">
                            Next: Tax Regime
                        </button>
                    </div>
                </div>

                <!-- SEGMENT 02: REGIME SELECTION -->
                <div v-if="currentStep === 'regime'" :key="'regime'" class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Selection Matrix -->
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-10">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-8">Select Tax Regime</h3>
                        
                        <div class="space-y-6">
                            <div 
                                v-for="opt in [
                                    { id: 'New', name: 'New Regime (Default)', desc: 'Lower tax rates with no exemptions. Best suited for income above ₹15L annually.' },
                                    { id: 'Old', name: 'Old Regime', desc: 'Claim deductions like 80C, HRA, Medical. Best suited for high investments and rent payment.' }
                                ]" 
                                :key="opt.id"
                                @click="!locked && updateRegime(opt.id)"
                                class="relative p-8 rounded-[2rem] border-2 transition-all duration-500 cursor-pointer overflow-hidden group/card"
                                :class="[
                                    currentRegime === opt.id 
                                    ? 'border-indigo-500 bg-indigo-50/50 shadow-xl shadow-indigo-500/5' 
                                    : 'border-slate-50 hover:border-slate-200'
                                ]"
                            >
                                <div v-if="currentRegime === opt.id" class="absolute -right-4 -top-4 w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg transform rotate-12">
                                    <CheckBadgeIcon class="w-10 h-10 text-emerald-500" />
                                </div>

                                <div class="flex items-start gap-6 relative z-10">
                                    <div class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 transition-colors" :class="currentRegime === opt.id ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-slate-200'">
                                        <div v-if="currentRegime === opt.id" class="w-3 h-3 rounded-full bg-white animate-pulse"></div>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-black text-slate-900 uppercase tracking-widest">{{ opt.name }}</span>
                                        <span class="block text-sm font-black text-slate-400 uppercase tracking-widest mt-2 leading-relaxed italic">{{ opt.desc }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="locked" class="mt-10 p-5 bg-amber-50 rounded-2xl border border-amber-100 flex items-center gap-4 text-amber-700">
                            <LockClosedIcon class="w-5 h-5" />
                            <p class="text-xs font-black uppercase tracking-widest">Tax regime is locked by Admin. Contact HR to make changes.</p>
                        </div>
                    </div>

                    <!-- Holographic Simulator -->
                    <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-indigo-500/30 p-10 text-white relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-[80px] -mr-32 -mt-32"></div>
                        <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[100px] -ml-32 -mb-32"></div>
                        
                        <h3 class="text-base font-black uppercase tracking-[0.2em] mb-10 relative z-10 flex items-center gap-4">
                            Strategic Comparison
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
                        </h3>
                        
                        <div class="space-y-6 relative z-10">
                            <div class="p-8 bg-white/5 rounded-[2rem] border border-white/10 hover:bg-white/10 transition-colors">
                                <div class="text-xs font-black text-slate-500 uppercase tracking-[0.3em] mb-3">New Regime Tax</div>
                                <div class="flex items-end gap-3">
                                    <span class="text-3xl font-black tracking-tighter text-emerald-400">₹ 1.24L</span>
                                    <span class="text-xs text-slate-500 uppercase font-black mb-1.5 opacity-50">Annual Est.</span>
                                </div>
                            </div>
                            
                            <div class="p-8 bg-white/5 rounded-[2rem] border border-white/10 hover:bg-white/10 transition-colors">
                                <div class="text-xs font-black text-slate-500 uppercase tracking-[0.3em] mb-3">Old Regime Tax</div>
                                <div class="flex items-end gap-3">
                                    <span class="text-3xl font-black tracking-tighter text-rose-400">₹ 1.52L</span>
                                    <span class="text-xs text-slate-500 uppercase font-black mb-1.5 opacity-50">Annual Est.</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-20 p-6 bg-white/5 rounded-2xl border border-white/5 relative z-10">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-relaxed italic">
                                " Based on your income, the New Regime may offer lower tax liability. Consult your CA before finalizing. "
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SEGMENT 03: HRA HUB -->
                <div v-if="currentStep === 'hra'" :key="'hra'" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                    <div class="p-10 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">House Rent Allowance (HRA)</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Rent exemption claim under Section 10(13A)</p>
                        </div>
                        <button v-if="!locked" @click="saveHra" class="h-12 px-8 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all flex items-center gap-3">
                             <ShieldCheckIcon class="w-4 h-4 text-indigo-400" />
                             Sync HRA Nodes
                        </button>
                    </div>
                    
                    <div class="p-10 grid grid-cols-1 xl:grid-cols-2 gap-12">
                        <div class="space-y-10">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label class="px-2 text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Monthly Installment</label>
                                    <div class="relative group/input">
                                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 font-black">₹</span>
                                        <input type="number" v-model.number="hraForm.rent_monthly" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-10 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" :disabled="locked">
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <label class="px-2 text-xs font-black text-slate-500 uppercase tracking-[0.2em]">City Type</label>
                                    <select v-model="hraForm.is_metro_city" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 text-xs font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer tracking-widest" :disabled="locked">
                                        <option :value="true">Metro City (50%)</option>
                                        <option :value="false">Non-Metro City (40%)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="px-2 text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Landlord Name</label>
                                <input type="text" v-model="hraForm.landlord_name" placeholder="Full name of landlord..." class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 text-sm font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-200" :disabled="locked">
                            </div>

                             <div class="space-y-3">
                                <label class="px-2 text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Landlord PAN</label>
                                <input type="text" v-model="hraForm.landlord_pan" placeholder="Required if annual rent exceeds ₹1L" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 text-sm font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-200 tracking-widest" :disabled="locked">
                            </div>
                            
                            <div class="space-y-3">
                                <label class="px-2 text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Rented Address</label>
                                <textarea v-model="hraForm.rented_address" rows="3" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] p-6 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-200 uppercase tracking-widest" :disabled="locked" placeholder="Full rented address..."></textarea>
                            </div>
                        </div>

                        <!-- HRA Evidence Vault -->
                        <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden group/vault shadow-2xl shadow-indigo-500/20">
                            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl group-hover/vault:translate-x-10 transition-transform duration-1000"></div>
                            
                            <h4 class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mb-10 flex items-center gap-4 relative z-10">
                                <CloudArrowUpIcon class="w-5 h-5 text-indigo-400" />
                                Rent Evidence Hub
                            </h4>
                            
                            <!-- Proof List -->
                            <div v-if="getProofs('HRA').length > 0" class="space-y-4 mb-10 relative z-10">
                                <div v-for="proof in getProofs('HRA')" :key="proof.id" class="flex items-center justify-between bg-white/5 border border-white/10 rounded-2xl p-4 group/item hover:bg-white/10 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400 group-hover/item:rotate-12 transition-transform">
                                            <DocumentTextIcon class="w-5 h-5" />
                                        </div>
                                        <span class="text-xs font-black uppercase tracking-widest truncate max-w-[150px]">{{ proof.file_name }}</span>
                                    </div>
                                    <button v-if="!locked" @click="deleteProof(proof, 'HRA')" class="w-8 h-8 bg-rose-500/20 text-rose-500 rounded-lg flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-16 text-slate-600 text-xs font-black uppercase tracking-[0.4em] italic relative z-10 border-2 border-dashed border-white/5 rounded-[2rem]">
                                No rent receipts uploaded yet
                            </div>

                            <div v-if="!locked" class="relative z-10 mt-10">
                                 <input type="file" ref="hraFileInput" class="hidden" accept=".pdf,.jpg,.png" @change="(e) => handleFileUpload(e, 'HRA')">
                                 <button @click="$refs.hraFileInput.click()" class="w-full h-14 bg-white/10 border-2 border-dashed border-white/20 rounded-2xl text-xs font-black text-indigo-400 uppercase tracking-[0.2em] hover:bg-white group-hover/vault:text-slate-900 transition-all flex items-center justify-center gap-4 active:scale-95">
                                    <PlusIcon class="w-5 h-5" />
                                    Upload Rent Receipt
                                 </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEGMENT 04: STRATEGIC DEDUCTIONS (DEEP) -->
                <div v-if="currentStep === 'deductions'" :key="'deductions'" class="space-y-8">
                     <div class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-2xl shadow-indigo-500/30 flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden group">
                        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000"></div>
                        <div class="relative z-10">
                            <span class="text-xs font-black uppercase tracking-[0.4em] text-indigo-200">Total Deductions Declared</span>
                            <div class="text-4xl font-black tracking-tighter mt-2">{{ formatCurrency(totalDeductions) }}</div>
                        </div>
                        <button v-if="!locked" @click="saveDeclarations" class="relative z-10 h-14 px-12 bg-white text-indigo-600 rounded-2xl text-xs font-black uppercase tracking-[0.3em] shadow-xl hover:scale-105 active:scale-95 transition-all flex items-center gap-4">
                            <ShieldCheckIcon class="w-5 h-5" />
                            Save Declarations
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div v-for="section in deductionSections" :key="section.id" class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/20 overflow-hidden group/sec">
                            <div 
                                class="px-8 py-6 flex justify-between items-center cursor-pointer hover:bg-slate-50 transition-all duration-300"
                                @click="toggleAccordion(section.id)"
                            >
                                <div class="flex items-center gap-6">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors" :class="openSection === section.id ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-100 text-slate-400 group-hover/sec:bg-indigo-50'">
                                        <component :is="CalculatorIcon" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest group-hover/sec:text-indigo-600 transition-colors">{{ section.name }} <span class="text-slate-400 font-black ml-2 opacity-50">#{{ section.section_code }}</span></h4>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1 italic">Max Limit: {{ section.max_deduction ? formatCurrency(section.max_deduction) : 'No Limit' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8">
                                     <div class="text-right">
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-0.5">ALLOCATED</span>
                                        <span class="text-[15px] font-black" :class="declarationsMap[section.id]?.declared_amount > 0 ? 'text-indigo-600' : 'text-slate-300'">{{ formatCurrency(declarationsMap[section.id]?.declared_amount || 0) }}</span>
                                     </div>
                                     <ChevronDownIcon class="w-5 h-5 text-slate-300 transform transition-transform duration-500" :class="openSection === section.id ? 'rotate-180 text-indigo-500' : ''" />
                                </div>
                            </div>

                            <Transition
                                enter-active-class="transition duration-500 cubic-bezier(0.16, 1, 0.3, 1)"
                                enter-from-class="transform -translate-y-4 opacity-0"
                                enter-to-class="transform translate-y-0 opacity-100"
                            >
                                <div v-show="openSection === section.id" class="px-8 py-8 bg-slate-50/50 border-t border-slate-50 grid grid-cols-1 md:grid-cols-2 gap-10">
                                    <!-- Input -->
                                    <div class="space-y-4">
                                        <label class="px-2 text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Declared Amount (₹)</label>
                                         <div class="relative group/input">
                                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 font-black">₹</span>
                                            <input 
                                                type="number" 
                                                v-model.number="declarationsMap[section.id].declared_amount" 
                                                class="w-full h-16 bg-white border-2 border-slate-100 rounded-2xl pl-12 pr-6 text-[15px] font-black text-slate-900 focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all" 
                                                :disabled="locked"
                                                placeholder="0"
                                            >
                                        </div>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic opacity-60 ml-2">Estimated investment amount for this year</p>
                                    </div>

                                    <!-- Evidence Vault -->
                                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center relative overflow-hidden">
                                         <div class="absolute -right-4 -top-4 text-indigo-50/50">
                                            <CloudArrowUpIcon class="w-16 h-16" />
                                         </div>
                                         
                                         <div v-if="getProofs(section.id).length > 0" class="w-full space-y-2 mb-4 relative z-10">
                                            <div v-for="proof in getProofs(section.id)" :key="proof.id" class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100 group/item">
                                                 <span class="text-xs font-black uppercase text-slate-500 truncate max-w-[140px]">{{ proof.file_name }}</span>
                                                 <button v-if="!locked" @click="deleteProof(proof, section.id)" class="text-rose-400 hover:text-rose-600 transition-colors p-1"><TrashIcon class="w-3.5 h-3.5" /></button>
                                            </div>
                                         </div>
                                         <div v-else class="py-4 relative z-10">
                                             <DocumentTextIcon class="w-10 h-10 text-slate-100 mx-auto mb-2" />
                                             <p class="text-xs font-black text-slate-300 uppercase tracking-[0.3em]">No Proofs Uploaded</p>
                                         </div>

                                         <div v-if="!locked" class="w-full mt-auto relative z-10">
                                            <input type="file" :ref="el => fileInputs[section.id] = el" class="hidden" accept=".pdf,.jpg,.png" @change="(e) => handleFileUpload(e, section.id)">
                                             <button @click="triggerUpload(section.id)" class="w-full py-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl text-xs font-black text-slate-500 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all uppercase tracking-widest active:scale-95">Upload Proof Document</button>
                                         </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

                <!-- SEGMENT 05: EXTERNAL REVENUE (OTHER) -->
                <div v-if="currentStep === 'other'" :key="'other'" class="space-y-8">
                     <div class="bg-amber-600 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-amber-500/20 flex items-start gap-8 relative overflow-hidden group">
                        <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
                        <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center shrink-0 border border-white/20">
                            <InformationCircleIcon class="w-10 h-10" />
                        </div>
                        <div>
                            <h4 class="text-xl font-black uppercase tracking-tight">Other Income Sources</h4>
                            <p class="text-sm font-black text-amber-100 uppercase tracking-widest mt-2 leading-relaxed opacity-80 italic">
                                " Declare income from savings interest, freelance, rent, or any other source to help calculate your TDS correctly and avoid year-end tax mismatch. "
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div v-for="section in otherSections" :key="section.id" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-10 flex flex-col lg:flex-row items-center gap-12 group">
                             <div class="flex-1 lg:border-r lg:border-slate-50 lg:pr-12">
                                <h4 class="text-base font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ section.name }}</h4>
                                <div class="inline-flex px-3 py-1 bg-slate-900 text-white rounded-lg text-xs font-black tracking-widest uppercase mt-3">Sector: {{ section.section_code }}</div>
                            </div>
                            
                            <div class="w-full lg:w-72 space-y-3">
                                <label class="px-2 text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Annual Income Amount (₹)</label>
                                <div class="relative group/input">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 font-black">₹</span>
                                    <input 
                                        type="number" 
                                        v-model.number="declarationsMap[section.id].declared_amount" 
                                        class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-12 pr-6 text-[15px] font-black text-slate-900 focus:ring-8 focus:ring-amber-500/5 focus:border-amber-500 transition-all font-mono" 
                                        :disabled="locked"
                                    >
                                </div>
                            </div>

                             <div class="w-full lg:w-80 flex flex-col justify-end">
                                <label class="px-2 text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-3">Upload Proof</label>
                                <div v-if="getProofs(section.id).length > 0" class="flex items-center justify-between bg-emerald-50 rounded-2xl p-4 border border-emerald-100 mb-2">
                                     <div class="flex items-center gap-3">
                                        <DocumentTextIcon class="w-5 h-5 text-emerald-500" />
                                        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest truncate max-w-[140px]">{{ getProofs(section.id)[0].file_name }}</span>
                                     </div>
                                     <button v-if="!locked" @click="deleteProof(getProofs(section.id)[0], section.id)" class="text-emerald-300 hover:text-rose-500 transition-colors p-1"><TrashIcon class="w-4 h-4" /></button>
                                </div>
                                <button v-if="!locked && getProofs(section.id).length === 0" @click="triggerUpload(section.id)" class="w-full h-16 bg-white border-2 border-dashed border-slate-200 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-[0.2em] hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all flex items-center justify-center gap-4 active:scale-95">
                                    <CloudArrowUpIcon class="w-5 h-5" />
                                    Inject Certificate
                                </button>
                                <input type="file" :ref="el => fileInputs[section.id] = el" class="hidden" accept=".pdf,.jpg,.png" @change="(e) => handleFileUpload(e, section.id)">
                            </div>
                        </div>
                    </div>

                     <div class="flex justify-end pt-10" v-if="!locked">
                        <button @click="saveDeclarations" class="h-16 px-16 bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.4em] shadow-2xl shadow-slate-300 hover:bg-indigo-600 transition-all active:scale-95 flex items-center gap-4">
                            Save & Finish
                            <ArrowRightIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
