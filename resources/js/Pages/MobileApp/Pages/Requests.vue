<template>
    <AppLayout>
        <div class="px-6 space-y-7 pb-32 pt-4">
            <header>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Request Hub</h2>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Initiate Operational Requests</p>
            </header>

            <!-- Request Form Section -->
            <div class="nature-card p-6 bg-white shadow-xl shadow-emerald-900/5 border-emerald-50 space-y-6">
                <div class="space-y-2">
                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Protocol Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            v-for="type in leaveTypes" :key="type.id"
                            @click="form.leave_type_id = type.id"
                            class="py-3 px-2 rounded-xl text-[9px] font-black uppercase tracking-widest border transition-all"
                            :class="form.leave_type_id === type.id ? 'bg-slate-900 text-emerald-400 border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent'"
                        >
                            {{ type.name }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Start Cycle</label>
                        <input v-model="form.start_date" type="date" class="nature-input text-[10px]" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">End Cycle</label>
                        <input v-model="form.end_date" type="date" class="nature-input text-[10px]" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Justification</label>
                    <textarea v-model="form.reason" rows="3" placeholder="Define request parameters..." class="nature-input text-xs italic"></textarea>
                </div>

                <button @click="submitRequest" :disabled="submitting" class="nature-button-primary">
                    {{ submitting ? 'Transmitting...' : 'Submit Protocol Request' }}
                </button>
            </div>

            <!-- History Summary -->
            <div class="space-y-4">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] px-2 italic">Historical Decisions</h3>
                <div v-for="req in requests" :key="req.id" class="nature-card p-4 flex justify-between items-center bg-white border-white/50">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-white flex items-center justify-center text-emerald-600 text-[10px] font-black shadow-inner italic">
                            {{ req.total_days }}d
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ req.leave_type?.name }}</h4>
                            <p class="text-[8px] text-slate-400 font-bold tracking-tight mt-1">
                                {{ formatDate(req.start_date) }} - {{ formatDate(req.end_date) }}
                            </p>
                        </div>
                    </div>
                    <span :class="getStatusClass(req.status)" class="nature-pill-sm">{{ req.status }}</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '../App.vue';
import axios from 'axios';

const leaveTypes = ref([]);
const requests = ref([]);
const loading = ref(true);
const submitting = ref(false);

const form = ref({
    leave_type_id: null,
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    reason: ''
});

const fetchLeaveData = async () => {
    try {
        const [typesRes, historyRes] = await Promise.all([
            axios.get('/api/mobile/v1/leave/types'),
            axios.get('/api/mobile/v1/leave')
        ]);
        leaveTypes.value = typesRes.data;
        requests.value = historyRes.data;
    } catch (err) { console.error(err); }
    finally { loading.value = false; }
};

const submitRequest = async () => {
    if (!form.value.leave_type_id) return alert("Select protocol type.");
    submitting.value = true;
    try {
        await axios.post('/api/mobile/v1/leave/apply', form.value);
        form.value.reason = '';
        fetchLeaveData();
        alert("Request Transmitted.");
    } catch (err) { alert("Failed."); }
    finally { submitting.value = false; }
};

const getStatusClass = (status) => {
    if (status === 'Approved') return 'status-approved';
    if (status === 'Rejected') return 'status-rejected';
    return 'status-pending';
};

const formatDate = (date) => new Date(date).toLocaleDateString([], { month: 'short', day: 'numeric' });
onMounted(fetchLeaveData);
</script>
