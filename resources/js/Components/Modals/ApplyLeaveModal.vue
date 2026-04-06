<template>
  <Modal :show="show" @close="$emit('close')">
    <template #header>
      <h3 class="text-lg font-bold text-gray-800">Apply for Leave</h3>
    </template>
    
    <template #body>
      <div class="space-y-4">
        <!-- Leave Type -->
        <BaseSelect 
            v-model="form.leave_type_id" 
            label="Leave Type" 
            :options="typeOptions"
            required
            :error="errors.leave_type_id"
        />

        <!-- Date Range -->
        <div class="grid grid-cols-2 gap-4">
            <BaseInput 
                v-model="form.start_date" 
                type="date" 
                label="Start Date" 
                required
                :min="today"
                :error="errors.start_date"
            />
            <BaseInput 
                v-model="form.end_date" 
                type="date" 
                label="End Date" 
                required
                :min="form.start_date || today"
                :error="errors.end_date"
            />
        </div>

        <!-- Duration Info -->
        <div v-if="duration > 0" class="p-3 bg-emerald-50 rounded-lg flex justify-between items-center">
            <span class="text-sm text-emerald-800 font-medium">Total Duration:</span>
            <span class="text-lg font-bold text-emerald-700">{{ duration }} Days</span>
        </div>

        <!-- Reason -->
        <BaseTextarea 
            v-model="form.reason"
            label="Reason"
            placeholder="Please detail why you are requesting leave..."
            required
            rows="3"
            :error="errors.reason"
        />
      </div>
    </template>

    <template #footer>
        <div class="flex justify-end gap-3">
            <button @click="$emit('close')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition">Cancel</button>
            <button 
                @click="submit" 
                :disabled="submitting || !isValid"
                class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:scale-[1.02] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
                <svg v-if="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Submit Request
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
import BaseTextarea from '@/Components/BaseTextarea.vue';

const props = defineProps({
    show: Boolean,
    leaveTypes: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'submitted']);
const toast = useToastStore();

const form = ref({
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: ''
});

const errors = ref({});
const submitting = ref(false);

// Date Utils
const today = new Date().toISOString().split('T')[0];

const duration = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return 0;
    const start = new Date(form.value.start_date);
    const end = new Date(form.value.end_date);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
    return diffDays > 0 ? diffDays : 0;
});

const typeOptions = computed(() => props.leaveTypes.map(t => ({ value: t.id, label: `${t.name} (${t.code})` })));

const isValid = computed(() => {
    return form.value.leave_type_id && form.value.start_date && form.value.end_date && form.value.reason && duration.value > 0;
});

const submit = async () => {
    submitting.value = true;
    errors.value = {};
    try {
        await axios.post('/api/leaves/apply', form.value);
        toast.success("Leave request submitted successfully");
        emit('submitted');
        emit('close');
        
        // Reset
        form.value = {
            leave_type_id: '',
            start_date: '',
            end_date: '',
            reason: ''
        };
    } catch (e) {
        if (e.response?.status === 422) {
             // Handle Validation Errors normally
             errors.value = e.response.data.errors || {};
             // Handle "Insufficient Balance" custom error message
             if (e.response.data.message && !e.response.data.errors) {
                  toast.error(e.response.data.message);
             }
        } else {
             toast.error(e.response?.data?.message || "Failed to submit request");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
