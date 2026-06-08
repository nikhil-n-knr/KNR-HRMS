<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from "vue";
import {
    InformationCircleIcon,
    ShoppingBagIcon,
    PlusIcon,
    LinkIcon,
    CurrencyRupeeIcon as CashIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ClockIcon,
    TruckIcon,
    TagIcon,
    TrashIcon,
    ArrowLeftIcon,
    WalletIcon,
    InboxArrowDownIcon,
    SparklesIcon,
    BuildingStorefrontIcon,
    ArchiveBoxIcon,
    CubeIcon,
    BoltIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    purchase_requests: Object,
    vendors: Array
});

// --- New PO Modal ---
const showModal = ref(false);
const form = useForm({
    vendor_id: '',
    items: [{ name: '', quantity: 1, unit_cost: 0 }]
});

const addItem = () => form.items.push({ name: '', quantity: 1, unit_cost: 0 });
const removeItem = (i) => form.items.splice(i, 1);

const totalCost = () => form.items.reduce((sum, i) => sum + (parseFloat(i.unit_cost) || 0) * (parseInt(i.quantity) || 0), 0);

const submit = () => {
    form.post(route('procurement.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.items = [{ name: '', quantity: 1, unit_cost: 0 }];
        }
    });
};

const statusConfig = (status) => {
    const map = {
        'Draft': { label: 'Planning', color: 'text-slate-400', bg: 'bg-slate-50', dot: 'bg-slate-300' },
        'Approved': { label: 'Ready to Buy', color: 'text-indigo-600', bg: 'bg-indigo-50', dot: 'bg-indigo-500' },
        'Ordered': { label: 'On the Way', color: 'text-amber-600', bg: 'bg-amber-50', dot: 'bg-amber-500' },
        'Received': { label: 'Arrived', color: 'text-emerald-600', bg: 'bg-emerald-50', dot: 'bg-emerald-500' },
    };
    return map[status] || { label: status, color: 'text-slate-400', bg: 'bg-slate-50', dot: 'bg-slate-300' };
};
</script>

