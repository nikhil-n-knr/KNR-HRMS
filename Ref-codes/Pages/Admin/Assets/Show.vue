<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';
import { Link, useForm, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    PrinterIcon,
    PencilSquareIcon,
    WrenchScrewdriverIcon,
    IdentificationIcon,
    ClockIcon,
    CheckCircleIcon,
    CpuChipIcon,
    UserCircleIcon,
    TrashIcon,
    ArrowPathIcon,
    MapPinIcon,
    BoltIcon,
    UserPlusIcon,
    ArrowUturnLeftIcon,
    ClipboardDocumentCheckIcon,
    DocumentTextIcon,
    BanknotesIcon,
    Squares2X2Icon,
    ShieldCheckIcon,
    QrCodeIcon,
    CalendarDaysIcon,
    TagIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
    asset: Object,
    timeline: Array,
    qrCode: String,
    users: Array,
    categories: Array,
    vendors: Array,
});

const showServiceModal = ref(false);
const showEditModal = ref(false);
const showAssignModal = ref(false);
const showDeleteModal = ref(false);

const serviceForm = useForm({
    type: 'Repair',
    description: '',
    cost: 0,
    service_date: new Date().toISOString().split('T')[0],
});

const editForm = useForm({
    name: props.asset?.name || '',
    category_id: props.asset?.category_id || '',
    serial_number: props.asset?.serial_number || '',
    purchase_cost: props.asset?.purchase_cost || 0,
    purchase_date: props.asset?.purchase_date?.split("|")[0] || '',
    status: props.asset?.status || 'Available',
    is_serialized: !!props.asset?.is_serialized,
    make: props.asset?.meta?.make || '',
    model: props.asset?.meta?.model || '',
    vendor_id: props.asset?.vendor_id || '',
});

const assignForm = useForm({
    user_id: '',
});

