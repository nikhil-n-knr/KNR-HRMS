<template>
    <Modal :show="show" :title="title" maxWidth="md" @close="$emit('close')">
        <div class="space-y-4">
            <p class="text-sm text-gray-600">{{ message }}</p>
        </div>

        <template #footer>
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                {{ cancelText }}
            </button>
            <button 
                @click="$emit('confirm')" 
                class="px-4 py-2 text-white rounded-lg text-sm font-medium shadow-md transition-all flex items-center gap-2"
                :class="type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'"
            >
                {{ confirmText }}
            </button>
        </template>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';

defineProps({
    show: Boolean,
    title: { type: String, default: 'Confirm Action' },
    message: { type: String, required: true },
    confirmText: { type: String, default: 'Confirm' },
    cancelText: { type: String, default: 'Cancel' },
    type: { type: String, default: 'primary' } // 'primary' or 'danger'
});

defineEmits(['close', 'confirm']);
</script>
