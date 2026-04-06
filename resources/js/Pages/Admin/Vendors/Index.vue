<template>
    <MainLayout>
        <Head title="Vendor Registry" />
        
        <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Strategic Header Terminal -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-slate-50 rounded-full blur-2xl group-hover:bg-emerald-50 transition-colors"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:rotate-6 transition-transform">
                        <TruckIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                            Vendor Procurement Hub
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">Core Logistics</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 flex items-center gap-2">
                            <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
                            Active Supply Chain & Strategic Partner Management
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 relative z-10 w-full lg:w-auto">
                    <button @click="showModal = true" class="flex-1 lg:flex-none h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-slate-300 hover:bg-emerald-600 transition-all active:scale-95 flex items-center justify-center gap-4 group">
                        <PlusIcon class="w-5 h-5 group-hover:rotate-90 transition-transform" />
                        Initialize Partner
                    </button>
                </div>
            </div>

            <!-- Neural Metrics Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in [
                    { label: 'Total Partners', value: vendors.data.length, icon: BuildingOfficeIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                    { label: 'Active Nodes', value: vendors.data.filter(v => v.is_active).length, icon: SignalIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                    { label: 'Critical SLAs', value: vendors.data.filter(v => v.sla_response_hours < 5).length, icon: BoltIcon, color: 'text-amber-600', bg: 'bg-amber-50' },
                    { label: 'Expiring Contracts', value: '02', icon: ClockIcon, color: 'text-rose-600', bg: 'bg-rose-50' }
                ]" :key="stat.label" class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all">
                    <div class="flex items-center gap-4">
                        <div :class="[stat.bg, stat.color]" class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm group-hover:rotate-12 transition-transform">
                            <component :is="stat.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">{{ stat.label }}</span>
                            <span class="text-xl font-black text-slate-900 tabular-nums">{{ stat.value }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registry Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                
                <div class="overflow-x-auto relative z-10">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Strategic Entity</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Communications Terminal</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Response SLA</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Contract Omega</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em] text-right">Operational Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="vendor in vendors.data" :key="vendor.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover/row:bg-slate-900 group-hover/row:text-emerald-400 transition-all shadow-inner border border-slate-100">
                                            <BuildingStorefrontIcon class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover/row:text-emerald-700 transition-colors">{{ vendor.name }}</div>
                                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-60 italic">Partner Node #{{ String(vendor.id).padStart(3, '0') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="text-base font-black text-slate-700 uppercase tracking-widest leading-none">{{ vendor.contact_person || 'NULL_ENTITY' }}</div>
                                        <div class="text-sm font-black text-slate-400 lowercase tracking-tight leading-none opacity-80">{{ vendor.email }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 group-hover/row:bg-indigo-600 group-hover/row:text-white transition-all">
                                        <ClockIcon class="w-3.5 h-3.5" />
                                        <span class="text-sm font-black uppercase tracking-widest">{{ vendor.sla_response_hours }} HRS_SYNC</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-base font-black text-slate-600 uppercase tracking-tighter tabular-nums">
                                        {{ vendor.contract_end_date ? new Date(vendor.contract_end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'PERPETUAL_LINK' }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end">
                                        <div v-if="vendor.is_active" class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 text-xs font-black uppercase tracking-[0.2em] shadow-sm flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                            Active_Node
                                        </div>
                                        <div v-else class="px-4 py-1.5 bg-slate-50 text-slate-400 rounded-full border border-slate-200 text-xs font-black uppercase tracking-[0.2em] flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                            Segment_Locked
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="vendors.data.length === 0">
                                <td colspan="5" class="px-8 py-32 text-center grayscale opacity-30">
                                    <BuildingOfficeIcon class="w-20 h-20 mx-auto text-slate-300 mb-6 animate-pulse" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Zero external partners mapped</p>
                                    <button @click="showModal = true" class="mt-6 text-emerald-600 text-sm font-black uppercase tracking-widest hover:underline flex items-center justify-center gap-2 mx-auto">
                                        <PlusIcon class="w-4 h-4" />
                                        Initialize First Node
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Partner Initialization Modal -->
            <PremiumModal 
                :show="showModal" 
                @close="showModal = false" 
                title="Partner Initialization" 
                subtitle="Establish Strategic Procurement Link"
                icon="fa-truck-fast"
                maxWidth="xl"
            >
                <form @submit.prevent="submit" class="space-y-8 pt-4">
                    <div class="space-y-3">
                        <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Entity Signature (Vendor Name)</label>
                        <div class="relative group/input">
                            <BuildingStorefrontIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within/input:text-emerald-500 transition-colors" />
                            <input v-model="form.name" type="text" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] pl-16 pr-6 text-[14px] font-black text-slate-900 focus:bg-white focus:ring-8 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all placeholder:text-slate-200 uppercase tracking-widest" required placeholder="VENDOR_CODE_NAME...">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Operational Liaison</label>
                            <input v-model="form.contact_person" type="text" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest" placeholder="CORE_CONTACT...">
                        </div>
                        <div class="space-y-3">
                            <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Digital Terminal (Email)</label>
                            <input v-model="form.email" type="email" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all lowercase tracking-tight" placeholder=" liaison@external.node ">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Response SLA Threshold</label>
                            <div class="relative group/input">
                                <ClockIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within/input:text-indigo-500" />
                                <input v-model="form.sla_response_hours" type="number" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-16 pr-6 text-lg font-black text-slate-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-mono" placeholder="24">
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="px-2 text-sm font-black text-slate-500 uppercase tracking-[0.2em] leading-none">Contract Omega Date</label>
                            <input v-model="form.contract_end_date" type="date" class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        </div>
                    </div>

                    <div class="flex justify-end gap-6 pt-10 border-t border-slate-50 mt-8">
                        <button type="button" @click="showModal = false" class="text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:text-slate-600 transition-colors px-4">Abort_Protocol</button>
                        <button type="submit" :disabled="form.processing" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-xl hover:bg-emerald-600 transition-all flex items-center gap-4 active:scale-95">
                            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
                            <ShieldCheckIcon v-else class="w-5 h-5 text-emerald-400" />
                            Finalize Linkage
                        </button>
                    </div>
                </form>
            </PremiumModal>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    TruckIcon, 
    PlusIcon, 
    ShieldCheckIcon, 
    BuildingOfficeIcon, 
    SignalIcon, 
    BoltIcon, 
    ClockIcon,
    BuildingStorefrontIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

defineProps({
    vendors: Object
});

const showModal = ref(false);
const form = useForm({
    name: '',
    contact_person: '',
    email: '',
    sla_response_hours: 24,
    contract_end_date: ''
});

const submit = () => {
    form.post(route('admin.vendors.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
