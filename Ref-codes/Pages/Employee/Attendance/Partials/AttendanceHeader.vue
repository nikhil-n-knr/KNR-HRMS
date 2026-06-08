<script setup>
import { Link } from '@inertiajs/vue3';
import {
    ClockIcon,
    TableCellsIcon,
    ArrowsRightLeftIcon,
    DocumentCheckIcon
} from '@heroicons/vue/24/outline';

defineProps({
    activeTab: String
});

const tabs = [
    { id: 'dashboard',  label: 'Dashboard', icon: ClockIcon },
    { id: 'timesheets', label: 'Timesheet',     icon: TableCellsIcon },
    { id: 'requests',   label: 'Requests',   icon: DocumentCheckIcon },
    { id: 'leave',      label: 'Holidays',    icon: ArrowsRightLeftIcon },
];
</script>

<template>
    <!-- ▓▓ ATTENDANCE HERO ▓▓ -->
    <div
        class="relative overflow-hidden
               rounded-none sm:rounded-[32px]
               bg-gradient-to-r from-[#4527c5] via-[#7446e8] to-[#b05cff]"
    >
        <!-- Decorative circles -->
        <div
            class="absolute -top-24 left-[32%]
                   w-72 h-72 rounded-full bg-white/5"
        ></div>

        <div
            class="absolute -top-10 right-8
                   w-96 h-96 rounded-full bg-white/5"
        ></div>

        <!-- Main Content -->
        <div
            class="relative z-10
                   px-4 sm:px-10 lg:px-14
                   py-6 sm:py-10"
        >
            <div
                class="flex flex-col lg:flex-row
                       lg:items-center lg:justify-between
                       gap-6"
            >
                <!-- LEFT CONTENT -->
                <div class="max-w-2xl">
                    <p
                        class="text-[10px] sm:text-sm
                               font-black uppercase
                               tracking-[0.3em]
                               text-white/55 mb-2"
                    >
                        Attendance
                    </p>

                    <h1
                        class="text-2xl sm:text-2xl lg:text-2xl
                               font-black text-white
                               tracking-tight leading-none"
                    >
                        Attendance Center
                    </h1>

                    <p
                        class="mt-2
                               text-xs sm:text-base lg:text-sm
                               text-white/65 leading-relaxed"
                    >
                        Track your work, request changes, and manage shifts.
                    </p>
                </div>

                <!-- RIGHT TABS -->
                <div class="w-full lg:w-auto">
                    <nav
                        class="grid grid-cols-4 sm:inline-flex
                               items-center gap-1 sm:gap-2
                               w-full sm:min-w-max
                               bg-white/10 backdrop-blur-xl
                               border border-white/15
                               rounded-2xl
                               p-1.5 sm:p-2"
                    >
                        <Link
                            v-for="t in tabs"
                            :key="t.id"
                            :href="
                                t.id === 'leave'
                                    ? route('leave.dashboard', { tab: 'restricted' })
                                    : t.id === 'requests'
                                        ? route('attendance.requests.index')
                                        : route('employee.attendance.hub', { tab: t.id })
                            "
                            class="relative flex flex-col sm:inline-flex
                                   sm:flex-row items-center justify-center
                                   gap-1 sm:gap-2
                                   px-2 sm:px-5 py-2.5 sm:py-3
                                   rounded-xl
                                   text-[9px] sm:text-xs
                                   font-black uppercase tracking-wide
                                   transition-all duration-300
                                   text-center"
                            :class="
                                activeTab === t.id
                                    ? 'bg-white text-[#5b35d6] shadow-xl'
                                    : 'text-white/75 hover:text-white hover:bg-white/10'
                            "
                        >
                            <component
                                :is="t.icon"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0"
                            />

                            <span class="leading-tight">
                                {{ t.label }}
                            </span>

                            <!-- Active Ring -->
                            <span
                                v-if="activeTab === t.id"
                                class="absolute inset-0 rounded-xl
                                       ring-1 ring-white/40"
                            ></span>
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>