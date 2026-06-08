<script setup>
import { ref, onMounted } from 'vue';
import { PlusIcon, TrashIcon, PencilIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const rotations = ref([]);
const loading = ref(false);
const showModal = ref(false);
const showAssignModal = ref(false);

const form = ref({
    id: null,
    name: '',
    frequency: 'weekly',
    pattern: ['General'] // Simplified default
});

const assignForm = ref({
    rotation_id: null,
    employee_ids: [],
    start_date: new Date().toISOString().split('T')[0]
});

// Mock shifts for now, ideally passed as props
const availableShifts = ['General', 'Morning', 'Evening', 'Night'];
const employees = ref([]); // Need to fetch employees or pass as prop

const fetchRotations = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('admin.attendance.rotations.index'));
        rotations.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const openCreate = () => {
    form.value = { id: null, name: '', frequency: 'weekly', pattern: ['General'] };
    showModal.value = true;
};

const editRotation = (rot) => {
    form.value = { ...rot, pattern: JSON.parse(JSON.stringify(rot.pattern)) }; // Deep copy pattern
    showModal.value = true;
};

const addShiftToPattern = () => {
    form.value.pattern.push('General');
};

const removeShiftFromPattern = (index) => {
    form.value.pattern.splice(index, 1);
};

const saveRotation = async () => {
    try {
        if (form.value.id) {
            await axios.put(route('admin.attendance.rotations.update', form.value.id), form.value);
            toast.success('Rotation updated');
        } else {
            await axios.post(route('admin.attendance.rotations.store'), form.value);
            toast.success('Rotation created');
        }
        showModal.value = false;
        fetchRotations();
    } catch (e) {
        toast.error('Failed to save rotation');
    }
};

const deleteRotation = async (id) => {
    if(!confirm('Are you sure?')) return;
    try {
        await axios.delete(route('admin.attendance.rotations.destroy', id));
        toast.success('Rotation deleted');
        fetchRotations();
    } catch (e) {
        toast.error('Failed to delete');
    }
};

const openAssign = (rot) => {
    assignForm.value.rotation_id = rot.id;
    assignForm.value.start_date = new Date().toISOString().split('T')[0];
    assignForm.value.employee_ids = [];
    showAssignModal.value = true;
    // Ideally fetch employees here if not already loaded
    fetchEmployees(); 
};

const fetchEmployees = async () => {
    // Quick fetch for employees picker
    // Assuming an endpoint exists or we use a heavy prop. 
    // For now simple alert if empty
    if(employees.value.length === 0) {
        // Mock or real fetch
        const res = await axios.get('/admin/employees/export'); // Abuse export or use dedicated search
        // Simplified:
        // employees.value = [{id: 1, name: 'John'}, {id: 2, name: 'Jane'}];
    }
};

const submitAssign = async () => {
    try {
        await axios.post(route('admin.attendance.rotations.assign'), assignForm.value);
        toast.success('Employees assigned successfully');
        showAssignModal.value = false;
    } catch (e) {
        toast.error('Assignment failed');
    }
};

onMounted(() => {
    fetchRotations();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Shift Rotations</h3>
                <p class="text-sm text-gray-500">Define automatic shift patterns.</p>
            </div>
            <button @click="openCreate" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm">
                <PlusIcon class="w-5 h-5" />
                <span>New Pattern</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="rot in rotations" :key="rot.id" class="border border-gray-200 rounded-xl p-4 hover:border-indigo-200 transition bg-gray-50/50">
                <div class="flex justify-between items-start mb-3">
                    <h4 class="font-bold text-gray-800">{{ rot.name }}</h4>
                    <span class="text-xs px-2 py-1 bg-white border rounded text-gray-500 capitalize">{{ rot.frequency }}</span>
                </div>
                
                <div class="flex gap-1 overflow-x-auto pb-2 mb-4">
                    <div v-for="(shift, idx) in rot.pattern" :key="idx" 
                        class="flex-shrink-0 px-2 py-1 text-xs font-mono bg-indigo-50 text-indigo-700 rounded border border-indigo-100 whitespace-nowrap">
                        {{ idx + 1 }}. {{ shift }}
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <button @click="openAssign(rot)" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                        <UserGroupIcon class="w-4 h-4" /> Assign
                    </button>
                    <div class="flex gap-2">
                        <button @click="editRotation(rot)" class="text-gray-400 hover:text-indigo-600"><PencilIcon class="w-4 h-4" /></button>
                        <button @click="deleteRotation(rot.id)" class="text-gray-400 hover:text-red-600"><TrashIcon class="w-4 h-4" /></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4">{{ form.id ? 'Edit' : 'Create' }} Rotation</h3>
                <div class="space-y-4">
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Name</label>
                         <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Frequency (Change shift every...)</label>
                         <select v-model="form.frequency" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                             <option value="weekly">Week</option>
                             <option value="bi-weekly">2 Weeks</option>
                             <option value="monthly">Month</option>
                             <option value="daily">Day</option>
                         </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pattern Sequence</label>
                        <div class="space-y-2">
                            <div v-for="(shift, idx) in form.pattern" :key="idx" class="flex gap-2">
                                <span class="text-sm font-mono text-gray-500 w-6 pt-2">{{ idx + 1 }}.</span>
                                <input v-model="form.pattern[idx]" type="text" placeholder="Shift Name (e.g. Morning)" class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                <button @click="removeShiftFromPattern(idx)" class="text-red-500 hover:text-red-700"><TrashIcon class="w-5 h-5" /></button>
                            </div>
                        </div>
                        <button @click="addShiftToPattern" class="mt-2 text-sm text-indigo-600 font-medium">+ Add Step</button>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 bg-gray-100 rounded-lg">Cancel</button>
                    <button @click="saveRotation" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
                </div>
            </div>
        </Modal>

        <!-- Assign Modal (Simple ID input for now to save time) -->
        <Modal :show="showAssignModal" @close="showAssignModal = false">
             <div class="p-6">
                <h3 class="text-lg font-bold mb-4">Assign Rotation</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employee IDs (Comma separated)</label>
                        <input v-model="form.temp_ids" type="text" placeholder="1, 2, 5" class="w-full border-gray-300 rounded-md shadow-sm">
                        <p class="text-xs text-gray-500 mt-1">Temporary: Enter IDs manually for Beta.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input v-model="assignForm.start_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showAssignModal = false" class="px-4 py-2 bg-gray-100 rounded-lg">Cancel</button>
                    <button @click="() => { assignForm.employee_ids = form.temp_ids?.split(',').map(Number); submitAssign(); }" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">Assign</button>
                </div>
             </div>
        </Modal>
    </div>
</template>
