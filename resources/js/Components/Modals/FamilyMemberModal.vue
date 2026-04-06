<template>
  <Modal :show="show" :title="isEditing ? 'Edit Family Member' : 'Add Family Member'" @close="$emit('close')">
    
      <div class="space-y-4">
        <BaseInput v-model="form.name" label="Full Name" required :error="errors.name" />
        
        <BaseSelect 
            v-model="form.relationship" 
            label="Relationship" 
            required 
            :error="errors.relationship"
        >
            <option value="" disabled>Select Relationship</option>
            <option v-for="opt in relationshipOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
            </option>
        </BaseSelect>

        <div class="grid grid-cols-2 gap-4">
             <BaseInput v-model="form.dob" type="date" label="Date of Birth" :max="today" :error="errors.dob" />
             <BaseInput v-model="form.phone" label="Phone Number" restrict="numbers" :error="errors.phone" />
        </div>

        <BaseInput v-model="form.occupation" label="Occupation" :error="errors.occupation" />
        
        <div class="flex flex-col gap-3 pt-2">
            <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition">
                <input type="checkbox" v-model="form.is_dependent" class="rounded text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                <div>
                    <span class="block text-sm font-medium text-gray-900">Dependent</span>
                    <span class="block text-xs text-gray-500">Covered under company health insurance</span>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition">
                <input type="checkbox" v-model="form.is_emergency_contact" class="rounded text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                <div>
                    <span class="block text-sm font-medium text-gray-900">Emergency Contact</span>
                    <span class="block text-xs text-gray-500">Contact in case of emergency</span>
                </div>
            </label>
        </div>
      </div>

    <template #footer>
        <div class="flex justify-end gap-3">
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">Cancel</button>
            <button 
                @click="submit" 
                :disabled="submitting"
                class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:scale-[1.02] transition-all disabled:opacity-50"
            >
                <span v-if="submitting">Saving...</span>
                <span v-else>Save Member</span>
            </button>
        </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useToastStore } from '@/stores/toast';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps({
    show: Boolean,
    employee: { type: Object, required: true },
    memberData: { type: Object, default: null } // If passed, edit mode
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();

const form = ref({
    name: '',
    relationship: '',
    dob: '',
    phone: '',
    occupation: '',
    is_dependent: false,
    is_emergency_contact: false
});

const errors = ref({});
const submitting = ref(false);
const today = new Date().toISOString().split('T')[0];

const isEditing = computed(() => !!props.memberData);

const relationshipOptions = [
    { value: 'spouse', label: 'Spouse' },
    { value: 'child', label: 'Child' },
    { value: 'father', label: 'Father' },
    { value: 'mother', label: 'Mother' },
    { value: 'sibling', label: 'Sibling' },
    { value: 'other', label: 'Other' },
];

watch(() => props.show, (val) => {
    if (val) {
        if (props.memberData) {
            form.value = { 
                ...props.memberData, 
                is_dependent: !!props.memberData.is_dependent,
                is_emergency_contact: !!props.memberData.is_emergency_contact
            };
        } else {
            form.value = { name: '', relationship: '', dob: '', phone: '', occupation: '', is_dependent: false, is_emergency_contact: false };
        }
        errors.value = {};
    }
});

const submit = async () => {
    submitting.value = true;
    errors.value = {};
    try {
        if (isEditing.value) {
            await axios.put(`/admin/employees/${props.employee.id}/families/${props.memberData.id}`, form.value);
            toast.success("Family member updated");
        } else {
            await axios.post(`/admin/employees/${props.employee.id}/families`, form.value);
            toast.success("Family member added");
        }
        emit('saved');
        emit('close');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            toast.error("Failed to save details");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
