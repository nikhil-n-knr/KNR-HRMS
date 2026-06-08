<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import {
    CommandLineIcon,
    ArrowLeftIcon,
    PlusIcon,
    TrashIcon,
    AdjustmentsHorizontalIcon,
    SparklesIcon,
    BoltIcon,
    CubeIcon,
    TableCellsIcon,
    MapPinIcon,
    ShieldCheckIcon,
    InformationCircleIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    InboxIcon,
    HomeIcon,
    BuildingOfficeIcon,
    Square3Stack3DIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/solid';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";


defineOptions({ layout: MainLayout });

const props = defineProps({
    locations: Array
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const parentNode = ref(null);
const nodeToDelete = ref(null);
const form = useForm({
    name: '',
    type: 'Room',
    parent_id: null
});

const openAddModal = (parent = null) => {
    parentNode.value = parent;
    form.reset();
    form.parent_id = parent ? parent.id : null;
    form.type = parent ? suggestType(parent.type) : 'Room';
    showModal.value = true;
};

const suggestType = (parentType) => {
    const hierarchy = { 'Room': 'Cabinet', 'Cabinet': 'Rack', 'Rack': 'Shelf', 'Shelf': 'Bin' };
    return hierarchy[parentType] || 'Bin';
};

const submitNode = () => {
    form.post(route('admin.physical-documents.locations.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const openDeleteConfirm = (node) => {
    nodeToDelete.value = node;
    showDeleteModal.value = true;
};

const closeDeleteConfirm = () => {
    showDeleteModal.value = false;
    nodeToDelete.value = null;
};

const deleteNode = () => {
    if (!nodeToDelete.value) return;

    router.delete(route('admin.physical-documents.locations.destroy', nodeToDelete.value.id), {
        onSuccess: () => {
            closeDeleteConfirm();
        },
        onError: () => {
            closeDeleteConfirm();
        },
    });
};

const getIcon = (type) => {
    switch (type) {
        case 'Room': return HomeIcon;
        case 'Cabinet': return BuildingOfficeIcon;
        case 'Rack': return InboxIcon;
        case 'Shelf': return CommandLineIcon;
        case 'Bin': return CubeIcon;
        default: return Square3Stack3DIcon;
    }
};
</script>

<template>

    <Head title="Storage Matrix Configuration" />

    <GradientHeroHeader kicker="MATRIX_CONFIG_MODE" title="Storage Builder"
        subtitle="PHYSICAL_STRUCTURE_ARCHITECT_v1.0.">
        <template #right>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button @click="openAddModal(null)"
                    class="h-13 px-4  bg-white text-slate-950 rounded-[1.75rem] text-[10px] lg:text-[11px] font-black uppercase tracking-[0.3em] shadow-2xl hover:bg-indigo-500 hover:text-white transition-all flex items-center gap-3 active:scale-95 group italic border-none">
                    <PlusIcon class="w-8 h-8 group-hover:rotate-180 transition-transform duration-700 italic" />
                    <span>Initialize Root Zone</span>
                </button>
            </div>
        </template>
    </GradientHeroHeader>

    <div class="p-6">

        <!-- Strategic Header Terminal -->
        <!-- <header class="bg-slate-950/95 backdrop-blur-xl rounded-[3rem] border border-white/10 p-8 lg:p-10 shadow-[0_30px_100px_-40px_rgba(15,23,42,0.85)] mb-10 relative overflow-hidden group">
            <div class="absolute -right-24 -top-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000 pointer-events-none"></div>
            <div class="absolute -left-24 bottom-0 w-72 h-72 bg-cyan-400/10 rounded-full blur-[120px] pointer-events-none"></div>
            
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-10 relative z-10">
                <div class="flex items-center gap-10 italic text-left">
                    <Link :href="route('admin.physical-documents.index')" 
                        class="w-20 h-20 bg-white/5 border border-white/10 rounded-[2rem] flex items-center justify-center text-white hover:bg-white/10 hover:border-indigo-500/50 transition-all active:scale-90 shadow-2xl shrink-0 italic">
                        <ArrowLeftIcon class="w-8 h-8" />
                    </Link>
                    <div class="italic">
                    <div class="flex items-center gap-4 lg:gap-6 italic flex-wrap">
                            <h1 class="text-4xl lg:text-5xl font-black text-white uppercase tracking-tighter italic leading-none">Storage Builder</h1>
                            <div class="px-4 py-2 bg-indigo-500/10 border border-indigo-500/20 rounded-full flex items-center gap-3 italic">
                                <div class="w-2.5 h-2.5 bg-indigo-400 rounded-full animate-pulse shadow-[0_0_12px_#818cf8]"></div>
                                <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest italic">MATRIX_CONFIG_MODE</span>
                            </div>
                        </div>
                        <p class="text-[11px] font-black text-slate-500 uppercase tracking-[0.6em] mt-5 italic leading-none drop-shadow-sm truncate uppercase">PHYSICAL_STRUCTURE_ARCHITECT_v1.0</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 italic">
                    <button @click="openAddModal(null)" 
                        class="h-16 px-8 lg:h-20 lg:px-12 bg-white text-slate-950 rounded-[1.75rem] text-[10px] lg:text-[11px] font-black uppercase tracking-[0.3em] shadow-2xl hover:bg-indigo-500 hover:text-white transition-all flex items-center gap-4 lg:gap-6 active:scale-95 group italic border-none">
                        <PlusIcon class="w-8 h-8 group-hover:rotate-180 transition-transform duration-700 italic" />
                        <span>Initialize Root Zone</span>
                    </button>
                </div>
            </div>
        </header> -->

        <!-- Main Architect Workspace -->
        <main
            class="flex-1 bg-white/85 backdrop-blur-3xl rounded-[1.75rem] border border-slate-200 p-5 shadow-[0_30px_100px_-50px_rgba(15,23,42,0.35)] relative overflow-hidden z-10 transition-all duration-1000 italic scroll-smooth overflow-y-auto custom-scrollbar">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_var(--tw-gradient-stops))] from-indigo-50/50 via-transparent to-transparent pointer-events-none italic">
            </div>

            <div v-if="locations.length === 0"
                class="h-full flex flex-col items-center justify-center text-center py-40 italic">
                <div
                    class="w-40 h-40 bg-slate-50 border-4 border-white rounded-[4rem] shadow-inner flex items-center justify-center text-slate-200 mb-10 group hover:scale-110 transition-transform duration-700 italic">
                    <CubeIcon class="w-20 h-20 group-hover:rotate-12 transition-transform italic" />
                </div>
                <h3 class="text-3xl font-black text-slate-950 uppercase tracking-tighter italic leading-none">Matrix
                    Void
                    Detected</h3>
                <p
                    class="text-[11px] font-black text-slate-400 uppercase tracking-[0.5em] mt-6 italic px-10 max-w-lg leading-loose">
                    No storage protocols defined. Initialize your physical repository structure to begin tracking.</p>
            </div>

            <!-- Recursive Protocol Tree -->
            <div class="max-w-6xl mx-auto space-y-10 italic pb-24">
                <div v-for="root in locations" :key="root.id" class="group/root space-y-8 italic">
                    <!-- Root Node Card -->
                    <div
                        class="group/card relative overflow-hidden rounded-[2rem] border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-slate-100/80 p-6 shadow-[0_20px_60px_-25px_rgba(15,23,42,0.22)] transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_30px_80px_-30px_rgba(79,70,229,0.35)]">
                        <!-- Ambient Effects -->
                        <div
                            class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl transition-all duration-700 group-hover/card:scale-125">
                        </div>

                        <div
                            class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-cyan-400/10 blur-3xl transition-all duration-700 group-hover/card:scale-125">
                        </div>

                        <!-- Top Gradient Border -->
                        <div
                            class="absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
                        </div>

                        <!-- Main Content -->
                        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                            <!-- Left Section -->
                            <div class="flex items-center gap-5 lg:gap-7 min-w-0">

                                <!-- Icon -->
                                <div
                                    class="relative flex h-18 w-18 lg:h-20 lg:w-20 shrink-0 items-center justify-center rounded-[1.75rem] border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white text-indigo-600 shadow-lg shadow-indigo-500/10 transition-all duration-500 group-hover/card:rotate-6 group-hover/card:scale-105">
                                    <div
                                        class="absolute inset-0 rounded-[1.75rem] bg-gradient-to-br from-indigo-500/5 to-cyan-400/5">
                                    </div>

                                    <component :is="getIcon(root.type)" class="relative z-10 w-9 h-9 lg:w-10 lg:h-10" />
                                </div>

                                <!-- Text -->
                                <div class="min-w-0">

                                    <div
                                        class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.35em] text-indigo-600 shadow-sm">
                                        {{ root.type }}
                                    </div>

                                    <h3
                                        class="mt-3 text-2xl lg:text-3xl font-black uppercase tracking-tight text-slate-950 leading-none break-words">
                                        {{ root.name }}
                                    </h3>

                                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                        Resource Node
                                    </p>
                                </div>
                            </div>

                            <!-- Right Actions -->
                            <div class="relative z-10 flex items-center gap-3">

                                <!-- Add Button -->
                                <button @click="openAddModal(root)"
                                    class="group/add flex h-12 items-center gap-3 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-indigo-500/20 transition-all duration-300 hover:scale-[1.03] hover:shadow-indigo-500/30 active:scale-95 cursor-pointer">
                                    <PlusIcon
                                        class="w-4 h-4 transition-transform duration-300 group-hover/add:rotate-90" />

                                    <span>Add Child</span>
                                </button>

                                <!-- Delete -->
                                <button type="button" @click.prevent.stop="openDeleteConfirm(root)"
                                    class="group/delete flex h-12 w-12 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 cursor-pointer">
                                    <TrashIcon
                                        class="w-5 h-5 transition-transform duration-300 group-hover/delete:scale-110" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- L2 Nesting -->
                    <div v-if="root.children && root.children.length" class="relative pl-4 lg:pl-14 space-y-5">
                        <!-- Connector Line -->
                        <div
                            class="absolute left-1 lg:left-5 top-2 bottom-2 w-px bg-gradient-to-b from-indigo-200 via-indigo-100 to-transparent">
                        </div>

                        <div v-for="child in root.children" :key="child.id" class="space-y-5">
                            <div
                                class="group/card relative overflow-hidden rounded-[2rem] border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-slate-100/80 p-6 shadow-[0_20px_60px_-25px_rgba(15,23,42,0.22)] transition-all duration-500">
                                <!-- Ambient Effects -->
                                <div
                                    class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl transition-all duration-700">
                                </div>

                                <div
                                    class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-cyan-400/10 blur-3xl transition-all duration-700">
                                </div>

                                <!-- Top Gradient Border -->
                                <div
                                    class="absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
                                </div>

                                <!-- Main Content -->
                                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="flex items-center gap-5 lg:gap-7 min-w-0">
                                        <div
                                            class="relative flex h-18 w-18 lg:h-20 lg:w-20 shrink-0 items-center justify-center rounded-[1.75rem] border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white text-indigo-600 shadow-lg shadow-indigo-500/10 transition-all duration-500">
                                            <div
                                                class="absolute inset-0 rounded-[1.75rem] bg-gradient-to-br from-indigo-500/5 to-cyan-400/5">
                                            </div>
                                            <component :is="getIcon(child.type)"
                                                class="relative z-10 w-9 h-9 lg:w-10 lg:h-10" />
                                        </div>

                                        <div class="min-w-0">
                                            <div
                                                class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.35em] text-indigo-600 shadow-sm">
                                                {{ child.type }}
                                            </div>

                                            <h4
                                                class="mt-3 text-2xl lg:text-3xl font-black uppercase tracking-tight text-slate-950 leading-none break-words">
                                                {{ child.name }}
                                            </h4>

                                            <p class="mt-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                                Nested Resource Node
                                            </p>
                                        </div>
                                    </div>

                                    <div class="relative z-10 flex items-center gap-3">
                                        <button type="button" @click.prevent.stop="openAddModal(child)"
                                            class="group/add flex h-12 items-center gap-3 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-indigo-500/20 transition-all duration-300 hover:scale-[1.03] hover:shadow-indigo-500/30 active:scale-95 cursor-pointer">
                                            <PlusIcon class="w-4 h-4 transition-transform duration-300 group-hover/add:rotate-90" />
                                            <span>Add Child</span>
                                        </button>

                                        <button type="button" @click.prevent.stop="openDeleteConfirm(child)"
                                            class="group/delete flex h-12 w-12 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 cursor-pointer">
                                            <TrashIcon class="w-5 h-5 transition-transform duration-300 group-hover/delete:scale-110" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="child.children && child.children.length" class="relative pl-4 lg:pl-14">
                                <!-- Connector Line -->
                                <div
                                    class="absolute left-1 lg:left-5 top-2 bottom-2 w-px bg-gradient-to-b from-indigo-200 via-indigo-100 to-transparent">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                    <div v-for="gc in child.children" :key="gc.id"
                                        class="group/card relative overflow-hidden rounded-[2rem] border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-slate-100/80 p-6 shadow-[0_20px_60px_-25px_rgba(15,23,42,0.22)] transition-all duration-500">

                                        <!-- Glow Effects -->
                                        <div
                                            class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl transition-all duration-700">
                                        </div>

                                        <div
                                            class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-cyan-400/10 blur-3xl transition-all duration-700">
                                        </div>

                                        <!-- Top Border Glow -->
                                        <div
                                            class="absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
                                        </div>

                                        <!-- Content -->
                                        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                            <!-- Left -->
                                            <div class="flex items-center gap-5 lg:gap-7 min-w-0">
                                                <!-- Icon -->
                                                <div
                                                    class="relative flex h-14 w-14 lg:h-16 lg:w-16 shrink-0 items-center justify-center rounded-[1.5rem] border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white text-indigo-600 shadow-lg shadow-indigo-500/10 transition-all duration-500">
                                                    <div
                                                        class="absolute inset-0 rounded-[1.5rem] bg-gradient-to-br from-indigo-500/5 to-cyan-400/5">
                                                    </div>

                                                    <component :is="getIcon(gc.type)" class="relative z-10 w-7 h-7 lg:w-8 lg:h-8" />
                                                </div>

                                                <!-- Content -->
                                                <div class="min-w-0">
                                                    <!-- Type Badge -->
                                                    <div
                                                        class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.35em] text-indigo-600 shadow-sm">
                                                        {{ gc.type }}
                                                    </div>

                                                    <!-- Name -->
                                                    <h5
                                                        class="mt-3 text-xl lg:text-2xl font-black uppercase tracking-tight text-slate-950 leading-none break-words">
                                                        {{ gc.name }}
                                                    </h5>

                                                    <!-- Meta -->
                                                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                                        Nested Storage Node
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Delete -->
                                            <div class="relative z-10 flex items-center gap-3">
                                                <button type="button" @click.prevent.stop="openDeleteConfirm(gc)"
                                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 cursor-pointer">
                                                    <TrashIcon class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm"
        @click.self="closeDeleteConfirm">
        <div
            class="w-full max-w-md rounded-[2rem] bg-white shadow-2xl border border-slate-200 overflow-hidden text-left">
            <div class="bg-gradient-to-r from-rose-50 via-white to-white px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <TrashIcon class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-rose-500">Confirm Delete</p>
                        <h3 class="mt-1 text-xl font-black text-slate-950">Delete this node?</h3>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-slate-600 leading-relaxed">
                    This will permanently remove the selected storage node and any nested structure beneath it.
                </p>

                <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                    <div class="text-[10px] font-black uppercase tracking-[0.22em] text-rose-500">Selected Node</div>
                    <div class="mt-2 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-rose-100 text-rose-500">
                            <component :is="nodeToDelete ? getIcon(nodeToDelete.type) : Square3Stack3DIcon"
                                class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-950 uppercase tracking-tight truncate">
                                {{ nodeToDelete?.name || 'Selected node' }}
                            </p>
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-rose-500">
                                {{ nodeToDelete?.type || 'Unknown Type' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-5 bg-slate-50 border-t border-slate-100">
                <button type="button" @click="closeDeleteConfirm"
                    class="h-11 px-5 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-[0.28em] text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="deleteNode"
                    class="h-11 px-5 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-[0.28em] hover:bg-rose-700 transition-all cursor-pointer">
                    Delete Node
                </button>
            </div>
        </div>
    </div>

    <!-- Node Acquisition Modal -->
    <PremiumModal :show="showModal" @close="showModal = false" title="Node Acquisition"
        subtitle="Expand physical archiving structure with new storage terminal">
        <form @submit.prevent="submitNode" class="relative overflow-hidden p-4  text-left">
            <!-- Ambient Background -->
            <div
                class="absolute -top-24 -right-20 h-72 w-72 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
            </div>

            <div
                class="absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl pointer-events-none">
            </div>

            <!-- Header Strip -->
            <div class="relative flex items-start justify-between gap-5 border-b border-slate-200/70 pb-6">
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-[8px] font-black uppercase tracking-[0.28em] text-indigo-600">
                        Storage Architecture
                    </div>

                    <div>
                        <h2 class="text-2xl font-black uppercase tracking-tight text-slate-950 leading-none">
                            Create Storage Node
                        </h2>

                        <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                            Register physical storage hierarchy structure
                        </p>
                    </div>
                </div>

                <div
                    class="hidden sm:flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.4rem] bg-gradient-to-br from-indigo-600 to-violet-600 shadow-lg shadow-indigo-500/20">
                    <ArchiveBoxIcon class="w-7 h-7 text-white" />
                </div>
            </div>

            <!-- Parent Node -->
            <div v-if="parentNode"
                class="relative mt-7 overflow-hidden rounded-[2rem] border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-50/80 p-4 shadow-sm">
                <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-indigo-100/60 blur-3xl"></div>

                <div class="relative flex items-center gap-5">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.4rem] border border-indigo-100 bg-white text-indigo-600 shadow-sm">
                        <component :is="getIcon(parentNode.type)" class="w-7 h-7" />
                    </div>

                    <div class="min-w-0">
                        <div
                            class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.3em] text-indigo-600">
                            Parent Node
                        </div>

                        <h4 class="mt-3 text-xl font-black uppercase tracking-tight text-slate-950 break-words">
                            {{ parentNode.name }}
                        </h4>

                        <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ parentNode.type }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="relative mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">

                <!-- Type -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <Squares2X2Icon class="w-4 h-4 text-indigo-500" />

                        <InputLabel value="Terminal Classification"
                            class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                    </div>

                    <BaseSelect v-model="form.type"
                        class="w-full h-12 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide outline-none appearance-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        <option>Room</option>
                        <option>Cabinet</option>
                        <option>Rack</option>
                        <option>Shelf</option>
                        <option>Bin</option>
                        <option>Safe</option>
                        <option>Offsite</option>
                    </BaseSelect>
                </div>

                <!-- Name -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <QrCodeIcon class="w-4 h-4 text-violet-500" />

                        <InputLabel value="Reference Descriptor"
                            class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                    </div>

                    <TextInput v-model="form.name" placeholder="E.G. ZONE_A / BIN_402" required
                        class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide shadow-sm focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10" />
                </div>
            </div>

            <!-- Footer -->
            <div
                class="relative mt-9 flex flex-col-reverse gap-4 border-t border-slate-200/70 pt-7 sm:flex-row sm:items-center sm:justify-between">
                <!-- Cancel -->
                <button @click="showModal = false" type="button"
                    class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-5 text-[10px] font-black uppercase tracking-[0.22em] text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 sm:w-auto cursor-pointer">
                    Abort Protocol
                </button>

                <!-- Submit -->
                <button type="submit" :disabled="form.processing"
                    class="group flex h-13 w-full items-center justify-center gap-3 rounded-[1.4rem] bg-gradient-to-r from-indigo-600 to-violet-600 px-8 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-[0_15px_35px_-15px_rgba(79,70,229,0.7)] transition-all hover:scale-[1.01] hover:shadow-indigo-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto cursor-pointer">
                    <ArrowPathIcon v-if="form.processing" class="w-5 h-5 animate-spin" />

                    <CheckCircleIcon v-else
                        class="w-5 h-5 text-cyan-200 transition-transform duration-300 group-hover:scale-110" />

                    <span>
                        {{
                            form.processing
                                ? 'Syncing Matrix...'
                                : 'Initialize Storage Node'
                        }}
                    </span>
                </button>
            </div>
        </form>
    </PremiumModal>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.05);
    border-radius: 20px;
}

.shadow-3xl {
    box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.15);
}
</style>
