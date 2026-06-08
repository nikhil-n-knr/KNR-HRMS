<script setup>
import { ref, watch } from "vue";
import { useForm, Head, Link, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import BaseDataTable from "@/Components/BaseDataTable.vue";
import PremiumModal from "@/Components/PremiumModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import BaseSelect from "@/Components/BaseSelect.vue";
import InputError from "@/Components/InputError.vue";
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";
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
    ArrowRightStartOnRectangleIcon,
} from "@heroicons/vue/24/solid";
import { useToastStore } from "@/stores/toast";

defineOptions({ layout: MainLayout });

const props = defineProps({
    documents: Object,
    locations: Array,
    users: Array,
});

const columns = [
    { key: "document_type", label: "Classification", sortable: true, align: "center" },
    { key: "user", label: "Ownership", sortable: true, align: "center" },
    { key: "location", label: "Storage Node", sortable: true, align: "center" },
    { key: "container_ref", label: "Reference Code", sortable: true, align: "center" },
    { key: "status", label: "Vital Status", sortable: true, align: "center" },
    { key: "actions", label: "Actions", sortable: false, align: "right" },
];

const showModal = ref(false);
const isOutsider = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const activeRecord = ref(null);
const recordToDelete = ref(null);
const toast = useToastStore();
const checkInSuccessPending = ref(false);

const form = useForm({
    document_type: "",
    user_id: "",
    outsider_name: "",
    location_id: "",
    container_ref: "",
    notes: "",
});

const editForm = useForm({
    id: null,
    document_type: "",
    user_id: "",
    outsider_name: "",
    location_id: "",
    container_ref: "",
    notes: "",
});

const closeCreateModal = () => {
    showModal.value = false;
    form.reset();
    isOutsider.value = false;
};

const handleCheckInSuccess = () => {
    if (!checkInSuccessPending.value) return;

    checkInSuccessPending.value = false;
    toast.success("Document Checked In");
    closeCreateModal();
};

watch(
    () => form.wasSuccessful,
    (wasSuccessful) => {
        if (wasSuccessful) {
            handleCheckInSuccess();
        }
    },
);

const submit = () => {
    if (isOutsider.value) {
        form.user_id = null;
    } else {
        form.outsider_name = null;
    }

    checkInSuccessPending.value = true;
    form.post('/admin/physical-documents/check-in', {
        onSuccess: () => {
            handleCheckInSuccess();
        },
        onError: () => {
            checkInSuccessPending.value = false;
        },
    });
};

const openEdit = (row) => {
    activeRecord.value = row;
    editForm.id = row.id;
    editForm.document_type = row.document_type || "";
    editForm.user_id = row.user?.id || "";
    editForm.outsider_name = row.outsider_name || "";
    editForm.location_id = row.location?.id || "";
    editForm.container_ref = row.container_ref || "";
    editForm.notes = row.notes || "";
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(`/admin/physical-document/${editForm.id}/update`, {
        onSuccess: () => {
            showEditModal.value = false;
            activeRecord.value = null;
            editForm.reset();
        },
        onError: () => {
            toast.error("Failed to update document");
        },
    });
};

const openDeleteConfirm = (row) => {
    recordToDelete.value = row;
    showDeleteModal.value = true;
};

const closeDeleteConfirm = () => {
    showDeleteModal.value = false;
    recordToDelete.value = null;
};

const deleteRecord = () => {
    if (!recordToDelete.value) return;

    const deleteId = recordToDelete.value.id;
    closeDeleteConfirm();

    router.delete(`/admin/physical-document/${deleteId}`, {
        onSuccess: () => {
            toast.success("Document Deleted");
        },
        onError: () => {
            toast.error("Failed to delete document");
        },
    });
};

const checkoutRecord = (row) => {
    router.post(`/admin/physical-document/${row.id}/checkout`, {
        onSuccess: () => {
            toast.success("Document Checked Out");
        },
        onError: () => {
            toast.error("Failed to checkout document");
        },
    });
};

const returnRecord = (row) => {
    router.post(`/admin/physical-document/${row.id}/return`, {
        onSuccess: () => {
            toast.success("Document Returned to Custody");
        },
        onError: () => {
            toast.error("Failed to return document");
        },
    });
};

