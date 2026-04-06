<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import WebcamCapture from '@/Components/Visitors/WebcamCapture.vue';
import { CameraIcon, CheckBadgeIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    pass: Object,
    visitor: Object,
    host: Object,
    purpose: Object,
    config: Object
});

const showCamera = ref(false);

const form = useForm({
    photo_path: props.visitor.photo_path || '',
    company: props.visitor.company || '',
    phone: props.visitor.phone || '',
    nda_signature: '',
    nda_signed: false
});

const onPhotoCaptured = (base64) => {
    form.photo_path = base64;
    showCamera.value = false;
};

const submit = () => {
    form.post(route('visitors.guest.update', props.pass.id));
};
</script>

<template>
    <Head title="Guest Pre-Registration" />
    
    <div class="min-h-screen bg-gray-50 flex flex-col items-center py-12 px-4">
        <div class="max-w-2xl w-full space-y-8">
            <!-- Branding -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-600 rounded-3xl shadow-xl mb-6 text-white text-3xl font-black">
                    V
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Welcome to the Hub</h1>
                <p class="text-gray-500 mt-2 font-medium">Please complete your pre-registration for your visit with <b>{{ host.name }}</b></p>
            </div>

            <div class="bg-white rounded-[40px] shadow-2xl shadow-indigo-100 border border-gray-100 p-8 md:p-12 space-y-10">
                <!-- Photo Section -->
                <section class="space-y-4">
                    <h3 class="text-lg font-black text-gray-800 flex items-center gap-2">
                        <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                        Security Identification
                    </h3>
                    <div class="flex flex-col items-center">
                        <div v-if="form.photo_path" class="relative group">
                            <img :src="form.photo_path" class="w-48 h-48 rounded-[40px] object-cover border-4 border-white shadow-xl">
                            <button @click="showCamera = true" class="absolute inset-0 bg-black/40 rounded-[40px] opacity-0 group-hover:opacity-100 flex items-center justify-center text-white font-bold transition-all">
                                Change Photo
                            </button>
                        </div>
                        <button v-else @click="showCamera = true" class="w-48 h-48 bg-gray-50 border-2 border-dashed border-gray-200 rounded-[40px] flex flex-col items-center justify-center gap-3 hover:border-indigo-300 hover:bg-indigo-50 transition-all text-gray-400 hover:text-indigo-600">
                            <CameraIcon class="w-10 h-10" />
                            <span class="text-xs font-black uppercase tracking-widest">Capture Photo</span>
                        </button>
                        <p class="text-xs text-gray-400 mt-4 text-center">Your photo will be used to generate your security badge upon arrival.</p>
                    </div>
                </section>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Dynamic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-sm uppercase font-black text-gray-400 tracking-widest ml-1">Phone Number</label>
                            <input v-model="form.phone" type="tel" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-4 focus:ring-indigo-100 font-bold text-gray-700" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm uppercase font-black text-gray-400 tracking-widest ml-1">Company / Organization</label>
                            <input v-model="form.company" type="text" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-4 focus:ring-indigo-100 font-bold text-gray-700" placeholder="Acme Inc.">
                        </div>
                    </div>

                    <!-- NDA Section -->
                    <div class="space-y-4">
                         <h3 class="text-lg font-black text-gray-800 flex items-center gap-2">
                            <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                            Policies & NDA
                        </h3>
                        <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100">
                            <div class="h-48 overflow-y-auto text-sm text-gray-600 space-y-4 scrollbar-hide pr-2">
                                <p class="font-bold">Non-Disclosure Agreement:</p>
                                <p>By checking the box below, you agree to keep all confidential information witnessed during your visit private. You also agree to follow all on-site safety protocols and data protection policies as mandated by the security department.</p>
                                <p>Unauthorized photography or recording inside Restricted Zone B is strictly prohibited without written consent from your host.</p>
                            </div>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative">
                                        <input v-model="form.nda_signed" type="checkbox" class="peer hidden" id="nda-check">
                                        <div class="w-8 h-8 rounded-xl border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all flex items-center justify-center">
                                            <ShieldCheckIcon v-if="form.nda_signed" class="w-5 h-5 text-white" />
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-600 group-hover:text-indigo-600 transition-colors">I have read and agree to the Visitor Safety & NDA Policy.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button :disabled="form.processing || !form.nda_signed" type="submit" class="w-full py-5 bg-gray-900 text-white rounded-3xl font-black text-lg flex items-center justify-center gap-3 hover:scale-[1.02] active:scale-95 transition-all shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                        <CheckBadgeIcon class="w-6 h-6 text-indigo-400" />
                        Complete Registration
                    </button>
                </form>
            </div>
        </div>

        <WebcamCapture v-if="showCamera" @captured="onPhotoCaptured" @close="showCamera = false" />
    </div>
</template>
