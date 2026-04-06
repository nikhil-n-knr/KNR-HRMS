<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckBadgeIcon, ClockIcon, MapPinIcon, QrCodeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    pass: Object
});

const confirmed = ref(false);
const form = useForm({});

const submit = () => {
    form.post(route('visitors.guest.confirm', props.pass.id), {
        onSuccess: () => {
            confirmed.value = true;
        }
    });
};
</script>

<template>
    <Head title="Guest Pre-Checkin" />

    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden border border-slate-100">
            <!-- Header Banner -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 p-8 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-3xl mx-auto flex items-center justify-center mb-4 border border-white/30 shadow-xl">
                    <CheckBadgeIcon class="w-10 h-10 text-white" />
                </div>
                <h1 class="text-white font-black text-2xl uppercase tracking-tighter">{{ confirmed ? 'Welcome Onboard!' : 'Secure Entry Pass' }}</h1>
                <p class="text-indigo-100 text-sm font-bold uppercase tracking-widest mt-1">Resource Management Portal</p>
            </div>

            <div class="p-8">
                <div v-if="!confirmed">
                    <div class="mb-8">
                        <h2 class="text-slate-400 font-black text-sm uppercase tracking-widest mb-2">Event Invitation</h2>
                        <h3 class="text-slate-900 font-black text-xl leading-tight">{{ pass.event?.title || 'Campus Visit' }}</h3>
                        
                        <div class="flex items-center gap-4 mt-6">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-xl">
                                <MapPinIcon class="w-3.5 h-3.5 text-indigo-50" />
                                <span class="text-sm font-black text-slate-600 uppercase">{{ pass.event?.location || 'Main Lobby' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-xl">
                                <ClockIcon class="w-3.5 h-3.5 text-indigo-50" />
                                <span class="text-sm font-black text-slate-600 uppercase">{{ new Date(pass.visit_date).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-50/50 p-6 rounded-3xl border border-indigo-100 mb-8">
                        <p class="text-base text-slate-500 font-medium leading-relaxed">
                            Hello <span class="font-black text-indigo-600">{{ pass.visitor.name }}</span>, we have your credentials ready. Confirming your arrival will notify your host and prepare your security badge.
                        </p>
                    </div>

                    <button @click="submit" :disabled="form.processing" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-slate-200 hover:bg-black transition-all transform active:scale-95 disabled:opacity-50">
                        {{ form.processing ? 'Syncing Gateway...' : 'Confirm One-Click Entry' }}
                    </button>
                    
                    <p class="text-center text-sm text-slate-400 font-bold uppercase mt-6 tracking-widest">Powered by HRMS Intelligent Access</p>
                </div>

                <div v-else class="text-center animate-fade-in">
                    <div class="bg-green-50 p-6 rounded-3xl border border-green-100 mb-8">
                        <h3 class="text-green-700 font-black text-lg mb-2">Check-in Complete!</h3>
                        <p class="text-sm text-green-600 font-black uppercase tracking-widest leading-loose">
                            Your host has been notified.<br>Please proceed to the main gate.<br>Your badge is printing...
                        </p>
                    </div>

                    <div class="w-32 h-32 bg-white border-4 border-slate-50 rounded-3xl mx-auto flex items-center justify-center mb-8 shadow-sm">
                        <QrCodeIcon class="w-20 h-20 text-slate-200" />
                    </div>

                    <button @click="window.close()" class="text-slate-400 font-black text-sm uppercase tracking-widest hover:text-indigo-600 transition-colors">
                        Close This Portal
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
