<template>
    <Modal :show="show" title="Update System Role" @close="$emit('close')">
        <div class="space-y-4">
             <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
                <p>Assigning a role determines what <strong>{{ employee.first_name }}</strong> can access in the system.</p>
            </div>

            <div>
                <BaseSelect
                    v-model="selectedRole"
                    label="Select Role"
                >
                    <option value="" disabled>Choose a role...</option>
                    <option v-for="role in roles" :key="role.id" :value="role.name">
                        {{ role.name }}
                    </option>
                </BaseSelect>
            </div>
        </div>

        <template #footer>
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Cancel
            </button>
            <button 
                @click="submit" 
                :disabled="loading || !selectedRole"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-indigo-700 transition-all disabled:opacity-70 flex items-center gap-2"
            >
                <div v-if="loading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Update Role
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import Modal from '@/Components/Modal.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps({
    show: Boolean,
    employee: Object
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();
const loading = ref(false);
const roles = ref([]);
const selectedRole = ref('');

// Fetch roles when modal opens or component mounts
const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/admin/roles');
        roles.value = response.data.data;
    } catch (error) {
        console.error("Failed to load roles", error);
    }
};

watch(() => props.show, (newVal) => {
    if(newVal) {
        // Pre-select current role if exists
        // Note: Backend 'user' object has 'role' property (array or single object depending on user resource)
        // Adjusting based on standard resource format
        const currentRole = props.employee.user?.roles?.[0]?.name || props.employee.user?.role?.name;
        selectedRole.value = currentRole || 'Employee';
        if(roles.value.length === 0) fetchRoles();
    }
});

const submit = async () => {
    loading.value = true;
    try {
        await axios.put(`/api/admin/employees/${props.employee.id}/update-role`, {
            role_name: selectedRole.value
        });
        toast.success(`Role updated to ${selectedRole.value}`);
        emit('saved');
        emit('close');
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to update role");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
   fetchRoles(); 
});
</script>
