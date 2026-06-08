<script setup>
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';
import {
    ArchiveBoxIcon,
    ArrowPathIcon,
    BuildingOffice2Icon,
    BuildingStorefrontIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ChevronRightIcon,
    CubeIcon,
    MapPinIcon,
    PencilSquareIcon,
    PlusIcon,
    Squares2X2Icon,
    TagIcon,
    TrashIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    vendors: { type: Array, default: () => [] },
});

const locationTypeOptions = ['Branch', 'Building', 'Floor', 'Room', 'Zone', 'Locker', 'Cupboard', 'Shelf', 'Bin'];
const inventoryTypeOptions = ['Consumable', 'Non_Consumable', 'Serviceable'];
const recurringFrequencyOptions = ['Weekly', 'Monthly'];
const weeklyDayOptions = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
];

const activeSubTab = ref('issue-register');
const loadingPracticalData = ref(false);

const storeCategories = ref([]);
const locationNodes = ref([]);
const recurringRules = ref([]);
const recurringMeta = ref({ items: [], locations: [] });
const issueLines = ref([]);
const issueMeta = ref({ items: [], users: [], employees: [], clients: [], projects: [], issue_types: [], issued_to_types: [] });
const issueSummary = ref({ open: 0, pending_return: 0 });
const issueFilters = ref({
    item_id: '',
    issue_type: '',
    status: '',
    issued_to_type: '',
    from_date: '',
    to_date: '',
});
const clientSupplies = ref([]);
const clientSupplyMeta = ref({ items: [], clients: [], projects: [] });
const clientSupplySummary = ref({ total_qty: 0, total_value: 0, records: 0 });
const clientSupplyFilters = ref({
    client_id: '',
    item_id: '',
    project_id: '',
    from_date: '',
    to_date: '',
});

const practicalTabs = [
    { id: 'asset-categories', label: 'Asset Categories', icon: TagIcon },
    { id: 'store-categories', label: 'Store Categories', icon: ArchiveBoxIcon },
    { id: 'locations', label: 'Location Map', icon: MapPinIcon },
    { id: 'recurring', label: 'Recurring Usage', icon: CalendarDaysIcon },
    { id: 'issue-register', label: 'Issue Register', icon: CubeIcon },
    { id: 'client-supplies', label: 'Client Supplies', icon: BuildingStorefrontIcon },
    { id: 'vendors', label: 'Vendors', icon: BuildingStorefrontIcon },
];

const assetCategoryForm = useForm({
    id: null,
    name: '',
    description: '',
    is_electronic: false,
});

const vendorForm = useForm({
    id: null,
    name: '',
    email: '',
    phone: '',
    contact_person: '',
    service_type: 'General',
});

const showAssetCategoryModal = ref(false);
const showVendorModal = ref(false);
const editingAssetCategory = ref(null);
const editingVendor = ref(null);

const showStoreCategoryModal = ref(false);
const storeCategoryBusy = ref(false);
const storeCategoryErrors = ref({});
const editingStoreCategory = ref(null);
const storeCategoryForm = ref(createStoreCategoryPayload());

const showLocationModal = ref(false);
const locationBusy = ref(false);
const locationErrors = ref({});
const editingLocationNode = ref(null);
const locationForm = ref(createLocationPayload());

const showRecurringModal = ref(false);
const recurringBusy = ref(false);
const recurringErrors = ref({});
const editingRecurringRule = ref(null);
const recurringForm = ref(createRecurringPayload());

const showIssueModal = ref(false);
const issueBusy = ref(false);
const issueErrors = ref({});
const issueForm = ref(createIssuePayload());
const issueReturnBusyId = ref(null);

const showClientSupplyModal = ref(false);
const clientSupplyBusy = ref(false);
const clientSupplyErrors = ref({});
const editingClientSupply = ref(null);
const clientSupplyForm = ref(createClientSupplyPayload());

const flatStoreCategories = computed(() => flattenTree(storeCategories.value));
const flatLocationNodes = computed(() => flattenTree(locationNodes.value));
const locationOptions = computed(() => flatLocationNodes.value.filter((node) => ['Room', 'Zone', 'Locker', 'Cupboard', 'Shelf', 'Bin'].includes(node.node_type)));

const practicalStats = computed(() => {
    const mappedAssets = flatLocationNodes.value.reduce((total, node) => total + (node.mapped_assets_count || 0), 0);
    const mappedInventory = flatLocationNodes.value.reduce((total, node) => total + (node.mapped_inventory_count || 0), 0);
    const mappedDocuments = flatLocationNodes.value.reduce((total, node) => total + (node.mapped_documents_count || 0), 0);

    return [
        { label: 'Asset Categories', value: props.categories.length, icon: TagIcon, tone: 'text-indigo-600 bg-indigo-50 border-indigo-100' },
        { label: 'Store Hierarchy', value: flatStoreCategories.value.length, icon: Squares2X2Icon, tone: 'text-emerald-600 bg-emerald-50 border-emerald-100' },
        { label: 'Mapped Spaces', value: flatLocationNodes.value.length, icon: BuildingOffice2Icon, tone: 'text-amber-600 bg-amber-50 border-amber-100' },
        { label: 'Recurring Rules', value: recurringRules.value.length, icon: CalendarDaysIcon, tone: 'text-rose-600 bg-rose-50 border-rose-100' },
        { label: 'Assets Mapped', value: mappedAssets, icon: CubeIcon, tone: 'text-slate-700 bg-slate-100 border-slate-200' },
        { label: 'Store Mapped', value: mappedInventory, icon: ArchiveBoxIcon, tone: 'text-cyan-700 bg-cyan-50 border-cyan-100' },
        { label: 'Docs Mapped', value: mappedDocuments, icon: UserGroupIcon, tone: 'text-fuchsia-700 bg-fuchsia-50 border-fuchsia-100' },
    ];
});

function createStoreCategoryPayload() {
    return {
        id: null,
        parent_id: '',
        name: '',
        code: '',
        item_type: 'Consumable',
    };
}

function createLocationPayload() {
    return {
        id: null,
        parent_id: '',
        node_type: 'Branch',
        name: '',
        code: '',
        capacity: '',
        is_active: true,
    };
}

function createRecurringPayload() {
    return {
        id: null,
        item_id: '',
        scope_type: 'LocationNode',
        scope_id: '',
        frequency: 'Monthly',
        day_of_week: 1,
        day_of_month: 1,
        expected_qty: 1,
        auto_create_request: true,
        is_active: true,
    };
}

function createIssuePayload() {
    return {
        item_id: '',
        issue_type: 'Employee_Issue',
        issued_to_type: 'Employee',
        issued_to_id: '',
        quantity: 1,
        issue_date: new Date().toISOString().slice(0, 10),
        returnable: true,
    };
}

function createClientSupplyPayload() {
    return {
        id: null,
        client_id: '',
        item_id: '',
        supplied_qty: 1,
        unit_rate: '',
        supplied_on: new Date().toISOString().slice(0, 10),
        project_id: '',
        notes: '',
    };
}

