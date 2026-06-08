<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Infrastructure" activeTab="biometric" v-bind="$props">
    <Head v-if="!embedded" title="Biometric Nodes" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-6 pb-12">
        <!-- Compact Infrastructure Intelligence Command Bar -->
        <div class="lg:h-14 bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200 p-2 shadow-sm flex flex-col lg:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-3 px-2 w-full lg:w-auto">
                <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                    <i class="fas fa-microchip text-base"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Biometric Mesh</h2>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5">IoT Infrastructure Protocol</p>
                </div>
                
                <!-- Mobile Stats (Inline) -->
                <div class="lg:hidden flex items-center gap-2 ml-auto bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                     <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                     <span class="text-sm font-black text-slate-600">{{ devices.filter(d => d.status === 'online').length }} ON</span>
                </div>
            </div>

            <div class="flex items-center gap-2 pr-1 w-full lg:w-auto overflow-x-auto no-scrollbar pb-1 lg:pb-0">
                <div class="hidden lg:flex items-center gap-3 px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg h-10 shadow-inner">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Active</span>
                        <span class="text-base font-black text-emerald-600 leading-none">{{ devices.filter(d => d.status === 'online').length }}</span>
                    </div>
                    <div class="w-px h-2.5 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Offline</span>
                        <span class="text-base font-black text-rose-600 leading-none">{{ devices.filter(d => d.status !== 'online').length }}</span>
                    </div>
                </div>

                <button @click="openCreateModal" class="flex-shrink-0 h-10 px-6 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 shadow-md active:scale-95 group ml-auto lg:ml-0">
                    <i class="fas fa-plus text-sm group-hover:rotate-90 transition-transform"></i>
                    Inject Node
                </button>
            </div>
        </div>

        <!-- Telemetry Grid Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
            <div 
                v-for="device in devices" 
                :key="device.id" 
                class="group relative bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300 flex flex-col"
            >
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div 
                            class="w-9 h-9 rounded-lg flex items-center justify-center border transition-all shadow-inner"
                            :class="device.status === 'online' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-200'"
                        >
                            <i class="fas fa-tower-broadcast text-base"></i>
                        </div>
                        <div class="truncate flex-1 min-w-0">
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-tight truncate leading-none">{{ device.name }}</h3>
                            <!-- <p class="text-xs font-black text-slate-400 font-mono mt-1.5 leading-none uppercase tracking-widest truncate break-all" :title="device.ip_address">{{ device.ip_address }}</p> -->
                        </div>
                    </div>
                    
                    <div class="flex gap-1.5">
                        <button @click="pingDevice(device)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-100 transition-all shadow-sm active:scale-95">
                            <i class="fas fa-sync text-xs" :class="{'animate-spin': device.status === 'pinging'}"></i>
                        </button>
                        <button @click="editDevice(device)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-100 transition-all shadow-sm active:scale-95">
                            <i class="fas fa-sliders text-xs"></i>
                        </button>
                        <button @click="deleteDevice(device.id)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 transition-all shadow-sm active:scale-95">
                            <i class="fas fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-slate-50/50 p-2 rounded-lg border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Serial Index</p>
                        <p class="text-sm font-black text-slate-600 uppercase truncate leading-none">{{ device.serial_number }}</p>
                    </div>
                    <div class="bg-slate-50/50 p-2 rounded-lg border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5 text-right">Zone</p>
                        <p class="text-sm font-black text-slate-600 uppercase text-right truncate leading-none">{{ device.zone?.name || 'GLOBAL' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <div v-if="device.port" class="px-2 py-1 bg-slate-100 rounded text-xs font-black text-slate-500 uppercase tracking-widest">
                        Port: {{ device.port }}
                    </div>
                    <div v-if="device.protocol" class="px-2 py-1 bg-slate-100 rounded text-xs font-black text-slate-500 uppercase tracking-widest">
                        {{ device.protocol }}
                    </div>
                    <div v-if="device.location_name" class="px-2 py-1 bg-slate-100 rounded text-xs font-black text-slate-500 uppercase tracking-widest">
                        Loc: {{ device.location_name }}
                    </div>
                </div>
                 <div class="flex flex-wrap gap-2 mb-4">
                    <div v-if="device.port" class="px-2 py-1 bg-slate-100 rounded text-xs font-black text-slate-500 uppercase tracking-widest">
                         <p class="text-xs font-black text-slate-400 font-mono mt-1.5 leading-none uppercase tracking-widest truncate break-all" :title="device.ip_address">{{ device.ip_address }}</p> 
                    </div>
                </div>

                <div v-if="device.description" class="mb-4">
                    <p class="text-sm text-slate-400 font-bold uppercase tracking-tight line-clamp-2 leading-tight">
                        {{ device.description }}
                    </p>
                </div>

                <div class="mt-auto pt-4 border-t border-slate-100 flex justify-between items-center">
                    <span 
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-black uppercase tracking-widest border shadow-sm" 
                        :class="device.status === 'online' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'"
                    >
                        <i class="fas fa-circle text-[5px] mr-1.5" :class="device.status === 'online' ? 'animate-pulse' : ''"></i>
                        {{ (device.status || 'OFFLINE').toUpperCase() }}
                    </span>

                    <span v-if="device.is_office_wifi" class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-emerald-600 text-white shadow-sm flex items-center gap-1.5 ml-2">
                        <i class="fas fa-wifi text-[8px]"></i>
                        OFFICE WIFI
                    </span>
                    
                    <div class="text-right">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Last Pulse</p>
                        <p class="text-sm font-black text-slate-600 uppercase leading-none">
                            {{ device.last_sync_at ? new Date(device.last_sync_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'PENDING' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Infrastructure Injection Modal -->
        <PremiumModal 
            :show="showModal" 
            @close="showModal = false" 
            title="Node Configuration"
            subtitle="Infrastructure Mapping Protocol"
            icon="fa-microchip"
            maxWidth="xl"
        >
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Node Designation</label>
                        <input v-model="form.name" type="text" placeholder="e.g. CORE-ALPHA-01" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-tight h-12" :class="{'border-rose-500 ring-rose-500/10': errors.name}">
                        <p v-if="errors.name" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.name[0] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Access Channel (IP/Host)</label>
                            <input v-model="form.ip_address" type="text" placeholder="192.168.1.1 or iot-node.local" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-12" :class="{'border-rose-500 ring-rose-500/10': errors.ip_address}">
                            <p v-if="errors.ip_address" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.ip_address[0] }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Port</label>
                            <input v-model="form.port" type="number" placeholder="4370" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-12" :class="{'border-rose-500 ring-rose-500/10': errors.port}">
                            <p v-if="errors.port" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.port[0] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Serial Index</label>
                            <input v-model="form.serial_number" type="text" placeholder="SN-XXXX" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-12" :class="{'border-rose-500 ring-rose-500/10': errors.serial_number}">
                            <p v-if="errors.serial_number" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.serial_number[0] }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Mapping Alias (Location)</label>
                            <input v-model="form.location_name" type="text" placeholder="e.g. MAIN LOBBY - NORTH" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-12" :class="{'border-rose-500 ring-rose-500/10': errors.location_name}">
                            <p v-if="errors.location_name" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.location_name[0] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Auth ID (Username)</label>
                            <input v-model="form.username" type="text" placeholder="ADMIN" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-12" :class="{'border-rose-500 ring-rose-500/10': errors.username}">
                            <p v-if="errors.username" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.username[0] }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Security Key (Password)</label>
                            <input v-model="form.password" type="password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-12" :class="{'border-rose-500 ring-rose-500/10': errors.password}">
                            <p v-if="errors.password" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.password[0] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Infrastructure Protocol</label>
                            <select v-model="form.protocol" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all cursor-pointer h-12 appearance-none tracking-widest leading-none" :class="{'border-rose-500 ring-rose-500/10': errors.protocol}">
                                <option value="TCP">TCP/IP Protocol</option>
                                <option value="UDP">UDP Stream</option>
                                <option value="HTTP">REST/HTTP Mesh</option>
                                <option value="WS">Websocket Protocol</option>
                            </select>
                            <p v-if="errors.protocol" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.protocol[0] }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Deployment Zone</label>
                            <select v-model="form.attendance_zone_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all cursor-pointer h-12 appearance-none tracking-widest leading-none" :class="{'border-rose-500 ring-rose-500/10': errors.attendance_zone_id}">
                                <option value="">GLOBAL ARCHITECTURE</option>
                                <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name.toUpperCase() }}</option>
                            </select>
                            <p v-if="errors.attendance_zone_id" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.attendance_zone_id[0] }}</p>
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-200 flex items-center justify-center text-emerald-600 shadow-sm">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Office WiFi Node</h4>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Allow automatic IP-based check-in</p>
                            </div>
                        </div>
                        <Toggle v-model="form.is_office_wifi" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">System Description</label>
                        <textarea v-model="form.description" rows="2" placeholder="HARDWARE SPECIFICATIONS OR DEPLOYMENT NOTES..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase resize-none" :class="{'border-rose-500 ring-rose-500/10': errors.description}"></textarea>
                        <p v-if="errors.description" class="text-[10px] font-black text-rose-500 uppercase px-1">{{ errors.description[0] }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <button @click="showModal = false" class="px-6 py-2.5 rounded-lg text-sm font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all active:scale-95">
                            ABORT
                        </button>
                        <button @click="submit" class="h-10 px-8 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest shadow-md hover:bg-slate-800 active:scale-95 transition-all flex items-center gap-2 group">
                            <i class="fas fa-save text-sm text-emerald-400 group-hover:scale-110 transition-transform"></i>
                            <span>{{ isEditing ? 'UPDATE NODE' : 'DEPLOY NODE' }}</span>
                        </button>
                    </div>
                </div>
        </PremiumModal>
    </div>
  </component>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import Toggle from '@/Components/Toggle.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    biometric_devices: { type: Array, default: () => [] },
    attendance_zones: { type: Array, default: () => [] }
});

