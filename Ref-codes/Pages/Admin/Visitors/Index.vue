<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { MagnifyingGlassIcon, QrCodeIcon, UserGroupIcon, CalendarIcon, ClockIcon, InformationCircleIcon, CameraIcon, ChartBarIcon, ShieldExclamationIcon, ArrowTrendingUpIcon, DocumentArrowUpIcon, UserPlusIcon, PrinterIcon, ArrowsPointingOutIcon, CheckIcon } from '@heroicons/vue/24/outline';
import WebcamCapture from '@/Components/Visitors/WebcamCapture.vue';
import BaseChart from '@/Components/BaseChart.vue';

const props = defineProps({
    stats: Object,
    visitors_inside: Array,
    all_passes: Array, // Added
    events: Array,
    employees: Array,
    dependents: Array,
    purposes: Array,
    analytics: Object,
    filters: Object // Added
});

// ... (imports)
const showInviteModal = ref(false);
const invitationResult = ref(null); // { link: '', qr: '', name: '' }
const showSuccessNotification = ref(false);
const successMessage = ref('');

const flashActive = (msg) => {
    successMessage.value = msg;
    showSuccessNotification.value = true;
    setTimeout(() => showSuccessNotification.value = false, 5000);
};

const inviteForm = useForm({
    name: '',
    email: '',
    phone: '',
    purpose_id: props.purposes[0]?.id || 1, 
    visit_date: new Date().toISOString().slice(0, 16),
    subject: 'Exclusive Invitation: Campus Visit',
    body: 'You are invited to visit our offices. Please find your fast-track registration details below.',
    cc: '',
    bcc: ''
});

const submitInvite = () => {
    inviteForm.post(route('visitors.invite'), {
        onSuccess: (page) => {
            if (page.props.flash.invitation_data) {
                invitationResult.value = page.props.flash.invitation_data;
                flashActive('Invitation Sent Successfully');
            }
            inviteForm.reset();
        }
    });
};

const closeInviteModal = () => {
    showInviteModal.value = false;
    invitationResult.value = null;
    inviteForm.reset();
};

// -- Restore Missing Logic --
const activeTab = ref('console'); // console, events, family, kiosk, analytics
const showWebcam = ref(false);

const showWalkInModal = ref(false);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    host_id: '',
    company: '',
    purpose_id: 1, // Add purpose mapping
    event_id: '',
    expected_duration: 60,
    photo: null,
    is_vip: false,
    vehicle_no: '',
    materials: []
});

const submitCheckIn = () => {
    form.post(route('visitors.check-in'), {
        onSuccess: (page) => {
            if (page.props.flash.print_id) {
                // Trigger Silent Print / Window Print
                window.open(route('visitors.print', page.props.flash.print_id), '_blank', 'width=400,height=600');
            }
            form.reset();
            showWalkInModal.value = false;
        }
    });
};

const filters = ref({
    dateRange: props.filters?.dateRange || 'today',
    status: props.filters?.status || 'All Statuses',
    category: props.filters?.category || 'All Categories',
    startDate: props.filters?.startDate || '',
    endDate: props.filters?.endDate || '',
    showArchived: props.filters?.showArchived === 'true' || props.filters?.showArchived === true || false
});

watch(filters, (newVal) => {
    router.get(route('visitors.index'), newVal, { preserveState: true, replace: true });
}, { deep: true });

const exportUrl = computed(() => {
    const cleanFilters = {};
    Object.keys(filters.value).forEach(k => {
        if (filters.value[k] !== undefined && filters.value[k] !== null && filters.value[k] !== '') {
            cleanFilters[k] = filters.value[k];
        }
    });
    return route('visitors.export', cleanFilters);
});

const showEditModal = ref(false);
const editForm = useForm({
    id: '',
    name: '',
    phone: '',
    email: '',
    company: '',
    host_id: '',
    expected_duration: 60
});

const openEditModal = (visit) => {
    editForm.id = visit.id;
    editForm.name = visit.visitor?.name || '';
    editForm.phone = visit.visitor?.phone || '';
    editForm.email = visit.visitor?.email || '';
    editForm.company = visit.visitor?.company || '';
    editForm.host_id = visit.visitor?.host_id || '';
    editForm.expected_duration = visit.expected_duration || 60;
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.post(route('visitors.update', editForm.id), {
        onSuccess: () => {
            flashActive('Visitor Details Updated Successfully');
            showEditModal.value = false;
        }
    });
};

const restorePass = (id) => {
    if (confirm('Are you sure you want to restore this visitor pass?')) {
        router.post(route('visitors.restore', id), {}, {
            onSuccess: () => flashActive('Pass Restored Successfully')
        });
    }
};

const restoreEvent = (id) => {
    if (confirm('Are you sure you want to restore this event?')) {
        router.post(route('events.restore', id), {}, {
            onSuccess: () => flashActive('Event Restored Successfully')
        });
    }
};

const forceDeletePass = (id) => {
    if (confirm('Are you sure you want to PERMANENTLY delete this visitor pass? This action cannot be undone.')) {
        router.delete(route('visitors.force-delete', id), {
            onSuccess: () => flashActive('Pass Deleted Permanently')
        });
    }
};

const forceDeleteEvent = (id) => {
    if (confirm('Are you sure you want to PERMANENTLY delete this event? This action cannot be undone.')) {
        router.delete(route('events.force-delete', id), {
            onSuccess: () => flashActive('Event Deleted Permanently')
        });
    }
};

const checkInFamilyMember = (fam) => {
    const personalPurpose = props.purposes.find(p => p.key === 'personal') || props.purposes[0];
    const personalPurposeId = personalPurpose ? personalPurpose.id : 1;
    
    router.post(route('visitors.check-in'), {
        name: fam.name,
        phone: fam.phone || '',
        host_id: fam.employee?.user?.id,
        purpose_id: personalPurposeId,
        expected_duration: 60,
    }, {
        onSuccess: (page) => {
            if (page.props.flash.print_id) {
                window.open(route('visitors.print', page.props.flash.print_id), '_blank', 'width=400,height=600');
            }
            flashActive('Family member checked in successfully!');
        }
    });
};

const getSelectedPurpose = () => {
    return props.purposes.find(p => p.id === form.purpose_id) || props.purposes[0];
};

const isFieldVisible = (field) => {
    const purpose = getSelectedPurpose();
    const config = JSON.parse(purpose?.form_config || '{}');
    return config[field] !== 'hidden';
};

const isFieldRequired = (field) => {
    const purpose = getSelectedPurpose();
    const config = JSON.parse(purpose?.form_config || '{}');
    return config[field] === 'required';
};

const closeWalkInModal = () => {
    showWalkInModal.value = false;
    form.reset();
};

const showCheckoutConfirm = ref(false);
const selectedPassForCheckout = ref(null);
const badgeCollectedOnCheckout = ref(true);

const confirmCheckout = (id) => {
    selectedPassForCheckout.value = id;
    showCheckoutConfirm.value = true;
};

const archivePass = (id) => {
    if (confirm('Are you sure you want to archive this visitor pass? It will be removed from the active board but kept in logs.')) {
        router.delete(route('visitors.destroy', id), {
            onSuccess: () => flashActive('Pass Archived Successfully')
        });
    }
};

const executeCheckOut = () => {
    router.post(route('visitors.check-out', selectedPassForCheckout.value), {
        badge_collected: badgeCollectedOnCheckout.value
    }, {
        onSuccess: () => {
            flashActive('Visitor Checked Out Successfully');
            showCheckoutConfirm.value = false;
            selectedPassForCheckout.value = null;
        }
    });
};

const showEventGuestModal = ref(false);
const selectedEvent = ref(null);
const eventGuests = ref([]);
const isLoadingGuests = ref(false);

const manageGuests = async (event) => {
    selectedEvent.value = event;
    showEventGuestModal.value = true;
    isLoadingGuests.value = true;
    try {
        const response = await fetch(route('events.guests', event.id));
        const data = await response.json();
        eventGuests.value = data.passes || [];
    } catch (error) {
        console.error('Failed to fetch guests:', error);
    } finally {
        isLoadingGuests.value = false;
    }
};

