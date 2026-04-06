<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" @click="close"></div>

                <div class="relative bg-white/80 backdrop-blur-xl border border-white/50 rounded-xl shadow-2xl w-full max-w-lg mx-auto z-10 overflow-hidden transform transition-all">
                    
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ isEdit ? 'Edit Interview' : 'Schedule Interview' }}</h3>
                            <div class="mt-1">
                                <p class="text-xs text-gray-500">{{ isEdit ? 'Update interview details' : 'Add a new round' }} for <span class="font-medium text-indigo-600">{{ candidateName }}</span></p>
                            </div>
                        </div>
                        <button @click="close" class="text-gray-400 hover:text-gray-600 p-1 rounded-md hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-5">
                       
                       <!-- Rounds Config -->
                       <div class="grid grid-cols-2 gap-4">
                           <div>
                                <InputLabel value="Round Type" class="mb-1.5" />
                                <select v-model="form.round" class="block w-full border-gray-300 rounded-lg text-sm bg-gray-50/50 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                                    <option v-for="round in roundTypes" :key="round">{{ round }}</option>
                                    <option disabled>---</option>
                                    <option disabled>Manage Rounds (Config)</option>
                                </select>
                                <InputError :message="form.errors?.round" />
                           </div>
                           <div>
                                <InputLabel value="Round Title (Optional)" class="mb-1.5" />
                                <TextInput v-model="form.round_title" placeholder="e.g. System Design" class="block w-full bg-gray-50/50" />
                                <InputError :message="form.errors?.round_title" />
                           </div>
                       </div>

                       <!-- Time & Method -->
                       <div class="grid grid-cols-2 gap-4">
                           <div class="col-span-1">
                                <InputLabel value="Date & Time" class="mb-1.5" />
                                <TextInput type="datetime-local" v-model="form.scheduled_at" class="block w-full" />
                                <InputError :message="form.errors?.scheduled_at" />
                           </div>
                            <div class="col-span-1">
                                <InputLabel value="Duration (min)" class="mb-1.5" />
                                <TextInput type="number" v-model="form.duration" class="block w-full" />
                                <InputError :message="form.errors?.duration" />
                           </div>
                       </div>

                       <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Interview Mode" class="mb-1.5" />
                                <select v-model="form.type" class="block w-full border-gray-300 rounded-lg text-sm bg-gray-50/50 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option>Online</option>
                                    <option>In-Person</option>
                                    <option>Phone</option>
                                </select>
                                <InputError :message="form.errors?.type" />
                            </div>
                            <!-- Dynamic Field based on Mode -->
                             <div v-if="form.type === 'Online'">
                                <InputLabel value="Meeting Link" class="mb-1.5" />
                                <TextInput type="text" v-model="form.meeting_link" placeholder="Zoom/Teams URL" class="block w-full" />
                                <InputError :message="form.errors?.meeting_link" />
                             </div>
                             <div v-if="form.type === 'In-Person'">
                                <InputLabel value="Location" class="mb-1.5" />
                                <TextInput type="text" v-model="form.location" placeholder="Office / Room" class="block w-full" />
                                <InputError :message="form.errors?.location" />
                             </div>
                       </div>

                       <!-- Interviewers -->
                        <div v-if="!isEdit">
                             <InputLabel value="Interviewers" class="mb-1.5" />
                             <Combobox 
                                v-model="form.interviewer_ids" 
                                :items="users" 
                                :multiple="true" 
                                placeholder="Select Interviewers..." 
                            />
                            <InputError :message="form.errors?.interviewer_ids" />
                        </div>

                         <!-- Invite Message Config -->
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Invite Message</label>
                                 <button type="button" 
                                        @click="useCustomMessage = !useCustomMessage" 
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        {{ useCustomMessage ? 'Use Default Template' : 'Customize Message' }}
                                </button>
                            </div>
                            
                            <div v-if="!useCustomMessage" class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-600 italic">
                                <p>{{ isRescheduling ? 'Reschedule notification' : 'Default invitation' }} template will be sent based on interview details.</p>
                            </div>
                             <div v-else>
                                <RichTextEditor v-model="form.message_body" />
                            </div>

                            <!-- Reschedule Option -->
                            <div v-if="isRescheduling" class="mt-3 flex items-center">
                                <input id="send_reschedule" type="checkbox" v-model="form.send_reschedule_email" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="send_reschedule" class="ml-2 block text-sm text-gray-900">
                                    Send Email Notification to Candidate
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                        <button @click="close" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">Cancel</button>
                        <button @click="confirm" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-75">
                            {{ isEdit ? 'Update Interview' : 'Schedule Interview' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Combobox from '@/Components/Combobox.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: Boolean,
    candidateName: String,
    interview: Object, // If passed, implies Edit Mode
    users: Array,
    initialRound: String,
    applicationId: [String, Number]
});

