<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import PremiumModal from "@/Components/PremiumModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import BaseSelect from "@/Components/BaseSelect.vue";
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";
import {
    ChartBarIcon,
    BuildingStorefrontIcon,
    BuildingStorefrontIcon as LibraryIcon,
    TableCellsIcon as TableIcon,
    ShoppingCartIcon,
    CogIcon,
    ExclamationTriangleIcon,
    MagnifyingGlassIcon,
    CpuChipIcon,
    ServerStackIcon,
    WrenchScrewdriverIcon,
    CubeIcon,
    BanknotesIcon,
    InformationCircleIcon,
    AdjustmentsHorizontalIcon,
    BoltIcon,
    SparklesIcon,
    ArrowPathIcon,
    ArrowUpRightIcon,
    CheckCircleIcon,
    CheckBadgeIcon,
    ArchiveBoxIcon,
    TagIcon,
    PlusIcon,
    MapPinIcon,
    ChevronRightIcon,
    TrashIcon,
    DocumentDuplicateIcon,
    DocumentTextIcon,
    PhoneIcon,
    EnvelopeIcon,
    UserIcon,
    CalendarDaysIcon,
    PencilSquareIcon,
    ArrowUturnLeftIcon,
    UserPlusIcon,
    PrinterIcon,
    EyeIcon,
} from '@heroicons/vue/24/solid';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { useToastStore } from "@/stores/toast";

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: { type: String, default: 'stats' },
    stats: Object,
    assets: [Object, Array],
    vendors: Array,
    procurement: Array,
    requests: Array,
    categories: Array,
    locations: Array,
    statuses: Array,
    users: Array,
});

const vendorSearch = ref('');
const showDeleteModal = ref(false);
const showDeleteRequestModal = ref(false);
const showDeleteVendorModal = ref(false);
const vendorToDelete = ref(null);
const assetToDelete = ref(null);
const requestToDelete = ref(null);
const showModal = ref(false);
const showRejectModal = ref(false);
const selectedRequest = ref(null);
const editingRequest = ref(null);
const editingVendor = ref(null);
const toast = useToastStore();
const showVendorModal = ref(false);
const showAssignModal = ref(false);
const showBulkMaintenanceModal = ref(false);
const selectedAssets = ref([]);
const bulkDeleteMode = ref(false);
const showLocationModal = ref(false);
const selectedLocationItem = ref(null);
const allLocationAssets = computed(() => {
    if (Array.isArray(props.assets)) return props.assets;
    return props.assets?.data ?? [];
});

const selectedLocationVerifiedAssets = computed(() => {
    return selectedLocationItem.value?.assets ?? [];
});

const selectedLocationMissingAssets = computed(() => {
    const locationId = selectedLocationItem.value?.id;
    if (!locationId) return [];

    return allLocationAssets.value.filter(asset => asset.location_id !== locationId);
});

const locationAssetStats = computed(() => {
    const assets = selectedLocationVerifiedAssets.value;
    const countByStatus = (status) => assets.filter(asset => (asset.status || '').toLowerCase() === status).length;

    return {
        total: assets.length,
        lost: countByStatus('lost'),
        inService: countByStatus('in_service'),
        assigned: countByStatus('assigned'),
    };
});

const getAssetStatusClasses = (status) => {
    switch ((status || '').toLowerCase()) {
        case 'available':
            return 'border-emerald-200 bg-emerald-50 text-emerald-700';
        case 'assigned':
            return 'border-indigo-200 bg-indigo-50 text-indigo-700';
        case 'in_service':
            return 'border-amber-200 bg-amber-50 text-amber-700';
        case 'scrapped':
            return 'border-slate-200 bg-slate-50 text-slate-700';
        case 'lost':
            return 'border-rose-200 bg-rose-50 text-rose-700';
        case 'draft':
            return 'border-slate-200 bg-slate-50 text-slate-500';
        default:
            return 'border-slate-200 bg-slate-50 text-slate-600';
    }
};

const getAssetStatusCardClasses = (status) => {
    switch ((status || '').toLowerCase()) {
        case 'available':
            return 'border-emerald-100 bg-emerald-50/70';
        case 'assigned':
            return 'border-indigo-100 bg-indigo-50/70';
        case 'in_service':
            return 'border-amber-100 bg-amber-50/70';
        case 'scrapped':
            return 'border-slate-200 bg-slate-50/70';
        case 'lost':
            return 'border-rose-100 bg-rose-50/70';
        case 'draft':
            return 'border-slate-100 bg-slate-50/70';
        default:
            return 'border-slate-100 bg-slate-50/70';
    }
};
const vendorForm = useForm({
    name: '',
    category: '',
    gstin: '',
    vendor_type: '',
    status: '1',
    sla_response_hours: '',
    contact_person: '',
    email: '',
    phone: '',
    contract_end_date: '',
});
const form = useForm({
    user_id: '',
    item: '',
    category: '',
    priority: 'Normal',
    reason: ''
});

const rejectForm = useForm({
    rejection_reason: ''
});

const assignForm = useForm({
    user_id: '',
    asset_ids: [],
});

const serviceForm = useForm({
    asset_ids: [],
    type: 'Repair',
    description: '',
    cost: 0,
    service_date: new Date().toISOString().split('T')[0],
});

const clearSelectedAssets = () => {
    selectedAssets.value = [];
};

const requireSelectedAssets = (actionLabel) => {
    if (!selectedAssets.value.length) {
        toast.error(`Select at least one asset to ${actionLabel}.`);
        return false;
    }

    return true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    assignForm.reset();
    assignForm.clearErrors();
};

const openBulkAssignModal = () => {
    if (!requireSelectedAssets('assign')) return;

    const selectedList = props.assets?.data?.filter(a => selectedAssets.value.includes(a.id)) || [];
    const nonAvailable = selectedList.filter(a => a.status !== 'Available');
    if (nonAvailable.length > 0) {
        toast.error(`Only assets with status "Available" can be bulk assigned. Non-available selected: ${nonAvailable.map(a => a.name).join(', ')}.`);
        return;
    }

    assignForm.reset();
    assignForm.clearErrors();
    showAssignModal.value = true;
};

const submitAssign = () => {
    if (!requireSelectedAssets('assign')) return;

    assignForm.asset_ids = [...selectedAssets.value];
    assignForm.post(route('admin.assets.bulk-assign.process'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Assigned ${selectedAssets.value.length} assets successfully.`);
            closeAssignModal();
            clearSelectedAssets();
        },
        onError: () => {
            toast.error('Unable to assign selected assets.');
        },
    });
};

const makeBulkAvailable = () => {
    if (!requireSelectedAssets('mark available')) return;

    router.post(route('admin.assets.bulk-available'), {
        asset_ids: [...selectedAssets.value]
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Marked selected assets as Available.`);
            clearSelectedAssets();
        },
        onError: () => {
            toast.error('Failed to mark selected assets as Available.');
        }
    });
};

const openBulkMaintenanceModal = () => {
    if (!requireSelectedAssets('mark maintenance on')) return;

    serviceForm.reset();
    serviceForm.clearErrors();
    serviceForm.asset_ids = [...selectedAssets.value];
    showBulkMaintenanceModal.value = true;
};

const closeBulkMaintenanceModal = () => {
    showBulkMaintenanceModal.value = false;
    serviceForm.reset();
    serviceForm.clearErrors();
};

const submitBulkMaintenance = () => {
    if (!requireSelectedAssets('mark maintenance on')) return;

    serviceForm.asset_ids = [...selectedAssets.value];
    serviceForm.post(route('admin.assets.bulk-maintenance'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Maintenance logged for ${selectedAssets.value.length} assets.`);
            closeBulkMaintenanceModal();
            clearSelectedAssets();
        },
        onError: () => {
            toast.error('Unable to add maintenance for selected assets.');
        },
    });
};

const returnSelectedAssets = () => {
    if (!requireSelectedAssets('return')) return;

    router.post(route('admin.assets.bulk-return'), {
        asset_ids: [...selectedAssets.value],
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Returned ${selectedAssets.value.length} assets successfully.`);
            clearSelectedAssets();
        },
        onError: () => {
            toast.error('Unable to return selected assets.');
        },
    });
};
const openVendorModal = () => {
    editingVendor.value = null;
    showVendorModal.value = true;
    vendorForm.reset();
    vendorForm.clearErrors();
};

const filteredAssets = computed(() => {
    return props.assets?.data || [];
});

const areAllSelected = computed(() => {
    const list = filteredAssets.value;
    if (list.length === 0) return false;
    return list.every(a => selectedAssets.value.includes(a.id));
});

const toggleSelectAll = (event) => {
    const list = filteredAssets.value;
    if (event.target.checked) {
        list.forEach(a => {
            if (!selectedAssets.value.includes(a.id)) {
                selectedAssets.value.push(a.id);
            }
        });
    } else {
        const idsToRemove = list.map(a => a.id);
        selectedAssets.value = selectedAssets.value.filter(id => !idsToRemove.includes(id));
    }
};

