<script setup>
import { ref, onMounted } from 'vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import { GiftIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const credits = ref([]);
const loading = ref(false);
const scanning = ref(false);
const scanForm = ref({
    start_date: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0]
});
const toast = useToastStore();

const fetchCredits = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('admin.attendance.compoffs.index'));
        credits.value = res.data.credits;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const runScan = async () => {
    scanning.value = true;
    try {
        const res = await axios.post(route('admin.attendance.compoffs.scan'), scanForm.value);
        toast.success(res.data.message);
        fetchCredits();
    } catch (e) {
        toast.error('Scan failed');
    } finally {
        scanning.value = false;
    }
};

onMounted(() => fetchCredits());
</script>

<template>
    <AttendanceLayout title="Comp-Off Terminal" activeTab="compoffs">
        <div class="space-y-6 pb-12">
            <!-- Compact Discovery Section -->
            <div class="h-14 bg-white/90 backdrop-blur-md p-2 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-search-dollar text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Discovery Terminal</h2>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none px-0.5">Audit logs for weekend deployment credit</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 pr-1">
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl h-10 px-3 shadow-inner">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">SCOPE</span>
                        <input type="date" v-model="scanForm.start_date" class="bg-transparent border-transparent py-0 px-2 text-sm font-black uppercase tracking-widest focus:ring-0 text-slate-700 h-full cursor-pointer">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest mx-1">TO</span>
                        <input type="date" v-model="scanForm.end_date" class="bg-transparent border-transparent py-0 px-2 text-sm font-black uppercase tracking-widest focus:ring-0 text-slate-700 h-full cursor-pointer">
                    </div>
                    <button @click="runScan" :disabled="scanning" class="h-10 px-8 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all active:scale-95 flex items-center gap-3 shadow-lg disabled:opacity-50 group">
                        <ArrowPathIcon class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" :class="{'animate-spin': scanning}" />
                        <span>{{ scanning ? 'AUDITING...' : 'Initiate Scan' }}</span>
                    </button>
                </div>
            </div>
            
            <!-- Credits Matrix -->
             <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:border-emerald-500/30 transition-all duration-300">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-3 leading-none">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Dispatch Protocol Registry
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Active Operative</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Date</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Expiry Protocol</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Dispatch Status</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operational Intelligence</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="credit in credits" :key="credit.id" class="group hover:bg-emerald-50/20 transition-all duration-300 border-b border-slate-50 last:border-0 border-l-4 border-l-transparent hover:border-l-emerald-500">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-base border border-white shadow-md flex-shrink-0 uppercase group-hover:bg-emerald-600 transition-all">
                                            {{ credit.employee?.first_name[0] }}{{ credit.employee?.last_name[0] }}
                                        </div>
                                        <div>
                                            <span class="block text-base font-black text-slate-800 uppercase tracking-tight leading-none group-hover:text-emerald-700 transition-colors">{{ credit.employee?.first_name }} {{ credit.employee?.last_name }}</span>
                                            <span class="block text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2.5 leading-none">{{ credit.employee?.employee_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-slate-600 uppercase tracking-tighter leading-none tabular-nums whitespace-nowrap">{{ new Date(credit.date_earned).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-slate-600 uppercase tracking-tighter leading-none tabular-nums whitespace-nowrap">{{ new Date(credit.expiry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1.5 text-xs font-black uppercase tracking-widest rounded-xl border shadow-sm leading-none inline-flex items-center gap-2" 
                                        :class="{
                                            'bg-emerald-50 text-emerald-600 border-emerald-100': credit.status === 'Available',
                                            'bg-slate-50 text-slate-500 border-slate-200': credit.status === 'Used',
                                            'bg-rose-50 text-rose-600 border-rose-100': credit.status === 'Expired'
                                        }">
                                        <span class="w-1 h-1 rounded-full bg-current"></span>
                                        {{ credit.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-slate-500 truncate max-w-[200px] block leading-normal uppercase tracking-tight">{{ credit.notes || '---' }}</span>
                                </td>
                            </tr>
                            <tr v-if="credits.length === 0">
                                <td colspan="5" class="px-5 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 grayscale opacity-30">
                                        <i class="fas fa-database text-2xl"></i>
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest">No Intelligence Records Found</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AttendanceLayout>

</template>
