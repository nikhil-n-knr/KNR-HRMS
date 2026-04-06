<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3'; 
import MainLayout from '@/Layouts/MainLayout.vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import { CheckCircleIcon, XCircleIcon, ArrowPathIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/Stores/toast';
import pickBy from 'lodash/pickBy';
import debounce from 'lodash/debounce';

const toast = useToastStore();

const props = defineProps({
    embedded: Boolean,
    leaves: Array,
    regularizations: Array,
    swaps: Array,
    floatingHolidays: Array,
    timesheets: Array,
    overtime: Array,
    wfh: Array,
    expenses: Array,
    all_pending: Array,
    filters: Object
});

// State
const activeTab = ref('all');
const loading = ref(false);
const showModal = ref(false);
const processing = ref(false);
const selectedItems = ref([]); // Array of IDs
const searchQuery = ref('');
const currentPage = ref(1);
const perPage = 10;

// Filter State
const params = ref({
    status: props.filters?.status || 'Pending',
    month: props.filters?.month || '',
    year: props.filters?.year || '',
    search: props.filters?.search || ''
});

const statusOptions = [
    { id: 'Pending', name: 'Status: Pending' },
    { id: 'Approved', name: 'Status: Approved' },
    { id: 'Rejected', name: 'Status: Rejected' },
];

const months = [
    { id: '', name: 'All Months' },
    { id: '1', name: 'January' }, { id: '2', name: 'February' }, { id: '3', name: 'March' },
    { id: '4', name: 'April' }, { id: '5', name: 'May' }, { id: '6', name: 'June' },
    { id: '7', name: 'July' }, { id: '8', name: 'August' }, { id: '9', name: 'September' },
    { id: '10', name: 'October' }, { id: '11', name: 'November' }, { id: '12', name: 'December' },
];

const years = computed(() => {
    const current = new Date().getFullYear();
    const list = [{ id: '', name: 'All Years' }];
    for (let i = current - 2; i <= current + 1; i++) {
        list.push({ id: String(i), name: String(i) });
    }
    return list;
});

const updateParams = debounce(() => {
    // Reset pagination on filter change (handled by response but good to reset client side too)
    currentPage.value = 1;
    selectedItems.value = [];
    
    router.get(route('manager.approvals.index'), pickBy(params.value), {
        preserveState: true,
        preserveScroll: true,
        only: ['leaves', 'regularizations', 'swaps', 'floatingHolidays', 'timesheets', 'overtime', 'wfh', 'filters'],
        onStart: () => loading.value = true,
        onFinish: () => loading.value = false
    });
}, 500);

watch(params, () => {
    updateParams();
}, { deep: true });

// Action State
const actionForm = ref({
    type: '',
    ids: [], // Array for bulk
    action: '',
    remarks: ''
});

// Columns Definitions
const columns = {
    leaves: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        type_name: { label: 'Leave Type', class: 'text-left' },
        date_range: { label: 'Dates', class: 'text-left' },
        total_days: { label: 'Days', class: 'text-center' },
        reason: { label: 'Reason', class: 'text-left w-1/4 truncate' },
        created_at: { label: 'Requested', class: 'text-right text-gray-500' }
    },
    regularizations: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        date: { label: 'Date', class: 'text-left' },
        reason: { label: 'Reason', class: 'text-left w-1/4' },
        clock_in: { label: 'Clock In', class: 'text-center' },
        clock_out: { label: 'Clock Out', class: 'text-center' },
    },
    swaps: {
        requester: { label: 'Requester', class: 'text-left font-medium' },
        recipient: { label: 'Recipient', class: 'text-left font-medium' },
        date: { label: 'Date', class: 'text-left' },
        shift_from: { label: 'From Shift', class: 'text-left' },
        shift_to: { label: 'To Shift', class: 'text-left' }
    },
    floating: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        holiday_name: { label: 'Holiday', class: 'text-left' },
        date: { label: 'Date', class: 'text-left' },
        status: { label: 'Status', class: 'text-center' }
    },
    timesheets: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        date: { label: 'Date', class: 'text-left' },
        project_name: { label: 'Project', class: 'text-left' },
        task: { label: 'Task', class: 'text-left w-1/4 truncate' },
        hours: { label: 'Hours', class: 'text-center' }
    },
    overtime: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        date: { label: 'Date', class: 'text-left' },
        reason: { label: 'Reason', class: 'text-left w-1/3 truncate' },
        hours: { label: 'Hours', class: 'text-center' }
    },
    wfh: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        date: { label: 'Date', class: 'text-left' },
        reason: { label: 'Reason', class: 'text-left w-1/3 truncate' },
        status: { label: 'Status', class: 'text-center' }
    },
    expenses: {
        employee_name: { label: 'Employee', class: 'text-left font-medium' },
        date: { label: 'Date', class: 'text-left' },
        category: { label: 'Category', class: 'text-left' },
        amount: { label: 'Amount', class: 'text-right font-mono font-bold' },
        project: { label: 'Project', class: 'text-left' },
        stage: { label: 'Workflow Stage', class: 'text-center text-xs' }
    },
    payrolls: {
        batch: { label: 'Batch / Month', class: 'text-left font-medium' },
        dates: { label: 'Period', class: 'text-left' },
        payout: { label: 'Total Payout', class: 'text-right font-mono font-bold' },
        processor: { label: 'Processed By', class: 'text-left' },
        stage: { label: 'Workflow Stage', class: 'text-center text-xs' }
    },
    all: {
        requester: { label: 'Requester', class: 'text-left font-medium' },
        workflow: { label: 'Workflow', class: 'text-left' },
        summary: { label: 'Summary', class: 'text-left w-1/3' },
        stage: { label: 'Current Stage', class: 'text-center' },
        requested_at: { label: 'Requested', class: 'text-right text-gray-400' }
    }
};

