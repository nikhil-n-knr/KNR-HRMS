<template>
    <Head title="Exit Clearance Management" />

    <MainLayout>
        <div class="px-6 py-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exit Clearance Management</h2>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4">
                    <select v-model="filters.module" @change="applyFilters" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">All Departments</option>
                        <option value="it">IT Assets</option>
                        <option value="finance">Finance / Loans</option>
                        <option value="admin">Admin / Facilities</option>
                    </select>

                    <select v-model="filters.status" @change="applyFilters" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Cleared">Cleared</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dues</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="clearance in clearances.data" :key="clearance.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ clearance.employee.first_name }} {{ clearance.employee.last_name }}</div>
                                        <div class="text-sm text-gray-500">{{ clearance.employee.employee_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ clearance.employee.department?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ clearance.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': clearance.status === 'Pending',
                                                'bg-green-100 text-green-800': clearance.status === 'Cleared',
                                                'bg-red-100 text-red-800': clearance.status === 'Rejected'
                                            }">
                                            {{ clearance.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ clearance.due_amount > 0 ? '₹' + clearance.due_amount : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openModal(clearance)" class="text-indigo-600 hover:text-indigo-900">Update</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Paginator -->
                         <div class="mt-4">
                            <!-- Helper Pagination Component or simple links -->
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Update Clearance: {{ selectedClearance?.type }}
                </h2>

                <div class="mt-4">
                    <InputLabel for="status" value="Status" />
                    <select id="status" v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="Pending">Pending</option>
                        <option value="Cleared">Cleared</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Hold">Hold</option>
                    </select>
                </div>

                <div class="mt-4">
                    <InputLabel for="due_amount" value="Due / Recovery Amount" />
                    <TextInput id="due_amount" type="number" v-model="form.due_amount" class="mt-1 block w-full" />
                </div>

                <div class="mt-4">
                    <InputLabel for="remarks" value="Remarks" />
                    <textarea id="remarks" v-model="form.remarks" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="3"></textarea>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submit" :disabled="form.processing">
                        Update
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </MainLayout>
</template>

<script setup>
import MainLayout from '../../../Layouts/MainLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import Modal from '../../../Components/Modal.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import TextInput from '../../../Components/TextInput.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';

const props = defineProps({
    clearances: Object,
    filters: Object
});

const filters = reactive({
    module: props.filters.module || '',
    status: props.filters.status || ''
});

const applyFilters = () => {
    router.get(route('admin.clearances.index'), filters, { preserveState: true });
};

const showModal = ref(false);
const selectedClearance = ref(null);
const form = useForm({
    status: 'Pending',
    due_amount: 0,
    remarks: ''
});

const openModal = (clearance) => {
    selectedClearance.value = clearance;
    form.status = clearance.status;
    form.due_amount = clearance.due_amount;
    form.remarks = clearance.remarks;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    form.put(route('admin.clearances.update', selectedClearance.value.id), {
        onSuccess: () => closeModal()
    });
};
</script>
