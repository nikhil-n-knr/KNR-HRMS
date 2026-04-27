<script setup>
import { ref } from 'vue';
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';
import { 
    InformationCircleIcon, 
    FolderIcon, 
    PlusIcon, 
    UserCircleIcon, 
    MapPinIcon, 
    ArchiveBoxIcon, 
    CheckBadgeIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    SparklesIcon,
    BoltIcon,
    ArrowPathIcon,
    DocumentDuplicateIcon,
    UserGroupIcon,
    IdentificationIcon,
    FingerPrintIcon,
    ArrowRightIcon,
    AdjustmentsHorizontalIcon,
    InboxIcon,
    DocumentTextIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowUturnLeftIcon,
    ArrowRightStartOnRectangleIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    documents: Object,
    locations: Array,
    users: Array
});

const columns = [
    { key: 'document_type', label: 'Classification', sortable: true },
    { key: 'user', label: 'Ownership', sortable: true },
    { key: 'location', label: 'Storage Node', sortable: true },
    { key: 'container_ref', label: 'Reference Code', sortable: true },
    { key: 'status', label: 'Vital Status', sortable: true },
    { key: 'actions', label: '', sortable: false, align: 'right' }
];

const showModal = ref(false);
const isOutsider = ref(false);
const showEditModal = ref(false);
const activeRecord = ref(null);

const form = useForm({
    document_type: '',
    user_id: '',
    outsider_name: '',
    location_id: '',
    container_ref: ''
});

const editForm = useForm({
    id: null,
    document_type: '',
    user_id: '',
    outsider_name: '',
    location_id: '',
    container_ref: '',
    notes: ''
});

const submit = () => {
    if (isOutsider.value) {
        form.user_id = null;
    } else {
        form.outsider_name = null;
    }

    form.post(route('admin.physical-documents.check-in'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            isOutsider.value = false;
        }
    });
};

const openEdit = (row) => {
    activeRecord.value = row;
    editForm.id = row.id;
    editForm.document_type = row.document_type || '';
    editForm.user_id = row.user?.id || '';
    editForm.outsider_name = row.outsider_name || '';
    editForm.location_id = row.location?.id || '';
    editForm.container_ref = row.container_ref || '';
    editForm.notes = row.notes || '';
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.physical-documents.update', editForm.id), {
        onSuccess: () => {
            showEditModal.value = false;
            activeRecord.value = null;
            editForm.reset();
        }
    });
};

const deleteRecord = (row) => {
    if (confirm(`Delete document '${row.document_type}'? This action cannot be undone.`)) {
        router.delete(route('admin.physical-documents.destroy', row.id));
    }
};

const checkoutRecord = (row) => {
    router.post(route('admin.physical-documents.checkout', row.id));
};

const returnRecord = (row) => {
    router.post(route('admin.physical-documents.return', row.id));
};

const markMissing = (row) => {
    router.post(route('admin.physical-documents.missing', row.id));
};
</script>

