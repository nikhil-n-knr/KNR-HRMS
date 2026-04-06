<template>
    <Modal :show="show" title="Create Login Access" @close="$emit('close')">
        <form @submit.prevent="createLogin" class="space-y-4">
            <p class="text-sm text-gray-600">
                Create system access for <span class="font-semibold">{{ employee?.first_name }} {{ employee?.last_name }}</span>.
            </p>
            
            <BaseInput 
                v-model="form.email"
                type="email"
                label="Email (Username)"
                required
                :error="errors.email"
            />
            
            <BaseInput 
                v-model="form.password"
                type="text"
                label="Temporary Password"
                required
                :error="errors.password"
            />
            
            <BaseSelect
                v-model="form.role"
                label="Role"
                required
                :error="errors.role"
            >
                <option value="Employee">Employee</option>
                <option value="Manager">Manager</option>
                <option value="Admin">Admin</option>
            </BaseSelect>
        </form>

        <template #footer>
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Cancel
            </button>
            <button 
                @click="createLogin" 
                :disabled="loading"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-emerald-700 transition-all disabled:opacity-70 flex items-center gap-2"
            >
                <div v-if="loading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Create Access
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps({
    show: Boolean,
    employee: Object
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();
const loading = ref(false);
const errors = ref({});

const form = ref({
    email: '',
    password: '',
    role: 'Employee'
});

watch(() => props.show, (val) => {
    if (val && props.employee) {
        form.value.email = props.employee.email || '';
        form.value.password = 'Welcome@123';
        form.value.role = 'Employee';
        errors.value = {};
    }
});

const createLogin = async () => {
    loading.value = true;
    errors.value = {};
    try {
        await axios.post(`/api/admin/employees/${props.employee.id}/create-login`, form.value);
        toast.success(`Login created for ${props.employee.first_name}`);
        emit('saved');
        emit('close');
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
             toast.error(error.response?.data?.message || "Failed to create login");
        }
    } finally {
        loading.value = false;
    }
};
</script>
