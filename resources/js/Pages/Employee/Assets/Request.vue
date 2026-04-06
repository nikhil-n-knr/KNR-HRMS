<template>
    <MainLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <h1 class="text-2xl font-bold text-gray-800">Request New Asset</h1>
            <p class="text-gray-500">Need execution tools? Submit a request for approval.</p>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Type</label>
                        <select v-model="form.category_id" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Select Category...</option>
                            <option v-for="cat in categories" :value="cat.id" :key="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</p>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" v-model="form.priority" value="Normal" class="text-emerald-600 focus:ring-emerald-500">
                                <span>Normal</span>
                            </label>
                             <label class="flex items-center gap-2">
                                <input type="radio" v-model="form.priority" value="High" class="text-red-600 focus:ring-red-500">
                                <span class="text-red-600 font-medium">Urgent</span>
                            </label>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Justification</label>
                        <textarea v-model="form.reason" rows="4" placeholder="Why do you need this equipment?" class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        <p v-if="form.errors.reason" class="text-red-500 text-xs mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="route('employee.assets.index')" class="px-5 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 shadow-sm disabled:opacity-50">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    categories: Array
});

const form = useForm({
    category_id: '',
    reason: '',
    priority: 'Normal'
});

const submit = () => {
    form.post(route('employee.assets.store'));
};
</script>
