<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { Link, useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PrinterIcon, 
    PencilSquareIcon, 
    ArrowLeftStartOnRectangleIcon, 
    LinkIcon,
    WrenchScrewdriverIcon,
    CurrencyDollarIcon,
    HeartIcon,
    SparklesIcon,
    CalendarDaysIcon,
    IdentificationIcon,
    TagIcon,
    ClockIcon,
    CheckCircleIcon,
    CpuChipIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    asset: Object,
    timeline: Array,
    qrCode: String
});

const showServiceModal = ref(false);
const serviceForm = useForm({
    type: 'Repair',
    description: '',
    cost: 0,
    service_date: new Date().toISOString().split('T')[0]
});

const submitService = () => {
    serviceForm.post(route('admin.assets.maintenance.store', props.asset.id), {
        onSuccess: () => {
            showServiceModal.value = false;
            serviceForm.reset();
        }
    });
};

const getStatusStyles = (status) => {
    switch (status) {
        case 'Available': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'Assigned': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'In_Service': return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};
</script>

<template>
    <Head :title="'Intelligence Node: ' + asset.name" />
    <MainLayout>
        <div class="max-w-[1600px] mx-auto space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            
            <!-- Strategic Header Terminal -->
            <div class="bg-slate-900 rounded-[3rem] p-10 md:p-14 border border-slate-800 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
                <!-- Premium Background Elements -->
                <div class="absolute -right-32 -top-32 w-[30rem] h-[30rem] bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                <div class="absolute -left-16 bottom-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px] group-hover:scale-125 transition-transform duration-1000"></div>

                <div class="relative z-10 flex flex-col xl:flex-row justify-between items-start gap-12">
                    <div class="flex flex-col md:flex-row gap-10 lg:gap-14">
                        <!-- QR Code & Visual Identity -->
                        <div class="flex flex-col items-center gap-6">
                            <div class="w-40 h-40 bg-white/5 backdrop-blur-xl rounded-[2rem] p-4 border border-white/10 shadow-2xl flex items-center justify-center relative group/qr overflow-hidden hover:rotate-3 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent"></div>
                                <div class="relative z-10 w-full h-full [&>svg]:w-full [&>svg]:h-full [&>svg]:text-white" v-html="qrCode"></div>
                                <div class="absolute inset-0 bg-slate-900/95 flex flex-col items-center justify-center opacity-0 group-hover/qr:opacity-100 transition-opacity backdrop-blur-sm">
                                    <TagIcon class="w-8 h-8 text-indigo-400 mb-3" />
                                    <span class="text-sm font-black text-white uppercase tracking-[0.3em]">{{ asset.asset_code }}</span>
                                </div>
                            </div>
                            <span class="text-sm font-black text-indigo-300 uppercase tracking-[0.4em] font-mono px-4 py-2 bg-indigo-950/50 border border-indigo-500/20 rounded-xl shadow-inner">
                                NODE: {{ asset.asset_code }}
                            </span>
                        </div>
                        
                        <div class="space-y-8">
                            <div>
                                <div class="flex flex-wrap items-center gap-5 mb-4">
                                    <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight uppercase leading-none">{{ asset.name }}</h1>
                                    <span class="px-4 py-1.5 rounded-lg text-sm font-black uppercase tracking-[0.3em] border shadow-sm backdrop-blur-md" :class="getStatusStyles(asset.status)">
                                        <span class="w-2 h-2 rounded-full inline-block mr-2" :class="asset.status === 'Available' ? 'bg-emerald-400 animate-pulse' : 'bg-current'"></span>
                                        {{ asset.status.replace('_', ' ') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 bg-white/5 w-fit px-4 py-2 rounded-xl border border-white/5">
                                    <IdentificationIcon class="w-5 h-5 text-indigo-400" />
                                    <p class="text-base font-black text-slate-300 uppercase tracking-[0.3em] font-mono leading-none pt-0.5">S/N: {{ asset.serial_number || 'UNKNOWN_LOG_ENTRY' }}</p>
                                </div>
                            </div>
                            
                            <!-- Telemetry Array -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
                                <div class="bg-white/5 p-5 rounded-[1.5rem] border border-white/5 hover:border-white/10 transition-colors hover:bg-white/10">
                                    <span class="block text-sm font-black text-slate-500 uppercase tracking-[0.3em] mb-3 leading-none">Class Node</span>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                            <TagIcon class="w-4 h-4" />
                                        </div>
                                        <span class="text-base font-black text-white uppercase tracking-widest">{{ asset.category?.name || 'GENERIC' }}</span>
                                    </div>
                                </div>
                                <div class="bg-white/5 p-5 rounded-[1.5rem] border border-white/5 hover:border-white/10 transition-colors hover:bg-white/10">
                                    <span class="block text-sm font-black text-slate-500 uppercase tracking-[0.3em] mb-3 leading-none">Acquisition Date</span>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                                            <CalendarDaysIcon class="w-4 h-4" />
                                        </div>
                                        <span class="text-base font-black text-white uppercase tracking-widest font-mono">{{ asset.purchase_date || 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="bg-white/5 p-5 rounded-[1.5rem] border border-white/5 hover:border-white/10 transition-colors hover:bg-white/10">
                                    <span class="block text-sm font-black text-slate-500 uppercase tracking-[0.3em] mb-3 leading-none">Capital Load</span>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400">
                                            <CurrencyDollarIcon class="w-4 h-4" />
                                        </div>
                                        <span class="text-base font-black text-white uppercase tracking-tight tabular-nums font-mono">₹{{ Number(asset.purchase_cost).toLocaleString() }}</span>
                                    </div>
                                </div>
                                 <div class="bg-white/5 p-5 rounded-[1.5rem] border border-white/5 hover:border-white/10 transition-colors hover:bg-white/10 relative overflow-hidden">
                                    <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-blue-500/10 rounded-full blur-xl"></div>
                                    <span class="block text-sm font-black text-slate-500 uppercase tracking-[0.3em] mb-3 leading-none relative z-10">Current Target</span>
                                    <div class="flex items-center gap-3 relative z-10">
                                        <div class="h-8 w-8 rounded-lg bg-slate-800 border border-slate-600 flex items-center justify-center text-sm text-white font-black uppercase shadow-inner">
                                            <span v-if="asset.assignment">{{ asset.assignment.user.name.charAt(0) }}</span>
                                            <span v-else class="text-slate-500">Ø</span>
                                        </div>
                                        <span class="text-base font-black uppercase tracking-tight truncate max-w-[100px]" :class="asset.assignment ? 'text-blue-400' : 'text-slate-500 italic'">
                                            {{ asset.assignment?.user?.name || 'In_Depository' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Terminal Configured for Dark Theme -->
                    <div class="flex flex-col sm:flex-row xl:flex-col gap-4 w-full xl:w-72 shrink-0">
                         <Link :href="route('admin.assets.label', asset.id)" class="flex-1 h-14 bg-white/5 border border-white/10 text-slate-300 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-white/10 hover:text-white transition-all flex items-center justify-center gap-3 active:scale-95 group/btn">
                            <PrinterIcon class="h-5 w-5 text-slate-400 group-hover/btn:text-white transition-all" />
                            Render Registry Label
                        </Link>
                        <button class="flex-1 h-14 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] hover:bg-indigo-500 transition-all flex items-center justify-center gap-3 shadow-[0_0_30px_rgba(79,70,229,0.3)] active:scale-95 group/btn">
                            <PencilSquareIcon class="h-5 w-5 text-indigo-200 group-hover/btn:rotate-12 transition-transform" />
                            Modify Parameters
                        </button>
                        
                        <div class="flex gap-4">
                            <Link v-if="asset.status === 'Assigned'" :href="route('admin.assets.return', asset.id)" method="post" as="button" :data="{ condition: 'Good' }" class="flex-1 h-14 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center gap-3 shadow-sm active:scale-95 group/btn">
                                <ArrowLeftStartOnRectangleIcon class="h-5 w-5 group-hover/btn:-translate-x-1 transition-transform" />
                                Recall
                            </Link>
                            <button v-else class="flex-1 h-14 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-3 shadow-sm active:scale-95 group/btn">
                                <LinkIcon class="h-5 w-5 group-hover/btn:scale-110 transition-transform" />
                                Deploy
                            </button>
                            
                             <button @click="showServiceModal = true" class="w-14 h-14 bg-amber-500/10 border border-amber-500/20 text-amber-500 rounded-2xl flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-sm active:scale-95" title="Initialize Maintenance">
                                <WrenchScrewdriverIcon class="h-6 w-6" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                <!-- TCO Intelligence & Chronology -->
                <div class="lg:col-span-2 space-y-10">
                    
                    <!-- Advanced Predictive Telemetry -->
                    <div v-if="asset.tco_analysis" class="bg-white rounded-[3rem] p-10 md:p-14 border border-slate-100 shadow-2xl shadow-slate-200/40 relative overflow-hidden group/tco">
                        <div class="absolute -right-20 -top-20 w-80 h-80 bg-slate-50 rounded-full blur-3xl group-hover/tco:bg-indigo-50 transition-all duration-1000"></div>
                        <CpuChipIcon class="absolute right-10 top-10 w-40 h-40 text-slate-100 opacity-50 group-hover/tco:rotate-90 transition-transform duration-[2s]" />
                        
                        <div class="relative z-10 space-y-12">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-4">
                                    Cost Analysis Matrix
                                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-sm font-black uppercase tracking-[0.3em] shadow-sm flex items-center gap-2">
                                        <SparklesIcon class="w-3 h-3" /> AI_Assisted
                                    </span>
                                </h3>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">Comprehensive lifecycle expenditure simulation</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                                <div class="space-y-4">
                                    <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center shadow-inner">
                                        <CurrencyDollarIcon class="w-6 h-6" />
                                    </div>
                                    <h4 class="text-slate-400 text-sm font-black uppercase tracking-[0.3em]">Cost / Temporal Unit</h4>
                                    <div class="text-3xl font-black text-slate-900 font-mono tracking-tighter">₹{{ asset.tco_analysis.cost_per_day }}</div>
                                    <div class="inline-block px-3 py-1 bg-slate-50 border border-slate-100 rounded-lg text-sm font-black text-slate-500 uppercase tracking-widest mt-2">
                                        Index: ₹{{ (asset.tco_analysis.total_tco || 0).toLocaleString() }}
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center shadow-inner">
                                        <WrenchScrewdriverIcon class="w-6 h-6" />
                                    </div>
                                    <h4 class="text-slate-400 text-sm font-black uppercase tracking-[0.3em]">Maint. Expenditure</h4>
                                    <div class="text-3xl font-black text-slate-900 font-mono tracking-tighter">₹{{ (asset.tco_analysis.total_maintenance || 0).toLocaleString() }}</div>
                                    <div class="inline-block px-3 py-1 bg-slate-50 border border-slate-100 rounded-lg text-sm font-black text-slate-500 uppercase tracking-widest mt-2">
                                        {{ asset.maintenance_logs?.length || 0 }} Events Logged
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-inner" :class="asset.tco_analysis.health_score > 50 ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500'">
                                        <HeartIcon class="w-6 h-6" />
                                    </div>
                                    <h4 class="text-slate-400 text-sm font-black uppercase tracking-[0.3em]">Structural Integrity</h4>
                                    <div class="flex items-end gap-1 font-mono">
                                        <div class="text-3xl font-black tracking-tighter" :class="asset.tco_analysis.health_score > 50 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ asset.tco_analysis.health_score }}
                                        </div>
                                        <span class="text-sm font-black text-slate-400 mb-1">%</span>
                                    </div>
                                    <div class="inline-block px-3 py-1 bg-slate-50 border border-slate-100 rounded-lg text-sm font-black text-slate-500 uppercase tracking-widest mt-2">
                                        Vitality Matrix
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center shadow-inner">
                                        <CpuChipIcon class="w-6 h-6" />
                                    </div>
                                    <h4 class="text-slate-400 text-sm font-black uppercase tracking-[0.3em]">AI Directive</h4>
                                    <div class="inline-flex items-center px-4 py-3 bg-slate-900 rounded-2xl text-white shadow-xl">
                                         <span class="text-sm font-black uppercase tracking-[0.3em]" :class="asset.tco_analysis.recommendation.includes('Scrap') ? 'text-rose-400' : 'text-emerald-400'">
                                             {{ asset.tco_analysis.recommendation }}
                                         </span>
                                    </div>
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2 leading-tight">Recommended Action</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chronological Timeline -->
                    <div class="bg-white p-10 md:p-14 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 relative group">
                        <div class="flex justify-between items-center mb-16">
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Temporal Registry</h3>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">Immutable chain of custody events</p>
                            </div>
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center text-slate-400 shadow-sm group-hover:text-indigo-600 transition-colors duration-500">
                                <ClockIcon class="w-8 h-8" />
                            </div>
                        </div>
                        
                        <div class="relative pl-12 border-l-2 border-slate-100 space-y-16">
                            <div v-for="(event, index) in timeline" :key="index" class="relative group/event">
                                <!-- Specialized Temporal Marker -->
                                <div class="absolute -left-[71px] top-1/2 -translate-y-1/2 h-16 w-16 rounded-2xl border-[6px] border-white shadow-xl flex items-center justify-center transition-all duration-300 group-hover/event:scale-110 group-hover/event:rotate-12 z-10" 
                                     :class="{
                                        'bg-emerald-50 text-emerald-500': event.color === 'emerald',
                                        'bg-blue-50 text-blue-500': event.color === 'blue',
                                        'bg-amber-50 text-amber-500': event.color === 'amber',
                                        'bg-slate-50 text-slate-500': !event.color
                                     }">
                                     <CheckCircleIcon class="w-6 h-6 shadow-sm" />
                                </div>

                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-6 bg-white p-8 rounded-[2rem] border border-slate-100 hover:border-indigo-100 hover:bg-slate-50/50 transition-all duration-300 group-hover/event:shadow-2xl group-hover/event:shadow-indigo-500/5 group-hover/event:-translate-y-1">
                                    <div class="space-y-4 flex-1">
                                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-wide group-hover/event:text-indigo-600 transition-colors">{{ event.title }}</h4>
                                        <p class="text-base font-black text-slate-400 uppercase tracking-[0.2em] leading-relaxed italic opacity-90 max-w-lg">
                                            " {{ event.description }} "
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right shrink-0 flex flex-col justify-center gap-4 border-t sm:border-t-0 sm:border-l border-slate-100 pt-4 sm:pt-0 sm:pl-8">
                                        <span class="text-sm font-black text-slate-900 uppercase tracking-[0.3em] font-mono leading-none bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm inline-block">
                                            {{ new Date(event.date).toLocaleDateString() }}
                                        </span>
                                        <span class="inline-flex justify-center items-center px-4 py-2 rounded-xl text-sm font-black uppercase tracking-[0.2em] bg-white border border-slate-200 shadow-sm">
                                            {{ event.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="timeline.length === 0" class="py-32 text-center grayscale opacity-30">
                                <ClockIcon class="h-20 w-20 mx-auto text-slate-300 mb-6 animate-pulse" />
                                <span class="text-base font-black text-slate-400 uppercase tracking-[0.4em]">Zero temporal shifts analyzed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contextual Side Bar -->
                <div class="space-y-10 sticky top-10">
                     <!-- Resource Specifications -->
                     <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 relative overflow-hidden group/specs">
                        <div class="absolute -right-6 top-1/2 -translate-y-1/2 w-3 h-32 bg-slate-900 rounded-l-xl group-hover/specs:bg-indigo-600 transition-all duration-500 shadow-lg"></div>
                        
                        <div class="mb-10">
                             <h4 class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mb-2 flex items-center gap-2">
                                 <Cog8ToothIcon class="w-4 h-4 text-slate-300" />
                                 Node Architecture
                             </h4>
                             <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Technical Specs</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div v-for="(val, label) in { 'Serial Topology': asset.is_serialized ? 'SERIALIZED' : 'BULK_NODE', 'Depreciation Algo': 'SLM_STANDARD', 'Tax Exemption': 'ACTIVE (SEC 32)', 'System Integration': 'NTV_MANAGED' }" :key="label" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl group-hover/specs:bg-white transition-colors duration-500">
                                <span class="block text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-2">{{ label }}</span>
                                <span class="text-base font-black text-slate-900 uppercase tracking-wide">{{ val }}</span>
                            </div>
                        </div>

                        <div class="mt-8 pt-8 border-t border-slate-100">
                             <div class="p-6 bg-slate-900 rounded-[2rem] text-white shadow-xl shadow-slate-900/20 relative overflow-hidden">
                                 <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-500/20 rounded-full blur-xl transform translate-x-1/2 -translate-y-1/2"></div>
                                 <div class="flex items-center gap-4 mb-4 relative z-10">
                                     <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center shadow-inner">
                                         <SparklesIcon class="w-4 h-4 text-white" />
                                     </div>
                                     <span class="text-sm font-black text-indigo-200 uppercase tracking-[0.3em]">AI Synthesis</span>
                                 </div>
                                 <p class="text-base font-medium leading-relaxed text-slate-300 italic relative z-10">Asset configuration is nominal. Recommended to initiate structural diagnostics every 90 standard cycles.</p>
                             </div>
                        </div>
                     </div>

                     <!-- Related Artifacts / Manuals -->
                     <button class="w-full bg-white p-8 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group/art text-left focus:outline-none">
                         <div class="absolute inset-0 bg-slate-50 opacity-0 group-hover/art:opacity-100 transition-opacity"></div>
                         <div class="relative z-10 flex flex-col items-center justify-center py-6">
                             <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 mb-6 group-hover/art:bg-indigo-600 group-hover/art:text-white transition-colors shadow-inner group-hover/art:scale-110 duration-300">
                                 <LinkIcon class="w-8 h-8 group-hover/art:-rotate-45 transition-transform duration-500" />
                             </div>
                             <h4 class="text-base font-black text-slate-900 uppercase tracking-tight mb-2">Access Virtual Manuals</h4>
                             <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">Map Connected Volumes</span>
                         </div>
                     </button>
                </div>
            </div>
        </div>

        <!-- Premium Modal Component for Action -->
        <PremiumModal 
            :show="showServiceModal" 
            @close="showServiceModal = false" 
            title="Initialize Engineering Protocol" 
            subtitle="Record Technical Intervention Event"
            icon="fa-screwdriver-wrench"
            maxWidth="xl"
            buttonColor="bg-indigo-600"
        >
            <form @submit.prevent="submitService" class="space-y-8 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-2">Diagnostic Domain</label>
                        <select v-model="serviceForm.type" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer tracking-widest shadow-sm">
                            <option>Repair_Protocol</option>
                            <option>Audit_Inspection</option>
                            <option>Hardware_Upgrade</option>
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-2">Temporal Execution</label>
                        <input v-model="serviceForm.service_date" type="date" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all uppercase tracking-widest shadow-inner">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-2">Observation Telemetry (Notes)</label>
                    <textarea v-model="serviceForm.description" rows="4" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[2rem] p-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm resize-none" placeholder="ENTER DIAGNOSTIC SUMMARY..."></textarea>
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-2">Capital Investment (Cost)</label>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-base font-black text-slate-400">INR</span>
                        <input v-model="serviceForm.cost" type="number" step="0.01" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] pl-16 pr-6 text-base font-black text-slate-900 font-mono tracking-tighter focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-inner" placeholder="0.00">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-10 mt-10 border-t border-slate-100">
                    <button @click="showServiceModal = false" type="button" class="text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:text-rose-500 transition-colors px-4 py-2">Abort Protocol</button>
                    <button type="submit" :disabled="serviceForm.processing" class="h-16 px-14 bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.3em] hover:bg-indigo-600 transition-all flex items-center gap-4 shadow-xl shadow-slate-200 active:scale-95 group/submit">
                        <div v-if="serviceForm.processing" class="w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                        <WrenchScrewdriverIcon v-else class="w-5 h-5 text-indigo-400 group-hover/submit:-rotate-12 transition-transform" />
                        <span>{{ serviceForm.processing ? 'Syncing...' : 'Commit Maintenance' }}</span>
                    </button>
                </div>
            </form>
        </PremiumModal>
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
