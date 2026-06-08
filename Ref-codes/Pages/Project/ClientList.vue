<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Clients</h1>
            <button 
                v-can="'create-client'"
                @click="openModal()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Client
            </button>
        </div>

        <!-- Data Table -->
        <BaseDataTable
            :data="clients.data"
            :meta="clients"
            :columns="columns"
            :filters="filters"
            @search="handleSearch"
            @page-change="handlePageChange"
        >
            <template #cell-name="{ item: row }">
                <div>
                    <Link :href="route('projects.clients.show', { client: row.id })" class="font-medium text-indigo-600 hover:text-indigo-900 transition hover:underline">
                        {{ row.name }}
                    </Link>
                    <div class="text-xs text-gray-500 max-w-[200px] truncate">{{ row.email }}</div>
                </div>
            </template>
            
            <template #cell-status="{ item: row }">
                 <span v-if="row.portal_access" class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                    Portal Active
                 </span>
                 <span v-else class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                    Offline
                 </span>
            </template>

            <template #cell-contract_end="{ item: row }">
                <span v-if="row.contract_end" class="text-sm text-gray-700">
                    {{ new Date(row.contract_end).toLocaleDateString('en-GB') }}
                </span>
                <span v-else class="text-sm text-gray-400">Not set</span>
            </template>

            <template #rowActions="{ item: row }">
                <div class="flex items-center gap-2">
                    <button 
                        v-can="'edit-client'"
                        @click="openModal(row)"
                        class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                        title="Edit"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    <button 
                        v-can="'delete-client'"
                        @click="confirmDelete(row)"
                        class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Delete"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 000-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </template>
        </BaseDataTable>

        <!-- Create/Edit Modal -->
        <Modal 
            :show="showModal" 
            :title="isEditing ? 'Edit Client' : 'Add New Client'" 
            @close="closeModal"
            max-width="2xl"
        >
            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <div class="col-span-full">
                        <BaseInput
                            v-model="form.name"
                            label="Client Name"
                            :error="form.errors.name"
                            color="indigo"
                            required
                        />
                    </div>

                    <div>
                        <BaseInput
                            v-model="form.code"
                            label="Client Code"
                            :error="form.errors.code"
                            color="indigo"
                            placeholder="CLI-001"
                            required
                            class="uppercase"
                        />
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-gray-700 mb-1">Portal Access</label>
                         <div class="mt-2 flex items-center p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                            <input v-model="form.portal_access" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5" />
                            <span class="ml-2 text-sm text-gray-700 font-medium">Enable Client Portal</span>
                         </div>
                    </div>

                    <!-- Contact -->
                    <div class="col-span-full border-t border-gray-100 pt-4 mt-2">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Primary Contact</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput
                                v-model="form.contact_person"
                                label="Contact Person"
                                color="indigo"
                            />
                            <BaseInput
                                v-model="form.email"
                                label="Email Address"
                                type="email"
                                color="indigo"
                            />
                        </div>
                    </div>

                    <!-- Contract Dates -->
                     <div class="col-span-full border-t border-gray-100 pt-4 mt-2">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Service Contract</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput
                                v-model="form.contract_start"
                                label="Start Date"
                                type="date"
                                color="indigo"
                            />
                            <BaseInput
                                v-model="form.contract_end"
                                label="End Date"
                                type="date"
                                color="indigo"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium">
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-lg hover:shadow-lg hover:from-indigo-700 hover:to-violet-700 transition shadow-md font-medium" 
                        :disabled="form.processing"
                    >
                        {{ isEditing ? 'Update Client' : 'Create Client' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    clients: Object,
    filters: Object
});

// Config
const columns = [
    { key: 'code', label: 'Code', class: 'w-24 font-mono text-xs' },
    { key: 'name', label: 'Client Name' },
    { key: 'contact_person', label: 'Contact', class: 'hidden md:table-cell' },
    { key: 'projects_count', label: 'Projects', class: 'text-center' },
    { key: 'status', label: 'Status' },
    { key: 'contract_end', label: 'Contract End', class: 'hidden lg:table-cell' },
];

// State
const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    code: '',
    contact_person: '',
    email: '',
    contract_start: '',
    contract_end: '',
    portal_access: false
});

// Actions
const handleSearch = (query) => {
    router.get(route('projects.clients.index'), { search: query }, { preserveState: true, replace: true });
};

const handlePageChange = (page) => {
    router.get(route('projects.clients.index'), { page: page, search: props.filters.search }, { preserveState: true, replace: true });
};

const openModal = (client = null) => {
    isEditing.value = !!client;
    if (client) {
        form.id = client.id;
        form.name = client.name;
        form.code = client.code;
        form.contact_person = client.contact_person;
        form.email = client.email;
        form.contract_start = client.contract_start ? client.contract_start.split('T')[0] : '';
        form.contract_end = client.contract_end ? client.contract_end.split('T')[0] : '';
        form.portal_access = !!client.portal_access;
    } else {
        form.reset();
        form.id = null;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('projects.clients.update', { client: form.id }), {
             onSuccess: () => closeModal()
        });
    } else {
         form.post(route('projects.clients.store'), {
             onSuccess: () => closeModal()
         });
    }
};

const confirmDelete = (client) => {
    if (confirm(`Are you sure you want to delete ${client.name}?`)) {
        router.delete(route('projects.clients.destroy', { client: client.id }));
    }
};
</script>
