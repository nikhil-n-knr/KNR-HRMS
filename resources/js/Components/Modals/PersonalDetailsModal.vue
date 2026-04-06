<template>
    <Modal :show="show" title="Edit Personal Details" maxWidth="4xl" @close="$emit('close')">
        <form @submit.prevent="save">
            <!-- Bio Section -->
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 pb-1 border-b">Bio & Identity</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                 <BaseInput 
                    v-model="form.dob"
                    type="date"
                    :max="new Date().toISOString().split('T')[0]"
                    label="Date of Birth"
                 />
                 <BaseSelect
                    v-model="form.gender"
                    label="Gender"
                 >
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                    <option value="prefer_not_to_say">Prefer not to say</option>
                 </BaseSelect>
                 <BaseSelect
                    v-model="form.marital_status"
                    label="Marital Status"
                 >
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="divorced">Divorced</option>
                    <option value="widowed">Widowed</option>
                 </BaseSelect>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                 <BaseInput 
                    v-model="form.nationality"
                    label="Nationality"
                    placeholder="e.g. Indian"
                 />
                 <BaseInput 
                    v-model="form.passport_number"
                    label="Passport Number"
                    maxlength="20"
                    placeholder="e.g. A1234567"
                 />
            </div>

            <!-- Current Address -->
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 mt-6 pb-1 border-b">Current Address</h4>
            <div class="space-y-4">
                <BaseInput 
                    v-model="form.current_address"
                    label="Address Line"
                    placeholder="House No, Street, Area"
                />
                <div class="grid grid-cols-2 gap-4">
                     <BaseInput v-model="form.current_city" label="City" placeholder="City Name" />
                     <BaseInput v-model="form.current_state" label="State" placeholder="State Name" />
                     <BaseInput 
                        v-model="form.current_zip" 
                        label="Zip/Postal" 
                        placeholder="e.g. 110001" 
                        maxlength="10"
                        @input="e => form.current_zip = e.target.value.replace(/[^0-9a-zA-Z-]/g, '')"
                     />
                     <BaseInput v-model="form.current_country" label="Country" placeholder="Country Name" />
                </div>
            </div>

             <!-- Permanent Address -->
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 mt-6 pb-1 border-b flex items-center justify-between">
                Permanent Address
                <label class="flex items-center gap-2 cursor-pointer normal-case text-gray-600 text-xs font-normal">
                    <input type="checkbox" v-model="form.is_permanent_same" class="rounded text-emerald-600 focus:ring-emerald-500">
                    Same as Current
                </label>
            </h4>
            
            <div v-if="!form.is_permanent_same" class="space-y-4 transition-all">
                <BaseInput 
                    v-model="form.permanent_address"
                    label="Address Line"
                    placeholder="House No, Street, Area"
                />
                <div class="grid grid-cols-2 gap-4">
                     <BaseInput v-model="form.permanent_city" label="City" />
                     <BaseInput v-model="form.permanent_state" label="State" />
                     <BaseInput 
                        v-model="form.permanent_zip" 
                        label="Zip/Postal"
                        maxlength="10"
                        @input="e => form.permanent_zip = e.target.value.replace(/[^0-9a-zA-Z-]/g, '')" 
                     />
                     <BaseInput v-model="form.permanent_country" label="Country" />
                </div>
            </div>

             <!-- Errors -->
            <div v-if="Object.keys(errors).length > 0" class="mt-4 bg-red-50 p-3 rounded-lg border border-red-100">
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
                Save Details
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
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps({
  show: Boolean,
  employee: Object,
  detail: Object
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();
const processing = ref(false);
const errors = ref({});

const form = ref({});

const initForm = () => ({
    dob: '',
    gender: '',
    marital_status: '',
    nationality: '',
    passport_number: '',
    current_address: '',
    current_city: '',
    current_state: '',
    current_zip: '',
    current_country: 'India',
    is_permanent_same: false,
    permanent_address: '',
    permanent_city: '',
    permanent_state: '',
    permanent_zip: '',
    permanent_country: 'India',
});

watch(() => props.show, (val) => {
    if (val) {
        if (props.detail) {
            // Ensure boolean conversion for checkbox
            form.value = { 
                ...props.detail, 
                is_permanent_same: Boolean(props.detail.is_permanent_same) 
            };
        } else {
            form.value = initForm();
        }
        errors.value = {};
    }
});

const save = async () => {
    processing.value = true;
    errors.value = {};
    
    const payload = { ...form.value };
    if (payload.is_permanent_same) {
        payload.permanent_address = payload.current_address;
        payload.permanent_city = payload.current_city;
        payload.permanent_state = payload.current_state;
        payload.permanent_zip = payload.current_zip;
        payload.permanent_country = payload.current_country;
    }

    try {
        await axios.put(`/api/admin/employees/${props.employee.id}/personal`, payload);
        toast.success("Personal details updated");
        emit('saved');
        emit('close');
    } catch (error) {
         if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            toast.error("Failed to save details");
        }
    } finally {
        processing.value = false;
    }
};
</script>
