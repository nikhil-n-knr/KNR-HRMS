<template>
    <div class="h-full flex flex-col bg-gray-50">
        <!-- Header -->
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Form Builder</h2>
                <p class="text-xs text-gray-500 mt-0.5">Design forms with drag-drop, collect submissions, map to CRM leads.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> New Form
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-hidden flex">
            <!-- Form List -->
            <div class="w-72 border-r border-gray-200 bg-white flex flex-col shrink-0">
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        <input v-model="search" placeholder="Search forms..." class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-indigo-400" />
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto py-2">
                    <div v-for="form in filteredForms" :key="form.id"
                        @click="selectForm(form)"
                        class="mx-2 my-1 px-4 py-3 rounded-xl cursor-pointer transition-all group"
                        :class="activeForm?.id === form.id ? 'bg-indigo-50 border border-indigo-200' : 'hover:bg-gray-50 border border-transparent'">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-bold text-sm" :class="activeForm?.id === form.id ? 'text-indigo-700' : 'text-gray-900'">{{ form.name }}</p>
                                <p class="text-sm text-gray-400 font-mono mt-0.5">{{ form.slug }}</p>
                            </div>
                            <span class="text-sm font-black px-1.5 py-0.5 rounded-full shrink-0 ml-2"
                                :class="form.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                                {{ form.is_active ? 'On' : 'Off' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-sm text-gray-400">
                            <span><i class="fas fa-th-list mr-1"></i>{{ fieldCount(form) }} fields</span>
                            <span><i class="fas fa-inbox mr-1"></i>{{ form.submissions_count || 0 }} submissions</span>
                        </div>
                    </div>
                    <div v-if="!filteredForms.length" class="px-6 py-10 text-center text-gray-400">
                        <i class="fas fa-wpforms text-3xl text-gray-200 mb-3 block"></i>
                        <p class="text-xs font-bold">No forms yet</p>
                    </div>
                </div>
            </div>

            <!-- Builder Canvas -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Empty state -->
                <div v-if="!activeForm" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                    <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-5"><i class="fas fa-wpforms text-3xl text-gray-300"></i></div>
                    <h3 class="font-black text-gray-600 text-lg mb-2">Select or Create a Form</h3>
                    <p class="text-sm text-gray-400 mb-5">Choose from the list or start a new form.</p>
                    <button @click="openCreate" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Create Form
                    </button>
                </div>

                <template v-else>
                    <!-- Field canvas -->
                    <div class="flex-1 flex flex-col overflow-hidden">
                        <!-- form actions bar -->
                        <div class="h-12 bg-white border-b border-gray-200 flex items-center px-6 gap-3 shrink-0">
                            <div class="flex items-center gap-2 flex-1">
                                <i class="fas fa-wpforms text-indigo-400"></i>
                                <input v-model="activeForm.name" class="text-sm font-black text-gray-900 bg-transparent border-none outline-none flex-1" />
                            </div>
                            <!-- Tab Switcher -->
                            <div class="flex bg-gray-100 p-1 rounded-lg gap-1 border border-gray-200 mr-4">
                                <button @click="viewMode = 'builder'" class="px-3 py-1 rounded-md text-sm font-black transition-all" :class="viewMode === 'builder' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-800'">BUILDER</button>
                                <button @click="loadSubmissions" class="px-3 py-1 rounded-md text-sm font-black transition-all" :class="viewMode === 'submissions' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-800'">SUBMISSIONS</button>
                            </div>
                            <div class="flex gap-2">
                                <button @click="toggleActive" class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 border transition-all"
                                    :class="activeForm.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="activeForm.is_active ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                    {{ activeForm.is_active ? 'Active' : 'Inactive' }}
                                </button>
                                <button @click="saveForm" :disabled="saving" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-black hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-1.5">
                                    <i v-if="saving" class="fas fa-spinner fa-spin text-sm"></i>
                                    <i v-else class="fas fa-save text-sm"></i>
                                    Save
                                </button>
                            </div>
                        </div>

                        <!-- Builder Content -->
                        <div v-show="viewMode === 'builder'" class="flex-1 overflow-y-auto p-6 space-y-3">
                            <!-- Header settings -->
                            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-3">
                                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400">Form Header</h3>
                                <div>
                                    <label class="field-label">Title</label>
                                    <input v-model="activeForm.title" class="field-input" placeholder="Contact Us" />
                                </div>
                                <div>
                                    <label class="field-label">Description</label>
                                    <textarea v-model="activeForm.description" rows="2" class="field-input resize-none" placeholder="We'll reply within 24 hours"></textarea>
                                </div>
                                <div>
                                    <label class="field-label">Submit Button Text</label>
                                    <input v-model="activeForm.submit_text" class="field-input" placeholder="Send Message" />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="field-label flex-1 mb-0">CRM Lead Creation</label>
                                    <label class="relative flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="activeForm.crm_sync" class="sr-only peer" />
                                        <div class="w-9 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-4 after:shadow-sm"></div>
                                        <span class="text-xs font-bold text-gray-600">Auto-create CRM lead</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Fields list with drag handles -->
                            <div class="space-y-2">
                                <div v-for="(field, idx) in activeFields" :key="field._key"
                                    class="bg-white rounded-xl border border-gray-200 flex items-start gap-3 p-4 shadow-sm group hover:border-indigo-200 transition-all"
                                    :class="editingField === field._key ? 'ring-2 ring-indigo-500/30 border-indigo-400' : ''"
                                    draggable="true"
                                    @dragstart="dragStart(idx)"
                                    @dragover.prevent="dragOver(idx)"
                                    @drop="drop">
                                    <!-- drag handle -->
                                    <div class="pt-1 text-gray-300 cursor-grab hover:text-gray-500 shrink-0">
                                        <i class="fas fa-grip-vertical text-sm"></i>
                                    </div>
                                    <!-- Field info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0"
                                                :style="{background: fieldTypeMap[field.type]?.bg||'#f3f4f6', color: fieldTypeMap[field.type]?.color||'#6b7280'}">
                                                <i :class="fieldTypeMap[field.type]?.icon||'fas fa-font'" class="text-sm"></i>
                                            </div>
                                            <span class="text-xs font-black text-gray-900 truncate">{{ field.label || '(no label)' }}</span>
                                            <span class="ml-1 text-sm font-bold px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded-full uppercase">{{ field.type }}</span>
                                            <span v-if="field.required" class="text-sm text-red-500 font-black">*required</span>
                                        </div>
                                        <!-- Expanded editor -->
                                        <div v-if="editingField === field._key" class="mt-3 grid grid-cols-2 gap-3">
                                            <div><label class="field-label">Label</label><input v-model="field.label" class="field-input" /></div>
                                            <div><label class="field-label">Name (key)</label><input v-model="field.name" class="field-input font-mono" /></div>
                                            <div><label class="field-label">Placeholder</label><input v-model="field.placeholder" class="field-input" /></div>
                                            <div>
                                                <label class="field-label">Type</label>
                                                <select v-model="field.type" class="field-input">
                                                    <option v-for="t in fieldTypes" :key="t" :value="t">{{ t }}</option>
                                                </select>
                                            </div>
                                            <div v-if="['select','radio','checkbox'].includes(field.type)" class="col-span-2">
                                                <label class="field-label">Options (one per line)</label>
                                                <textarea v-model="field._optionsRaw" rows="3" class="field-input font-mono resize-none" placeholder="Option 1&#10;Option 2&#10;Option 3" @input="syncOptions(field)"></textarea>
                                            </div>
                                            <div class="col-span-2 flex items-center gap-4">
                                                <label class="flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                                                    <input type="checkbox" v-model="field.required" class="accent-indigo-600" />Required
                                                </label>
                                                <label class="flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                                                    <input type="checkbox" v-model="field.half_width" class="accent-indigo-600" />Half width
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- actions -->
                                    <div class="flex gap-1 shrink-0">
                                        <button @click="editingField = editingField === field._key ? null : field._key"
                                            class="w-6 h-6 rounded-md flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                                            <i class="fas fa-pen text-sm"></i>
                                        </button>
                                        <button @click="removeField(idx)"
                                            class="w-6 h-6 rounded-md flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                            <i class="fas fa-times text-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Add field drop zone -->
                                <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center text-gray-400 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all cursor-pointer"
                                    @click="showFieldPicker = true">
                                    <i class="fas fa-plus text-sm mb-1"></i>
                                    <p class="text-xs font-bold">Add Field</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submissions Mode -->
                        <div v-show="viewMode === 'submissions'" class="flex-1 overflow-hidden p-6 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xs font-black uppercase text-gray-400">Recent Submissions ({{ activeForm.submissions_count || 0 }})</h3>
                                <button v-if="submissions.length" @click="loadSubmissions" :disabled="loadingSubmissions" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                                    <i class="fas fa-sync" :class="loadingSubmissions ? 'fa-spin' : ''"></i> Refresh
                                </button>
                            </div>
                            <div class="flex-1 overflow-y-auto space-y-3 pr-2">
                                <div v-for="sub in submissions" :key="sub.id" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 group">
                                    <div class="flex items-center justify-between mb-3 border-b border-gray-50 pb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-mono text-gray-400">#{{ sub.id }}</span>
                                            <span class="text-sm font-bold text-gray-600">{{ new Date(sub.created_at).toLocaleString() }}</span>
                                            <span v-if="sub.ip" class="text-sm px-1.5 py-0.5 bg-gray-50 text-gray-400 rounded-lg">{{ sub.ip }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span v-if="sub.crm_lead_id" class="text-sm font-black bg-emerald-50 text-emerald-600 border border-emerald-100 px-2 py-0.5 rounded-full">CRM LEAD</span>
                                            <button @click="deleteSubmission(sub.id)" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"><i class="fas fa-trash-alt text-sm"></i></button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                                        <div v-for="(val, key) in parseSubData(sub.data)" :key="key" class="text-base">
                                            <span class="font-black text-gray-400 uppercase tracking-tighter mr-2">{{ key }}:</span>
                                            <span class="text-gray-900 font-semibold">{{ val }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="!submissions.length" class="h-64 flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-100 rounded-2xl bg-gray-50/50">
                                    <i class="fas fa-inbox text-2xl mb-2 opacity-30"></i>
                                    <p class="text-xs font-bold">No submissions yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Border Settings -->
                    <div class="w-64 bg-white border-l border-gray-200 flex flex-col overflow-y-auto shrink-0">
                        <div v-show="viewMode === 'builder'" class="flex flex-col flex-1">
                            <div class="p-4 border-b border-gray-100">
                                <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-3">Add Field</h3>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <button v-for="ft in fieldTypeGroups" :key="ft.type" @click="addField(ft.type)"
                                        class="flex flex-col items-center gap-1 p-2 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all group">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" :style="{background: ft.bg, color: ft.color}">
                                            <i :class="ft.icon" class="text-xs"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-600 group-hover:text-indigo-700">{{ ft.label }}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 space-y-3 border-b border-gray-100">
                                <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">After Submit</h3>
                                <div>
                                    <label class="field-label">Success Message</label>
                                    <textarea v-model="activeForm.success_message" rows="2" class="field-input resize-none text-xs" placeholder="Thank you! We'll be in touch."></textarea>
                                </div>
                                <div>
                                    <label class="field-label">Redirect URL <span class="text-gray-300">(optional)</span></label>
                                    <input v-model="activeForm.redirect_url" class="field-input text-xs font-mono" placeholder="/thank-you" />
                                </div>
                                <div>
                                    <label class="field-label">Email Notify</label>
                                    <input v-model="activeForm.notify_email" class="field-input text-xs" placeholder="admin@example.com" />
                                </div>
                            </div>
                            <!-- CRM Mapping -->
                            <div class="p-4 space-y-3">
                                <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 flex items-center gap-2">
                                    <i class="fas fa-plug text-emerald-500"></i> CRM Integration
                                </h3>
                                <p class="text-sm text-gray-400 font-bold leading-tight">Map fields to auto-create leads in CRM.</p>
                                <div>
                                    <label class="field-label">Full Name Field</label>
                                    <select v-model="activeForm.crm_map_name" class="field-input text-xs">
                                        <option :value="null">None</option>
                                        <option v-for="f in activeFields" :key="f.name" :value="f.name">{{ f.label || f.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Email Field</label>
                                    <select v-model="activeForm.crm_map_email" class="field-input text-xs">
                                        <option :value="null">None</option>
                                        <option v-for="f in activeFields" :key="f.name" :value="f.name">{{ f.label || f.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Phone Field</label>
                                    <select v-model="activeForm.crm_map_phone" class="field-input text-xs">
                                        <option :value="null">None</option>
                                        <option v-for="f in activeFields" :key="f.name" :value="f.name">{{ f.label || f.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="p-4 mt-auto border-t border-gray-100">
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-sm font-black uppercase tracking-widest text-gray-500 group-hover:text-indigo-600">Form Status</span>
                                    <div @click="toggleActive" class="w-8 h-4 rounded-full relative transition-all" :class="activeForm.is_active ? 'bg-indigo-600' : 'bg-gray-200'">
                                        <div class="absolute top-0.5 w-3 h-3 bg-white rounded-full transition-all" :class="activeForm.is_active ? 'left-4.5' : 'left-0.5'"></div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <!-- Submission Stats Stats -->
                        <div v-show="viewMode === 'submissions'" class="p-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Submission Stats</h3>
                            <div class="space-y-4">
                                <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                                    <p class="text-sm font-black text-indigo-400 uppercase tracking-widest">Total Entries</p>
                                    <p class="text-2xl font-black text-indigo-700">{{ activeForm.submissions_count || 0 }}</p>
                                </div>
                                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                                    <p class="text-sm font-black text-emerald-400 uppercase tracking-widest">CRM Sync Rate</p>
                                    <p class="text-2xl font-black text-emerald-700">100%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        <!-- Create modal -->
        <div v-if="createModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" @click.self="createModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-[420px] p-7">
                <h3 class="text-base font-black text-gray-900 mb-5">New Form</h3>
                <div class="space-y-4">
                    <div><label class="field-label">Form Name</label><input v-model="newForm.name" class="field-input" placeholder="Contact Form" /></div>
                    <div><label class="field-label">Slug</label><input v-model="newForm.slug" class="field-input font-mono" placeholder="contact-form" /></div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="createModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="createForm" :disabled="creating" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 flex justify-center gap-2 items-center">
                        <i v-if="creating" class="fas fa-spinner fa-spin text-xs"></i>Create Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ forms: { type: Array, default: () => [] } });
const formList    = ref([...(props.forms || [])]);
const activeForm  = ref(null);
const activeFields = ref([]);
const editingField = ref(null);
const search      = ref('');
const saving      = ref(false);
const creating    = ref(false);
const createModal = ref(false);
const showFieldPicker = ref(false);
const newForm     = ref({ name: '', slug: '' });

// View modes
const viewMode = ref('builder'); // builder, submissions
const submissions = ref([]);
const loadingSubmissions = ref(false);

const loadSubmissions = async () => {
    viewMode.value = 'submissions';
    if (!activeForm.value) return;
    loadingSubmissions.value = true;
    try {
        const { data } = await axios.get(route('cms.forms.submissions', activeForm.value.id));
        submissions.value = data.data || data;
    } catch { alert('Failed to load submissions'); }
    finally { loadingSubmissions.value = false; }
};

const parseSubData = (data) => {
    try { return typeof data === 'string' ? JSON.parse(data) : data; }
    catch { return {}; }
};

const deleteSubmission = async (id) => {
    if (!confirm('Delete this submission?')) return;
    try {
        await axios.delete(route('cms.forms.submissions.destroy', id));
        submissions.value = submissions.value.filter(s => s.id !== id);
    } catch { alert('Delete failed'); }
};

// Drag state
let dragIdx = null;
const dragStart = (i) => dragIdx = i;
const dragOver  = (i) => {
    if (dragIdx === null || dragIdx === i) return;
    const moved = activeFields.value.splice(dragIdx, 1)[0];
    activeFields.value.splice(i, 0, moved);
    dragIdx = i;
};
const drop = () => dragIdx = null;

const filteredForms = computed(() =>
    !search.value ? formList.value : formList.value.filter(f => f.name?.toLowerCase().includes(search.value.toLowerCase()))
);

const fieldCount = (form) => { try { return JSON.parse(form.fields || '[]').length; } catch { return 0; } };

const fieldTypes = ['text','email','phone','number','textarea','select','radio','checkbox','date','time','file','url','hidden'];

const fieldTypeMap = {
    text:     { icon:'fas fa-font',        color:'#6366f1', bg:'#eef2ff' },
    email:    { icon:'fas fa-at',           color:'#ec4899', bg:'#fdf2f8' },
    phone:    { icon:'fas fa-phone',        color:'#10b981', bg:'#ecfdf5' },
    number:   { icon:'fas fa-hashtag',      color:'#f59e0b', bg:'#fffbeb' },
    textarea: { icon:'fas fa-align-left',   color:'#6b7280', bg:'#f9fafb' },
    select:   { icon:'fas fa-chevron-down', color:'#8b5cf6', bg:'#f5f3ff' },
    radio:    { icon:'fas fa-dot-circle',   color:'#0ea5e9', bg:'#f0f9ff' },
    checkbox: { icon:'fas fa-check-square', color:'#16a34a', bg:'#f0fdf4' },
    date:     { icon:'fas fa-calendar',     color:'#ef4444', bg:'#fef2f2' },
    file:     { icon:'fas fa-paperclip',    color:'#f97316', bg:'#fff7ed' },
};

const fieldTypeGroups = [
    { type:'text',     label:'Text',     icon:'fas fa-font',        color:'#6366f1', bg:'#eef2ff' },
    { type:'email',    label:'Email',    icon:'fas fa-at',           color:'#ec4899', bg:'#fdf2f8' },
    { type:'phone',    label:'Phone',    icon:'fas fa-phone',        color:'#10b981', bg:'#ecfdf5' },
    { type:'number',   label:'Number',   icon:'fas fa-hashtag',      color:'#f59e0b', bg:'#fffbeb' },
    { type:'textarea', label:'Textarea', icon:'fas fa-align-left',   color:'#6b7280', bg:'#f3f4f6' },
    { type:'select',   label:'Dropdown', icon:'fas fa-bars',         color:'#8b5cf6', bg:'#f5f3ff' },
    { type:'radio',    label:'Radio',    icon:'fas fa-dot-circle',   color:'#0ea5e9', bg:'#f0f9ff' },
    { type:'checkbox', label:'Checkbox', icon:'fas fa-check-square', color:'#16a34a', bg:'#f0fdf4' },
    { type:'date',     label:'Date',     icon:'fas fa-calendar',     color:'#ef4444', bg:'#fef2f2' },
    { type:'file',     label:'File',     icon:'fas fa-paperclip',    color:'#f97316', bg:'#fff7ed' },
];

const selectForm = (form) => {
    activeForm.value  = { ...form };
    
    // Unwrap settings
    const settings = (() => { try { return JSON.parse(form.settings || '{}'); } catch { return {}; } })();
    activeForm.value.success_message = settings.success_message || '';
    activeForm.value.redirect_url    = settings.redirect_url    || '';
    activeForm.value.notify_email    = settings.notify_email    || '';
    
    // CRM Map
    const crmMap = settings.crm_lead_map || {};
    activeForm.value.crm_map_name  = crmMap.name  || null;
    activeForm.value.crm_map_email = crmMap.email || null;
    activeForm.value.crm_map_phone = crmMap.phone || null;

    activeFields.value = (() => { try { return JSON.parse(form.fields || '[]'); } catch { return []; } })()
        .map((f, i) => ({ ...f, _key: f.name + i, _optionsRaw: (f.options||[]).join('\n') }));
    editingField.value = null;
    viewMode.value = 'builder';
};

let _keyCounter = 0;
const addField = (type) => {
    _keyCounter++;
    activeFields.value.push({ _key: type + _keyCounter, type, label: '', name: type + _keyCounter, placeholder: '', required: false, half_width: false, options: [], _optionsRaw: '' });
};

const removeField = (idx) => activeFields.value.splice(idx, 1);

const syncOptions = (field) => {
    field.options = field._optionsRaw.split('\n').map(s => s.trim()).filter(Boolean);
};

const toggleActive = () => { activeForm.value.is_active = !activeForm.value.is_active; };

const saveForm = async () => {
    saving.value = true;
    
    // Wrap settings
    const settings = {
        success_message: activeForm.value.success_message,
        redirect_url:    activeForm.value.redirect_url,
        notify_email:    activeForm.value.notify_email,
        crm_lead_map: {
            name:  activeForm.value.crm_map_name,
            email: activeForm.value.crm_map_email,
            phone: activeForm.value.crm_map_phone,
        }
    };

    const payload = { 
        ...activeForm.value, 
        fields: JSON.stringify(activeFields.value.map(f => { const { _key, _optionsRaw, ...clean } = f; return clean; })),
        settings: JSON.stringify(settings)
    };

    try {
        const { data } = await axios.put(route('cms.forms.update', activeForm.value.id), payload);
        const idx = formList.value.findIndex(f => f.id === data.id);
        if (idx !== -1) formList.value[idx] = data;
        
        // Re-select to refresh refs
        selectForm(data);
    } catch (e) { alert('Save failed'); }
    finally { saving.value = false; }
};

const openCreate = () => { newForm.value = { name: '', slug: '' }; createModal.value = true; };

const createForm = async () => {
    if (!newForm.value.name) return;
    creating.value = true;
    try {
        const { data } = await axios.post(route('cms.forms.store'), { ...newForm.value, fields: '[]', settings: '{}' });
        formList.value.unshift(data);
        createModal.value = false;
        selectForm(data);
    } catch (e) { alert('Create failed'); }
    finally { creating.value = false; }
};
</script>

<style scoped>
.field-label { display:block; font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.4rem 0.65rem; font-size:0.8rem; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>
