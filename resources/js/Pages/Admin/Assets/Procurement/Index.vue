<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    InformationCircleIcon, 
    ShoppingBagIcon, 
    PlusIcon, 
    LinkIcon,
    CurrencyRupeeIcon as CashIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ClockIcon,
    TruckIcon,
    TagIcon,
    TrashIcon,
    ArrowLeftIcon,
    WalletIcon,
    InboxArrowDownIcon,
    SparklesIcon,
    BuildingStorefrontIcon,
    ArchiveBoxIcon,
    CubeIcon,
    BoltIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    purchase_requests: Object,
    vendors: Array
});

// --- New PO Modal ---
const showModal = ref(false);
const form = useForm({
    vendor_id: '',
    items: [{ name: '', quantity: 1, unit_cost: 0 }]
});

const addItem = () => form.items.push({ name: '', quantity: 1, unit_cost: 0 });
const removeItem = (i) => form.items.splice(i, 1);

const totalCost = () => form.items.reduce((sum, i) => sum + (parseFloat(i.unit_cost) || 0) * (parseInt(i.quantity) || 0), 0);

const submit = () => {
    form.post(route('procurement.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.items = [{ name: '', quantity: 1, unit_cost: 0 }];
        }
    });
};

const statusConfig = (status) => {
    const map = {
        'Draft':    { label: 'Planning', color: 'text-slate-400', bg: 'bg-slate-50', dot: 'bg-slate-300' },
        'Approved': { label: 'Ready to Buy', color: 'text-indigo-600', bg: 'bg-indigo-50', dot: 'bg-indigo-500' },
        'Ordered':  { label: 'On the Way', color: 'text-amber-600', bg: 'bg-amber-50', dot: 'bg-amber-500' },
        'Received': { label: 'Arrived', color: 'text-emerald-600', bg: 'bg-emerald-50', dot: 'bg-emerald-500' },
    };
    return map[status] || { label: status, color: 'text-slate-400', bg: 'bg-slate-50', dot: 'bg-slate-300' };
};
</script>

