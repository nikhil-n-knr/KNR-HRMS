<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { useToastStore } from '@/stores/toast';
import PersonalDetailsModal from '@/Components/Modals/PersonalDetailsModal.vue';
import FamilyMemberModal from '@/Components/Modals/FamilyMemberModal.vue';
import HealthRecordModal from '@/Components/Modals/HealthRecordModal.vue';
import { 
    FingerPrintIcon, 
    MapPinIcon, 
    UsersIcon, 
    HeartIcon, 
    PencilSquareIcon, 
    PlusIcon, 
    TrashIcon, 
    UserIcon,
    CalendarIcon,
    IdentificationIcon,
    GlobeAltIcon,
    ShieldCheckIcon,
    PhoneIcon,
    ExclamationTriangleIcon,
    CameraIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: { type: Object, required: true }
});

const emit = defineEmits(['refresh']);
const toast = useToastStore();

const showModal = ref(false);
const showHealthModal = ref(false);

// Family Logic
const showFamilyModal = ref(false);
const selectedFamilyMember = ref(null);

const openFamilyModal = (member = null) => {
    selectedFamilyMember.value = member;
    showFamilyModal.value = true;
};

const closeFamilyModal = () => {
    showFamilyModal.value = false;
    selectedFamilyMember.value = null;
};

const deleteFamilyMember = async (member) => {
    if (!confirm(`Are you sure you want to remove ${member.name}?`)) return;
    try {
        await axios.delete(`/admin/employees/${props.employee.id}/families/${member.id}`);
        toast.success("Member removed");
        emit('refresh');
    } catch (e) {
        toast.error("Failed to remove member");
    }
};

const avatarForm = useForm({
    avatar: null
});

const fileInput = ref(null);

const triggerAvatarUpload = () => {
    fileInput.value.click();
};

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        avatarForm.avatar = file;
        avatarForm.post(route('employee.profile.update-avatar', props.employee.uuid), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Profile picture updated!");
                emit('refresh');
            },
            onError: (err) => {
                toast.error("Failed to update profile picture.");
            }
        });
    }
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '--';
</script>

