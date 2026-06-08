<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
    ClockIcon,
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    UserIcon
} from '@heroicons/vue/24/outline'; // v2
import { computed } from 'vue';
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";


defineOptions({ layout: MainLayout });

const props = defineProps({
    custody_items: Array
});

// Computed Groups
const highRiskItems = computed(() => props.custody_items.filter(i => i.is_high_risk));
const normalItems = computed(() => props.custody_items.filter(i => !i.is_high_risk));

const formatDuration = (hours) => {
    if (hours < 1) return '< 1 Hour';
    if (hours < 24) return `${hours} Hours`;
    const days = Math.floor(hours / 24);
    return `${days} Days ${hours % 24} Hours`;
};
</script>

<template>

    <Head title="Chain of Custody" />

    <GradientHeroHeader kicker="" title="Chain of Custody"
        subtitle="Real-time tracking of physical documents In the Wild.">
    </GradientHeroHeader>

    <div class="p-6" >
        <!-- HIGH RISK SECTION -->
        <div v-if="highRiskItems.length > 0" class="mb-10 animate-pulse-slow relative z-10">
            <div class="flex items-center gap-2 mb-4 text-red-600">
                <ExclamationTriangleIcon class="h-6 w-6" />
                <h2 class="text-lg font-bold uppercase tracking-wider">High Risk Alerts (> 24 Hours)</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="item in highRiskItems" :key="item.id"
                    class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2 opacity-10">
                        <ClockIcon class="h-24 w-24 text-indigo-900" />
                    </div>

                    <div class="relative z-10">
                        <span
                            class="inline-block px-3 py-1 bg-rose-50 text-rose-700 border border-rose-100 text-[10px] font-black uppercase tracking-widest rounded-lg mb-3">
                            OUT {{ formatDuration(item.duration_hours) }}
                        </span>
                        <h3 class="font-black text-slate-900 text-lg mb-1 uppercase tracking-tight">{{
                            item.document_type }}</h3>

                        <div class="flex items-center gap-2 text-sm text-slate-600 mb-4">
                            <UserIcon class="h-4 w-4 text-slate-400" />
                            <span class="font-bold uppercase tracking-widest text-[11px]">{{ item.user_name }}</span>
                        </div>

                        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-4">
                            Origin: {{ item.location_name }} ({{ item.container_ref }})
                        </div>

                        <Link :href="route('admin.physical-documents.index')"
                            class="block w-full text-center h-10 px-4 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center justify-center">
                            Investigate / Return
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- NORMAL CUSTODY SECTION -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/50 shadow-xl overflow-hidden relative">
            <div class="px-6 py-4 border-b border-slate-100 bg-white/80 flex justify-between items-center">
                <h3 class="font-black text-slate-700 uppercase">Active Custody Log</h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ normalItems.length }}
                    Items Checked Out</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur-sm border-b border-slate-200 shadow-[0_1px_0_rgba(148,163,184,0.35)]">
                        <tr>
                            <th
                                class="px-6 py-3 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-center">
                                Document</th>
                            <th
                                class="px-6 py-3 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-center">
                                Custodian</th>
                            <th
                                class="px-6 py-3 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-center">
                                Time Out</th>
                            <th
                                class="px-6 py-3 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-center">
                                Home Location</th>
                            <th
                                class="px-6 py-3 text-[11px] font-black text-slate-500 uppercase tracking-[0.22em] text-center">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in normalItems" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-2 font-black text-slate-900 uppercase tracking-tight">{{
                                item.document_type }}</td>
                            <td class="px-4 py-2 flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-sky-50 border border-sky-100 flex items-center justify-center text-[10px] text-sky-700 font-black">
                                    {{ item.user_name.charAt(0) }}
                                </div>
                                <span class="font-bold uppercase tracking-widest text-[11px] text-slate-700">{{
                                    item.user_name }}</span>
                            </td>
                            <td class="px-4 py-2 text-slate-500 font-semibold">
                                {{ formatDuration(item.duration_hours) }}
                            </td>
                            <td class="px-4 py-2 text-slate-500 font-semibold">
                                {{ item.location_name }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <Link :href="route('admin.physical-documents.index')"
                                    class="text-indigo-600 hover:text-indigo-800 font-black text-[10px] uppercase tracking-widest">
                                    Return</Link>
                            </td>
                        </tr>
                        <tr v-if="normalItems.length === 0 && highRiskItems.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <ShieldCheckIcon class="h-12 w-12 mx-auto text-emerald-100 mb-2" />
                                All documents are secure in storage. No active checkouts.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>

<style scoped>
@keyframes pulse-slow {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.95;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 3s infinite;
}
</style>
