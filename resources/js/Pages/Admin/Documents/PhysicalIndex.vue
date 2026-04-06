<template>
    <MainLayout>
         <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
                    Physical Documents
                </h1>
                <button @click="showModal = true" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow-sm hover:bg-emerald-700 transition">
                    Check-In Document
                </button>
            </div>

             <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/50 overflow-hidden">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-emerald-50/50 text-emerald-900 border-b border-white/20">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Document Type</th>
                            <th class="px-6 py-4 font-semibold">Owner (User)</th>
                            <th class="px-6 py-4 font-semibold">Location</th>
                            <th class="px-6 py-4 font-semibold">Ref</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-white/60 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ doc.document_type }}</td>
                            <td class="px-6 py-4">{{ doc.user?.name || 'Unassigned' }}</td>
                            <td class="px-6 py-4">{{ doc.location?.name }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ doc.container_ref }}</td>
                             <td class="px-6 py-4">
                                <span :class="{
                                    'px-2 py-1 rounded-full text-xs font-semibold': true,
                                    'bg-green-100 text-green-700': doc.status === 'In_Custody',
                                    'bg-amber-100 text-amber-700': doc.status === 'With_Employee',
                                    'bg-red-100 text-red-700': doc.status === 'Missing'
                                }">
                                    {{ doc.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button class="text-emerald-600 font-medium">Checkout</button>
                            </td>
                        </tr>
                         <tr v-if="documents.data.length === 0">
                           <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                              No physical documents tracked.
                           </td>
                        </tr>
                    </tbody>
                </table>
             </div>
            <!-- Check-In Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
                <div class="bg-white rounded-xl p-6 w-[500px] shadow-2xl">
                    <h2 class="text-lg font-bold mb-4">Check-In Physical Document</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Document Type</label>
                            <input v-model="form.document_type" type="text" placeholder="e.g. Employee Contract, NDA" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">User (Owner)</label>
                            
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs text-gray-500">Is this for an external party?</span>
                                <button type="button" @click="isOutsider = !isOutsider" class="text-xs font-bold text-emerald-600 underline">
                                    {{ isOutsider ? 'Switch to Employee' : 'Switch to Outsider' }}
                                </button>
                            </div>

                            <div v-if="!isOutsider">
                                <select v-model="form.user_id" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 text-sm">
                                    <option value="" disabled>Select Employee...</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                            <div v-else>
                                <input v-model="form.outsider_name" type="text" placeholder="Enter Name (e.g. Auditor, Client)" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 text-sm">
                            </div>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <select v-model="form.location_id" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500" required>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Container Ref / Shelf</label>
                            <input v-model="form.container_ref" type="text" placeholder="e.g. Box A-12" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500" required>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Confirm Check-In</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
    documents: Object,
    locations: Array,
    users: Array // Added
});

const showModal = ref(false);
const isOutsider = ref(false); // Added

const form = useForm({
    document_type: '',
    user_id: '',
    outsider_name: '', // Added
    location_id: '',
    container_ref: ''
});

const submit = () => {
    // Clear the non-relevant field
    if (isOutsider.value) {
        form.user_id = null;
    } else {
        form.outsider_name = null;
    }

    form.post(route('admin.physical-documents.check-in'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            isOutsider.value = false;
        }
    });
};
</script>
