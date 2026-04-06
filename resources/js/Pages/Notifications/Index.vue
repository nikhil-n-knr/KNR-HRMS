<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notification Center</h2>
                        <p class="text-sm text-gray-500 mt-1">Stay updated with your tasks, sprints, and alerts.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button 
                            @click="markAllRead" 
                            :disabled="processing"
                            class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-lg transition-all"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Mark All Read
                        </button>
                    </div>
                </div>

                <!-- List -->
                <div class="space-y-4">
                    <div v-if="notifications.data.length === 0" class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <p class="text-gray-400 font-medium">No notifications yet.</p>
                    </div>

                    <div 
                        v-for="notification in notifications.data" 
                        :key="notification.id"
                        class="bg-white p-5 rounded-xl border transition-all duration-200 group relative overflow-hidden"
                        :class="[
                            notification.read_at ? 'border-gray-100 opacity-75' : 'border-indigo-100 shadow-md ring-1 ring-indigo-50'
                        ]"
                    >
                        <!-- Unread Indicator -->
                        <div v-if="!notification.read_at" class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>

                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="h-10 w-10 rounded-full flex items-center justify-center shrink-0" 
                                :class="{
                                    'bg-amber-100 text-amber-600': notification.type === 'task_moved',
                                    'bg-emerald-100 text-emerald-600': notification.type === 'task_assigned',
                                    'bg-blue-100 text-blue-600': notification.type === 'sprint_status',
                                    'bg-red-100 text-red-600': notification.type === 'alert'
                                }">
                                <svg v-if="notification.type === 'task_moved'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                <svg v-else-if="notification.type === 'task_assigned'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <svg v-else-if="notification.type === 'sprint_status'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                <svg v-else-if="notification.type === 'alert'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-gray-900 text-sm">{{ notification.title }}</h3>
                                    <span class="text-xs text-gray-400 font-mono">{{ notification.created_at }}</span>
                                </div>
                                <p class="text-gray-600 text-sm mt-1 mb-3">
                                    {{ notification.message }}
                                </p>
                                
                                <!-- Meta Data Badges -->
                                <div class="flex gap-2 mb-4" v-if="notification.data">
                                    <span v-if="notification.data.old_stage" class="text-sm bg-gray-100 text-gray-500 px-2 py-0.5 rounded">
                                        {{ notification.data.old_stage }} &rarr; {{ notification.data.new_stage }}
                                    </span>
                                    <span v-if="notification.data.moved_by" class="text-sm bg-gray-100 text-gray-500 px-2 py-0.5 rounded">
                                        By {{ notification.data.moved_by }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-3">
                                    <button 
                                        @click="handleAction(notification.id, 'clicked')"
                                        class="text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm transition-all"
                                    >
                                        View Details
                                    </button>
                                    
                                    <button 
                                        v-if="!notification.read_at"
                                        @click="handleAction(notification.id, 'acknowledged')"
                                        class="text-xs font-bold bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-2 rounded-lg shadow-sm transition-all flex items-center gap-2"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Acknowledge
                                    </button>

                                    <span v-else class="text-xs font-medium text-emerald-600 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        Acknowledged
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                 <div class="mt-6 flex justify-center" v-if="notifications.prev_page_url || notifications.next_page_url">
                    <div class="flex gap-2">
                         <Link v-if="notifications.prev_page_url" :href="notifications.prev_page_url" class="px-4 py-2 bg-white border rounded shadow-sm text-sm">Previous</Link>
                         <Link v-if="notifications.next_page_url" :href="notifications.next_page_url" class="px-4 py-2 bg-white border rounded shadow-sm text-sm">Next</Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';

const props = defineProps({
    notifications: Object
});

const processing = ref(false);

const handleAction = async (id, action) => {
    try {
        const response = await axios.post(route('notifications.action', { id, action }));
        
        if (response.data.success) {
            // Update local state to reflect read status
            const n = props.notifications.data.find(x => x.id === id);
            if (n) n.read_at = new Date().toISOString();
            
            // Redirect if clicked
            if (action === 'clicked' && response.data.redirect_url) {
                window.location.href = response.data.redirect_url;
            }
        }
    } catch (e) {
        console.error(e);
    }
};

const markAllRead = () => {
    processing.value = true;
    router.post(route('notifications.read-all'), {}, {
        onFinish: () => processing.value = false
    });
};
</script>