const devices = ref(props.biometric_devices);
const zones = ref(props.attendance_zones);

watch(() => props.biometric_devices, (newVal) => { if (newVal) devices.value = newVal; }, { immediate: true });
watch(() => props.attendance_zones, (newVal) => { if (newVal) zones.value = newVal; }, { immediate: true });

const loading = ref(false);
const showModal = ref(false);
const errors = ref({});
const form = ref({ 
    name: '', 
    serial_number: '', 
    ip_address: '', 
    port: 4370,
    username: '',
    password: '',
    protocol: 'TCP',
    location_name: '',
    description: '',
    heartbeat_interval: 60,
    attendance_zone_id: '', 
    is_active: true,
    is_office_wifi: false
});
const isEditing = ref(false);
const editingId = ref(null);
const toast = useToastStore();

const fetchDevices = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('admin.attendance.devices.data'));
        devices.value = res.data.devices;
        zones.value = res.data.zones;
    } catch (e) {
        toast.error("Telemetry fetch failed");
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    errors.value = {};
    form.value = { 
        name: '', 
        serial_number: '', 
        ip_address: '', 
        port: 4370,
        username: '',
        password: '',
        protocol: 'TCP',
        location_name: '',
        description: '',
        heartbeat_interval: 60,
        attendance_zone_id: '', 
        is_active: true,
        is_office_wifi: false
    };
    showModal.value = true;
};

