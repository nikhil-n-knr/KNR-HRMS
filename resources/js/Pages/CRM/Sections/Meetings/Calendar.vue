<script setup>
import { ref, onMounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

const props = defineProps({
    employees: { type: Array, default: () => [] },
    audience: { type: Array, default: () => [] },
    events: { type: Array, default: () => [] },
    view: { type: String, default: 'calendar' }
});

const initialEvents = ref([...props.events]);

import { watch } from 'vue';
watch(() => props.events, (newEvents) => {
    initialEvents.value = [...newEvents];
});

const activeStep = ref(1);
const showNewModal = ref(false);
const attendeeSearch = ref('');
const selectedEmployee = ref(null);

const meetingForm = ref({
    title: '',
    start_time: null,
    end_time: null,
    provider: null,
    employee_id: null,
    attendees: [],
    location: '',
    reminders: [{ offset: 15, channel: 'email' }],
});

const manualAttendee = ref({ name: '', email: '' });

onMounted(() => {
    const user = usePage().props.auth.user;
    if (user && user.employee) {
        meetingForm.value.employee_id = user.employee.id;
    }
});

const addManualAttendee = () => {
    if (manualAttendee.value.name && manualAttendee.value.email) {
        meetingForm.value.attendees.push({
            id: 'manual_' + Date.now(),
            name: manualAttendee.value.name,
            email: manualAttendee.value.email,
            type: 'External'
        });
        manualAttendee.value = { name: '', email: '' };
    }
};

const filteredAudience = computed(() => {
    if (!attendeeSearch.value) return [];
    return props.audience.filter(a => 
        (a.first_name + ' ' + a.last_name)?.toLowerCase().includes(attendeeSearch.value.toLowerCase()) ||
        a.email?.toLowerCase().includes(attendeeSearch.value.toLowerCase())
    ).slice(0, 5);
});

const toggleAttendee = (person) => {
    const idx = meetingForm.value.attendees.findIndex(a => a.id === person.id);
    if (idx > -1) meetingForm.value.attendees.splice(idx, 1);
    else meetingForm.value.attendees.push({ ...person, type: person.type || (person.first_name ? 'Contact' : 'Lead') });
};

const handleDateSelect = (info) => {
    // Format to YYYY-MM-DDTHH:mm for datetime-local
    const start = info.startStr.includes('T') ? info.startStr.substring(0, 16) : info.startStr + 'T09:00';
    const end = info.endStr ? (info.endStr.includes('T') ? info.endStr.substring(0, 16) : info.endStr + 'T10:00') : start;
    
    meetingForm.value.start_time = start;
    meetingForm.value.end_time = end;
    showNewModal.value = true;
};

const handleEventDrop = (info) => {
    axios.post(`/api/meetings/${info.event.id}/reschedule`, {
        start_time: info.event.startStr,
        end_time: info.event.endStr
    }).then(() => {
        router.reload({ only: ['initialEvents'] });
    });
};

const isProcessing = ref(false);
const errors = ref({});

const submitMeeting = () => {
    isProcessing.value = true;
    router.post(route('crm.meetings.store'), meetingForm.value, {
        onSuccess: () => {
            showNewModal.value = false;
            activeStep.value = 1;
            meetingForm.value = { 
                title: '', 
                attendees: [], 
                reminders: [{ offset: 15, channel: 'email' }],
                employee_id: usePage().props.auth.user?.employee?.id 
            };
            isProcessing.value = false;
        },
        onError: (err) => {
            errors.value = err;
            isProcessing.value = false;
            alert('Deployment Failed: ' + Object.values(err).join(', '));
        }
    });
};

const displayEvents = computed(() => {
    if (!selectedEmployee.value) return initialEvents.value;
    return initialEvents.value.filter(e => e.extendedProps?.employee_id == selectedEmployee.value || e.employee_id == selectedEmployee.value);
});

const calendarOptions = {
    plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
    initialView: 'dayGridMonth',
    selectable: true,
    editable: true,
    height: 'auto',
    events: displayEvents,
    select: handleDateSelect,
    eventDrop: handleEventDrop,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    }
};
</script>

