<template>
    <div class="space-y-4">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total Leads</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ leads?.length || 0 }}</p>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <p class="text-sm font-medium text-gray-600">New</p>
                <p class="mt-2 text-3xl font-bold text-blue-600">
                    {{ leads?.filter(l => l.status === 'new').length || 0 }}
                </p>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Qualified</p>
                <p class="mt-2 text-3xl font-bold text-green-600">
                    {{ leads?.filter(l => l.status === 'qualified').length || 0 }}
                </p>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Converted</p>
                <p class="mt-2 text-3xl font-bold text-purple-600">
                    {{ leads?.filter(l => l.status === 'converted').length || 0 }}
                </p>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">All Leads</h3>
            </div>
            
            <div v-if="!leads || leads.length === 0" class="p-12 text-center">
                <i class="mb-4 text-5xl text-gray-300 fas fa-user-plus"></i>
                <p class="text-gray-500">No leads yet</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ lead.full_name }}</div>
                            <div class="text-sm text-gray-500">{{ lead.title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.company }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ lead.email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ lead.source }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span 
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-blue-100 text-blue-800': lead.status === 'new',
                                    'bg-yellow-100 text-yellow-800': lead.status === 'contacted',
                                    'bg-green-100 text-green-800': lead.status === 'qualified',
                                    'bg-purple-100 text-purple-800': lead.status === 'converted',
                                    'bg-gray-100 text-gray-800': lead.status === 'unqualified',
                                }"
                            >
                                {{ lead.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.score }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
defineProps({
    leads: Array,
});
</script>
