<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-red-600 mb-4">Cancel Interview</h2>
            <p class="text-sm text-gray-500 mb-4">
                Are you sure you want to cancel this interview? A notification will be sent to the candidate and interviewer.
            </p>
            
            <div class="mb-5">
                <InputLabel value="Reason for Cancellation" />
                <textarea 
                    v-model="form.cancellation_reason" 
                    rows="3" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="e.g. Candidate withdrew, Position filled..."
                ></textarea>
                <InputError :message="form.errors.cancellation_reason" />
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="close" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Keep Interview</button>
                <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 disabled:opacity-75">
                    Confirm Cancellation
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
    interviewId: [String, Number]
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    cancellation_reason: ''
});

watch(() => props.show, (val) => {
    if (val) form.reset();
});

const close = () => emit('close');

const submit = () => {
    if (!form.cancellation_reason) {
        form.errors.cancellation_reason = 'Reason is required';
        return;
    }
    
    form.post(route('talent.candidates.interviews.cancel', props.interviewId), {
        onSuccess: () => {
            emit('success');
            close();
        }
    });
};
</script>
