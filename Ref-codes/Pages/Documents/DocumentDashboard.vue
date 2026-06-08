<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Documents Hub</h1>
            <p class="text-sm text-gray-500 mt-1">Centralized registry of all employee documents.</p>
        </div>
        
        <!-- Filters Area -->
        <div class="flex gap-3">
             <select 
                v-model="categoryFilter" 
                @change="fetchDocuments(1)"
                class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none"
             >
                <option value="">All Categories</option>
                <option value="Official">Official</option>
                <option value="Financial">Financial</option>
                <option value="Identity">Identity</option>
                <option value="Education">Education</option>
                <option value="Legal">Legal</option>
                <option value="Other">Other</option>
             </select>
        </div>
    </div>

    <!-- Data Table -->
    <BaseDataTable
        :columns="columns"
        :data="documents"
        :meta="meta"
        :loading="loading"
        :per-page="perPage"
        search-placeholder="Search document or employee..."
        :row-clickable="false"
        @search="handleSearch"
        @page-change="fetchDocuments"
        @limit-change="handleLimitChange"
    >
        <!-- Document Name Column -->
        <template #cell-document="{ item }">
             <div class="flex items-center gap-3">
                 <div class="h-10 w-10 flex-shrink-0 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                    <svg v-if="item.file_type.includes('pdf')" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                 </div>
                 <div>
                     <p class="text-sm font-medium text-gray-900 truncate max-w-[200px]" :title="item.title">{{ item.title }}</p>
                     <div class="flex items-center gap-2">
                         <span class="text-xs text-gray-500">{{ item.document_type }}</span>
                         <span v-if="item.is_system_generated" class="bg-blue-100 text-blue-700 text-sm px-1.5 py-0.5 rounded font-bold">SYSTEM</span>
                     </div>
                 </div>
             </div>
        </template>

        <!-- Employee Column -->
        <template #cell-employee="{ item }">
             <div v-if="item.employee" class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-1 rounded transition" @click="$router.push({ name: 'employees.show', params: { id: item.employee.id } })">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center text-emerald-700 text-xs font-bold">
                        {{ item.employee.first_name[0] }}{{ item.employee.last_name[0] }}
                  </div>
                  <div>
                      <p class="text-sm font-medium text-gray-900">{{ item.employee.first_name }} {{ item.employee.last_name }}</p>
                      <p class="text-xs text-gray-500">{{ item.employee.employee_code }}</p>
                  </div>
             </div>
             <span v-else class="text-xs text-gray-400 italic">Unknown</span>
        </template>

        <!-- Category Badge -->
        <template #cell-category="{ item }">
            <span 
                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                :class="{
                    'bg-purple-50 text-purple-700 border-purple-200': item.category === 'Identity',
                    'bg-green-50 text-green-700 border-green-200': item.category === 'Financial',
                    'bg-blue-50 text-blue-700 border-blue-200': item.category === 'Official',
                    'bg-gray-50 text-gray-700 border-gray-200': !['Identity','Financial','Official'].includes(item.category)
                }"
            >
                {{ item.category }}
            </span>
        </template>

        <!-- Source -->
        <template #cell-source="{ item }">
             <p class="text-sm text-gray-600">{{ item.source_module }}</p>
             <p class="text-xs text-gray-400">{{ new Date(item.created_at).toLocaleDateString() }}</p>
        </template>

        <!-- Actions -->
        <template #rowActions="{ item }">
             <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <!-- Download -->
                <a 
                    :href="`/api/documents/${item.id}/stream`" 
                    target="_blank"
                    class="p-1.5 bg-white text-indigo-600 rounded-lg border border-indigo-100 shadow-sm hover:shadow hover:bg-indigo-50 transition-all"
                    title="Download/View"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                </a>
                
                <!-- Delete (Only if not system or admin override) -->
                <button 
                    v-if="!item.is_system_generated"
                    @click="deleteDoc(item)" 
                    class="p-1.5 bg-white text-red-600 rounded-lg border border-red-100 shadow-sm hover:shadow hover:bg-red-50 transition-all"
                    title="Delete"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        </template>
    </BaseDataTable>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import BaseDataTable from '@/Components/BaseDataTable.vue';

const toast = useToastStore();
const documents = ref([]);
const meta = ref({});
const loading = ref(false);
const search = ref('');
const categoryFilter = ref('');
const perPage = ref(15);

const columns = {
    document: { label: 'Document' },
    employee: { label: 'Owner' },
    category: { label: 'Category' },
    source: { label: 'Source' }
};

const fetchDocuments = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/documents', {
            params: {
                page,
                search: search.value,
                category: categoryFilter.value,
                per_page: perPage.value
            }
        });
        documents.value = response.data.data.data;
        meta.value = response.data.data;
    } catch (e) {
        toast.error("Failed to load documents");
    } finally {
        loading.value = false;
    }
};

const handleSearch = (val) => {
    search.value = val;
    fetchDocuments(1);
};

const handleLimitChange = (val) => {
    perPage.value = val;
    fetchDocuments(1);
};

const deleteDoc = async (doc) => {
    if(!confirm(`Delete ${doc.title}?`)) return;
    try {
        // We use the employee-specific endpoint for deletion as our controller struct currently is nested
        // But wait, AdminDocumentController doesn't have destroy.
        // We might need to use the nested one: api/admin/employees/{id}/documents/{docId}
        // Luckily we have doc.employee_id
        await axios.delete(`/api/admin/employees/${doc.employee_id}/documents/${doc.id}`);
        toast.success("Document deleted");
        fetchDocuments(meta.value.current_page);
    } catch (e) {
        toast.error("Failed to delete document");
    }
};

onMounted(() => {
    fetchDocuments();
});
</script>
