<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    SparklesIcon,
    CpuChipIcon,
    WrenchScrewdriverIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    AdjustmentsHorizontalIcon,
    InboxStackIcon,
    PlusCircleIcon
} from '@heroicons/vue/24/solid';

defineProps({
    categories: Array
});

const form = useForm({
    category_id: '',
    reason: '',
    priority: 'Normal',
    is_custom: false,
    custom_name: '',
    custom_qty: 1,
    custom_details: ''
});

const submit = () => {
    form.post(route('employee.assets.store'));
};
</script>

<template>
    <MainLayout>
        <div class="max-w-2xl mx-auto space-y-8 p-4 bg-slate-50 min-h-screen">
            <!-- Header Section -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-5">
                <div>
                    <Link :href="route('employee.assets.index')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors mb-3">
                        <ArrowLeftIcon class="w-3 h-3" /> Back to Inventory
                    </Link>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Request New Resource</h1>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Submit a request to acquire hardware, software, or tools for execution.</p>
                </div>
            </div>

            <!-- Form Body -->
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm relative overflow-hidden">
                <!-- Decorative Glows -->
                <div class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500/5 rounded-full blur-[100px] pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none"></div>

                <form @submit.prevent="submit" class="relative z-10 space-y-7 text-left">
                    
                    <!-- Toggle Switch for Custom Asset -->
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between shadow-sm">
                        <div class="flex gap-4 items-center">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600">
                                <SparklesIcon class="w-5 h-5 text-indigo-500" />
                            </div>
                            <div>
                                <label class="text-xs font-black uppercase tracking-wider text-slate-900">Is Custom Asset?</label>
                                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Check this if the item does not fit standard categories.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_custom" class="sr-only peer" />
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-3">
                        <label class="px-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Request Priority</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center justify-center gap-3 h-12 rounded-xl border cursor-pointer font-bold text-xs uppercase tracking-wider transition-all"
                                :class="form.priority === 'Normal' ? 'bg-indigo-50/50 border-indigo-200 text-indigo-700 shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300'">
                                <input type="radio" v-model="form.priority" value="Normal" class="sr-only" />
                                <CheckCircleIcon class="w-4 h-4" v-if="form.priority === 'Normal'" />
                                Normal
                            </label>
                            <label class="flex items-center justify-center gap-3 h-12 rounded-xl border cursor-pointer font-bold text-xs uppercase tracking-wider transition-all"
                                :class="form.priority === 'High' ? 'bg-rose-50/50 border-rose-200 text-rose-700 shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:border-rose-300'">
                                <input type="radio" v-model="form.priority" value="High" class="sr-only" />
                                <CheckCircleIcon class="w-4 h-4 text-rose-600" v-if="form.priority === 'High'" />
                                Urgent / High
                            </label>
                        </div>
                        <p v-if="form.errors.priority" class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ form.errors.priority }}</p>
                    </div>

                    <!-- CONDITIONAL LAYOUTS -->
                    <Transition name="fade" mode="out-in">
                        <!-- STANDARD ASSET REQUEST FIELDS -->
                        <div v-if="!form.is_custom" class="space-y-6">
                            <!-- Category Selection -->
                            <div class="space-y-3">
                                <label class="px-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Asset Category / Class</label>
                                <div class="relative">
                                    <select v-model="form.category_id" class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-4 text-xs font-bold text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                        <option value="">Select Category...</option>
                                        <option v-for="cat in categories" :value="cat.id" :key="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.category_id" class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ form.errors.category_id }}</p>
                            </div>

                            <!-- Justification Reason -->
                            <div class="space-y-3">
                                <label class="px-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Business Justification</label>
                                <textarea v-model="form.reason" rows="4" placeholder="Provide detailed justification. E.g. Current laptop suffers from screen flickering; required for project release..." class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-xs font-bold text-slate-700 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 resize-none transition-all"></textarea>
                                <p v-if="form.errors.reason" class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ form.errors.reason }}</p>
                            </div>
                        </div>

                        <!-- CUSTOM ASSET REQUEST FIELDS -->
                        <div v-else class="space-y-6">
                            <!-- Custom Name -->
                            <div class="space-y-3">
                                <label class="px-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Custom Asset Name</label>
                                <input v-model="form.custom_name" type="text" placeholder="E.g. Apple iPad Pro 12.9 (M4)" class="w-full h-12 rounded-xl border border-slate-200 bg-slate-50/50 px-4 text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                <p v-if="form.errors.custom_name" class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ form.errors.custom_name }}</p>
                            </div>

                            <!-- Custom Quantity -->
                            <div class="space-y-3">
                                <label class="px-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Requested Quantity</label>
                                <input v-model="form.custom_qty" type="number" min="1" placeholder="1" class="w-full h-12 rounded-xl border border-slate-200 bg-slate-50/50 px-4 text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                <p v-if="form.errors.custom_qty" class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ form.errors.custom_qty }}</p>
                            </div>

                            <!-- Custom Specifications / Reason -->
                            <div class="space-y-3">
                                <label class="px-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Custom Specifications & Purpose</label>
                                <textarea v-model="form.custom_details" rows="4" placeholder="Detail the exact specifications, approximate price, and workflow requirement..." class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-xs font-bold text-slate-700 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 resize-none transition-all"></textarea>
                                <p v-if="form.errors.custom_details" class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ form.errors.custom_details }}</p>
                            </div>
                        </div>
                    </Transition>

                    <!-- Footer Action buttons -->
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-5 border-t border-slate-100">
                        <Link :href="route('employee.assets.index')" class="h-11 px-6 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all flex items-center justify-center">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing" class="h-11 px-8 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center justify-center gap-2">
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit Request</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
