<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Sector Registry Console" activeTab="zones" v-bind="$props">
    <Head v-if="!embedded" title="Geo-Spatial Infrastructure Console" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-12 animate-fade-in relative z-10 pb-24">
        <!-- Geographical Intelligence Command Bar -->
        <div class="h-14 bg-white/80 backdrop-blur-md rounded-xl border border-slate-200 p-2 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                    <i class="fas fa-satellite-dish text-base"></i>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-tight leading-none">Sector Registry</h2>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1.5">Geo-Spatial Infrastructure</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-3 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="text-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block leading-none mb-1">Online</span>
                        <span class="text-base font-bold text-emerald-600 block leading-none">{{ zones.length }}</span>
                    </div>
                </div>

                <button @click="openCreateModal" class="h-9 px-4 bg-slate-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-slate-800 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-sm text-emerald-400"></i>
                    New Sector
                </button>
            </div>
        </div>

        <!-- Spatial Telemetry Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="zone in zones" 
                :key="zone.id" 
                class="group relative bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col overflow-hidden"
            >
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center transition-all group-hover:rotate-6 shadow-sm">
                            <i class="fas fa-location-dot text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 tracking-tight group-hover:text-emerald-600 transition-colors uppercase leading-none">{{ zone.name }}</h3>
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">BIND:</span>
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-tighter">{{ zone.location?.name || 'ORPHAN' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-1">
                        <a :href="getMapLink(zone.latitude, zone.longitude)" target="_blank" class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-600 transition-all">
                            <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                        <button @click="editZone(zone)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-600 transition-all">
                            <i class="fas fa-sliders text-xs"></i>
                        </button>
                        <button @click="deleteZone(zone.id)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-300 hover:text-rose-500 transition-all">
                            <i class="fas fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 relative z-10">
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-none">Coord Alpha</p>
                        <p class="text-sm font-bold text-slate-600 uppercase tracking-widest font-mono">{{ zone.latitude }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-none text-right">Coord Beta</p>
                        <p class="text-sm font-bold text-slate-600 uppercase tracking-widest text-right font-mono">{{ zone.longitude }}</p>
                    </div>
                </div>

                <div class="mt-auto pt-4 border-t border-slate-100 flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-2">
                         <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                            <i class="fas fa-bullseye text-xs"></i>
                         </div>
                         <div>
                            <p class="text-xs font-bold text-slate-300 uppercase tracking-widest leading-none mb-0.5">Radius</p>
                            <span class="text-sm font-bold text-emerald-600 uppercase tracking-widest">{{ zone.radius_meters }}M</span>
                         </div>
                    </div>
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm">
                        <i class="fas fa-satellite text-xs mr-1.5"></i>
                        ACTIVE
                    </span>
                </div>
            </div>
        </div>

        <!-- Infrastructure Injection Modal -->
        <PremiumModal 
            :show="showModal" 
            @close="showModal = false" 
            :title="isEditing ? 'Refine Sector' : 'Deploy Sector'" 
            subtitle="Geo-Fence Configuration Protocol"
            icon="fa-location-crosshairs"
            max-width="xl"
        >
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Sector Designation</label>
                    <input v-model="form.name" type="text" placeholder="e.g. NORTH_CAMPUS_S1" class="w-full h-10 bg-slate-50 border-slate-200 rounded-lg py-2 px-4 text-base font-bold text-slate-700 focus:bg-white focus:ring-0 transition-all uppercase tracking-tight shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Latitudinal Anchor</label>
                        <input v-model="form.latitude" type="text" placeholder="12.9716" class="w-full h-10 bg-slate-50 border-slate-200 rounded-lg py-2 px-4 text-base font-bold text-slate-700 focus:bg-white focus:ring-0 transition-all shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Longitudinal Anchor</label>
                        <input v-model="form.longitude" type="text" placeholder="77.5946" class="w-full h-10 bg-slate-50 border-slate-200 rounded-lg py-2 px-4 text-base font-bold text-slate-700 focus:bg-white focus:ring-0 transition-all shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Sphere Radius (Meters)</label>
                        <input v-model="form.radius_meters" type="number" class="w-full h-10 bg-slate-50 border-slate-200 rounded-lg py-2 px-4 text-base font-bold text-slate-700 focus:bg-white focus:ring-0 transition-all shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Parent Hierarchy</label>
                        <select v-model="form.location_id" class="w-full h-10 bg-slate-50 border-slate-200 rounded-lg py-2 px-4 text-base font-bold uppercase text-slate-600 focus:bg-white focus:ring-0 transition-all cursor-pointer shadow-sm">
                            <option value="">SELECT LOCATION</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name.toUpperCase() }}</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3 font-bold text-xs uppercase tracking-widest">
                    <button @click="showModal = false" class="px-6 py-2 text-slate-400 hover:text-slate-600 transition-all">
                        ABORT
                    </button>
                    <button @click="submit" class="px-8 py-2 bg-slate-900 text-white rounded-lg shadow-md hover:bg-slate-800 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save text-sm text-emerald-400"></i>
                        {{ isEditing ? 'Commit Changes' : 'Deploy Sector' }}
                    </button>
                </div>
            </div>
        </PremiumModal>
    </div>
  </component>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean
});

const zones = ref([]);
const locations = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const toast = useToastStore();

const form = ref({
    name: '',
    latitude: '',
    longitude: '',
    radius_meters: 100,
    location_id: '',
    is_active: true
});

const fetchZones = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('admin.attendance.zones.index'));
        zones.value = res.data.zones;
        locations.value = res.data.locations;
    } catch (e) {
        toast.error("Spatial data fetch failed");
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    form.value = { name: '', latitude: '', longitude: '', radius_meters: 100, location_id: '', is_active: true };
    showModal.value = true;
};

const editZone = (zone) => {
    isEditing.value = true;
    editingId.value = zone.id;
    form.value = { ...zone };
    showModal.value = true;
};

const submit = async () => {
    try {
        if (isEditing.value) {
            await axios.put(route('admin.attendance.zones.update', editingId.value), form.value);
            toast.success('Sector synchronized');
        } else {
            await axios.post(route('admin.attendance.zones.store'), form.value);
            toast.success('Sector deployed');
        }
        showModal.value = false;
        fetchZones();
    } catch (e) {
        toast.error('Sector mapping failure');
    }
};

const deleteZone = async (id) => {
    if (!confirm('Dismantle this sector?')) return;
    try {
        await axios.delete(route('admin.attendance.zones.destroy', id));
        toast.success('Sector deconstructed');
        fetchZones();
    } catch (e) {
        toast.error('Sector dismantling failure');
    }
};

const getMapLink = (lat, lng) => `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;

onMounted(fetchZones);
</script>
