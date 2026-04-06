<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    holidays: { type: [Array, Object], default: () => ({ data: [], meta: {} }) }
});

const toast = useToastStore();
const holidaysData = ref(props.holidays?.data ? props.holidays : { data: props.holidays || [], meta: {} });
const availableYears = ref([]);
const isPublished = ref(false);
const loading = ref(false);
const counts = ref({ total: 0, fixed: 0, restricted: 0 });
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const processing = ref(false);
const errors = ref({});

// Generic Confirm Modal
const showConfirmModal = ref(false);
const confirmTitle = ref('');
const confirmMessage = ref('');
const onConfirm = ref(null);

const openConfirmModal = (title, message, callback) => {
    confirmTitle.value = title;
    confirmMessage.value = message;
    onConfirm.value = callback;
    showConfirmModal.value = true;
};
const handleConfirm = () => {
    if (onConfirm.value) onConfirm.value();
    showConfirmModal.value = false;
};

const filters = ref({
    year: new Date().getFullYear(),
    type: ''
});

const form = ref({
    name: '',
    date: '',
    type: 'Fixed',
    is_recurring: false
});

// ...
const columns = {
    name: { label: 'Holiday Name' },
    date: { label: 'Date' },
    type: { label: 'Type' },
    actions: { label: 'Actions', align: 'right' }
};

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/holidays', {
            params: { 
                page, 
                year: filters.value.year,
                type: filters.value.type 
            },
            headers: { 'Accept': 'application/json' }
        });
        
        // Defensive: Extract holidays from Hub state or direct response
        const data = res.data.holidays || res.data.data || res.data;
        holidaysData.value = data.data ? data : { data: Array.isArray(data) ? data : [], meta: {} };
        
        availableYears.value = res.data.years || [new Date().getFullYear()];
        isPublished.value = res.data.is_published || false;
        counts.value = res.data.counts || { total: 0, fixed: 0, restricted: 0 };
    } catch (e) {
        toast.error("Failed to load holidays");
    } finally {
        loading.value = false;
    }
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if(filters.value.year) params.append('year', filters.value.year);
    if(filters.value.type) params.append('type', filters.value.type);
    return `/admin/holidays/export?${params.toString()}`;
});

const toggleVisibility = async (item) => {
    try {
        await axios.post(`/admin/holidays/${item.id}/toggle-visibility`, {
            year: filters.value.year
        });
        toast.success("Visibility updated");
        fetchData(holidaysData.value.current_page);
    } catch (e) {
        toast.error("Failed to update visibility");
    }
};

const publishCalendar = () => {
    openConfirmModal('Publish Calendar', `Are you sure you want to publish the holiday list for ${filters.value.year}? It will become visible to all employees.`, async () => {
        try {
            await axios.post('/admin/holidays/publish', { year: filters.value.year });
            toast.success("Calendar Published Successfully");
            fetchData(holidaysData.value.current_page);
        } catch(e) {
            toast.error("Failed to publish");
        }
    });
};

const unpublishCalendar = () => {
    openConfirmModal('Unpublish Calendar', `Are you sure you want to unpublish the holiday list for ${filters.value.year}? It will be hidden from employees.`, async () => {
        try {
            await axios.post('/admin/holidays/unpublish', { year: filters.value.year });
            toast.success("Calendar Unpublished Successfully");
            fetchData(holidaysData.value.current_page);
        } catch(e) {
            toast.error("Failed to unpublish");
        }
    });
};
// ...

const submit = async () => {
    processing.value = true;
    errors.value = {}; // Clear errors
    try {
        if (isEditing.value) {
            await axios.put(`/admin/holidays/${editId.value}`, form.value);
            toast.success("Holiday updated");
        } else {
            await axios.post('/admin/holidays', form.value);
            toast.success("Holiday created");
        }
        closeModal();
        fetchData(holidaysData.value.current_page);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            toast.error(e.response?.data?.message || "Operation failed");
        }
    } finally {
        processing.value = false;
    }
};




const showDeleteModal = ref(false);
const deleteId = ref(null);

