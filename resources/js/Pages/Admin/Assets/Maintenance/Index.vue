<script setup>
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import { 
    WrenchScrewdriverIcon, 
    ClockIcon, 
    CheckCircleIcon, 
    CurrencyDollarIcon,
    ExclamationCircleIcon,
    UserIcon
} from '@heroicons/vue/24/outline'; // v2 Icons

defineOptions({ layout: MainLayout });

const props = defineProps({
    board: Object, // { 'Reported': [], 'In Repair': [] ... }
    stats: Object
});

const statuses = [
    { key: 'Reported', label: 'Reported', color: 'bg-red-50 text-red-700 border-red-200' },
    { key: 'Vendor Pending', label: 'Vendor Pending', color: 'bg-orange-50 text-orange-700 border-orange-200' },
    { key: 'In Repair', label: 'In Repair', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    { key: 'Fixed', label: 'Fixed / QA', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    { key: 'Bill Submitted', label: 'Bill Processed', color: 'bg-indigo-50 text-indigo-700 border-indigo-200' }
];

const draggedItem = ref(null);

const onDragStart = (event, item) => {
    draggedItem.value = item;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.dropEffect = 'move';
};

const onDrop = (event, status) => {
    const item = draggedItem.value;
    if (!item) return;

    if (item.status === status) return; // No change

    // Optimistic Update
    // We strictly should wait for server, but for UI snap we can assume success or reload
    router.post(route('admin.assets.maintenance.update-status', item.id), {
        status: status
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Toast handled globally
        }
    });

    draggedItem.value = null;
};

const getPriorityColor = (cost) => {
    if (cost > 1000) return 'text-red-600 font-bold';
    if (cost > 0) return 'text-gray-900';
    return 'text-gray-400 italic';
};
</script>

<template>
    <Head title="Maintenance Kanban" />

    <div class="h-screen flex flex-col overflow-hidden bg-gray-100">
        
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex flex-shrink-0 justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                    <WrenchScrewdriverIcon class="h-6 w-6" />
                </div>
                <div>
                     <h1 class="text-xl font-bold text-gray-900">Maintenance Hub</h1>
                     <p class="text-xs text-gray-500">Active Repairs & Warranties</p>
                </div>
            </div>
            
            <div class="flex gap-4 text-sm">
                <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="text-gray-500 block text-xs uppercase font-bold">Total Active</span>
                    <span class="text-lg font-bold text-gray-900">{{ stats.total_active }}</span>
                </div>
                <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="text-gray-500 block text-xs uppercase font-bold">Est Cost</span>
                    <span class="text-lg font-bold text-blue-600">${{ stats.total_cost }}</span>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-6">
            <div class="flex h-full gap-6 min-w-max">
                
                <!-- Columns -->
                <div 
                    v-for="status in statuses" 
                    :key="status.key"
                    class="w-80 flex flex-col h-full rounded-xl bg-gray-200/50 border border-gray-200"
                    @dragover.prevent
                    @drop="onDrop($event, status.key)"
                >
                    <!-- Column Header -->
                    <div class="p-3 font-bold text-sm flex justify-between items-center border-b border-gray-200 bg-white/50 backdrop-blur rounded-t-xl" :class="status.color">
                        {{ status.label }}
                        <span class="bg-white/80 px-2 rounded-full text-xs py-0.5 shadow-sm">
                            {{ board[status.key]?.length || 0 }}
                        </span>
                    </div>

                    <!-- Cards Container -->
                    <div class="flex-1 p-3 overflow-y-auto space-y-3">
                        <div 
                            v-for="item in board[status.key]" 
                            :key="item.id"
                            draggable="true"
                            @dragstart="onDragStart($event, item)"
                            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 cursor-grab active:cursor-grabbing hover:shadow-md transition-all group relative"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ item.asset?.asset_tag || 'TAG-???' }}</span>
                                <WrenchScrewdriverIcon class="h-4 w-4 text-gray-300 group-hover:text-blue-500" />
                            </div>
                            
                            <h4 class="font-bold text-gray-900 text-sm mb-1">{{ item.asset?.name }}</h4>
                            <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ item.description }}</p>

                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs text-gray-500">
                                <div class="flex items-center gap-1" title="Service Date">
                                    <ClockIcon class="h-3.5 w-3.5" />
                                    <span>{{ new Date(item.service_date).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex items-center gap-1 font-mono" :class="getPriorityColor(item.cost)">
                                    <CurrencyDollarIcon class="h-3.5 w-3.5" />
                                    {{ item.cost }}
                                </div>
                            </div>
                            
                            <!-- Logger info tooltip could go here -->
                        </div>

                        <!-- Empty State -->
                        <div v-if="!board[status.key]?.length" class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-300 rounded-lg">
                            <span class="text-xs">No Items</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
