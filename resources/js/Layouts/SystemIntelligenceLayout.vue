<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppFooter from '@/Components/UI/AppFooter.vue';
import { 
    ShieldCheckIcon, 
    TrophyIcon, 
    UserGroupIcon, 
    BoltIcon 
} from '@heroicons/vue/24/outline';

const page = usePage();
const currentUrl = computed(() => page.url);

const navigation = computed(() => [
    {
        name: 'Policies',
        href: route().has('admin.attendance.policies.index') ? route('admin.attendance.policies.index') : '#',
        icon: ShieldCheckIcon,
        active: currentUrl.value.includes('/admin/attendance/policies')
    },
    {
        name: 'Gamification',
        href: route().has('admin.gamification.index') ? route('admin.gamification.index') : '#',
        icon: TrophyIcon,
        active: currentUrl.value.includes('/admin/attendance/gamification')
    },
    {
        name: 'Teams',
        href: route().has('admin.teams.index') ? route('admin.teams.index') : '#',
        icon: UserGroupIcon,
        active: currentUrl.value.includes('/admin/attendance/teams')
    },
    {
        name: 'Workflows',
        href: route().has('admin.workflows.index') ? route('admin.workflows.index') : '#',
        icon: BoltIcon,
        active: currentUrl.value.includes('/admin/workflows')
    }
]);
</script>

<template>
    <MainLayout>
        <!-- System Intelligence Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-6">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Intelligence</h1>
                    <p class="mt-1 text-sm text-gray-500">Master control for Rules, Rewards, Teams, and Flows.</p>
                </div>
                
                <!-- Navigation Tabs -->
                <div class="flex space-x-8 overflow-x-auto">
                    <Link 
                        v-for="item in navigation" 
                        :key="item.name"
                        :href="item.href"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200 ease-in-out"
                        :class="[
                            item.active
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        <component 
                            :is="item.icon" 
                            class="mr-2 h-5 w-5"
                            :class="[
                                item.active ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'
                            ]"
                            aria-hidden="true" 
                        />
                        {{ item.name }}
                    </Link>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto py-8">
                <slot />
                <AppFooter />
            </div>
        </div>
    </MainLayout>
</template>