const openCreateModal = () => {
    isEditing.value = false;
    editId.value = null;
    form.value = {
        name: '',
        date: '',
        type: 'Fixed',
        is_recurring: false
    };
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editId.value = item.id;
    form.value = {
        name: item.name,
        date: item.date ? item.date.substring(0, 10) : '',
        type: item.type,
        is_recurring: !!item.is_recurring
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showDeleteModal.value = false;
    // Reset form handled in openCreateModal usually, but good to clean errors
    errors.value = {};
};

const confirmDelete = (item) => {
    openConfirmModal('Delete Holiday', 'Are you sure you want to delete this holiday? This action cannot be undone.', async () => {
        try {
            await axios.delete(`/admin/holidays/${item.id}`);
            toast.success("Holiday deleted");
            fetchData(holidaysData.value.current_page);
        } catch (e) {
            toast.error("Failed to delete holiday");
        }
    });
};


const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};

onMounted(() => {
    fetchData();
});
</script>

<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Holiday Matrix Console" activeTab="holidays" v-bind="$props">
    <Head v-if="!embedded" title="Holiday Intel Pulse" />

    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-12 animate-fade-in relative z-10 pb-24">
        <!-- Premium Stats Area -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
             <div class="bg-white/70 backdrop-blur-xl p-4 md:p-6 rounded-2xl border border-white shadow-sm group hover:scale-[1.02] transition-all">
                  <div class="flex items-center gap-3 mb-2 md:mb-4">
                      <div class="w-8 h-8 md:w-10 md:h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg">
                          <i class="fas fa-calendar-days text-sm md:text-sm"></i>
                      </div>
                      <span class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em]">Scheduled</span>
                  </div>
                  <div class="text-2xl md:text-4xl font-black text-slate-800 tracking-tighter">{{ counts.total }}</div>
             </div>

             <div class="bg-indigo-600 p-4 md:p-6 rounded-2xl shadow-lg shadow-indigo-500/20 group hover:scale-[1.02] transition-all relative overflow-hidden">
                  <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12 blur-2xl"></div>
                  <div class="flex items-center gap-3 mb-2 md:mb-4 relative z-10">
                      <div class="w-8 h-8 md:w-10 md:h-10 bg-white/20 rounded-xl flex items-center justify-center text-white backdrop-blur-md">
                          <i class="fas fa-landmark text-sm md:text-sm"></i>
                      </div>
                      <span class="text-xs md:text-sm font-black text-white/60 uppercase tracking-[0.2em] md:tracking-[0.3em]">Public</span>
                  </div>
                  <div class="text-2xl md:text-4xl font-black text-white tracking-tighter relative z-10">{{ counts.fixed }}</div>
             </div>

             <div class="bg-white/70 backdrop-blur-xl p-4 md:p-6 rounded-2xl border border-white shadow-sm group hover:scale-[1.02] transition-all md:col-auto col-span-2">
                  <div class="flex items-center gap-3 mb-2 md:mb-4">
                      <div class="w-8 h-8 md:w-10 md:h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                          <i class="fas fa-star text-sm md:text-sm"></i>
                      </div>
                      <span class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em]">Restricted</span>
                  </div>
                  <div class="text-2xl md:text-4xl font-black text-slate-800 tracking-tighter">{{ counts.restricted }}</div>
             </div>
        </div>

        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center bg-white/70 backdrop-blur-2xl p-4 md:p-8 rounded-3xl border border-white shadow-sm transition-all hover:shadow-md gap-6">
            <div class="flex items-center gap-4 md:gap-8 px-2 md:px-4">
                <div class="w-12 h-12 md:w-20 md:h-20 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl md:rounded-[30px] flex items-center justify-center text-white shadow-lg md:rotate-3 shrink-0">
                    <i class="fas fa-umbrella-beach text-xl md:text-3xl"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 md:gap-4">
                        <h2 class="text-xl md:text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">Holiday Intel</h2>
                        <span v-if="isPublished" class="px-3 py-1 md:px-4 md:py-1.5 rounded-full text-xs md:text-sm font-black bg-emerald-50 text-emerald-600 border border-emerald-100 tracking-widest uppercase">Live</span>
                        <span v-else class="px-3 py-1 md:px-4 md:py-1.5 rounded-full text-xs md:text-sm font-black bg-amber-50 text-amber-600 border border-amber-100 tracking-widest uppercase">Draft</span>
                    </div>
                    <p class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-widest md:tracking-[0.4em] mt-1.5 md:mt-3 block">Configure global patterns</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 items-center w-full xl:w-auto pr-2">
                <div class="flex items-center gap-2 bg-white/80 rounded-2xl p-2 border border-slate-100 shadow-sm w-full sm:w-auto">
                    <select v-model="filters.year" @change="fetchData(1)" class="flex-1 bg-transparent border-none text-sm md:text-sm font-black text-slate-600 focus:ring-0 px-2 md:px-4 py-2 uppercase tracking-widest cursor-pointer hover:bg-slate-50 transition-all appearance-none">
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }} CALENDAR</option>
                    </select>
                    <div class="h-6 w-px bg-slate-100"></div>
                    <select v-model="filters.type" @change="fetchData(1)" class="flex-1 bg-transparent border-none text-sm md:text-sm font-black text-slate-600 focus:ring-0 px-2 md:px-4 py-2 uppercase tracking-widest cursor-pointer hover:bg-slate-50 transition-all appearance-none">
                        <option value="">ALL TYPES</option>
                        <option value="Fixed">FIXED</option>
                        <option value="Restricted">RESTRICTED</option>
                    </select>
                </div>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    <button v-if="!isPublished" @click="publishCalendar" class="flex-1 px-4 md:px-8 py-3 md:py-4 bg-indigo-600 text-white text-sm md:text-sm font-black uppercase tracking-widest rounded-2xl hover:shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-sm"></i> Publish
                    </button>
                    <button v-else @click="unpublishCalendar" class="flex-1 px-4 md:px-8 py-3 md:py-4 bg-white text-rose-500 text-sm md:text-sm font-black uppercase tracking-widest rounded-2xl hover:bg-rose-50 border border-rose-100 shadow-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-circle-xmark text-sm"></i> Hide
                    </button>
                    
                    <a :href="exportUrl" target="_blank" class="w-11 h-11 md:w-14 md:h-14 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-slate-100 shadow-sm hover:text-emerald-500 transition-all active:scale-95 shrink-0">
                         <i class="fas fa-file-export text-sm md:text-sm"></i>
                    </a>
                    
                    <button @click="openCreateModal" class="flex-1 sm:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white px-6 md:px-10 py-3 md:py-4 rounded-2xl hover:shadow-xl transition-all text-sm md:text-sm font-black uppercase tracking-widest active:scale-95 group whitespace-nowrap">
                        <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                        <span>New Protocol</span>
                    </button>
                </div>
            </div>
        </div>

        <BaseDataTable
            :columns="columns"
            :data="holidaysData.data || []"
            :pagination="holidaysData"
            :loading="loading"
            @page-change="fetchData"
        >
            <template #cell-name="{ item }">
                <span :class="{'text-gray-400 line-through italic': item.is_hidden_for_year, 'font-medium text-gray-900': !item.is_hidden_for_year}">{{ item.name }}</span>
            </template>

            <template #cell-date="{ item }">
                <span class="font-medium text-gray-900" :class="{'text-gray-400': item.is_hidden_for_year}">{{ formatDate(item.date) }}</span>
            </template>
            
            <template #cell-type="{ item }">
                 <span class="px-2 py-1 rounded text-xs font-semibold"
                    :class="{
                        'bg-blue-100 text-blue-800': item.type === 'Fixed' && !item.is_hidden_for_year,
                        'bg-purple-100 text-purple-800': item.type === 'Restricted' && !item.is_hidden_for_year,
                        'bg-gray-100 text-gray-500': item.is_hidden_for_year
                    }"
                 >
                     {{ item.type }}
                 </span>
                 <span v-if="item.is_recurring" class="ml-2 text-sm text-gray-400 border border-gray-200 rounded px-1">Recurring</span>
            </template>

            <template #cell-actions="{ item }">
                 <div class="flex justify-end gap-4 p-2">
                    <button v-if="item.is_recurring" 
                        @click="toggleVisibility(item)" 
                        class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center transition-all hover:scale-110"
                        :class="item.is_hidden_for_year ? 'text-slate-300' : 'text-emerald-500'"
                        :title="item.is_hidden_for_year ? 'Hidden for this year' : 'Visible for this year'"
                    >
                        <EyeSlashIcon v-if="item.is_hidden_for_year" class="w-4 h-4" />
                        <EyeIcon v-else class="w-4 h-4" />
                    </button>
                    
                    <button @click="openEditModal(item)" class="w-10 h-10 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center transition-all hover:scale-110 hover:bg-indigo-50">
                        <PencilIcon class="w-4 h-4" />
                    </button>
                    <button @click="confirmDelete(item)" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center transition-all hover:scale-110">
                        <TrashIcon class="w-4 h-4" />
                    </button>
                </div>
            </template>
        </BaseDataTable>

        <!-- New Holiday Modal -->
        <PremiumModal 
            :show="showModal" 
            @close="closeModal" 
            :title="isEditing ? 'Edit Holiday' : 'New Holiday'" 
            subtitle="Calendar Logic Configuration"
            icon="fa-cake-candles"
            maxWidth="2xl"
        >
            <form @submit.prevent="submit" class="space-y-10">
                <div class="space-y-4">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Holiday Name</label>
                    <input v-model="form.name" required placeholder="e.g. Independence Day" class="w-full bg-slate-50 border-transparent rounded-[24px] py-5 px-8 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest">
                    <p v-if="errors.name" class="text-sm font-black text-rose-500 uppercase tracking-widest mt-2 px-1">{{ errors.name[0] }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Date</label>
                        <input type="date" v-model="form.date" required class="w-full bg-slate-50 border-transparent rounded-[24px] py-5 px-8 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest">
                        <p v-if="errors.date" class="text-sm font-black text-rose-500 uppercase tracking-widest mt-2 px-1">{{ errors.date[0] }}</p>
                    </div>

                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Type</label>
                        <select v-model="form.type" required class="w-full bg-slate-50 border-transparent rounded-[24px] py-5 px-8 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer uppercase tracking-widest">
                            <option value="Fixed">Public Holiday</option>
                            <option value="Restricted">Optional Holiday</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-4 bg-indigo-50/50 p-6 rounded-[24px] border border-indigo-100/30">
                    <input id="recurring" type="checkbox" v-model="form.is_recurring" class="w-6 h-6 rounded-lg text-indigo-600 focus:ring-indigo-500 border-indigo-200">
                    <div>
                        <label for="recurring" class="text-sm font-black text-slate-700 uppercase tracking-widest block">Recurring Protocol</label>
                        <p class="text-sm font-black text-indigo-400 uppercase tracking-widest mt-1">Automatically repeat this holiday every year</p>
                    </div>
                </div>

                <div class="flex justify-end gap-6 pt-10 mt-10 border-t border-slate-50">
                    <button type="button" @click="closeModal" class="px-10 py-5 rounded-[22px] text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
                    <button type="submit" :disabled="processing" class="px-14 py-5 bg-slate-900 text-white rounded-[24px] text-sm font-black uppercase tracking-[0.4em] shadow-2xl shadow-slate-900/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-4">
                        <i class="fas fa-check-double text-sm"></i>
                        <span>{{ isEditing ? 'Update Holiday' : 'Create Holiday' }}</span>
                    </button>
                </div>
            </form>
        </PremiumModal>

        <!-- Generic Confirmation Modal -->
        <PremiumModal 
            :show="showConfirmModal" 
            @close="showConfirmModal = false" 
            :title="confirmTitle" 
            subtitle="System Protocol Authorization"
            icon="fa-circle-exclamation"
            maxWidth="lg"
        >
            <div class="space-y-10">
                <p class="text-sm font-black text-slate-500 uppercase tracking-widest leading-relaxed text-center">{{ confirmMessage }}</p>
                <div class="flex justify-center gap-6">
                    <button @click="showConfirmModal = false" class="px-10 py-5 rounded-[22px] text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:bg-slate-50 transition-all">Abort</button>
                    <button @click="handleConfirm" class="px-14 py-5 bg-indigo-600 text-white rounded-[24px] text-sm font-black uppercase tracking-[0.4em] shadow-2xl shadow-indigo-500/30 hover:scale-105 active:scale-95 transition-all">Confirm</button>
                </div>
            </div>
        </PremiumModal>
    </div>
  </component>
</template>