const issueIssuedToOptions = computed(() => {
    const type = issueForm.value.issued_to_type;

    if (type === 'User') return issueMeta.value.users || [];
    if (type === 'Employee') return issueMeta.value.employees || [];
    if (type === 'Client') return issueMeta.value.clients || [];
    if (type === 'Project') return issueMeta.value.projects || [];

    return [];
});

function flattenTree(nodes, depth = 0, parentLabel = '', rows = []) {
    nodes.forEach((node) => {
        const currentLabel = parentLabel ? `${parentLabel} / ${node.name}` : node.name;

        rows.push({
            ...node,
            depth,
            path_label: currentLabel,
        });

        if (Array.isArray(node.children) && node.children.length) {
            flattenTree(node.children, depth + 1, currentLabel, rows);
        }
    });

    return rows;
}

function extractErrorMessage(error, fallback) {
    return error?.response?.data?.message || fallback;
}

function assignValidationErrors(target, error) {
    target.value = error?.response?.status === 422 ? (error.response.data.errors || {}) : {};
}

function resetValidationErrors(target) {
    target.value = {};
}

function locationName(id) {
    const match = recurringMeta.value.locations.find((location) => location.id === Number(id));
    return match ? `${match.node_type} - ${match.name}` : 'Unmapped';
}

function recurringScheduleLabel(rule) {
    if (rule.frequency === 'Weekly') {
        const day = weeklyDayOptions.find((option) => option.value === Number(rule.day_of_week));
        return `Every ${day ? day.label : 'week'}`;
    }

    return `Day ${rule.day_of_month || 1} of month`;
}

function toQueryParams(filters) {
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
            params.append(key, String(value));
        }
    });

    return params;
}

async function loadStoreCategories() {
    const response = await axios.get('/api/admin/inventory/category-tree');
    storeCategories.value = response.data.data || [];
}

async function loadLocationNodes() {
    const response = await axios.get('/api/admin/assets/location-nodes');
    locationNodes.value = response.data.data || [];
}

async function loadRecurringRules() {
    const response = await axios.get('/api/admin/inventory/recurring-rules');
    recurringRules.value = response.data.data?.data || [];
    recurringMeta.value = response.data.meta || { items: [], locations: [] };
}

async function loadIssueRegister() {
    const response = await axios.get('/api/admin/inventory/issues', {
        params: Object.fromEntries(toQueryParams(issueFilters.value).entries()),
    });
    issueLines.value = response.data.data?.data || [];
    issueMeta.value = response.data.meta || { items: [], users: [], employees: [], clients: [], projects: [], issue_types: [], issued_to_types: [] };
    issueSummary.value = response.data.summary || { open: 0, pending_return: 0 };
}

async function loadClientSupplies() {
    const response = await axios.get('/api/admin/inventory/client-supplies', {
        params: Object.fromEntries(toQueryParams(clientSupplyFilters.value).entries()),
    });
    clientSupplies.value = response.data.data?.data || [];
    clientSupplyMeta.value = response.data.meta || { items: [], clients: [], projects: [] };
    clientSupplySummary.value = response.data.summary || { total_qty: 0, total_value: 0, records: 0 };
}

async function applyIssueFilters() {
    await loadIssueRegister();
}

async function resetIssueFilters() {
    issueFilters.value = {
        item_id: '',
        issue_type: '',
        status: '',
        issued_to_type: '',
        from_date: '',
        to_date: '',
    };

    await loadIssueRegister();
}

function exportIssueRegister() {
    const query = toQueryParams(issueFilters.value).toString();
    const url = query ? `/api/admin/inventory/issues/export?${query}` : '/api/admin/inventory/issues/export';
    window.open(url, '_blank');
}

async function applyClientSupplyFilters() {
    await loadClientSupplies();
}

async function resetClientSupplyFilters() {
    clientSupplyFilters.value = {
        client_id: '',
        item_id: '',
        project_id: '',
        from_date: '',
        to_date: '',
    };

    await loadClientSupplies();
}

function exportClientSupplyRegister() {
    const query = toQueryParams(clientSupplyFilters.value).toString();
    const url = query ? `/api/admin/inventory/client-supplies/export?${query}` : '/api/admin/inventory/client-supplies/export';
    window.open(url, '_blank');
}

async function loadPracticalWorkspace() {
    loadingPracticalData.value = true;

    try {
        await Promise.all([loadStoreCategories(), loadLocationNodes(), loadRecurringRules(), loadIssueRegister(), loadClientSupplies()]);
    } catch (error) {
        alert(extractErrorMessage(error, 'Unable to load practical configuration workspace.'));
    } finally {
        loadingPracticalData.value = false;
    }
}

function openIssueModal() {
    resetValidationErrors(issueErrors);
    issueForm.value = createIssuePayload();
    showIssueModal.value = true;
}

function resetIssueTarget() {
    issueForm.value.issued_to_id = '';
}

async function submitIssue() {
    issueBusy.value = true;
    resetValidationErrors(issueErrors);

    const payload = {
        item_id: Number(issueForm.value.item_id),
        issue_type: issueForm.value.issue_type,
        issued_to_type: issueForm.value.issued_to_type,
        issued_to_id: Number(issueForm.value.issued_to_id),
        quantity: Number(issueForm.value.quantity),
        issue_date: issueForm.value.issue_date,
        returnable: Boolean(issueForm.value.returnable),
    };

    try {
        await axios.post('/api/admin/inventory/issues', payload);
        showIssueModal.value = false;
        issueForm.value = createIssuePayload();
        await Promise.all([loadIssueRegister(), loadClientSupplies()]);
    } catch (error) {
        assignValidationErrors(issueErrors, error);
        alert(extractErrorMessage(error, 'Unable to record inventory issue.'));
    } finally {
        issueBusy.value = false;
    }
}

async function returnIssue(issue) {
    const pending = Number(issue.quantity || 0) - Number(issue.returned_qty || 0);

    if (pending <= 0) {
        alert('No pending quantity available for return.');
        return;
    }

    const input = prompt(`Enter return quantity (max ${pending}):`, `${pending}`);
    if (input === null) {
        return;
    }

    const returnedQty = Number(input);
    if (!returnedQty || returnedQty <= 0) {
        alert('Please enter a valid return quantity.');
        return;
    }

    issueReturnBusyId.value = issue.id;

    try {
        await axios.post(`/api/admin/inventory/issues/${issue.id}/returns`, { returned_qty: returnedQty });
        await Promise.all([loadIssueRegister(), loadClientSupplies()]);
    } catch (error) {
        alert(extractErrorMessage(error, 'Unable to post return.'));
    } finally {
        issueReturnBusyId.value = null;
    }
}

function openClientSupplyModal(supply = null) {
    editingClientSupply.value = supply;
    resetValidationErrors(clientSupplyErrors);

    if (supply) {
        clientSupplyForm.value = {
            id: supply.id,
            client_id: supply.client_id,
            item_id: supply.item_id,
            supplied_qty: Number(supply.supplied_qty),
            unit_rate: supply.unit_rate ?? '',
            supplied_on: supply.supplied_on,
            project_id: supply.project_id ?? '',
            notes: supply.notes ?? '',
        };
    } else {
        clientSupplyForm.value = createClientSupplyPayload();
    }

    showClientSupplyModal.value = true;
}

