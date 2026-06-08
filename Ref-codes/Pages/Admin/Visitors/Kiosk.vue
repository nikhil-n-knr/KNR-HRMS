<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CameraIcon, CheckBadgeIcon } from '@heroicons/vue/24/solid';
import WebcamCapture from '@/Components/Visitors/WebcamCapture.vue';

const props = defineProps({
    employees: Array,
    purposes: Array
});

const showWebcam = ref(false);
const successMessage = ref('');
const showSuccess = ref(false);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    host_id: '',
    purpose_id: props.purposes[0]?.id || 1,
    photo: null,
    expected_duration: 60,
    nda_signed: false
});

const selectedPurpose = computed(() => {
    return props.purposes.find(p => p.id === form.purpose_id) || props.purposes[0];
});

const isFieldVisible = (field) => {
    const purpose = selectedPurpose.value;
    const config = JSON.parse(purpose?.form_config || '{}');
    return config[field] !== 'hidden';
};

const isFieldRequired = (field) => {
    const purpose = selectedPurpose.value;
    const config = JSON.parse(purpose?.form_config || '{}');
    return config[field] === 'required';
};

const submit = () => {
    form.post(route('visitors.check-in'), {
        onSuccess: (page) => {
            if (page.props.flash.print_id) {
                // Open badge print popup which triggers native window.print()
                window.open(route('visitors.print', page.props.flash.print_id), '_blank', 'width=400,height=600');
            }
            showSuccess.value = true;
            successMessage.value = 'Welcome! Your badge is printing, please wait...';
            form.reset();
            setTimeout(() => {
                showSuccess.value = false;
            }, 6000);
        }
    });
};
</script>

<template>
    <Head title="Self Check-In Kiosk" />
    
    <div class="min-h-screen bg-slate-950 flex items-center justify-center p-6 md:p-12 font-sans relative overflow-hidden">
        <!-- Background Gradients -->
        <div class="absolute top-0 -left-4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 -right-4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>

        <div class="w-full max-w-5xl bg-slate-900 border border-slate-800 rounded-[3rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[650px] relative z-10">
            
            <!-- Left Panel: Brand & Info -->
            <div class="w-full lg:w-5/12 bg-slate-900/60 p-8 md:p-12 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg">
                        KNR
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-white uppercase tracking-wider">KNR Hub</h1>
                        <p class="text-xs text-indigo-400 font-bold uppercase tracking-widest">Digital Reception</p>
                    </div>
                </div>

                <div class="my-8 space-y-4">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white leading-tight">
                        Self-Service<br/>Visitor Kiosk
                    </h2>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Please enter your details, take a photo identity capture, and agree to our NDAs. A printed access badge will be generated for you automatically.
                    </p>
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    Powered by Antigravity Kiosk Integration
                </div>
            </div>

            <!-- Right Panel: Smart Form -->
            <div class="w-full lg:w-7/12 p-8 md:p-12 flex flex-col justify-center bg-slate-900">
                <div v-if="showSuccess" class="text-center py-12 space-y-4">
                    <div class="w-20 h-20 bg-green-500/10 text-green-500 rounded-full flex items-center justify-center mx-auto animate-bounce border border-green-500/20">
                        <CheckBadgeIcon class="w-12 h-12" />
                    </div>
                    <h3 class="text-2xl font-black text-white">Check-In Successful!</h3>
                    <p class="text-slate-400 text-sm max-w-sm mx-auto leading-relaxed">{{ successMessage }}</p>
                </div>

                <form v-else @submit.prevent="submit" class="space-y-6">
                    <!-- Photo Capture Section -->
                    <div class="flex items-center gap-6 bg-slate-950 p-4 rounded-[2rem] border border-slate-800">
                        <div class="relative w-24 h-24 rounded-[1.5rem] bg-slate-900 border border-slate-800 overflow-hidden flex items-center justify-center flex-shrink-0">
                            <img v-if="form.photo" :src="form.photo" class="w-full h-full object-cover">
                            <CameraIcon v-else class="w-8 h-8 text-slate-600" />
                        </div>
                        <div>
                            <button type="button" @click="showWebcam = true" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-colors">
                                {{ form.photo ? 'Retake Photo' : 'Capture Photo' }}
                            </button>
                            <p class="text-slate-500 text-xs mt-1.5 font-medium">A real-time photo identity is required for security.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Visitor Name</label>
                            <input v-model="form.name" type="text" class="w-full bg-slate-950 border-slate-800 text-white text-base px-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required placeholder="John Doe">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Phone Number</label>
                            <input v-model="form.phone" type="tel" class="w-full bg-slate-950 border-slate-800 text-white text-base px-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" :required="isFieldRequired('phone')" placeholder="+91 98765 43210">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Reason for Visit</label>
                            <select v-model="form.purpose_id" class="w-full bg-slate-950 border-slate-800 text-white text-base px-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="p in purposes" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Whom to Meet</label>
                            <select v-model="form.host_id" class="w-full bg-slate-950 border-slate-800 text-white text-base px-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="" disabled>Select Employee...</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="isFieldVisible('email')">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                        <input v-model="form.email" type="email" class="w-full bg-slate-950 border-slate-800 text-white text-base px-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" :required="isFieldRequired('email')" placeholder="email@example.com">
                    </div>

                    <!-- NDA Terms Checkbox -->
                    <label class="flex items-start p-4 border border-slate-800 bg-slate-950/40 rounded-xl cursor-pointer hover:bg-slate-900/60 transition-colors select-none">
                        <input v-model="form.nda_signed" type="checkbox" class="w-5 h-5 text-indigo-600 rounded bg-slate-950 border-slate-800 focus:ring-indigo-500 focus:ring-offset-slate-900 mt-0.5" required>
                        <span class="ml-4 text-xs font-medium text-slate-400 leading-normal">
                            I hereby sign and agree to the <span class="text-indigo-400 font-bold underline">NDA Agreement, Safety Code & Policy Guidelines</span>.
                        </span>
                    </label>

                    <button type="submit" :disabled="form.processing" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-base font-black py-4 rounded-xl shadow-lg hover:shadow-indigo-500/20 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                        <CheckBadgeIcon class="w-6 h-6 text-white"/>
                        {{ form.processing ? 'Registering Guest...' : 'Check In & Print Badge' }}
                    </button>
                </form>
            </div>
        </div>

        <WebcamCapture 
            v-if="showWebcam" 
            @close="showWebcam = false" 
            @captured="(img) => { form.photo = img; showWebcam = false; }" 
        />
    </div>
</template>