const emit = defineEmits(['close', 'success']);

const isEdit = computed(() => !!props.interview);
const useCustomMessage = ref(false);

const form = useForm({
    round: props.initialRound || 'Round 1',
    round_title: '',
    scheduled_at: '',
    duration: 60,
    type: 'Online',
    meeting_link: '',
    location: '',
    interviewer_ids: [],
    message_body: '',
    send_reschedule_email: true
});

const roundTypes = [
    'Round 1', 'Round 2', 'Round 3', 
    'Technical', 'Managerial', 'HR', 'Final',
    'Screening', 'Behavioral'
];

const isRescheduling = computed(() => {
    if (!isEdit.value || !props.interview) return false;
    // Simple string comparison for datetime-local format
    return form.scheduled_at !== props.interview.scheduled_at;
});

// Default Template Generator
const generateDefaultTemplate = () => {
    if (isRescheduling.value) {
        return `<p>Hi ${props.candidateName},</p>
            <p>Your <strong>${form.round}</strong> interview has been rescheduled.</p>
            <p><strong>New Time:</strong> ${new Date(form.scheduled_at).toLocaleString()}</p>
            <p><strong>Mode:</strong> ${form.type}</p>
            ${form.type === 'Online' ? `<p><strong>Link:</strong> ${form.meeting_link || 'TBD'}</p>` : ''}
            <p>We apologize for any inconvenience.</p>
            <p>Best regards,<br>Hiring Team</p>`;
    }
    return `<p>Hi ${props.candidateName},</p>
            <p>We would like to invite you to a <strong>${form.round}</strong> interview.</p>
            <p><strong>Time:</strong> ${new Date(form.scheduled_at).toLocaleString()}</p>
            <p><strong>Mode:</strong> ${form.type}</p>
            ${form.type === 'Online' ? `<p><strong>Link:</strong> ${form.meeting_link || 'TBD'}</p>` : ''}
            <p>Best regards,<br>Hiring Team</p>`;
};

watch(() => props.show, (val) => {
    if (val) {
        form.clearErrors();
        if (props.interview) {
            // Edit Mode - Fill
            form.round = props.interview.round;
            form.round_title = props.interview.round_title || '';
            form.scheduled_at = props.interview.scheduled_at; // Ensure format YYYY-MM-DDTHH:mm
            form.duration = props.interview.duration;
            form.type = props.interview.type;
            form.meeting_link = props.interview.meeting_link;
            form.location = props.interview.location;
            form.message_body = props.interview.message_body || '';
            useCustomMessage.value = !!props.interview.message_body;
        } else {
            // Create Mode - Reset
            form.reset();
            form.round = props.initialRound || 'Round 1';
            form.round_title = '';
            form.scheduled_at = '';
            form.duration = 60;
            form.type = 'Online';
            form.meeting_link = '';
            form.location = '';
            form.interviewer_ids = [];
            form.message_body = '';
            form.send_reschedule_email = true;
            useCustomMessage.value = false;
        }
    }
});

// Watch for changes to update template if not custom
watch([() => form.round, () => form.scheduled_at, () => form.type, () => form.meeting_link, isRescheduling], () => {
    if (!useCustomMessage.value) {
        form.message_body = generateDefaultTemplate();
    }
});

const close = () => emit('close');

const confirm = () => {
    if (!form.scheduled_at) return alert('Please select a date and time.');
    if (!isEdit.value && form.interviewer_ids.length === 0) return alert('Select at least one interviewer.');

    const options = {
        onSuccess: () => {
            emit('success');
            close();
        }
    };

    if (isEdit.value) {
        form.put(route('talent.candidates.interviews.update', props.interview.id), options);
    } else {
        if (!props.applicationId) {
            alert("Application context missing. Cannot schedule.");
            return;
        }
        form.post(route('talent.candidates.interviews.store', props.applicationId), options);
    }
};

</script>
