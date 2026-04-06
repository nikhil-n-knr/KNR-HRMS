<template>
    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex justify-between items-center">
            <div class="flex gap-3">
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Search accounts..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
            <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-blue-100 font-semibold">
                <i class="fas fa-plus mr-2 text-sm"></i>New Account
            </button>
        </div>

        <!-- Accounts List -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
             <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Account Name</th>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Industry</th>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Website</th>
                        <th class="px-6 py-4 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Contacts</th>
                        <th class="px-6 py-4 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Deals</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <tr v-for="account in filteredAccounts" :key="account.id" class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12 bg-indigo-50 rounded-2xl flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ account.name }}</div>
                                    <div class="text-sm text-gray-400 font-medium">Customer ID: AC-{{ account.id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-600">
                             <span class="px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">{{ account.industry || 'General' }}</span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-blue-600">
                            <a v-if="account.website" :href="account.website" target="_blank" class="hover:underline flex items-center">
                                {{ account.website.replace('https://', '').replace('http://', '') }}
                                <i class="fas fa-external-link-alt ml-2 text-sm opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </a>
                            <span v-else class="text-gray-300">-</span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-center">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                {{ account.contacts_count || 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-center">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                {{ account.deals_count || 0 }}
                            </span>
                        </td>
                         <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                            <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all mr-2 shadow-sm border border-gray-100">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-all shadow-sm border border-gray-100">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <!-- Empty State -->
            <div v-if="!filteredAccounts || filteredAccounts.length === 0" class="text-center py-20 bg-gray-50/50">
                <div class="w-20 h-20 bg-white rounded-3xl shadow-sm flex items-center justify-center mx-auto mb-6 transform -rotate-3 border border-gray-100">
                    <i class="fas fa-building text-3xl text-gray-200"></i>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">No Accounts Found</h3>
                <p class="text-gray-500 max-w-xs mx-auto text-sm leading-relaxed mb-8">Centralize your business partnerships and manage corporate relationships with ease.</p>
                <button @click="showCreateModal = true" class="px-8 py-3 bg-blue-600 text-white rounded-2xl font-bold shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                    Create Your First Account
                </button>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-white">
                <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-100 flex justify-between items-center text-left">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">New Account</h3>
                        <p class="text-xs text-gray-500 font-medium tracking-tight">Register a corporate entity in your CRM</p>
                    </div>
                    <button @click="showCreateModal = false" class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center shadow-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-8 space-y-6 text-left">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Account Name</label>
                        <input v-model="form.name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm py-3 px-4 shadow-inner" placeholder="Acme Corporation">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Industry</label>
                        <select v-model="form.industry" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm py-3 px-4 shadow-inner">
                            <option value="">Select Industry</option>
                            <option value="Technology">Technology</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Retail">Retail</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Website</label>
                        <input v-model="form.website" type="text" class="w-full bg-gray-50 border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm py-3 px-4 shadow-inner" placeholder="https://www.acme.com">
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-4">
                        <button type="button" @click="showCreateModal = false" class="text-sm font-bold text-gray-400 hover:text-gray-600">DISCARD</button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-black text-sm hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 disabled:opacity-50">
                            {{ form.processing ? 'SAVING...' : 'SAVE ACCOUNT' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    }
});

const showCreateModal = ref(false);
const searchQuery = ref('');

const form = useForm({
    name: '',
    industry: '',
    website: '',
    phone: '',
    billing_address: '',
});

const submit = () => {
    form.post(route('crm.accounts.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const filteredAccounts = computed(() => {
    let list = props.accounts || [];
    if (!searchQuery.value) return list;
    
    return list.filter(acc =>
        acc.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        acc.industry?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>
