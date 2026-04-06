<template>
    <Head title="Deployment Pulse" />
    <MainLayout>
        <div class="h-full bg-gray-50 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Deployment Pulse</h1>
                        <p class="mt-2 text-sm text-gray-600">Track release health, versioning, and deployment rounds across projects.</p>
                    </div>
                    <button @click="showModal = true" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Release Round
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <div v-for="round in rounds" :key="round.id" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 flex justify-between items-center text-sm border-b border-gray-50">
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-bold">v{{ round.version }}</span>
                                <span class="text-gray-400 font-medium">{{ formatDate(round.created_at) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" :class="statusColor(round.status)"></div>
                                <span class="font-bold text-gray-900 uppercase tracking-widest text-sm">{{ round.status }}</span>
                            </div>
                        </div>
                        <div class="p-8">
                            <h4 class="font-bold text-gray-900 text-lg mb-2">{{ round.project?.name || 'Global Release' }}</h4>
                            <p class="text-sm text-gray-500 mb-6">{{ round.notes || 'No release notes provided.' }}</p>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex -space-x-2">
                                    <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600">{{ i }}</div>
                                    <div class="w-8 h-8 rounded-full border-2 border-white bg-indigo-50 flex items-center justify-center text-sm font-bold text-indigo-600">+{{ round.tickets_count }}</div>
                                </div>
                                <button class="text-indigo-600 font-bold text-sm hover:underline">View Roadmap</button>
                            </div>
                        </div>
                    </div>

                    <div v-if="rounds.length === 0" class="bg-white rounded-3xl border-2 border-dashed border-gray-200 p-20 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">No Deployment Records</h3>
                        <p class="text-gray-500 mt-2">Start your first release round to track progress across environments.</p>
                        <button @click="showModal = true" class="mt-6 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition">Create First Round</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Release Round Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showModal = false">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">New Release Round</h2>
                            <p class="text-sm text-gray-500 mt-0.5">Create a new deployment tracking round</p>
                        </div>
                        <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Version *</label>
                                <input v-model="form.version" type="text" placeholder="e.g. 2.4.1" required
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Status *</label>
                                <select v-model="form.status" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="planning">Planning</option>
                                    <option value="staging">Staging</option>
                                    <option value="production">Production</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Project</label>
                            <select v-model="form.project_id" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">Global / All Projects</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Release Notes</label>
                            <textarea v-model="form.notes" rows="3" placeholder="What's included in this release..."
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showModal = false" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                            <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow hover:bg-indigo-700 transition disabled:opacity-60">
                                {{ submitting ? 'Creating...' : 'Create Round' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
    rounds: Array,
    projects: Array
});

const showModal = ref(false);
const submitting = ref(false);
const form = reactive({ version: '', status: 'planning', project_id: '', notes: '' });

const submit = () => {
    submitting.value = true;
    router.post(route('deployments.store'), form, {
        onSuccess: () => { showModal.value = false; Object.assign(form, { version: '', status: 'planning', project_id: '', notes: '' }); },
        onFinish: () => { submitting.value = false; }
    });
};

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');
const statusColor = (status) => ({ 'planning': 'bg-blue-400', 'staging': 'bg-amber-400', 'production': 'bg-emerald-400' }[status] || 'bg-gray-400');
</script>