const closeVendorModal = () => {
    showVendorModal.value = false;
    editingVendor.value = null;
    vendorForm.reset();
    vendorForm.clearErrors();
};

const openEdit = (vendor) => {
    editingVendor.value = vendor;
    vendorForm.clearErrors();
    vendorForm.name = vendor.name || '';
    vendorForm.category = vendor.category || '';
    vendorForm.gstin = vendor.gstin || '';
    vendorForm.vendor_type = vendor.vendor_type || '';
    vendorForm.status = vendor.is_active == 1 ? '1' : '0';
    vendorForm.sla_response_hours = vendor.sla_response_hours ?? '';
    vendorForm.contact_person = vendor.contact_person || '';
    vendorForm.email = vendor.email || '';
    vendorForm.phone = vendor.phone || '';
    vendorForm.contract_end_date = vendor.contract_end_date || '';
    showVendorModal.value = true;
};

const openCreateModal = () => {
    editingRequest.value = null;
    showModal.value = true;
    form.clearErrors();
};

const closeCreateModal = () => {
    showModal.value = false;
    editingRequest.value = null;
    form.reset();
    form.clearErrors();
};

const openRequestEdit = (request) => {
    editingRequest.value = request;
    form.clearErrors();
    form.user_id = request.user_id || request.user?.id || '';
    form.item = request.item || '';
    form.category = request.category?.name || request.category || '';
    form.priority = request.priority || 'Normal';
    form.reason = request.reason || '';
    showModal.value = true;
};

const openRejectModal = (request) => {
    selectedRequest.value = request;
    rejectForm.clearErrors();
    rejectForm.reset();
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedRequest.value = null;
    rejectForm.reset();
    rejectForm.clearErrors();
};

const submitVendor = () => {
    const isEditing = !!editingVendor.value;

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            toast.success(isEditing ? 'Vendor updated successfully!' : 'Vendor added successfully!');
            closeVendorModal();
        },

        onError: () => {
            toast.error(isEditing ? 'Unable to update vendor.' : 'Unable to add vendor.');
        }
    };

    if (isEditing) {
        vendorForm.put(route('admin.assets.vendors.update', editingVendor.value.id), options);
        return;
    }

    vendorForm.post(route('admin.assets.vendors.store'), options);
};

const filteredVendors = computed(() => {
    const query = vendorSearch.value.trim().toLowerCase();

    if (!query) return props.vendors || [];

    return (props.vendors || []).filter((vendor) => {
        const fields = [
            vendor.name,
            vendor.category,
            vendor.gstin,
            vendor.email,
            vendor.phone,
            vendor.contact_person,
            vendor.vendor_type,
            vendor.sla_response_hours,
            vendor.rating,
        ];

        return fields.some((field) => String(field ?? '').toLowerCase().includes(query));
    });
});

const submit = () => {
    const isEditing = !!editingRequest.value;

    const onSuccess = () => {
        toast.success(isEditing ? "Request updated successfully!" : "Request created successfully!");
        closeCreateModal();
    };

    const onError = () => {
        toast.error(isEditing ? "Unable to update request." : "Unable to create request.");
    };

    if (isEditing) {
        form.put(route('admin.assets.asset-requests.update', { assetRequest: editingRequest.value.id }), {
            preserveScroll: true,
            onSuccess,
            onError,
        });
        return;
    }

    form.post(route('admin.assets.asset-requests.store'), {
        preserveScroll: true,
        onSuccess,
        onError,
    });
};


const handleVendorSearch = (query) => {
    vendorSearch.value = query;
};

const getRequestStatusClasses = (status) => {
    switch ((status || '').toLowerCase()) {
        case 'approved':
            return 'text-emerald-700 border-emerald-100';
        case 'pending':
            return 'text-amber-700 border-amber-100';
        case 'rejected':
            return 'text-rose-700 border-rose-100';
        case 'fulfilled':
            return 'text-blue-700 border-blue-100';
        default:
            return 'text-slate-700 border-slate-200';
    }
};

// --- Filter Logic ---
const filterForm = ref({
    search: props.filters?.search || '',
    category_id: props.filters?.category_id || '',
    status: props.filters?.status || '',
    location_id: props.filters?.location_id || ''
});

const applyFilters = () => {
    router.get(route('admin.assets.dashboard', { view: 'list' }), filterForm.value, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

const resetFilters = () => {
    filterForm.value = { search: '', category_id: '', status: '', location_id: '' };
    applyFilters();
};

// Tab Navigation
const tabs = [
    { id: 'stats', label: 'Quick Look', icon: ChartBarIcon },
    { id: 'requests', label: 'Requests', icon: ArchiveBoxIcon },
    { id: 'vendors', label: 'Store List', icon: LibraryIcon },
    { id: 'list', label: 'Master List', icon: TableIcon },
    { id: 'locations', label: 'Locations', icon: MapPinIcon }
];

const switchTab = (id) => {
    // if (id === 'locations') {
    //     router.get(route('admin.assets.location-nodes.index'));
    //     return;
    // }
    router.get(route('admin.assets.dashboard'), { view: id }, { preserveState: true, replace: true, only: ['tab', 'stats', 'assets', 'vendors', 'procurement', 'categories', 'filters', 'requests', 'users', 'locations'] });
};

const openDeleteConfirm = (asset) => {
    bulkDeleteMode.value = false;
    assetToDelete.value = asset;
    showDeleteModal.value = true;
};

const openBulkDeleteConfirm = () => {
    if (!requireSelectedAssets('delete')) return;

    bulkDeleteMode.value = true;
    assetToDelete.value = null;
    showDeleteModal.value = true;
};

const closeDeleteConfirm = () => {
    showDeleteModal.value = false;
    assetToDelete.value = null;
    bulkDeleteMode.value = false;
};

const openDeleteRequestConfirm = (request) => {
    requestToDelete.value = request;
    showDeleteRequestModal.value = true;
};

const closeDeleteRequestConfirm = () => {
    showDeleteRequestModal.value = false;
    requestToDelete.value = null;
};

const openDeleteVendorConfirm = (vendor) => {
    vendorToDelete.value = vendor;
    showDeleteVendorModal.value = true;
};

const openLocationModal = (location) => {
    selectedLocationItem.value = location;
    showLocationModal.value = true;
};

const closeLocationModal = () => {
    showLocationModal.value = false;
    selectedLocationItem.value = null;
};

const closeDeleteVendorConfirm = () => {
    showDeleteVendorModal.value = false;
    vendorToDelete.value = null;
};


const deleteAsset = () => {
    if (bulkDeleteMode.value) {
        if (!requireSelectedAssets('delete')) return;

        router.delete(route('admin.assets.bulk-destroy'), {
            data: { asset_ids: [...selectedAssets.value] },
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`Deleted ${selectedAssets.value.length} assets successfully.`);
                closeDeleteConfirm();
                clearSelectedAssets();
            },
            onError: () => {
                toast.error('Unable to delete selected assets.');
                closeDeleteConfirm();
            }
        });
        return;
    }

    if (!assetToDelete.value) return;

    router.delete(route('admin.assets.destroy', assetToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteConfirm();
        },
        onError: () => {
            closeDeleteConfirm();
        }
    });
};

const deleteRequest = () => {
    if (!requestToDelete.value) return;

    router.delete(route('admin.assets.asset-requests.destroy', requestToDelete.value.id), {
        onSuccess: () => {
            toast.success('Request deleted successfully.');
            closeDeleteRequestConfirm();
        },
        onError: () => {
            toast.error('Unable to delete request.');
            closeDeleteRequestConfirm();
        }
    });
};

const deleteVendor = () => {
    if (!vendorToDelete.value) return;

    router.delete(route('admin.assets.vendors.destroy', vendorToDelete.value.id), {
        onSuccess: () => {
            toast.success('Vendor deleted successfully.');
            closeDeleteVendorConfirm();
        },
        onError: () => {
            toast.error('Unable to delete vendor.');
            closeDeleteVendorConfirm();
        }
    });
};
const rejectRequest = () => {
    if (!selectedRequest.value) return;

    rejectForm.post(route('admin.assets.asset-requests.reject', { assetRequest: selectedRequest.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Request rejected.');
            closeRejectModal();
        },
        onError: () => {
            toast.error('Unable to reject request.');
        }
    });
};

const approveRequest = (request) => {
    router.post(route('admin.assets.asset-requests.approve', { assetRequest: request.id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Request approved.');
        },
        onError: () => {
            toast.error('Unable to approve request.');
        }
    });
};

const fulfillRequest = (request) => {
    router.post(route('admin.assets.asset-requests.fulfill', { assetRequest: request.id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Request fulfilled.');
        },
        onError: () => {
            toast.error('Unable to fulfill request.');
        }
    });
};

const ReturnAsset = (request) => {
    router.put(route('admin.assets.asset-requests.return', { assetRequest: request.id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Asset marked as returned.');
        },
        onError: () => {
            toast.error('Unable to mark asset as returned.');
        }
    });
};