const foundRecord = (row) => {
    router.post(`/admin/physical-document/${row.id}/found`, {
        onSuccess: () => {
            toast.success("Document Marked as Found");
        },
        onError: () => {
            toast.error("Failed to mark document as found");
        },
    });
};

const markMissing = (row) => {
    router.post(`/admin/physical-document/${row.id}/missing`, {
        onSuccess: () => {
            toast.success("Document Marked Missing");
        },
        onError: () => {
            toast.error("Failed to mark document as missing");
        },
    });
};

</script>

<template>

    <Head title="Physical Documents" />

    <GradientHeroHeader kicker="" title="Physical Documents"
        subtitle="Custody tracking and storage visibility.">
    </GradientHeroHeader>

    <div class="p-6">
        <!-- AI Grid Background -->

        <div class="flex flex-wrap items-center mb-5 gap-6 justify-center z-10 relative">
            <div v-for="stat in [
                {
                    label: 'Total Files',
                    value: documents.total || 0,
                    color: 'text-slate-900',
                    bg: 'bg-slate-80',
                    border: 'border-slate-200',
                },
                {
                    label: 'Safekeeping',
                    value: documents.data.filter((d) => d.status === 'In_Custody')
                        .length,
                    color: 'text-emerald-700',
                    bg: 'bg-emerald-50',
                    border: 'border-emerald-100',
                },
                {
                    label: 'Borrowed',
                    value: documents.data.filter(
                        (d) => d.status === 'With_Employee',
                    ).length,
                    color: 'text-amber-700',
                    bg: 'bg-amber-50',
                    border: 'border-amber-100',
                },
                {
                    label: 'Missing',
                    value: documents.data.filter((d) => d.status === 'Missing')
                        .length,
                    color: 'text-rose-700',
                    bg: 'bg-rose-50',
                    border: 'border-rose-100',
                },
            ]" :key="stat.label" class="rounded-2xl px-6 py-4 min-w-[160px] border transition-all"
                :class="[stat.bg, stat.border]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">
                    {{ stat.label }}
                </p>
                <p class="text-2xl font-black tracking-tight leading-none" :class="stat.color">
                    {{ stat.value }}
                </p>
            </div>
        </div>

        <!-- Lifecycle and Related Links -->
        <section class="relative bg-white rounded-3xl border border-slate-200 p-4 mb-8">
            <button @click="showModal = true"
                class="float-right h-10 px-2 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-1 cursor-pointer active:scale-95">
                <PlusIcon class="w-4 h-4" />
                <span>Add Document</span>
            </button>
            <Link :href="route('admin.physical-documents.config')"
                class="float-right h-10 px-2 text-black border border-slate-200 rounded-xl text-[12px] font-bold uppercase tracking-widest shadow-sm transition-all flex items-center gap-1 cursor-pointer shadow-sm tracking-widest active:scale-95 mr-2">
                <AdjustmentsHorizontalIcon class="w-4 h-4 mr-2" />
                Configure
            </Link>
            <div class="text-left">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    Document Lifecycle
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">Checked
                        In</span>
                    <span class="text-slate-300">-></span>
                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">In
                        Custody</span>
                    <span class="text-slate-300">-></span>
                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">With
                        Employee</span>
                    <span class="text-slate-300">-></span>
                    <span
                        class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Returned</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 justify-end mt-5">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })"
                    class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center">
                    All Assets</Link>
                <Link :href="route('admin.assets.audit.run')"
                    class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center">
                    Blind Audit</Link>
                <Link :href="route('admin.inventory.dashboard', { view: 'list' })"
                    class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center">
                    Store Items</Link>
                <Link :href="route('admin.physical-documents.config')"
                    class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center">
                    Storage Config</Link>
            </div>
        </section>

        <!-- Documents Table -->
        <section
            class="flex-1 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative z-10 transition-all duration-700">
            <BaseDataTable :rows="documents.data" :columns="columns"
                search-placeholder="Search documents by type, owner, or reference" class="flex-1">
                <template #cell-document_type="{ item: row }">
                    <div class="flex items-center gap-3 py-2">
                        <div
                            class="w-6 h-6 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 group-hover:bg-sky-50 group-hover:text-sky-600 transition-all duration-500 shadow-inner shrink-0">
                            <DocumentTextIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <div
                                class="text-sm font-black text-slate-900 uppercase tracking-tighter group-hover:text-sky-600 transition-colors leading-none">
                                {{ row.document_type }}
                            </div>
                            <div class="flex items-center gap-3 mt-1">
                                <FingerPrintIcon class="w-3.5 h-3.5 text-slate-300" />
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest font-mono">
                                    {{ String(row.id).padStart(6, "0") }}
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template #cell-user="{ item: row }">
                    <div class="flex items-center gap-3 group/user">
                        <div
                            class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 group-hover:bg-sky-50 group-hover:text-sky-600 transition-all shrink-0">
                            <IdentificationIcon class="w-5 h-5" />
                        </div>
                        <span
                            class="text-[11px] font-black text-slate-600 uppercase tracking-widest truncate max-w-[200px]">{{
                                row.user?.name || row.outsider_name || "UNASSIGNED" }}</span>
                    </div>
                </template>

                <template #cell-location="{ item: row }">
                    <div class="flex items-center gap-3 group/loc">
                        <div
                            class="w-10 h-10 bg-sky-50 rounded-xl flex items-center justify-center text-sky-500 group-hover:bg-sky-100 group-hover:text-sky-700 transition-all shrink-0">
                            <MapPinIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <span
                                class="text-[11px] font-black text-slate-900 uppercase tracking-widest leading-none">{{
                                    row.location?.name || "NOT SET" }}</span>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1">
                                STORAGE LOCATION
                            </p>
                        </div>
                    </div>
                </template>

                <template #cell-container_ref="{ item: row }">
                    <div class="flex items-center gap-3">
                        <InboxIcon class="w-4 h-4 text-slate-300" />
                        <span
                            class="px-5 py-2 bg-slate-100 text-slate-700 rounded-xl text-[10px] font-black font-mono tracking-widest uppercase shadow-sm border border-slate-200">
                            {{ row.container_ref || "NO REF" }}
                        </span>
                    </div>
                </template>

                <template #cell-status="{ item: row }">
                    <div class="flex items-center gap-3 px-1">
                        <div class="w-3 h-3 rounded-full relative" :class="{
                            'bg-emerald-500 shadow-[0_0_12px_#10b981]':
                                row.status === 'In_Custody',
                            'bg-amber-500 shadow-[0_0_12px_#f59e0b]':
                                row.status === 'With_Employee',
                            'bg-rose-500 shadow-[0_0_12px_#f43f5e]':
                                row.status === 'Missing',
                        }">
                            <div class="absolute inset-0 rounded-full animate-ping opacity-20" :class="{
                                'bg-emerald-500': row.status === 'In_Custody',
                                'bg-amber-500': row.status === 'With_Employee',
                                'bg-rose-500': row.status === 'Missing',
                            }"></div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em]" :class="{
                            'text-emerald-700': row.status === 'In_Custody',
                            'text-amber-700': row.status === 'With_Employee',
                            'text-rose-700': row.status === 'Missing',
                        }">
                            {{
                                row.status === "In_Custody"
                                    ? "IN CUSTODY"
                                    : row.status === "With_Employee"
                                        ? "WITH EMPLOYEE"
                                        : "MISSING"
                            }}
                        </span>
                    </div>
                </template>

                <template #cell-actions="{ item: row }">
                    <div class="flex justify-end gap-4 opacity-100 transition-all">
                        <button @click="openEdit(row)"
                            class="h-10 px-3 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-1 cursor-pointer">
                            <PencilSquareIcon class="w-4 h-4" />
                            Edit
                        </button>

                        <button v-if="row.status === 'In_Custody'" @click="checkoutRecord(row)"
                            class="h-10 px-3 bg-amber-50 text-amber-700 border border-amber-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-amber-100 transition-all flex items-center gap-1 cursor-pointer">
                            <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
                            Checkout
                        </button>

                        <button v-if="row.status === 'With_Employee'" @click="returnRecord(row)"
                            class="h-10 px-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-emerald-100 transition-all flex items-center gap-1 cursor-pointer">
                            <ArrowUturnLeftIcon class="w-4 h-4" />
                            Return
                        </button>

                        <button v-if="row.status === 'Missing'" @click="foundRecord(row)"
                            class="h-10 px-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-emerald-100 transition-all flex items-center gap-1 cursor-pointer">
                            <ArrowUturnLeftIcon class="w-4 h-4" />
                            Found
                        </button>

                        <button v-if="row.status !== 'Missing'" @click="markMissing(row)"
                            class="h-10 px-3 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all flex items-center gap-1 cursor-pointer">
                            Mark Missing
                        </button>

                        <button @click="openDeleteConfirm(row)"
                            class="h-10 px-3 bg-rose-500 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-600 transition-all flex items-center gap-1 cursor-pointer ">
                            <TrashIcon class="w-4 h-4" />
                            Delete
                        </button>
                    </div>
                </template>
            </BaseDataTable>
        </section>
    </div>

    <!-- Add Document Modal -->
    <PremiumModal :show="showModal" @close="showModal = false" title="Add Physical Document"
        subtitle="Register a new hard-copy document">
        <form @submit.prevent="submit"
            class="relative p-6 space-y-6 text-left bg-white/95 backdrop-blur-xl border border-slate-200/70 rounded-[1.75rem] shadow-[0_12px_40px_-12px_rgba(15,23,42,0.12)] overflow-hidden">

            <!-- Glow -->
            <div
                class="absolute -top-20 -right-20 w-52 h-52 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <!-- Header -->
            <div class="relative flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 mb-3 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-[8px] font-black uppercase tracking-[0.25em]">
                        Document Registry
                    </div>

                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-950 uppercase leading-none">
                            Create Document
                        </h2>

                        <p class="text-[10px] text-slate-400 font-semibold mt-1">
                            Register and assign document custody details.
                        </p>
                    </div>
                </div>

                <div
                    class="hidden md:flex w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg items-center justify-center shrink-0">
                    <DocumentDuplicateIcon class="w-5 h-5 text-white" />
                </div>
            </div>

            <!-- Document Type -->
            <div class="space-y-2.5">
                <div class="flex items-center gap-2">
                    <DocumentTextIcon class="w-4 h-4 text-slate-500 shrink-0" />
                    <InputLabel class="font-black text-slate-900" value="Document Type" />
                </div>
                <div class="relative group">
                    <TextInput v-model="form.document_type" placeholder="E.g. Employment Bond, NDA" required
                        class="h-11 pl-4 pr-10 rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none transition-all text-[11px] font-bold tracking-wide" />


                </div>

                <InputError :message="form.errors.document_type" />
            </div>

            <!-- Holder -->
            <div class="flex justify-end mb-3">
                <button type="button" @click="isOutsider = !isOutsider"
                    class="shrink-0 text-[10px] font-black text-indigo-600 uppercase tracking-[0.1em] hover:border-indigo-300 hover:bg-indigo-50 transition-all active:scale-95 cursor-pointer whitespace-nowrap">
                    {{ isOutsider ? "Internal User" : "External Holder" }}
                </button>
            </div>
            <div
                class="relative p-4 rounded-[1.5rem] border border-slate-200 bg-gradient-to-br from-slate-50 to-white shadow-sm space-y-4">


                <div class="flex items-center justify-between gap-3">
                    <div>
                        <InputLabel value="Current Holder" />

                        <p class="text-[9px] text-slate-400 font-semibold mt-1">
                            Assign internal staff or external custody.
                        </p>
                    </div>

                </div>

                <Transition name="fade" mode="out-in">
                    <div v-if="!isOutsider" key="internal">
                        <BaseSelect v-model="form.user_id"
                            class="h-11 rounded-xl border-slate-200 bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:outline-none text-[11px] font-bold tracking-wide">
                            <option value="" disabled>Select internal user</option>

                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name.toUpperCase() }}
                            </option>
                        </BaseSelect>
                    </div>

                    <div v-else key="external">
                        <TextInput v-model="form.outsider_name" placeholder="External holder name" required
                            class="h-11 rounded-xl border-slate-200 bg-white focus:ring-4 px-3 focus:ring-indigo-500/10 focus:border-indigo-500 focus:outline-none text-[11px] font-bold tracking-wide" />
                    </div>
                </Transition>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

                <!-- Storage -->
                <div
                    class="group relative p-4 rounded-[1.5rem] border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300">

                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                            <MapPinIcon class="w-5 h-5" />
                        </div>

                        <div>
                            <InputLabel value="Storage Location" />

                            <p class="text-[9px] text-slate-400 font-semibold mt-1">
                                Physical archive placement
                            </p>
                        </div>
                    </div>

                    <BaseSelect v-model="form.location_id"
                        class="h-11 rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:outline-none text-[11px] font-bold tracking-wide">
                        <option value="" disabled>Select location</option>

                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                            {{ loc.name.toUpperCase() }}
                        </option>
                    </BaseSelect>
                </div>

                <!-- Container -->
                <div
                    class="group relative p-4 rounded-[1.5rem] border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300">

                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                            <ArchiveBoxIcon class="w-5 h-5" />
                        </div>

                        <div>
                            <InputLabel value="Container Reference" />

                            <p class="text-[9px] text-slate-400 font-semibold mt-1">
                                Box / file indexing reference
                            </p>
                        </div>
                    </div>

                    <TextInput v-model="form.container_ref" placeholder="E.g. BOX-404" required
                        class="h-11 rounded-xl px-3 border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 focus:outline-none text-[11px] font-bold tracking-wide" />
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-3">
                <InputLabel value="Custody / Check-in Notes" />
                <textarea v-model="form.notes" rows="3" placeholder="Add optional details like condition, verified attachments, or custody limits..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-3 text-[11px] font-bold tracking-wide focus:bg-white focus:ring-4 focus:ring-slate-500/10 focus:border-slate-400 focus:outline-none"></textarea>
                <InputError :message="form.errors.notes" />
            </div>

            <!-- Footer -->
            <div
                class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 pt-5 border-t border-slate-100">

                <button @click="showModal = false" type="button"
                    class="w-full sm:w-auto h-10 px-2 rounded-xl border border-slate-500 bg-white text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all active:scale-95 cursor-pointer">
                    Cancel
                </button>

                <button type="submit" :disabled="form.processing"
                    class="w-full sm:w-auto h-11 px-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-[9px] font-black uppercase tracking-[0.2em] shadow-[0_10px_25px_-10px_rgba(79,70,229,0.65)] hover:scale-[1.01] transition-all duration-300 disabled:opacity-50 disabled:hover:scale-100 flex items-center justify-center gap-2.5 cursor-pointer">

                    <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />

                    <CheckBadgeIcon v-else class="w-4 h-4 text-white" />

                    <span>
                        {{ form.processing ? "Saving..." : "Save Document" }}
                    </span>
                </button>
            </div>
        </form>
    </PremiumModal>

    <PremiumModal :show="showEditModal" @close="showEditModal = false" title="Edit Physical Document"
        subtitle="Update metadata and ownership details">
        <form @submit.prevent="submitEdit"
            class="relative p-6 space-y-6 text-left bg-white/95 backdrop-blur-xl border border-slate-200/70 rounded-[1.75rem] shadow-[0_12px_40px_-12px_rgba(15,23,42,0.12)] overflow-hidden">

            <div
                class="absolute -top-20 -right-20 w-52 h-52 bg-violet-500/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="relative flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 mb-3 rounded-full bg-violet-50 border border-violet-100 text-violet-600 text-[8px] font-black uppercase tracking-[0.2em]">
                        Document Update
                    </div>

                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-950 uppercase leading-none tracking-[0.2em]">
                            Edit Document
                        </h2>

                        <p class="text-[10px] text-slate-400 font-semibold mt-1">
                            Update document metadata, ownership, and storage details.
                        </p>
                    </div>
                </div>

                <div
                    class="hidden md:flex w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 shadow-lg items-center justify-center shrink-0">
                    <PencilSquareIcon class="w-6 h-6 text-white" />
                </div>
            </div>

            <div class="relative space-y-6">
                <div class="space-y-3">
                    <InputLabel value="Document Type" />
                    <TextInput v-model="editForm.document_type" required
                        class="h-11 pl-4 pr-10 rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 focus:outline-none text-[11px] font-bold tracking-wide" />
                    <InputError :message="editForm.errors.document_type" />
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    <div
                        class="group relative p-4 rounded-[1.5rem] border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                                <UserGroupIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Internal User" />
                                <p class="text-[9px] text-slate-400 font-semibold mt-1">
                                    Assign internal custody owner
                                </p>
                            </div>
                        </div>

                        <BaseSelect v-model="editForm.user_id"
                            class="h-11 rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:outline-none text-[11px] font-bold tracking-wide">
                            <option value="">UNASSIGNED</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name.toUpperCase() }}
                            </option>
                        </BaseSelect>
                        <InputError :message="editForm.errors.user_id" />
                    </div>

                    <div
                        class="group relative p-4 rounded-[1.5rem] border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shadow-sm shrink-0">
                                <UserCircleIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Outsider Name" />
                                <p class="text-[9px] text-slate-400 font-semibold mt-1">
                                    Optional when an internal user is assigned
                                </p>
                            </div>
                        </div>

                        <TextInput v-model="editForm.outsider_name" :disabled="!!editForm.user_id"
                            placeholder="Optional if user is assigned"
                            class="h-11 rounded-xl px-3 border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 focus:outline-none text-[11px] font-bold tracking-wide" />
                        <InputError :message="editForm.errors.outsider_name" />
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    <div
                        class="group relative p-4 rounded-[1.5rem] border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                                <MapPinIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Location" />
                                <p class="text-[9px] text-slate-400 font-semibold mt-1">
                                    Select storage location
                                </p>
                            </div>
                        </div>

                        <BaseSelect v-model="editForm.location_id"
                            class="h-11 rounded-xl border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:outline-none text-[11px] font-bold tracking-wide">
                            <option value="">SELECT LOCATION</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.name.toUpperCase() }}
                            </option>
                        </BaseSelect>
                        <InputError :message="editForm.errors.location_id" />
                    </div>

                    <div
                        class="group relative p-4 rounded-[1.5rem] border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                                <ArchiveBoxIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Container Reference" />
                                <p class="text-[9px] text-slate-400 font-semibold mt-1">
                                    Box / file indexing reference
                                </p>
                            </div>
                        </div>

                        <TextInput v-model="editForm.container_ref" placeholder="E.g. BOX-404" required
                            class="h-11 rounded-xl px-3 border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 focus:outline-none text-[11px] font-bold tracking-wide" />
                        <InputError :message="editForm.errors.container_ref" />
                    </div>
                </div>

                <div class="space-y-3">
                    <InputLabel value="Notes" />
                    <textarea v-model="editForm.notes" rows="3"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-3 text-[11px] font-bold tracking-wide focus:bg-white focus:ring-4 focus:ring-slate-500/10 focus:border-slate-400 focus:outline-none"></textarea>
                    <InputError :message="editForm.errors.notes" />
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 pt-5 border-t border-slate-100">
                <button type="button" @click="showEditModal = false"
                    class="w-full sm:w-auto h-10 px-2 rounded-xl border border-slate-500 bg-white text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all active:scale-95 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" :disabled="editForm.processing"
                    class="w-full sm:w-auto h-11 px-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-[9px] font-black uppercase tracking-[0.2em] shadow-[0_10px_25px_-10px_rgba(79,70,229,0.65)] hover:scale-[1.01] transition-all duration-300 disabled:opacity-50 disabled:hover:scale-100 flex items-center justify-center gap-2.5 cursor-pointer">
                    <ArrowPathIcon v-if="editForm.processing" class="w-4 h-4 animate-spin" />
                    <CheckBadgeIcon v-else class="w-4 h-4 text-white" />
                    <span>{{ editForm.processing ? "Updating..." : "Update Document" }}</span>
                </button>
            </div>
        </form>
    </PremiumModal>

    <PremiumModal :show="showDeleteModal" @close="closeDeleteConfirm" title="Delete Physical Document"
        subtitle="This action cannot be undone" maxWidth="sm">
        <div class="p-3 space-y-4 text-left">
            <div class="flex items-start gap-2">
                <div
                    class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <TrashIcon class="w-5 h-5" />
                </div>
                <div class="space-y-1 p-0">
                    <p class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                        Confirm deletion
                    </p>
                    <p class="text-sm text-slate-500 leading-snug">
                        Are you sure you want to delete
                        <span class="font-bold text-slate-900">
                            "{{ recordToDelete?.document_type || 'this document' }}"
                        </span>
                        ?
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="closeDeleteConfirm"
                    class="h-10 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="deleteRecord" :disabled="!recordToDelete"
                    class="h-10 px-4 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all disabled:opacity-50 cursor-pointer">
                    Delete
                </button>
            </div>
        </div>
    </PremiumModal>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.shadow-3xl {
    box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.15);
}

.font-mono {
    font-family: "JetBrains Mono", monospace;
}
</style>
