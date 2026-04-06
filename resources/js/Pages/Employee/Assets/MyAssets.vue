<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import { 
    CheckCircleIcon, 
    ExclamationTriangleIcon, 
    DevicePhoneMobileIcon, 
    ComputerDesktopIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline'; // Using v2 icons

defineOptions({ layout: MainLayout });

const props = defineProps({
    assets: Object, // Grouped by status: { pending: [], active: [], returned: [] }
    user: Object
});

const showAcceptModal = ref(false);
const showReturnModal = ref(false);
const selectedAsset = ref(null);

// Forms
const acceptForm = useForm({
    otp: '',
    terms_accepted: false
});

const returnForm = useForm({
    reason: '',
    condition: 'Working',
    notes: ''
});

// Actions
const openAccept = (asset) => {
    selectedAsset.value = asset;
    acceptForm.reset();
    showAcceptModal.value = true;
};

const openReturn = (asset) => {
    selectedAsset.value = asset;
    returnForm.reset();
    showReturnModal.value = true;
};

const submitAccept = () => {
    acceptForm.post(route('employee.assets.accept', selectedAsset.value.id), {
        onSuccess: () => showAcceptModal.value = false
    });
};

const submitReturn = () => {
    returnForm.post(route('employee.assets.return', selectedAsset.value.id), {
        onSuccess: () => showReturnModal.value = false
    });
};

const getIcon = (type) => {
    if (type === 'Laptop' || type === 'Monitor') return ComputerDesktopIcon;
    return DevicePhoneMobileIcon;
};

const getStatusColor = (status) => {
    if (status === 'Assigned') return 'bg-amber-100 text-amber-800 border-amber-200'; // Pending User Action
    if (status === 'Deployed') return 'bg-emerald-100 text-emerald-800 border-emerald-200'; // Active
    return 'bg-gray-100 text-gray-600';
};
</script>

<template>
    <Head title="My Assets" />

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">My Workspace Assets</h1>
            <p class="text-sm text-gray-500 mt-1">Manage the equipment currently in your custody.</p>
        </div>

        <!-- 1. Pending Acceptance (Urgent) -->
        <section v-if="assets.pending.length" class="mb-10">
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <ExclamationTriangleIcon class="h-6 w-6 text-amber-600" />
                    <h2 class="text-lg font-bold text-amber-900">Pending Handover</h2>
                </div>
                <p class="text-sm text-amber-700 mb-6">Please customized verify the physical condition of these items and accept custody.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="asset in assets.pending" :key="asset.id" class="bg-white rounded-xl p-5 shadow-sm border border-amber-100">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <component :is="getIcon(asset.category?.name)" class="h-6 w-6 text-gray-600" />
                            </div>
                            <span class="px-2 py-1 text-xs font-bold rounded bg-amber-100 text-amber-700">Action Required</span>
                        </div>
                        <h3 class="font-bold text-gray-900">{{ asset.name }}</h3>
                        <p class="text-xs text-gray-500 font-mono mt-1">{{ asset.serial_number }}</p>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100 flex gap-3">
                            <button @click="openAccept(asset)" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                                I Accept Asset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Active Assets -->
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <CheckCircleIcon class="h-5 w-5 text-emerald-600" />
                Active Custody
            </h2>
            
            <div v-if="assets.active.length" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="asset in assets.active" :key="asset.id" class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-shadow">
                     <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                             <component :is="getIcon(asset.category?.name)" class="h-6 w-6 text-blue-600" />
                        </div>
                        <span class="px-2 py-1 text-xs font-bold rounded bg-emerald-100 text-emerald-800">Active</span>
                     </div>
                     <h3 class="font-bold text-gray-900">{{ asset.name }}</h3>
                     <p class="text-xs text-gray-500 font-mono mt-1">{{ asset.serial_number }}</p>
                     
                     <div class="mt-4 flex flex-col gap-2">
                         <div class="text-xs text-gray-500 flex justify-between">
                             <span>Assigned:</span>
                             <span class="font-medium text-gray-700">{{ new Date(asset.pivot?.assigned_at || asset.updated_at).toLocaleDateString() }}</span>
                         </div>
                     </div>

                     <div class="mt-4 pt-4 border-t border-gray-100">
                         <button @click="openReturn(asset)" class="w-full py-2 text-sm text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium">
                            Request Return
                         </button>
                     </div>
                </div>
            </div>
            <div v-else class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <div class="mx-auto h-12 w-12 text-gray-300">
                    <ComputerDesktopIcon />
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No active assets</h3>
                <p class="mt-1 text-sm text-gray-500">You don't have any equipment assigned to you yet.</p>
            </div>
        </section>

        <!-- Modals -->
        <!-- Accept Modal -->
        <div v-if="showAcceptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 animate-scale-in">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Accept Custody</h3>
                <p class="text-sm text-gray-500 mb-4">By accepting, you acknowledge that <b>{{ selectedAsset?.name }}</b> is in your possession and good working condition.</p>
                
                <form @submit.prevent="submitAccept">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg mb-4">
                        <input v-model="acceptForm.terms_accepted" type="checkbox" id="terms" class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 border-2" required>
                        <label for="terms" class="text-xs text-gray-600 leading-relaxed cursor-pointer select-none">
                            I agree to the <a href="#" class="text-blue-600 underline">Equipment Policy</a>. I understand that I am liable for physical damage due to negligence.
                        </label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showAcceptModal = false" class="px-4 py-2 text-sm text-gray-700 font-bold hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="!acceptForm.terms_accepted || acceptForm.processing" class="px-4 py-2 text-sm text-white font-bold bg-amber-600 hover:bg-amber-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirm Acceptance
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Return Modal -->
        <div v-if="showReturnModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
             <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 animate-scale-in">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Initiate Return</h3>
                    <button @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600"><XCircleIcon class="h-6 w-6"/></button>
                </div>
                
                <form @submit.prevent="submitReturn">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Return</label>
                            <select v-model="returnForm.reason" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="" disabled>Select a reason...</option>
                                <option value="Resignation">Resignation / Offboarding</option>
                                <option value="Malfunction">Device Malfunction / Repair</option>
                                <option value="Upgrade">Upgrade Request</option>
                                <option value="Unused">No longer needed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Condition</label>
                             <select v-model="returnForm.condition" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="Working">Good / Working</option>
                                <option value="Damaged">Physically Damaged</option>
                                <option value="Dead">Not Powering On</option>
                            </select>
                        </div>
                        
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                             <textarea v-model="returnForm.notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Any scratches, issues, etc..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" :disabled="returnForm.processing" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-md transition-all">
                            Submit Return Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<style scoped>
.animate-scale-in {
    animation: scaleIn 0.2s ease-out;
}
@keyframes scaleIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