</script>

<template>

    <Head title="Master List" />

    <GradientHeroHeader kicker="Administration" title="Asset Matrix"
        subtitle="Manage IT infrastructure, fixed assets, and supply networks.">
        <template #right>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <Link :href="route('admin.assets.import.smart')"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 text-[10px] font-extrabold uppercase tracking-widest text-white hover:bg-white/20 active:scale-[0.97] transition-all">
                    Smart Import
                </Link>
                <Link :href="route('admin.assets.create')"
                    class="inline-flex items-center justify-center h-10 px-5 rounded-2xl bg-white text-indigo-700 text-[10px] font-extrabold uppercase tracking-widest shadow-lg shadow-black/10 hover:bg-indigo-50 active:scale-[0.97] transition-all gap-2">
                    <PlusIcon class="w-4 h-4" />
                    Add Resource
                </Link>
            </div>
        </template>
    </GradientHeroHeader>

    <div class="p-6">

        <!-- Lifecycle and Related Links -->
        <section class="relative bg-white rounded-3xl border border-slate-200 p-4 mb-8 shadow-sm overflow-hidden">
            <div
                class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px] pointer-events-none">
            </div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="text-left">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asset Lifecycle</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                        <span
                            class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700">Draft</span>
                        <span class="text-slate-300">-></span>
                        <span
                            class="px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700">Available</span>
                        <span class="text-slate-300">-></span>
                        <span
                            class="px-3 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700">Assigned</span>
                        <span class="text-slate-300">-></span>
                        <span class="px-3 py-1 rounded-lg bg-amber-50 border border-amber-100 text-amber-700">In
                            Service</span>
                        <span class="text-slate-300">-></span>
                        <span
                            class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700">Returned</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 mt-5 justify-end">
                <Link :href="route('admin.assets.audit.run')"
                    class="relative z-20 h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center cursor-pointer">
                    Blind Audit</Link>
                <Link :href="route('admin.assets.maintenance.index')"
                    class="relative z-20 h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center cursor-pointer">
                    Maintenance</Link>
                <Link :href="route('admin.inventory.dashboard', { view: 'list' })"
                    class="relative z-20 h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center cursor-pointer">
                    Store Master List</Link>
                <Link :href="route('admin.physical-documents.index')"
                    class="relative z-20 h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center text-center cursor-pointer">
                    Physical Docs</Link>
            </div>
        </section>

        <!-- Enhanced Tactical Tab Bar -->
        <div
            class="flex items-center gap-2 mb-8 p-1.5 bg-slate-100/50 border border-slate-200 rounded-2xl w-fit overflow-x-auto max-w-full relative z-10">
            <button v-for="t in tabs" :key="t.id" @click="switchTab(t.id)"
                class="h-10 px-6 text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all flex items-center gap-3 cursor-pointer shrink-0"
                :class="tab === t.id ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:bg-white/50 hover:shadow-sm hover:border-slate-300 '">
                <component :is="t.icon" class="w-4 h-4" />
                {{ t.label }}
            </button>
        </div>

        <!-- Dynamic Content Engine -->
        <div class="relative min-h-[100px]">

            <div v-if="tab === 'requests'" class="animate-in slide-in-from-bottom-4 duration-500">
                <button type="button" @click="openCreateModal"
                    class="float-right mb-6 h-10 px-2 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-1 cursor-pointer active:scale-95">
                    <PlusIcon class="w-4 h-4" />
                    <span>New Request</span>
                </button>
                <div
                    class="relative z-10 w-full bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[240px]">
                    <BaseDataTable :data="requests" @search="handleRequestSearch" :columns="[
                        { key: 'requester', label: 'Requester', sortable: true, align: 'left' },
                        { key: 'item', label: 'Item', sortable: true, align: 'left' },
                        { key: 'category', label: 'Category', sortable: true, align: 'left' },
                        { key: 'priority', label: 'Priority', sortable: true, align: 'left' },
                        { key: 'reason', label: 'Reason', sortable: false, align: 'left' },
                        { key: 'status', label: 'Status', sortable: true, align: 'left' },
                        { key: 'actions', label: 'Actions', sortable: false, align: 'center' },
                        { key: 'operations', label: 'Operations', sortable: false, align: 'center' }
                    ]" search-placeholder="Search requests...">

                        <!-- Requester -->
                        <template #cell-requester="{ item: row }">
                            <div class="flex items-center gap-4 py-1">
                                <div
                                    class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-black text-sm shrink-0 border border-indigo-100">
                                    {{ row.user?.name?.charAt(0) || 'U' }}
                                </div>

                                <div class="text-left">
                                    <div
                                        class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">
                                        {{ row.user?.name || 'Unknown' }}
                                    </div>

                                    <div
                                        class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 leading-none">
                                        Request Initiator
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Item -->
                        <template #cell-item="{ item: row }">
                            <div class="text-left">
                                <div class="text-[11px] font-black text-slate-800 uppercase tracking-wide">
                                    <span v-if="row.is_custom">
                                        {{ row.custom_name }} (Qty: {{ row.custom_qty }})
                                    </span>
                                    <span v-else>
                                        {{ row.item || 'N/A' }}
                                    </span>
                                </div>
                                <div v-if="row.is_custom && row.custom_details" class="text-[10px] text-slate-400 mt-0.5 normal-case font-normal">
                                    {{ row.custom_details }}
                                </div>
                            </div>
                        </template>

                        <!-- Category -->
                        <template #cell-category="{ item: row }">
                            <div class="text-left text-[11px] font-bold text-slate-600 uppercase tracking-widest">
                                <span v-if="row.is_custom" class="px-2 py-0.5 rounded bg-purple-50 text-purple-600 border border-purple-100 text-[9px] font-black">Custom Request</span>
                                <span v-else>{{ row.category?.name || 'N/A' }}</span>
                            </div>
                        </template>

                        <!-- Priority -->
                        <template #cell-priority="{ item: row }">
                            <span
                                class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-1 text-[9px] font-black uppercase tracking-[0.18em] text-slate-700">
                                {{ row.priority }}
                            </span>
                        </template>

                        <!-- Reason -->
                        <template #cell-reason="{ item: row }">
                            <div class="max-w-[260px] text-[11px] text-slate-600 leading-relaxed">
                                {{ row.reason || 'No reason provided' }}
                            </div>
                        </template>

                        <!-- Status -->
                        <template #cell-status="{ item: row }">
                            <span
                                class="inline-flex items-center rounded-xl border px-3 py-1 text-[9px] font-black uppercase tracking-[0.18em]"
                                :class="getRequestStatusClasses(row.status)">
                                {{ row.status }}
                            </span>
                        </template>

                        <!-- Actions -->
                        <template #cell-actions="{ item: row }">
                            <div class="flex justify-center gap-2">

                                <button v-if="row.status !== 'Fulfilled'" type="button"
                                    @click.prevent="approveRequest(row)"
                                    class="h-9 px-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-emerald-100 transition-all cursor-pointer">
                                    Approve
                                </button>

                                <button v-if="row.status !== 'Fulfilled'" type="button"
                                    @click.prevent="openRejectModal(row)"
                                    class="h-9 px-3 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all cursor-pointer">
                                    Reject
                                </button>

                                <button v-if="row.status !== 'Fulfilled'" type="button" @click="fulfillRequest(row)"
                                    class="h-9 px-3 bg-amber-50 text-amber-700 border border-amber-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-amber-100 transition-all cursor-pointer">
                                    Fulfill
                                </button>
                                <button v-if="row.status === 'Fulfilled'" type="button" @click="ReturnAsset(row)"
                                    class="h-9 px-3 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-blue-100 transition-all cursor-pointer">
                                    Returned
                                </button>
                            </div>
                        </template>

                        <!-- Operations -->
                        <template #cell-operations="{ item: row }">
                            <div class="flex justify-center gap-2">

                                <button @click="openRequestEdit(row)"
                                    class="h-10 px-3 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-1 cursor-pointer">
                                    <PencilSquareIcon class="w-4 h-4" />
                                    Edit
                                </button>

                                <button @click="openDeleteRequestConfirm(row)"
                                    class="h-10 px-3 bg-rose-500 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-600 transition-all flex items-center gap-1 cursor-pointer">
                                    <TrashIcon class="w-4 h-4" />
                                    Delete
                                </button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>
            </div>

            <!-- Add/edit req Modal -->
            <PremiumModal :show="showModal" @close="closeCreateModal"
                :title="editingRequest ? 'Edit Request' : 'New Request'" subtitle="Raise a request for review"
                icon="fa-clipboard-list" maxWidth="2xl">
                <form @submit.prevent="submit"
                    class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-gradient-to-b from-slate-50/90 to-white p-5 text-left shadow-[0_20px_50px_-20px_rgba(15,23,42,0.18)]">
                    <div
                        class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
                    </div>
                    <div
                        class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-violet-500/10 blur-3xl pointer-events-none">
                    </div>

                    <div class="relative flex items-start justify-between gap-4 border-b border-slate-200/70 pb-6">
                        <div class="space-y-2">
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-[8px] font-black uppercase tracking-[0.25em] text-indigo-600">
                                Request Intake
                            </div>
                            <div>
                                <h2 class="text-xl font-black uppercase tracking-tight text-slate-950 leading-none">
                                    {{ editingRequest ? 'Edit Request' : 'Create New Request' }}
                                </h2>
                                <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                    {{ editingRequest ? 'Update the request details before saving changes' : 'Capture the request details before approval' }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="hidden md:flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg">
                            <DocumentDuplicateIcon class="h-5 w-5" />
                        </div>
                    </div>

                    <div class="relative mt-7 grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-slate-500 shrink-0" />
                                <InputLabel class="font-black text-slate-900" value="Requester" />
                            </div>
                            <BaseSelect v-model="form.user_id"
                                class="w-full h-12 rounded-xl border-slate-200 bg-white text-[11px] font-bold tracking-wide focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none">
                                <option value="" disabled>Select requester</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name.toUpperCase() }}
                                </option>
                            </BaseSelect>
                            <InputError :message="form.errors.user_id" />
                        </div>

                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-slate-500 shrink-0" />
                                <InputLabel class="font-black text-slate-900" value="Item" />
                            </div>
                            <TextInput v-model="form.item" placeholder="Item" required
                                class="w-full h-12 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-[11px] font-bold tracking-wide transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:outline-none" />
                            <InputError :message="form.errors.item" />
                        </div>

                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-slate-500 shrink-0" />
                                <InputLabel class="font-black text-slate-900" value="Category" />
                            </div>
                            <TextInput v-model="form.category" placeholder="Category" required
                                class="w-full h-12 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-[11px] font-bold tracking-wide transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:outline-none" />
                            <InputError :message="form.errors.category" />
                        </div>

                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-slate-500 shrink-0" />
                                <InputLabel class="font-black text-slate-900" value="Priority" />
                            </div>
                            <BaseSelect v-model="form.priority"
                                class="w-full h-12 rounded-xl border-slate-200 bg-white text-[11px] font-bold tracking-wide focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none">
                                <option value="Low">Low</option>
                                <option value="Normal">Normal</option>
                                <option value="High">High</option>
                            </BaseSelect>
                            <InputError :message="form.errors.priority" />
                        </div>

                        <div class="xl:col-span-2 space-y-2.5">
                            <div class="flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-slate-500 shrink-0" />
                                <InputLabel class="font-black text-slate-900" value="Reason" />
                            </div>
                            <textarea v-model="form.reason" rows="5" placeholder="Reason for request" required
                                class="min-h-40 w-full rounded-[1.25rem] border border-slate-200 bg-slate-50/70 px-4 py-3 text-[11px] font-semibold tracking-wide text-slate-700 transition-all placeholder:text-slate-300 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:outline-none"></textarea>
                            <InputError :message="form.errors.reason" />
                        </div>
                    </div>

                    <div
                        class="relative mt-4 flex flex-col-reverse gap-3 border-t border-slate-200/70 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <button @click="closeCreateModal" type="button"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 sm:w-auto cursor-pointer">
                            Cancel
                        </button>

                        <button type="submit" :disabled="form.processing"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 text-[9px] font-black uppercase tracking-[0.2em] text-white shadow-[0_10px_25px_-10px_rgba(79,70,229,0.65)] transition-all hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 sm:w-auto cursor-pointer">
                            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
                            <CheckBadgeIcon v-else class="w-4 h-4 text-white" />
                            <span>{{ form.processing ? (editingRequest ? 'Updating...' : 'Saving...') : (editingRequest
                                ?
                                'Update' : 'Save') }}</span>
                        </button>
                    </div>
                </form>
            </PremiumModal>

            <PremiumModal :show="showRejectModal" @close="closeRejectModal" title="Reject Request"
                subtitle="Tell us why this request is being rejected" icon="fa-circle-xmark" maxWidth="2xl">
                <form @submit.prevent="rejectRequest"
                    class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-gradient-to-b from-slate-50/90 to-white p-8 text-left shadow-[0_20px_50px_-20px_rgba(15,23,42,0.18)]">
                    <div
                        class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-rose-500/10 blur-3xl pointer-events-none">
                    </div>

                    <div class="relative flex items-start justify-between gap-4 border-b border-slate-200/70 pb-6">
                        <div class="space-y-2">
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-rose-50 px-3 py-1.5 text-[8px] font-black uppercase tracking-[0.25em] text-rose-600">
                                Rejection Reason
                            </div>
                            <h2 class="text-xl font-black uppercase tracking-tight text-slate-950 leading-none">
                                Reject Request
                            </h2>
                            <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                Please add a reason before rejecting
                            </p>
                        </div>
                    </div>

                    <div class="relative mt-6 space-y-2.5">
                        <InputLabel class="font-black text-slate-900" value="Reason" />
                        <textarea v-model="rejectForm.rejection_reason" rows="5" required
                            placeholder="Enter rejection reason"
                            class="min-h-40 w-full rounded-[1.25rem] border border-slate-200 bg-slate-50/70 px-4 py-3 text-[11px] font-semibold tracking-wide text-slate-700 transition-all placeholder:text-slate-300 focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 focus:outline-none"></textarea>
                        <InputError :message="rejectForm.errors.rejection_reason" />
                    </div>

                    <div
                        class="relative mt-8 flex flex-col-reverse gap-3 border-t border-slate-200/70 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" @click="closeRejectModal"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 sm:w-auto">
                            Cancel
                        </button>

                        <button type="submit" :disabled="rejectForm.processing"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-6 text-[9px] font-black uppercase tracking-[0.2em] text-white shadow-[0_10px_25px_-10px_rgba(220,38,38,0.65)] transition-all hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 sm:w-auto">
                            <ArrowPathIcon v-if="rejectForm.processing" class="w-4 h-4 animate-spin" />
                            <TrashIcon v-else class="w-4 h-4" />
                            <span>{{ rejectForm.processing ? 'Rejecting...' : 'Reject' }}</span>
                        </button>
                    </div>
                </form>
            </PremiumModal>

            <!-- Tab 1: Stats & Alerts -->
            <div v-if="tab === 'stats'" class="space-y-8 animate-in slide-in-from-bottom-4 duration-500">
                <!-- Numbers at a Glance -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(s, idx) in [
                        { label: 'Resource Fleet', val: stats.total_assets, icon: ServerStackIcon, color: 'text-indigo-600', bg: 'bg-indigo-50' },
                        { label: 'Total Valuation', val: '₹' + (stats.total_value || 0).toLocaleString(), icon: BanknotesIcon, color: 'text-emerald-600', bg: 'bg-emerald-50' },
                        { label: 'Vendor Nodes', val: stats.active_vendors, icon: LibraryIcon, color: 'text-rose-600', bg: 'bg-rose-50' },
                        { label: 'Broken / Lost', val: stats.low_health, icon: ExclamationTriangleIcon, color: 'text-orange-600', bg: 'bg-orange-50' }
                    ]" :key="idx"
                        class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm group hover:border-indigo-200 transition-all flex flex-col justify-between h-48 relative overflow-hidden">
                        <div class="flex justify-between items-start text-left">
                            <div>
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 block">{{
                                        s.label }}</span>
                                <span
                                    class="text-2xl font-black text-slate-900 tracking-tight tabular-nums truncate block">{{
                                        s.val }}</span>
                            </div>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center border border-slate-100 shadow-sm"
                                :class="s.bg">
                                <component :is="s.icon" class="w-5 h-5" :class="s.color" />
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5 leading-none">
                                <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                                Live Sync
                            </span>
                            <ChevronRightIcon
                                class="w-4 h-4 text-slate-300 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </div>

                <!-- AI Repair Forecast -->
                <div v-if="stats.predictive_alerts && stats.predictive_alerts.length"
                    class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-6 text-left">
                        <div
                            class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center shrink-0 border border-indigo-100">
                            <SparklesIcon class="w-8 h-8 text-indigo-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">Forecast
                            </h2>
                            <p class="text-xs font-medium text-slate-500 mt-2">Assets requiring upcoming service
                                attention.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 overflow-x-auto w-full md:w-auto pb-2 no-scrollbar">
                        <div v-for="alert in stats.predictive_alerts" :key="alert.asset_id"
                            class="bg-slate-50 border border-slate-200 px-6 py-4 rounded-2xl flex items-center gap-4 shrink-0 hover:bg-white hover:border-indigo-200 transition-all cursor-pointer group">
                            <div
                                class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center">
                                <ExclamationTriangleIcon class="w-4 h-4"
                                    :class="alert.risk_level === 'Critical' ? 'text-rose-500' : 'text-amber-500'" />
                            </div>
                            <div class="text-left">
                                <div
                                    class="text-[10px] font-bold text-slate-900 uppercase tracking-widest truncate w-32 leading-none">
                                    {{ alert.name }}</div>
                                <div class="text-[9px] font-medium text-slate-500 mt-1.5 leading-none">Due in {{
                                    alert.days_until }} days</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Vendor List -->
            <div v-if="tab === 'vendors'" class="animate-in slide-in-from-bottom-4 duration-500">
                <header
                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 bg-white p-4 rounded-3xl border border-slate-200 shadow-sm mb-8">
                    <div class="flex items-center gap-4 text-left">
                        <div
                            class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 shadow-sm border border-rose-100 shrink-0">
                            <LibraryIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h2 class="text-1xl font-black text-slate-900 uppercase tracking-tight leading-none">Vendor
                                Directory</h2>
                            <p class="text-xs font-semibold text-slate-400 mt-2">Equipment suppliers & verified
                                maintenance partners.</p>
                        </div>
                    </div>
                    <button type="button" @click="openVendorModal"
                        class="float-right h-10 px-2 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-1 cursor-pointer active:scale-95">
                        <PlusIcon class="w-4 h-4" />
                        <span>Add Vendor</span>
                    </button>
                </header>

                <div
                    class="relative z-10 w-full bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[240px]">
                    <BaseDataTable :data="filteredVendors" @search="handleVendorSearch" :columns="[
                        { key: 'identity', label: 'Store Identity', sortable: true, align: 'center' },
                        { key: 'category', label: 'Classification', sortable: true, align: 'right' },
                        { key: 'financials', label: 'Financial ID', sortable: false, align: 'right' },
                        { key: 'performance', label: 'Performance', sortable: true, align: 'right' },
                        { key: 'actions', label: 'Actions', sortable: false, align: 'right' },
                        { key: 'operations', label: 'Operations', sortable: false, align: 'right' }
                    ]" search-placeholder="Search vendors...">
                        <template #cell-identity="{ item: row }">
                            <div class="flex items-center gap-4 py-1">
                                <div
                                    class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-bold text-lg shrink-0">
                                    {{ row.name.charAt(0) }}
                                </div>
                                <div class="text-left">
                                    <div
                                        class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">
                                        {{ row.name }}</div>
                                    <div
                                        class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 leading-none">
                                        Verified Partner</div>
                                </div>
                            </div>
                        </template>

                        <template #cell-category="{ item: row }">
                            <div class="text-left text-[11px] font-bold text-slate-600 uppercase tracking-widest">
                                {{ row.category }}
                            </div>
                        </template>

                        <template #cell-financials="{ item: row }">
                            <div class="flex items-center gap-2 text-left">
                                <span
                                    class="text-[10px] font-mono font-bold text-slate-500 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                                    {{ row.gstin || 'NO_TAX_ID' }}
                                </span>
                            </div>
                        </template>

                        <template #cell-performance="{ item: row }">
                            <div class="flex items-center gap-4 justify-start">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="text-xs font-black text-slate-900 tabular-nums leading-none">{{
                                        row.sla_response_hours }}h</span>
                                    <span
                                        class="text-[8px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none mt-1">Response</span>
                                </div>
                                <div class="w-px h-6 bg-slate-100 mx-2"></div>
                                <div class="flex flex-col items-start gap-1">
                                    <span :class="[
                                        'text-xs font-black uppercase leading-none',
                                        row.is_active == 1
                                            ? 'text-emerald-600'
                                            : 'text-rose-600'
                                    ]">
                                        {{ row.is_active == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template #cell-actions="{ item: row }">
                            <div class="flex justify-center gap-2">
                                <button
                                    class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest hover:underline">History</button>
                                <button
                                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-all">Details</button>
                            </div>
                        </template>

                        <template #cell-operations="{ item: row }">
                            <div class="flex justify-center gap-2">
                                <button @click="openEdit(row)"
                                    class="h-10 px-3 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-1 cursor-pointer">
                                    <PencilSquareIcon class="w-4 h-4" />
                                    Edit
                                </button>
                                <button type="button" @click="openDeleteVendorConfirm(row)"
                                    class="h-10 px-3 bg-rose-500 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-600 transition-all flex items-center gap-1 cursor-pointer ">
                                    <TrashIcon class="w-4 h-4" />
                                    Delete
                                </button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>

                <!-- Add/edit vendor Modal -->
                <PremiumModal :show="showVendorModal" @close="closeVendorModal"
                    :title="editingVendor ? 'Edit Vendor' : 'Add New Vendor'" subtitle="Raise a request for review"
                    icon="fa-clipboard-list" maxWidth="2xl">

                    <form @submit.prevent="submitVendor"
                        class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-gradient-to-b from-slate-50/90 to-white p-6 text-left shadow-[0_20px_50px_-20px_rgba(15,23,42,0.18)]">
                        <!-- Ambient Effects -->
                        <div
                            class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
                        </div>

                        <div
                            class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-violet-500/10 blur-3xl pointer-events-none">
                        </div>

                        <!-- Header -->
                        <div class="relative flex items-start justify-between gap-4 border-b border-slate-200/70 pb-6">
                            <div class="space-y-2">
                                <div
                                    class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-[8px] font-black uppercase tracking-[0.25em] text-indigo-600">
                                    Vendor Intake
                                </div>

                                <div>
                                    <h2 class="text-xl font-black uppercase tracking-tight text-slate-950 leading-none">
                                        {{ editingVendor ? 'Edit Vendor' : 'Add New Vendor' }}
                                    </h2>

                                    <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                        {{ editingVendor ? 'Update the Vendor information' : 'Capture vendor information and contract metadata'}}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="hidden md:flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg">
                                <BuildingStorefrontIcon class="h-5 w-5" />
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="relative mt-7 grid grid-cols-1 gap-5 xl:grid-cols-2">

                            <!-- Vendor Name -->
                            <div class="space-y-2.5">
                                <InputLabel value="Vendor Name"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.name" placeholder="Enter vendor name" required
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10" />

                                <InputError :message="vendorForm.errors.name" />
                            </div>

                            <!-- Category -->
                            <div class="space-y-2.5">
                                <InputLabel value="Vendor Category"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.category" placeholder="Hardware / Software / Service"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10" />

                                <InputError :message="vendorForm.errors.category" />
                            </div>

                            <!-- Contact Person -->
                            <div class="space-y-2.5">
                                <InputLabel value="Contact Person"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.contact_person" placeholder="Vendor representative"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-sky-500 focus:bg-white focus:ring-4 focus:ring-sky-500/10" />

                                <InputError :message="vendorForm.errors.contact_person" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2.5">
                                <InputLabel value="Email Address"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.email" type="email" placeholder="vendor@email.com"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" />

                                <InputError :message="vendorForm.errors.email" />
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2.5">
                                <InputLabel value="Phone Number"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.phone" placeholder="+91 XXXXX XXXXX"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10" />

                                <InputError :message="vendorForm.errors.phone" />
                            </div>

                            <!-- GST -->
                            <div class="space-y-2.5">
                                <InputLabel value="GSTIN / Tax ID"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.gstin" placeholder="Enter GSTIN"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10" />

                                <InputError :message="vendorForm.errors.gstin" />
                            </div>

                            <!-- SLA -->
                            <div class="space-y-2.5">
                                <InputLabel value="SLA Response Hours"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.sla_response_hours" type="number" min="1"
                                    placeholder="24"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10" />

                                <InputError :message="vendorForm.errors.sla_response_hours" />
                            </div>

                            <!-- Contract End -->
                            <div class="space-y-2.5">
                                <InputLabel value="Contract End Date"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <TextInput v-model="vendorForm.contract_end_date" type="date"
                                    class="w-full h-12 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-bold tracking-wide focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10" />

                                <InputError :message="vendorForm.errors.contract_end_date" />
                            </div>

                            <!-- Status -->
                            <div class="xl:col-span-2 space-y-3 pt-1">
                                <InputLabel value="Vendor Status"
                                    class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />

                                <div class="flex flex-wrap gap-4">
                                    <label
                                        class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-3 cursor-pointer transition-all hover:scale-[1.02]">
                                        <input type="radio" v-model="vendorForm.status" value="1"
                                            class="text-emerald-600 focus:ring-emerald-500">

                                        <span class="text-[11px] font-black uppercase tracking-widest text-emerald-700">
                                            Active
                                        </span>
                                    </label>

                                    <label
                                        class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-3 cursor-pointer transition-all hover:scale-[1.02]">
                                        <input type="radio" v-model="vendorForm.status" value="0"
                                            class="text-rose-600 focus:ring-rose-500">

                                        <span class="text-[11px] font-black uppercase tracking-widest text-rose-700">
                                            Inactive
                                        </span>
                                    </label>
                                </div>

                                <InputError :message="vendorForm.errors.status" />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="relative mt-7 flex flex-col-reverse gap-3 border-t border-slate-200/70 pt-6 sm:flex-row sm:items-center sm:justify-between">

                            <button @click="closeVendorModal" type="button"
                                class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 sm:w-auto cursor-pointer">
                                Cancel
                            </button>

                            <button type="submit" :disabled="vendorForm.processing"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-7 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-[0_10px_25px_-10px_rgba(79,70,229,0.65)] transition-all hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 sm:w-auto cursor-pointer">
                                <ArrowPathIcon v-if="vendorForm.processing" class="w-4 h-4 animate-spin" />

                                <CheckBadgeIcon v-else class="w-4 h-4 text-white" />

                                <span>{{ vendorForm.processing ? (editingVendor ? 'Updating...' : 'Saving...') :
                                    (editingVendor ? 'Update' : 'Save') }}</span>
                            </button>
                        </div>
                    </form>
                </PremiumModal>
            </div>

            <!-- Tab 3: Master List Table -->
            <div v-if="tab === 'list'" class="animate-in slide-in-from-bottom-4 duration-500">
                <!-- Advanced Tactical Filter Array -->
                <div
                    class="bg-white p-3 rounded-3xl shadow-sm border border-slate-200 flex flex-col xl:flex-row gap-4 items-center mb-8 relative z-20">
                    <div class="relative w-full xl:w-[400px] group/search">
                        <MagnifyingGlassIcon
                            class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within/search:text-indigo-600 transition-colors shrink-0" />
                        <input v-model="filterForm.search" @input="debouncedSearch" type="text"
                            placeholder="Search Master List..."
                            class="w-full h-10 bg-slate-50 border border-slate-200 rounded-xl pl-13 pr-2 text-sm font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:outline-none transition-all uppercase tracking-wide placeholder:text-slate-300">
                    </div>

                    <div class="flex flex-col sm:flex-row w-full xl:w-auto gap-4">
                        <div class="relative">
                            <TagIcon
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            <select v-model="filterForm.category_id" @change="applyFilters"
                                class="h-10 w-full sm:w-48 bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-600 rounded-xl pl-12 pr-10 uppercase tracking-widest transition-all appearance-none cursor-pointer focus:bg-white focus:border-indigo-500  focus:outline-none">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="relative">
                            <BoltIcon
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            <select v-model="filterForm.status" @change="applyFilters"
                                class="h-10 w-full sm:w-48 bg-slate-50 border border-slate-200 text-[10px] font-bold text-slate-600 rounded-xl pl-12 pr-10 uppercase tracking-widest transition-all appearance-none cursor-pointer focus:bg-white focus:border-indigo-500  focus:outline-none">
                                <option value="">All Statuses</option>
                                <option value="Available">Ready</option>
                                <option value="Assigned">Assigned</option>
                                <option value="In_Service">In Service</option>
                            </select>
                        </div>
                    </div>

                    <button @click="resetFilters"
                        class="h-12  text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 cursor-pointer transition-all">
                        <ArrowPathIcon @click="applyFilters"
                            class="w-5 h-5 text-slate-400 hover:text-red-600 transition-colors cursor-pointer mr-1" />
                        Reset
                        Filters
                    </button>
                </div>

                <div class="flex flex-wrap gap-3 h-fit mb-3 justify-end">
                    <button @click="makeBulkAvailable" :disabled="!selectedAssets.length"
                        class="h-11 px-4 bg-teal-50 border border-teal-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-teal-700 hover:bg-teal-100 transition-all flex items-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40">
                        <CheckCircleIcon class="w-4 h-4" /> Available Selected
                    </button>
                    <button @click="openBulkAssignModal" :disabled="!selectedAssets.length"
                        class="h-11 px-4 bg-indigo-50 border border-indigo-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-indigo-700 hover:bg-indigo-100 transition-all flex items-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40">
                        <UserPlusIcon class="w-4 h-4" /> Assign Selected
                    </button>
                    <button @click="returnSelectedAssets" :disabled="!selectedAssets.length"
                        class="h-11 px-4 bg-amber-50 border border-amber-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-amber-700 hover:bg-amber-100 transition-all flex items-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40">
                        <ArrowUturnLeftIcon class="w-4 h-4" /> Return Selected
                    </button>
                    <button @click="openBulkMaintenanceModal" :disabled="!selectedAssets.length"
                        class="h-11 px-4 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-emerald-700 hover:bg-emerald-100 transition-all flex items-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40">
                        <WrenchScrewdriverIcon class="w-4 h-4" /> Maintenance Selected
                    </button>
                    <button @click="openBulkDeleteConfirm" :disabled="!selectedAssets.length"
                        class="h-11 px-4 bg-rose-50 border border-rose-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-rose-700 hover:bg-rose-100 transition-all flex items-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40">
                        <TrashIcon class="w-4 h-4" /> Delete Selected
                    </button>
                </div>

                <PremiumModal :show="showAssignModal" @close="closeAssignModal" title="Assign Selected Assets"
                    subtitle="Select user to hand over the selected assets">
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
                            <button type="button" @click="closeAssignModal"
                                class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-500">Cancel</button>
                            <button type="submit" :disabled="assignForm.processing"
                                class="h-10 px-5 bg-indigo-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest">
                                {{ assignForm.processing ? 'Assigning...' : `Assign Selected (${selectedAssets.length})`
                                }}
                            </button>
                        </div>
                    </form>
                </PremiumModal>

                <PremiumModal :show="showBulkMaintenanceModal" @close="closeBulkMaintenanceModal" title="Maintainance"
                    subtitle="">
                    <div
                        class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-white via-slate-50 to-emerald-50/40">

                        <!-- Ambient Effects -->
                        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl"></div>

                        <div class="absolute -left-16 -bottom-16 h-60 w-60 rounded-full bg-cyan-400/10 blur-3xl"></div>

                        <!-- Top Glow -->
                        <div
                            class="absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-emerald-300/70 to-transparent">
                        </div>

                        <!-- Header -->
                        <div class="relative z-10 border-b border-slate-200/70 px-6 py-6 sm:px-8">
                            <div class="flex items-start justify-between gap-5">

                                <!-- Left -->
                                <div class="space-y-3">

                                    <div
                                        class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[8px] font-black uppercase tracking-[0.28em] text-emerald-600">
                                        Maintenance Matrix
                                    </div>

                                    <div>
                                        <h2 class="text-2xl font-black tracking-tight text-slate-950 leading-none">
                                            Bulk Maintenance
                                        </h2>

                                        <p
                                            class="mt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            Log maintenance records for selected assets
                                        </p>
                                    </div>
                                </div>

                                <!-- Icon -->
                                <div
                                    class="hidden sm:flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.5rem] bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/20">
                                    <WrenchScrewdriverIcon class="w-6 h-6" />
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitBulkMaintenance"
                            class="relative z-10 p-6 sm:p-8 space-y-7 text-left">

                            <!-- Type -->
                            <div class="rounded-[1.8rem] border border-slate-200 bg-white/80 p-5 shadow-sm">
                                <div class="flex items-center gap-2 mb-4">
                                    <Squares2X2Icon class="w-4 h-4 text-emerald-500" />

                                    <InputLabel value="Maintenance Type"
                                        class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                                </div>

                                <BaseSelect v-model="serviceForm.type"
                                    class="w-full h-13 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[12px] font-black uppercase tracking-[0.16em] focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                                    <option value="Repair">Repair</option>
                                    <option value="Upgrade">Upgrade</option>
                                    <option value="Routine_Service">Routine Service</option>
                                </BaseSelect>

                                <InputError :message="serviceForm.errors.type" />
                            </div>

                            <!-- Description -->
                            <div class="rounded-[1.8rem] border border-slate-200 bg-white/80 p-5 shadow-sm">
                                <div class="flex items-center gap-2 mb-4">
                                    <DocumentTextIcon class="w-4 h-4 text-cyan-500" />

                                    <InputLabel value="Maintenance Description"
                                        class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                                </div>

                                <textarea v-model="serviceForm.description" rows="5"
                                    placeholder="Describe the performed maintenance activity..."
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-5 py-4 text-sm text-slate-700 shadow-sm outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"></textarea>

                                <InputError :message="serviceForm.errors.description" />
                            </div>

                            <!-- Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                                <!-- Cost -->
                                <div class="rounded-[1.8rem] border border-slate-200 bg-white/80 p-5 shadow-sm">
                                    <div class="flex items-center gap-2 mb-4">
                                        <BanknotesIcon class="w-4 h-4 text-indigo-500" />

                                        <InputLabel value="Maintenance Cost"
                                            class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                                    </div>

                                    <TextInput v-model="serviceForm.cost" type="number" min="0" placeholder="0.00"
                                        class="w-full h-13 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-black tracking-wide focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10" />

                                    <InputError :message="serviceForm.errors.cost" />
                                </div>

                                <!-- Date -->
                                <div class="rounded-[1.8rem] border border-slate-200 bg-white/80 p-5 shadow-sm">
                                    <div class="flex items-center gap-2 mb-4">
                                        <CalendarDaysIcon class="w-4 h-4 text-amber-500" />

                                        <InputLabel value="Service Date"
                                            class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-600" />
                                    </div>

                                    <TextInput v-model="serviceForm.service_date" type="date"
                                        class="w-full h-13 rounded-2xl border-slate-200 bg-slate-50/70 px-4 text-[13px] font-black tracking-wide focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/10" />

                                    <InputError :message="serviceForm.errors.service_date" />
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="flex flex-col-reverse gap-4 border-t border-slate-200/70 pt-6 sm:flex-row sm:items-center sm:justify-between">

                                <!-- Selected Count -->
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-3 shadow-sm">
                                    <span
                                        class="block text-[8px] font-black uppercase tracking-[0.24em] text-emerald-600">
                                        Selected Assets
                                    </span>

                                    <span class="mt-1 block text-2xl font-black text-slate-950">
                                        {{ selectedAssets.length }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-3 justify-end">

                                    <!-- Cancel -->
                                    <button type="button" @click="closeBulkMaintenanceModal"
                                        class="h-11 rounded-2xl border border-slate-300 bg-white px-5 text-[10px] font-black uppercase tracking-[0.22em] text-slate-600 shadow-sm transition-all duration-300 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 cursor-pointer">
                                        Cancel
                                    </button>

                                    <!-- Submit -->
                                    <button type="submit" :disabled="serviceForm.processing"
                                        class="group flex h-12 items-center gap-3 rounded-[1.4rem] bg-gradient-to-r from-emerald-600 to-teal-600 px-7 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-[0_15px_35px_-15px_rgba(5,150,105,0.7)] transition-all hover:scale-[1.02] hover:shadow-emerald-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer">
                                        <ArrowPathIcon v-if="serviceForm.processing" class="w-5 h-5 animate-spin" />

                                        <WrenchScrewdriverIcon v-else
                                            class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />

                                        <span>
                                            {{
                                                serviceForm.processing
                                                    ? 'Saving Maintenance...'
                                                    : 'Save Maintenance'
                                            }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </PremiumModal>
                <!-- Strategic Terminal Grid -->
                <div class=" rounded-3xl border border-slate-200 overflow-hidden min-h-[500px]">
                    <BaseDataTable :data="assets.data" :meta="assets"
                        @page-change="(page) => router.get(route('admin.assets.dashboard', { view: 'list', page }), filterForm, { preserveState: true, preserveScroll: true })"
                        :columns="[
                            { key: 'select', label: '', sortable: false, align: 'center' },
                            { key: 'node', label: 'Matrix Node (Item)', sortable: true },
                            { key: 'location', label: 'Deployment Room', sortable: true },
                            { key: 'source', label: 'Source Channel', sortable: true },
                            { key: 'integrity', label: 'Integrity Status', sortable: true },
                            { key: 'actions', label: 'Actions', sortable: false, align: 'center' }
                        ]" search-placeholder="Filter current view...">

                        <!-- Header Checkbox -->
                        <template #header-select>
                            <div class="flex justify-center">
                                <input type="checkbox" @change="toggleSelectAll" :checked="areAllSelected"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                            </div>
                        </template>

                        <!-- Row Checkbox -->
                        <template #cell-select="{ item: row }">
                            <div class="flex justify-center">
                                <input type="checkbox" v-model="selectedAssets" :value="row.id"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                            </div>
                        </template>

                        <template #cell-node="{ item: row }">
                            <div class="flex items-center gap-4 py-1 text-left">
                                <div
                                    class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-100 shrink-0">
                                    <CubeIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <div
                                        class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">
                                        {{ row.name }}</div>
                                    <div
                                        class="text-[9px] font-mono font-bold text-slate-400 mt-1 uppercase tracking-widest leading-none">
                                        SN: {{ row.serial_number || 'UNKNOWN' }}</div>
                                </div>
                            </div>
                        </template>

                        <template #cell-location="{ item: row }">
                            <div class="flex items-center gap-3 text-left">
                                <MapPinIcon class="w-3.5 h-3.5 text-slate-300" />
                                <span
                                    class="text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-none">{{
                                        row.current_location_node?.name || 'Central Store' }}</span>
                            </div>
                        </template>

                        <template #cell-source="{ item: row }">
                            <div class="flex items-center gap-3 text-left">
                                <BuildingStorefrontIcon class="w-3.5 h-3.5 text-slate-300" />
                                <span
                                    class="text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-none">{{
                                        row.vendor?.name || 'In-House' }}</span>
                            </div>
                        </template>

                        <template #cell-integrity="{ item: row }">
                            <span
                                class="px-4 py-1.5 inline-flex text-[9px] font-bold tracking-widest rounded-lg border uppercase leading-none"
                                :class="{
                                    'bg-emerald-50 text-emerald-600 border-emerald-100': row.status === 'Available',
                                    'bg-indigo-50 text-indigo-600 border-indigo-100': row.status === 'Assigned',
                                    'bg-amber-50 text-amber-600 border-amber-100': ['In_Service', 'Maintenance', 'In Service'].includes(row.status)
                                }">
                                {{ row.status.replace('_', ' ') }}
                            </span>
                        </template>

                        <template #cell-actions="{ item: row }">
                            <div class="flex justify-center gap-3">
                                <Link :href="route('admin.assets.show', row.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-95 shadow-sm cursor-pointer">
                                    View</Link>
                                <Link :href="route('admin.assets.label', row.id)"
                                    class="px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all cursor-pointer">
                                    Label</Link>
                                <button @click="openDeleteConfirm(row)"
                                    class="px-4 py-2 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all inline-flex items-center gap-2 cursor-pointer">
                                    <span
                                        class="flex h-5 w-5 items-center justify-center rounded-md bg-white/80 text-rose-600 shadow-sm">
                                        <TrashIcon class="w-3.5 h-3.5" />
                                    </span>
                                    Delete
                                </button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>
            </div>

            <div v-if="tab === 'locations'" class="animate-in slide-in-from-bottom-4 duration-500">

                <div
                    class="relative z-10 w-full bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[240px]">
                    <BaseDataTable :data="locations" :columns="[
                        { key: 'location', label: 'Location', sortable: true, align: 'center', class: 'w-[26%] min-w-[180px]' },
                        { key: 'code', label: 'Code', sortable: true, align: 'center', class: 'w-[23%] min-w-[200px]'  },
                        { key: 'city', label: 'City', sortable: true, align: 'left' },
                        { key: 'actions', label: 'Actions', sortable: false, align: 'center' }
                    ]" search-placeholder="Search locations...">

                        <template #cell-location="{ item }">
                            <div class="flex items-center gap-4 py-1 text-left">
                                <div
                                    class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-100 shrink-0">
                                    <MapPinIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <div
                                        class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">
                                        {{ item.name }}</div>
                                    <div
                                        class="text-[9px] font-mono font-bold text-slate-400 mt-1 uppercase tracking-widest leading-none">
                                        {{ item.address || '' }}</div>
                                </div>
                            </div>
                        </template>

                        <template #cell-code="{ item }">
                            <span
                                class="inline-flex rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                {{ item.code || '' }}
                            </span>
                        </template>

                        <template #cell-city="{ item }">
                            <div class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                {{ item.city || '' }}
                            </div>
                        </template>

                        <template #cell-actions="{ item }">
                            <div class="flex justify-center gap-2">
                                <!-- <Link :href="route('admin.locations.index')"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-700 transition-all hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100"
                                    title="Open location settings">
                                    <CogIcon class="w-4 h-4" />
                                </Link> -->
                                <button type="button" @click="openLocationModal(item)"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-100 bg-indigo-50 text-indigo-600 transition-all hover:bg-indigo-100"
                                    title="View location details">
                                    <EyeIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>

                <PremiumModal v-if="selectedLocationItem" :show="showLocationModal" @close="closeLocationModal" title="Asset List"
                    subtitle="Quick view of the assets in specific location">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-1">
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 text-left min-h-[450px] shadow-sm">
                                <div v-if="selectedLocationVerifiedAssets.length" class="stats mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                                        <p class="text-[9px] font-black uppercase tracking-[0.25em] text-slate-400">Total</p>
                                        <p class="mt-2 text-2xl font-black text-slate-900 tabular-nums">{{ locationAssetStats.total }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-center">
                                        <p class="text-[9px] font-black uppercase tracking-[0.25em] text-rose-500">Lost</p>
                                        <p class="mt-2 text-2xl font-black text-rose-700 tabular-nums">{{ locationAssetStats.lost }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-center">
                                        <p class="text-[9px] font-black uppercase tracking-[0.25em] text-amber-500">In Service</p>
                                        <p class="mt-2 text-2xl font-black text-amber-700 tabular-nums">{{ locationAssetStats.inService }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-center">
                                        <p class="text-[9px] font-black uppercase tracking-[0.25em] text-indigo-500">Assigned</p>
                                        <p class="mt-2 text-2xl font-black text-indigo-700 tabular-nums">{{ locationAssetStats.assigned }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-3 overflow-y-auto pr-1">
                                    <template v-if="selectedLocationVerifiedAssets.length">
                                        <div v-for="asset in selectedLocationVerifiedAssets" :key="asset.id"
                                            class="rounded-2xl border border-slate-200 bg-slate-50/100 px-4 py-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="text-sm font-black uppercase tracking-tight text-slate-900">
                                                            {{ asset.name }}
                                                        </p>
                                                        <span
                                                            class="inline-flex rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest"
                                                            :class="getAssetStatusClasses(asset.status)">
                                                            {{ asset.status}}
                                                        </span>
                                                    </div>
                                                    <p
                                                        class="mt-1 text-[9px] font-mono font-bold uppercase tracking-widest text-emerald-700">
                                                        {{ asset.serial_number || asset.asset_tag || 'NO TAG' }}
                                                    </p>
                                                </div>
                                                <CheckBadgeIcon v-if="asset.status==='Available'" class="w-5 h-5 text-emerald-600 shrink-0" />
                                            </div>
                                        </div>
                                    </template>
                                    <div v-else
                                        class="flex min-h-[430px] flex-col items-center justify-center rounded-3xl to-slate-50 px-6 text-center">
                                        <p class="text-sm uppercase tracking-[0.'em] text-rose-600">
                                            No assets found for this location
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </PremiumModal>
            </div>

            <!-- Tab 4: Procurement Pipeline -->
            <div v-if="tab === 'procurement'" class="animate-in fade-in slide-in-from-bottom-5 duration-700">
                <div
                    class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm mb-10 flex flex-col md:flex-row justify-between items-center gap-10 relative overflow-hidden group">
                    <div
                        class="absolute -right-24 -top-24 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000 font-outfit">
                    </div>

                    <div class="flex items-center gap-5 relative z-10">
                        <div
                            class="w-12 h-12 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm transition-transform group-hover:scale-105 shrink-0">
                            <ShoppingCartIcon class="w-9 h-9" />
                        </div>
                        <div class="text-left">
                            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Recent Orders</h2>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] mt-1">Verified
                                equipment
                                procurement logs</p>
                        </div>
                    </div>
                    <Link :href="route('procurement.index')"
                        class="h-10 px-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.1em] hover:bg-indigo-700 transition-all flex items-center gap-2 active:scale-95 shadow-lg relative z-10 group/btn">
                        <PlusIcon class="w-5 h-5 group-hover/btn:rotate-90 transition-transform" />
                        New Purchase Order
                    </Link>
                </div>

                <div
                    class="relative z-10 w-full bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[240px]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th
                                        class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Order
                                        Identifier</th>
                                    <th
                                        class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Store
                                        Channel</th>
                                    <th
                                        class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                        Capital Outlay</th>
                                    <th
                                        class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                        Protocol Status</th>
                                    <th
                                        class="px-10 py-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Operation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="po in procurement" :key="po.id"
                                    class="group/prow hover:bg-slate-50 transition-all duration-300">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-6">
                                            <div
                                                class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 shadow-sm group-hover/prow:bg-indigo-50 group-hover/prow:text-indigo-600 transition-all shrink-0">
                                                <BanknotesIcon class="w-6 h-6" />
                                            </div>
                                            <span
                                                class="font-mono text-base font-black text-slate-900 uppercase tracking-tight">{{
                                                    po.po_number || 'PLAN_NODE' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-sm font-black text-slate-600 uppercase tracking-widest">
                                        {{
                                            po.vendor?.name }}</td>
                                    <td class="px-10 py-8 text-center">
                                        <div
                                            class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-lg font-black text-slate-900 font-mono tracking-tighter shadow-sm inline-flex items-center gap-3">
                                            <BanknotesIcon class="w-5 h-5 text-emerald-500" />
                                            ₹{{ (po.total_cost || 0).toLocaleString() }}
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-center">
                                        <span
                                            class="px-4 py-1.5 bg-slate-100 text-[9px] font-black text-slate-500 uppercase tracking-widest rounded-lg border border-slate-200 shadow-sm shrink-0">
                                            {{ po.status }}
                                        </span>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <Link v-if="po.status !== 'Converted'" method="post" as="button"
                                            :href="route('procurement.convert', po.id)"
                                            class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md inline-flex items-center gap-4 group/conv active:scale-95">
                                            Commit to Matrix
                                            <CubeIcon
                                                class="w-4 h-4 text-indigo-400 group-hover/conv:scale-125 transition-transform" />
                                        </Link>
                                        <div v-else
                                            class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center justify-end gap-3">
                                            <CheckCircleIcon class="w-5 h-5" /> Synchronized
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!procurement.length">
                                    <td colspan="5" class="px-10 py-32 text-center bg-slate-50/30">
                                        <ShoppingCartIcon class="w-20 h-20 mx-auto text-slate-200 mb-8" />
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.1em]">No
                                            active
                                            procurement logs found.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Control Center -->
            <div v-if="tab === 'config'"
                class="animate-in zoom-in-95 duration-700 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm text-center flex flex-col items-center group relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-50 via-transparent to-transparent">
                </div>
                <div class="relative z-10 flex flex-col items-center">
                    <div
                        class="w-15 h-15 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm mb-4 border border-indigo-100 shrink-0">
                        <AdjustmentsHorizontalIcon class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-950 uppercase tracking-tight mb-2">Control Center</h3>
                    <p class="text-xs font-semibold text-slate-500 mb-10 max-w-xl leading-relaxed">
                        Manage categories and item details here. define how items should be grouped and tracked across
                        the company.
                    </p>

                    <div class="grid gap-4 w-full max-w-lg">
                        <Link v-for="cat in categories" :key="cat.id"
                            :href="route('admin.assets.configurations', { tab: 'attributes', category_id: cat.id })"
                            class="p-6 bg-white border border-slate-200 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-md hover:border-indigo-300 group/list transition-all active:scale-[0.98] font-black">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-2.5 h-2.5 rounded-full bg-slate-300 group-hover/list:bg-indigo-500 transition-colors shadow-sm">
                                </div>
                                <span
                                    class="text-base font-black text-slate-800 uppercase tracking-tight leading-none">{{
                                        cat.name
                                    }}</span>
                            </div>
                            <span
                                class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] group-hover/list:text-indigo-600 transition-colors">Set
                                Up &rarr;</span>
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm"
                @click.self="closeDeleteConfirm">
                <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-rose-50 via-white to-white px-5 py-4 border-b border-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.1em] text-rose-500">Confirm Delete</p>
                        <h3 class="mt-1 text-lg font-black text-slate-900">
                            {{ bulkDeleteMode ? 'Delete selected assets?' : 'Delete this asset?' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            This action cannot be undone.
                            <span class="font-semibold text-slate-700">
                                {{ bulkDeleteMode ? `${selectedAssets.length} selected assets` : (assetToDelete?.name ||
                                    'Selected asset') }}
                            </span>
                        </p>
                    </div>

                    <div class="px-5 py-4">
                        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ bulkDeleteMode ? 'The selected assets will be removed from the system permanently.' :
                                'The asset will be removed from the system permanently.' }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 px-5 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" @click="closeDeleteConfirm"
                            class="h-9 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" @click="deleteAsset"
                            class="h-9 px-4 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all cursor-pointer">
                            {{ bulkDeleteMode ? 'Delete Selected' : 'Delete Asset' }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="showDeleteRequestModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm"
                @click.self="closeDeleteConfirm">
                <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-rose-50 via-white to-white px-5 py-4 border-b border-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.1em] text-rose-500">Confirm Delete</p>
                        <h3 class="mt-1 text-lg font-black text-slate-900">Delete this Request?</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            This action cannot be undone.
                            <span class="font-semibold text-slate-700">
                                {{ assetToDelete?.name || 'Selected asset' }}
                            </span>
                        </p>
                    </div>

                    <div class="px-5 py-4">
                        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            The asset will be removed from the system permanently.
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 px-5 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" @click="closeDeleteRequestConfirm"
                            class="h-9 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" @click="deleteRequest"
                            class="h-9 px-4 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all cursor-pointer">
                            Delete request
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="showDeleteVendorModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm"
                @click.self="closeDeleteVendorConfirm">
                <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-rose-50 via-white to-white px-5 py-4 border-b border-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.1em] text-rose-500">Confirm Delete</p>
                        <h3 class="mt-1 text-lg font-black text-slate-900">Delete this Vendor?</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            This action cannot be undone.
                            <span class="font-semibold text-slate-700">
                                {{ vendorToDelete?.name }}
                            </span>
                        </p>
                    </div>

                    <div class="px-5 py-4">
                        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            The vendor will be removed from the system permanently.
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 px-5 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" @click="closeDeleteVendorConfirm"
                            class="h-9 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" @click="deleteVendor"
                            class="h-9 px-4 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all cursor-pointer">
                            Delete vendor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
}

.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.15);
}
</style>