<template>
    <Head title="Buy & Restock" />

    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative text-left">
        <div class="absolute -right-32 -top-32 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute -left-32 bottom-0 w-128 h-128 bg-emerald-500/5 rounded-full blur-[140px] pointer-events-none"></div>

        <!-- Strategic Header Terminal -->
        <div class="bg-white px-10 py-8 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-10 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 group/back shrink-0">
                    <ArrowLeftIcon class="w-6 h-6 group-hover/back:-translate-x-1 transition-transform" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-6">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Buy & Restock</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-white/10 leading-relaxed">
                                Planning & Acquisition Terminal. Order new hardware from verified vendors and track delivery logs.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none px-1">Supply Logistics & Procurement Matrix</p>
                </div>
            </div>
            
            <div class="relative z-10 flex items-center gap-8">
                <div class="px-7 py-3 bg-white border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
                     <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 leading-none font-outfit">Active Matrix Spend</span>
                     <span class="text-2xl font-black text-slate-900 tabular-nums leading-none tracking-tight">₹{{ (purchase_requests.total_buy || 0).toLocaleString() }}</span>
                </div>
                <button @click="showModal = true" class="h-14 px-8 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center gap-4 active:scale-95 group/btn border border-slate-800">
                    <PlusIcon class="w-5 h-5 text-indigo-400 group-hover/btn:rotate-90 transition-transform duration-700" />
                    New Order
                </button>
            </div>
        </div>

        <!-- Order Grid Terminal -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-12 italic relative z-10 px-2">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-12 italic">
                <div v-for="req in purchase_requests.data" :key="req.id" class="group relative bg-white/80 backdrop-blur-3xl rounded-[4rem] border-4 border-white p-10 hover:shadow-3xl hover:border-indigo-400 transition-all duration-700 italic shadow-lg flex flex-col h-fit">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/20 via-transparent to-transparent pointer-events-none italic"></div>
                    
                    <div class="relative z-10 italic">
                        <div class="flex justify-between items-center mb-10 italic border-b-2 border-slate-50 pb-8 group-hover:pb-10 transition-all">
                             <div class="flex items-center gap-6 italic">
                                <div class="w-16 h-16 bg-slate-50 border-4 border-white rounded-[1.5rem] flex items-center justify-center text-slate-400 shadow-xl group-hover:bg-indigo-600 group-hover:text-white transition-all duration-700 italic shrink-0">
                                    <ShoppingBagIcon class="w-8 h-8" />
                                </div>
                                <div class="italic">
                                     <h4 class="text-2xl font-black text-slate-950 uppercase tracking-tighter italic leading-none mb-3">#{{ req.po_number || 'TRK_000' }}</h4>
                                     <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest italic font-mono">{{ req.created_at || 'JAN_2024' }}</span>
                                </div>
                             </div>
                             <div class="px-6 py-3 rounded-2xl border-2 text-[10px] font-black uppercase tracking-[0.3em] inline-flex items-center gap-4 italic shadow-lg" :class="statusConfig(req.status).bg + ' ' + statusConfig(req.status).color">
                                <div class="w-2.5 h-2.5 rounded-full" :class="statusConfig(req.status).dot + (req.status === 'Ordered' ? ' animate-pulse shadow-[0_0_10px_rgba(245,158,11,1)]' : '')"></div>
                                {{ statusConfig(req.status).label }}
                             </div>
                        </div>

                        <div class="space-y-6 mb-10 italic">
                             <div class="flex items-center justify-between bg-slate-50 p-8 rounded-[2.5rem] border-2 border-white shadow-inner italic">
                                 <div class="flex items-center gap-6 italic">
                                    <BuildingStorefrontIcon class="w-7 h-7 text-rose-400 shadow-sm" />
                                    <div class="italic">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] italic mb-1 leading-none">Vendor Source</p>
                                        <h5 class="text-lg font-black text-slate-950 uppercase tracking-tight italic leading-none">{{ req.vendor?.name || 'GENERIC_VENDOR' }}</h5>
                                    </div>
                                 </div>
                                 <LinkIcon class="w-5 h-5 text-slate-200 group-hover:text-indigo-600 transition-colors italic" />
                             </div>

                             <div class="bg-indigo-50/50 p-8 rounded-[3rem] border-2 border-white italic">
                                <div class="flex justify-between items-center mb-6 italic border-b border-indigo-100 pb-4">
                                     <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.5em] italic">Manifest Hub</span>
                                     <span class="text-[9px] font-black text-slate-300 uppercase italic">{{ req.items?.length || 0 }} Categories</span>
                                </div>
                                <ul class="space-y-4 italic">
                                    <li v-for="item in (req.items || []).slice(0, 2)" :key="item.id" class="flex justify-between items-center italic">
                                        <div class="flex items-center gap-4 italic truncate max-w-[150px]">
                                            <div class="w-2 h-2 rounded-full bg-indigo-300"></div>
                                            <span class="text-xs font-black text-slate-600 uppercase tracking-widest italic truncate">{{ item.name }}</span>
                                        </div>
                                        <span class="text-sm font-black text-slate-950 italic">x{{ item.quantity }}</span>
                                    </li>
                                    <li v-if="req.items?.length > 2" class="text-[9px] font-black text-indigo-300 uppercase italic text-center pt-2">+ {{ req.items.length - 2 }} extra lines</li>
                                </ul>
                             </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 italic mt-auto">
                            <div class="flex flex-col italic">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.5em] italic leading-none mb-2">Total Net Value</span>
                                <span class="text-3xl font-black text-slate-950 tabular-nums italic leading-none tracking-tighter shadow-indigo-500/10">₹{{ (req.total_cost || 0).toLocaleString() }}</span>
                            </div>
                            <div class="flex gap-4 italic">
                                 <button class="w-14 h-14 bg-white border-2 border-slate-100 text-slate-400 rounded-2xl flex items-center justify-center shadow-xl hover:bg-slate-950 hover:text-white transition-all italic active:scale-75">
                                    <InboxArrowDownIcon class="w-7 h-7" />
                                 </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Injection Node -->
                <div @click="showModal = true" class="bg-white rounded-[4rem] border-4 border-dashed border-slate-100 p-12 flex flex-col items-center justify-center text-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 hover:text-indigo-600 transition-all group cursor-pointer min-h-[450px] shadow-sm hover:shadow-indigo-500/5 relative overflow-hidden italic">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity italic"></div>
                    <div class="w-28 h-28 rounded-[2.5rem] border-4 border-dashed border-slate-100 flex items-center justify-center mb-12 group-hover:rotate-180 group-hover:border-indigo-500 transition-all duration-1000 group-hover:scale-110 relative z-10 bg-white shadow-2xl italic shadow-indigo-500/5">
                        <PlusIcon class="w-14 h-14 text-slate-100 group-hover:text-indigo-600 transition-colors italic" />
                    </div>
                    <div class="text-center relative z-10 italic">
                        <span class="block text-2xl font-black uppercase tracking-tighter text-slate-950 italic mb-4 leading-none">Draft Log Matrix</span>
                        <span class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.5em] italic opacity-60">Architect a new purchase order.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Order Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="4xl">
             <div class="bg-white rounded-3xl overflow-hidden text-left">
                <div class="p-8 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Initialize PO</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Create new acquisition log</p>
                    </div>
                    <button @click="showModal = false" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-500 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-10 space-y-10">
                    <div class="space-y-4">
                        <InputLabel value="Resource Source (Vendor)" class="px-2" />
                        <select v-model="form.vendor_id" class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all appearance-none cursor-pointer" required>
                             <option value="">CHOOSE_VENDOR...</option>
                             <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name.toUpperCase() }}</option>
                        </select>
                        <InputError :message="form.errors.vendor_id" />
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <InputLabel value="Items Matrix" class="px-2" />
                            <button type="button" @click="addItem" class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest flex items-center gap-2 hover:bg-indigo-50 px-4 py-2 rounded-xl transition-all">
                                <PlusIcon class="w-4 h-4" />
                                Add Row
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2 no-scrollbar">
                            <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-100 group/row relative">
                                <div class="col-span-6">
                                    <InputLabel value="Item Name" class="mb-2" />
                                    <TextInput v-model="item.name" type="text" class="w-full" placeholder="E.G. LENOVO T14" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Qty" class="mb-2" />
                                    <TextInput v-model="item.quantity" type="number" class="w-full text-center" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Unit Cost" class="mb-2" />
                                    <TextInput v-model="item.unit_cost" type="number" class="w-full" />
                                </div>
                                <div class="col-span-1 flex items-end justify-center pb-2">
                                    <button v-if="form.items.length > 1" @click="removeItem(i)" type="button" class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-rose-500 transition-colors">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-8 border-t border-slate-100 mt-6">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 leading-none font-outfit">Total Payload Spend</span>
                            <span class="text-3xl font-black text-slate-900 font-mono tracking-tight leading-none">₹{{ totalCost().toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center gap-6">
                            <SecondaryButton @click="showModal = false" type="button">Discard</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="form.processing" class="h-14 px-10">
                                <ArrowPathIcon v-if="form.processing" class="w-5 h-5 animate-spin mr-3" />
                                <CheckCircleIcon v-else class="w-5 h-5 mr-3" />
                                {{ form.processing ? 'Syncing...' : 'Confirm PO' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
             </div>
        </Modal>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