const submitService = () => {
    serviceForm.post(route('admin.assets.maintenance.store', props.asset.id), {
        onSuccess: () => {
            showServiceModal.value = false;
            serviceForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.put(route('admin.assets.update', props.asset.id), {
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const submitAssign = () => {
    assignForm.post(route('admin.assets.assign', props.asset.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            assignForm.reset();
        },
    });
};

const returnAsset = () => {
    router.post(route('admin.assets.return', props.asset.id), {});
};

const markAvailable = () => {
    router.post(route('admin.assets.markAvailable', props.asset.id), {}, {
        preserveScroll: true
    });
};

const openDeleteConfirm = () => {
    showDeleteModal.value = true;
};

const closeDeleteConfirm = () => {
    showDeleteModal.value = false;
};

const deleteAsset = () => {
    router.delete(route('admin.assets.destroy', props.asset.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteConfirm();
            setTimeout(() => {
                const previousUrl = document.referrer || route('admin.assets.index');
                window.location.href = previousUrl;
            }, 150);
        },
    });
};

const getStatusStyles = (status) => {
    switch (status) {
        case 'Available':
            return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'Assigned':
            return 'bg-indigo-50 text-indigo-700 border-indigo-100';
        case 'In_Service':
            return 'bg-amber-50 text-amber-700 border-amber-100';
        case 'Lost':
            return 'bg-rose-50 text-rose-700 border-rose-100';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>

    <Head :title="'Asset Details - ' + asset.name" />
    <MainLayout>
        <div class="w-full mx-auto space-y-8 p-4 bg-slate-50 min-h-screen">
            <section class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <div class="flex flex-col xl:flex-row justify-between gap-8">
                    <div class="flex items-start gap-6">
                        <div class="w-40 h-40 bg-slate-50 border border-slate-200 rounded-2xl p-4 shrink-0">
                            <div class="w-full h-full [&>svg]:w-full [&>svg]:h-full [&>svg]:text-slate-900"
                                v-html="qrCode"></div>
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-3 mb-4">
                                <span
                                    class="px-3 py-1 rounded-lg border text-[10px] font-bold uppercase tracking-widest"
                                    :class="getStatusStyles(asset.status)">
                                    {{ asset.status }}
                                </span>
                                <span
                                    class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[10px] font-bold uppercase tracking-widest text-slate-700">
                                    {{ asset.category?.name || 'Uncategorized' }}
                                </span>
                            </div>
                            <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ asset.name }}</h1>
                            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-5 text-left">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Serial
                                        Number</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.serial_number || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Location
                                    </p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.location?.name || 'N/A'
                                        }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Purchase
                                        Cost</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">₹{{ (asset.purchase_cost ||
                                        0).toLocaleString() }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Purchase
                                        date</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.purchase_date.split('T')[0] || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Vendor</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.vendor?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Quantity</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.quantity || '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 h-fit">
                        <Link :href="route('admin.assets.label', asset.id)"
                            class="h-11 px-4 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center gap-2 cursor-pointer">
                            <PrinterIcon class="w-4 h-4" /> Label
                        </Link>
                        <button @click="showEditModal = true"
                            class="h-11 px-4 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-700 hover:border-indigo-200 hover:text-indigo-700 transition-all flex items-center gap-2 cursor-pointer">
                            <PencilSquareIcon class="w-4 h-4" /> Edit
                        </button>
                        <button v-if="asset.status !== 'Available'" @click="markAvailable"
                            class="h-11 px-4 bg-teal-50 border border-teal-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-teal-700 hover:bg-teal-100 transition-all flex items-center gap-2 cursor-pointer">
                            <CheckCircleIcon class="w-4 h-4" /> Mark Available
                        </button>
                        <button @click="showAssignModal = true"
                            class="h-11 px-4 bg-indigo-50 border border-indigo-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-indigo-700 hover:bg-indigo-100 transition-all flex items-center gap-2 cursor-pointer">
                            <UserPlusIcon class="w-4 h-4" /> Assign
                        </button>
                        <button @click="returnAsset"
                            class="h-11 px-4 bg-amber-50 border border-amber-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-amber-700 hover:bg-amber-100 transition-all flex items-center gap-2 cursor-pointer">
                            <ArrowUturnLeftIcon class="w-4 h-4" /> Return
                        </button>
                        <button @click="showServiceModal = true"
                            class="h-11 px-4 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-emerald-700 hover:bg-emerald-100 transition-all flex items-center gap-2 cursor-pointer">
                            <WrenchScrewdriverIcon class="w-4 h-4" /> Maintenance
                        </button>
                        <button @click="openDeleteConfirm"
                            class="h-11 px-4 bg-rose-50 border border-rose-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-rose-700 hover:bg-rose-100 transition-all flex items-center gap-2 cursor-pointer">
                            <TrashIcon class="w-4 h-4" /> Delete
                        </button>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">Lifecycle Timeline</h2>
                <div class="space-y-4">
                    <div v-for="(event, idx) in timeline" :key="idx"
                        class="p-5 border border-slate-100 rounded-2xl bg-slate-50/50">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-black text-slate-900">{{ event.title }}</p>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ event.date
                                || 'N/A' }}</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-2">{{ event.description || 'No description' }}</p>
                        <span
                            class="inline-flex mt-3 px-3 py-1 rounded-lg text-[9px] font-bold uppercase tracking-widest border"
                            :class="getStatusStyles(event.status || asset.status)">
                            {{ event.status || 'Event' }}
                        </span>
                    </div>
                    <div v-if="!timeline || !timeline.length"
                        class="py-10 text-center text-slate-400 text-[11px] font-bold uppercase tracking-widest">
                        No timeline events yet
                    </div>
                </div>
            </section>
        </div>

        <PremiumModal :show="showServiceModal" @close="showServiceModal = false" title="Add Maintenance Log"
            subtitle="Record maintenance details for this asset">
            <form @submit.prevent="submitService" class="relative overflow-hidden space-y-6 p-2 sm:p-3 text-left">
                <!-- Ambient Effects -->
                <div
                    class="absolute -top-24 -right-24 w-72 h-72 bg-amber-500/10 rounded-full blur-[120px] pointer-events-none">
                </div>

                <div
                    class="absolute -bottom-24 -left-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none">
                </div>

                <!-- Maintenance Overview -->
                <div
                    class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-5 shadow-sm">

                    <div class="absolute -right-20 -top-20 w-56 h-56 bg-amber-100/50 rounded-full blur-[90px]">
                    </div>

                    <div class="relative z-10 flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-2xl border border-amber-100 bg-white flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                            <WrenchScrewdriverIcon class="w-7 h-7" />
                        </div>

                        <div class="min-w-0">
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-amber-500 block mb-2">
                                Maintenance Registry
                            </span>

                            <h3 class="text-lg font-black text-slate-950 uppercase tracking-tight">
                                Asset Maintenance Log
                            </h3>

                            <p class="text-[11px] text-slate-400 font-semibold mt-1">
                                Track servicing, repairs, upgrades and operational maintenance.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Grid -->
                <div class="space-y-6">

                    <!-- Maintenance Type -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 px-1">
                            <div
                                class="w-10 h-10 rounded-xl border border-amber-100 bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                                <ClipboardDocumentCheckIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Maintenance Type"
                                    class="text-[12px] font-black uppercase tracking-[0.1em]" />

                                <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                    Select the maintenance operation category
                                </p>
                            </div>
                        </div>

                        <BaseSelect v-model="serviceForm.type"
                            class="w-full h-12 rounded-2xl border-slate-200 bg-white px-4 py-2.5 focus:bg-white focus:border-amber-500 text-[14px] font-bold appearance-none outline-none">
                            <option value="Repair">Repair</option>
                            <option value="Upgrade">Upgrade</option>
                            <option value="Routine_Service">Routine Service</option>
                        </BaseSelect>

                        <InputError :message="serviceForm.errors.type" />
                    </div>

                    <!-- Description -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 px-1">
                            <div
                                class="w-10 h-10 rounded-xl border border-indigo-100 bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                                <DocumentTextIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Maintenance Description"
                                    class="text-[12px] font-black uppercase tracking-[0.1em]" />

                                <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                    Enter detailed maintenance notes
                                </p>
                            </div>
                        </div>

                        <textarea v-model="serviceForm.description" rows="4"
                            placeholder="Describe the maintenance activity, repair details, replaced parts, observations etc..."
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-[14px] font-semibold text-slate-700 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 resize-none transition-all"></textarea>

                        <InputError :message="serviceForm.errors.description" />
                    </div>

                    <!-- Cost & Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Cost -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 px-1">
                                <div
                                    class="w-10 h-10 rounded-xl border border-emerald-100 bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                                    <BanknotesIcon class="w-5 h-5" />
                                </div>

                                <div>
                                    <InputLabel value="Maintenance Cost"
                                        class="text-[12px] font-black uppercase tracking-[0.2em]" />

                                    <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                        Total maintenance expenditure
                                    </p>
                                </div>
                            </div>

                            <TextInput v-model="serviceForm.cost" type="number" min="0" step="0.01" placeholder="0.00"
                                class="w-full h-12 rounded-2xl border-slate-200 bg-white px-4 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-[14px] font-bold" />

                            <InputError :message="serviceForm.errors.cost" />
                        </div>

                        <!-- Service Date -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 px-1">
                                <div
                                    class="w-10 h-10 rounded-xl border border-violet-100 bg-violet-50 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                                    <CalendarDaysIcon class="w-5 h-5" />
                                </div>

                                <div>
                                    <InputLabel value="Service Date"
                                        class="text-[12px] font-black uppercase tracking-[0.2em]" />

                                    <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                        Date when maintenance was performed
                                    </p>
                                </div>
                            </div>

                            <TextInput v-model="serviceForm.service_date" type="date"
                                class="w-full h-12 rounded-2xl border-slate-200 bg-white px-4 focus:bg-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 text-[14px] font-bold" />

                            <InputError :message="serviceForm.errors.service_date" />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-5 border-t border-slate-100">

                    <button type="button" @click="showServiceModal = false"
                        class="h-10 px-3 rounded-2xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-[0.22em] text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all duration-300 cursor-pointer">
                        Cancel
                    </button>

                    <button type="submit" :disabled="serviceForm.processing"
                        class="h-10 px-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-black uppercase tracking-[0.1em] shadow-lg shadow-amber-500/20 hover:shadow-amber-500/30 hover:scale-[1.01] transition-all duration-300 flex items-center justify-center cursor-pointer">
                        <ArrowPathIcon v-if="serviceForm.processing" class="w-4 h-4 animate-spin mr-2" />

                        <ShieldCheckIcon v-else class="w-4 h-4 mr-2" />

                        {{
                            serviceForm.processing
                                ? 'Saving...'
                                : 'Save Maintenance'
                        }}
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showEditModal" @close="showEditModal = false" title="Edit Asset"
            subtitle="Update core asset details">
            <form @submit.prevent="submitEdit" class="relative overflow-hidden space-y-7 text-left">
                <!-- Ambient Effects -->
                <div
                    class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none">
                </div>

                <div
                    class="absolute -bottom-24 -left-24 w-72 h-72 bg-sky-400/10 rounded-full blur-[120px] pointer-events-none">
                </div>

                <!-- Asset Identity Card -->
                <div
                    class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-5 shadow-sm">

                    <div class="absolute -right-20 -top-20 w-56 h-56 bg-indigo-100/50 rounded-full blur-[90px]">
                    </div>

                    <div class="relative z-10 flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-2xl border border-indigo-100 bg-white flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                            <CpuChipIcon class="w-7 h-7" />
                        </div>

                        <div class="min-w-0">
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-indigo-400 block mb-2">
                                Asset Registry
                            </span>

                            <h3 class="text-lg font-black text-slate-950 uppercase tracking-tight truncate">
                                {{ editForm.name || 'Asset Resource' }}
                            </h3>

                            <p class="text-[11px] text-slate-400 font-semibold mt-1">
                                Modify asset metadata and operational status.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">

                    <!-- Name -->
                    <div class="md:col-span-2 space-y-3">
                        <InputLabel value="Asset Name"
                            class="px-1 text-[12px] font-black uppercase tracking-[0.2em] text-slate-500" />

                        <TextInput v-model="editForm.name"
                            class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-indigo-500 px-3 focus:ring-4 focus:ring-indigo-500/10 text-[14px] font-bold tracking-wide"
                            placeholder="Enter asset name" />

                        <InputError :message="editForm.errors.name" />
                    </div>

                    <!-- Category -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 px-1">
                            <div
                                class="w-10 h-10 rounded-xl border border-sky-100 bg-sky-50 flex items-center justify-center text-sky-600 shadow-sm shrink-0">
                                <Squares2X2Icon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Category"
                                    class="text-[12px] font-black uppercase tracking-[0.2em]" />

                                <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                    Asset classification group
                                </p>
                            </div>
                        </div>

                        <BaseSelect v-model="editForm.category_id"
                            class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 text-[14px] font-bold">
                            <option value="">Select Category</option>

                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </BaseSelect>

                        <InputError :message="editForm.errors.category_id" />
                    </div>

                    <!-- Serial -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 px-1">
                            <div
                                class="w-10 h-10 rounded-xl border border-violet-100 bg-violet-50 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                                <QrCodeIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Serial Number"
                                    class="text-[12px] font-black uppercase tracking-[0.2em]" />

                                <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                    Unique tracking identifier
                                </p>
                            </div>
                        </div>
                        <TextInput v-model="editForm.serial_number"
                            class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 text-[14px] px-3  font-bold"
                            placeholder="Enter serial number" />

                        <InputError :message="editForm.errors.serial_number" />
                    </div>



                    <div class="space-y-3">
                        <div class="flex items-center gap-3 px-1">
                            <div
                                class="w-10 h-10 rounded-xl border border-violet-100 bg-violet-50 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                                <QrCodeIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Vendor"
                                    class="text-[12px] font-black uppercase tracking-[0.2em]" />

                                <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                    provider of the asset
                                </p>
                            </div>
                        </div>

                        <BaseSelect v-model="editForm.vendor_id"
                            class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 text-[14px] px-3 font-bold">
                            <option value="">Select vendor</option>
                            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                                {{ vendor.name }}
                            </option>
                        </BaseSelect>

                        <InputError :message="editForm.errors.vendor_id" />
                    </div>

                    <!-- Section: Secondary Details -->
                        <div class="space-y-10 bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-sm">
                            <div class="flex items-center gap-6 border-b border-white">
                                <TagIcon class="w-8 h-8 text-indigo-400 shadow-indigo-500/10" />
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">
                                    Details
                                </h3>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <label
                                        class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Brand
                                        Name</label>
                                    <input v-model="editForm.make" type="text"
                                        class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all shadow-sm"
                                        placeholder="SHIPPING BRAND..." />
                                </div>
                                <div class="space-y-3">
                                    <label
                                        class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Model
                                        Code</label>
                                    <input v-model="editForm.model" type="text"
                                        class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all shadow-sm"
                                        placeholder="VERSION X.0..." />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-10 bg-emerald-50/20 p-4 rounded-2xl border border-emerald-50 shadow-sm">
                            <div class="flex items-center gap-3 border-b border-white">
                                <BanknotesIcon class="w-8 h-8 text-emerald-500 shadow-emerald-500/10" />
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">
                                    Financials
                                </h3>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <label
                                        class="px-1 text-[13px] font-bold text-emerald-600 uppercase tracking-widest leading-none">Buy
                                        Cost (₹)</label>
                                    <input v-model="editForm.purchase_cost" type="number"
                                        class="w-full h-14 bg-white border border-slate-200 rounded-xl px-4 text-base font-black focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:shadow-md outline-none transition-all shadow-sm tabular-nums"
                                        placeholder="0.00" />
                                </div>
                                <div class="space-y-3">
                                    <label
                                        class="px-1 text-[13px] font-bold text-emerald-600 uppercase tracking-widest leading-none">Entry
                                        Date</label>
                                    <input v-model="editForm.purchase_date" type="date"
                                        class="w-full h-14 bg-white border border-slate-200 rounded-xl px-4 text-xs font-black text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all shadow-sm" />
                                </div>
                            </div>
                        </div>

                    <!-- Status -->
                    <div class="md:col-span-2 space-y-3">
                        <div class="flex items-center gap-3 px-1">
                            <div
                                class="w-10 h-10 rounded-xl border border-emerald-100 bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                                <ShieldCheckIcon class="w-5 h-5" />
                            </div>

                            <div>
                                <InputLabel value="Asset Status"
                                    class="text-[12px] font-black uppercase tracking-[0.2em]" />

                                <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                    Operational lifecycle state
                                </p>
                            </div>
                        </div>

                        <BaseSelect v-model="editForm.status"
                            class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-[14px] font-bold">
                            <option value="Available">Available</option>
                            <option value="Assigned">Assigned</option>
                            <option value="In_Service">In Service</option>
                            <option value="Scrapped">Scrapped</option>
                            <option value="Lost">Lost</option>
                            <option value="Draft">Draft</option>
                        </BaseSelect>

                        <InputError :message="editForm.errors.status" />
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 border-t border-slate-100">

                    <button type="button" @click="showEditModal = false"
                        class="h-11 px-6 rounded-2xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all duration-300 cursor-pointer ">
                        Cancel
                    </button>

                    <button type="submit" :disabled="editForm.processing"
                        class="h-12 px-8 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-[11px] font-black uppercase tracking-[0.22em] shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 hover:scale-[1.01] transition-all duration-300 flex items-center justify-center cursor-pointer">
                        <ArrowPathIcon v-if="editForm.processing" class="w-4 h-4 animate-spin mr-2" />

                        <CheckCircleIcon v-else class="w-4 h-4 mr-2" />

                        {{
                            editForm.processing
                                ? 'Updating...'
                                : 'Update Asset'
                        }}
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showAssignModal" @close="showAssignModal = false" title="Assign Asset"
            subtitle="Select user to hand over this asset">
            <form @submit.prevent="submitAssign" class="p-6 space-y-5 text-left">
                <div>
                    <InputLabel value="Assign To" />
                    <BaseSelect v-model="assignForm.user_id">
                        <option value="">Select User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </BaseSelect>
                    <InputError :message="assignForm.errors.user_id" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAssignModal = false"
                        class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-500">Cancel</button>
                    <button type="submit" :disabled="assignForm.processing"
                        class="h-10 px-5 bg-indigo-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest">
                        {{ assignForm.processing ? 'Assigning...' : 'Assign Asset' }}
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showDeleteModal" @close="closeDeleteConfirm" title="Delete Asset"
            subtitle="This action cannot be undone" max-width="lg">
            <div class="p-1 text-left space-y-6">
                <div class="rounded-[1.5rem] border border-rose-100 bg-rose-50/70 p-3 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-white border border-rose-100 flex items-center justify-center text-rose-600 shadow-sm shrink-0">
                            <TrashIcon class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-black uppercase tracking-widest text-slate-900">
                                Remove this asset?
                            </h4>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                                {{ asset.name }} will be permanently deleted from the system.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                    <button type="button" @click="closeDeleteConfirm"
                        class="h-10 px-5 rounded-2xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-[0.22em] text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-all duration-300 cursor-pointer">
                        Cancel
                    </button>

                    <button type="button" @click="deleteAsset"
                        class="h-10 px-5 rounded-2xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-[0.22em] shadow-lg shadow-rose-500/20 hover:bg-rose-700 transition-all duration-300 cursor-pointer">
                        Delete Asset
                    </button>
                </div>
            </div>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
