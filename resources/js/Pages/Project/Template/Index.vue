<template>
    <ProjectLayout :project="project">
        <div class="h-full flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Task Templates</h2>
                    <p class="text-sm text-gray-500">Manage reusable task structures</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium shadow-sm transition-colors flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Template
                </button>
            </div>

            <div class="flex-1 bg-white rounded-xl shadow-xl shadow-indigo-500/5 border border-gray-100 overflow-hidden overflow-x-auto no-scrollbar scroll-smooth">
                <table class="min-w-full divide-y divide-gray-100 table-fixed md:table-auto">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Created By</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Checklist Items</th>
                             <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="template in templates" :key="template.id" class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ template.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium tracking-tight">
                                {{ template.creator?.name || 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 bg-gray-50 border border-gray-100 rounded-lg text-sm font-black uppercase tracking-widest text-gray-500">{{ template.checklist_items?.length || 0 }} items</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black uppercase tracking-widest">
                                <button class="text-indigo-600 hover:text-indigo-900 mr-4 text-sm">Edit</button>
                                <button class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                            </td>
                        </tr>
                         <tr v-if="templates.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                                No templates found. Create one to get started.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Create Modal (Simple Placeholder) -->
             <Modal :show="showCreate" @close="showCreate = false">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create Template</h3>
                    <p class="text-gray-500 text-sm mb-4">Use the "Save as Template" feature in Task Details to create templates easily.</p>
                     <div class="flex justify-end">
                        <SecondaryButton @click="showCreate = false">Close</SecondaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </ProjectLayout>
</template>

<script setup>
import { ref } from 'vue';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    project: Object,
    templates: Array
});

const showCreate = ref(false);

const openCreateModal = () => {
    showCreate.value = true;
};
</script>
