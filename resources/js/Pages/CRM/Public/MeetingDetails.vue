<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    meeting: Object
});

const rsvpStatus = ref('');

const submitRsvp = (status) => {
    const attendee = props.meeting.attendees.find(a => a.email); // Simplified, usually based on token
    if (!attendee) return;

    router.post(route('crm.meetings.public.rsvp', { uuid: props.meeting.uuid }), {
        email: attendee.email,
        status: status
    }, {
        onSuccess: () => rsvpStatus.value = status
    });
};
</script>

<template>
    <Head title="Meeting Details" />
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6 font-inter">
        <div class="max-w-2xl w-full bg-white rounded-[40px] shadow-2xl border border-white overflow-hidden animate-in fade-in zoom-in duration-700">
            <!-- Hero Header -->
            <div class="p-12 bg-indigo-600 text-white relative overflow-hidden">
                <i class="fas fa-calendar-check absolute -bottom-10 -right-10 text-[200px] text-white/10 rotate-12"></i>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] mb-4 opacity-70">Official Session Invitation</p>
                <h1 class="text-4xl font-black tracking-tight leading-tight">{{ meeting.title }}</h1>
            </div>

            <!-- Content Area -->
            <div class="p-12 space-y-10">
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">When</p>
                        <p class="text-lg font-black text-gray-900 leading-none">{{ new Date(meeting.start_time).toLocaleString() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Host</p>
                        <p class="text-lg font-black text-gray-900 leading-none">{{ meeting.employee?.first_name }} {{ meeting.employee?.last_name }}</p>
                    </div>
                </div>

                <div v-if="meeting.link" class="p-8 bg-indigo-50/50 rounded-3xl border border-indigo-100 flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-xl shadow-indigo-100">
                            <i class="fas fa-video"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-indigo-600/70 uppercase tracking-widest leading-none mb-1">Live Stream Active</p>
                            <p class="text-sm font-black text-indigo-900 tracking-tight">Virtual Venue Ready</p>
                        </div>
                    </div>
                    <a :href="meeting.link" target="_blank" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100">Join Session</a>
                </div>

                <div v-else-if="meeting.location" class="p-8 bg-gray-50/50 rounded-3xl border border-gray-100 flex items-center gap-5">
                    <div class="w-14 h-14 bg-gray-900 rounded-2xl flex items-center justify-center text-white text-2xl">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Physical Location</p>
                        <p class="text-sm font-black text-gray-900 tracking-tight">{{ meeting.location }}</p>
                    </div>
                </div>

                <!-- RSVP Stack -->
                <div class="space-y-6 pt-6 border-t border-gray-100">
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest text-center">Confirm Your Presence</h4>
                    <div class="flex gap-4">
                        <button 
                            @click="submitRsvp('accepted')"
                            :class="rsvpStatus === 'accepted' ? 'bg-emerald-500 text-white' : 'bg-gray-50 text-emerald-600 hover:bg-emerald-50'"
                            class="flex-1 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all border border-emerald-100"
                        >
                            Accept
                        </button>
                        <button 
                            @click="submitRsvp('declined')"
                            :class="rsvpStatus === 'declined' ? 'bg-rose-500 text-white' : 'bg-gray-50 text-rose-600 hover:bg-rose-50'"
                            class="flex-1 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all border border-rose-100"
                        >
                            Decline
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="px-12 py-8 bg-gray-50/50 border-t border-gray-100 flex justify-center">
                 <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Powered by HRMS Communications Infrastructure</p>
            </div>
        </div>
    </div>
</template>
