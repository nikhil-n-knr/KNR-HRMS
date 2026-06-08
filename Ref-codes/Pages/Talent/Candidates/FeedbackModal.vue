<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Submit Interview Feedback</h2>
            
            <div class="mb-5">
                <InputLabel value="Rating" class="mb-2" />
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
                <InputLabel value="Recommendation" />
                <select v-model="form.recommendation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="" disabled>Select Recommendation</option>
                    <option>Strong Hire</option>
                    <option>Hire</option>
                    <option>On Hold</option>
                    <option>No Hire</option>
                    <option>Strong No</option>
                </select>
                <InputError :message="form.errors.recommendation" />
            </div>

            <div class="mb-4">
                <InputLabel value="Round Outcome (Result)" />
                <select v-model="form.result" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="" disabled>Select Result</option>
                    <option value="Passed">Passed</option>
                    <option value="Failed">Failed</option>
                    <option value="Hold">On Hold</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Marking as "Passed" will suggest scheduling the next round.</p>
            </div>

            <div class="mb-4">
                <InputLabel value="Summary" />
                <textarea v-model="form.summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Overall thoughts..."></textarea>
                <InputError :message="form.errors.summary" />
            </div>

            <div class="mb-4">
                <InputLabel value="Recording Link (Optional)" />
                <input v-model="form.recording_url" type="url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="https://zoom.us/rec/..." />
                <InputError :message="form.errors.recording_url" />
                <p class="text-xs text-gray-500 mt-1">Link to Zoom/Teams/Loom recording</p>
            </div>

            <div class="mb-4">
                <InputLabel value="Upload Recording/Attachment (Optional)" />
                <input @change="handleFileUpload" type="file" accept="audio/*,video/*,.pdf,.doc,.docx" class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700
                    hover:file:bg-indigo-100" />
                <p class="text-xs text-gray-500 mt-1">Upload recording, notes, or test results</p>
                <InputError :message="form.errors.attachment" />
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="close" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-75">
                    Submit Feedback
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
    interviewId: [String, Number],
    existingFeedback: Object
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    rating: 0,
    recommendation: '',
    result: '', // New Field
    summary: '',
    recording_url: '',
    attachment: null,
    pros: [],
    cons: []
});

const handleFileUpload = (event) => {
    form.attachment = event.target.files[0];
};

watch(() => props.show, (val) => {
    if (val) {
        if (props.existingFeedback) {
            form.rating = props.existingFeedback.rating || 0;
            form.recommendation = props.existingFeedback.recommendation || '';
            form.result = props.existingFeedback.result || ''; // Load existing result
            form.summary = props.existingFeedback.summary || '';
            form.recording_url = props.existingFeedback.recording_url || '';
        } else {
            form.reset();
        }
    }
});

const close = () => emit('close');

const submit = () => {
    if (form.rating === 0) return alert('Please provide a rating.');
    
    form.post(route('talent.candidates.interviews.feedback.store', props.interviewId), {
        onSuccess: () => {
            emit('success');
            if (form.result === 'Passed') {
                emit('scheduleNext');
            }
            close();
        }
    });
};
</script>