// Data normalization helper
const normalizeData = (tab, data) => {
    if (!data) return [];
    return data.map(item => {
        let normalized = { ...item };
        // Flatten common fields for table
        if (tab === 'leaves') {
            normalized.employee_name = item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : '-';
            normalized.type_name = item.leave_type?.name;
            normalized.date_range = `${new Date(item.start_date).toLocaleDateString()} - ${new Date(item.end_date).toLocaleDateString()}`;
            normalized.reason = item.reason;
            normalized.created_at = new Date(item.created_at).toLocaleDateString();
        }
        else if (tab === 'regularizations') {
            normalized.employee_name = item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : '-';
            normalized.date = new Date(item.date).toLocaleDateString();
            normalized.clock_in = item.regularized_in_time;
            normalized.clock_out = item.regularized_out_time;
        }
        else if (tab === 'swaps') {
            normalized.requester = item.requester ? item.requester.first_name : '-';
            normalized.recipient = item.recipient ? item.recipient.first_name : '-';
            normalized.date = new Date(item.date).toLocaleDateString();
            normalized.shift_from = item.shift_from?.name;
            normalized.shift_to = item.shift_to?.name;
        }
        else if (tab === 'floating') {
             normalized.employee_name = item.user?.employee ? `${item.user.employee.first_name} ${item.user.employee.last_name}` : '-';
             normalized.holiday_name = item.holiday?.name;
             normalized.date = new Date(item.holiday?.date).toLocaleDateString();
        }
        else if (tab === 'timesheets') {
             normalized.employee_name = item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : '-';
             normalized.date = new Date(item.date).toLocaleDateString();
             normalized.project_name = item.project_name;
             normalized.task = item.task_description;
             normalized.hours = item.hours_spent;
        }
        else if (tab === 'overtime') {
             normalized.employee_name = item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : '-';
             normalized.date = new Date(item.date).toLocaleDateString();
             normalized.reason = item.reason;
             normalized.hours = item.hours;
        }
        else if (tab === 'wfh') {
             normalized.employee_name = item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : '-';
             normalized.date = new Date(item.date).toLocaleDateString();
        }
        else if (tab === 'expenses') {
             normalized.employee_name = item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : '-';
             normalized.date = new Date(item.incurred_date).toLocaleDateString();
             normalized.category = item.category?.name || 'General';
             normalized.amount = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(item.amount);
             normalized.project = item.project ? item.project.name + (item.is_billable ? ' (Billable)' : '') : '-';
             normalized.stage = item.current_stage ? item.current_stage.stage_name : (item.status === 'Approved' ? 'Completed' : item.status);
        }
        else if (tab === 'payrolls') {
             normalized.batch = `${item.batch_name || 'Payroll'} - ${item.month}/${item.year}`;
             normalized.dates = `${new Date(item.start_date).toLocaleDateString()} - ${new Date(item.end_date).toLocaleDateString()}`;
             normalized.payout = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(item.total_payout);
             normalized.processor = item.processor ? item.processor.name : 'System';
             normalized.stage = item.current_stage ? item.current_stage.stage_name : item.status;
        }
        return normalized;
    });
};