const archiveEvent = (id) => {
    if (confirm('Archive this event?')) {
        router.delete(route('events.destroy', id), {
            onSuccess: () => flashActive('Event Archived Successfully')
        });
    }
};

const importForm = useForm({
    file: null,
});

const submitImport = () => {
    if (!selectedEvent.value) return;
    importForm.post(route('events.import', selectedEvent.value.id), {
        onSuccess: () => {
            flashActive('Guest List Uploaded & Invitations Queued');
            importForm.reset();
            manageGuests(selectedEvent.value); // Refresh
        },
    });
};

const selectedGuests = ref([]);
const guestQuery = ref('');

const filteredModalGuests = computed(() => {
    if (!guestQuery.value) return eventGuests.value;
    const q = guestQuery.value.toLowerCase();
    return eventGuests.value.filter(g => 
        g.visitor?.name.toLowerCase().includes(q) || 
        g.visitor?.email?.toLowerCase().includes(q) ||
        g.visitor?.company?.toLowerCase().includes(q)
    );
});

const toggleGuestSelection = (id) => {
    if (selectedGuests.value.includes(id)) {
        selectedGuests.value = selectedGuests.value.filter(gid => gid !== id);
    } else {
        selectedGuests.value.push(id);
    }
};

const applyBulkAction = (action) => {
    if (selectedGuests.value.length === 0) return;
    router.post(route('visitors.bulk-action'), {
        pass_ids: selectedGuests.value,
        action: action
    }, {
        onSuccess: () => {
            flashActive(`Bulk ${action} Applied`);
            selectedGuests.value = [];
            manageGuests(selectedEvent.value);
        }
    });
};

const showEventAnalytics = ref(false);
const performanceData = ref(null);
const isLoadingPerformance = ref(false);

const getPerformanceData = async () => {
    if (!selectedEvent.value) return;
    showEventAnalytics.value = true;
    isLoadingPerformance.value = true;
    try {
        const response = await fetch(route('events.performance', selectedEvent.value.id));
        performanceData.value = await response.json();
    } catch (error) {
        console.error('Failed to fetch performance data:', error);
    } finally {
        isLoadingPerformance.value = false;
    }
};

const wrapUpEvent = (id) => {
    if (confirm('Are you sure you want to wrap up this event? This will check out all remaining guests and mark the event as completed.')) {
        router.post(route('events.wrap-up', id), {}, {
            onSuccess: () => {
                flashActive('Event Wrapped Up Successfully');
                showEventGuestModal.value = false;
            }
        });
    }
};

const eventFilters = ref('upcoming'); // upcoming, past, all

const filteredEvents = computed(() => {
    const now = new Date();
    return props.events.filter(e => {
        const startTime = new Date(e.start_time);
        if (eventFilters.value === 'upcoming') return startTime >= now;
        if (eventFilters.value === 'past') return startTime < now;
        return true;
    });
});

const eventForm = useForm({
    title: '',
    description: '',
    start_time: '',
    end_time: '',
    location: '',
    access_areas: [],
    guest_limit: 10
});

const submitEvent = () => {
    eventForm.post(route('events.store'), {
        onSuccess: () => {
            eventForm.reset();
            flashActive('Event Created Successfully');
        },
        onError: () => {
            flashActive('Failed to create event. Please check inputs.');
        }
    });
};

const showInfoModal = ref(false);
const infoContent = ref({ title: '', body: '' });

const showInfo = (title, body) => {
    infoContent.value = { title, body };
    showInfoModal.value = true;
};

// Analytics Computing
const overstayData = computed(() => ({
    labels: ['On-Time', 'Overstayed'],
    datasets: [{
        data: [props.analytics.overstay_stats.on_time, props.analytics.overstay_stats.overstayed],
        backgroundColor: ['#4F46E5', '#EF4444'],
        borderWidth: 0
    }]
}));

const heatmapData = computed(() => ({
    labels: props.analytics.facility_heatmap.map(h => `${h.hour}:00`),
    datasets: [{
        label: 'Visitors',
        data: props.analytics.facility_heatmap.map(h => h.count),
        borderColor: '#4F46E5',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        tension: 0.4,
        fill: true
    }]
}));

const showHubInfo = () => showInfo('Visitor & Access Hub', 
    'The Visitor & Access Hub is your central security and hospitality command center.\n\n' +
    '• Console: Manual check-in for walk-ins. Automatically creates visitor records and prints badges.\n' +
    '• Invitations: Send QR codes and magic links to guests for fast-track entry.\n' +
    '• Events: Group access management for meetings, interviews, or conferences.\n' +
    '• Family: Pre-verify employee dependents for instant access during emergencies or visits.\n' +
    '• Kiosk: Configure self-service reception tablets with NDA signatures and selfie capture.'
);

const showCheckInInfo = () => showInfo('Reception Check-In Logic', 
    'FRONTEND ACTION: Captures visitor details and meeting host. On submit, it hits /visitors/check-in via Inertia.\n\n' +
    'BACKEND LOGIC:\n' +
    '1. Validates input against Purpose-specific rules (e.g. email required for Interviews).\n' +
    '2. Dynamic Workflows: If "Interview" purpose is selected, it automatically creates a Candidate record in OPSCORE.\n' +
    '3. Blacklist Check: Instant verification against the blocked visitors database.\n' +
    '4. Pass Generation: Generates a unique 8-digit code and guest WiFi credentials.'
);

const showInvitationInfo = () => showInfo('Smart Invitation System', 
    'FRONTEND ACTION: Opens the Invite modal. Submits to /visitors/invite.\n\n' +
    'BACKEND LOGIC:\n' +
    '1. Pre-registration: Creates a Visitor record and a VisitorPass with "Pre-Registered" status.\n' +
    '2. Secure Link: Generates a cryptographically signed magic link valid only for the visit date.\n' +
    '3. Communication: Dispatches an email with a unique QR code for at-gate scanning.'
);
</script>