<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700 font-outfit pb-20">
        <!-- Profile Picture Enhancement Section -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative group overflow-hidden">
            <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                <div class="relative group/avatar cursor-pointer" @click="triggerAvatarUpload">
                    <div class="h-24 w-24 rounded-3xl bg-slate-900 flex items-center justify-center text-2xl font-black text-indigo-400 border-4 border-white shadow-xl overflow-hidden relative">
                        <img v-if="employee.avatar_url" :src="employee.avatar_url" class="w-full h-full object-cover" />
                        <UserIcon v-else class="w-10 h-10" />
                        
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/avatar:opacity-100 transition-opacity flex items-center justify-center">
                            <CameraIcon class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
                
                <div class="text-center md:text-left">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Profile Identity Visual</h3>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Update your professional profile picture</p>
                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-3">
                        <button @click="triggerAvatarUpload" class="px-6 py-2 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 transition-all flex items-center gap-2 shadow-lg">
                            <CameraIcon class="w-4 h-4" />
                            Upload New Photo
                        </button>
                        <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleAvatarChange" />
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 uppercase tracking-widest font-black opacity-60">JPEG, PNG or JPG (Max 2MB)</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Bio Terminal -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative group overflow-hidden">
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-slate-50 rounded-full blur-2xl group-hover:bg-indigo-50 transition-colors"></div>
                
                <div class="flex justify-between items-center mb-10 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-6 transition-transform">
                            <FingerPrintIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em]">Personal Identity</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Biometric & Legal Anchor Data</p>
                        </div>
                    </div>
                    <button @click="showModal = true" class="px-5 py-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        <PencilSquareIcon class="w-4 h-4" />
                        Modify
                    </button>
                </div>

                <div v-if="employee.personal_detail" class="grid grid-cols-1 sm:grid-cols-2 gap-10 relative z-10">
                    <div v-for="item in [
                        { label: 'Temporal Entry (DOB)', val: formatDate(employee.personal_detail.dob), icon: CalendarIcon },
                        { label: 'Gender Protocol', val: employee.personal_detail.gender || '--', icon: UserIcon },
                        { label: 'Marital Lifecycle', val: employee.personal_detail.marital_status || '--', icon: UsersIcon },
                        { label: 'Nationality Node', val: employee.personal_detail.nationality || '--', icon: GlobeAltIcon },
                    ]" :key="item.label" class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest">
                            <component :is="item.icon" class="w-3.5 h-3.5 opacity-60" />
                            {{ item.label }}
                        </div>
                        <div class="text-base font-black text-slate-900 uppercase tracking-tight">{{ item.val }}</div>
                    </div>
                    <div class="col-span-2 p-5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Passport Signature</div>
                            <div class="text-sm font-black text-slate-900 uppercase tracking-widest mt-1 font-mono">{{ employee.personal_detail.passport_number || 'UNSPECIFIED_NODE' }}</div>
                        </div>
                        <IdentificationIcon class="w-8 h-8 text-slate-200" />
                    </div>
                </div>
                
                <div v-else class="text-center py-20 grayscale opacity-30 relative z-10">
                    <FingerPrintIcon class="w-16 h-16 mx-auto mb-6 text-slate-300" />
                    <p class="text-xs font-black uppercase tracking-[0.4em]">Zero identity nodes detected</p>
                    <button @click="showModal = true" class="mt-6 text-indigo-600 text-xs font-black uppercase tracking-widest hover:underline">Initialize Identity Node</button>
                </div>
            </div>

            <!-- Geospatial Anchor (Address) -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 group overflow-hidden">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:-rotate-6 transition-transform">
                        <MapPinIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em]">Geospatial Anchors</h3>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Residential Coordinates</p>
                    </div>
                </div>

                <div v-if="employee.personal_detail" class="space-y-8">
                    <!-- Current Address -->
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 relative group/addr">
                        <div class="absolute top-4 right-6 text-xs font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 uppercase tracking-widest">Current_Active</div>
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Primary Sector</div>
                        <p class="text-base font-black text-slate-800 uppercase tracking-tight leading-relaxed">
                            {{ employee.personal_detail.current_address }}<br>
                            <span class="text-indigo-600">{{ [employee.personal_detail.current_city, employee.personal_detail.current_state].filter(Boolean).join(', ') }}</span> 
                            {{ employee.personal_detail.current_zip ? `- ${employee.personal_detail.current_zip}` : '' }}<br>
                            <span class="text-slate-400">{{ employee.personal_detail.current_country }}</span>
                        </p>
                    </div>

                    <!-- Permanent Address -->
                    <div class="p-6 bg-white border border-slate-100 rounded-3xl group/addr2">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Origin Sector</div>
                        <p class="text-base font-black text-slate-800 uppercase tracking-tight leading-relaxed">
                            <span v-if="employee.personal_detail.is_permanent_same" class="italic text-slate-400">SYNCED_WITH_PRIMARY_SECTOR</span>
                            <span v-else>
                                {{ employee.personal_detail.permanent_address }}<br>
                                <span class="text-indigo-600">{{ [employee.personal_detail.permanent_city, employee.personal_detail.permanent_state].filter(Boolean).join(', ') }}</span> 
                                {{ employee.personal_detail.permanent_zip ? `- ${employee.personal_detail.permanent_zip}` : '' }}<br>
                                <span class="text-slate-400">{{ employee.personal_detail.permanent_country }}</span>
                            </span>
                        </p>
                    </div>
                </div>
                
                <div v-else class="text-center py-20 grayscale opacity-30">
                    <MapPinIcon class="w-16 h-16 mx-auto mb-6 text-slate-300" />
                    <p class="text-xs font-black uppercase tracking-[0.4em]">Zero spatial coordinates found</p>
                </div>
            </div>
        </div>

        <!-- Kinship & Dependents (Family) -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-10 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
             <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl opacity-50"></div>
             
             <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-6 relative z-10">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-amber-400 shadow-xl group-hover:scale-110 transition-transform">
                        <UsersIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.2em]">Kinship Registry</h3>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1 uppercase tracking-[0.3em]">Dependents & Emergency Nodes</p>
                    </div>
                </div>
                <button @click="openFamilyModal()" class="h-12 px-8 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 flex items-center gap-3 group">
                    <PlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-90 transition-transform" />
                    Map Member Node
                </button>
             </div>

             <div v-if="employee.families?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                <div v-for="member in employee.families" :key="member.id" class="group bg-white border border-slate-100 rounded-[2rem] p-6 hover:border-indigo-200 hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500 relative">
                    <div class="absolute top-4 right-6 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all flex gap-3">
                        <button @click="openFamilyModal(member)" class="w-8 h-8 bg-slate-50 text-indigo-600 rounded-lg flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all shadow-sm"><PencilSquareIcon class="w-4 h-4" /></button>
                        <button @click="deleteFamilyMember(member)" class="w-8 h-8 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm"><TrashIcon class="w-4 h-4" /></button>
                    </div>

                    <div class="flex items-center gap-5 mb-6">
                        <div class="h-14 w-14 rounded-2xl bg-slate-900 flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-6 transition-transform">
                            <span class="text-xs font-black uppercase">{{ member.name.substring(0,2).toUpperCase() }}</span>
                        </div>
                        <div>
                            <p class="text-[14px] font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ member.name }}</p>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">{{ member.relationship }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 px-1">
                        <div v-if="member.phone" class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-3">
                            <PhoneIcon class="w-4 h-4 text-slate-300" />
                            {{ member.phone }}
                        </div>
                        <div class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-3">
                            <CalendarIcon class="w-4 h-4 text-slate-300" />
                            {{ formatDate(member.dob) }}
                        </div>
                    </div>
                    
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span v-if="member.is_dependent" class="px-3 py-1 bg-emerald-50 text-emerald-600 text-xs font-black rounded-lg border border-emerald-100 uppercase tracking-widest">Dependent_Locked</span>
                        <span v-if="member.is_emergency_contact" class="px-3 py-1 bg-rose-50 text-rose-600 text-xs font-black rounded-lg border border-rose-100 uppercase tracking-widest animate-pulse flex items-center gap-2">
                            <ExclamationTriangleIcon class="w-3 h-3" />
                            Emergency_Ops
                        </span>
                    </div>
                </div>
             </div>
             <div v-else class="text-center py-20 grayscale opacity-20 bg-slate-50/50 rounded-[2.5rem] border-2 border-dashed border-slate-100 relative z-10 transition-all hover:bg-slate-50 group/empty">
                <UsersIcon class="w-16 h-16 mx-auto mb-6 text-slate-300 group-hover/empty:scale-110 transition-transform" />
                <p class="text-xs font-black uppercase tracking-[0.4em]">Zero kinship nodes detected</p>
             </div>
        </div>

        <!-- Vitality & Medical Intelligence -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-10 shadow-2xl shadow-slate-200/40 group overflow-hidden relative">
             <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-rose-50 rounded-full blur-3xl opacity-30"></div>
             
             <div class="flex justify-between items-center mb-10 relative z-10">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-rose-400 shadow-xl group-hover:scale-110 transition-transform">
                        <HeartIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.2em]">Vitality Scan</h3>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Medical Intel & Insurance Encryption</p>
                    </div>
                </div>
                <button @click="showHealthModal = true" class="px-6 py-3 bg-rose-50 text-rose-600 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center gap-3">
                    <PencilSquareIcon class="w-4 h-4" />
                    Update Vitals
                </button>
             </div>

             <div v-if="employee.health_record" class="grid grid-cols-1 md:grid-cols-2 gap-12 relative z-10">
                <!-- Vitals Matrix -->
                <div class="space-y-8">
                    <div class="flex items-center gap-10">
                        <div class="space-y-3">
                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Heuristic Group</div>
                            <div class="text-3xl font-black text-rose-600 tracking-tighter">{{ employee.health_record.blood_group || '--' }}</div>
                        </div>
                        <div class="h-10 w-px bg-slate-100"></div>
                        <div class="space-y-3">
                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Physical Dimensions</div>
                            <div class="text-xl font-black text-slate-900 tracking-tight">
                                {{ employee.health_record.height_cm ? `${employee.health_record.height_cm}CM` : '--' }} 
                                <span class="text-slate-300 px-2">/</span>
                                {{ employee.health_record.weight_kg ? `${employee.health_record.weight_kg}KG` : '--' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                             <ExclamationTriangleIcon class="w-4 h-4 text-rose-400" />
                             Immunological Alerts (Allergies)
                        </div>
                        <div class="text-sm font-black text-rose-700 uppercase tracking-tight bg-rose-50 p-5 rounded-2xl border border-rose-100 shadow-sm italic leading-relaxed">
                            " {{ employee.health_record.allergies || 'ZERO_ALLERGY_NODES_DETECTED' }} "
                        </div>
                    </div>
                </div>

                <!-- Insurance Interface -->
                <div class="grid grid-cols-1 gap-6 bg-slate-50/50 p-8 rounded-[2rem] border border-slate-100">
                    <div class="space-y-1">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Coverage Operative</div>
                        <div class="text-base font-black text-slate-900 uppercase tracking-[0.1em]">{{ employee.health_record.insurance_provider || 'NO_COVERAGE_DETECTED' }}</div>
                    </div>
                     <div class="space-y-1">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Policy Signature</div>
                        <div class="text-sm font-black text-slate-700 uppercase tracking-widest font-mono">{{ employee.health_record.policy_number || 'NULL_POLICY_HASH' }}</div>
                    </div>
                     <div class="space-y-1">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Diagnostic Interval</div>
                        <div class="text-sm font-black text-emerald-600 uppercase tracking-tight flex items-center gap-2">
                            <ShieldCheckIcon class="w-4 h-4" />
                            Last Pulse: {{ formatDate(employee.health_record.last_checkup_date) }}
                        </div>
                    </div>
                </div>
             </div>
             
             <div v-else class="text-center py-16 grayscale opacity-20 relative z-10 border-2 border-dashed border-slate-100 rounded-[2rem]">
                 <HeartIcon class="w-16 h-16 mx-auto mb-4 text-slate-300" />
                 <p class="text-xs font-black uppercase tracking-[0.4em]">Zero vitality logs detected</p>
                 <button @click="showHealthModal = true" class="mt-4 text-rose-500 text-xs font-black uppercase tracking-widest hover:underline">Initialize Health Scan</button>
             </div>
        </div>

        <!-- Tactical Modals -->
        <PersonalDetailsModal :show="showModal" :employee="employee" :detail="employee.personal_detail" @close="showModal = false" @saved="$emit('refresh')" />
        <FamilyMemberModal :show="showFamilyModal" :employee="employee" :member-data="selectedFamilyMember" @close="closeFamilyModal" @saved="$emit('refresh')" />
        <HealthRecordModal :show="showHealthModal" :employee="employee" :record="employee.health_record" @close="showHealthModal = false" @saved="$emit('refresh')" />
    </div>
</template>

<style scoped>
/* No specific styles needed. Tailwind is used for custom aesthetics. */
</style>
