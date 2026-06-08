<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";

import {
    MagnifyingGlassIcon,
    ArrowLeftIcon,
    UserCircleIcon,
    ArrowPathIcon,
    SparklesIcon,
    CheckBadgeIcon,
    UserPlusIcon,
    UserIcon,
    CpuChipIcon,
    ShieldCheckIcon,
    FingerPrintIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    assets: Array,
    users: Array
});

const form = useForm({
    asset_ids: [],
    user_id: ''
});

const search = ref('');

const filteredAssets = computed(() => {
    const source = props.assets ?? [];
    if (!search.value) return source;
    const q = search.value.toLowerCase();
    return source.filter(a =>
        a.name.toLowerCase().includes(q) ||
        (a.serial_number && a.serial_number.toLowerCase().includes(q))
    );
});

const toggleSelectAll = () => {
    if (form.asset_ids.length === filteredAssets.value.length && filteredAssets.value.length > 0) {
        form.asset_ids = [];
    } else {
        form.asset_ids = filteredAssets.value.map(a => a.id);
    }
};

const submit = () => {
    if (!form.user_id || form.asset_ids.length === 0) return;
    form.post(route('admin.assets.bulk-assign.process'));
};

const selectedUser = computed(() => {
    return (props.users ?? []).find(u => u.id == form.user_id);
});
</script>

