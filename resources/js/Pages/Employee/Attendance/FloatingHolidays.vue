<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { CalendarIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const loading = ref(false);

const allocation = ref({ total_quota: 0, used_count: 0 });
const availableHolidays = ref([]);
const myRequests = ref([]);

const processing = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/employee/attendance/floating-holidays');
        allocation.value = response.data.allocation;
        availableHolidays.value = response.data.availableHolidays;
        myRequests.value = response.data.myRequests;
    } catch (e) {
        console.error(e);
        toast.error("Failed to load holiday data");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});

const apply = async (holidayId) => {
    if (confirm('Are you sure you want to apply for this Restricted Holiday?')) {
        processing.value = true;
        try {
            await axios.post('/api/employee/attendance/floating-holidays', {
                holiday_id: holidayId,
            });
            toast.success("Restricted Holiday requested successfully");
            fetchData();
        } catch (e) {
            toast.error(e.response?.data?.message || "Application failed");
        } finally {
            processing.value = false;
        }
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Approved': return 'text-green-600 bg-green-50';
        case 'Rejected': return 'text-red-600 bg-red-50';
        default: return 'text-yellow-600 bg-yellow-50';
    }
};
</script>

<template>
    <!-- <Head title="Floating Holidays" /> -->

    <div class="space-y-6">
            <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Restricted Holidays (RH)</h2>
                <p class="text-sm text-gray-500 mt-1">Select your optional holidays for this year.</p>
            </div>
                <Link href="/attendance" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                &larr; Back to Dashboard
            </Link>
        </div>

        <!-- Quota Card -->
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg text-white p-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-indigo-100 text-sm font-medium uppercase tracking-wider">Your Quota</p>
                    <p class="text-3xl font-bold mt-1">{{ allocation.total_quota - allocation.used_count }} Remaining</p>
                    <p class="text-sm opacity-80 mt-1">Total Allocated: {{ allocation.total_quota }}</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <CalendarIcon class="w-10 h-10 text-white" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Available Holidays -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-medium text-gray-800">Upcoming Options</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li v-for="holiday in availableHolidays" :key="holiday.id" class="p-6 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ holiday.name }}</p>
                            <p class="text-sm text-gray-500">{{ new Date(holiday.date).toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                        </div>
                        <PrimaryButton 
                            @click="apply(holiday.id)" 
                            :disabled="processing || (allocation.used_count >= allocation.total_quota)"
                            class="text-xs"
                        >
                            Apply
                        </PrimaryButton>
                    </li>
                        <li v-if="availableHolidays.length === 0" class="p-6 text-center text-gray-400 text-sm">
                        No upcoming restricted holidays found.
                    </li>
                </ul>
            </div>

            <!-- My Requests -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-medium text-gray-800">My Requests</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li v-for="req in myRequests" :key="req.id" class="p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ req.holiday.name }}</p>
                            <p class="text-xs text-gray-500">Requested on: {{ new Date(req.created_at).toLocaleDateString() }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full font-medium" :class="getStatusColor(req.status)">
                                {{ req.status }}
                            </span>
                        </div>
                    </li>
                        <li v-if="myRequests.length === 0" class="p-6 text-center text-gray-400 text-sm">
                        You haven't applied for any holidays yet.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
