<template>
    <Head :title="payroll.batch_name" />
    <MainLayout>
        <div class="h-[calc(100vh-4rem)] flex flex-col bg-gray-50">
            <!-- Header -->
            <div class="px-8 py-6 bg-white border-b border-gray-200 flex justify-between items-center shadow-sm">
                <div class="flex items-center gap-4">
                    <Link :href="route('hr.payroll.index')" class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors" title="Back to Runs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ payroll.batch_name }}</h1>
                        <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                            <span>{{ formatDate(payroll.start_date) }} - {{ formatDate(payroll.end_date) }}</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                    :class="statusClass(payroll.status)">
                                {{ payroll.status }}
                            </span>
                            <span v-if="payroll.status === 'Pending Approval' && payroll.approver" class="text-xs text-gray-400">
                                Assigned to: {{ payroll.approver.name }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    
                    <!-- ACTION: Submit (Draft / Rejected) -->
                    <button v-if="['Draft', 'Rejected'].includes(payroll.status)" @click="showSubmitModal = true" 
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 shadow-lg transition"
                    >
                         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Submit for Verification
                    </button>

                    <!-- ACTION: Approve/Reject (Pending & Authorized) -->
                    <div v-if="payroll.status === 'Pending Approval'" class="flex space-x-2">
                        <template v-if="canApprove">
                            <button @click="approvePayroll" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 shadow-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Approve
                            </button>
                            <button @click="showRejectModal = true" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 shadow-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Reject
                            </button>
                        </template>
                        <span v-else class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-md border border-gray-200">
                            Waiting for Approval
                        </span>
                    </div>

                    <!-- ACTION: Publish (Approved) -->
                    <button v-if="payroll.status === 'Approved'" @click="publishPayroll" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-lg transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                        Release (Publish)
                    </button>
                    
                    <!-- Helper: Regenerate (Only Draft/Rejected) -->
                     <button v-if="['Draft', 'Rejected', 'Approved'].includes(payroll.status)" @click="regeneratePayroll" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition"
                    >
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Regenerate
                    </button>

                     <a :href="route('hr.payroll.export-bank', payroll.id)" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Bank File
                    </a>

                    <a :href="route('hr.payroll.export-breakdown', payroll.id)" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 00-2-2H5a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2zm0 0h2a2 2 0 002-2M9 7V5a2 2 0 00-2-2H5a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2zm0 0h2a2 2 0 002-2M9 7l2 2m0 0l2-2m-2 2v2m0 8h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2z"></path></svg>
                        Breakdown
                    </a>

                    <a :href="route('hr.payroll.export-pre-approval', payroll.id)" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Pre-Approval
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8 relative">
                 <!-- Watermark if Paid/Published -->
                 <div v-if="['Paid', 'Published'].includes(payroll.status)" class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-5">
                    <span class="text-9xl font-black text-gray-900 uppercase transform -rotate-12">{{ payroll.status }}</span>
                 </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 relative z-10">
                     <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                         <span class="block text-gray-500 text-xs uppercase tracking-wider font-semibold">Total Payout</span>
                         <span class="block text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(payroll.total_payout) }}</span>
                     </div>
                     <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                         <span class="block text-gray-500 text-xs uppercase tracking-wider font-semibold">Employees</span>
                         <span class="block text-2xl font-bold text-gray-900 mt-1">{{ payslips.length }}</span>
                     </div>
                     
                     <!-- Rejection Reason Box -->
                     <div v-if="payroll.status === 'Rejected' && payroll.rejection_reason" class="col-span-2 bg-red-50 p-5 rounded-lg border border-red-200">
                         <span class="block text-red-600 text-xs uppercase tracking-wider font-bold">Rejection Reason</span>
                         <p class="text-sm text-red-800 mt-1 font-medium italic">"{{ payroll.rejection_reason }}"</p>
                     </div>
                </div>

                <!-- Toolbar -->
                <div class="flex justify-between items-center mb-4 relative z-10">
                    <div class="relative w-72">
                        <input type="text" v-model="searchQuery" placeholder="Search employees..." 
                            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        Showing {{ filteredPayslips.length }} of {{ payslips.length }}
                    </div>
                </div>

                <!-- Payslip Table (Sticky Header & Column) -->
                <div class="bg-white rounded-lg shadow border border-gray-200 relative z-10">
                     <div class="overflow-x-auto h-[600px]"> <!-- Fixed height for large lists -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <!-- Sticky 1st Col -->
                                    <th scope="col" class="sticky left-0 z-10 bg-gray-50 px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider shadow-sm">
                                        Employee
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Variable/Bonus</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Deductions</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Net Pay</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Attendance</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="slip in filteredPayslips" :key="slip.id" class="hover:bg-gray-50 transition-colors" :class="{'bg-gray-50 opacity-50': slip.is_held}">
                                    <!-- Sticky Cell -->
                                    <td class="sticky left-0 z-10 bg-white px-6 py-4 whitespace-nowrap shadow-sm">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                                {{ slip.employee?.user?.name.charAt(0) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ slip.employee?.user?.name }}</div>
                                                <div class="text-xs text-gray-500">{{ slip.employee?.employee_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        {{ formatCurrency(slip.gross_earnings) }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                         <span :class="{'text-gray-400': !getBonus(slip), 'text-green-600 font-semibold': getBonus(slip) > 0}">
                                             {{ getBonus(slip) ? formatCurrency(getBonus(slip)) : '-' }}
                                         </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 text-right">
                                        <div class="group relative cursor-help">
                                            - {{ formatCurrency(slip.gross_deductions) }}
                                            <!-- Tooltip -->
                                            <div class="absolute hidden group-hover:block right-0 bottom-full mb-2 w-48 bg-gray-800 text-white text-xs rounded p-2 z-20 shadow-lg">
                                                <div v-for="(amt, label) in slip.deductions_breakdown" :key="label" class="flex justify-between">
                                                    <span>{{ label }}:</span>
                                                    <span>{{ formatCurrency(amt) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700 text-right">
                                        {{ formatCurrency(slip.net_pay) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                         <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-md">{{ slip.payable_days }} Days</span>
                                         <span v-if="slip.lop_days > 0" class="ml-1 bg-red-100 text-red-600 px-2 py-1 rounded-md">{{ slip.lop_days }} LOP</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                        <span v-if="slip.is_held" class="bg-red-100 text-red-800 px-2 py-1 rounded-full font-semibold">HELD</span>
                                        <span v-else class="text-green-600 font-semibold">Active</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <!-- Edit / Actions Dropdown could be better, but buttons for now -->
                                        <template v-if="['Draft', 'Rejected'].includes(payroll.status)">
                                            <button @click="openAdjustModal(slip)" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Edit</button>
                                            <button @click="toggleHold(slip)" class="text-amber-600 hover:text-amber-900 font-medium text-xs">
                                                {{ slip.is_held ? 'Release' : 'Hold' }}
                                            </button>
                                            <button @click="removePayslip(slip)" class="text-red-500 hover:text-red-700 font-medium text-xs">Remove</button>
                                        </template>

                                        <template v-if="['Published', 'Paid'].includes(payroll.status)">
                                            <a :href="route('hr.payslip.download', slip.id)" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">PDF</a>
                                            <button @click="emailPayslip(slip)" class="text-gray-500 hover:text-gray-900 font-medium text-xs">Email</button>
                                        </template>
                                    </td>
                                </tr>
                                <tr v-if="filteredPayslips.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        No employees found matching "{{ searchQuery }}"
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit for Approval Modal -->
        <Modal :show="showSubmitModal" @close="showSubmitModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Submit Payroll for Verification?</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Approver</label>
                        <select v-model="submitForm.approver_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" disabled>Select User...</option>
                            <option v-for="user in approvers" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                         <p v-if="submitForm.errors.approver_id" class="text-red-500 text-xs mt-1">{{ submitForm.errors.approver_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea v-model="submitForm.notes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Added bonuses for Sales Team, please check."></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="showSubmitModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button @click="submit" :disabled="submitForm.processing" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                        {{ submitForm.processing ? 'Sending...' : 'Send for Approval' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
             <div class="p-6">
                <h2 class="text-lg font-medium text-red-900 mb-4">Reject Payroll?</h2>
                <p class="text-sm text-gray-500 mb-4">Please provide a reason for rejection. This will be sent to the creator.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rejection Reason</label>
                    <textarea v-model="rejectForm.reason" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500" placeholder="e.g. Incorrect bonus calculations..."></textarea>
                     <p v-if="rejectForm.errors.reason" class="text-red-500 text-xs mt-1">{{ rejectForm.errors.reason }}</p>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="showRejectModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button @click="rejectPayroll" :disabled="rejectForm.processing" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50">
                        {{ rejectForm.processing ? 'Rejecting...' : 'Confirm Rejection' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Force Adjust Modal -->
        <Modal :show="showAdjustModal" @close="showAdjustModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-2">Adjust Payslip</h2>
                <p class="text-sm text-gray-500 mb-4">Manual override for {{ adjustForm.employee_name }}. CAUTION: This affects net pay directly.</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Gross Earnings</label>
                        <input type="number" v-model="adjustForm.gross_earnings" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                         <label class="block text-xs font-semibold text-gray-700 uppercase">Total Deductions</label>
                         <input type="number" v-model="adjustForm.gross_deductions" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Simple JSON Editors for Breakdown (Textarea for now to avoid complexity, or detailed list) -->
                <!-- For MVP/Speed, we'll just allow overriding the totals mostly, but we need to update the breakdown JSON to match totals or it looks weird.
                     Let's list top 3-4 components and 'Other'. 
                -->
                <div class="mt-4">
                     <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Earnings Breakdown</label>
                     <div v-for="(amt, key) in adjustForm.earnings_breakdown" :key="key" class="flex justify-between items-center mb-1 text-sm">
                         <span class="text-gray-600">{{ key }}</span>
                         <input type="number" v-model="adjustForm.earnings_breakdown[key]" class="w-32 text-right border-gray-300 rounded-md py-1 text-sm">
                     </div>
                </div>

                 <div class="mt-4">
                     <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Deductions Breakdown</label>
                     <div v-for="(amt, key) in adjustForm.deductions_breakdown" :key="key" class="flex justify-between items-center mb-1 text-sm">
                         <span class="text-gray-600">{{ key }}</span>
                         <input type="number" v-model="adjustForm.deductions_breakdown[key]" class="w-32 text-right border-gray-300 rounded-md py-1 text-sm">
                     </div>
                </div>

                <div class="mt-6 bg-gray-50 p-3 rounded flex justify-between items-center">
                    <span class="font-bold text-gray-700">Calculated Net Pay:</span>
                    <span class="font-bold text-xl text-indigo-600">{{ formatCurrency(calculatedNetPay) }}</span>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Remarks / Reason</label>
                    <textarea v-model="adjustForm.remarks" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="showAdjustModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button @click="saveAdjustment" :disabled="adjustForm.processing" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                        {{ adjustForm.processing ? 'Saving...' : 'Save & Recalculate' }}
                    </button>
                </div>
            </div>
        </Modal>

    </MainLayout>
</template>

<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';
import { ref, nextTick, computed, watch } from 'vue';

const props = defineProps({
    payroll: Object,
    payslips: Array,
    approvers: Array
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const showSubmitModal = ref(false);
const showRejectModal = ref(false);
const showAdjustModal = ref(false);
const searchQuery = ref('');

const submitForm = useForm({
    approver_id: '',
    notes: ''
});

const rejectForm = useForm({
    reason: ''
});

const adjustForm = useForm({
    id: null,
    employee_name: '',
    gross_earnings: 0,
    earnings_breakdown: {},
    gross_deductions: 0,
    deductions_breakdown: {},
    net_pay: 0,
    remarks: ''
});

// Computed properties for UI
const filteredPayslips = computed(() => {
    if (!searchQuery.value) return props.payslips;
    const q = searchQuery.value.toLowerCase();
    return props.payslips.filter(p => 
        p.employee?.user?.name.toLowerCase().includes(q) || 
        p.employee?.employee_id.toLowerCase().includes(q)
    );
});

const calculatedNetPay = computed(() => {
    // Auto-sum based on breakdown edits, or manual inputs?
    // Let's rely on the breakdown sums to keep it consistent
    let earnings = Object.values(adjustForm.earnings_breakdown).reduce((a, b) => Number(a) + Number(b), 0);
    let deductions = Object.values(adjustForm.deductions_breakdown).reduce((a, b) => Number(a) + Number(b), 0);
    
    // Update the master fields for submission
    adjustForm.gross_earnings = earnings;
    adjustForm.gross_deductions = deductions;
    adjustForm.net_pay = earnings - deductions;

    return adjustForm.net_pay;
});

// --- Actions ---

const openAdjustModal = (slip) => {
    adjustForm.id = slip.id;
    adjustForm.employee_name = slip.employee?.user?.name;
    adjustForm.gross_earnings = slip.gross_earnings;
    adjustForm.earnings_breakdown = { ...slip.earnings_breakdown }; // Clone
    adjustForm.gross_deductions = slip.gross_deductions;
    adjustForm.deductions_breakdown = { ...slip.deductions_breakdown }; // Clone
    adjustForm.net_pay = slip.net_pay;
    adjustForm.remarks = '';
    showAdjustModal.value = true;
};

const saveAdjustment = () => {
    adjustForm.post(route('hr.payslip.update', adjustForm.id), {
        onSuccess: () => showAdjustModal.value = false
    });
};

const toggleHold = (slip) => {
    if (confirm(`Are you sure you want to ${slip.is_held ? 'RELEASE' : 'HOLD'} this payslip from the bank file?`)) {
        router.post(route('hr.payslip.toggle-hold', slip.id));
    }
};

const removePayslip = (slip) => {
    if (confirm(`Permanently remove ${slip.employee?.user?.name} from this payroll run? This cannot be undone easily (requires regenerate).`)) {
         router.delete(route('hr.payslip.destroy', slip.id));
    }
};

const canApprove = computed(() => {
    const authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'];
    if (currentUser.value?.roles?.some(r => authorizedRoles.includes(r.name))) return true;
    return props.payroll.approver_id === currentUser.value?.id;
});

const submit = () => {
    submitForm.post(route('hr.payroll.submit', props.payroll.id), {
        onSuccess: () => showSubmitModal.value = false
    });
};

const approvePayroll = () => {
    if(confirm('Approve this payroll?')) {
        router.post(route('hr.payroll.approve', props.payroll.id));
    }
};

const rejectPayroll = () => {
    rejectForm.post(route('hr.payroll.reject', props.payroll.id), {
        onSuccess: () => showRejectModal.value = false
    });
};

const publishPayroll = () => {
    if(confirm('Release/Publish this payroll? Employees will be notified.')) {
        router.post(route('hr.payroll.publish', props.payroll.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');

const statusClass = (status) => {
    if (status === 'Draft') return 'bg-yellow-100 text-yellow-800';
    if (status === 'Pending Approval') return 'bg-blue-100 text-blue-800';
    if (status === 'Approved') return 'bg-purple-100 text-purple-800';
    if (status === 'Published') return 'bg-green-100 text-green-800';
    if (status === 'Rejected') return 'bg-red-100 text-red-800';
    return 'bg-gray-100 text-gray-800';
};

const getBonus = (slip) => {
    return slip.earnings_breakdown?.['Bonus'] || 0;
};

const emailPayslip = (slip) => {
    if (confirm(`Send payslip via email to ${slip.employee?.user?.name}?`)) {
        router.post(route('hr.payslip.email', slip.id));
    }
};

const regeneratePayroll = () => {
    if (confirm('Are you sure you want to REGENERATE this payroll? This will reset edits.')) {
        router.post(route('hr.payroll.regenerate', props.payroll.id));
    }
};
</script>
