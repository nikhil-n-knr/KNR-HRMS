<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CubeIcon, 
    LinkIcon, 
    MapPinIcon, 
    IdentificationIcon, 
    CurrencyDollarIcon,
    CalendarDaysIcon,
    ServerStackIcon,
    DocumentTextIcon,
    WrenchIcon,
    ArrowLeftIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    categories: Array,
    locations: Array
});

const form = useForm({
    name: '',
    category_id: '',
    location_id: '',
    serial_number: '',
    make: '',
    model: '',
    purchase_cost: '',
    purchase_date: new Date().toISOString().split('T')[0],
    is_serialized: false
});

const submit = () => {
    form.post(route('admin.assets.store'));
};
</script>

<template>
    <Head title="Initialize New Asset" />
    <MainLayout>
        <div class="max-w-5xl mx-auto space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Strategic Header Terminal -->
            <div class="bg-slate-900 rounded-[3rem] p-10 md:p-14 border border-slate-800 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -right-32 -top-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                <div class="absolute -left-16 bottom-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px] group-hover:scale-125 transition-transform duration-1000"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-8">
                        <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-14 h-14 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all shadow-sm active:scale-95">
                            <ArrowLeftIcon class="w-6 h-6" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400 shadow-inner">
                                    <CubeIcon class="w-5 h-5" />
                                </div>
                                <h1 class="text-3xl font-black text-white uppercase tracking-tight">Initialize Asset</h1>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] ml-14">System physical node registration protocol</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Form Matrix -->
            <form @submit.prevent="submit" class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-10 md:p-14 relative overflow-hidden">
                <div class="space-y-12 relative z-10">
                    
                    <!-- Section: Primary Identification -->
                    <div class="space-y-8">
                        <h3 class="text-base font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-3 border-b border-slate-100 pb-4">
                            <IdentificationIcon class="w-5 h-5 text-indigo-500" />
                            Primary Node Identification
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Designation Name</label>
                                <input v-model="form.name" type="text" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm" placeholder="e.g. MacBook Pro M3" required>
                                <p v-if="form.errors.name" class="text-sm font-black text-rose-500 uppercase tracking-widest px-2">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Architectural Class (Category)</label>
                                <select v-model="form.category_id" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer shadow-sm" required>
                                    <option value="" disabled selected>Select Class Node</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-sm font-black text-rose-500 uppercase tracking-widest px-2">{{ form.errors.category_id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8">
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Physical Vector (Location)</label>
                                <div class="relative">
                                    <MapPinIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                    <select v-model="form.location_id" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] pl-16 pr-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer shadow-sm text-opacity-80">
                                        <option value="" disabled selected>Select Geographic Vector</option>
                                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.location_id" class="text-sm font-black text-rose-500 uppercase tracking-widest px-2">{{ form.errors.location_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Hardware Specifications -->
                    <div class="space-y-8 pt-8 border-t border-slate-100">
                        <h3 class="text-base font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-3 border-b border-slate-100 pb-4">
                            <WrenchIcon class="w-5 h-5 text-emerald-500" />
                            Hardware Specifications
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Manufacturer (Make)</label>
                                <input v-model="form.make" type="text" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm" placeholder="e.g. Apple">
                            </div>
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Model Topology</label>
                                <input v-model="form.model" type="text" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm" placeholder="e.g. A2991">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Financial Telemetry & Serialization -->
                    <div class="space-y-8 pt-8 border-t border-slate-100">
                        <h3 class="text-base font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-3 border-b border-slate-100 pb-4">
                            <CurrencyDollarIcon class="w-5 h-5 text-amber-500" />
                            Capital Impact & Tracking Protocol
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Acquisition Capital (Cost)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-base font-black text-slate-400">INR</span>
                                    <input v-model="form.purchase_cost" type="number" step="0.01" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] pl-16 pr-6 text-lg font-black font-mono tracking-tighter text-slate-900 uppercase focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm" placeholder="0.00">
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Temporal Onboarding (Date)</label>
                                <div class="relative">
                                    <CalendarDaysIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                    <input v-model="form.purchase_date" type="date" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] pl-16 pr-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm cursor-pointer border-opacity-50">
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Serialization Toggle -->
                        <div class="p-8 bg-indigo-50/50 rounded-[2.5rem] border border-indigo-100/50 space-y-6">
                            <label class="flex items-center gap-4 cursor-pointer group w-fit">
                                <div class="relative">
                                    <input v-model="form.is_serialized" type="checkbox" class="sr-only peer">
                                    <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600 shadow-inner"></div>
                                </div>
                                <div>
                                    <span class="block text-base font-black text-slate-900 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Individual Node Tracking (Serialization)</span>
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-widest mt-1 opacity-80">Requires unique hardware identifier</span>
                                </div>
                            </label>

                            <div v-if="form.is_serialized" class="animate-in slide-in-from-top-4 fade-in duration-300">
                                <label class="text-sm font-black text-indigo-500 uppercase tracking-widest px-2 block mb-3">Unique Hardware Identity Array (S/N)</label>
                                <div class="relative">
                                    <ServerStackIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-indigo-400" />
                                    <input v-model="form.serial_number" type="text" placeholder="ENTER EXACT S/N..." class="w-full h-16 bg-white border-2 border-indigo-200 rounded-[2rem] pl-16 pr-6 text-[14px] font-black font-mono tracking-tighter text-indigo-900 uppercase focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-lg shadow-indigo-500/10 placeholder:text-indigo-200" required>
                                </div>
                                <p v-if="form.errors.serial_number" class="text-sm font-black text-rose-500 uppercase tracking-widest px-2 mt-2">{{ form.errors.serial_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Interface -->
                    <div class="pt-10 mt-10 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-6">
                        <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-2">
                            <SparklesIcon class="w-4 h-4 text-emerald-400" />
                            Validation checks active
                        </div>
                        <div class="flex gap-4 w-full sm:w-auto">
                            <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="flex-1 sm:flex-none h-16 px-10 bg-slate-50 hover:bg-slate-100 text-slate-500 rounded-[2rem] text-sm font-black uppercase tracking-[0.3em] transition-all flex items-center justify-center border border-slate-200 active:scale-95 shadow-sm">
                                Abort
                            </Link>
                            <button type="submit" :disabled="form.processing" class="flex-1 sm:flex-none h-16 px-14 bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.3em] hover:bg-indigo-600 transition-all flex items-center justify-center gap-4 shadow-2xl shadow-slate-900/30 active:scale-95 group/submit disabled:opacity-50 disabled:cursor-not-allowed">
                                <div v-if="form.processing" class="w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                                <CubeIcon v-else class="w-5 h-5 text-indigo-400 group-hover/submit:scale-110 transition-transform" />
                                <span>{{ form.processing ? 'Compiling...' : 'Commit Node' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.6) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