async function submitClientSupply() {
    clientSupplyBusy.value = true;
    resetValidationErrors(clientSupplyErrors);

    const payload = {
        client_id: Number(clientSupplyForm.value.client_id),
        item_id: Number(clientSupplyForm.value.item_id),
        supplied_qty: Number(clientSupplyForm.value.supplied_qty),
        unit_rate: clientSupplyForm.value.unit_rate === '' ? null : Number(clientSupplyForm.value.unit_rate),
        supplied_on: clientSupplyForm.value.supplied_on,
        project_id: clientSupplyForm.value.project_id ? Number(clientSupplyForm.value.project_id) : null,
        notes: clientSupplyForm.value.notes || null,
    };

    try {
        if (clientSupplyForm.value.id) {
            await axios.put(`/api/admin/inventory/client-supplies/${clientSupplyForm.value.id}`, {
                supplied_qty: payload.supplied_qty,
                unit_rate: payload.unit_rate,
                supplied_on: payload.supplied_on,
                project_id: payload.project_id,
                notes: payload.notes,
            });
        } else {
            await axios.post('/api/admin/inventory/client-supplies', payload);
        }

        showClientSupplyModal.value = false;
        clientSupplyForm.value = createClientSupplyPayload();
        await Promise.all([loadClientSupplies(), loadIssueRegister()]);
    } catch (error) {
        assignValidationErrors(clientSupplyErrors, error);
        alert(extractErrorMessage(error, 'Unable to save client supply.'));
    } finally {
        clientSupplyBusy.value = false;
    }
}

async function deleteClientSupply(id) {
    if (!confirm('Delete this client supply record and restore stock?')) {
        return;
    }

    try {
        await axios.delete(`/api/admin/inventory/client-supplies/${id}`);
        await Promise.all([loadClientSupplies(), loadIssueRegister()]);
    } catch (error) {
        alert(extractErrorMessage(error, 'Unable to delete client supply.'));
    }
}

function openAssetCategoryModal(category = null) {
    editingAssetCategory.value = category;

    if (category) {
        assetCategoryForm.id = category.id;
        assetCategoryForm.name = category.name;
        assetCategoryForm.description = category.description || '';
        assetCategoryForm.is_electronic = Boolean(category.is_electronic);
    } else {
        assetCategoryForm.reset();
        assetCategoryForm.id = null;
        assetCategoryForm.is_electronic = false;
    }

    showAssetCategoryModal.value = true;
}

function submitAssetCategory() {
    if (assetCategoryForm.id) {
        assetCategoryForm.put(route('admin.assets.categories.update', assetCategoryForm.id), {
            onSuccess: () => {
                showAssetCategoryModal.value = false;
                assetCategoryForm.reset();
            },
        });

        return;
    }

    assetCategoryForm.post(route('admin.assets.categories.store'), {
        onSuccess: () => {
            showAssetCategoryModal.value = false;
            assetCategoryForm.reset();
        },
    });
}

function deleteAssetCategory(id) {
    if (!confirm('Delete this asset category? Existing linked assets may be affected.')) {
        return;
    }

    useForm({}).delete(route('admin.assets.categories.destroy', id), { preserveScroll: true });
}

function openVendorModal(vendor = null) {
    editingVendor.value = vendor;

    if (vendor) {
        vendorForm.id = vendor.id;
        vendorForm.name = vendor.name;
        vendorForm.email = vendor.email || '';
        vendorForm.phone = vendor.phone || '';
        vendorForm.contact_person = vendor.contact_person || '';
        vendorForm.service_type = vendor.service_type || 'General';
    } else {
        vendorForm.reset();
        vendorForm.id = null;
        vendorForm.service_type = 'General';
    }

    showVendorModal.value = true;
}

function submitVendor() {
    if (vendorForm.id) {
        vendorForm.put(route('admin.vendors.update', vendorForm.id), {
            onSuccess: () => {
                showVendorModal.value = false;
                vendorForm.reset();
            },
        });

        return;
    }

    vendorForm.post(route('admin.vendors.store'), {
        onSuccess: () => {
            showVendorModal.value = false;
            vendorForm.reset();
        },
    });
}

function deleteVendor(id) {
    if (!confirm('Delete this vendor from the approved list?')) {
        return;
    }

    useForm({}).delete(route('admin.vendors.destroy', id), { preserveScroll: true });
}

function openStoreCategoryModal(category = null, parentId = '') {
    editingStoreCategory.value = category;
    resetValidationErrors(storeCategoryErrors);

    if (category) {
        storeCategoryForm.value = {
            id: category.id,
            parent_id: category.parent_id || '',
            name: category.name || '',
            code: category.code || '',
            item_type: category.item_type || 'Consumable',
        };
    } else {
        storeCategoryForm.value = createStoreCategoryPayload();
        storeCategoryForm.value.parent_id = parentId;
    }

    showStoreCategoryModal.value = true;
}

async function submitStoreCategory() {
    storeCategoryBusy.value = true;
    resetValidationErrors(storeCategoryErrors);

    const payload = {
        parent_id: storeCategoryForm.value.parent_id || null,
        name: storeCategoryForm.value.name,
        code: storeCategoryForm.value.code || null,
        item_type: storeCategoryForm.value.item_type,
    };

    try {
        if (storeCategoryForm.value.id) {
            await axios.put(`/api/admin/inventory/category-tree/${storeCategoryForm.value.id}`, payload);
        } else {
            await axios.post('/api/admin/inventory/category-tree', payload);
        }

        showStoreCategoryModal.value = false;
        storeCategoryForm.value = createStoreCategoryPayload();
        await loadStoreCategories();
    } catch (error) {
        assignValidationErrors(storeCategoryErrors, error);
        alert(extractErrorMessage(error, 'Unable to save store category.'));
    } finally {
        storeCategoryBusy.value = false;
    }
}

async function deleteStoreCategory(id) {
    if (!confirm('Delete this store category? It must have no subcategories or mapped items.')) {
        return;
    }

    try {
        await axios.delete(`/api/admin/inventory/category-tree/${id}`);
        await loadStoreCategories();
    } catch (error) {
        alert(extractErrorMessage(error, 'Unable to delete store category.'));
    }
}

function openLocationModal(node = null, parentId = '') {
    editingLocationNode.value = node;
    resetValidationErrors(locationErrors);

    if (node) {
        locationForm.value = {
            id: node.id,
            parent_id: node.parent_id || '',
            node_type: node.node_type || 'Branch',
            name: node.name || '',
            code: node.code || '',
            capacity: node.capacity || '',
            is_active: node.is_active !== false,
        };
    } else {
        locationForm.value = createLocationPayload();
        locationForm.value.parent_id = parentId;
    }

    showLocationModal.value = true;
}

