<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Screening Feedback</h2>
            
            <div class="mb-5">
                <InputLabel value="Screening Rating" class="mb-2" />
                <div class="flex space-x-2">
                     <button v-for="star in 5" :key="star" type="button" @click="form.rating = star" class="focus:outline-none transition-colors">
                        <svg class="w-8 h-8" :class="star <= form.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.rating" />
            </div>

            <div class="mb-4">
                <InputLabel value="Review Notes / Summary" />
                <textarea v-model="form.feedback" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Applicant matches requirements..."></textarea>
                <InputError :message="form.errors.feedback" />
            </div>

            <div class="mb-4">
                <InputLabel value="Outcome (Optional)" />
                <select v-model="form.result" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="" disabled>Select Outcome</option>
                    <option value="Interview">Move to Interview</option>
                    <option value="Rejected">Reject Candidate</option>
                    <option value="Hold">Keep on Hold</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">This will automatically update the candidate's status.</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="close" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-75">
                    Save Assessment
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
    candidateId: [String, Number],
    existingRating: Number,
    existingFeedback: String
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    rating: 0,
    feedback: '',
    result: ''
});

watch(() => props.show, (val) => {
    if (val) {
        form.rating = props.existingRating || 0;
        form.feedback = props.existingFeedback || '';
        form.result = ''; // Always reset result unless we want to persist
    }
});

const close = () => emit('close');

const submit = () => {
    form.post(route('talent.candidates.rate-screening', props.candidateId), {
        onSuccess: () => {
            emit('success');
            close();
        }
    });
};
</script>
