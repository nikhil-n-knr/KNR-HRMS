<template>
    <MainLayout :title="`${module.display_name} - User Access`">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        @click="router.visit(route('admin.modules.index'))"
                        class="p-2 text-gray-400 transition-colors rounded-md hover:text-gray-600 hover:bg-gray-100"
                    >
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ module.display_name }} - User Access
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage which users can access this module
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Users</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900">
                                {{ usersWithAccess.length + usersWithoutAccess.length }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-lg">
                            <i class="text-gray-600 fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">With Access</p>
                            <p class="mt-1 text-2xl font-semibold text-green-600">
                                {{ usersWithAccess.length }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                            <i class="text-green-600 fas fa-user-check"></i>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Without Access</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900">
                                {{ usersWithoutAccess.length }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-lg">
                            <i class="text-gray-600 fas fa-user-slash"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Lists -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Users WITH Access -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <i class="mr-2 text-green-600 fas fa-user-check"></i>
                            Users with Access
                            <span class="ml-2 text-sm font-normal text-gray-500">
                                ({{ usersWithAccess.length }})
                            </span>
                        </h3>
                    </div>
                    <div class="p-4">
                        <div v-if="usersWithAccess.length === 0" class="py-8 text-center">
                            <i class="mb-2 text-4xl text-gray-300 fas fa-user-slash"></i>
                            <p class="text-sm text-gray-500">No users have access yet</p>
                        </div>
                        <div v-else class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="user in usersWithAccess"
                                :key="user.id"
                                class="flex items-center justify-between p-3 transition-colors border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 font-medium text-white bg-blue-500 rounded-full">
                                        {{ getInitials(user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                        <span
                                            v-if="getUserModuleRole(user)"
                                            class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ getUserModuleRole(user) }}
                                        </span>
                                    </div>
                                </div>
                                <button
                                    @click="revokeAccess(user)"
                                    class="px-3 py-1 text-sm font-medium text-red-700 transition-colors bg-red-50 border border-red-200 rounded-md hover:bg-red-100"
                                >
                                    <i class="mr-1 fas fa-times"></i>
                                    Revoke
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users WITHOUT Access -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <i class="mr-2 text-gray-400 fas fa-user-slash"></i>
                            Users without Access
                            <span class="ml-2 text-sm font-normal text-gray-500">
                                ({{ usersWithoutAccess.length }})
                            </span>
                        </h3>
                    </div>
                    <div class="p-4">
                        <div v-if="usersWithoutAccess.length === 0" class="py-8 text-center">
                            <i class="mb-2 text-4xl text-green-300 fas fa-check-circle"></i>
                            <p class="text-sm text-gray-500">All users have access!</p>
                        </div>
                        <div v-else class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="user in usersWithoutAccess"
                                :key="user.id"
                                class="flex items-center justify-between p-3 transition-colors border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 font-medium text-white bg-gray-400 rounded-full">
                                        {{ getInitials(user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="openGrantModal(user)"
                                    class="px-3 py-1 text-sm font-medium text-green-700 transition-colors bg-green-50 border border-green-200 rounded-md hover:bg-green-100"
                                >
                                    <i class="mr-1 fas fa-plus"></i>
                                    Grant
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grant Access Modal -->
            <GrantAccessModal
                v-if="showGrantModal"
                :user="selectedUser"
                :module="module"
                @close="showGrantModal = false"
                @granted="handleAccessGranted"
            />
        </div>
    </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import GrantAccessModal from './GrantAccessModal.vue';
import axios from 'axios';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    usersWithAccess: {
        type: Array,
        required: true,
    },
    usersWithoutAccess: {
        type: Array,
        required: true,
    },
});

const showGrantModal = ref(false);
const selectedUser = ref(null);

const getInitials = (name) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const getUserModuleRole = (user) => {
    const moduleAccess = user.modules?.find((m) => m.id === props.module.id);
    return moduleAccess?.pivot?.module_role || null;
};

const openGrantModal = (user) => {
    selectedUser.value = user;
    showGrantModal.value = true;
};

const revokeAccess = async (user) => {
    if (!confirm(`Are you sure you want to revoke ${user.name}'s access to ${props.module.display_name}?`)) {
        return;
    }

    try {
        await axios.delete(
            route('admin.modules.revoke-access', {
                module: props.module.id,
                user: user.id,
            })
        );

        router.reload();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to revoke access');
    }
};

const handleAccessGranted = () => {
    showGrantModal.value = false;
    router.reload();
};
</script>