const currentData = computed(() => {
    let raw = [];
    if (activeTab.value === 'all') return props.all_pending || [];
    
    if (activeTab.value === 'leaves') raw = props.leaves;
    else if (activeTab.value === 'regularizations') raw = props.regularizations;
    else if (activeTab.value === 'swaps') raw = props.swaps;
    else if (activeTab.value === 'floating') raw = props.floatingHolidays;
    else if (activeTab.value === 'timesheets') raw = props.timesheets;
    else if (activeTab.value === 'overtime') raw = props.overtime;
    else if (activeTab.value === 'wfh') raw = props.wfh;
    else if (activeTab.value === 'expenses') raw = props.expenses;
    else if (activeTab.value === 'payrolls') raw = props.payrolls;
    
    return normalizeData(activeTab.value, raw);
});

// Client-side Filtering & Pagination
const filteredData = computed(() => {
    let data = currentData.value;
    if (searchQuery.value) {
        const lower = searchQuery.value.toLowerCase();
        data = data.filter(item => 
            Object.values(item).some(val => 
                String(val).toLowerCase().includes(lower)
            )
        );
    }
    return data;
});

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredData.value.slice(start, start + perPage);
});

const meta = computed(() => ({
    current_page: currentPage.value,
    last_page: Math.ceil(filteredData.value.length / perPage) || 1,
    total: filteredData.value.length,
    per_page: perPage,
    from: (currentPage.value - 1) * perPage + 1,
    to: Math.min(currentPage.value * perPage, filteredData.value.length)
}));

// Actions
const openConfirm = (action, item = null) => {
    actionForm.value.action = action; // 'approve' or 'reject'
    actionForm.value.remarks = '';
    
    // Determine type (mapping tab name to backend 'type')
    const typeMap = {
        leaves: 'leave',
        regularizations: 'regularization',
        swaps: 'swap',
        floating: 'floating_holiday',
        timesheets: 'timesheet',
        overtime: 'overtime',
        wfh: 'wfh',
        expenses: 'expense',
        payrolls: 'payroll'
    };
    actionForm.value.type = typeMap[activeTab.value];

    if (item) {
        // Single Action
        if (activeTab.value === 'all') {
            actionForm.value.items = [{ approval_id: item.id, type: item.type, id: item.entity_id }];
        } else {
            actionForm.value.items = [{ type: typeMap[activeTab.value], id: item.id }];
        }
    } else {
        // Bulk Action
        if (selectedItems.value.length === 0) {
            toast.error('No items selected');
            return;
        }
        
        if (activeTab.value === 'all') {
            actionForm.value.items = selectedItems.value.map(id => {
                const row = paginatedData.value.find(r => r.id === id);
                return { approval_id: id, type: row?.type, id: row?.entity_id };
            });
        } else {
            actionForm.value.items = selectedItems.value.map(id => ({
                type: typeMap[activeTab.value],
                id: id
            }));
        }
    }
    showModal.value = true;
};

const submitAction = () => {
    router.post('/manager/approvals/bulk-action', actionForm.value, {
        onStart: () => processing.value = true,
        onFinish: () => processing.value = false,
        onSuccess: () => {
            toast.success(`Processed ${actionForm.value.items.length} requests successfully`);
            showModal.value = false;
            selectedItems.value = []; // Clear selection
            actionForm.value.items = [];
        },
        onError: () => toast.error("Action failed"),
        preserveScroll: true
    });
};

const fetchData = () => {
    router.reload({ only: ['leaves', 'regularizations', 'swaps', 'floatingHolidays', 'timesheets', 'overtime', 'wfh'] });
};

const changeTab = (tab) => {
    activeTab.value = tab;
    searchQuery.value = '';
    currentPage.value = 1;
    selectedItems.value = [];
};
</script>

