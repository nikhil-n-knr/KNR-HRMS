<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Reject Candidate</h2>
            
            <div class="mb-4">
                <InputLabel value="Rejection Reason" />
                <select v-model="form.rejection_reason" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="" disabled>Select a reason</option>
                    <option>Skills Mismatch</option>
                    <option>Experience Mismatch</option>
                    <option>Culture Fit</option>
                    <option>Salary Expectations</option>
                    <option>Other</option>
                </select>
                <InputError :message="form.errors.rejection_reason" />
            </div>

            <div class="mb-4">
                <InputLabel value="Internal Notes (Optional)" />
                <textarea v-model="form.rejection_notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Add specific details..."></textarea>
                <InputError :message="form.errors.rejection_notes" />
            </div>

            <div class="mb-6">
                <!-- Notification Checkbox -->
                <div class="flex items-center">
                    <input id="notify" type="checkbox" v-model="form.notify_candidate" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="notify" class="ml-2 block text-sm text-gray-900">
                        Send automated rejection email to candidate
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="close" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 disabled:opacity-75">
                    Reject Candidate
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    show: Boolean,
    applicationId: [String, Number]
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    rejection_reason: '',
    rejection_notes: '',
    notify_candidate: true // Default to true
});

watch(() => props.show, (val) => {
    if (val) form.reset();
});

const close = () => emit('close');

const submit = () => {
    form.put(route('candidates.reject', props.applicationId), {
        onSuccess: () => {
            emit('success');
            close();
        }
    });
};
</script>
