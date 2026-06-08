<template>
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/50 shadow-xl p-6 relative overflow-hidden group">
      <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl -mr-16 -mt-16 transition-all group-hover:bg-emerald-500/20"></div>
      
      <div class="relative flex flex-col sm:flex-row gap-6 items-start sm:items-center">
        <!-- Avatar -->
        <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-3xl font-bold text-indigo-600 border-4 border-white shadow-lg shrink-0">
            {{ getInitials(employee.first_name, employee.last_name) }}
        </div>
        
        <!-- Info -->
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">{{ employee.first_name }} {{ employee.last_name }}</h1>
            <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-600">
                <span class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    {{ employee.designation }}
                </span>
                <span class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                     <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    {{ employee.employee_code }}
                </span>
                 <span class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    {{ employee.email || 'No email' }}
                </span>
            </div>
        </div>

        <!-- Action -->
        <div>
            <span 
                class="px-3 py-1 inline-flex text-sm font-semibold rounded-full border shadow-sm"
                :class="{
                    'bg-green-50 text-green-700 border-green-200': employee.status === 'active',
                    'bg-red-50 text-red-700 border-red-200': employee.status === 'terminated',
                    'bg-yellow-50 text-yellow-700 border-yellow-200': ['probation', 'notice_period'].includes(employee.status)
                }"
            >
                {{ formatStatus(employee.status) }}
            </span>
        </div>
      </div>
    </div>
</template>

<script setup>
defineProps({
    employee: { type: Object, required: true }
});

const getInitials = (f, l) => `${f?.[0] || ''}${l?.[0] || ''}`.toUpperCase();
const formatStatus = (s) => (s || '').split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
</script>
