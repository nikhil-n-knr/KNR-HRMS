<template>
    <div class="space-y-6">
            <!-- Empty State -->
            <div v-if="!employee.health_record" class="bg-white/80 backdrop-blur rounded-xl border border-white/50 p-6 shadow-sm flex flex-col items-center justify-center min-h-[200px] border-dashed border-2 border-gray-200/60">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="text-base font-medium text-gray-900">Medical History Empty</h3>
            <p class="text-gray-500 text-xs mt-1 mb-4">No health records found for this employee.</p>
            <button 
                @click="showModal = true"
                class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-medium hover:bg-emerald-100 transition"
            >
                + Add Health Record
            </button>
            </div>

            <!-- Health Record View -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vitals Card -->
            <div class="bg-white/80 backdrop-blur rounded-xl border border-white/50 p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        Key Vitals
                        </div>
                        <button @click="showModal = true" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Edit</button>
                </h3>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase tracking-wide">Blood Group</dt>
                        <dd class="mt-1 text-xs font-medium text-gray-900">{{ employee.health_record.blood_group || '--' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase tracking-wide">Height / Weight</dt>
                        <dd class="mt-1 text-xs font-medium text-gray-900">
                            {{ employee.health_record.height_cm ? `${employee.health_record.height_cm}cm` : '--' }} / 
                            {{ employee.health_record.weight_kg ? `${employee.health_record.weight_kg}kg` : '--' }}
                        </dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs text-gray-500 uppercase tracking-wide">Allergies</dt>
                        <dd class="mt-1 text-xs text-gray-900 bg-red-50 p-2 rounded-lg border border-red-100">
                            {{ employee.health_record.allergies || 'None reported' }}
                        </dd>
                    </div>
                        <div class="col-span-2">
                        <dt class="text-xs text-gray-500 uppercase tracking-wide">Chronic Conditions</dt>
                        <dd class="mt-1 text-xs text-gray-900 bg-yellow-50 p-2 rounded-lg border border-yellow-100">
                            {{ employee.health_record.chronic_conditions || 'None reported' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Checkup & Insurance -->
            <div class="space-y-6">
                    <div class="bg-white/80 backdrop-blur rounded-xl border border-white/50 p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex justify-between">
                        Checkup Status
                        <button @click="showModal = true" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Edit</button>
                    </h3>
                    <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-100">
                        <div>
                            <p class="text-xs text-emerald-600 font-medium">Next Due</p>
                            <p class="font-bold text-gray-900">{{ formatDate(employee.health_record.next_checkup_due) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Last Checkup</p>
                            <p class="text-xs font-medium text-gray-900">{{ formatDate(employee.health_record.last_checkup_date) }}</p>
                        </div>
                    </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur rounded-xl border border-white/50 p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Insurance</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500">Provider</dt>
                            <dd class="text-xs font-medium text-gray-900">{{ employee.health_record.insurance_provider || '--' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Policy Number</dt>
                            <dd class="text-xs font-mono text-gray-700 bg-gray-50 px-2 py-1 rounded inline-block">
                                {{ employee.health_record.policy_number || 'No Policy' }}
                            </dd>
                        </div>
                    </dl>
                    </div>
            </div>
            </div>

        <HealthRecordModal 
            :show="showModal"
            :employee="employee"
            :record="employee.health_record"
            @close="showModal = false"
            @saved="$emit('refresh')"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import HealthRecordModal from '@/Components/Modals/HealthRecordModal.vue';

defineProps({
    employee: { type: Object, required: true }
});

defineEmits(['refresh']);
const showModal = ref(false);
const formatDate = (d) => d ? new Date(d).toLocaleDateString() : 'N/A';
</script>
