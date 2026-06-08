<template>
    <Head title="Separation Hub" />
    <MainLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex justify-between items-start">
                <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center text-white shadow-lg overflow-hidden border-2 border-white/20">
                         <span class="text-2xl font-bold">{{ employee.user.name.charAt(0) }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ employee.user.name }}</h1>
                        <p class="text-sm font-medium text-gray-500 flex items-center">
                            <span class="bg-gray-100 px-2 py-0.5 rounded mr-2">{{ employee.employee_code }}</span>
                            {{ employee.designation }} • {{ employee.department?.name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span :class="statusBadge(employee.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                        {{ employee.status }}
                    </span>
                </div>
            </div>

            <!-- Smart Tabs Container -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl overflow-hidden transition-all duration-300">
                <div class="border-b border-gray-100 px-8 py-2 bg-gray-50/50 flex space-x-8">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id 
                            ? 'border-red-500 text-red-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 uppercase tracking-widest'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </div>

                <div class="p-8">
                    <!-- Tab 1: Resignation & Dates -->
                    <div v-if="activeTab === 'resignation'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                                    <CalendarIcon class="h-5 w-5 mr-2 text-red-500" /> Separation Timeline
                                </h3>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Working Day (LWD)</label>
                                        <input type="date" v-model="form.last_working_day" class="block w-full border-0 bg-transparent text-lg font-bold text-gray-900 focus:ring-0 p-0">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notice Period Option</label>
                                        <select v-model="form.notice_option" class="block w-full border-0 bg-transparent text-lg font-bold text-gray-900 focus:ring-0 p-0">
                                            <option value="strict">Strict Enforcement</option>
                                            <option value="waived">Full Waiver</option>
                                            <option value="pay_in_lieu">Pay In Lieu (Shortfall)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-6 bg-red-50 rounded-2xl border border-red-100 space-y-4">
                                <h4 class="font-bold text-red-900">Exit Summary</h4>
                                <ul class="space-y-2 text-sm text-red-700">
                                    <li>• Status: <strong>{{ exit_record?.status || 'Initiated' }}</strong></li>
                                    <li>• Resignation Date: <strong>{{ exit_record?.resignation_date || 'N/A' }}</strong></li>
                                    <li>• Remaining Notice: <strong>{{ calculatedNoticeDays }} Days</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Clearance Matrix -->
                    <div v-if="activeTab === 'clearance'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Department</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Recovery Amount</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Final Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="item in clearance_matrix" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ item.type }}</div>
                                            <div class="text-xs text-gray-500">{{ item.remarks }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="clearanceStatusClass(item.status)" class="px-2 py-0.5 rounded text-sm font-bold uppercase">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                            ₹{{ item.due_amount }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 italic max-w-xs truncate">
                                            {{ item.cleared_notes || 'No notes' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6 flex justify-between items-center bg-gray-50 p-6 rounded-2xl">
                            <div class="flex items-center space-x-2 text-sm font-medium text-gray-700">
                                <ShieldCheckIcon v-if="allCleared" class="h-6 w-6 text-green-500" />
                                <ExclamationTriangleIcon v-else class="h-6 w-6 text-orange-500" />
                                <span>{{ allCleared ? 'Policy Validation: All Clearances Received' : 'Notice: Mandatory Clearances Pending' }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-gray-500 uppercase">Total Asset Recovery</p>
                                <p class="text-2xl font-black text-red-600">₹{{ totalRecovery }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: F&F Settlement Processing -->
                    <div v-if="activeTab === 'settlement'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Earnings Column -->
                                <div class="space-y-4">
                                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest border-l-4 border-green-500 pl-3">Credits (Earnings)</h3>
                                    
                                    <div class="space-y-4 p-6 bg-green-50/30 rounded-3xl border border-green-100">
                                        <div class="flex justify-between items-center group">
                                            <label class="text-sm font-bold text-gray-700">Leave Encashment ({{ calculatedLeaveDays }} Days)</label>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500 font-medium">₹</span>
                                                <input type="number" v-model="form.leave_encashment_val" class="w-32 bg-white border-gray-200 rounded-xl text-right font-bold focus:ring-green-500">
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <label class="text-sm font-bold text-gray-700">Gratuity Amount</label>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500 font-medium soft-blink" v-if="gratuity_eligible">✅</span>
                                                <span class="text-xs text-gray-500 font-medium">₹</span>
                                                <input type="number" v-model="form.gratuity_amount" class="w-32 bg-white border-gray-200 rounded-xl text-right font-bold focus:ring-green-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deductions Column -->
                                <div class="space-y-4">
                                     <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest border-l-4 border-red-500 pl-3">Recoveries (Deductions)</h3>
                                     
                                     <div class="space-y-4 p-6 bg-red-50/30 rounded-3xl border border-red-100">
                                        <div class="flex justify-between items-center">
                                            <label class="text-sm font-bold text-gray-700">Clearance Recovery (Auto)</label>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500 font-medium">₹</span>
                                                <input type="number" :value="totalRecovery" disabled class="w-32 bg-gray-100 border-transparent rounded-xl text-right font-bold text-red-600">
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <label class="text-sm font-bold text-gray-700">Notice Shortfall Recovery</label>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500 font-medium">₹</span>
                                                <input type="number" v-model="form.shortfall_recovery" class="w-32 bg-white border-gray-200 rounded-xl text-right font-bold focus:ring-red-500">
                                            </div>
                                        </div>
                                     </div>
                                </div>
                            </div>

                            <!-- Footer Section -->
                             <div class="pt-8 border-t border-gray-100 flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Net Payable (Tentative)</p>
                                    <p class="text-4xl font-black text-gray-900 tracking-tighter">₹{{ netPayable }}</p>
                                </div>
                                <div class="flex space-x-4">
                                    <Link :href="route('hr.payroll.index')" class="px-6 py-3 rounded-2xl font-bold text-gray-500 hover:bg-gray-100 transition-colors uppercase tracking-widest text-xs">
                                        Discard Draft
                                    </Link>
                                    <button 
                                        type="submit" 
                                        :disabled="!allCleared || form.processing"
                                        class="px-8 py-3 bg-red-600 text-white rounded-2xl font-bold shadow-xl shadow-red-200 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all uppercase tracking-widest text-xs"
                                    >
                                        {{ !allCleared ? 'Awaiting Clearance' : 'Approve & Settle' }}
                                    </button>
                                </div>
                             </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CalendarIcon, 
    ShieldCheckIcon, 
    ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: Object,
    exit_record: Object,
    clearance_matrix: Array,
    last_working_day: String,
    gratuity_eligible: Boolean,
    total_leaves: Number, // Accumulated
    notice_period_days: Number
});

const activeTab = ref('resignation');
const tabs = [
    { id: 'resignation', name: 'Timeline' },
    { id: 'clearance', name: 'Clearance Matrix' },
    { id: 'settlement', name: 'Final Settlement' }
];

const form = useForm({
    last_working_day: props.last_working_day,
    notice_option: 'pay_in_lieu',
    leave_encashment_val: 0,
    gratuity_amount: 0,
    shortfall_recovery: 0,
    custom_additions: [],
    custom_deductions: [],
    remarks: ''
});

// Computed Values
const allCleared = computed(() => {
    return props.clearance_matrix.every(item => item.status === 'Cleared');
});

const totalRecovery = computed(() => {
    return props.clearance_matrix.reduce((sum, item) => sum + parseFloat(item.due_amount || 0), 0);
});

const calculatedNoticeDays = computed(() => {
    // Diff between LWD and Resignation Date logic
    return 30; // Mock for now
});

const calculatedLeaveDays = computed(() => props.total_leaves || 0);

const netPayable = computed(() => {
    const earnings = parseFloat(form.leave_encashment_val || 0) + parseFloat(form.gratuity_amount || 0);
    const deductions = parseFloat(totalRecovery.value) + parseFloat(form.shortfall_recovery || 0);
    return Math.max(0, earnings - deductions).toLocaleString('en-IN');
});

// Helpers
const statusBadge = (status) => {
    const maps = {
        'active': 'bg-green-100 text-green-700',
        'terminated': 'bg-red-100 text-red-700',
        'resigned': 'bg-orange-100 text-orange-700'
    };
    return maps[status.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const clearanceStatusClass = (status) => {
    const maps = {
        'Cleared': 'bg-green-100 text-green-700',
        'Pending': 'bg-orange-100 text-orange-700',
        'Rejected': 'bg-red-100 text-red-700'
    };
    return maps[status] || 'bg-gray-100 text-gray-700';
};

const submit = () => {
    if (!confirm("This action is irreversible. It will finalize payouts and terminate employee login access. Proceed?")) return;
    form.post(route('hr.settlement.store', props.employee.id));
};
</script>

<style scoped>
.soft-blink {
    animation: soft-blink 2s infinite;
}
@keyframes soft-blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>
