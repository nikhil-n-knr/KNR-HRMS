<template>
    <MainLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-800 to-indigo-700">
                    Asset Kits (Bundles)
                </h1>
                <button @click="showModal = true" class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 transition">
                    + Create New Kit
                </button>
            </div>

            <!-- List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="kit in kits.data" :key="kit.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ kit.name }}</h3>
                            <p class="text-xs text-gray-500">{{ kit.items.length }} Items</p>
                        </div>
                        <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-lg text-xs font-bold">BUNDLE</span>
                    </div>
                    
                    <ul class="space-y-2 mb-6">
                        <li v-for="item in kit.items" :key="item.id" class="flex justify-between text-sm text-gray-600 bg-gray-50 p-2 rounded">
                            <span>{{ item.category.name }}</span>
                            <span class="font-mono font-bold">x{{ item.quantity }}</span>
                        </li>
                    </ul>

                    <div class="pt-4 border-t border-gray-100">
                        <form @submit.prevent="assign(kit)" class="flex gap-2">
                             <input v-model="assignForms[kit.id]" type="text" placeholder="User ID" class="w-full text-sm rounded border-gray-200" required>
                             <button type="submit" class="px-3 py-1 bg-gray-900 text-white text-xs font-bold rounded hover:bg-gray-700">Assign</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Create Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
                <div class="bg-white rounded-xl p-6 w-[600px] shadow-2xl">
                    <h2 class="text-lg font-bold mb-4">Create Asset Kit</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kit Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g. Remote Dev Kit" class="w-full rounded-lg border-gray-300 focus:ring-purple-500" required>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Items</label>
                            <div v-for="(item, index) in form.items" :key="index" class="flex gap-2">
                                <select v-model="item.category_id" class="flex-1 rounded-lg border-gray-300 focus:ring-purple-500" required>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <input v-model="item.quantity" type="number" min="1" class="w-20 rounded-lg border-gray-300 focus:ring-purple-500" required>
                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 px-2">x</button>
                            </div>
                            <button type="button" @click="addItem" class="text-sm text-purple-600 font-bold hover:underline">+ Add Item</button>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Create Kit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    kits: Object,
    categories: Array
});

// Create Logic
const showModal = ref(false);
const form = useForm({
    name: '',
    description: '',
    items: [{ category_id: '', quantity: 1 }]
});

const addItem = () => form.items.push({ category_id: '', quantity: 1 });
const removeItem = (i) => form.items.splice(i, 1);

const submit = () => {
    form.post(route('kits.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.items = [{ category_id: '', quantity: 1 }];
        }
    });
};

// Assign Logic (Simple ID input for now)
const assignForms = reactive({});

const assign = (kit) => {
    const userId = assignForms[kit.id];
    if(!userId) return;

    router.post(route('kits.assign', kit.id), {
        user_id: userId
    }, {
        onSuccess: () => {
             assignForms[kit.id] = '';
        }
    });
};
</script>