<template>

    <Head title="Box Handover Terminal" />

    <GradientHeroHeader kicker="" title="Handover Terminal" subtitle="MASS PROVISIONING SYSTEM_v4.0">
        <template #right>
            <div
                class="flex flex-wrap lg:flex-nowrap items-center gap-2 md:gap-3 w-full lg:w-auto justify-start lg:justify-end">
                <div
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center min-w-[84px] md:min-w-[96px]">
                    <p class="text-[7px] md:text-[8px] font-bold text-slate-400 uppercase tracking-[0.3em] mb-1">UNITS
                    </p>
                    <p class="text-lg md:text-xl font-black text-slate-900 leading-none tabular-nums">{{
                        form.asset_ids.length }}</p>
                </div>
                <div
                    class="px-3 py-2 bg-indigo-50 border border-indigo-100 rounded-xl text-center min-w-[84px] md:min-w-[96px]">
                    <p class="text-[7px] md:text-[8px] font-bold text-indigo-400 uppercase tracking-[0.3em] mb-1">ASSETS
                    </p>
                    <p class="text-lg md:text-xl font-black text-indigo-700 leading-none tabular-nums">{{ (assets ??
                        []).length }}</p>
                </div>
                <div
                    class="px-3 py-2 bg-cyan-50 border border-cyan-100 rounded-xl text-center min-w-[84px] md:min-w-[96px]">
                    <p class="text-[7px] md:text-[8px] font-bold text-cyan-500 uppercase tracking-[0.3em] mb-1">USERS
                    </p>
                    <p class="text-lg md:text-xl font-black text-cyan-700 leading-none tabular-nums">{{ (users ??
                        []).length }}</p>
                </div>
            </div>
        </template>
    </GradientHeroHeader>

    <div class="p-6">
        <!-- AI Grid Background -->

        <div
            class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 min-h-0 relative z-10 overflow-hidden">

            <!-- Resource Matrix (Selection) -->
            <div
                class="bg-white/95 backdrop-blur-xl rounded-[1.5rem] border border-slate-200 p-4 md:p-6 flex flex-col min-h-0 overflow-hidden shadow-sm relative group/matrix">
            
                <div class="relative z-10 text-left mb-5">
                    <div class="flex items-start justify-between gap-4 mb-7">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400 mb-1.5">Selection
                                Matrix</p>
                            <h2 class="text-lg md:text-xl font-black uppercase tracking-tight text-slate-950">Choose
                                Assets to Handover</h2>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div class="relative w-full md:flex-1 group/search">
                            <MagnifyingGlassIcon
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within/search:text-indigo-600 transition-all duration-500" />
                            <input v-model="search" type="text" placeholder="SEARCH ASSETS BY NAME OR SERIAL..."
                                class="w-full h-13 md:h-13 bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 text-sm font-bold text-slate-900 uppercase tracking-tight outline-none appearance-none focus:outline-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 shadow-sm transition-all placeholder:text-slate-300">
                        </div>
                        <button @click="toggleSelectAll"
                            class="h-12 px-4 bg-indigo-600 text-white rounded-xl text-[10px] text-center font-bold uppercase tracking-[0.1em] hover:bg-indigo-600 transition-all active:scale-95 shadow-lg shrink-0 group/all border border-slate-800 cursor-pointer inline-flex items-center justify-center cursor-pointer">
                            <div class="flex items-center justify-center gap-2.5 leading-none">
                                <SparklesIcon
                                    class="w-4 h-4 text-indigo-400 text-center group-hover/all:rotate-90 transition-transform duration-700" />
                                {{ form.asset_ids.length === filteredAssets.length && filteredAssets.length > 0 ?
                                    'CLEAR STAGE' : 'SELECT ALL' }}
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Matrix Grid -->
                <div class="flex-1 overflow-y-auto pr-1 md:pr-2 no-scrollbar relative z-10 text-left">
                    <div v-if="filteredAssets.length"
                        class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4 md:gap-5 pb-6">
                        <div v-for="asset in filteredAssets" :key="asset.id"
                            @click="form.asset_ids.includes(asset.id) ? form.asset_ids.splice(form.asset_ids.indexOf(asset.id), 1) : form.asset_ids.push(asset.id)"
                            class="group/card relative bg-white border border-slate-200 rounded-[1.75rem] p-5 md:p-6 cursor-pointer transition-all duration-500 shadow-sm hover:shadow-xl hover:-translate-y-1 overflow-hidden text-left"
                            :class="form.asset_ids.includes(asset.id) ? 'border-indigo-600 ring-4 ring-indigo-500/10 bg-indigo-50/30' : 'hover:border-indigo-200'">
                            <div v-if="form.asset_ids.includes(asset.id)"
                                class="absolute top-0 right-0 w-14 h-14 bg-indigo-600 rounded-bl-3xl flex items-start justify-end p-3 text-white animate-in slide-in-from-top-right-full duration-500 shadow-lg">
                                <CheckBadgeIcon class="w-5 h-5" />
                            </div>

                            <div class="flex flex-col gap-5 relative z-10">
                                <div class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center transition-all duration-700 shadow-sm group-hover/card:bg-slate-900 group-hover/card:text-indigo-400 shrink-0"
                                    :class="form.asset_ids.includes(asset.id) ? 'bg-slate-900 text-indigo-400' : 'text-slate-300'">
                                    <CpuChipIcon class="w-5 h-5 md:w-6 md:h-6" />
                                </div>
                                <div class="text-left">
                                    <h5
                                        class="text-base md:text-lg font-black text-slate-900 uppercase tracking-tight leading-tight group-hover/card:text-indigo-600 transition-colors">
                                        {{ asset.name }}</h5>
                                    <div class="flex items-center gap-2 mt-2">
                                        <FingerPrintIcon class="w-3 h-3 text-slate-400" />
                                        <p
                                            class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">
                                            {{ asset.serial_number || 'BULK STOCK' }}</p>
                                    </div>
                                    <div class="mt-4 flex items-center gap-2">
                                        <div
                                            class="px-3 py-1 bg-slate-100 text-[8px] font-bold text-slate-500 uppercase tracking-widest rounded-full transition-all group-hover/card:bg-indigo-600 group-hover/card:text-white">
                                            {{ asset.category?.name || 'NODE' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="min-h-[280px] flex flex-col items-center justify-center text-center py-16">
                        <div
                            class="w-20 h-20 rounded-3xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 mb-6 shadow-sm">
                            <MagnifyingGlassIcon class="w-8 h-8" />
                        </div>
                        <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">No assets found</h4>
                        <p
                            class="max-w-sm text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] leading-loose">
                            Try a different search term or clear the filter to see every available resource.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recipient Command Panel -->
            <div
                class="lg:sticky lg:top-6 self-start bg-white/95 backdrop-blur-xl rounded-[1.5rem] p-4 md:p-6 shadow-sm flex flex-col relative overflow-hidden group/panel border border-slate-200">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/30 via-transparent to-transparent pointer-events-none">
                </div>

                <h3
                    class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.1em] mb-8 flex items-center gap-3 border-b border-slate-100 pb-5 relative z-10">
                    <UserPlusIcon class="w-6 h-6 text-indigo-500" />
                    Target Recipient Node
                </h3>

                <div class="space-y-8 flex-1 relative z-10 text-left">
                    <div class="space-y-4 group/select">
                        <label
                            class="px-2 text-[10px] font-bold text-slate-500 uppercase leading-none group-focus-within/select:text-indigo-500 tracking-[0.1em] transition-colors text-left block">Select
                            Operational Lead</label>
                        <div class="relative flex items-center">
                            <UserIcon
                                class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within/select:text-indigo-500 transition-colors" />
                            <select v-model="form.user_id"
                                class="w-full h-12 md:h-12 bg-slate-50 border border-slate-200 rounded-2xl pl-12 md:pl-12 text-sm md:text-base font-black text-slate-900 uppercase tracking-[0.1em] outline-none appearance-none focus:outline-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 transition-all shadow-sm cursor-pointer">
                                <option value="">SELECT_IDENTITY...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name.toUpperCase()
                                }}</option>
                            </select>
                        </div>
                    </div>

                    <Transition name="fade-up-complex">
                        <div v-if="selectedUser"
                            class="p-6 md:p-8 bg-slate-50 border border-slate-200 rounded-[1.75rem] flex flex-col items-center text-center animate-in zoom-in-95 duration-700 group/user shadow-sm scale-[1.01] hover:bg-white transition-all">
                            <div
                                class="w-20 h-20 md:w-24 md:h-24 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-3xl font-black text-indigo-600 shadow-md mb-6 md:mb-8 group-hover/user:scale-110 transition-transform duration-700 overflow-hidden">
                                <img v-if="selectedUser.profile_photo_url" :src="selectedUser.profile_photo_url"
                                    class="w-full h-full object-cover">
                                <span v-else>{{ selectedUser.name.charAt(0) }}</span>
                            </div>
                            <h4
                                class="text-xl md:text-2xl font-black text-slate-900 uppercase tracking-tight leading-none group-hover/user:text-indigo-600 transition-colors">
                                {{ selectedUser.name }}</h4>
                            <p
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-4 bg-white px-4 py-1.5 rounded-full border border-slate-100 shadow-sm">
                                VERIFIED_RECIPIENT_LIAISON</p>
                        </div>
                        <div v-else
                            class="min-h-[280px] flex flex-col items-center justify-center text-center opacity-35">
                            <div
                                class="w-20 h-20 border-2 border-slate-200 border-dashed rounded-3xl flex items-center justify-center mb-6 bg-slate-50">
                                <UserCircleIcon class="w-10 h-10 text-slate-300 animate-pulse" />
                            </div>
                            <p
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em] px-8 leading-loose">
                                Establish a recipient node to initialize the handover protocol.</p>
                        </div>
                    </Transition>
                </div>

                <div class="pt-8 md:pt-10 relative z-10">
                    <button @click="submit" :disabled="!form.user_id || form.asset_ids.length === 0 || form.processing"
                        class="w-fit mx-auto px-4 md:px-6 h-14 md:h-15 bg-indigo-500 text-white rounded-2xl text-[10px] font-bold uppercase tracking-[0.1em] shadow-lg hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 md:gap-4 cursor-pointer active:scale-95 disabled:opacity-20 disabled:grayscale group/submit border border-slate-800">
                        <ArrowPathIcon v-if="form.processing" class="w-5 h-5 md:w-6 md:h-6 animate-spin" />
                        <ShieldCheckIcon v-else
                            class="w-5 h-5 md:w-6 md:h-6 text-indigo-400 group-hover/submit:scale-125 transition-all" />
                        <span>{{ form.processing ? 'SYNCING_PROTOCOL...' : 'COMMIT_HANDOVER' }}</span>
                    </button>
                    <p v-if="form.asset_ids.length > 0"
                        class="text-center text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-4">
                        PROVISIONING {{ form.asset_ids.length }} STAGED NODES</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.fade-up-complex-enter-active {
    transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-up-complex-enter-from {
    opacity: 0;
    transform: translateY(30px) scale(0.9);
}
</style>
