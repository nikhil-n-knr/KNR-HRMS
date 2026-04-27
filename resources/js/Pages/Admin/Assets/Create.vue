<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CubeIcon, 
    ArrowLeftIcon,
    IdentificationIcon, 
    CurrencyRupeeIcon as CashIcon,
    CalendarDaysIcon,
    MapPinIcon,
    CpuChipIcon,
    InformationCircleIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    ArchiveBoxIcon,
    DocumentTextIcon,
    TagIcon,
    BoltIcon,
    SparklesIcon,
    BuildingStorefrontIcon,
    ArchiveBoxArrowDownIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    categories: Array,
    locations: Array
});

const form = useForm({
    name: '',
    category_id: '',
    current_location_node_id: '',
    serial_number: '',
    make: '',
    model: '',
    purchase_cost: '',
    purchase_date: new Date().toISOString().split('T')[0],
    is_serialized: true
});

const submit = () => {
    form.post(route('admin.assets.store'));
};
</script>

<template>
    <Head title="Add New Item" />
    
    <div class="h-full flex flex-col font-outfit -m-8 p-12 bg-slate-50 min-h-screen relative overflow-hidden text-left">
        <div class="absolute -right-32 -top-32 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute -left-32 bottom-0 w-128 h-128 bg-emerald-500/5 rounded-full blur-[140px] pointer-events-none"></div>

        <!-- Strategic Header -->
        <div class="bg-white p-10 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl mb-10 shadow-sm border border-slate-200">
            <div class="absolute -right-32 -top-32 w-96 h-96 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 group/back shrink-0">
                    <ArrowLeftIcon class="h-6 w-6 group-hover/back:-translate-x-1 transition-transform" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-6">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Add New Item</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-white/10 leading-relaxed">
                                Global Resource Initialization Terminal. Use this interface to register individual assets into the primary matrix.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none">New Resource Entry & Registration Terminal</p>
                </div>
            </div>

            <div class="flex items-center gap-6 z-10">
                 <div class="px-5 py-2 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3 shadow-sm">
                    <BoltIcon class="w-4 h-4 text-emerald-500" />
                    <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Swift Entry Active</span>
                 </div>
            </div>
        </div>

        <!-- Registration Portal Terminal -->
        <form @submit.prevent="submit" class="max-w-[1000px] mx-auto w-full bg-white rounded-3xl shadow-sm p-12 relative overflow-hidden z-10 animate-in zoom-in-95 duration-700 border border-slate-200 text-left">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,_var(--tw-gradient-stops))] from-indigo-50/10 via-transparent to-transparent pointer-events-none"></div>
            
            <div class="space-y-12 relative z-10">
                
                <!-- Section: Facts -->
                <div class="space-y-10">
                    <div class="flex items-center gap-6 border-b border-slate-100 pb-8">
                        <div class="w-14 h-14 bg-slate-50 border border-slate-200 text-slate-400 rounded-2xl flex items-center justify-center shadow-sm group hover:rotate-6 transition-transform shrink-0">
                            <ArchiveBoxIcon class="w-8 h-8 text-indigo-500" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Basic Identifier</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-2">
                        <div class="space-y-3">
                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Resource Name</label>
                            <input v-model="form.name" type="text" class="w-full h-16 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-xl font-black text-slate-900 uppercase tracking-tight focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 shadow-sm transition-all placeholder:text-slate-200" placeholder="E.G. MACBOOK PRO M3" required>
                        </div>

                        <div class="space-y-3">
                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Classification (Type)</label>
                            <select v-model="form.category_id" class="w-full h-16 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-sm font-bold text-slate-400 focus:text-slate-900 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all appearance-none cursor-pointer shadow-sm">
                                <option value="" disabled selected>CHOOSE_TYPE...</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name.toUpperCase() }}</option>
                            </select>
                        </div>

                        <div class="space-y-3">
                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Deployment Room (Location)</label>
                            <select v-model="form.current_location_node_id" class="w-full h-16 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-sm font-bold text-slate-400 focus:text-slate-900 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all appearance-none cursor-pointer shadow-sm">
                                <option value="" disabled selected>CHOOSE_ROOM...</option>
                                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name.toUpperCase() }}</option>
                            </select>
                        </div>

                         <div class="space-y-3">
                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Serial Matrix / Tag</label>
                            <input v-model="form.serial_number" type="text" class="w-full h-16 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-xl font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all font-mono shadow-sm placeholder:text-slate-200" placeholder="E.G. S/N: 123-ABC">
                        </div>
                    </div>
                </div>

                <!-- Section: Secondary Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-2">
                    
                    <div class="space-y-10 bg-slate-50 p-10 rounded-3xl border border-slate-100 shadow-sm">
                        <div class="flex items-center gap-6 border-b border-white pb-6">
                             <TagIcon class="w-8 h-8 text-indigo-400 shadow-indigo-500/10" />
                             <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">Details</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <label class="px-6 text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">Brand Name</label>
                                <input v-model="form.make" type="text" class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-sm font-bold text-slate-900 focus:border-indigo-400 transition-all shadow-sm" placeholder="SHIPPING BRAND...">
                            </div>
                            <div class="space-y-3">
                                <label class="px-6 text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">Model Code</label>
                                <input v-model="form.model" type="text" class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-sm font-bold text-slate-900 focus:border-indigo-400 transition-all shadow-sm" placeholder="VERSION X.0...">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-10 bg-emerald-50/20 p-10 rounded-3xl border border-emerald-50 shadow-sm">
                        <div class="flex items-center gap-6 border-b border-white pb-6">
                             <CashIcon class="w-8 h-8 text-emerald-500 shadow-emerald-500/10" />
                             <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">Financials</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <label class="px-6 text-[8px] font-bold text-emerald-600 uppercase tracking-widest leading-none">Buy Cost (₹)</label>
                                <input v-model="form.purchase_cost" type="number" class="w-full h-14 bg-white border border-slate-200 rounded-xl px-4 text-base font-black text-emerald-600 focus:border-emerald-500 transition-all shadow-sm tabular-nums" placeholder="0.00">
                            </div>
                            <div class="space-y-3">
                                <label class="px-6 text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">Entry Date</label>
                                <input v-model="form.purchase_date" type="date" class="w-full h-14 bg-white border border-slate-200 rounded-xl px-4 text-xs font-bold text-slate-900 focus:border-indigo-400 transition-all shadow-sm">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Execution -->
                <div class="flex items-center justify-between pt-12 border-t border-slate-100 mt-10 pb-4">
                    <button type="button" @click="$inertia.back()" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-all">Discard Matrix</button>
                    <button type="submit" :disabled="form.processing" class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center gap-6 active:scale-95 disabled:opacity-30 group/save border border-slate-800">
                        <ArrowPathIcon v-if="form.processing" class="w-6 h-6 animate-spin" />
                        <CheckCircleIcon v-else class="w-6 h-6 text-indigo-400 group-hover:scale-125 transition-transform" />
                        <span>{{ form.processing ? 'Syncing...' : 'Confirm Entry' }}</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