<template>

    <Head title="Buy & Restock" />

    <div class="min-h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-8 xl:p-12 relative text-left">
        <div
            class="absolute -right-32 -top-32 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none">
        </div>
        <div
            class="absolute -left-32 bottom-0 w-128 h-128 bg-emerald-500/5 rounded-full blur-[140px] pointer-events-none">
        </div>

        <!-- Strategic Header Terminal -->
        <div
            class="bg-white px-6 py-6 flex flex-shrink-0 flex-col xl:flex-row xl:justify-between xl:items-center gap-6 z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-8 shadow-sm">
            <div
                class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px] pointer-events-none">
            </div>

            <div class="relative z-10 flex items-center gap-5">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })"
                    class="w-11 h-11 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 group/back shrink-0">
                    <ArrowLeftIcon class="w-6 h-6 group-hover/back:-translate-x-1 transition-transform" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-4">
                        <h1
                            class="text-3xl xl:text-4xl font-black text-slate-950 uppercase tracking-tight leading-none">
                            Buy & Restock</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon
                                class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div
                                class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-white/10 leading-relaxed">
                                Planning & Acquisition Terminal. Order new hardware from verified vendors and track
                                delivery logs.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 leading-none px-1">
                        Supply Logistics & Procurement Matrix</p>
                </div>
            </div>

            <div class="relative z-10 flex flex-wrap items-center gap-3 xl:gap-4 justify-start xl:justify-end">
                <div
                    class="px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
                    <span
                        class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 leading-none font-outfit">Active
                        Matrix Spend</span>
                    <span class="text-2xl font-black text-slate-900 tabular-nums leading-none tracking-tight">₹{{
                        (purchase_requests.total_buy || 0).toLocaleString() }}</span>
                </div>
                <button @click="showModal = true"
                    class="h-11 px-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-200 hover:scale-[1.01] transition-all flex items-center gap-3 active:scale-95 group/btn border border-indigo-500 cursor-pointer">
                    <PlusIcon
                        class="w-4 h-4 text-white/90 group-hover/btn:rotate-90 transition-transform duration-700" />
                    New Order
                </button>
            </div>
        </div>

        <!-- Order Grid Terminal -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-8 relative z-10 px-1 xl:px-2">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="req in purchase_requests.data" :key="req.id"
                    class="group relative flex h-full flex-col overflow-hidden rounded-[2.2rem] border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-slate-100/70 p-6 shadow-[0_20px_60px_-25px_rgba(15,23,42,0.18)] transition-all duration-500 hover:-translate-y-1 hover:border-indigo-100 hover:shadow-[0_30px_80px_-30px_rgba(79,70,229,0.32)]">

                    <!-- Ambient Effects -->
                    <div
                        class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl transition-all duration-700 group-hover:scale-125">
                    </div>

                    <div
                        class="absolute -left-16 -bottom-16 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl transition-all duration-700 group-hover:scale-125">
                    </div>

                    <!-- Top Glow -->
                    <div
                        class="absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
                    </div>

                    <div class="relative z-10 flex flex-1 flex-col">

                        <!-- Header -->
                        <div class="flex items-start justify-between gap-4 border-b border-slate-200/70 pb-5">

                            <!-- Left -->
                            <div class="flex items-center gap-4 min-w-0">

                                <!-- Icon -->
                                <div
                                    class="relative flex h-15 w-15 shrink-0 items-center justify-center rounded-[1.5rem] border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white text-indigo-600 shadow-lg shadow-indigo-500/10 transition-all duration-500 group-hover:rotate-6 group-hover:scale-105">
                                    <div
                                        class="absolute inset-0 rounded-[1.5rem] bg-gradient-to-br from-indigo-500/5 to-cyan-400/5">
                                    </div>

                                    <ShoppingBagIcon class="relative z-10 w-7 h-7" />
                                </div>

                                <!-- Content -->
                                <div class="min-w-0">

                                    <div
                                        class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.28em] text-indigo-600">
                                        Purchase Order
                                    </div>

                                    <h4
                                        class="mt-3 text-2xl font-black uppercase tracking-tight text-slate-950 leading-none break-words">
                                        #{{ req.po_number || 'TRK_000' }}
                                    </h4>

                                    <p
                                        class="mt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                        {{ req.created_at || 'JAN_2024' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="inline-flex items-center gap-3 rounded-2xl border px-4 py-3 text-[9px] font-black uppercase tracking-[0.24em] shadow-sm"
                                :class="statusConfig(req.status).bg + ' ' + statusConfig(req.status).color">
                                <div class="h-2.5 w-2.5 rounded-full" :class="statusConfig(req.status).dot + (req.status === 'Ordered'
                                    ? ' animate-pulse shadow-[0_0_10px_rgba(245,158,11,1)]'
                                    : '')"></div>

                                {{ statusConfig(req.status).label }}
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="mt-6 space-y-5 flex-1">

                            <!-- Vendor -->
                            <div
                                class="group/vendor relative overflow-hidden rounded-[1.8rem] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-50/80 p-5 shadow-sm transition-all duration-500 hover:border-indigo-100 hover:shadow-md">

                                <div
                                    class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-400/10 blur-3xl">
                                </div>

                                <div class="relative z-10 flex items-center justify-between gap-4">

                                    <!-- Left -->
                                    <div class="flex items-center gap-4 min-w-0">

                                        <!-- Icon -->
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 shadow-sm">
                                            <BuildingStorefrontIcon class="w-5 h-5" />
                                        </div>

                                        <!-- Content -->
                                        <div class="min-w-0">

                                            <div
                                                class="inline-flex items-center rounded-full border border-rose-100 bg-rose-50 px-3 py-1 text-[7px] font-black uppercase tracking-[0.28em] text-rose-600">
                                                Vendor Source
                                            </div>

                                            <h5
                                                class="mt-3 text-lg font-black uppercase tracking-tight text-slate-950 break-words">
                                                {{ req.vendor?.name || 'GENERIC_VENDOR' }}
                                            </h5>
                                        </div>
                                    </div>

                                    <!-- Link -->
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-100 bg-white text-slate-300 transition-all duration-300 group-hover/vendor:bg-indigo-600 group-hover/vendor:text-white">
                                        <LinkIcon class="w-5 h-5" />
                                    </div>
                                </div>
                            </div>

                            <!-- Manifest -->
                            <div
                                class="relative overflow-hidden rounded-[1.8rem] border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-cyan-50/60 p-5 shadow-sm">

                                <!-- Glow -->
                                <div
                                    class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-indigo-500/10 blur-3xl">
                                </div>

                                <!-- Header -->
                                <div
                                    class="relative z-10 mb-5 flex items-center justify-between border-b border-indigo-100 pb-4">
                                    <div>
                                        <div
                                            class="inline-flex items-center rounded-full border border-indigo-100 bg-white px-3 py-1 text-[8px] font-black uppercase tracking-[0.28em] text-indigo-600 shadow-sm">
                                            Manifest Hub
                                        </div>

                                        <p
                                            class="mt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            Procurement payload summary
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-indigo-100 bg-white px-4 py-2 text-center shadow-sm">
                                        <span class="block text-lg font-black text-slate-950 leading-none">
                                            {{ req.items?.length || 0 }}
                                        </span>

                                        <span
                                            class="mt-1 block text-[8px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            Items
                                        </span>
                                    </div>
                                </div>

                                <!-- Items -->
                                <ul class="relative z-10 space-y-3">

                                    <li v-for="item in (req.items || []).slice(0, 3)" :key="item.id"
                                        class="flex items-center justify-between rounded-2xl border border-white/70 bg-white/70 px-4 py-3 shadow-sm">

                                        <!-- Left -->
                                        <div class="flex items-center gap-3 truncate max-w-[70%]">
                                            <div
                                                class="h-2.5 w-2.5 rounded-full bg-indigo-400 shadow-[0_0_10px_rgba(99,102,241,0.5)]">
                                            </div>

                                            <span
                                                class="truncate text-[11px] font-black uppercase tracking-[0.16em] text-slate-700">
                                                {{ item.name }}
                                            </span>
                                        </div>

                                        <!-- Qty -->
                                        <div
                                            class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-700">
                                            x{{ item.quantity }}
                                        </div>
                                    </li>

                                    <!-- Extra -->
                                    <li v-if="req.items?.length > 3"
                                        class="pt-2 text-center text-[9px] font-black uppercase tracking-[0.22em] text-indigo-400">
                                        + {{ req.items.length - 3 }} more payload entries
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-7 flex items-center justify-between border-t border-slate-200/70 pt-6">

                            <!-- Amount -->
                            <div>

                                <span class="block text-[9px] font-black uppercase tracking-[0.28em] text-slate-400">
                                    Total Net Value
                                </span>

                                <span class="mt-2 block text-4xl font-black tracking-tight text-slate-950 leading-none">
                                    ₹{{ (req.total_cost || 0).toLocaleString() }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">

                                <button
                                    class="group/action flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-slate-950 hover:text-white hover:shadow-lg active:scale-95">
                                    <InboxArrowDownIcon
                                        class="w-5 h-5 transition-transform duration-300 group-hover/action:scale-110" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Injection Node -->
                <div @click="showModal = true"
                    class="bg-white rounded-[2rem] border-2 border-dashed border-slate-100 p-10 flex flex-col items-center justify-center text-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 hover:text-indigo-600 transition-all group cursor-pointer min-h-[420px] shadow-sm hover:shadow-indigo-500/5 relative overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                    </div>
                    <div
                        class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-100 flex items-center justify-center mb-8 group-hover:rotate-180 group-hover:border-indigo-500 transition-all duration-1000 group-hover:scale-110 relative z-10 bg-white shadow-lg shadow-indigo-500/5">
                        <PlusIcon class="w-12 h-12 text-slate-100 group-hover:text-indigo-600 transition-colors" />
                    </div>
                    <div class="text-center relative z-10">
                        <span
                            class="block text-2xl font-black uppercase tracking-tighter text-slate-950 mb-3 leading-none">Draft
                            Log Matrix</span>
                        <span
                            class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.45em] opacity-60">Architect
                            a new purchase order.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Order Modal -->
        <Modal :show="showModal" @close="showModal = false" title="" max-width="3xl">
            <div
                class="relative overflow-hidden rounded-[2rem] border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-slate-100/70 shadow-[0_35px_100px_-30px_rgba(15,23,42,0.32)] max-h-[calc(100vh-2rem)] flex flex-col">

                <!-- Ambient Effects -->
                <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-indigo-500/10 blur-3xl"></div>

                <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <!-- Top Glow -->
                <div
                    class="absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
                </div>

                <!-- Header -->
                <div
                    class="relative z-10 flex items-center justify-between border-b border-slate-200/70 bg-white/70 backdrop-blur-xl px-6 py-6 sm:px-8">
                    <div class="space-y-2">

                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.28em] text-indigo-600">
                            Procurement Engine
                        </div>

                        <div>
                            <h3
                                class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-slate-950 leading-none">
                                Initialize Purchase Order
                            </h3>

                            <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                Create acquisition log and vendor payload matrix
                            </p>
                        </div>
                    </div>

                    <button @click="showModal = false"
                        class="group flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm transition-all duration-300 hover:scale-105 hover:border-rose-100 hover:bg-rose-50 hover:text-rose-500 active:scale-95 cursor-pointer">
                        <XMarkIcon class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit"
                    class="relative z-10 flex-1 overflow-y-auto no-scrollbar px-6 py-7 sm:px-8 space-y-8">

                    <!-- Vendor -->
                    <div class="rounded-[1.8rem] border border-slate-200 bg-white/80 p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <BuildingStorefrontIcon class="w-4 h-4 text-indigo-500" />

                            <InputLabel value="Resource Source (Vendor)"
                                class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                        </div>

                        <select v-model="form.vendor_id"
                            class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/70 px-5 text-[12px] font-black uppercase tracking-[0.18em] text-slate-900 shadow-sm transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer"
                            required>
                            <option value="">Choose Vendor...</option>

                            <option v-for="v in vendors" :key="v.id" :value="v.id">
                                {{ v.name.toUpperCase() }}
                            </option>
                        </select>

                        <InputError :message="form.errors.vendor_id" />
                    </div>

                    <!-- Items -->
                    <div class="rounded-[1.8rem] border border-slate-200 bg-white/80 p-5 shadow-sm">

                        <!-- Header -->
                        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h4 class="text-lg font-black uppercase tracking-tight text-slate-950">
                                    Items Matrix
                                </h4>

                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    Add procurement payload rows
                                </p>
                            </div>

                            <button type="button" @click="addItem"
                                class="group flex h-11 items-center gap-2 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-600 to-violet-600 px-5 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-lg shadow-indigo-500/20 transition-all duration-300 hover:scale-[1.03] hover:shadow-indigo-500/30 active:scale-95 cursor-pointer">
                                <PlusIcon class="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />

                                <span>Add Row</span>
                            </button>
                        </div>

                        <!-- Rows -->
                        <div class="space-y-4 max-h-[380px] overflow-y-auto pr-1 no-scrollbar">

                            <div v-for="(item, i) in form.items" :key="i"
                                class="group/row relative overflow-hidden rounded-[1.8rem] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-indigo-50/40 p-5 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:border-indigo-100 hover:shadow-[0_20px_50px_-25px_rgba(79,70,229,0.28)]">

                                <!-- Glow -->
                                <div
                                    class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-400/10 blur-3xl">
                                </div>

                                <div class="relative z-10 grid grid-cols-12 gap-4">

                                    <!-- Item -->
                                    <div class="col-span-12 md:col-span-6 space-y-2">
                                        <InputLabel value="Item Name"
                                            class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500" />

                                        <TextInput v-model="item.name" type="text"
                                            class="w-full h-12 rounded-2xl border-slate-200 bg-white/80 p-3"
                                            placeholder="E.G. LENOVO THINKPAD T14" />
                                    </div>

                                    <!-- Qty -->
                                    <div class="col-span-6 md:col-span-2 space-y-2">
                                        <InputLabel value="Qty"
                                            class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500" />

                                        <TextInput v-model="item.quantity" type="number"
                                            class="w-full h-12 rounded-2xl border-slate-200 bg-white/80 text-center font-black" />
                                    </div>

                                    <!-- Cost -->
                                    <div class="col-span-6 md:col-span-3 space-y-2">
                                        <InputLabel value="Unit Cost"
                                            class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500" />

                                        <TextInput v-model="item.unit_cost" type="number"
                                            class="w-full h-12 rounded-2xl border-slate-200 bg-white/80 font-black text-center" />
                                    </div>

                                    <!-- Delete -->
                                    <div class="col-span-12 md:col-span-1 flex md:items-end justify-end">
                                        <button v-if="form.items.length > 1" @click="removeItem(i)" type="button"
                                            class="group/delete flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-400 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 cursor-pointer">
                                            <TrashIcon
                                                class="w-4 h-4 transition-transform duration-300 group-hover/delete:scale-110" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex flex-col gap-5 border-t border-slate-200/70 pt-7 sm:flex-row sm:items-center sm:justify-between">

                        <!-- Total -->
                        <div class="rounded-[1.6rem] border border-slate-200 bg-white/80 px-6 py-5 shadow-sm">
                            <span class="block text-[9px] font-black uppercase tracking-[0.24em] text-slate-400">
                                Total Payload Spend
                            </span>

                            <span class="mt-2 block text-3xl font-black tracking-tight text-slate-950 font-mono">
                                ₹{{ totalCost().toLocaleString() }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 justify-end">

                            <button @click="showModal = false" type="button"
                                class="h-11 rounded-2xl border border-slate-300 bg-white px-6 text-[10px] font-black uppercase tracking-[0.22em] text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 cursor-pointer">
                                Discard
                            </button>

                            <button type="submit" :disabled="form.processing"
                                class="group flex h-12 items-center gap-3 rounded-[1.4rem] bg-gradient-to-r from-indigo-600 to-violet-600 px-7 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-[0_15px_35px_-15px_rgba(79,70,229,0.7)] transition-all hover:scale-[1.02] hover:shadow-indigo-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer">
                                <ArrowPathIcon v-if="form.processing" class="w-5 h-5 animate-spin" />

                                <CheckCircleIcon v-else
                                    class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />

                                <span>
                                    {{
                                        form.processing
                                            ? 'Syncing Payload...'
                                            : 'Confirm Purchase Order'
                                    }}
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