<template>
    <div class="space-y-12">
        <header class="flex items-center justify-between">
            <div class="flex flex-col text-left">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">
                    {{ view === 'team' ? 'Team Performance Radar' : 'My Dynamic Schedule' }}
                </h2>
                <div v-if="view === 'team'" class="flex items-center gap-4 mt-2">
                    <span class="flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-100">
                        <i class="fas fa-signal animate-pulse"></i> {{ employees.length }} Active Reps
                    </span>
                    <span class="flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-indigo-100">
                        <i class="fas fa-microchip"></i> {{ initialEvents.length }} Daily Total
                    </span>
                </div>
                <p v-else class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1 opacity-60">Synchronizing neural appointments...</p>
            </div>
            
            <div class="flex gap-4">
                <div v-if="view === 'team'" class="flex -space-x-3 items-center mr-4">
                    <div v-for="e in employees.slice(0, 5)" :key="e.id" 
                         @click="selectedEmployee = (selectedEmployee === e.id ? null : e.id)"
                         :class="['w-10 h-10 rounded-2xl border-2 border-white flex items-center justify-center font-black text-xs cursor-pointer transition-all hover:scale-110 hover:z-10 shadow-lg', selectedEmployee === e.id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400']"
                         :title="e.first_name">
                        {{ e.first_name[0] }}
                    </div>
                </div>

                <select v-if="view === 'team'" v-model="selectedEmployee" class="bg-white border border-gray-100 rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest shadow-sm focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                    <option :value="null">Global Fleet View</option>
                    <option v-for="e in employees" :key="e.id" :value="e.id">Focus: {{ e.first_name }}</option>
                </select>

                <button @click="() => { 
                    const now = new Date(); 
                    const next = new Date(now.getTime() + 3600000); 
                    meetingForm.start_time = now.toISOString().substring(0, 16);
                    meetingForm.end_time = next.toISOString().substring(0, 16);
                    showNewModal = true; 
                }" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-indigo-100 transition-all hover:scale-105 active:scale-95 flex items-center gap-3">
                    <i class="fas fa-magic"></i> Initiate Wizard
                </button>
            </div>
        </header>

        <div class="bg-white rounded-[40px] p-10 border border-gray-50 shadow-sm">
            <FullCalendar :options="calendarOptions" />
        </div>

        <!-- Production Modal: Enterprise Wizard -->
        <div v-if="showNewModal" class="fixed inset-0 z-[100] bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-6">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-4xl w-full border border-white flex flex-col max-h-[85vh] overflow-hidden">
                <div class="p-8 bg-gray-50/50 flex justify-between items-center border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Session Wizard</h3>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1">Step {{ activeStep }} of 3: Deployment Strategy</p>
                    </div>
                    <button @click="showNewModal = false" class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-300 hover:text-gray-900 shadow-sm transition-all"><i class="fas fa-times"></i></button>
                </div>

                <div class="flex-1 overflow-y-auto p-10 custom-scrollbar">
                    <!-- Step 1: Basics -->
                    <div v-if="activeStep === 1" class="space-y-6 animate-in slide-in-from-right-4">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Meeting Target</label>
                            <input v-model="meetingForm.title" type="text" placeholder="Strategy Review: TechNova" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-lg font-black focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Start Time</label>
                                <input v-model="meetingForm.start_time" type="datetime-local" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold text-gray-700 shadow-inner">
                            </div>
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">End Time</label>
                                <input v-model="meetingForm.end_time" type="datetime-local" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold text-gray-700 shadow-inner">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Team Host</label>
                                <select v-model="meetingForm.employee_id" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold text-gray-700 shadow-inner">
                                    <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }} (Host)</option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Stack/Provider</label>
                                <select v-model="meetingForm.provider" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold text-gray-700 shadow-inner">
                                    <option :value="null">In-Person / Physical Venue</option>
                                    <option value="zoom">Zoom Video</option>
                                    <option value="meet">Google Meet</option>
                                    <option value="teams">MS Teams</option>
                                </select>
                            </div>
                        </div>
                        <div v-if="!meetingForm.provider" class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Physical Location / Room Details</label>
                            <input v-model="meetingForm.location" type="text" placeholder="Boardroom A, 4th Floor or Full Address..." class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold shadow-inner">
                        </div>
                    </div>

                    <!-- Step 2: Audience Integration -->
                    <div v-if="activeStep === 2" class="space-y-8 animate-in slide-in-from-right-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Global Audience Sync</label>
                                <div class="relative mt-3">
                                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                    <input v-model="attendeeSearch" type="text" placeholder="Search contacts..." class="w-full bg-gray-50 border-none rounded-2xl pl-12 pr-6 py-4 text-sm font-bold shadow-inner">
                                    <div v-if="filteredAudience.length" class="absolute inset-x-0 top-full mt-2 bg-white rounded-3xl shadow-2xl border border-gray-50 p-3 z-50 overflow-hidden">
                                        <div v-for="p in filteredAudience" :key="p.id" @click="toggleAttendee(p)" class="p-4 hover:bg-gray-50 rounded-2xl cursor-pointer flex items-center justify-between group transition-all">
                                            <span class="text-xs font-black">{{ p.first_name }} {{ p.last_name }}</span>
                                            <i class="fas fa-plus text-[10px] text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50/50 rounded-[32px] p-6 border border-gray-100 flex flex-col gap-4">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Manual Entry</label>
                                <div class="flex flex-col gap-2">
                                    <input v-model="manualAttendee.name" type="text" placeholder="Name" class="bg-white border-none rounded-xl p-3 text-[10px] font-black uppercase shadow-sm">
                                    <input v-model="manualAttendee.email" type="email" placeholder="Email" class="bg-white border-none rounded-xl p-3 text-[10px] font-black uppercase shadow-sm">
                                    <button @click="addManualAttendee" class="bg-gray-900 text-white rounded-xl p-3 text-[9px] font-black uppercase tracking-widest mt-2 transition-all hover:bg-indigo-600">Secure Add</button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div v-for="a in meetingForm.attendees" :key="a.id" class="px-5 py-2.5 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-3">
                                {{ a.first_name || a.name }} {{ a.last_name || '' }}
                                <button @click="toggleAttendee(a)" class="opacity-50 hover:opacity-100"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Invocation -->
                    <div v-if="activeStep === 3" class="space-y-10 animate-in slide-in-from-right-4">
                        <div class="p-8 bg-indigo-600 rounded-[32px] text-white relative overflow-hidden shadow-2xl shadow-indigo-100">
                             <i class="fas fa-rocket absolute -bottom-6 -right-6 text-9xl text-white/10 rotate-12"></i>
                             <h4 class="text-3xl font-black mb-2">Automated Launch Ready</h4>
                             <p class="text-indigo-100 font-bold opacity-80 leading-relaxed text-sm">We will generate the Live Join Link and dispatch high-density invitation templates to all sync'd audience members.</p>
                        </div>
                        <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-900 text-white rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-gray-100"><i class="fas fa-bell"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global Reminder Stack</p>
                                    <p class="text-sm font-black text-gray-900 tracking-tight">15 Min / 1 Day / 1 Week Before</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-10 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center bg-white sticky bottom-0">
                    <button v-if="activeStep > 1" @click="activeStep--" class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Stage Back
                    </button>
                    <div v-else></div>

                    <div class="flex items-center gap-4">
                        <button v-if="activeStep < 3" @click="activeStep++" class="px-12 py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-black transition-all shadow-xl flex items-center gap-2">
                            Next Stage <i class="fas fa-arrow-right"></i>
                        </button>
                        <button v-else @click="submitMeeting" :disabled="isProcessing" class="px-16 py-4 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-2">
                            <span v-if="isProcessing"><i class="fas fa-spinner fa-spin"></i> Deploying...</span>
                            <span v-else>Deploy Session <i class="fas fa-rocket"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.fc { font-family: 'Inter', sans-serif !important; --fc-border-color: #f3f4f6; --fc-today-bg-color: #f0f9ff; }
.fc-toolbar-title { font-size: 1.25rem !important; font-weight: 900 !important; color: #111827; text-transform: uppercase; letter-spacing: 0.1em; }
.fc .fc-button-primary { background: #111827; border: none; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 0.75rem 1.25rem; border-radius: 12px; }
</style>
