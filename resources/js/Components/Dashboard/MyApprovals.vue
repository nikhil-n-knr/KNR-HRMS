<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 h-full flex flex-col">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <ShieldCheckIcon class="w-5 h-5 text-indigo-600" />
                My Approvals
            </h3>
            <Link :href="route('manager.approvals.index')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                View All
            </Link>
        </div>

        <div class="flex-1 overflow-y-auto min-h-[300px] max-h-[400px] p-2">
            <div v-if="loading" class="flex justify-center items-center h-full text-gray-400">
                <ArrowPathIcon class="w-6 h-6 animate-spin mr-2" /> Loading...
            </div>

            <div v-else-if="allRequests.length === 0" class="flex flex-col justify-center items-center h-full text-gray-500">
                <CheckCircleIcon class="w-10 h-10 text-gray-300 mb-2" />
                <p>All caught up!</p>
            </div>

            <div v-else class="space-y-2">
                <div v-for="req in allRequests" :key="req.unique_id" 
                    class="group p-3 hover:bg-gray-50 rounded-md border border-transparent hover:border-gray-200 transition-all">
                    
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex items-center gap-2">
                            <span :class="getTypeColor(req.type)" class="text-xs px-2 py-0.5 rounded-full font-medium uppercase tracking-wide">
                                {{ req.type_label }}
                            </span>
                            <span class="text-xs text-gray-400">{{ req.date_human }}</span>
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="process(req, 'approve')" class="p-1 text-green-600 hover:bg-green-50 rounded" title="Approve">
                                <CheckIcon class="w-4 h-4" />
                            </button>
                            <button @click="process(req, 'reject')" class="p-1 text-red-600 hover:bg-red-50 rounded" title="Reject">
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                         <img :src="req.avatar" class="w-8 h-8 rounded-full bg-gray-200 object-cover" />
                         <div>
                             <p class="text-sm font-medium text-gray-900">{{ req.employee_name }}</p>
                             <p class="text-xs text-gray-500 line-clamp-1">{{ req.description }}</p>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ShieldCheckIcon, CheckCircleIcon, ArrowPathIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const loading = ref(true);
const rawData = ref({});
const allRequests = ref([]);

const form = useForm({
    type: '',
    id: null,
    action: '',
    remarks: ''
});

const loadData = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('manager.approvals.index'), {
            params: { status: 'Pending', json: true },
            headers: { 'Accept': 'application/json' } 
        });
        rawData.value = res.data;
        normalizeData(res.data);
    } catch (e) {
        console.error("Failed to load approvals", e);
    } finally {
        loading.value = false;
    }
};

const normalizeData = (data) => {
    let list = [];

    // Helper to push
    const add = (items, type, label, descFn, dateFn, empFn) => {
        if (!items) return;
        items.forEach(item => {
            list.push({
                unique_id: `${type}_${item.id}`,
                id: item.id,
                type: type,
                type_label: label,
                description: descFn(item),
                date: dateFn(item),
                date_human: dayjs(dateFn(item)).fromNow(),
                employee_name: empFn(item)?.name || empFn(item)?.first_name + ' ' + empFn(item)?.last_name || 'Unknown',
                avatar: empFn(item)?.profile_photo_url || `https://ui-avatars.com/api/?name=${empFn(item)?.first_name}+${empFn(item)?.last_name}&color=7F9CF5&background=EBF4FF`,
                raw: item
            });
        });
    };

    add(data.leaves, 'leave', 'Leave', i => `${i.leave_type?.name || 'Leave'}: ${i.total_days} days (${i.reason})`, i => i.created_at, i => i.employee?.user || i.employee);
    add(data.regularizations, 'regularization', 'Regul.', i => `Regularize ${i.date}`, i => i.created_at, i => i.employee?.user || i.employee);
    add(data.swaps, 'swap', 'Swap', i => `Swap Request`, i => i.created_at, i => i.requester?.user || i.requester);
    add(data.timesheets, 'timesheet', 'Timesheet', i => `Timesheet for ${i.date}`, i => i.date, i => i.employee?.user || i.employee);
    add(data.expenses, 'expense', 'Expense', i => `${i.category?.name || 'Exp'}: ${i.amount}`, i => i.incurred_date, i => i.employee?.user || i.employee);
    
    // Sort by date desc
    allRequests.value = list.sort((a, b) => new Date(b.date) - new Date(a.date));
};

const getTypeColor = (type) => {
    const map = {
        'leave': 'bg-blue-100 text-blue-800',
        'regularization': 'bg-orange-100 text-orange-800',
        'expense': 'bg-green-100 text-green-800',
        'swap': 'bg-purple-100 text-purple-800',
        'default': 'bg-gray-100 text-gray-800'
    };
    return map[type] || map['default'];
};

const process = (req, action) => {
    if (!confirm(`Are you sure you want to ${action} this request?`)) return;

    form.type = req.type;
    form.id = req.id;
    form.action = action;
    
    form.post(route('manager.approvals.action'), {
        preserveScroll: true,
        onSuccess: () => {
            // Remove from list locally
            allRequests.value = allRequests.value.filter(i => i.unique_id !== req.unique_id);
        }
    });
};

onMounted(() => {
    loadData();
});
</script>