const editDevice = (device) => {
    isEditing.value = true;
    editingId.value = device.id;
    errors.value = {};
    form.value = { ...device };
    showModal.value = true;
};

const submit = async () => {
    errors.value = {};
    try {
        if (isEditing.value) {
            await axios.put(route('admin.attendance.devices_resource.update', editingId.value), form.value);
            toast.success('Node synchronized');
        } else {
            await axios.post(route('admin.attendance.devices_resource.store'), form.value);
            toast.success('Node deployed');
        }
        showModal.value = false;
        fetchDevices();
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors;
            toast.error('Validation Error: Check Node Parameters');
        } else {
            toast.error('Deployment failure');
        }
    }
};

const deleteDevice = async (id) => {
    if (!confirm('Decommission this node?')) return;
    try {
        await axios.delete(route('admin.attendance.devices_resource.destroy', id));
        toast.success('Node dismantled');
        fetchDevices();
    } catch (e) {
        toast.error('Dismantling failure');
    }
};

const pingDevice = async (device) => {
    const originalStatus = device.status;
    device.status = 'pinging';
    try {
        const res = await axios.post(route('admin.attendance.devices.ping', device.id));
        device.status = res.data.status;
        toast.success(res.data.status === 'online' ? "Node Pulse Detected" : "Node silent");
    } catch (e) {
        device.status = originalStatus;
        toast.error('Pulse check failed');
    }
};

onMounted(fetchDevices);
</script>
