<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Combobox from '@/Components/Combobox.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import { ArrowsRightLeftIcon, CheckIcon, XMarkIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const loading = ref(false);

defineOptions({ layout: MainLayout });

const outgoing = ref([]);
const incoming = ref([]);
const colleagues = ref([]); 
const shifts = ref([]);
const processing = ref(false);

const form = ref({
    recipient_id: '',
    date: '',
    shift_id_from: '',
    shift_id_to: ''
});

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/employee/attendance/swaps');
        outgoing.value = response.data.outgoing;
        incoming.value = response.data.incoming;
        colleagues.value = response.data.colleagues;
        shifts.value = response.data.shifts || [];
        
        // Auto-set my shift if not set or empty
        if (!form.value.shift_id_from && response.data.my_shift_id) {
            form.value.shift_id_from = response.data.my_shift_id;
        }
    } catch (e) {
        console.error(e);
        toast.error("Failed to load shift swap data");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});

watch(() => form.value.recipient_id, (newId) => {
    if (newId) {
        const colleague = colleagues.value.find(c => c.id === newId);
        if (colleague && colleague.current_shift_id) {
            form.value.shift_id_to = colleague.current_shift_id;
        }
    }
});

const submit = async () => {
    if (!form.value.recipient_id) {
        toast.error("Please select a colleague to swap with.");
        return;
    }
    processing.value = true;
    try {
        await axios.post('/api/employee/attendance/swaps', form.value);
        toast.success("Shift swap requested");
        // Reset recipients but keep my shift?
        // form.value.recipient_id = ''; form.value.date = ''; 
        // Better: full reset then restore my shift
         const myShift = form.value.shift_id_from;
         form.value = { recipient_id: '', date: '', shift_id_from: myShift, shift_id_to: '' };
        fetchData();
    } catch (e) {
        toast.error(e.response?.data?.message || "Request failed");
    } finally {
        processing.value = false;
    }
};

const respond = async (id, action) => {
    if (confirm(`Are you sure you want to ${action} this swap?`)) {
        try {
            await axios.put(`/api/employee/attendance/swaps/${id}`, { action });
            toast.success(`Swap ${action}ed`);
            fetchData();
        } catch (e) {
            toast.error(e.response?.data?.message || "Action failed");
        }
    }
};

const getColleagueDisplay = (c) => {
    return `${c.first_name} ${c.last_name} (#${c.employee_code || c.id})`;
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">Shift Swaps</h2>
                <p class="text-sm text-gray-500 mt-1">Manage shift trade requests with your team.</p>
            </div>
             <Link href="/attendance" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-1">
                &larr; Dashboard
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Request Form (Left) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <ArrowsRightLeftIcon class="w-5 h-5 text-indigo-500" />
                        Request a Swap
                    </h3>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel value="Select Colleague" class="mb-1" />
                            <Combobox 
                                v-model="form.recipient_id" 
                                :items="colleagues"
                                :displayFormat="getColleagueDisplay"
                                placeholder="Search by name or ID..."
                            />
                            <p class="text-xs text-gray-400 mt-1">Search by name or employee ID.</p>
                        </div>

                        <div>
                            <InputLabel value="Swap Date" class="mb-1" />
                            <TextInput type="date" v-model="form.date" required class="w-full" />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div>
                                <InputLabel value="My Shift" class="mb-1 text-xs uppercase text-gray-500" />
                                <BaseSelect v-model="form.shift_id_from" required class="w-full bg-white text-sm">
                                    <option value="" disabled>Select</option>
                                    <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </BaseSelect>
                            </div>
                            <div>
                                <InputLabel value="Target Shift" class="mb-1 text-xs uppercase text-gray-500" />
                                <BaseSelect v-model="form.shift_id_to" required class="w-full bg-white text-sm">
                                    <option value="" disabled>Select</option>
                                    <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </BaseSelect>
                            </div>
                        </div>

                        <div class="pt-2">
                            <PrimaryButton :disabled="processing" class="w-full justify-center py-3 text-base">
                                <span v-if="processing">Serving request...</span>
                                <span v-else>Send Request</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lists (Right) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Incoming Requests -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-indigo-50/50 flex items-center justify-between">
                        <h3 class="text-base font-bold text-indigo-900">Incoming Requests</h3>
                        <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">{{ incoming.length }} Pending</span>
                    </div>
                    
                    <div v-if="incoming.length === 0" class="p-8 text-center text-gray-400 text-sm">
                        <ClockIcon class="w-8 h-8 mx-auto mb-2 opacity-50" />
                        No incoming requests at the moment.
                    </div>

                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="swap in incoming" :key="swap.id" class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-900 text-sm">{{ swap.requester?.first_name }} {{ swap.requester?.last_name }}</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">#{{ swap.requester?.employee_id }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">
                                    Wants to swap their <span class="font-medium text-gray-800">{{ swap.shiftFrom?.name }}</span> for your <span class="font-medium text-gray-800">{{ swap.shiftTo?.name }}</span>.
                                </p>
                                <p class="text-xs text-gray-400 font-medium flex items-center gap-1">
                                    <ClockIcon class="w-3 h-3" />
                                    {{ new Date(swap.date).toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button @click="respond(swap.id, 'reject')" class="flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium transition">
                                    <XMarkIcon class="w-4 h-4" /> Reject
                                </button>
                                <button @click="respond(swap.id, 'accept')" class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-medium shadow-sm transition">
                                    <CheckIcon class="w-4 h-4" /> Accept
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Outgoing Requests -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-800">My Sent Requests</h3>
                    </div>

                    <div v-if="outgoing.length === 0" class="p-8 text-center text-gray-400 text-sm">
                        No active outgoing requests.
                    </div>

                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="swap in outgoing" :key="swap.id" class="p-5 flex items-center justify-between hover:bg-gray-50 transition">
                            <div>
                                <p class="text-sm font-medium text-gray-900 mb-1">
                                    To: {{ swap.recipient?.first_name }} {{ swap.recipient?.last_name }}
                                </p>
                                <p class="text-xs text-gray-500 mb-1">
                                    Requested Date: {{ new Date(swap.date).toLocaleDateString() }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ swap.shiftFrom?.name }}</span>
                                    <ArrowsRightLeftIcon class="w-3 h-3" />
                                    <span>{{ swap.shiftTo?.name }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="px-3 py-1 text-xs font-bold rounded-full border" 
                                    :class="{
                                        'bg-yellow-50 text-yellow-700 border-yellow-200': swap.status === 'Pending',
                                        'bg-green-50 text-green-700 border-green-200': swap.status === 'Approved',
                                        'bg-red-50 text-red-700 border-red-200': swap.status === 'Rejected'
                                    }">
                                    {{ swap.status }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
