<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    userId: [Number, String]
});

const loading = ref(true);
const user = ref(null);
const stats = ref({
    attendance: '95%',
    leaves: 4,
    projects: 2
});

const loadUser = async () => {
    try {
        const res = await axios.get(`/api/admin/users/${props.userId}`);
        user.value = res.data.data || res.data;
    } catch (e) {
        console.error(e);
        useToastStore().error("Failed to load user details");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadUser();
});
</script>

<template>
    <div v-if="loading" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
    
    <div v-else-if="user" class="space-y-6 animate-fade-in">
        <!-- Profile Header -->
        <div class="text-center">
            <div class="h-20 w-20 rounded-full bg-gray-200 mx-auto flex items-center justify-center text-2xl font-bold text-gray-500 overflow-hidden border-4 border-white shadow-lg">
                <img v-if="user.profile_photo_url" :src="user.profile_photo_url" alt="" class="h-full w-full object-cover">
                <span v-else>{{ user.name.charAt(0) }}</span>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900">{{ user.name }}</h3>
            <p class="text-sm text-gray-500">{{ user.email }}</p>
            <div class="mt-2 flex justify-center gap-2">
                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase tracking-wider">{{ user.status }}</span>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase tracking-wider">{{ user.employee_id }}</span>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                <p class="text-gray-400 uppercase font-bold text-[10px]">Att.</p>
                <p class="text-indigo-600 font-bold text-lg">{{ stats.attendance }}</p>
            </div>
            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                <p class="text-gray-400 uppercase font-bold text-[10px]">Leave</p>
                <p class="text-indigo-600 font-bold text-lg">{{ stats.leaves }}</p>
            </div>
            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                <p class="text-gray-400 uppercase font-bold text-[10px]">Proj.</p>
                <p class="text-indigo-600 font-bold text-lg">{{ stats.projects }}</p>
            </div>
        </div>

        <!-- Details -->
        <div class="space-y-4">
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Work Information</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs">Department</p>
                        <p class="font-medium text-gray-900">{{ user.department?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Designation</p>
                        <p class="font-medium text-gray-900">{{ user.designation?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Location</p>
                        <p class="font-medium text-gray-900">{{ user.location?.name || 'N/A' }}</p>
                    </div>
                     <div>
                        <p class="text-gray-500 text-xs">Role</p>
                        <p class="font-medium text-gray-900">{{ user.roles?.[0]?.name || 'User' }}</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                 <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Contact</h4>
                 <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                        <span class="text-gray-900">{{ user.email }}</span>
                    </div>
                     <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg>
                        <span class="text-gray-900">{{ user.phone || 'N/A' }}</span>
                    </div>
                 </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex gap-3">
            <a :href="`/admin/users/${user.id}/edit`" class="flex-1 bg-indigo-600 text-white text-center py-2 rounded-lg font-medium hover:bg-indigo-700">Edit Profile</a>
            <button class="flex-1 bg-white border border-gray-300 text-gray-700 text-center py-2 rounded-lg font-medium hover:bg-gray-50">View Activity</button>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
