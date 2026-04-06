<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Section Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="stat in learnerStats" :key="stat.label" class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-6 group hover:shadow-md transition-all">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl transition-all" :class="stat.bg">
                    <i :class="stat.icon"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">{{ stat.label }}</p>
                    <h3 class="text-xl font-black text-gray-900 tracking-tighter">{{ stat.value }}</h3>
                </div>
            </div>
        </div>

        <!-- Learner Table -->
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden pb-12">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight mb-1 uppercase">Deep Learner Index</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Real-time status of 2.4k active minds</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <input 
                            type="text" 
                            placeholder="Search by UID, Name, or Email..." 
                            class="pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-xs font-bold text-gray-600 focus:ring-2 focus:ring-indigo-100 transition-all w-64 uppercase tracking-widest"
                        />
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-xs group-hover:text-indigo-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Identiy / Rank</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Engagement Level</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Mastery Assets</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Revenue Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Rapid Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in learners.data" :key="user.id" class="group hover:bg-emerald-50/20 transition-all">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex-shrink-0 border-2 border-white shadow-sm overflow-hidden relative group-hover:scale-110 transition-transform">
                                        <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-black text-xs">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-gray-900 tracking-tight leading-none mb-1.5 uppercase italic">{{ user.name }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Enrollments: {{ user.lms_enrollments_count }}</span>
                                    </div>
                                    <div class="w-32 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" :style="{ width: '65%' }"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col items-center px-3 py-1 bg-emerald-50 rounded-xl border border-emerald-100">
                                        <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">Certs</span>
                                        <span class="text-xs font-black text-emerald-700 leading-none">{{ user.lms_certificates_count }}</span>
                                    </div>
                                    <div class="flex flex-col items-center px-3 py-1 bg-orange-50 rounded-xl border border-orange-100">
                                        <span class="text-[9px] font-black text-orange-600 uppercase tracking-widest leading-none mb-1">XP</span>
                                        <span class="text-xs font-black text-orange-700 leading-none">2.4k</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <template v-if="user.lms_subscriptions?.length">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-200"></div>
                                        <span class="text-[10px] font-black text-gray-800 uppercase tracking-widest underline decoration-wavy decoration-emerald-200">PRO - ANNUAL</span>
                                    </div>
                                </template>
                                <template v-else>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Free Tier / B2C</span>
                                </template>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm group" title="View Profile">
                                        <i class="far fa-eye"></i>
                                    </button>
                                    <button class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50 transition-all shadow-sm group" title="Sync LMS Access">
                                        <i class="fas fa-sync-alt transform group-hover:rotate-180 transition-transform"></i>
                                    </button>
                                    <button class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 transition-all shadow-sm group" title="Access Matrix">
                                        <i class="fas fa-user-shield"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Placeholder -->
            <div v-if="learners.links" class="mt-8 px-8 flex justify-between items-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Showing {{ learners.from }}-{{ learners.to }} of {{ learners.total }} learners</p>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in learners.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-gray-100 transition-all hover:bg-emerald-600 hover:text-white"
                        :class="link.active ? 'bg-gray-900 text-white shadow-xl' : 'bg-white text-gray-400'"
                    ></Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    learners: Object
});

const learnerStats = computed(() => [
    { label: 'Total Learners', value: props.learners.total.toLocaleString(), icon: 'fas fa-users', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Daily Peak Active', value: '428', icon: 'fas fa-bolt', bg: 'bg-orange-50 text-orange-600' },
    { label: 'Avg Study Hours', value: '2.4 hrs/day', icon: 'fas fa-clock', bg: 'bg-emerald-50 text-emerald-600' },
]);
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
