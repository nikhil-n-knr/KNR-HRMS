<template>
    <MainLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <h2 class="font-bold text-xl text-gray-900 leading-tight">Bug Tracker Command Center</h2>
                <div class="flex space-x-1 bg-gray-100/80 p-1 rounded-xl overflow-x-auto scroll-smooth snap-x border border-gray-200/50">
                     <button 
                        v-for="t in tabs" 
                        :key="t.id"
                        @click="router.visit(route('bugs.index', { tab: t.id }))"
                        :class="[
                            activeTab === t.id 
                                ? 'bg-white shadow-md text-emerald-600' 
                                : 'text-gray-500 hover:text-gray-800 hover:bg-white/40',
                            'px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 whitespace-nowrap flex-shrink-0 snap-center'
                        ]"
                    >
                        {{ t.name }}
                    </button>
                    <div class="flex-shrink-0 w-8 md:hidden"></div>
                </div>
            </div>
        </template>

        <BugTrackerHeader :projects="projects">
            <template #actions>
                <div class="flex gap-2">
                    <a :href="route('portal.login')" target="_blank" class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all">
                        <ArrowTopRightOnSquareIcon class="w-4 h-4 text-emerald-600" />
                        Client Login UI
                    </a>
                    <a :href="route('bugs.export.pdf')" target="_blank" class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-2">
                        <DocumentArrowDownIcon class="w-4 h-4" />
                        Sheet
                    </a>
                    <button @click="showImportModal = true" class="px-3 py-1.5 text-xs font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center gap-2 shadow-md">
                        <ArrowUpTrayIcon class="w-4 h-4" />
                        Bulk Import
                    </button>
                </div>
            </template>
        </BugTrackerHeader>
        
        <!-- Bulk Import Modal -->
        <Modal :show="showImportModal" @close="showImportModal = false" max-width="lg">
            <template #default>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Bulk Import Bug Tickets</h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                            <p class="text-sm text-indigo-700 font-medium mb-2 flex items-center gap-2">
                                <InformationCircleIcon class="w-4 h-4" />
                                Instructions
                            </p>
                            <ul class="text-xs text-indigo-600 space-y-1 list-disc list-inside">
                                <li>Use the sample template for correct formatting.</li>
                                <li>Subject and Project mapping are mandatory.</li>
                                <li>Project name must match exactly.</li>
                            </ul>
                            <a :href="route('bugs.import.sample')" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 hover:underline">
                                <DocumentArrowDownIcon class="w-3.5 h-3.5" />
                                Download Sample CSV
                            </a>
                        </div>
                        
                        <div class="space-y-2">
                            <InputLabel value="Select Destination Project" />
                            <select v-model="importForm.project_id" class="w-full border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Select a project...</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <InputLabel value="Upload CSV/Excel File" />
                            <input type="file" @change="e => importForm.file = e.target.files[0]" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <SecondaryButton @click="showImportModal = false">Cancel</SecondaryButton>
                            <PrimaryButton @click="submitImport" :disabled="importForm.processing || !importForm.project_id || !importForm.file">
                                Start Import
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </template>
        </Modal>

        <div class="p-6">
            <!-- Dynamic Component Loading based on Tab -->
            <component 
                :is="activeComponent" 
                v-bind="$props" 
                @switch-to-intelligence="router.visit(route('bugs.index', { tab: 'intelligence' }))"
                class="flex-1" 
            />
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { DocumentArrowDownIcon, CodeBracketIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import Analytics from './Analytics.vue';
import GlobalTicketMatrix from './GlobalTicketMatrix.vue';
import Create from './Create.vue';
import ExternalPortalPartial from './Components/ExternalPortalPartial.vue';
import ReportBuilder from './ReportBuilder.vue';
import WorkflowArchitect from './WorkflowArchitect.vue';
import ClientHub from './ClientHub.vue';
import BugTrackerHeader from './Partials/BugTrackerHeader.vue';
import { useBugTrackerStore } from '@/Stores/bugTrackerStore';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/vue3';
import { InformationCircleIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';

// Define layout option to avoid nested layouts if components also define it
defineOptions({ layout: null }); 

const props = defineProps({
    tab: String,
    // Analytics props
    hotspots: Array,
    status_breakdown: Array,
    sla_breaches: Array,
    velocity: Object,
    avg_resolution_hours: Number,
    leaderboard: Array,
    // List props
    bugs: Object,
    filters: Object,
    projects: Array,
    stages: Array,
    custom_views: Array,
    open_critical_count: Number,
    lookup: Object,
    counts: Object,
    // Workflow props
    workflow: Object,
    teams: Array,
    roles: Array
});

const store = useBugTrackerStore();
const showImportModal = ref(false);
const importForm = useForm({
    project_id: '',
    file: null
});

const submitImport = () => {
    importForm.post(route('bugs.import.bulk'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        }
    });
};

const tabs = computed(() => {
    if (store.viewMode === 'client') {
        return [{ id: 'portal', name: 'Client Portal' }];
    }
    if (store.viewMode === 'audit') {
        return [{ id: 'security', name: 'Security & Access' }];
    }
    return [
        { id: 'tracker', name: 'Global Matrix' },
        { id: 'dashboard', name: 'Analytics' },
        { id: 'intelligence', name: 'Intelligence' },
        { id: 'workflow', name: 'Workflow Architect' },
        { id: 'report', name: 'Report Issue' } 
    ];
});

const activeTab = computed(() => {
    if (store.viewMode === 'client') return 'portal';
    if (store.viewMode === 'audit') return 'security';
    return props.tab || 'tracker';
});

const activeComponent = computed(() => {
    if (store.viewMode === 'client') return ExternalPortalPartial;
    if (store.viewMode === 'audit') return ClientHub;
    
    switch (activeTab.value) {
        case 'dashboard': return Analytics;
        case 'tracker': return GlobalTicketMatrix;
        case 'intelligence': return ReportBuilder;
        case 'workflow': return WorkflowArchitect;
        case 'report': return Create;
        default: return GlobalTicketMatrix;
    }
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