<template>
    <Head title="Visitor Hub" />
    <MainLayout>
        <!-- Walk-In Modal -->
        <div v-if="showWalkInModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-[2rem] shadow-xl max-w-3xl w-full overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-lg text-slate-800">New Walk-In</h3>
                        <button @click="showCheckInInfo" class="text-xs text-indigo-600 hover:underline flex items-center gap-1">
                            <InformationCircleIcon class="w-4 h-4" /> Explain Logic
                        </button>
                    </div>
                    <button @click="closeWalkInModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <!-- Photo Header Section -->
                    <div class="mb-8 flex flex-col items-center">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-[2.5rem] bg-slate-100 border-2 border-dashed border-slate-300 overflow-hidden flex items-center justify-center transition-all group-hover:border-indigo-400">
                                <img v-if="form.photo" :src="form.photo" class="w-full h-full object-cover">
                                <CameraIcon v-else class="w-12 h-12 text-slate-300" />
                            </div>
                            <button type="button" @click="showWebcam = true" class="absolute -bottom-2 -right-2 p-2 bg-indigo-600 text-white rounded-xl shadow-lg hover:bg-indigo-700 transition-transform active:scale-90">
                                <CameraIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-3">Security Photo Identity</p>
                    </div>

                    <form @submit.prevent="submitCheckIn" class="grid grid-cols-2 gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Visitor Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500" required placeholder="John Doe">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input v-model="form.phone" type="tel" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500" :required="isFieldRequired('phone')" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pass Type / Purpose</label>
                            <select v-model="form.purpose_id" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500">
                                <option v-for="p in purposes" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div v-if="isFieldVisible('email')" class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input v-model="form.email" type="email" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500" :required="isFieldRequired('email')">
                        </div>
                        <div v-if="isFieldVisible('company')" class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                            <input v-model="form.company" type="text" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500" :required="isFieldRequired('company')">
                        </div>
                        <div v-if="isFieldVisible('vehicle_no')" class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Vehicle No (Delivery)</label>
                            <input v-model="form.vehicle_no" type="text" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Whom to Meet</label>
                            <select v-model="form.host_id" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500" required>
                                <option value="" disabled>Select Employee...</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }} ({{ emp.department?.name || 'Gen' }})</option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Expected Duration (Minutes)</label>
                            <input v-model="form.expected_duration" type="number" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Link to Event (Optional)</label>
                            <select v-model="form.event_id" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-indigo-500 text-sm">
                                <option value="">No Active Event</option>
                                <option v-for="evt in events" :key="evt.id" :value="evt.id">
                                    {{ evt.title }} ({{ evt.passes_count }}/{{ evt.guest_limit }})
                                </option>
                            </select>
                        </div>
                        <div v-if="isFieldVisible('material_entry')" class="col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                             <div class="flex items-center justify-between mb-2">
                                 <label class="text-sm font-black text-slate-700">Equipments / Materials</label>
                                 <button type="button" @click="form.materials.push({item: '', serial: ''})" class="text-xs text-indigo-600 font-black">+ Add Item</button>
                             </div>
                             <div v-for="(m, idx) in form.materials" :key="idx" class="flex gap-2 mb-2">
                                 <input v-model="m.item" placeholder="Item Name" class="flex-1 text-xs border-slate-300 rounded">
                                 <input v-model="m.serial" placeholder="Serial (if any)" class="flex-1 text-xs border-slate-300 rounded">
                                 <button @click="form.materials.splice(idx, 1)" class="text-red-400">&times;</button>
                             </div>
                        </div>
                        <div class="col-span-2 flex items-center justify-between mt-4">
                            <div class="flex items-center">
                                <input v-model="form.is_vip" type="checkbox" id="vip_flag" class="rounded border-slate-300 text-indigo-600 shadow-xl shadow-slate-200/50 focus:border-indigo-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                <label for="vip_flag" class="ml-2 text-sm text-slate-700">Flag as VIP / Priority</label>
                            </div>
                            <button type="submit" :disabled="form.processing" class="bg-black hover:bg-slate-800 text-white px-6 py-2 rounded-lg font-medium shadow-2xl shadow-slate-200/50 transition-colors flex items-center">
                                <QrCodeIcon class="w-5 h-5 mr-2"/>
                                {{ form.processing ? 'Processing...' : 'Print Badge & Check In' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Invite Modal -->
        <div v-if="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-[2rem] shadow-xl max-w-2xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-lg text-slate-800">Create Invitation</h3>
                    </div>
                    <button @click="closeInviteModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <div v-if="!invitationResult" class="p-6 max-h-[80vh] overflow-y-auto">
                    <form @submit.prevent="submitInvite" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                           <div>
                               <label class="text-sm font-black text-slate-700">Visitor Name</label>
                               <input v-model="inviteForm.name" type="text" required class="w-full mt-1 border-slate-300 rounded-lg">
                           </div>
                           <div>
                               <label class="text-sm font-black text-slate-700">Email Address</label>
                               <input v-model="inviteForm.email" type="email" required class="w-full mt-1 border-slate-300 rounded-lg">
                           </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                           <div>
                               <label class="text-sm font-black text-slate-700">Phone (Optional)</label>
                               <input v-model="inviteForm.phone" type="text" class="w-full mt-1 border-slate-300 rounded-lg">
                           </div>
                           <div>
                                <label class="text-sm font-black text-slate-700">Expected Arrival</label>
                                <input v-model="inviteForm.visit_date" type="datetime-local" class="w-full mt-1 border-slate-300 rounded-lg">
                           </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                           <div>
                               <label class="text-sm font-black text-slate-700">CC Email (Optional)</label>
                               <input v-model="inviteForm.cc" type="email" class="w-full mt-1 border-slate-300 rounded-lg" placeholder="cc@example.com">
                           </div>
                           <div>
                               <label class="text-sm font-black text-slate-700">BCC Email (Optional)</label>
                               <input v-model="inviteForm.bcc" type="email" class="w-full mt-1 border-slate-300 rounded-lg" placeholder="bcc@example.com">
                           </div>
                        </div>
                        <div>
                            <label class="text-sm font-black text-slate-700">Email Subject</label>
                            <input v-model="inviteForm.subject" type="text" required class="w-full mt-1 border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="text-sm font-black text-slate-700">Email Content / Message</label>
                            <textarea v-model="inviteForm.body" rows="4" required class="w-full mt-1 border-slate-300 rounded-lg"></textarea>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="closeInviteModal" class="px-4 py-2 text-slate-600 font-black hover:bg-slate-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="inviteForm.processing" class="px-4 py-2 bg-indigo-600 text-white font-black rounded-lg hover:bg-indigo-700">
                                {{ inviteForm.processing ? 'Sending...' : 'Send Invitation' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Success State -->
                <div v-else class="p-6 text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                         <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h4 class="text-xl font-black text-slate-900">Invitation Sent!</h4>
                    <p class="text-slate-500 mt-1">An email has been sent to {{ invitationResult.name }}.</p>
                    
                    <div class="mt-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-xs font-black text-slate-400 uppercase mb-2">Share Link Manually</p>
                        <div class="flex items-center gap-2">
                             <input type="text" readonly :value="invitationResult.link" class="flex-1 text-xs border-slate-300 rounded bg-white">
                             <button class="text-xs font-black text-indigo-600 hover:underline">Copy</button>
                        </div>
                        <div class="mt-4 flex justify-center">
                            <img :src="`data:image/png;base64,${invitationResult.qr}`" class="w-32 h-32 border border-white shadow-xl shadow-slate-200/50 rounded-lg">
                        </div>
                         <p class="text-xs text-slate-400 mt-2">Scan for Fast Track Entry</p>
                    </div>
                     <button @click="closeInviteModal" class="mt-6 w-full py-2 bg-slate-900 text-white font-black rounded-lg">Done</button>
                </div>
            </div>
        </div>

        <div class="space-y-6 font-outfit">
            <!-- Pulse Header: Real-time Security & Operations -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl shadow-slate-200/50 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="p-3 bg-red-50 text-red-600 rounded-[2rem] group-hover:animate-pulse">
                            <CalendarIcon class="w-6 h-6"/>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest">Visited This Week</div>
                            <div class="text-2xl font-black text-slate-900">{{ props.stats.visited_this_week || 0 }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl shadow-slate-200/50 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-[2rem] group-hover:animate-bounce">
                            <CalendarIcon class="w-6 h-6"/>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest">Visited This Month</div>
                            <div class="text-2xl font-black text-slate-900">{{ props.stats.visited_this_month || 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl shadow-slate-200/50 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-slate-100 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="p-3 bg-slate-900 text-white rounded-[2rem] group-hover:rotate-12 transition-transform">
                            <UserGroupIcon class="w-6 h-6"/>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest">Active Passes</div>
                            <div class="text-2xl font-black text-slate-900">{{ props.stats.currently_inside || 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl shadow-slate-200/50 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-[2rem] group-hover:translate-y-[-2px] transition-transform">
                            <ClockIcon class="w-6 h-6"/>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest">Expected Today</div>
                            <div class="text-2xl font-black text-slate-900">{{ props.stats.expected_today || 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header & Action Bar -->
            <div class="mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mt-10">
                <div class="flex items-center gap-5">
                    <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-indigo-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <ShieldExclamationIcon class="w-7 h-7 relative z-10" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            Visitor & Access Hub
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest">Security Core</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Strategic Security & Hospitality Command Center</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 overflow-x-auto hide-scrollbar pb-2 lg:pb-0 w-full lg:w-auto">
                    <div class="flex bg-slate-100 p-1 rounded-2xl border border-slate-200 shadow-inner">
                        <button 
                            v-for="tab in ['console', 'events', 'family', 'kiosk', 'analytics']"
                            :key="tab"
                            @click="activeTab = tab"
                            class="px-6 py-3 rounded-xl text-sm font-black uppercase tracking-[0.15em] transition-all whitespace-nowrap"
                            :class="activeTab === tab ? 'bg-white text-indigo-600 shadow-md border-b-[3px] border-indigo-600' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
                        >
                            {{ tab }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Global Filter Engine & Action Bar -->
            <div class="bg-white/40 backdrop-blur-xl p-6 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-white flex flex-wrap lg:flex-nowrap items-center justify-between gap-6 relative z-10">
                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <!-- Date Range -->
                    <div class="flex bg-slate-50 rounded-2xl p-1 border border-slate-100 shadow-inner">
                        <button v-for="d in ['Today', 'Tomorrow', '7 Days', 'Custom']" :key="d" 
                            @click="filters.dateRange = d.toLowerCase()"
                            class="px-4 py-2 text-sm font-black uppercase tracking-widest rounded-xl transition-all"
                            :class="filters.dateRange === d.toLowerCase() ? 'bg-white text-indigo-600 shadow-sm border border-slate-100' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'"
                        >
                            {{ d }}
                        </button>
                    </div>

                    <!-- Custom Date Range Picker -->
                    <template v-if="filters.dateRange === 'custom'">
                        <input type="date" v-model="filters.startDate" class="h-10 px-4 text-sm font-black border border-slate-100 rounded-2xl bg-white text-slate-600 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer shadow-sm" placeholder="Start Date" />
                        <span class="text-slate-400 font-bold">to</span>
                        <input type="date" v-model="filters.endDate" class="h-10 px-4 text-sm font-black border border-slate-100 rounded-2xl bg-white text-slate-600 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer shadow-sm" placeholder="End Date" />
                    </template>

                    <!-- Status Toggles -->
                    <select v-model="filters.status" class="h-10 px-4 text-sm font-black uppercase tracking-widest border border-slate-100 rounded-2xl bg-white text-slate-600 focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer shadow-sm min-w-[140px]">
                        <option>All Statuses</option>
                        <option>Pre-Registered</option>
                        <option>Checked-In</option>
                        <option>Overstayed</option>
                        <option>Checked-Out</option>
                        <option>Revoked</option>
                    </select>

                    <!-- Category Multi-Select -->
                    <select v-model="filters.category" class="h-10 px-4 text-sm font-black uppercase tracking-widest border border-slate-100 rounded-2xl bg-white text-slate-600 focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer shadow-sm min-w-[140px]">
                        <option>All Categories</option>
                        <option v-for="p in purposes" :key="p.id" :value="p.name">{{ p.name }}</option>
                    </select>

                    <!-- Archived Toggle -->
                    <label class="flex items-center gap-2 cursor-pointer select-none px-4 h-10 border border-slate-100 rounded-2xl bg-white text-slate-600 shadow-sm text-xs font-black uppercase tracking-widest">
                        <input type="checkbox" v-model="filters.showArchived" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-200" /> 
                        Show Archived Only
                    </label>
                </div>

                <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 w-full lg:w-auto mt-4 lg:mt-0">
                    <a :href="exportUrl" class="flex-1 lg:flex-none h-12 px-6 bg-white border border-slate-200 text-slate-600 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center justify-center gap-2 shadow-sm active:scale-95">
                        <DocumentArrowUpIcon class="w-4 h-4 text-indigo-400" />
                        Export
                    </a>
                    <button @click="showInviteModal = true" class="flex-1 lg:flex-none h-12 px-6 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-100 transition-all shadow-sm active:scale-95">
                        Create Invitation
                    </button>
                    <button @click="showWalkInModal = true" class="flex-1 lg:flex-none h-12 px-8 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                        <UserPlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-12 transition-transform" />
                        Register Walk-in
                    </button>
                </div>
            </div>
            

            <div class="grid grid-cols-12 gap-6">
                <!-- Sidebar / Quick Stats (Always Visible) -->
                <div class="col-span-12 md:col-span-3 space-y-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-5 text-white shadow-lg">
                        <div class="text-xs opacity-75 uppercase tracking-wider font-semibold">Currently Inside</div>
                        <div class="text-4xl font-black mt-1">{{ stats.currently_inside }}</div>
                        <div class="mt-4 flex items-center text-sm opacity-90">
                            <ClockIcon class="w-4 h-4 mr-1" /> Peak: {{ stats.busiest_hour }}
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-xl shadow-slate-200/50 border border-slate-200 p-4">
                        <h3 class="font-black text-slate-800 mb-3 flex items-center">
                            <UserGroupIcon class="w-5 h-5 mr-2 text-indigo-600"/>
                            Active Passes
                        </h3>
                        <div class="space-y-3">
                            <div v-for="visit in visitors_inside" :key="visit.id" class="flex justify-between items-start text-sm border-b border-slate-100 last:border-0 pb-2">
                                <div>
                                    <div class="font-medium text-slate-900">{{ visit.visitor.name }}</div>
                                    <div class="text-xs text-slate-500">Meeting: {{ visit.visitor.host?.name }}</div>
                                </div>
                                <div class="text-right">
                                    <button @click="confirmCheckout(visit.id)" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                        Check Out
                                    </button>
                                    <div class="text-sm text-slate-400 mt-1">{{ new Date(visit.check_in_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</div>
                                </div>
                            </div>
                            <div v-if="visitors_inside.length === 0" class="text-sm text-slate-400 italic">No visitors currently inside.</div>
                        </div>
                    </div>
                </div>

                <!-- Main Area -->
                <div class="col-span-12 md:col-span-9">
                    
                    <!-- TAB 1: CONSOLE (LIVE BOARD) -->
                    <div v-if="activeTab === 'console'" class="space-y-4">
                        <div class="flex justify-between items-center px-2">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">
                                {{ filters.status === 'All Statuses' && filters.dateRange === 'today' ? 'Live Visitor Board' : 'Filtered Visitor Logs' }}
                            </h3>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-green-500"></div> Active</span>
                                <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-red-500"></div> Overstayed</span>
                                <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-blue-500"></div> Pre-Reg</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Visitor Cards -->
                            <div v-for="visit in all_passes" :key="visit.id" 
                                class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:shadow-2xl shadow-slate-200/50 transition-all overflow-hidden relative group"
                                :class="visit.status === 'Checked-Out' ? 'opacity-75 grayscale-[0.5]' : ''"
                            >
                                <div v-if="visit.status === 'Overstayed'" class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
                                <div v-else-if="visit.status === 'Checked-In'" class="absolute top-0 left-0 w-full h-1 bg-green-500"></div>
                                <div v-else-if="visit.status === 'Checked-Out'" class="absolute top-0 left-0 w-full h-1 bg-slate-400"></div>
                                <div v-else class="absolute top-0 left-0 w-full h-1 bg-blue-500"></div>

                                <div class="p-5">
                                    <div class="flex items-start gap-4">
                                        <!-- Photo Capture Simulation -->
                                        <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0">
                                            <img v-if="visit.visitor.photo_path" :src="visit.visitor.photo_path" class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-black text-slate-900 truncate">{{ visit.visitor.name }}</h4>
                                                <span v-if="visit.is_vip" class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-sm font-black rounded uppercase">VIP</span>
                                            </div>
                                            <p class="text-xs text-slate-400 truncate">{{ visit.visitor.company || 'Private Visit' }}</p>
                                            
                                            <div class="mt-3 flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center border border-indigo-100">
                                                    <span class="text-sm font-black text-indigo-600">{{ visit.visitor.host?.name.charAt(0) }}</span>
                                                </div>
                                                <span class="text-xs text-slate-600 font-medium truncate">Meeting Contact: {{ visit.visitor.host?.name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status & Timers -->
                                    <div class="mt-5 pt-4 border-t border-slate-50 flex items-center justify-between">
                                        <div class="space-y-1">
                                            <div class="text-sm font-black text-slate-400 uppercase">Status</div>
                                            <div class="text-xs font-black" :class="visit.status === 'Checked-In' ? 'text-green-600' : 'text-slate-500 text-indigo-600'">
                                                {{ visit.status }}
                                            </div>
                                        </div>

                                        <div class="text-right space-y-1">
                                            <div class="text-sm font-black text-slate-400 uppercase">Time</div>
                                            <div class="text-xs font-mono font-black text-slate-700">
                                                {{ visit.check_in_at ? new Date(visit.check_in_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Waiting' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Overlay -->
                                    <div class="mt-4 flex gap-2">
                                        <template v-if="filters.showArchived">
                                            <button @click="restorePass(visit.id)" class="flex-1 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg text-xs font-black border border-green-200 transition-colors">
                                                Restore
                                            </button>
                                            <button @click="forceDeletePass(visit.id)" class="py-1.5 px-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-black border border-red-200 transition-colors" title="Delete Permanently">
                                                Delete
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button v-if="visit.status === 'Checked-In' || visit.status === 'Overstayed'" @click="confirmCheckout(visit.id)" class="flex-1 py-1.5 bg-slate-50 hover:bg-red-50 text-slate-500 hover:text-red-600 rounded-lg text-xs font-black border border-slate-100 transition-colors">
                                                Check Out
                                            </button>
                                            <a :href="route('visitors.print', visit.id)" target="_blank" class="px-3 py-1.5 bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 rounded-lg border border-slate-100 flex items-center justify-center">
                                                <QrCodeIcon class="w-4 h-4" />
                                            </a>
                                            <button @click="openEditModal(visit)" class="px-3 py-1.5 bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-blue-600 rounded-lg border border-slate-100" title="Edit Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button @click="archivePass(visit.id)" class="px-3 py-1.5 bg-slate-50 hover:bg-orange-50 text-slate-400 hover:text-orange-600 rounded-lg border border-slate-100" title="Archive Pass">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State Card -->
                            <div v-if="all_passes.length === 0" class="col-span-full py-12 flex flex-col items-center justify-center bg-slate-50/50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                                <div class="w-16 h-16 bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 flex items-center justify-center mb-4">
                                     <UserGroupIcon class="w-8 h-8 text-slate-200" />
                                </div>
                                <h4 class="font-black text-slate-400">No visitors found matching these criteria</h4>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or start a new check-in.</p>
                                <button @click="showWalkInModal = true" class="mt-4 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-black hover:bg-indigo-100">
                                    Start First Check-In
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: EVENTS -->
                    <div v-if="activeTab === 'events'" class="space-y-6">
                        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Create New Event</h3>
                                    <p class="text-sm text-slate-400 font-black">Register group visits, meetings or campus events</p>
                                </div>
                                <div class="flex gap-2">
                                     <button @click="eventFilters = 'upcoming'" :class="eventFilters === 'upcoming' ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:bg-slate-100'" class="px-3 py-1 text-sm font-black uppercase rounded-lg transition-all">Upcoming</button>
                                     <button @click="eventFilters = 'past'" :class="eventFilters === 'past' ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:bg-slate-100'" class="px-3 py-1 text-sm font-black uppercase rounded-lg transition-all">Past</button>
                                     <button @click="eventFilters = 'all'" :class="eventFilters === 'all' ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:bg-slate-100'" class="px-3 py-1 text-sm font-black uppercase rounded-lg transition-all">All</button>
                                </div>
                            </div>
                            <div class="p-6">
                                <form @submit.prevent="submitEvent" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="text-sm font-black text-slate-400 uppercase mb-1 block">Event Title</label>
                                        <input v-model="eventForm.title" placeholder="Annual Shareholders Meeting..." class="w-full rounded-xl border-slate-200 text-sm focus:ring-purple-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-black text-slate-400 uppercase mb-1 block">Location</label>
                                        <input v-model="eventForm.location" placeholder="Conference Hall B" class="w-full rounded-xl border-slate-200 text-sm focus:ring-purple-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-black text-slate-400 uppercase mb-1 block">Guest Limit</label>
                                        <input v-model="eventForm.guest_limit" type="number" class="w-full rounded-xl border-slate-200 text-sm focus:ring-purple-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-black text-slate-400 uppercase mb-1 block">Start Time</label>
                                        <input v-model="eventForm.start_time" type="datetime-local" class="w-full rounded-xl border-slate-200 text-sm focus:ring-purple-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-black text-slate-400 uppercase mb-1 block">End Time</label>
                                        <input v-model="eventForm.end_time" type="datetime-local" class="w-full rounded-xl border-slate-200 text-sm focus:ring-purple-500 focus:border-indigo-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-sm font-black text-slate-400 uppercase mb-1 block">Description</label>
                                        <textarea v-model="eventForm.description" rows="1" placeholder="Brief details about the event context..." class="w-full rounded-xl border-slate-200 text-sm focus:ring-purple-500 focus:border-indigo-500"></textarea>
                                    </div>
                                    <div class="md:col-span-4 flex justify-end">
                                        <button type="submit" :disabled="eventForm.processing" class="px-8 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:shadow-lg hover:shadow-purple-200 transition-all disabled:opacity-50">
                                            {{ eventForm.processing ? 'Syncing...' : 'Deploy Event' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div v-for="event in filteredEvents" :key="event.id" class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 relative overflow-hidden group hover:shadow-xl transition-all">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110 opacity-50"></div>
                                <div class="flex justify-between items-start relative z-10 mb-4">
                                    <div>
                                        <h4 class="font-black text-lg text-slate-900 leading-tight">{{ event.title }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <CalendarIcon class="w-3 h-3 text-indigo-500" />
                                            <span class="text-sm font-black text-slate-400 uppercase">{{ new Date(event.start_time).toLocaleDateString() }} @ {{ new Date(event.start_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                         <span class="text-sm font-black px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 uppercase tracking-tighter">{{ event.location }}</span>
                                         <span class="mt-1 text-xs font-black text-indigo-600">{{ event.passes_count || 0 }}/{{ event.guest_limit }}</span>
                                    </div>
                                </div>
                                
                                <p class="text-base text-slate-500 line-clamp-2 mb-6 relative z-10 font-medium">
                                    {{ event.description || 'No description provided for this campus event.' }}
                                </p>

                                <div class="flex justify-between items-center relative z-10 border-t border-slate-50 pt-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-black text-indigo-600 uppercase">
                                            {{ event.organizer?.name.charAt(0) }}
                                        </div>
                                        <span class="text-sm font-black text-slate-400 uppercase">{{ event.organizer?.name }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <template v-if="filters.showArchived">
                                            <button @click="restoreEvent(event.id)" class="text-green-600 text-sm font-black uppercase tracking-widest hover:underline p-1">Restore</button>
                                            <button @click="forceDeleteEvent(event.id)" class="text-red-600 text-sm font-black uppercase tracking-widest hover:underline p-1">Delete</button>
                                        </template>
                                        <template v-else>
                                            <button @click="manageGuests(event)" class="text-indigo-600 text-sm font-black uppercase tracking-widest hover:underline p-1">Manage Guests &rarr;</button>
                                            <button @click="archiveEvent(event.id)" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Archive Event">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div v-if="filteredEvents.length === 0" class="col-span-full py-16 text-center bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                                <CalendarIcon class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                                <h4 class="font-black text-slate-400">No {{ eventFilters }} events listed</h4>
                                <p class="text-xs text-slate-400">Use the form above to deploy a new campus event.</p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: FAMILY -->
                    <div v-if="activeTab === 'family'" class="bg-white rounded-xl shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <div>
                                <h3 class="text-lg font-black text-slate-800">Family & Dependents</h3>
                                <p class="text-sm text-slate-500">Registered family members eligible for quick access.</p>
                            </div>
                            <button class="text-sm text-indigo-600 font-medium hover:underline">Sync from HR &rarr;</button>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="fam in dependents" :key="fam.id" class="p-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <div class="h-10 w-10 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center font-black text-sm">
                                        {{ fam.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-slate-900">{{ fam.name }}</h4>
                                        <p class="text-xs text-slate-500">{{ fam.relationship }} of {{ fam.employee?.user?.name || 'Unknown' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span v-if="fam.is_emergency_contact" class="px-2 py-1 bg-red-100 text-red-600 text-sm font-black uppercase rounded-full">Emergency Contact</span>
                                    <button @click="checkInFamilyMember(fam)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 font-medium hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-xl shadow-slate-200/50">
                                        Check In
                                    </button>
                                </div>
                            </div>
                            <div v-if="dependents.length === 0" class="p-8 text-center text-slate-400">
                                No family members found.
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: KIOSK -->
                    <div v-if="activeTab === 'kiosk'" class="bg-white rounded-xl shadow-xl shadow-slate-200/50 border border-slate-200 p-8">
                        <h3 class="text-lg font-black text-slate-800 mb-6">Self-Service Kiosk Configuration</h3>
                        
                        <div class="flex items-center justify-between bg-slate-50 p-4 rounded-lg border border-slate-200 mb-6">
                            <div>
                                <div class="font-medium text-slate-900">Kiosk Link</div>
                                <div class="text-sm text-slate-500">Open this URL on the reception iPad</div>
                            </div>
                            <div class="flex space-x-2">
                                <input type="text" readonly value="https://hrms.local/kiosk/login" class="bg-white border-slate-300 rounded-md text-sm w-64">
                                <button class="px-3 py-1 bg-white border border-slate-300 rounded-md shadow-xl shadow-slate-200/50 text-sm font-medium hover:bg-slate-50">Copy</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <h4 class="font-medium text-slate-700 mb-3">Customization</h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" checked class="rounded text-indigo-600 border-slate-300 focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-slate-600">Company Logo on Splash Screen</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="rounded text-indigo-600 border-slate-300 focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-slate-600">Require NDA Signature</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" checked class="rounded text-indigo-600 border-slate-300 focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-slate-600">Take Visitor Selfie</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-slate-700 mb-3">Preview</h4>
                                <div class="aspect-video bg-slate-900 rounded-lg flex items-center justify-center text-white border-4 border-slate-300">
                                    <div class="text-center">
                                        <div class="text-2xl font-light">Welcome to</div>
                                        <div class="text-3xl font-black mt-1">Acme Corp</div>
                                        <button class="mt-4 px-6 py-2 bg-white text-black rounded-full text-sm font-black">Check In</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: ANALYTICS -->
                     <div v-if="activeTab === 'analytics'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Overstay Matrix -->
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 flex flex-col">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="p-2 bg-red-50 text-red-600 rounded-xl"><ShieldExclamationIcon class="w-5 h-5"/></div>
                                    <h4 class="font-black text-slate-800">Overstay Matrix</h4>
                                </div>
                                <div class="h-48 relative">
                                    <BaseChart type="doughnut" :data="overstayData" :options="{ cutout: '70%', plugins: { legend: { display: false } } }" />
                                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                        <span class="text-2xl font-black text-slate-800">{{ props.analytics?.overstay_stats?.overstayed || 0 }}</span>
                                        <span class="text-sm font-black text-slate-400 uppercase">Violations</span>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                     <div class="flex justify-between text-xs font-black text-slate-500">
                                         <span>Total Completed</span>
                                         <span class="text-slate-900">{{ (props.analytics?.overstay_stats?.on_time || 0) + (props.analytics?.overstay_stats?.overstayed || 0) }}</span>
                                     </div>
                                </div>
                            </div>

                            <!-- Heatmap -->
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 md:col-span-2 flex flex-col">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><ChartBarIcon class="w-5 h-5"/></div>
                                    <h4 class="font-black text-slate-800">Facility Heatmap (Peak Hours)</h4>
                                </div>
                                <div class="h-48">
                                    <BaseChart type="line" :data="heatmapData" :options="{ scales: { y: { display: false }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }" />
                                </div>
                            </div>
                        </div>

                        <!-- NEW: TOP INSIGHTS SECTION -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Top Hosts -->
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><UserGroupIcon class="w-5 h-5"/></div>
                                    <h4 class="font-black text-slate-800">Top Hosts (Most Visits)</h4>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="host in props.analytics?.top_insights?.most_active_hosts" :key="host.id" class="flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-black text-slate-500 text-xs">
                                                {{ host.name.charAt(0) }}
                                            </div>
                                            <div class="text-sm font-black text-slate-700">{{ host.name }}</div>
                                        </div>
                                        <div class="text-xs font-black text-slate-400 bg-slate-50 px-2 py-1 rounded group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                            {{ host.visitor_passes_count }} SESSIONS
                                        </div>
                                    </div>
                                    <div v-if="!props.analytics?.top_insights?.most_active_hosts?.length" class="text-xs text-slate-400 text-center py-4 italic">
                                        No host data recorded yet.
                                    </div>
                                </div>
                            </div>

                            <!-- Frequent Visitors -->
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="p-2 bg-blue-50 text-blue-600 rounded-xl"><ArrowTrendingUpIcon class="w-5 h-5"/></div>
                                    <h4 class="font-black text-slate-800">Frequent Visitors</h4>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="visitor in props.analytics?.top_insights?.frequent_visitors" :key="visitor.id" class="flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center font-black text-blue-600 text-xs">
                                                {{ (visitor.passes_count > 10 ? '👑' : visitor.name.charAt(0)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-slate-700">{{ visitor.name }}</div>
                                                <div class="text-sm text-slate-400 font-black uppercase">{{ visitor.company || 'Private' }}</div>
                                            </div>
                                        </div>
                                        <div class="text-xs font-black text-slate-400 bg-slate-50 px-2 py-1 rounded group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                            {{ visitor.passes_count }} VISITS
                                        </div>
                                    </div>
                                    <div v-if="!props.analytics?.top_insights?.frequent_visitors?.length" class="text-xs text-slate-400 text-center py-4 italic">
                                        No frequent visitors yet.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Host Responsiveness -->
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-green-50 text-green-600 rounded-xl"><ArrowTrendingUpIcon class="w-5 h-5"/></div>
                                        <h4 class="font-black text-slate-800">Host Responsiveness</h4>
                                    </div>
                                    <span class="text-xs font-black text-green-600 bg-green-50 px-2 py-1 rounded">AVG {{ Math.round(props.analytics?.host_responsiveness || 0) }}m</span>
                                </div>
                                <p class="text-sm text-slate-500">Average time taken by hosts to acknowledge visitor arrivals at reception.</p>
                                <div class="mt-4 w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                     <div class="bg-green-500 h-full" :style="{ width: Math.min(100, (60 / Math.max(1, props.analytics?.host_responsiveness || 1)) * 10) + '%' }"></div>
                                </div>
                            </div>

                            <!-- Denied Access Ledger -->
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="p-2 bg-slate-900 text-white rounded-xl"><ShieldExclamationIcon class="w-5 h-5"/></div>
                                    <h4 class="font-black text-slate-800">Denied Access Ledger</h4>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="text-sm font-black text-slate-400 uppercase border-b border-slate-50">
                                                <th class="pb-2">Visitor</th>
                                                <th class="pb-2">Reason</th>
                                                <th class="pb-2">Time</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            <tr v-for="log in props.analytics?.denied_logs" :key="log.id" class="border-b border-slate-50 last:border-0">
                                                <td class="py-3 font-black text-slate-700">{{ log.visitor_name || 'Anonymous' }}</td>
                                                <td class="py-3">
                                                    <span class="px-2 py-0.5 bg-red-50 text-red-600 rounded-full font-black">{{ log.reason }}</span>
                                                </td>
                                                <td class="py-3 text-slate-400">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</td>
                                            </tr>
                                            <tr v-if="!props.analytics?.denied_logs?.length">
                                                <td colspan="3" class="py-8 text-center text-slate-300 font-medium">No access denials recorded</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        <!-- Info Modal -->
        <div v-if="showInfoModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showInfoModal = false">
            <div class="bg-white rounded-[2rem] shadow-2xl p-6 w-[500px] max-h-[85vh] overflow-y-auto animate-fade-in">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        <InformationCircleIcon class="h-6 w-6 text-indigo-500" />
                        {{ infoContent.title }}
                    </h2>
                    <button @click="showInfoModal = false" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>
                <div class="text-sm text-slate-600 whitespace-pre-line leading-relaxed bg-indigo-50 border border-indigo-100 rounded-xl p-5">
                    {{ infoContent.body }}
                </div>
                <div class="flex justify-end mt-6">
                    <button @click="showInfoModal = false" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-black hover:bg-indigo-700 transition-colors">
                        Understood
                    </button>
                </div>
            </div>
        </div>
        <!-- Success Status Toast Simulation -->
        <!-- ... -->

        <WebcamCapture 
            v-if="showWebcam" 
            @close="showWebcam = false" 
            @captured="(img) => { form.photo = img; showWebcam = false; }" 
        />

        <!-- Checkout Confirmation Modal -->
        <div v-if="showCheckoutConfirm" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-8 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-red-500"></div>
                <div class="w-20 h-20 bg-red-50 text-red-600 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                    <ClockIcon class="w-10 h-10" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">Confirm Checkout</h3>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">Are you sure you want to check out this visitor? This will record the exit time and terminate their session.</p>
                
                <div class="bg-slate-50 rounded-[2rem] p-4 mb-8 border border-slate-100 flex items-center justify-between">
                    <div class="text-left">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest">ID Management</div>
                        <div class="text-sm font-black text-slate-700">Badge Collected?</div>
                    </div>
                    <button @click="badgeCollectedOnCheckout = !badgeCollectedOnCheckout" 
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="badgeCollectedOnCheckout ? 'bg-indigo-600' : 'bg-slate-200'"
                    >
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="badgeCollectedOnCheckout ? 'translate-x-5' : 'translate-x-0'"
                        ></span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button @click="showCheckoutConfirm = false" class="py-3 px-4 bg-white border border-slate-200 text-slate-600 rounded-xl font-black hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button @click="executeCheckOut" class="py-3 px-4 bg-red-600 text-white rounded-xl font-black hover:bg-red-700 transition-colors shadow-lg shadow-red-100">
                        Confirm Exit
                    </button>
                </div>
            </div>
        </div>

        <!-- Professional Success Notification -->
        <Transition name="slide-fade">
            <div v-if="showSuccessNotification" class="fixed bottom-8 right-8 z-[100] bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-4 flex items-center gap-4 max-w-sm animate-slide-up">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-black text-slate-900">Success</div>
                    <div class="text-xs text-slate-500">{{ successMessage }}</div>
                </div>
                <button @click="showSuccessNotification = false" class="text-slate-300 hover:text-slate-500">&times;</button>
            </div>
        </Transition>
        <!-- Manage Guest Modal -->
        <div v-if="showEventGuestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-[2.5rem] shadow-xl max-w-4xl w-full overflow-hidden flex flex-col max-h-[85vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div class="flex items-center gap-3">
                         <div class="w-12 h-12 bg-indigo-600 text-white rounded-[2rem] flex items-center justify-center shadow-lg shadow-indigo-100">
                            <UserGroupIcon class="w-7 h-7" />
                         </div>
                         <div>
                            <h3 class="font-black text-slate-900 uppercase tracking-widest text-base">{{ selectedEvent?.title }}</h3>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-sm text-indigo-600 font-black uppercase bg-indigo-50 px-2 py-0.5 rounded">{{ selectedEvent?.status || 'Scheduled' }}</span>
                                <p class="text-sm text-slate-400 font-black uppercase">{{ selectedEvent?.location }} • {{ eventGuests.length }} Total Guests</p>
                            </div>
                         </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button v-if="selectedEvent?.status !== 'Completed'" @click="wrapUpEvent(selectedEvent.id)" class="px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm font-black uppercase hover:bg-red-100 transition-all flex items-center gap-2">
                             <CheckBadgeIcon class="w-4 h-4" />
                             <span>Wrap-up Event</span>
                        </button>
                        <button @click="getPerformanceData" class="px-4 py-2 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-xl text-sm font-black uppercase hover:bg-indigo-100 transition-all flex items-center gap-2">
                             <ChartBarIcon class="w-4 h-4" />
                             <span>Performance Analytics</span>
                        </button>
                        <label class="cursor-pointer px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-black uppercase text-slate-600 hover:bg-slate-50 flex items-center gap-2 transition-all">
                            <DocumentArrowUpIcon class="w-4 h-4 text-indigo-500" />
                            <span>Import CSV</span>
                            <input type="file" class="hidden" @change="e => { importForm.file = e.target.files[0]; submitImport(); }">
                        </label>
                        <button @click="showEventGuestModal = false" class="text-slate-400 hover:text-slate-600 p-2.5 bg-white rounded-xl border border-slate-100 transition-colors">&times;</button>
                    </div>
                </div>

                <div class="px-6 py-3 bg-white border-b border-slate-50 flex justify-between items-center">
                    <div class="relative w-72">
                        <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-2.5 text-slate-300" />
                        <input v-model="guestQuery" placeholder="Search guests by name, email or company..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border-transparent rounded-xl text-xs focus:ring-indigo-500 focus:bg-white transition-all">
                    </div>
                    <div v-if="selectedGuests.length > 0" class="flex items-center gap-2 animate-slide-in">
                        <span class="text-sm font-black text-indigo-600 uppercase mr-2">{{ selectedGuests.length }} Selective Actions:</span>
                        <button @click="applyBulkAction('resend_invite')" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-black uppercase hover:bg-indigo-700 transition-colors">Resend Invite</button>
                        <button @click="applyBulkAction('upgrade_vip')" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-black uppercase hover:bg-indigo-700 transition-colors">Upgrade to VIP</button>
                        <button @click="applyBulkAction('revoke')" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-sm font-black uppercase hover:bg-red-100 transition-colors">Revoke Access</button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-slate-50/20">
                    <div v-if="isLoadingGuests" class="flex flex-col items-center justify-center py-20">
                         <div class="w-10 h-10 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                         <span class="text-base font-black text-slate-400 uppercase mt-4 tracking-widest">Compiling Guest Data Hub...</span>
                    </div>
                    
                    <div v-else-if="eventGuests.length === 0" class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-slate-100">
                        <div class="w-20 h-20 bg-indigo-50 rounded-[2.5rem] flex items-center justify-center mb-6">
                             <UserPlusIcon class="w-10 h-10 text-indigo-200" />
                        </div>
                        <h4 class="font-black text-slate-800 text-lg">Event Guest Ledger Empty</h4>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto mt-2">Upload a CSV or manual check-in guests to start populating this enterprise event's dashboard.</p>
                        <label class="mt-6 px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-xl shadow-indigo-100 cursor-pointer hover:bg-indigo-700 transition-all">
                            Initiate Bulk Import
                            <input type="file" class="hidden" @change="e => { importForm.file = e.target.files[0]; submitImport(); }">
                        </label>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="guest in filteredModalGuests" :key="guest.id" 
                             @click="toggleGuestSelection(guest.id)"
                             :class="selectedGuests.includes(guest.id) ? 'border-indigo-500 bg-indigo-50/30' : 'border-slate-100 bg-white hover:border-indigo-200 hover:shadow-lg'"
                             class="p-4 rounded-[2.5rem] border shadow-xl shadow-slate-200/50 flex items-center gap-4 transition-all cursor-pointer group relative">
                             
                             <div class="w-14 h-14 rounded-[2rem] bg-slate-50 overflow-hidden flex-shrink-0 border border-slate-100 shadow-inner group-hover:scale-105 transition-transform">
                                <img v-if="guest.visitor?.photo_path" :src="guest.visitor.photo_path" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300 font-black text-sm bg-white uppercase">
                                    {{ guest.visitor?.name.charAt(0) }}
                                </div>
                             </div>
                             <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-0.5">
                                    <h5 class="font-black text-slate-900 text-lg truncate pr-2 tracking-tight">{{ guest.visitor?.name }}</h5>
                                    <div class="flex gap-1">
                                        <span v-if="guest.visitor?.is_blacklisted" class="text-xs font-black px-1.5 py-0.5 rounded-lg bg-red-100 text-red-600 uppercase flex items-center gap-1">
                                            <ShieldExclamationIcon class="w-2.5 h-2.5" />
                                            Risk
                                        </span>
                                        <span v-if="guest.status === 'Checked-In'" class="text-xs font-black px-1.5 py-0.5 rounded-lg bg-green-50 text-green-600 uppercase">Arrived</span>
                                        <span v-else-if="guest.status === 'Blacklisted'" class="text-xs font-black px-1.5 py-0.5 rounded-lg bg-red-50 text-red-600 uppercase">Blocked</span>
                                        <span v-else class="text-xs font-black px-1.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 uppercase">Invited</span>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-400 font-black uppercase truncate tracking-wider mb-2">{{ guest.visitor?.company || 'External Member' }}</p>
                                <div class="flex items-center justify-between">
                                    <span v-if="guest.check_in_at" class="text-sm font-black text-slate-500 flex items-center gap-1">
                                        <div class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></div>
                                        {{ new Date(guest.check_in_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) }}
                                    </span>
                                    <span v-else class="text-sm font-black text-slate-400">Scheduled Visit</span>
                                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                         <button @click.stop="window.open(route('visitors.print', guest.id), '_blank')" class="p-1 hover:text-indigo-600 transition-colors" title="Print Pass">
                                            <PrinterIcon class="w-3.5 h-3.5" />
                                         </button>
                                         <button class="p-1 hover:text-indigo-600 transition-colors" title="Guest Details">
                                            <ArrowsPointingOutIcon class="w-3.5 h-3.5" />
                                         </button>
                                    </div>
                                </div>
                             </div>
                             <div v-if="selectedGuests.includes(guest.id)" class="absolute -top-1.5 -right-1.5 bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                                <CheckIcon class="w-3.5 h-3.5" />
                             </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-white flex justify-between items-center">
                    <div class="flex items-center gap-6">
                         <div class="flex flex-col">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Event Capacity Performance</span>
                            <div class="flex items-center gap-3">
                                <div class="w-64 h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-50 shadow-inner">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full shadow-lg transition-all duration-1000" :style="{ width: (eventGuests.length / (selectedEvent?.guest_limit || 1) * 100) + '%' }"></div>
                                </div>
                                <span class="text-xs font-black text-indigo-600 tracking-tighter">{{ Math.round(eventGuests.length / (selectedEvent?.guest_limit || 1) * 100) }}%</span>
                            </div>
                         </div>
                         <div class="h-8 w-px bg-slate-100"></div>
                         <div class="flex gap-4">
                            <div class="text-center">
                                <p class="text-xs font-black text-slate-400 uppercase">Confirmed</p>
                                <p class="text-xs font-black text-slate-900 leading-none mt-1">{{ eventGuests.length }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-black text-slate-400 uppercase">Pending</p>
                                <p class="text-xs font-black text-amber-500 leading-none mt-1">0</p>
                            </div>
                         </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="showEventGuestModal = false" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-xl shadow-gray-200 hover:bg-black transition-all">Exit Control Tower</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Performance Analytics Modal -->
        <div v-if="showEventAnalytics" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-fade-in">
            <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-600 text-white rounded-[2rem] flex items-center justify-center shadow-lg">
                            <ChartBarIcon class="w-7 h-7" />
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 uppercase tracking-widest text-base">Event Performance Insights</h3>
                            <p class="text-sm text-slate-400 font-black uppercase">{{ selectedEvent?.title }}</p>
                        </div>
                    </div>
                    <button @click="showEventAnalytics = false" class="text-slate-400 hover:text-slate-600 p-2.5 bg-white rounded-xl border border-slate-100 transition-colors">&times;</button>
                </div>

                <div class="p-8">
                    <div v-if="isLoadingPerformance" class="flex flex-col items-center justify-center py-12">
                         <div class="w-10 h-10 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                         <span class="text-base font-black text-slate-400 uppercase mt-4 tracking-widest">Generating Insight Engine...</span>
                    </div>
                    <div v-else-if="performanceData" class="grid grid-cols-2 gap-6">
                        <div class="bg-indigo-50/50 p-6 rounded-[2.5rem] border border-indigo-100">
                            <p class="text-sm font-black text-indigo-400 uppercase tracking-widest mb-1">Total Conversion</p>
                            <h4 class="text-3xl font-black text-indigo-600">{{ Math.round((performanceData.total_arrived / (performanceData.total_invited || 1)) * 100) }}%</h4>
                            <p class="text-sm text-indigo-400 font-black uppercase mt-2">{{ performanceData.total_arrived }} / {{ performanceData.total_invited }} Guests Arrived</p>
                        </div>
                        <div class="bg-amber-50/50 p-6 rounded-[2.5rem] border border-amber-100">
                            <p class="text-sm font-black text-amber-400 uppercase tracking-widest mb-1">No-Show Velocity</p>
                            <h4 class="text-3xl font-black text-amber-600">{{ performanceData.no_show_rate }}%</h4>
                            <p class="text-sm text-amber-400 font-black uppercase mt-2">Retention Risk Factor</p>
                        </div>
                        <div class="bg-indigo-50/50 p-6 rounded-[2.5rem] border border-indigo-100 col-span-2">
                             <div class="flex justify-between items-end mb-4">
                                <div>
                                    <p class="text-sm font-black text-indigo-400 uppercase tracking-widest mb-1">Arrival Density Flow</p>
                                    <h4 class="text-xl font-black text-indigo-600">Peak Hour: {{ performanceData.peak_arrival_hour ? performanceData.peak_arrival_hour + ':00' : 'N/A' }}</h4>
                                </div>
                                <ArrowTrendingUpIcon class="w-8 h-8 text-indigo-200" />
                             </div>
                             <!-- Simple CSS Bar Chart -->
                             <div class="flex items-end gap-1.5 h-20 px-2 mt-4">
                                <div v-for="(count, hour) in performanceData.arrival_density" :key="hour" 
                                     class="flex-1 bg-indigo-200 rounded-t-lg transition-all hover:bg-indigo-400 group relative"
                                     :style="{ height: (count / (performanceData.total_arrived || 1) * 100) + '%' }">
                                     <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-sm font-black px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        {{ hour }}:00 - {{ count }}
                                     </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex justify-end">
                    <button @click="showEventAnalytics = false" class="px-8 py-3 bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-widest shadow-xl shadow-gray-200 hover:bg-black transition-all">Close Insights</button>
                </div>
            </div>
        </div>

        <!-- Edit Details Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-[2rem] shadow-xl max-w-lg w-full overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-black text-lg text-slate-800">Edit Visitor Details</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <div class="p-6">
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="text-sm font-black text-slate-700">Visitor Name</label>
                            <input v-model="editForm.name" type="text" required class="w-full mt-1 border-slate-300 rounded-lg">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-black text-slate-700">Phone</label>
                                <input v-model="editForm.phone" type="text" class="w-full mt-1 border-slate-300 rounded-lg">
                            </div>
                            <div>
                                <label class="text-sm font-black text-slate-700">Email Address</label>
                                <input v-model="editForm.email" type="email" class="w-full mt-1 border-slate-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-black text-slate-700">Company</label>
                            <input v-model="editForm.company" type="text" class="w-full mt-1 border-slate-300 rounded-lg">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-black text-slate-700">Whom to Meet</label>
                                <select v-model="editForm.host_id" required class="w-full mt-1 border-slate-300 rounded-lg">
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.user?.id">
                                        {{ emp.user?.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-black text-slate-700">Expected Duration (Mins)</label>
                                <input v-model="editForm.expected_duration" type="number" required class="w-full mt-1 border-slate-300 rounded-lg">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-slate-600 font-black hover:bg-slate-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-indigo-600 text-white font-black rounded-lg hover:bg-indigo-700">
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