async function submitLocationNode() {
    locationBusy.value = true;
    resetValidationErrors(locationErrors);

    const payload = {
        parent_id: locationForm.value.parent_id || null,
        node_type: locationForm.value.node_type,
        name: locationForm.value.name,
        code: locationForm.value.code || null,
        capacity: locationForm.value.capacity === '' ? null : Number(locationForm.value.capacity),
        is_active: locationForm.value.is_active,
    };

    try {
        if (locationForm.value.id) {
            await axios.put(`/api/admin/assets/location-nodes/${locationForm.value.id}`, payload);
        } else {
            await axios.post('/api/admin/assets/location-nodes', payload);
        }

        showLocationModal.value = false;
        locationForm.value = createLocationPayload();
        await Promise.all([loadLocationNodes(), loadRecurringRules()]);
    } catch (error) {
        assignValidationErrors(locationErrors, error);
        alert(extractErrorMessage(error, 'Unable to save location node.'));
    } finally {
        locationBusy.value = false;
    }
}

async function deleteLocationNode(id) {
    if (!confirm('Delete this location node? It must have no child nodes or active mappings.')) {
        return;
    }

    try {
        await axios.delete(`/api/admin/assets/location-nodes/${id}`);
        await Promise.all([loadLocationNodes(), loadRecurringRules()]);
    } catch (error) {
        alert(extractErrorMessage(error, 'Unable to delete location node.'));
    }
}

function openRecurringModal(rule = null) {
    editingRecurringRule.value = rule;
    resetValidationErrors(recurringErrors);

    if (rule) {
        recurringForm.value = {
            id: rule.id,
            item_id: rule.item_id,
            scope_type: rule.scope_type || 'LocationNode',
            scope_id: rule.scope_id,
            frequency: rule.frequency || 'Monthly',
            day_of_week: Number(rule.day_of_week ?? 1),
            day_of_month: Number(rule.day_of_month ?? 1),
            expected_qty: Number(rule.expected_qty || 1),
            auto_create_request: Boolean(rule.auto_create_request),
            is_active: Boolean(rule.is_active),
        };
    } else {
        recurringForm.value = createRecurringPayload();
    }

    showRecurringModal.value = true;
}

async function submitRecurringRule() {
    recurringBusy.value = true;
    resetValidationErrors(recurringErrors);

    const createPayload = {
        item_id: recurringForm.value.item_id,
        scope_type: recurringForm.value.scope_type,
        scope_id: recurringForm.value.scope_id,
        frequency: recurringForm.value.frequency,
        day_of_week: recurringForm.value.frequency === 'Weekly' ? Number(recurringForm.value.day_of_week) : null,
        day_of_month: recurringForm.value.frequency === 'Monthly' ? Number(recurringForm.value.day_of_month) : null,
        expected_qty: Number(recurringForm.value.expected_qty),
        auto_create_request: recurringForm.value.auto_create_request,
        is_active: recurringForm.value.is_active,
    };

    const updatePayload = {
        frequency: recurringForm.value.frequency,
        day_of_week: recurringForm.value.frequency === 'Weekly' ? Number(recurringForm.value.day_of_week) : null,
        day_of_month: recurringForm.value.frequency === 'Monthly' ? Number(recurringForm.value.day_of_month) : null,
        expected_qty: Number(recurringForm.value.expected_qty),
        auto_create_request: recurringForm.value.auto_create_request,
        is_active: recurringForm.value.is_active,
    };

    try {
        if (recurringForm.value.id) {
            await axios.put(`/api/admin/inventory/recurring-rules/${recurringForm.value.id}`, updatePayload);
        } else {
            await axios.post('/api/admin/inventory/recurring-rules', createPayload);
        }

        showRecurringModal.value = false;
        recurringForm.value = createRecurringPayload();
        await loadRecurringRules();
    } catch (error) {
        assignValidationErrors(recurringErrors, error);
        alert(extractErrorMessage(error, 'Unable to save recurring rule.'));
    } finally {
        recurringBusy.value = false;
    }
}

async function deleteRecurringRule(id) {
    if (!confirm('Delete this recurring usage rule?')) {
        return;
    }

    try {
        await axios.delete(`/api/admin/inventory/recurring-rules/${id}`);
        await loadRecurringRules();
    } catch (error) {
        alert(extractErrorMessage(error, 'Unable to delete recurring rule.'));
    }
}

onMounted(() => {
    loadPracticalWorkspace();
});
</script>

