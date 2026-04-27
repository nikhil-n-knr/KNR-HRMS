<template>
    <div class="h-full flex flex-col bg-white border-l border-gray-200">
        <div v-if="loading" class="p-12 text-center text-gray-500">
            <svg class="animate-spin h-8 w-8 mx-auto text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2">Loading ticket details...</p>
        </div>

        <div v-else-if="bug" class="flex flex-col h-full">
            <!-- Header -->
            <div class="px-6 py-4 border-b flex justify-between items-start bg-gray-50">
                <div>
                     <div class="flex items-center gap-3 mb-1">
                        <span class="text-xs font-mono text-gray-500">#{{ bug.id }}</span>
                        <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getSeverityClass(bug.severity)]">
                            {{ bug.severity }}
                        </span>
                        <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getPriorityClass(bug.priority)]">
                            {{ bug.priority }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 leading-tight">{{ bug.subject }}</h2>
                </div>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- High-Control Action Bar -->
            <div class="px-6 py-3 bg-white border-b flex items-center gap-3 overflow-x-auto no-scrollbar shadow-sm">
                <!-- Bounce Back -->
                <button @click="bounceBack" class="whitespace-nowrap flex items-center gap-2 px-3 py-1.5 bg-rose-50 text-rose-700 rounded-lg text-xs font-black uppercase tracking-widest border border-rose-100 hover:bg-rose-600 hover:text-white transition-all">
                    <ArrowUturnLeftIcon class="w-3.5 h-3.5" />
                    Bounce Back
                </button>

                <!-- Request Verification -->
                <button @click="requestVerification" class="whitespace-nowrap flex items-center gap-2 px-3 py-1.5 bg-teal-50 text-teal-700 rounded-lg text-xs font-black uppercase tracking-widest border border-teal-100 hover:bg-teal-600 hover:text-white transition-all">
                    <CheckBadgeIcon class="w-3.5 h-3.5" />
                    Request Verification
                </button>

                <button
                    @click="sendReminder"
                    :disabled="sendingReminder || bug?.reminder_sent_today"
                    class="whitespace-nowrap flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest border transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="bug?.reminder_sent_today
                        ? 'bg-slate-100 text-slate-500 border-slate-200'
                        : 'bg-indigo-50 text-indigo-700 border-indigo-100 hover:bg-indigo-600 hover:text-white'"
                >
                    <ClockIcon class="w-3.5 h-3.5" />
                    {{ bug?.reminder_sent_today ? 'Reminder Sent Today' : (sendingReminder ? 'Sending...' : 'Send Reminder') }}
                </button>

                <!-- QA Cycle (Visible if In Review or similar) -->
                <div class="flex bg-slate-100 rounded-lg p-1 gap-1">
                    <button @click="qaAction('approve')" class="p-1 px-2.5 rounded-md hover:bg-emerald-500 hover:text-white text-emerald-600 transition-all" title="Sign-off Fix">
                        <HandThumbUpIcon class="w-4 h-4" />
                    </button>
                    <button @click="qaAction('reject')" class="p-1 px-2.5 rounded-md hover:bg-rose-500 hover:text-white text-rose-600 transition-all" title="Reject Fix">
                        <HandThumbDownIcon class="w-4 h-4" />
                    </button>
                </div>

                <div class="h-6 w-px bg-gray-200"></div>

                <!-- NEW: Approval Gate Controls (Phase 11) -->
                <div v-if="bug.stage?.requires_approval" class="flex items-center gap-2 bg-amber-50 rounded-lg p-1 border border-amber-100 shadow-sm ring-4 ring-amber-500/5">
                    <div class="px-2 py-1 text-[10px] font-black text-amber-700 uppercase tracking-widest flex items-center gap-2">
                        <LockClosedIcon class="w-3.5 h-3.5" />
                        Gate Locked
                    </div>
                    <div v-if="canApprove" class="flex gap-1">
                        <button @click="quickApprove" class="px-3 py-1 bg-emerald-600 text-white rounded-md text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all">Quick Approve</button>
                    </div>
                    <div v-else class="px-2 py-1 text-[10px] font-bold text-slate-400">
                        Waiting for {{ bug.stage?.role?.name || 'Manager' }}
                    </div>
                </div>

                <!-- Code Integration -->
                <button @click="createBranch" :class="[branchCopied ? 'bg-emerald-500' : 'bg-slate-900', 'whitespace-nowrap flex items-center gap-2 px-3 py-1.5 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:opacity-90 transition-all shadow-lg']">
                    <CheckIcon v-if="branchCopied" class="w-3.5 h-3.5 animate-bounce" />
                    <CodeBracketIcon v-else class="w-3.5 h-3.5" />
                    {{ branchCopied ? 'Copied to Clipboard' : 'Create Branch' }}
                </button>
            </div>

            <!-- Auto-Closure Banner -->
            <div v-if="bug.system_closed_at" class="bg-amber-50 border-b border-amber-200 px-6 py-2.5 flex items-center gap-3">
                <ClockIcon class="w-4 h-4 text-amber-600" />
                <p class="text-sm font-bold text-amber-800 uppercase tracking-wider">
                    Verified automatically by System Policy on {{ new Date(bug.system_closed_at).toLocaleDateString() }}
                </p>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    <!-- Workflow State Stepper -->
                    <div class="mb-6 bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                        <label class="block text-xs font-bold text-emerald-700 uppercase tracking-wider mb-2">Current Stage</label>
                        <select v-model="selectedStage" @change="updateStage" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 rounded-md">
                            <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                {{ stage.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div class="prose max-w-none">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Description</h3>
                            <p class="text-gray-800 whitespace-pre-wrap text-sm">{{ bug.description }}</p>
                        </div>
                        
                        <div v-if="bug.steps_to_reproduce">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Steps to Reproduce</h3>
                             <p class="text-gray-800 whitespace-pre-wrap text-sm bg-gray-50 p-3 rounded">{{ bug.steps_to_reproduce }}</p>
                        </div>

                         <!-- Meta Data Grid -->
                        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-5 rounded-2xl text-sm border border-gray-100 shadow-inner">
                            <div>
                                <dt class="text-sm text-gray-400 uppercase font-black tracking-widest mb-1.5">Target Module</dt>
                                <dd class="font-bold text-emerald-700 flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                                    {{ bug.project?.name }} 
                                    <span v-if="bug.module" class="text-gray-300">/</span> 
                                    {{ bug.module?.name }}
                                </dd>
                            </div>
                            <div class="group/env relative">
                                <dt class="text-sm text-gray-400 uppercase font-black tracking-widest mb-1.5 flex items-center justify-between">
                                    Environment
                                    <button @click="captureEnv" class="text-emerald-600 hover:text-emerald-700 font-bold lowercase tracking-tighter">Capture Now</button>
                                </dt>
                                <dd class="font-bold text-slate-800 truncate" :title="bug.environment_metadata">
                                    {{ bug.environment_metadata || 'Not Synchronized' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-400 uppercase font-black tracking-widest mb-1.5">{{ assignedMembers.length > 1 ? 'Owners' : 'Owner' }}</dt>
                                <dd class="border-r pr-4">
                                    <div v-if="assignedMembers.length" class="flex flex-wrap gap-2">
                                        <span
                                            v-for="member in assignedMembers"
                                            :key="`owner-${member.id}`"
                                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-bold text-slate-700"
                                        >
                                            <span class="h-5 w-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">{{ member.name?.charAt(0) || '?' }}</span>
                                            {{ member.name }}
                                        </span>
                                    </div>
                                    <div v-else class="flex items-center gap-2 font-bold text-slate-900">
                                        <div class="h-6 w-6 rounded-full bg-slate-200 flex items-center justify-center text-sm">{{ ownerLabel.charAt(0) || '?' }}</div>
                                        {{ ownerLabel }}
                                    </div>
                                </dd>
                            </div>
                            <div class="relative group/link">
                                <dt class="text-sm text-gray-400 uppercase font-black tracking-widest mb-1.5">Dependencies</dt>
                                <button @click="linkDependency" class="text-xs font-bold text-emerald-600 flex items-center gap-1.5 py-1 px-2 -ml-2 rounded-lg hover:bg-emerald-50 transition-colors">
                                    <LinkIcon class="w-3 h-3" />
                                    Add Relation
                                </button>
                            </div>

                            <div class="col-span-2 rounded-xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div>
                                        <dt class="text-sm text-gray-400 uppercase font-black tracking-widest mb-1">Assign Members</dt>
                                        <p class="text-xs text-slate-500 font-semibold">Choose project team members for this bug.</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-black uppercase tracking-widest disabled:opacity-50"
                                        :disabled="savingAssignments || !selectedAssigneeIds.length"
                                        @click="saveAssignees"
                                    >
                                        {{ savingAssignments ? 'Saving...' : 'Save Members' }}
                                    </button>
                                </div>

                                <select
                                    v-model="selectedAssigneeIds"
                                    multiple
                                    class="block w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm h-32"
                                >
                                    <option v-for="member in availableAssignees" :key="member.id" :value="String(member.id)">
                                        {{ member.name }}
                                    </option>
                                </select>
                                <p class="mt-2 text-xs text-slate-400 font-medium">Hold Ctrl on Windows to select multiple members.</p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="member in assignedMembers"
                                        :key="`assigned-${member.id}`"
                                        class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-700"
                                    >
                                        <span class="h-5 w-5 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]">{{ member.name?.charAt(0) || '?' }}</span>
                                        {{ member.name }}
                                    </span>
                                    <span v-if="!assignedMembers.length" class="text-xs font-semibold text-slate-400">No members assigned yet.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div v-if="bug.attachments && bug.attachments.length">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Attachments</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <div v-for="(file, idx) in bug.attachments" :key="idx" class="relative group border rounded overflow-hidden shadow-sm">
                                    <!-- Image Preview -->
                                    <img v-if="isImage(file.mime)" :src="'/storage/' + file.path" class="h-32 w-full object-cover">
                                    <div v-else class="h-32 w-full flex items-center justify-center bg-gray-100 italic text-xs text-gray-400">
                                        {{ file.name.split('.').pop() }}
                                    </div>
                                    
                                    <a :href="'/storage/' + file.path" target="_blank" 
                                       class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white text-xs font-bold">
                                        View Full
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Context Panel (Git Mock Integration) -->
                        <div class="mt-8 pt-6 border-t border-slate-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <CodeBracketIcon class="w-5 h-5 text-slate-500" />
                                    Technical Context
                                </h3>
                                <button @click="createBranch" class="text-sm font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-1 rounded transition-colors hidden sm:block">
                                    + New Branch
                                </button>
                            </div>

                            <div class="space-y-3">
                                <!-- Mock PR -->
                                <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-indigo-100 p-1.5 rounded-lg text-indigo-600 mt-1">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v12"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9"/></svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a href="#" class="text-sm font-bold text-indigo-700 hover:underline hover:text-indigo-800 transition-colors">Fix UI glitch in #{{ bug.id }} reporting</a>
                                                <span class="bg-slate-200 text-slate-700 text-sm font-black px-1.5 py-0.5 rounded uppercase tracking-wider">#402</span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-1 font-mono group cursor-pointer relative w-max" title="Click to copy branch">
                                                <span class="font-sans font-medium">from</span> fix/bug-{{bug.id}}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs font-semibold">
                                        <div class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                                            <CheckBadgeIcon class="w-3.5 h-3.5" />
                                            Checks Passed
                                        </div>
                                        <div class="text-slate-500 hidden lg:block">Reviewed by <span class="text-slate-700">Sarah</span></div>
                                    </div>
                                </div>
                                
                                <!-- Mock Recent Commits -->
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                                    <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Recent Commits</h4>
                                    <ul class="space-y-2 relative before:absolute before:inset-y-0 before:left-2.5 before:w-px before:bg-slate-200 ml-2">
                                        <li class="relative pl-6">
                                            <span class="absolute left-1.5 top-1.5 w-2 h-2 rounded-full ring-4 ring-white bg-slate-400"></span>
                                            <div class="flex items-center gap-2">
                                                <a href="#" class="font-mono text-xs text-indigo-600 hover:underline">a1b2c3d</a>
                                                <span class="text-sm text-slate-700">Update layout gap for mobile</span>
                                            </div>
                                            <div class="text-sm text-slate-400 mt-0.5">Alex • 2 hours ago</div>
                                        </li>
                                        <li class="relative pl-6">
                                            <span class="absolute left-1.5 top-1.5 w-2 h-2 rounded-full ring-4 ring-white bg-slate-300"></span>
                                            <div class="flex items-center gap-2">
                                                <a href="#" class="font-mono text-xs text-slate-500 hover:underline">f8e7d6c</a>
                                                <span class="text-sm text-slate-600">Initial test fix for #{{bug.id}}</span>
                                            </div>
                                            <div class="text-sm text-slate-400 mt-0.5">Alex • 4 hours ago</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="mt-8 border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'comments'" :class="[activeTab === 'comments' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                                Comments
                            </button>
                            <button @click="activeTab = 'diagnostics'" :class="[activeTab === 'diagnostics' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm gap-2 flex items-center']">
                                Diagnostics
                                <span v-if="bug.forensics" class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                            </button>
                            <button @click="activeTab = 'history'" :class="[activeTab === 'history' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                                History
                            </button>
                        </nav>
                    </div>

                    <div class="mt-6">
                        <BugComments v-if="activeTab === 'comments'" :key="'comments-'+bug.id" :bug-id="bug.id" :initial-comments="bug.comments" :current-user-id="$page.props.auth.user.id" />
                        <BugForensics v-if="activeTab === 'diagnostics'" :key="'forensics-'+bug.id" :forensics="bug.forensics" />
                        <BugHistory v-if="activeTab === 'history'" :key="'history-'+bug.id" :activities="bug.activities" />
                    </div>

                </div>
            </div>
        </div>
        <div v-else class="flex-1 flex items-center justify-center text-gray-400">
            Select a bug to view details
        </div>

        <!-- Resolution Modal -->
         <Modal :show="showResolutionModal" @close="cancelResolution" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Resolution Required</h2>
                <p class="text-sm text-gray-500 mb-4">
                    You are closing this ticket. Please provide a resolution note or summary of the fix.
                </p>
                
                <div class="mt-4">
                    <InputLabel value="Resolution Note" />
                    <textarea 
                        v-model="resolutionNote" 
                        rows="3" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                        placeholder="Fixed by updating..."
                    ></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="cancelResolution">Cancel</SecondaryButton>
                    <PrimaryButton @click="confirmResolution" :disabled="!resolutionNote.trim()">
                        Confirm & Close
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import BugComments from './BugComments.vue';
import BugForensics from './BugForensics.vue';
import BugHistory from './BugHistory.vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    ClockIcon,
    ArrowUturnLeftIcon,
    CheckBadgeIcon,
    HandThumbUpIcon,
    HandThumbDownIcon,
    CodeBracketIcon,
    LinkIcon,
    ShieldCheckIcon,
    LockClosedIcon
} from '@heroicons/vue/24/outline';
import InputLabel from '@/Components/InputLabel.vue'; // Standardizing
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    bugId: Number,
    stages: Array
});

const emit = defineEmits(['close', 'updated']);
const toast = useToastStore();

const bug = ref(null);
const loading = ref(false);
const activeTab = ref('comments');
const selectedStage = ref(null);
const availableAssignees = ref([]);
const selectedAssigneeIds = ref([]);
const savingAssignments = ref(false);
const sendingReminder = ref(false);

const assignedMembers = computed(() => {
    if (!bug.value) return [];

    if (Array.isArray(bug.value.assignees) && bug.value.assignees.length) {
        return bug.value.assignees
            .map((entry) => ({
                id: entry.assignee_id,
                name: entry.assignee?.name || [entry.assignee?.first_name, entry.assignee?.last_name].filter(Boolean).join(' ') || 'Unknown',
            }))
            .filter((member) => member.id);
    }

    if (bug.value.assignee_id && bug.value.assignee) {
        return [{
            id: bug.value.assignee_id,
            name: bug.value.assignee.name || [bug.value.assignee.first_name, bug.value.assignee.last_name].filter(Boolean).join(' ') || 'Unknown',
        }];
    }

    return [];
});

const ownerLabel = computed(() => {
    if (!bug.value) return 'Awaiting Assignment';

    if (bug.value.assignee) {
        const name = bug.value.assignee.name
            || [bug.value.assignee.first_name, bug.value.assignee.last_name].filter(Boolean).join(' ');
        if (name) return name;
    }

    if (assignedMembers.value.length) {
        return assignedMembers.value[0].name;
    }

    return 'Awaiting Assignment';
});

const isAwaitingApproval = computed(() => {
    if (!bug.value || !props.stages) return false;
    const currentStage = props.stages.find(s => s.id === bug.value.workflow_stage_id);
    return currentStage && currentStage.name.toLowerCase().includes('manager');
});

const canApprove = computed(() => {
    if (!bug.value?.stage?.requires_approval) return true;
    const user = usePage().props.auth.user;
    // Admins or users with the required role can approve
    if (user.roles.some(r => ['Admin', 'Super Admin'].includes(r.name))) return true;
    return user.roles.some(r => r.id === bug.value.stage.role_id);
});

const quickApprove = async () => {
    if (!confirm("Authorizing transition from this stage. Proceed?")) return;
    try {
        // We simulate approval by just allowing the next stage change or adding a comment
        await axios.post(route('bugs.comments.store', bug.value.id), {
            body: `<strong>Gate Authorization:</strong> ${usePage().props.auth.user.name} has authorized movement from '${bug.value.stage.name}'.`,
            is_public: true
        });
        toast.success('Gate unlocked. You can now move the ticket to the next stage.');
    } catch (e) {
        console.error("Approval failed", e);
        toast.error('Approval failed.');
    }
};

import { usePage } from '@inertiajs/vue3';

const handleVerification = async (action) => {
    const isApprove = action === 'approve';
    let comment = '';
    
    if (!isApprove) {
        comment = prompt("Please provide a reason for rejection:");
        if (comment === null) return;
    } else {
        if (!confirm("Are you sure you want to approve this fix? This will unlock the verification gate.")) return;
    }

    try {
        await axios.post(route(`bugs.stage.${action}`, bug.value.id), { comment });
        fetchBugDetails();
        emit('updated');
        toast.success(isApprove ? 'Fix approved successfully.' : 'Fix rejected successfully.');
    } catch (e) {
        toast.error(e.response?.data?.message || 'Operation failed.');
    }
};

const approveTicket = async () => {
    if (!confirm("Are you sure you want to approve this ticket and release it to the development queue?")) return;
    
    // Find the next stage (usually 'Todo', 'Awaiting Assignee', or just the next ID if simple)
    // For now, we'll try to find 'Todo' or 'Internal' or the next stage in the array
    const currentIndex = props.stages.findIndex(s => s.id === bug.value.workflow_stage_id);
    let nextStage = props.stages.find(s => s.name.toLowerCase().includes('todo') || s.name.toLowerCase().includes('ready'));
    
    if (!nextStage && currentIndex < props.stages.length - 1) {
        nextStage = props.stages[currentIndex + 1];
    }

    if (!nextStage) {
        toast.info('Could not determine next workflow stage. Please select manually.');
        return;
    }

    try {
        await axios.put(route('bugs.stage.update', bug.value.id), {
            stage_id: nextStage.id,
            comment: "<strong>Management Approval:</strong> Ticket approved and released for development."
        });
        emit('updated');
        fetchBugDetails();
        toast.success('Ticket approved and released for development.');
    } catch (e) {
        console.error("Approval failed", e);
        toast.error('Approval failed.');
    }
};

const fetchBugDetails = async () => {
    if (!props.bugId) {
        bug.value = null;
        return;
    }
    loading.value = true;
    try {
        const { data } = await axios.get(route('bugs.show', props.bugId));
        bug.value = data;
        selectedStage.value = data.workflow_stage_id;
        selectedAssigneeIds.value = Array.isArray(data.assignees) && data.assignees.length
            ? data.assignees.map((entry) => String(entry.assignee_id))
            : (data.assignee_id ? [String(data.assignee_id)] : []);

        await loadAvailableAssignees(data.project_id || data.project?.id);
    } catch (e) {
        console.error("Failed to fetch bug details", e);
    } finally {
        loading.value = false;
    }
};

const loadAvailableAssignees = async (projectId) => {
    if (!projectId) {
        availableAssignees.value = [];
        return;
    }

    try {
        const response = await axios.get(route('bugs.assignees'), {
            params: { project_id: projectId },
        });
        availableAssignees.value = response.data || [];
    } catch (e) {
        console.error('Failed to fetch bug assignees', e);
        availableAssignees.value = [];
    }
};

const saveAssignees = async () => {
    if (!bug.value?.id || !bug.value?.workflow_stage_id) return;

    savingAssignments.value = true;
    try {
        const response = await axios.post(route('bugs.stage.update.advanced', bug.value.id), {
            stage: bug.value.workflow_stage_id,
            assignee_ids: selectedAssigneeIds.value,
            note: 'Updated assignees from bug detail pane.',
        });
        await fetchBugDetails();
        emit('updated');
        toast.success(response?.data?.message || 'Assignees updated successfully.');
    } catch (e) {
        console.error('Failed to save bug assignees', e);
        toast.error(e?.response?.data?.message || 'Failed to update assignees.');
    } finally {
        savingAssignments.value = false;
    }
};

const sendReminder = async () => {
    if (!bug.value?.id || sendingReminder.value || bug.value?.reminder_sent_today) return;

    sendingReminder.value = true;
    try {
        const response = await axios.post(route('bugs.reminder.send', bug.value.id));
        bug.value.reminder_sent_today = true;
        toast.success(response.data?.message || 'Reminder sent successfully.');
        emit('updated');
    } catch (e) {
        const message = e?.response?.data?.message || 'Failed to send reminder.';
        toast.error(message);
    } finally {
        sendingReminder.value = false;
    }
};

watch(() => props.bugId, (newId) => {
    fetchBugDetails();
    if (newId) activeTab.value = 'comments';
});

// Initial fetch if mounted with ID
onMounted(() => {
    if (props.bugId) fetchBugDetails();
});

const showResolutionModal = ref(false);
const resolutionNote = ref('');
const pendingStageId = ref(null);

const updateStage = async () => {
    const stage = props.stages.find(s => s.id === selectedStage.value);
    
    // Check if stage is final (e.g. Closed, Resolved)
    // Assuming 'is_final' property exists on stage from backend
    if (stage && stage.is_final) {
        pendingStageId.value = selectedStage.value;
        // Reset valid stage temporarily to avoid UI flicker until confirmed? 
        // Or just let it sit there.
        showResolutionModal.value = true;
        return;
    }

    await processStageUpdate(selectedStage.value);
};

const confirmResolution = async () => {
    if (!resolutionNote.value.trim()) return; // Validation
    
    await processStageUpdate(pendingStageId.value, resolutionNote.value);
    showResolutionModal.value = false;
    resolutionNote.value = '';
    pendingStageId.value = null;
};

const cancelResolution = () => {
    showResolutionModal.value = false;
    resolutionNote.value = '';
    pendingStageId.value = null;
    // Revert select to current bug stage
    if (bug.value) selectedStage.value = bug.value.workflow_stage_id;
};

const bounceBack = async () => {
    const reason = prompt("Why are you bouncing this back? (Notes for the reporter)");
    if (reason === null) return;
    
    try {
        await axios.put(route('bugs.stage.update', bug.value.id), {
            stage_id: props.stages.find(s => s.name.toLowerCase().includes('awaiting'))?.id || props.stages[0].id,
            comment: `<strong>Bounce Back:</strong> ${reason}`
        });
        emit('updated');
        fetchBugDetails();
        toast.success('Ticket bounced back successfully.');
    } catch (e) {
        console.error("Bounce back failed", e);
        toast.error('Bounce back failed.');
    }
};

const requestVerification = async () => {
    try {
        await axios.put(route('bugs.stage.update', bug.value.id), {
            stage_id: props.stages.find(s => s.name.toLowerCase().includes('verification'))?.id || props.stages[1].id,
            comment: "Fix completed. Requesting formal verification.",
            is_public: true // Formal verification requests should be visible to clients
        });
        emit('updated');
        fetchBugDetails();
        toast.success('Verification requested successfully.');
    } catch (e) {
        console.error("Verification request failed", e);
        toast.error('Verification request failed.');
    }
};

const qaAction = async (action) => {
    const isApprove = action === 'approve';
    try {
        await axios.put(route('bugs.stage.update', bug.value.id), {
            stage_id: isApprove 
                ? props.stages.find(s => s.name.toLowerCase().includes('client'))?.id || props.stages.find(s => s.is_final)?.id 
                : props.stages.find(s => s.name.toLowerCase().includes('todo'))?.id || props.stages[0].id,
            comment: isApprove ? "QA Sign-off: Fix verified internally." : "QA Rejection: Issue persists or regression found."
        });
        emit('updated');
        fetchBugDetails();
        toast.success(isApprove ? 'QA approval completed.' : 'QA rejection recorded.');
    } catch (e) {
        console.error("QA action failed", e);
        toast.error('QA action failed.');
    }
};

const branchCopied = ref(false);
const createBranch = () => {
    const branch = `fix/bug-${bug.value.id}-${bug.value.subject.toLowerCase().replace(/[^a-z0-0]/g, '-')}`;
    navigator.clipboard.writeText(`git checkout -b ${branch}`);
    branchCopied.value = true;
    setTimeout(() => branchCopied.value = false, 2000);
};

const captureEnv = async () => {
    const meta = {
        browser: navigator.userAgent,
        screen: `${window.screen.width}x${window.screen.height}`,
        url: window.location.href,
        timestamp: new Date().toISOString()
    };
    
    try {
        await axios.post(route('bugs.comments.store', bug.value.id), {
            body: `<strong>Environment Snapshot Captured:</strong> <pre class="text-sm bg-slate-50 p-2 rounded mt-1">${JSON.stringify(meta, null, 2)}</pre>`,
            is_public: false
        });
        fetchBugDetails();
    } catch (e) {
        console.error("Failed to capture env", e);
        toast.error('Failed to capture environment snapshot.');
    }
};

const linkDependency = () => {
    // For now, let's use a simple prompt to demo end-to-end linking
    const otherId = prompt("Enter the Ticket ID (#) you want to link as a dependency:");
    if (!otherId) return;

    axios.post(route('bugs.comments.store', bug.value.id), {
        body: `<strong>Linked Dependency:</strong> Related to ticket <a href="/projects/bugs?bug=${otherId}" class="text-indigo-600 font-bold underline">#${otherId}</a>`,
        is_public: false
    }).then(() => fetchBugDetails());
};

const processStageUpdate = async (stageId, note = null) => {
    try {
        await axios.put(route('bugs.stage.update', bug.value.id), {
            stage_id: stageId,
            resolution_note: note
        });
        emit('updated'); // Signal parent to refresh list
        fetchBugDetails();
        toast.success('Stage updated successfully.');
    } catch (e) {
        console.error("Failed to update stage", e);
        toast.error('Failed to update stage.');
        // Revert on error
        if (bug.value) selectedStage.value = bug.value.workflow_stage_id;
    }
};

const getSeverityClass = (severity) => {
    const map = {
        critical: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        medium: 'bg-teal-100 text-teal-800',
        low: 'bg-gray-100 text-gray-800'
    };
    return map[severity] || 'bg-gray-100 text-gray-800';
};

const getPriorityClass = (priority) => {
    const map = {
        urgent: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        normal: 'bg-teal-100 text-teal-800',
        low: 'bg-green-100 text-green-800'
    };
    return map[priority] || 'bg-gray-100 text-gray-800';
};

const isImage = (mime) => {
    return ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(mime);
};
</script>
