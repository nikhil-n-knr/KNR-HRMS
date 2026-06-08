<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';

const props = defineProps({
    embedded: Boolean,
    rules: Array,
    badges: Array
});

const activeTab = ref('rules');

// --- Rules Logic ---
const updateRule = (rule, field, value) => {
    if (!rule.id) return;
    // Optimistic UI for quick toggles
    router.put(route('admin.gamification.rules.update', { pointRule: rule.id }), {
        id: rule.id, // Critical for fallback support
        [field]: value
    }, {
        preserveScroll: true
    });
};

// --- Badge Logic ---
const showBadgeModal = ref(false);
const badgeForm = useForm({
    name: '',
    slug: '',
    description: '',
    icon: '🏆',
    points_bonus: 0
});

const createBadge = () => {
    // Auto-slugify
    badgeForm.slug = badgeForm.name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
    
    badgeForm.post(route('admin.gamification.badges.store'), {
        onSuccess: () => {
             showBadgeModal.value = false;
             badgeForm.reset();
        }
    });
};

const deleteBadge = (id) => {
    if(confirm('Delete badge?')) {
        router.delete(route('admin.gamification.badges.destroy', id));
    }
};
</script>

<template>
    <component :is="embedded ? 'div' : MainLayout">
        <Head v-if="!embedded" title="Gamification Manager" />


        <div class="max-w-7xl mx-auto p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-3xl">🎮</span> Gamification Engine
                    </h1>
                    <p class="text-gray-500">Manage economy, points, and achievement badges.</p>
                </div>
                
                <!-- Tab Switcher -->
                <div class="bg-gray-100 p-1 rounded-lg flex gap-1">
                    <button 
                        @click="activeTab = 'rules'"
                        class="px-4 py-1.5 rounded-md text-sm font-bold transition-all"
                        :class="activeTab === 'rules' ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Points Config
                    </button>
                    <button 
                         @click="activeTab = 'badges'"
                        class="px-4 py-1.5 rounded-md text-sm font-bold transition-all"
                        :class="activeTab === 'badges' ? 'bg-white shadow text-pink-600' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Badges
                    </button>
                </div>
            </div>

            <!-- RULES TAB -->
            <div v-if="activeTab === 'rules'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <th class="px-6 py-4">Event Trigger</th>
                            <th class="px-6 py-4">Active</th>
                            <th class="px-6 py-4">Points Awarded</th>
                            <th class="px-6 py-4">Logic Condition (Adv)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="rule in rules" :key="rule.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800 block">{{ rule.name }}</span>
                                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ rule.event_key }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        :checked="rule.is_active" 
                                        @change="updateRule(rule, 'is_active', $event.target.checked)"
                                        class="sr-only peer"
                                    >
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </td>
                            <td class="px-6 py-4">
                                <input 
                                    type="number" 
                                    :value="rule.points" 
                                    @change="updateRule(rule, 'points', $event.target.value)"
                                    class="w-24 px-2 py-1 rounded border-gray-200 text-sm font-bold text-center focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </td>
                            <td class="px-6 py-4">
                                <input 
                                    type="text" 
                                    :value="rule.condition_logic"
                                     @change="updateRule(rule, 'condition_logic', $event.target.value)"
                                    placeholder="e.g. time < 09:00"
                                    class="w-full px-2 py-1 rounded border-gray-200 text-xs font-mono text-gray-600 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50"
                                >
                            </td>
                        </tr>
                        <tr v-if="rules.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">
                                No rules defined. Run seeders or add manually in DB.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BADGES TAB -->
            <div v-if="activeTab === 'badges'" class="space-y-6 animate-fade-in">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Create Card -->
                    <button @click="showBadgeModal = true" class="h-full min-h-[200px] border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center text-gray-400 hover:border-pink-400 hover:text-pink-500 hover:bg-pink-50 transition-all">
                        <span class="text-4xl mb-2">+</span>
                        <span class="font-bold">Create Badge</span>
                    </button>

                    <!-- User Badges -->
                    <div v-for="badge in badges" :key="badge.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col items-center text-center relative group">
                        <div class="text-4xl mb-4 bg-pink-50 w-16 h-16 flex items-center justify-center rounded-full border border-pink-100">
                            {{ badge.icon || '🏆' }}
                        </div>
                        <h3 class="font-bold text-gray-900">{{ badge.name }}</h3>
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ badge.description }}</p>
                        <div class="mt-4 px-3 py-1 bg-gray-100 rounded-full text-xs font-bold text-gray-600">
                             +{{ badge.points_bonus }} pts
                        </div>
                        
                        <button @click="deleteBadge(badge.id)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                            &times;
                        </button>
                    </div>
                </div>
            </div>

            <!-- Create Badge Modal -->
            <div v-if="showBadgeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showBadgeModal = false"></div>
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Design New Badge</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Badge Name</label>
                            <input v-model="badgeForm.name" type="text" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Icon (Emoji)</label>
                                <input v-model="badgeForm.icon" type="text" class="w-full rounded-lg border-gray-300 text-center text-xl">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bonus Points</label>
                                <input v-model="badgeForm.points_bonus" type="number" class="w-full rounded-lg border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Criteria Description</label>
                            <textarea v-model="badgeForm.description" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
                        </div>
                        <div class="pt-4 flex justify-end gap-2">
                             <button @click="showBadgeModal = false" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg">Cancel</button>
                             <button @click="createBadge" :disabled="badgeForm.processing" class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-bold">Create Badge</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
