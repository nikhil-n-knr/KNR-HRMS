<template>
    <MainLayout title="Module Manager">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Module Manager</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Activate and manage premium modules for your organization
                    </p>
                </div>
            </div>

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="module in modules"
                    :key="module.id"
                    class="relative overflow-hidden transition-shadow bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md"
                >
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        <span
                            :class="getStatusBadgeClass(module)"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        >
                            {{ getStatusText(module) }}
                        </span>
                    </div>

                    <!-- Module Icon & Info -->
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 rounded-lg"
                                :class="module.is_activated ? 'bg-blue-100' : 'bg-gray-100'"
                            >
                                <i
                                    :class="[
                                        module.icon || 'fas fa-cube',
                                        'text-xl',
                                        module.is_activated ? 'text-blue-600' : 'text-gray-400'
                                    ]"
                                ></i>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ module.display_name }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                            {{ module.description }}
                        </p>

                        <!-- Module Stats (if activated) -->
                        <div
                            v-if="module.is_activated"
                            class="flex items-center gap-4 pt-4 mt-4 border-t border-gray-200"
                        >
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="mr-1 fas fa-users"></i>
                                <span>{{ module.current_users || 0 }}</span>
                                <span v-if="module.user_limit" class="text-gray-400">
                                    / {{ module.user_limit }}
                                </span>
                                <span class="ml-1">users</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 space-y-2">
                            <button
                                v-if="!module.is_activated"
                                @click="openActivationModal(module)"
                                class="w-full px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <i class="mr-2 fas fa-bolt"></i>
                                Activate Module
                            </button>

                            <button
                                v-if="module.is_activated"
                                @click="manageUsers(module)"
                                class="w-full px-4 py-2 text-sm font-medium text-blue-700 transition-colors bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <i class="mr-2 fas fa-user-cog"></i>
                                Manage User Access
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activation Modal -->
            <ActivationModal
                v-if="showActivationModal"
                :module="selectedModule"
                @close="showActivationModal = false"
                @activated="handleModuleActivated"
            />
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ActivationModal from './ActivationModal.vue';

const props = defineProps({
    modules: {
        type: Array,
        required: true,
    },
});

const showActivationModal = ref(false);
const selectedModule = ref(null);

const openActivationModal = (module) => {
    selectedModule.value = module;
    showActivationModal.value = true;
};

const manageUsers = (module) => {
    router.visit(route('admin.modules.users', { module: module.id }));
};

const handleModuleActivated = () => {
    showActivationModal.value = false;
    // Refresh the page to show updated status
    router.reload();
};

const getStatusText = (module) => {
    if (!module.is_activated) {
        return 'Inactive';
    }
    
    const orgModule = module.organizations?.[0];
    if (!orgModule) return 'Active';
    
    switch (orgModule.subscription_status) {
        case 'active':
            return orgModule.is_trial ? 'Trial' : 'Active';
        case 'expired':
            return 'Expired';
        case 'cancelled':
            return 'Cancelled';
        case 'pending':
            return 'Pending';
        default:
            return 'Active';
    }
};

const getStatusBadgeClass = (module) => {
    if (!module.is_activated) {
        return 'bg-gray-100 text-gray-800';
    }
    
    const orgModule = module.organizations?.[0];
    if (!orgModule) return 'bg-green-100 text-green-800';
    
    switch (orgModule.subscription_status) {
        case 'active':
            return orgModule.is_trial
                ? 'bg-yellow-100 text-yellow-800'
                : 'bg-green-100 text-green-800';
        case 'expired':
            return 'bg-red-100 text-red-800';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800';
        case 'pending':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-green-100 text-green-800';
    }
};
</script>
