<template>
    <Modal :show="show" title="Reset Password" @close="$emit('close')">
        <form @submit.prevent="submit" class="space-y-4">
             <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                <p>This will override the current password for <strong>{{ employee.first_name }} {{ employee.last_name }}</strong>. </p>
            </div>

            <BaseInput 
                v-model="form.password"
                type="password"
                label="New Password"
                required
                minlength="8"
            />

            <BaseInput 
                v-model="form.password_confirmation"
                type="password"
                label="Confirm Password"
                required
                minlength="8"
            />
        </form>

        <template #footer>
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Cancel
            </button>
            <button 
                @click="submit" 
                :disabled="loading"
                class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg transition-all disabled:opacity-70 flex items-center gap-2"
            >
                <div v-if="loading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Reset Password
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';

const props = defineProps({
    show: Boolean,
    employee: Object
});

const emit = defineEmits(['close']);
const toast = useToastStore();
const loading = ref(false);
const form = ref({
    password: '',
    password_confirmation: ''
});

const submit = async () => {
    if (form.value.password !== form.value.password_confirmation) {
        toast.error("Passwords do not match");
        return;
    }

    loading.value = true;
    try {
        await axios.post(`/api/admin/employees/${props.employee.id}/reset-password`, form.value);
        toast.success("Password reset successfully");
        form.value.password = '';
        form.value.password_confirmation = '';
        emit('close');
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to reset password");
    } finally {
        loading.value = false;
    }
};
</script>