<template>
    <div class="space-y-6" :class="{'p-0': embedded, 'max-w-[1600px] mx-auto px-4 md:px-0 font-outfit pb-12': !embedded}">
        <!-- Header -->
        <div v-if="!embedded" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Approvals Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">Review and process employee requests.</p>
            </div>
            <!-- Bulk Actions -->
            <div class="flex items-center gap-2">
                <div v-if="selectedItems.length > 0" class="flex gap-2">
                    <button 
                        @click="openConfirm('approve')"
                        class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition shadow-sm flex items-center gap-2"
                    >
                        <CheckCircleIcon class="w-4 h-4" /> Approve ({{ selectedItems.length }})
                    </button>
                    <button 
                        @click="openConfirm('reject')"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                    >
                        <XCircleIcon class="w-4 h-4 text-red-500" /> Reject
                    </button>
                </div>

                <button @click="fetchData" class="p-2 text-gray-400 hover:text-emerald-600 transition" title="Refresh">
                    <ArrowPathIcon class="w-5 h-5" :class="{'animate-spin': loading}" />
                </button>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex flex-wrap gap-4 w-full md:w-auto items-center">
                <div class="w-44">
                    <select 
                        v-model="params.status" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white"
                    >
                        <option v-for="opt in statusOptions" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                    </select>
                </div>
                <div class="h-8 w-px bg-gray-200 hidden md:block"></div>
                <div class="w-36">
                    <select 
                        v-model="params.month" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white"
                    >
                        <option value="">All Months</option>
                        <option v-for="m in months.slice(1)" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>
                <div class="w-32">
                    <select 
                        v-model="params.year" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white"
                    >
                        <option value="">All Years</option>
                        <option v-for="y in years.slice(1)" :key="y.id" :value="y.id">{{ y.name }}</option>
                    </select>
                </div>
            </div>
            
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input 
                    v-model="params.search"
                    type="text" 
                    placeholder="Search employee..." 
                    class="block w-full pl-10 pr-3 py-2 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-sm"
                >
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-200 overflow-x-auto">
                <button 
                    v-for="tab in ['all', 'leaves', 'regularizations', 'swaps', 'floating', 'timesheets', 'overtime', 'wfh', 'expenses', 'payrolls']"
                    :key="tab"
                    @click="changeTab(tab)" 
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap transition-colors capitalize"
                    :class="activeTab === tab ? 'border-b-2 border-emerald-500 text-emerald-600 bg-emerald-50/10' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                >
                    {{ tab === 'floating' ? 'Holidays' : tab }} ({{ (tab === 'all' ? props.all_pending : (tab === 'floating' ? props.floatingHolidays : props[tab]))?.length || 0 }})
                </button>
            </div>

            <!-- Content Area -->
            <div class="p-4">
                <BaseDataTable
                    :columns="columns[activeTab]"
                    :data="paginatedData"
                    :meta="meta"
                    :loading="loading"
                    selectable
                    v-model:modelValue="selectedItems"
                    @search="val => searchQuery = val"
                    @page-change="p => currentPage = p"
                >
                    <template #rowActions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openConfirm('approve', item)" class="p-1.5 bg-green-50 text-green-600 rounded-full hover:bg-green-100 transition" title="Approve">
                                <CheckCircleIcon class="w-5 h-5"/>
                            </button>
                            <button @click="openConfirm('reject', item)" class="p-1.5 bg-red-50 text-red-600 rounded-full hover:bg-red-100 transition" title="Reject">
                                <XCircleIcon class="w-5 h-5"/>
                            </button>
                        </div>
                    </template>
                </BaseDataTable>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900">Confirm Action</h3>
                <p class="text-gray-600 mt-2">
                    Are you sure you want to <span class="font-bold uppercase" :class="actionForm.action === 'approve' ? 'text-green-600' : 'text-red-600'">{{ actionForm.action }}</span> 
                    <span v-if="actionForm.items && actionForm.items.length > 1"> {{ actionForm.items.length }} requests?</span>
                    <span v-else> this request?</span>
                </p>
                
                <div class="mt-4">
                    <BaseTextarea 
                        label="Remarks (Optional)" 
                        v-model="actionForm.remarks" 
                        rows="2" 
                        placeholder="Great work! / Needs more detail..." 
                    />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                    <PrimaryButton 
                        @click="submitAction" 
                        :class="{'bg-red-600 hover:bg-red-700': actionForm.action === 'reject', 'bg-green-600 hover:bg-green-700': actionForm.action === 'approve'}"
                        :disabled="processing"
                    >
                        <span v-if="processing" class="flex items-center gap-2">
                            <ArrowPathIcon class="w-4 h-4 animate-spin" /> Processing...
                        </span>
                        <span v-else>Confirm {{ actionForm.action }}</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