<template>
    <Head title="Physical Documents" />
    
    <div class="h-full flex flex-col font-outfit -m-8 p-12 bg-slate-50 min-h-screen relative overflow-hidden animate-in fade-in duration-1000">
        <!-- AI Grid Background -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:40px_40px] pointer-events-none"></div>
        <div class="absolute -right-32 -top-32 w-128 h-128 bg-sky-500/5 rounded-full blur-[140px] pointer-events-none"></div>

        <!-- Header -->
        <header class="bg-white rounded-3xl border border-slate-200 p-10 shadow-sm mb-12 relative overflow-hidden group">
            <div class="absolute -right-24 -top-24 w-96 h-96 bg-sky-50 rounded-full blur-[100px]"></div>
            
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-12 relative z-10">
                <div class="flex items-center gap-10">
                    <div class="w-20 h-20 bg-sky-50 border border-sky-100 rounded-2xl flex items-center justify-center text-sky-600 shadow-sm shrink-0">
                        <FolderIcon class="w-10 h-10" />
                    </div>
                    <div class="text-left">
                        <div class="flex items-center gap-6">
                            <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tight leading-none">Physical Documents</h1>
                            <div class="px-4 py-2 bg-sky-50 border border-sky-100 rounded-xl flex items-center gap-3">
                                <div class="w-2.5 h-2.5 bg-sky-500 rounded-full animate-pulse"></div>
                                <span class="text-[9px] font-black text-sky-700 uppercase tracking-widest">In Sync</span>
                            </div>
                        </div>
                        <p class="text-xs font-semibold text-slate-500 mt-3">Custody tracking and storage visibility</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <div v-for="stat in [
                        { label: 'Total Files', value: documents.total || 0, color: 'text-slate-900', bg: 'bg-slate-50', border: 'border-slate-200' },
                        { label: 'Safekeeping', value: documents.data.filter(d => d.status === 'In_Custody').length, color: 'text-emerald-700', bg: 'bg-emerald-50', border: 'border-emerald-100' },
                        { label: 'Borrowed', value: documents.data.filter(d => d.status === 'With_Employee').length, color: 'text-amber-700', bg: 'bg-amber-50', border: 'border-amber-100' },
                        { label: 'Missing', value: documents.data.filter(d => d.status === 'Missing').length, color: 'text-rose-700', bg: 'bg-rose-50', border: 'border-rose-100' }
                    ]" :key="stat.label" class="rounded-2xl px-6 py-4 min-w-[160px] border transition-all" :class="[stat.bg, stat.border]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{{ stat.label }}</p>
                        <p class="text-2xl font-black tracking-tight leading-none" :class="stat.color">{{ stat.value }}</p>
                    </div>
                    <div class="w-px h-16 bg-slate-100 mx-2 hidden xl:block"></div>
                    <button @click="showModal = true" class="h-12 px-6 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-3 active:scale-95">
                        <PlusIcon class="w-4 h-4" />
                        <span>Add Document</span>
                    </button>
                    <Link :href="route('admin.physical-documents.config')" class="h-12 px-6 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:border-indigo-200 transition-all active:scale-95 shadow-sm">
                        <AdjustmentsHorizontalIcon class="w-4 h-4 mr-2" />
                        Configure
                    </Link>
                </div>
            </div>
        </header>

        <!-- Lifecycle and Related Links -->
        <section class="bg-white rounded-3xl border border-slate-200 p-6 mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="text-left">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Document Lifecycle</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">Checked In</span>
                    <span class="text-slate-300">-></span>
                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">In Custody</span>
                    <span class="text-slate-300">-></span>
                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">With Employee</span>
                    <span class="text-slate-300">-></span>
                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Returned</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">All Assets</Link>
                <Link :href="route('admin.assets.audit.run')" class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Blind Audit</Link>
                <Link :href="route('admin.inventory.dashboard', { view: 'list' })" class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600">Store Items</Link>
                <Link :href="route('admin.physical-documents.config')" class="h-10 px-4 bg-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-widest text-white">Storage Config</Link>
            </div>
        </section>

        <!-- Documents Table -->
        <section class="flex-1 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative z-10 transition-all duration-700">
            <BaseDataTable
                :rows="documents.data"
                :columns="columns"
                search-placeholder="Search documents by type, owner, or reference"
                class="flex-1"
            >
                <template #cell-document_type="{ row }">
                    <div class="flex items-center gap-6 py-2">
                        <div class="w-14 h-14 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 group-hover:bg-sky-50 group-hover:text-sky-600 transition-all duration-500 shadow-inner shrink-0">
                            <DocumentTextIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <div class="text-base font-black text-slate-950 uppercase tracking-tighter group-hover:text-sky-600 transition-colors leading-none">{{ row.document_type }}</div>
                            <div class="flex items-center gap-3 mt-3">
                                 <FingerPrintIcon class="w-3.5 h-3.5 text-slate-300" />
                                 <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest font-mono">{{ String(row.id).padStart(6, '0') }}</div>
                            </div>
                        </div>
                    </div>
                </template>

                <template #cell-user="{ row }">
                    <div class="flex items-center gap-4 group/user">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 group-hover:bg-sky-50 group-hover:text-sky-600 transition-all shrink-0">
                            <IdentificationIcon class="w-5 h-5" />
                        </div>
                        <span class="text-[11px] font-black text-slate-600 uppercase tracking-widest truncate max-w-[200px]">{{ row.user?.name || row.outsider_name || 'UNASSIGNED' }}</span>
                    </div>
                </template>

                <template #cell-location="{ row }">
                    <div class="flex items-center gap-4 group/loc">
                         <div class="w-10 h-10 bg-sky-50 rounded-xl flex items-center justify-center text-sky-500 group-hover:bg-sky-100 group-hover:text-sky-700 transition-all shrink-0">
                             <MapPinIcon class="w-5 h-5" />
                         </div>
                         <div>
                             <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest leading-none">{{ row.location?.name || 'NOT SET' }}</span>
                             <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1">STORAGE LOCATION</p>
                         </div>
                    </div>
                </template>

                <template #cell-container_ref="{ row }">
                    <div class="flex items-center gap-3">
                        <InboxIcon class="w-4 h-4 text-slate-300" />
                        <span class="px-5 py-2 bg-slate-100 text-slate-700 rounded-xl text-[10px] font-black font-mono tracking-widest uppercase shadow-sm border border-slate-200">
                            {{ row.container_ref || 'NO REF' }}
                        </span>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <div class="flex items-center gap-5 px-4">
                        <div class="w-3 h-3 rounded-full relative" :class="{
                            'bg-emerald-500 shadow-[0_0_12px_#10b981]': row.status === 'In_Custody',
                            'bg-amber-500 shadow-[0_0_12px_#f59e0b]': row.status === 'With_Employee',
                            'bg-rose-500 shadow-[0_0_12px_#f43f5e]': row.status === 'Missing'
                        }">
                             <div class="absolute inset-0 rounded-full animate-ping opacity-20" :class="{
                                'bg-emerald-500': row.status === 'In_Custody',
                                'bg-amber-500': row.status === 'With_Employee',
                                'bg-rose-500': row.status === 'Missing'
                             }"></div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em]" :class="{
                            'text-emerald-700': row.status === 'In_Custody',
                            'text-amber-700': row.status === 'With_Employee',
                            'text-rose-700': row.status === 'Missing'
                        }">
                            {{ row.status === 'In_Custody' ? 'IN CUSTODY' : (row.status === 'With_Employee' ? 'WITH EMPLOYEE' : 'MISSING') }}
                        </span>
                    </div>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex justify-end gap-2 opacity-100 transition-all">
                        <button @click="openEdit(row)" class="h-10 px-4 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-2">
                            <PencilSquareIcon class="w-4 h-4" />
                            Edit
                        </button>

                        <button v-if="row.status === 'In_Custody'" @click="checkoutRecord(row)" class="h-10 px-4 bg-amber-50 text-amber-700 border border-amber-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-amber-100 transition-all flex items-center gap-2">
                            <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
                            Checkout
                        </button>

                        <button v-if="row.status === 'With_Employee'" @click="returnRecord(row)" class="h-10 px-4 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-emerald-100 transition-all flex items-center gap-2">
                            <ArrowUturnLeftIcon class="w-4 h-4" />
                            Return
                        </button>

                        <button v-if="row.status !== 'Missing'" @click="markMissing(row)" class="h-10 px-4 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all">
                            Mark Missing
                        </button>

                        <button @click="deleteRecord(row)" class="h-10 px-4 bg-indigo-600 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-600 transition-all flex items-center gap-2">
                            <TrashIcon class="w-4 h-4" />
                            Delete
                        </button>
                    </div>
                </template>
            </BaseDataTable>
        </section>
    </div>

    <!-- Add Document Modal -->
    <PremiumModal :show="showModal" @close="showModal = false" title="Add Physical Document" subtitle="Register a new hard-copy document">
        <form @submit.prevent="submit" class="p-8 space-y-10 text-left">
             <div class="space-y-4">
                <InputLabel value="Document Type" />
                <TextInput v-model="form.document_type" placeholder="E.g. Employment Bond, NDA" required />
                <InputError :message="form.errors.document_type" />
             </div>

             <div class="space-y-6">
                <div class="flex items-center justify-between px-2">
                    <InputLabel value="Current Holder" />
                    <button type="button" @click="isOutsider = !isOutsider" class="text-[10px] font-black text-sky-600 uppercase tracking-widest hover:text-slate-950 transition-colors">
                        {{ isOutsider ? 'ASSIGN INTERNAL USER' : 'ASSIGN EXTERNAL HOLDER' }}
                    </button>
                </div>

                <div v-if="!isOutsider">
                    <BaseSelect v-model="form.user_id">
                         <option value="" disabled>Select internal user</option>
                         <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name.toUpperCase() }}</option>
                    </BaseSelect>
                </div>
                <div v-else>
                    <TextInput v-model="form.outsider_name" placeholder="External holder name" required />
                </div>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <InputLabel value="Storage Location" />
                    <BaseSelect v-model="form.location_id">
                        <option value="" disabled>Select location</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name.toUpperCase() }}</option>
                    </BaseSelect>
                </div>
                <div class="space-y-4">
                    <InputLabel value="Container Reference" />
                    <TextInput v-model="form.container_ref" placeholder="E.g. BOX-404" required />
                </div>
             </div>

             <div class="flex items-center justify-between pt-12 border-t border-slate-100 mt-10">
                <button @click="showModal = false" type="button" class="text-[11px] font-black uppercase tracking-[0.4em] text-slate-300 hover:text-rose-500 transition-all">Cancel</button>
                <button type="submit" :disabled="form.processing" class="h-14 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-3 active:scale-95 disabled:opacity-50 group">
                    <ArrowPathIcon v-if="form.processing" class="w-5 h-5 animate-spin" />
                    <CheckBadgeIcon v-else class="w-5 h-5 text-white" />
                    <span>{{ form.processing ? 'Saving...' : 'Save Document' }}</span>
                </button>
             </div>
        </form>
    </PremiumModal>

    <PremiumModal :show="showEditModal" @close="showEditModal = false" title="Edit Physical Document" subtitle="Update metadata and ownership details">
        <form @submit.prevent="submitEdit" class="p-8 space-y-8 text-left">
            <div class="space-y-3">
                <InputLabel value="Document Type" />
                <TextInput v-model="editForm.document_type" required />
                <InputError :message="editForm.errors.document_type" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <InputLabel value="Internal User" />
                    <BaseSelect v-model="editForm.user_id">
                        <option value="">UNASSIGNED</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </BaseSelect>
                    <InputError :message="editForm.errors.user_id" />
                </div>

                <div class="space-y-3">
                    <InputLabel value="Outsider Name" />
                    <TextInput v-model="editForm.outsider_name" :disabled="!!editForm.user_id" placeholder="Optional if user is assigned" />
                    <InputError :message="editForm.errors.outsider_name" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <InputLabel value="Location" />
                    <BaseSelect v-model="editForm.location_id">
                        <option value="">SELECT LOCATION</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </BaseSelect>
                    <InputError :message="editForm.errors.location_id" />
                </div>

                <div class="space-y-3">
                    <InputLabel value="Container Reference" />
                    <TextInput v-model="editForm.container_ref" />
                    <InputError :message="editForm.errors.container_ref" />
                </div>
            </div>

            <div class="space-y-3">
                <InputLabel value="Notes" />
                <textarea v-model="editForm.notes" rows="3" class="w-full rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-sky-500"></textarea>
                <InputError :message="editForm.errors.notes" />
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                <button type="button" @click="showEditModal = false" class="text-[11px] font-bold uppercase tracking-widest text-slate-400 hover:text-rose-500">Cancel</button>
                <button type="submit" :disabled="editForm.processing" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all">
                    {{ editForm.processing ? 'Updating...' : 'Update Document' }}
                </button>
            </div>
        </form>
    </PremiumModal>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.shadow-3xl {
    box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.15);
}
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
