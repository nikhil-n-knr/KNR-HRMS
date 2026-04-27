<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="xl">
        <div class="p-8">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Advance Stage</h2>
            <p class="text-sm text-slate-500 font-medium mb-8">
                Moving <strong class="text-indigo-600">#{{ bug?.id }}</strong> to <strong class="text-emerald-600">{{ targetStage?.name }}</strong>
            </p>
            
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    
                    <!-- New: Stage Info (Mentor/Approver) -->
                    <div v-if="targetStage?.mentor_id || targetStage?.requires_verification" class="p-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 flex flex-col gap-2">
                         <div v-if="targetStage?.mentor_id" class="flex items-center gap-2 text-xs font-bold text-indigo-700">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Stage Mentor: {{ users[targetStage.mentor_id] || 'Loading mentor...' }}
                         </div>
                         <div v-if="targetStage?.requires_verification" class="flex items-center gap-2 text-xs font-bold text-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Verification Required: Ticket will enter approval state
                         </div>
                    </div>

            <!-- Assignees -->
                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <InputLabel value="Assignees (Required or Optional depending on stage)" class="text-xs font-black uppercase tracking-widest text-slate-500 mb-0" />
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="showAllUsers" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 transition-colors">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-indigo-600 transition-colors">Show All Employees</span>
                            </label>
                        </div>
                        <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50 relative">
                            <select multiple v-model="form.assignee_ids" class="block w-full border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-32">
                                <option v-for="(name, id) in stageUsers" :key="id" :value="id">{{ name }}</option>
                            </select>
                            <p class="text-sm text-slate-400 mt-2 font-medium">Select who will be responsible for this ticket in the new stage. Hold Ctrl/Cmd to pick multiple.</p>
                            
                            <div v-if="!showAllUsers && Object.keys(stageUsers).length < Object.keys(users || {}).length" class="absolute bottom-4 right-4 bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-1 rounded-md">
                                Filtered to Stage Personnel
                            </div>
                        </div>
                    </div>

                    <!-- Transition Note -->
                    <div>
                        <InputLabel value="Transition Note & Instructions" class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2" />
                        <textarea v-model="form.note" rows="4" class="block w-full border-gray-300 rounded-2xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4" placeholder="Log details, handover notes, or resolution summary..."></textarea>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <InputLabel value="Supporting Files" class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2" />
                        <div class="mt-1 border-2 border-dashed border-slate-200 rounded-2xl p-6 flex flex-col items-center justify-center hover:bg-slate-50 transition-colors">
                            <input type="file" multiple @change="handleFileUpload" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:tracking-widest file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all cursor-pointer" />
                            <p class="text-sm text-slate-400 mt-3 font-medium" v-if="form.attachments.length">{{ form.attachments.length }} files queued for upload</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse sm:flex-row justify-end gap-3 border-t border-slate-100 pt-6">
                    <button type="button" @click="$emit('close')" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 rounded-xl transition-all">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-6 py-3 text-xs font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-500 shadow-lg shadow-indigo-200 rounded-xl transition-all disabled:opacity-50">Confirm Transition</button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    show: Boolean,
    bug: Object,
    targetStage: Object,
    users: Object // { id: name } map
});

const emit = defineEmits(['close', 'completed']);

const form = useForm({
    assignee_ids: [],
    note: '',
    attachments: []
});

const handleFileUpload = (e) => {
    form.attachments = Array.from(e.target.files);
};

const showAllUsers = ref(false);

// Filter users dynamically based on configured stage personnel
const stageUsers = computed(() => {
    let allUsers = props.users || {};
    
    if (showAllUsers.value) {
        return allUsers;
    }
    
    if (props.targetStage?.stage_personnel && props.targetStage.stage_personnel.length > 0) {
        let personnelIds = props.targetStage.stage_personnel
            .filter(p => p.type === 'user')
            .map(p => String(p.id));
            
        if (personnelIds.length > 0) {
            let filtered = {};
            for (const id of personnelIds) {
                if (allUsers[id]) filtered[id] = allUsers[id];
            }
            return Object.keys(filtered).length > 0 ? filtered : allUsers;
        }
    }
    
    return allUsers; 
});

// Pre-fill assignees efficiently
watch(() => props.show, (isVisible) => {
    if (isVisible && props.bug) {
        form.reset();
        showAllUsers.value = false; // Reset toggle when opened
        
        let targetPersonnelIds = [];
        if (props.targetStage?.stage_personnel && props.targetStage.stage_personnel.length > 0) {
             targetPersonnelIds = props.targetStage.stage_personnel
                .filter(p => p.type === 'user')
                .map(p => String(p.id));
        }

        if (targetPersonnelIds.length > 0) {
             // If personnel are mapped to the stage, Auto-assign ALL of them so the user doesn't have to manually click
             form.assignee_ids = targetPersonnelIds;
             
        } else {
            // No personnel configured: standard carry over of existing assignees
            if (props.bug.assignees && props.bug.assignees.length > 0) {
                form.assignee_ids = props.bug.assignees.map(a => String(a.assignee_id));
            } else if (props.bug.assignee_id) {
                form.assignee_ids = [String(props.bug.assignee_id)];
            } else {
                form.assignee_ids = [];
            }
        }
    }
});

const submit = () => {
    if (!props.bug || !props.targetStage) return;

    // Use Inertia's post to handle file uploads properly since PUT doesn't support multipart/form-data well in all browsers
    form.post(route('bugs.stage.update.advanced', { bug: props.bug.id, stage: props.targetStage.id }), {
        onSuccess: () => {
            form.reset();
            emit('completed');
        },
        preserveScroll: true
    });
};
</script>
