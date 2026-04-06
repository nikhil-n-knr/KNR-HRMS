<template>
  <Modal :show="show" title="Upload Document" @close="$emit('close')">
    <div class="space-y-4">
        <BaseInput 
            v-model="form.title" 
            label="Document Title" 
            placeholder="e.g. Aadhar Card, Offer Letter" 
            required 
            :error="errors.title" 
        />
        
        <BaseSelect 
            v-model="form.category" 
            label="Category" 
            required 
            :error="errors.category"
        >
            <option value="" disabled>Select Category</option>
            <optgroup label="General">
                <option value="Official">Official (Contracts, Offers)</option>
                <option value="Financial">Financial (Payslips, Bank)</option>
                <option value="Identity">Identity (Passport, Aadhar)</option>
                <option value="Legal">Legal & Compliance</option>
                <option value="Education">Education & Certificates</option>
                <option value="Other">Other</option>
            </optgroup>
            <optgroup label="Medical & Health">
                    <option value="Medical - Lab Report">Lab Report</option>
                    <option value="Medical - Prescription">Prescription</option>
                    <option value="Medical - Fitness">Fitness Certificate</option>
                    <option value="Medical - Other">Other Medical</option>
            </optgroup>
        </BaseSelect>

        <!-- Event Date (Contextual) -->
        <div v-if="form.category && form.category.startsWith('Medical')">
             <BaseInput 
                type="date" 
                v-model="form.event_date" 
                label="Checkup/Event Date" 
                :error="errors['metadata.event_date']"
            />
             <p class="text-[10px] text-gray-500 mt-1">Links this document to a timeline event.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select File <span class="text-red-500">*</span></label>
            <input 
                type="file" 
                ref="fileInput"
                @change="handleFileChange"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"
                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
            >
            <p v-if="errors.file" class="text-xs text-red-500 mt-1">{{ errors.file[0] }}</p>
            <p class="text-xs text-gray-500 mt-1">Accepted: JPG, PNG, PDF, DOC (Max 5MB)</p>
        </div>
    </div>

    <template #footer>
        <div class="flex justify-end gap-3">
            <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Cancel</button>
            <button 
                type="button"
                @click="submit" 
                :disabled="submitting"
                class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:scale-[1.02] transition-all disabled:opacity-50"
            >
                <span v-if="submitting">Uploading...</span>
                <span v-else>Upload</span>
            </button>
        </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useToastStore } from '@/stores/toast';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps({
    show: Boolean,
    employee: { type: Object, required: true },
    prefill: { type: Object, default: () => ({}) } // Allow pre-filling
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();
const fileInput = ref(null);

const form = ref({
    title: '',
    category: '',
    event_date: '',
    file: null
});

const errors = ref({});
const submitting = ref(false);

watch(() => props.show, (val) => {
    if (val) {
        // Hydrate from prefill if available
        form.value = { 
            title: props.prefill?.title || '', 
            category: props.prefill?.category || '', 
            event_date: props.prefill?.date || '',
            file: null 
        };
        if(fileInput.value) fileInput.value.value = '';
        errors.value = {};
    }
});

const handleFileChange = (e) => {
    form.value.file = e.target.files[0];
};

const submit = async () => {
    if (!form.value.file) {
        errors.value = { file: ['The file field is required.'] };
        toast.error("Please select a file to upload.");
        return;
    }
    if (!form.value.category) {
        errors.value = { category: ['Please select a category.'] };
        toast.error("Please select a document category.");
        return;
    }

    submitting.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('category', form.value.category);
    formData.append('file', form.value.file);

    // Meta logic
    if (form.value.event_date) {
        // Indexed array keys for Laravel array validation
        formData.append('metadata[event_date]', form.value.event_date);
        formData.append('metadata[event_type]', 'Checkup');
    }

    try {
        await axios.post(`/api/admin/employees/${props.employee.id}/documents`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success("Document uploaded successfully");
        emit('saved');
        emit('close');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            toast.error("Failed to upload document");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
