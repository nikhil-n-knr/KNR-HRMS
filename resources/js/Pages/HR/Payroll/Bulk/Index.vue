<template>
    <Head title="Bulk Salary Update" />
    <MainLayout>
        <PayrollTabs class="-mt-6 -mx-4 sm:-mx-6 lg:-mx-8 mb-6" />
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-xl font-semibold text-gray-900">Appraisal & Corrections Sheet</h1>
                    <p class="mt-2 text-sm text-gray-700">Update salary structures and CTCs for multiple employees at once. Use "Mass Edit" for hikes.</p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex items-center space-x-3">
                    <a :href="route('hr.payroll.bulk.sample')" 
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Sample Sheet
                    </a>
                     <button @click="massApply" :disabled="selectedEmployees.length === 0" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                        Mass Edit Selected
                    </button>
                    <button @click="save" :disabled="form.processing || !hasChanges" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto disabled:opacity-50">
                        Save Changes
                    </button>
                </div>
            </div>

            <!-- Mass Edit Modal -->
            <Modal :show="showMassEdit" @close="showMassEdit = false">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Mass Edit {{ selectedEmployees.length }} Employees</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Set Structure</label>
                            <select v-model="massForm.structure_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Do Not Change</option>
                                <option v-for="s in structures" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Percentage Hike (%)</label>
                             <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" v-model="massForm.hike_percentage" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Leaves empty to keep current CTC.</p>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Effective Date</label>
                            <input type="date" v-model="massForm.effective_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showMassEdit = false">Cancel</SecondaryButton>
                        <PrimaryButton @click="applyMassEdit">Apply to Grid</PrimaryButton>
                    </div>
                </div>
            </Modal>
            
            <!-- Career DNA Modal -->
            <CareerDnaModal :show="!!viewingEmployeeId" :employee-id="viewingEmployeeId" @close="viewingEmployeeId = null" />

            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                            <input type="checkbox" :checked="isAllSelected" @change="toggleAll" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                                        </th>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Current Structure</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">New Structure</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Current CTC</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">New CTC</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Effective From</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="emp in form.employees" :key="emp.id" :class="emp.selected ? 'bg-indigo-50' : ''">
                                        <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                            <div v-if="emp.selected" class="absolute inset-y-0 left-0 w-0.5 bg-indigo-600"></div>
                                            <input type="checkbox" v-model="emp.selected" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                                        </td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            <button @click="viewingEmployeeId = emp.id" class="hover:text-indigo-600 flex items-center gap-1 group">
                                                {{ emp.name }}
                                                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </button>
                                            <div class="text-xs text-gray-500 font-normal">{{ emp.department }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ emp.current_structure_name || '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <select v-model="emp.new_structure_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                                <option v-for="s in structures" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            ₹{{ emp.current_ctc.toLocaleString() }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                             <input type="number" v-model="emp.new_ctc" class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                        </td>
                                         <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                             <input type="date" v-model="emp.effective_date" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PayrollTabs from '@/Components/PayrollTabs.vue';
import CareerDnaModal from '@/Components/Analytics/CareerDnaModal.vue';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    employees: Array,
    structures: Array,
    departments: Array
});

const form = useForm({
    employees: props.employees.map(e => ({ ...e })) // Clone
});

const showMassEdit = ref(false);
const viewingEmployeeId = ref(null); // For Career DNA
const massForm = ref({
    structure_id: '',
    hike_percentage: '',
    effective_date: dayjs().format('YYYY-MM-d')
});

const selectedEmployees = computed(() => form.employees.filter(e => e.selected));
const isAllSelected = computed(() => form.employees.length > 0 && selectedEmployees.value.length === form.employees.length);

const toggleAll = () => {
    const newVal = !isAllSelected.value;
    form.employees.forEach(e => e.selected = newVal);
};

const hasChanges = computed(() => {
    // Check if any row differs from original prop
    return form.employees.some((e, i) => {
        const original = props.employees[i];
        return e.new_structure_id !== original.new_structure_id || 
               e.new_ctc !== original.new_ctc ||
               e.effective_date !== original.effective_date; // Comparing generic dates might need formatting
    });
});

const massApply = () => {
    showMassEdit.value = true;
};

const applyMassEdit = () => {
    form.employees.forEach(e => {
        if (e.selected) {
            if (massForm.value.structure_id) e.new_structure_id = massForm.value.structure_id;
            if (massForm.value.effective_date) e.effective_date = massForm.value.effective_date;
            
            if (massForm.value.hike_percentage && e.current_ctc > 0) {
                 const hike = parseFloat(massForm.value.hike_percentage);
                 e.new_ctc = Math.round(e.current_ctc * (1 + hike / 100));
            }
        }
    });
    showMassEdit.value = false;
};

const save = () => {
    // Only send modified rows
    const modified = form.employees.filter((e, i) => {
        const original = props.employees[i];
        // Allow update if Structure, CTC, OR Effective Date changes. 
        // Also simpler loose equality for ID/CTC is fine, but effective_date needs check.
        return e.new_structure_id != original.new_structure_id || 
               e.new_ctc != original.new_ctc ||
               e.effective_date !== original.effective_date; 
    });

    if (modified.length === 0) return;

    form.transform((data) => ({
        updates: modified.map(e => ({
            id: e.id,
            new_structure_id: e.new_structure_id,
            new_ctc: e.new_ctc,
            effective_date: e.effective_date
        }))
    })).post(route('hr.payroll.bulk.store'), {
        onSuccess: () => {
            // Reset "modified" state logic effectively by reloading page props (Inertia default)
        }
    });
};
</script>
