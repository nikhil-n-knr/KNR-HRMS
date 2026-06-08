<template>
    <Head title="Salary Holds" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Salary Holds & Blocks
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Active Holds</h3>
                    <button @click="showAddModal = true" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        Block Salary
                    </button>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hold Until</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="hold in holds.data" :key="hold.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ hold.employee?.user?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ hold.type }}
                                    <span v-if="hold.type === 'Partial'">({{ hold.amount }})</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ hold.reason }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ hold.hold_until || 'Indefinite' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="hold.status === 'Active' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                        {{ hold.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <button v-if="hold.status === 'Active'" @click="releaseHold(hold)" class="text-green-600 hover:text-green-900 font-bold">Release</button>
                                </td>
                            </tr>
                            <tr v-if="holds.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">No active salary holds.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Add Modal -->
                <Modal :show="showAddModal" @close="showAddModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Block / Hold Salary</h2>
                        <form @submit.prevent="submitHold">
                            <div class="space-y-4">
                                <div>
                                    <InputLabel value="Employee" />
                                    <select v-model="form.employee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Type" />
                                    <select v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="Full">Full Salary Hold</option>
                                        <!-- <option value="Partial">Partial Deduction</option> Not fully supported in logic yet -->
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Reason" />
                                    <TextInput v-model="form.reason" type="text" class="mt-1 block w-full" required placeholder="e.g. Absconding, Asset Recovery" />
                                </div>
                                <div>
                                    <InputLabel value="Hold Until (Optional)" />
                                    <TextInput v-model="form.hold_until" type="date" class="mt-1 block w-full" />
                                    <p class="text-xs text-gray-500 mt-1">Leave blank for indefinite hold.</p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="showAddModal = false">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="form.processing">Block Salary</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    holds: Object,
    employees: Array
});

const showAddModal = ref(false);
const form = useForm({
    employee_id: '',
    type: 'Full',
    reason: '',
    hold_until: ''
});

const submitHold = () => {
    form.post(route('hr.payroll.holds.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const releaseHold = (hold) => {
    if(confirm('Are you sure you want to release this salary hold? Logic: It will be paid in next payroll.')) {
        router.put(route('hr.payroll.holds.update', hold.id), { action: 'release' });
    }
};
</script>
