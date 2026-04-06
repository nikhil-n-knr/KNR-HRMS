<template>
    <Modal :show="show" title="Manage Health Record" maxWidth="4xl" @close="$emit('close')">
        <form @submit.prevent="save">
            <!-- Vitals Section -->
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 pb-1 border-b">Vitals & Config</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <BaseInput 
                    v-model="form.blood_group"
                    label="Blood Group"
                    placeholder="e.g. O+"
                    maxlength="5"
                />
                <BaseInput 
                    v-model="form.height_cm"
                    type="number"
                    step="0.01"
                    min="50"
                    max="300"
                    label="Height (cm)"
                />
                <BaseInput 
                    v-model="form.weight_kg"
                    type="number"
                    step="0.01"
                    min="20"
                    max="300"
                    label="Weight (kg)"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <BaseTextarea
                    v-model="form.allergies"
                    label="Allergies"
                    placeholder="Peanuts, Penicillin..."
                    rows="2"
                    maxlength="500"
                />
                <BaseTextarea
                    v-model="form.chronic_conditions"
                    label="Chronic Conditions"
                    placeholder="Diabetes, Hypertension..."
                    rows="2"
                    maxlength="500"
                />
            </div>

            <!-- Insurance Section -->
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 mt-6 pb-1 border-b">Insurance & Checkups</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <BaseInput 
                    v-model="form.insurance_provider"
                    label="Insurance Provider"
                    placeholder="Provider Name"
                />
                <BaseInput 
                    v-model="form.policy_number"
                    label="Policy Number"
                    placeholder="Policy No"
                />
                <BaseInput 
                    v-model="form.last_checkup_date"
                    type="date"
                    :max="new Date().toISOString().split('T')[0]"
                    label="Last Checkup"
                />
                <BaseInput 
                    v-model="form.next_checkup_due"
                    type="date"
                    :min="new Date().toISOString().split('T')[0]"
                    label="Next Due"
                />
            </div>

                <!-- Errors -->
            <div v-if="Object.keys(errors).length > 0" class="mb-4 bg-red-50 p-3 rounded-lg border border-red-100">
                <ul class="list-disc list-inside text-xs text-red-600">
                    <li v-for="(error, key) in errors" :key="key">{{ error[0] }}</li>
                </ul>
            </div>
        </form>

        <template #footer>
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Cancel
            </button>
            <button 
                @click="save" 
                :disabled="processing"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-emerald-700 transition-all disabled:opacity-70 flex items-center gap-2"
            >
                <div v-if="processing" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Save Health Record
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';

const props = defineProps({
  show: Boolean,
  employee: Object,
  record: Object
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();
const processing = ref(false);
const errors = ref({});

const form = ref({
    blood_group: '',
    height_cm: '',
    weight_kg: '',
    allergies: '',
    chronic_conditions: '',
    insurance_provider: '',
    policy_number: '',
    last_checkup_date: '',
    next_checkup_due: '',
});

watch(() => props.record, (newVal) => {
    if (newVal) {
        form.value = { ...newVal };
    } else {
        form.value = {
            blood_group: '',
            height_cm: '',
            weight_kg: '',
            allergies: '',
            chronic_conditions: '',
            insurance_provider: '',
            policy_number: '',
            last_checkup_date: '',
            next_checkup_due: '',
        };
    }
}, { immediate: true });

const save = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await axios.put(`/api/admin/employees/${props.employee.id}/health`, form.value);
        toast.success("Health record updated successfully");
        emit('saved');
        emit('close');
    } catch (error) {
         if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            toast.error("Failed to save health record");
        }
    } finally {
        processing.value = false;
    }
};
</script>
