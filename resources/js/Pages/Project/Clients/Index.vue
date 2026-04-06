<template>
    <Head title="Client Hub" />
    <MainLayout>
        <div class="h-full bg-gray-50 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Client Hub</h1>
                        <p class="mt-2 text-sm text-gray-600">Manage client relationships, accounts, and project access.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="client in clients" :key="client.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-lg">
                                {{ client.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ client.name }}</h3>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ client.code }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Contact</span>
                                <span class="font-medium text-gray-900">{{ client.contact_person }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Users</span>
                                <span class="font-medium text-gray-900">{{ client.client_users?.length || 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Projects</span>
                                <span class="font-medium text-gray-900">{{ client.projects?.length || 0 }}</span>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-2">
                            <button @click="router.visit(route('projects.clients.show', client.id))" class="flex-1 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-colors">Manage</button>
                            <button @click="router.visit(route('projects.clients.edit', client.id))" class="px-4 py-2 border border-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors">Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    clients: Array,
    all_projects: Array
});
</script>