<template>
    <div class="flex flex-col xl:flex-row gap-8 min-h-[700px] font-outfit animate-in fade-in duration-700">
        <aside class="w-full xl:w-[320px] flex-shrink-0 space-y-6">
            <div class="bg-white p-3 rounded-3xl border border-slate-200 shadow-sm space-y-1">
                <button
                    v-for="tab in practicalTabs"
                    :key="tab.id"
                    @click="activeSubTab = tab.id"
                    class="w-full flex items-center justify-between px-6 py-4 rounded-2xl text-[11px] font-bold uppercase tracking-widest transition-all text-left"
                    :class="activeSubTab === tab.id ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                >
                    <span class="flex items-center gap-4">
                        <component :is="tab.icon" class="w-5 h-5" />
                        {{ tab.label }}
                    </span>
                    <ChevronRightIcon v-if="activeSubTab === tab.id" class="w-4 h-4 text-white/60" />
                </button>
            </div>

            <div class="p-8 bg-slate-900 rounded-3xl border border-slate-800 text-white text-left">
                <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                    <MapPinIcon class="w-6 h-6 text-white" />
                </div>
                <h4 class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-200">Practical Workspace</h4>
                <p class="text-[11px] font-medium leading-relaxed text-slate-300 mt-4">
                    Build the real operating map for assets, room storage, vendors, and recurring issue plans from one place.
                </p>
            </div>
        </aside>

        <div class="flex-1 space-y-8">
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div
                    v-for="stat in practicalStats"
                    :key="stat.label"
                    class="rounded-3xl border bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ stat.label }}</p>
                            <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ stat.value }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl border flex items-center justify-center" :class="stat.tone">
                            <component :is="stat.icon" class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="loadingPracticalData" class="bg-white rounded-3xl border border-slate-200 p-10 shadow-sm flex items-center gap-4 text-slate-500">
                <ArrowPathIcon class="w-5 h-5 animate-spin" />
                Loading practical configuration workspace...
            </section>

            <section v-else-if="activeSubTab === 'asset-categories'" class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Asset Categories</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Define the capital asset catalogue used across procurement, issue, maintenance, and audits.</p>
                    </div>
                    <button @click="openAssetCategoryModal()" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-indigo-700 transition-all flex items-center gap-3">
                        <PlusIcon class="w-4 h-4" />
                        Add Asset Category
                    </button>
                </header>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div v-for="category in categories" :key="category.id" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-left">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ category.name }}</p>
                                <p class="text-[11px] font-medium text-slate-500 mt-2">{{ category.description || 'No description added yet.' }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-4">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-widest rounded-lg">{{ category.assets_count || 0 }} Assets</span>
                                    <span v-if="category.is_electronic" class="px-3 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-amber-100">Electronic</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openAssetCategoryModal(category)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                    <PencilSquareIcon class="w-4 h-4 mx-auto" />
                                </button>
                                <button @click="deleteAssetCategory(category.id)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all">
                                    <TrashIcon class="w-4 h-4 mx-auto" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else-if="activeSubTab === 'store-categories'" class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Store Category Hierarchy</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Create consumable and non-consumable structures like pantry, stationery, client kits, and housekeeping supplies.</p>
                    </div>
                    <button @click="openStoreCategoryModal()" class="h-12 px-8 bg-emerald-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-emerald-700 transition-all flex items-center gap-3">
                        <PlusIcon class="w-4 h-4" />
                        Add Store Category
                    </button>
                </header>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-[1.6fr,0.7fr,0.7fr,0.5fr] gap-4 px-6 py-4 border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <span>Category Path</span>
                        <span>Type</span>
                        <span>Mapped Items</span>
                        <span class="text-right">Actions</span>
                    </div>
                    <div v-if="!flatStoreCategories.length" class="px-6 py-10 text-sm text-slate-500">No store categories configured yet.</div>
                    <div v-for="category in flatStoreCategories" :key="category.id" class="grid grid-cols-[1.6fr,0.7fr,0.7fr,0.5fr] gap-4 px-6 py-5 border-b border-slate-100 items-center text-left">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-slate-300" :style="{ width: `${category.depth * 18}px` }"></span>
                            <div>
                                <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ category.name }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">{{ category.path_label }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">{{ category.item_type }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ category.items_count || 0 }}</span>
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openStoreCategoryModal(null, category.id)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                                <PlusIcon class="w-4 h-4 mx-auto" />
                            </button>
                            <button @click="openStoreCategoryModal(category)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                <PencilSquareIcon class="w-4 h-4 mx-auto" />
                            </button>
                            <button @click="deleteStoreCategory(category.id)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all">
                                <TrashIcon class="w-4 h-4 mx-auto" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else-if="activeSubTab === 'locations'" class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Location Designer</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Model branches, floors, rooms, lockers, cupboards, and shelves so assets and records can be mapped to real spaces.</p>
                    </div>
                    <button @click="openLocationModal()" class="h-12 px-8 bg-amber-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-amber-700 transition-all flex items-center gap-3">
                        <PlusIcon class="w-4 h-4" />
                        Add Location Node
                    </button>
                </header>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-[1.6fr,0.6fr,0.9fr,0.9fr,0.9fr,0.5fr] gap-4 px-6 py-4 border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <span>Location Path</span>
                        <span>Type</span>
                        <span>Assets</span>
                        <span>Store</span>
                        <span>Docs</span>
                        <span class="text-right">Actions</span>
                    </div>
                    <div v-if="!flatLocationNodes.length" class="px-6 py-10 text-sm text-slate-500">No location structure created yet.</div>
                    <div v-for="node in flatLocationNodes" :key="node.id" class="grid grid-cols-[1.6fr,0.6fr,0.9fr,0.9fr,0.9fr,0.5fr] gap-4 px-6 py-5 border-b border-slate-100 items-center text-left">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-slate-300" :style="{ width: `${node.depth * 18}px` }"></span>
                            <div>
                                <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ node.name }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">{{ node.path_label }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">{{ node.node_type }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ node.mapped_assets_count || 0 }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ node.mapped_inventory_count || 0 }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ node.mapped_documents_count || 0 }}</span>
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openLocationModal(null, node.id)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-200 transition-all">
                                <PlusIcon class="w-4 h-4 mx-auto" />
                            </button>
                            <button @click="openLocationModal(node)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                <PencilSquareIcon class="w-4 h-4 mx-auto" />
                            </button>
                            <button @click="deleteLocationNode(node.id)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all">
                                <TrashIcon class="w-4 h-4 mx-auto" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else-if="activeSubTab === 'recurring'" class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Recurring Consumption Planner</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Define weekly and monthly issue patterns for rooms, cupboards, or specific store points so procurement sees demand before stockouts happen.</p>
                    </div>
                    <button @click="openRecurringModal()" class="h-12 px-8 bg-rose-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-rose-700 transition-all flex items-center gap-3">
                        <PlusIcon class="w-4 h-4" />
                        Add Recurring Rule
                    </button>
                </header>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div v-for="rule in recurringRules" :key="rule.id" class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm text-left">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border" :class="rule.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200'">
                                        {{ rule.is_active ? 'Active' : 'Paused' }}
                                    </span>
                                    <span class="px-3 py-1 bg-rose-50 text-rose-700 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-rose-100">{{ rule.frequency }}</span>
                                </div>
                                <p class="text-lg font-black text-slate-900 mt-4 uppercase tracking-tight">{{ rule.item?.name || 'Unknown Item' }}</p>
                                <p class="text-[11px] text-slate-500 mt-2">{{ locationName(rule.scope_id) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openRecurringModal(rule)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                    <PencilSquareIcon class="w-4 h-4 mx-auto" />
                                </button>
                                <button @click="deleteRecurringRule(rule.id)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all">
                                    <TrashIcon class="w-4 h-4 mx-auto" />
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Schedule</p>
                                <p class="text-sm font-black text-slate-900 mt-2">{{ recurringScheduleLabel(rule) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Expected Qty</p>
                                <p class="text-sm font-black text-slate-900 mt-2">{{ Number(rule.expected_qty || 0) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Auto Request</p>
                                <p class="text-sm font-black text-slate-900 mt-2">{{ rule.auto_create_request ? 'Enabled' : 'Manual' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!recurringRules.length" class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm text-sm text-slate-500">
                    No recurring rules configured yet.
                </div>
            </section>

            <section v-else-if="activeSubTab === 'issue-register'" class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Issue Register</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Track employee issues, office usage, project allocations, and return closures with stock impact.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="exportIssueRegister" class="h-12 px-6 bg-white text-cyan-700 border border-cyan-200 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-cyan-50 transition-all">
                            Export CSV
                        </button>
                        <button @click="openIssueModal()" class="h-12 px-8 bg-cyan-700 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-cyan-800 transition-all flex items-center gap-3">
                            <PlusIcon class="w-4 h-4" />
                            Record Issue
                        </button>
                    </div>
                </header>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                        <div class="space-y-2">
                            <InputLabel value="Item" />
                            <BaseSelect v-model="issueFilters.item_id">
                                <option value="">All Items</option>
                                <option v-for="item in issueMeta.items" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Issue Type" />
                            <BaseSelect v-model="issueFilters.issue_type">
                                <option value="">All Types</option>
                                <option v-for="type in (issueMeta.issue_types || [])" :key="type" :value="type">{{ type }}</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Status" />
                            <BaseSelect v-model="issueFilters.status">
                                <option value="">All Status</option>
                                <option value="Open">Open</option>
                                <option value="Partial_Returned">Partial Returned</option>
                                <option value="Closed">Closed</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Issued To Type" />
                            <BaseSelect v-model="issueFilters.issued_to_type">
                                <option value="">All Targets</option>
                                <option v-for="type in (issueMeta.issued_to_types || [])" :key="type" :value="type">{{ type }}</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="From Date" />
                            <TextInput v-model="issueFilters.from_date" type="date" />
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="To Date" />
                            <TextInput v-model="issueFilters.to_date" type="date" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-4">
                        <button @click="resetIssueFilters" class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-50">Reset</button>
                        <button @click="applyIssueFilters" class="h-10 px-5 bg-cyan-700 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-cyan-800">Apply Filters</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-3xl border bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Open Issues</p>
                        <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ issueSummary.open || 0 }}</p>
                    </div>
                    <div class="rounded-3xl border bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pending Returns</p>
                        <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ issueSummary.pending_return || 0 }}</p>
                    </div>
                    <div class="rounded-3xl border bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Entries</p>
                        <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ issueLines.length }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-[1.2fr,0.8fr,0.8fr,0.6fr,0.6fr,0.7fr] gap-4 px-6 py-4 border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <span>Item / Target</span>
                        <span>Issue Type</span>
                        <span>Date</span>
                        <span>Qty</span>
                        <span>Status</span>
                        <span class="text-right">Action</span>
                    </div>
                    <div v-if="!issueLines.length" class="px-6 py-10 text-sm text-slate-500">No issue entries recorded yet.</div>
                    <div v-for="line in issueLines" :key="line.id" class="grid grid-cols-[1.2fr,0.8fr,0.8fr,0.6fr,0.6fr,0.7fr] gap-4 px-6 py-5 border-b border-slate-100 items-center text-left">
                        <div>
                            <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ line.item?.name || 'Unknown Item' }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">{{ line.issued_to_type }}: {{ line.issued_to_label }}</p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">{{ line.issue_type }}</span>
                        <span class="text-[11px] text-slate-700">{{ line.issue_date }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ Number(line.quantity || 0) }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-widest" :class="line.status === 'Closed' ? 'text-emerald-700' : 'text-amber-700'">{{ line.status }}</span>
                        <div class="flex items-center justify-end gap-2">
                            <button
                                v-if="line.returnable && line.status !== 'Closed'"
                                @click="returnIssue(line)"
                                :disabled="issueReturnBusyId === line.id"
                                class="h-9 px-3 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-700 text-[10px] font-bold uppercase tracking-widest disabled:opacity-50"
                            >
                                {{ issueReturnBusyId === line.id ? 'Posting...' : 'Return' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else-if="activeSubTab === 'client-supplies'" class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Client Supply Register</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Capture billable consumables delivered to clients and link each movement to projects and stock impact.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="exportClientSupplyRegister" class="h-12 px-6 bg-white text-teal-700 border border-teal-200 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-teal-50 transition-all">
                            Export CSV
                        </button>
                        <button @click="openClientSupplyModal()" class="h-12 px-8 bg-teal-700 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-teal-800 transition-all flex items-center gap-3">
                            <PlusIcon class="w-4 h-4" />
                            Add Client Supply
                        </button>
                    </div>
                </header>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        <div class="space-y-2">
                            <InputLabel value="Client" />
                            <BaseSelect v-model="clientSupplyFilters.client_id">
                                <option value="">All Clients</option>
                                <option v-for="client in clientSupplyMeta.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Item" />
                            <BaseSelect v-model="clientSupplyFilters.item_id">
                                <option value="">All Items</option>
                                <option v-for="item in clientSupplyMeta.items" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Project" />
                            <BaseSelect v-model="clientSupplyFilters.project_id">
                                <option value="">All Projects</option>
                                <option v-for="project in clientSupplyMeta.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                            </BaseSelect>
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="From Date" />
                            <TextInput v-model="clientSupplyFilters.from_date" type="date" />
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="To Date" />
                            <TextInput v-model="clientSupplyFilters.to_date" type="date" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-4">
                        <button @click="resetClientSupplyFilters" class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-50">Reset</button>
                        <button @click="applyClientSupplyFilters" class="h-10 px-5 bg-teal-700 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-teal-800">Apply Filters</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-3xl border bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Records</p>
                        <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ clientSupplySummary.records || 0 }}</p>
                    </div>
                    <div class="rounded-3xl border bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Supplied Qty</p>
                        <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ Number(clientSupplySummary.total_qty || 0).toFixed(2) }}</p>
                    </div>
                    <div class="rounded-3xl border bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Approx Value</p>
                        <p class="text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ Number(clientSupplySummary.total_value || 0).toFixed(2) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-[1fr,1fr,0.8fr,0.6fr,0.6fr,0.8fr] gap-4 px-6 py-4 border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <span>Client</span>
                        <span>Item / Project</span>
                        <span>Date</span>
                        <span>Qty</span>
                        <span>Rate</span>
                        <span class="text-right">Actions</span>
                    </div>
                    <div v-if="!clientSupplies.length" class="px-6 py-10 text-sm text-slate-500">No client supplies recorded yet.</div>
                    <div v-for="supply in clientSupplies" :key="supply.id" class="grid grid-cols-[1fr,1fr,0.8fr,0.6fr,0.6fr,0.8fr] gap-4 px-6 py-5 border-b border-slate-100 items-center text-left">
                        <div>
                            <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ supply.client?.name || 'Unknown Client' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ supply.item?.name || 'Unknown Item' }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">{{ supply.project?.name || 'No project linked' }}</p>
                        </div>
                        <span class="text-[11px] text-slate-700">{{ supply.supplied_on }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ Number(supply.supplied_qty || 0) }}</span>
                        <span class="text-sm font-black text-slate-900 tabular-nums">{{ supply.unit_rate ?? '-' }}</span>
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openClientSupplyModal(supply)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                <PencilSquareIcon class="w-4 h-4 mx-auto" />
                            </button>
                            <button @click="deleteClientSupply(supply.id)" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all">
                                <TrashIcon class="w-4 h-4 mx-auto" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else class="space-y-6">
                <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-left">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Vendor Directory</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Approved suppliers, maintenance partners, and store replenishment vendors.</p>
                    </div>
                    <button @click="openVendorModal()" class="h-12 px-8 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-slate-700 transition-all flex items-center gap-3">
                        <PlusIcon class="w-4 h-4" />
                        Add Vendor
                    </button>
                </header>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div v-for="vendor in vendors" :key="vendor.id" class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm text-left">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ vendor.name }}</p>
                                <p class="text-[11px] text-slate-500 mt-2">{{ vendor.contact_person || 'No primary contact added' }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-4">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-widest rounded-lg">{{ vendor.service_type || 'General' }}</span>
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-indigo-100">{{ vendor.assets_count || 0 }} Assets</span>
                                </div>
                                <div class="mt-4 text-[11px] text-slate-500 space-y-1">
                                    <p>{{ vendor.phone || 'No phone added' }}</p>
                                    <p>{{ vendor.email || 'No email added' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openVendorModal(vendor)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                    <PencilSquareIcon class="w-4 h-4 mx-auto" />
                                </button>
                                <button @click="deleteVendor(vendor.id)" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all">
                                    <TrashIcon class="w-4 h-4 mx-auto" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <PremiumModal :show="showAssetCategoryModal" @close="showAssetCategoryModal = false" :title="editingAssetCategory ? 'Edit Asset Category' : 'Add Asset Category'" subtitle="Standardise your asset master list">
            <form @submit.prevent="submitAssetCategory" class="space-y-8 p-6 text-left">
                <div class="space-y-3">
                    <InputLabel value="Category Name" />
                    <TextInput v-model="assetCategoryForm.name" placeholder="Laptop, Router, Biometric Device" required />
                    <InputError :message="assetCategoryForm.errors.name" />
                </div>
                <div class="space-y-3">
                    <InputLabel value="Description" />
                    <textarea v-model="assetCategoryForm.description" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-300 focus:ring-indigo-200 resize-none" placeholder="Where this asset category is used and how it is managed."></textarea>
                    <InputError :message="assetCategoryForm.errors.description" />
                </div>
                <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer">
                    <span>
                        <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Electronic Asset</span>
                        <span class="block text-[11px] text-slate-500 mt-1">Flag categories that need serial tracking, power, or maintenance.</span>
                    </span>
                    <input v-model="assetCategoryForm.is_electronic" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                </label>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showAssetCategoryModal = false">Cancel</button>
                    <button type="submit" :disabled="assetCategoryForm.processing" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="assetCategoryForm.processing" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Category
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showIssueModal" @close="showIssueModal = false" title="Record Inventory Issue" subtitle="Log stock issued to users, employees, clients, or projects">
            <form @submit.prevent="submitIssue" class="space-y-8 p-6 text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Item" />
                        <BaseSelect v-model="issueForm.item_id">
                            <option value="">Select item</option>
                            <option v-for="item in issueMeta.items" :key="item.id" :value="item.id">{{ item.name }} (Stock: {{ item.current_stock }})</option>
                        </BaseSelect>
                        <InputError :message="issueErrors.item_id?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Issue Type" />
                        <BaseSelect v-model="issueForm.issue_type">
                            <option v-for="type in (issueMeta.issue_types || ['Office_Consumption','Employee_Issue','Client_Delivery','Project_Use'])" :key="type" :value="type">{{ type }}</option>
                        </BaseSelect>
                        <InputError :message="issueErrors.issue_type?.[0]" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Issue Target Type" />
                        <BaseSelect v-model="issueForm.issued_to_type" @change="resetIssueTarget">
                            <option v-for="type in (issueMeta.issued_to_types || ['User','Employee','Client','Project'])" :key="type" :value="type">{{ type }}</option>
                        </BaseSelect>
                        <InputError :message="issueErrors.issued_to_type?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Issue Target" />
                        <BaseSelect v-model="issueForm.issued_to_id">
                            <option value="">Select target</option>
                            <option v-for="target in issueIssuedToOptions" :key="target.id" :value="target.id">{{ target.name }}</option>
                        </BaseSelect>
                        <InputError :message="issueErrors.issued_to_id?.[0]" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Quantity" />
                        <TextInput v-model="issueForm.quantity" type="number" step="0.01" min="0.01" />
                        <InputError :message="issueErrors.quantity?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Issue Date" />
                        <TextInput v-model="issueForm.issue_date" type="date" />
                        <InputError :message="issueErrors.issue_date?.[0]" />
                    </div>
                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 mt-8 cursor-pointer">
                        <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Returnable</span>
                        <input v-model="issueForm.returnable" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-cyan-700 focus:ring-cyan-500" />
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showIssueModal = false">Cancel</button>
                    <button type="submit" :disabled="issueBusy" class="h-12 px-8 bg-cyan-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="issueBusy" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Issue
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showClientSupplyModal" @close="showClientSupplyModal = false" :title="editingClientSupply ? 'Edit Client Supply' : 'Add Client Supply'" subtitle="Record delivered consumables and stock impact">
            <form @submit.prevent="submitClientSupply" class="space-y-8 p-6 text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Client" />
                        <BaseSelect v-model="clientSupplyForm.client_id" :disabled="Boolean(clientSupplyForm.id)">
                            <option value="">Select client</option>
                            <option v-for="client in clientSupplyMeta.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                        </BaseSelect>
                        <InputError :message="clientSupplyErrors.client_id?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Item" />
                        <BaseSelect v-model="clientSupplyForm.item_id" :disabled="Boolean(clientSupplyForm.id)">
                            <option value="">Select item</option>
                            <option v-for="item in clientSupplyMeta.items" :key="item.id" :value="item.id">{{ item.name }} (Stock: {{ item.current_stock }})</option>
                        </BaseSelect>
                        <InputError :message="clientSupplyErrors.item_id?.[0]" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Project" />
                        <BaseSelect v-model="clientSupplyForm.project_id">
                            <option value="">No project linked</option>
                            <option v-for="project in clientSupplyMeta.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                        </BaseSelect>
                        <InputError :message="clientSupplyErrors.project_id?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Supply Date" />
                        <TextInput v-model="clientSupplyForm.supplied_on" type="date" />
                        <InputError :message="clientSupplyErrors.supplied_on?.[0]" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Quantity" />
                        <TextInput v-model="clientSupplyForm.supplied_qty" type="number" step="0.01" min="0.01" />
                        <InputError :message="clientSupplyErrors.supplied_qty?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Unit Rate" />
                        <TextInput v-model="clientSupplyForm.unit_rate" type="number" step="0.01" min="0" placeholder="Optional" />
                        <InputError :message="clientSupplyErrors.unit_rate?.[0]" />
                    </div>
                </div>

                <div class="space-y-3">
                    <InputLabel value="Notes" />
                    <textarea v-model="clientSupplyForm.notes" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-teal-300 focus:ring-teal-200 resize-none" placeholder="Dispatch info, vehicle, or packing notes."></textarea>
                    <InputError :message="clientSupplyErrors.notes?.[0]" />
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showClientSupplyModal = false">Cancel</button>
                    <button type="submit" :disabled="clientSupplyBusy" class="h-12 px-8 bg-teal-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="clientSupplyBusy" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Supply
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showVendorModal" @close="showVendorModal = false" :title="editingVendor ? 'Edit Vendor' : 'Add Vendor'" subtitle="Save supplier and service partner details">
            <form @submit.prevent="submitVendor" class="space-y-8 p-6 text-left">
                <div class="space-y-3">
                    <InputLabel value="Vendor Name" />
                    <TextInput v-model="vendorForm.name" placeholder="KNR Stationery Supplies" required />
                    <InputError :message="vendorForm.errors.name" />
                </div>
                <div class="space-y-3">
                    <InputLabel value="Primary Contact" />
                    <TextInput v-model="vendorForm.contact_person" placeholder="Contact person" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Phone" />
                        <TextInput v-model="vendorForm.phone" placeholder="Phone number" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Email" />
                        <TextInput v-model="vendorForm.email" type="email" placeholder="Email address" />
                    </div>
                </div>
                <div class="space-y-3">
                    <InputLabel value="Service Type" />
                    <TextInput v-model="vendorForm.service_type" placeholder="Consumables, IT AMC, Furniture" />
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showVendorModal = false">Cancel</button>
                    <button type="submit" :disabled="vendorForm.processing" class="h-12 px-8 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="vendorForm.processing" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Vendor
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showStoreCategoryModal" @close="showStoreCategoryModal = false" :title="editingStoreCategory ? 'Edit Store Category' : 'Add Store Category'" subtitle="Build the consumable and store hierarchy">
            <form @submit.prevent="submitStoreCategory" class="space-y-8 p-6 text-left">
                <div class="space-y-3">
                    <InputLabel value="Parent Category" />
                    <BaseSelect v-model="storeCategoryForm.parent_id">
                        <option value="">Top Level</option>
                        <option v-for="category in flatStoreCategories" :key="category.id" :value="category.id">{{ category.path_label }}</option>
                    </BaseSelect>
                    <p class="text-[11px] text-slate-500">Use subcategories for pantry, stationery, housekeeping, and client issue groupings.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Category Name" />
                        <TextInput v-model="storeCategoryForm.name" placeholder="Pantry Tea Premix" required />
                        <InputError :message="storeCategoryErrors.name?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Code" />
                        <TextInput v-model="storeCategoryForm.code" placeholder="PAN-TEA" />
                        <InputError :message="storeCategoryErrors.code?.[0]" />
                    </div>
                </div>
                <div class="space-y-3">
                    <InputLabel value="Item Type" />
                    <BaseSelect v-model="storeCategoryForm.item_type">
                        <option v-for="option in inventoryTypeOptions" :key="option" :value="option">{{ option }}</option>
                    </BaseSelect>
                    <InputError :message="storeCategoryErrors.item_type?.[0]" />
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showStoreCategoryModal = false">Cancel</button>
                    <button type="submit" :disabled="storeCategoryBusy" class="h-12 px-8 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="storeCategoryBusy" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Store Category
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showLocationModal" @close="showLocationModal = false" :title="editingLocationNode ? 'Edit Location Node' : 'Add Location Node'" subtitle="Design the physical storage and custody tree">
            <form @submit.prevent="submitLocationNode" class="space-y-8 p-6 text-left">
                <div class="space-y-3">
                    <InputLabel value="Parent Location" />
                    <BaseSelect v-model="locationForm.parent_id">
                        <option value="">Top Level</option>
                        <option v-for="node in flatLocationNodes" :key="node.id" :value="node.id">{{ node.path_label }}</option>
                    </BaseSelect>
                    <InputError :message="locationErrors.parent_id?.[0]" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Node Type" />
                        <BaseSelect v-model="locationForm.node_type">
                            <option v-for="type in locationTypeOptions" :key="type" :value="type">{{ type }}</option>
                        </BaseSelect>
                        <InputError :message="locationErrors.node_type?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Name" />
                        <TextInput v-model="locationForm.name" placeholder="HQ Floor 2 Store Room" required />
                        <InputError :message="locationErrors.name?.[0]" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Code" />
                        <TextInput v-model="locationForm.code" placeholder="HQ-F2-SR" />
                        <InputError :message="locationErrors.code?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Capacity" />
                        <TextInput v-model="locationForm.capacity" type="number" min="0" placeholder="Optional capacity" />
                        <InputError :message="locationErrors.capacity?.[0]" />
                    </div>
                </div>
                <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer">
                    <span>
                        <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Active Location</span>
                        <span class="block text-[11px] text-slate-500 mt-1">Turn off old storage points without deleting the history.</span>
                    </span>
                    <input v-model="locationForm.is_active" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                </label>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showLocationModal = false">Cancel</button>
                    <button type="submit" :disabled="locationBusy" class="h-12 px-8 bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="locationBusy" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Location
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showRecurringModal" @close="showRecurringModal = false" :title="editingRecurringRule ? 'Edit Recurring Rule' : 'Add Recurring Rule'" subtitle="Schedule predictable room and store consumption">
            <form @submit.prevent="submitRecurringRule" class="space-y-8 p-6 text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Inventory Item" />
                        <BaseSelect v-model="recurringForm.item_id" :disabled="Boolean(recurringForm.id)">
                            <option value="">Select Item</option>
                            <option v-for="item in recurringMeta.items" :key="item.id" :value="item.id">{{ item.name }} ({{ item.current_stock }} {{ item.unit || 'units' }})</option>
                        </BaseSelect>
                        <InputError :message="recurringErrors.item_id?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Room or Storage Point" />
                        <BaseSelect v-model="recurringForm.scope_id" :disabled="Boolean(recurringForm.id)">
                            <option value="">Select Location</option>
                            <option v-for="location in locationOptions" :key="location.id" :value="location.id">{{ location.path_label }}</option>
                        </BaseSelect>
                        <InputError :message="recurringErrors.scope_id?.[0]" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-3">
                        <InputLabel value="Frequency" />
                        <BaseSelect v-model="recurringForm.frequency">
                            <option v-for="option in recurringFrequencyOptions" :key="option" :value="option">{{ option }}</option>
                        </BaseSelect>
                        <InputError :message="recurringErrors.frequency?.[0]" />
                    </div>
                    <div v-if="recurringForm.frequency === 'Weekly'" class="space-y-3">
                        <InputLabel value="Day of Week" />
                        <BaseSelect v-model="recurringForm.day_of_week">
                            <option v-for="option in weeklyDayOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </BaseSelect>
                        <InputError :message="recurringErrors.day_of_week?.[0]" />
                    </div>
                    <div v-else class="space-y-3">
                        <InputLabel value="Day of Month" />
                        <TextInput v-model="recurringForm.day_of_month" type="number" min="1" max="31" />
                        <InputError :message="recurringErrors.day_of_month?.[0]" />
                    </div>
                    <div class="space-y-3">
                        <InputLabel value="Expected Quantity" />
                        <TextInput v-model="recurringForm.expected_qty" type="number" min="0.01" step="0.01" />
                        <InputError :message="recurringErrors.expected_qty?.[0]" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer">
                        <span>
                            <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Auto Create Request</span>
                            <span class="block text-[11px] text-slate-500 mt-1">Send the item to restock flow when the schedule becomes due.</span>
                        </span>
                        <input v-model="recurringForm.auto_create_request" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500" />
                    </label>
                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 cursor-pointer">
                        <span>
                            <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Rule Active</span>
                            <span class="block text-[11px] text-slate-500 mt-1">Pause temporary demand without deleting the schedule.</span>
                        </span>
                        <input v-model="recurringForm.is_active" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500" />
                    </label>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400" @click="showRecurringModal = false">Cancel</button>
                    <button type="submit" :disabled="recurringBusy" class="h-12 px-8 bg-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3 disabled:opacity-50">
                        <ArrowPathIcon v-if="recurringBusy" class="w-4 h-4 animate-spin" />
                        <CheckCircleIcon v-else class="w-4 h-4" />
                        Save Rule
                    </button>
                </div>
            </form>
        </PremiumModal>
    </div>
</template>