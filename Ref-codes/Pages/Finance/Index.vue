<template>
  <div class="max-w-7xl mx-auto py-8">
      <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800">Invoices</h1>
      </div>

    <BaseDataTable
      :rows="invoices.data"
      :columns="columns"
      :filters="filters"
      search-placeholder="Search Client or Project..."
      @search="handleSearch"
      class="bg-white rounded-xl shadow-sm"
    >
      <template #status="{ row }">
        <span class="px-3 py-1 text-xs font-bold rounded-full"
            :class="{
                'bg-gray-100 text-gray-700': row.status === 'draft',
                'bg-blue-100 text-blue-700': row.status === 'sent',
                'bg-emerald-100 text-emerald-700': row.status === 'paid',
                'bg-red-100 text-red-700': row.status === 'cancelled'
            }"
        >
            {{ row.status.toUpperCase() }}
        </span>
      </template>

      <template #total="{ row }">
          <span class="font-bold text-gray-900">{{ formatCurrency(row.total) }}</span>
      </template>

      <template #actions="{ row }">
        <button class="text-gray-500 hover:text-indigo-600 font-medium text-sm">Download</button>
      </template>
    </BaseDataTable>
    
    <!-- Pagination -->
    <div class="mt-4 flex justify-center">
         <!-- Simple pagination links (Next/Prev) would go here, 
              connecting to invoices.links from Laravel pagination -->
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';

defineOptions({ layout: MainLayout });

const props = defineProps(['invoices', 'filters']);

const columns = [
  { key: 'id', label: '#' },
  { key: 'client.name', label: 'Client' },
  { key: 'project.name', label: 'Project' },
  { key: 'due_date', label: 'Due Date' },
  { key: 'total', label: 'Total', slot: 'total' },
  { key: 'status', label: 'Status', slot: 'status' }
];

const handleSearch = (query) => {
  router.get(route('invoices.index'), { search: query }, { preserveState: true, replace: true });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);
};
</script>
