<script setup>
import { ref, computed } from 'vue';
import { Link, router, Head, useForm } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/Layouts/MainLayout.vue';
import EmployeeDocumentsTab  from './Tabs/EmployeeDocumentsTab.vue';
import EmployeeOverviewTab   from './Tabs/EmployeeOverviewTab.vue';
import EmployeePersonalTab   from './Tabs/EmployeePersonalTab.vue';
import EmployeeBankingTab    from './Tabs/EmployeeBankingTab.vue';
import EmployeeExpensesTab   from './Tabs/EmployeeExpensesTab.vue';
import EmployeeTaxTab        from './Tabs/EmployeeTaxTab.vue';
import EmployeePayslipsTab   from './Tabs/EmployeePayslipsTab.vue';
import CreateLoginModal      from '@/Components/Modals/CreateLoginModal.vue';
import PasswordResetModal    from '@/Components/Modals/PasswordResetModal.vue';
import StatutoryDetailsModal from '@/Components/Modals/StatutoryDetailsModal.vue';
import BankDetailsModal      from '@/Components/Modals/BankDetailsModal.vue';
import CareerDnaModal        from '@/Components/Analytics/CareerDnaModal.vue';
import FamilyMemberModal     from '@/Components/Modals/FamilyMemberModal.vue';
import {
    ChevronLeftIcon, IdentificationIcon, UserGroupIcon, FolderIcon,
    ClockIcon, DocumentTextIcon, HeartIcon, ShieldCheckIcon, KeyIcon,
    PlusIcon, PencilSquareIcon, TrashIcon, MapPinIcon, EnvelopeIcon,
    BriefcaseIcon, SparklesIcon, Squares2X2Icon, ArrowPathRoundedSquareIcon,
    BanknotesIcon, CameraIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const authStore  = useAuthStore();
const toast      = useToastStore();
const hasAdminRole = computed(() => ['Super Admin','Admin','Manager'].includes(authStore.user?.role?.name));

const props = defineProps({
    employee: Object,
    history:  Array,
    payslips: Array,
    tab:      String
});

const currentTab          = ref(props.tab || 'overview');
const showCreateLogin     = ref(false);
const showResetPassword   = ref(false);
const showStatutoryModal  = ref(false);
const showBankModal       = ref(false);
const showCareerDna       = ref(false);
const showFamilyModal     = ref(false);
const selectedFamilyMember = ref(null);

const avatarForm = useForm({ avatar: null });
const fileInput  = ref(null);

const triggerAvatarUpload = () => { if (props.employee.uuid) fileInput.value?.click(); };
const handleAvatarChange  = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar = file;
    avatarForm.post(route('employee.profile.update-avatar', props.employee.uuid), {
        forceFormData: true, preserveScroll: true,
        onSuccess: () => toast.success('Profile picture updated!'),
        onError:   () => toast.error('Failed to update profile picture.')
    });
};

const tabs = [
    { id: 'overview',  name: 'Overview',  icon: Squares2X2Icon },
    { id: 'personal',  name: 'Personal',  icon: IdentificationIcon },
    { id: 'family',    name: 'Family',    icon: UserGroupIcon },
    { id: 'documents', name: 'Documents', icon: FolderIcon },
    { id: 'history',   name: 'History',   icon: ClockIcon },
    { id: 'tax',       name: 'Tax / TDS', icon: DocumentTextIcon },
    { id: 'payslips',  name: 'Payslips',  icon: BanknotesIcon },
    { id: 'expenses',  name: 'Expenses',  icon: HeartIcon },
];

const statusStyles = {
    active:        'bg-emerald-400/20 text-emerald-200 border-emerald-400/30',
    probation:     'bg-blue-400/20 text-blue-200 border-blue-400/30',
    notice_period: 'bg-amber-400/20 text-amber-200 border-amber-400/30',
    terminated:    'bg-rose-400/20 text-rose-200 border-rose-400/30',
    resigned:      'bg-rose-400/20 text-rose-200 border-rose-400/30',
    on_leave:      'bg-purple-400/20 text-purple-200 border-purple-400/30',
};
const statusDotColors = {
    active:'bg-emerald-400', probation:'bg-blue-400', notice_period:'bg-amber-400',
    terminated:'bg-rose-400', resigned:'bg-rose-400', on_leave:'bg-purple-400',
};
const getStatusStyles  = (s) => statusStyles[s]    || 'bg-white/10 text-white/70 border-white/20';
const getStatusDot     = (s) => statusDotColors[s] || 'bg-white/40';
const formatStatus     = (s) => (s||'').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
const getInitials      = (f, l) => `${f?.[0]||''}${l?.[0]||''}`.toUpperCase();

const fetchEmployee    = () => router.reload({ only: ['employee'] });
const openFamilyModal  = (member = null) => { selectedFamilyMember.value = member; showFamilyModal.value = true; };
const closeFamilyModal = () => { showFamilyModal.value = false; selectedFamilyMember.value = null; };

const deleteFamilyMember = async (member) => {
    if (!confirm('Delete this family member?')) return;
    try {
        await axios.delete(`/admin/employees/${props.employee.id}/families/${member.id}`);
        toast.success('Family member deleted'); fetchEmployee();
    } catch { toast.error('Failed to delete member'); }
};

// Card-level status badges for content tabs
const cardStatusStyles = {
    active:        'bg-emerald-50 text-emerald-700 border-emerald-100',
    probation:     'bg-blue-50 text-blue-700 border-blue-100',
    notice_period: 'bg-amber-50 text-amber-700 border-amber-100',
    terminated:    'bg-rose-50 text-rose-700 border-rose-100',
    resigned:      'bg-rose-50 text-rose-700 border-rose-100',
    on_leave:      'bg-purple-50 text-purple-700 border-purple-100',
};
const cardStatusDots = {
    active:'bg-emerald-500', probation:'bg-blue-500', notice_period:'bg-amber-500',
    terminated:'bg-rose-500', resigned:'bg-rose-500', on_leave:'bg-purple-500',
};
const getCardStatusStyles = (s) => cardStatusStyles[s] || 'bg-slate-50 text-slate-500 border-slate-100';
const getCardStatusDot    = (s) => cardStatusDots[s]   || 'bg-slate-400';
</script>

<template>
    <Head :title="`${employee.first_name} ${employee.last_name} | Profile`" />
    <MainLayout>

        <!-- ░░ Outer Page Shell ░░ -->
        <div class="min-h-screen bg-[#f4f5fa]">

            <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
            <div class="relative overflow-hidden sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-6
                        bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
                <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

                <div class="relative z-10 px-6 sm:px-10 pt-8 pb-0">

                    <!-- Back link -->
                    <Link v-if="hasAdminRole" :href="route('admin.employees.index')"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/60 hover:text-white transition-colors mb-5">
                        <ChevronLeftIcon class="w-3.5 h-3.5" />
                        Back to Workforce Registry
                    </Link>

                    <!-- Profile row -->
                    <div class="flex flex-col md:flex-row gap-6 items-start pb-6">

                        <!-- Avatar -->
                        <div class="relative group/av shrink-0 self-center md:self-start">
                            <div @click="triggerAvatarUpload"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl sm:text-4xl font-extrabold text-white cursor-pointer overflow-hidden border-2 border-white/30 shadow-xl">
                                <template v-if="employee.avatar_url">
                                    <img :src="employee.avatar_url" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/av:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
                                        <CameraIcon class="w-6 h-6 text-white" />
                                    </div>
                                </template>
                                <template v-else>
                                    {{ getInitials(employee.first_name, employee.last_name) }}
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover/av:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
                                        <CameraIcon class="w-6 h-6 text-white" />
                                    </div>
                                </template>
                            </div>
                            <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleAvatarChange" />
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-400 rounded-full border-2 border-white/20 flex items-center justify-center">
                                <ShieldCheckIcon class="w-2.5 h-2.5 text-white" />
                            </div>
                        </div>

                        <!-- Identity -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <p class="text-xs font-bold text-white/50 uppercase tracking-widest">Employee Profile</p>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
                                {{ employee.first_name }} {{ employee.last_name }}
                            </h1>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/10 border border-white/20 text-xs font-semibold text-white/80">
                                    <BriefcaseIcon class="w-3.5 h-3.5 text-white/50" />
                                    {{ employee.designation }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/10 border border-white/20 text-xs font-mono font-semibold text-white/80">
                                    <IdentificationIcon class="w-3.5 h-3.5 text-white/50" />
                                    {{ employee.employee_code }}
                                </span>
                                <span v-if="employee.email" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/10 border border-white/20 text-xs font-semibold text-white/80">
                                    <EnvelopeIcon class="w-3.5 h-3.5 text-white/50" />
                                    {{ employee.email }}
                                </span>
                                <span v-if="employee.department" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/10 border border-white/20 text-xs font-semibold text-white/80">
                                    <MapPinIcon class="w-3.5 h-3.5 text-white/50" />
                                    {{ employee.department?.name }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border text-xs font-semibold" :class="getStatusStyles(employee.status)">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(employee.status)"></span>
                                    {{ formatStatus(employee.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons (right) -->
                        <div class="flex flex-row md:flex-col gap-2 flex-wrap md:flex-nowrap shrink-0">
                            <button v-if="!employee.user_id" @click="showCreateLogin = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-indigo-700 text-xs font-bold hover:bg-indigo-50 transition-all shadow-sm">
                                <KeyIcon class="w-3.5 h-3.5" />
                                Create Login
                            </button>
                            <button v-else @click="showResetPassword = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white text-xs font-semibold hover:bg-white/20 transition-all">
                                <ArrowPathRoundedSquareIcon class="w-3.5 h-3.5" />
                                Reset Password
                            </button>
                            <Link :href="route('employee.rewards.index', { uuid: employee.uuid })"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white text-xs font-semibold hover:bg-white/20 transition-all">
                                <SparklesIcon class="w-3.5 h-3.5" />
                                Rewards
                            </Link>
                            <button @click="showCareerDna = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white text-xs font-semibold hover:bg-white/20 transition-all">
                                <HeartIcon class="w-3.5 h-3.5" />
                                Career DNA
                            </button>
                        </div>
                    </div>

                    <!-- Tab Navigation (inside hero, bottom) -->
                    <div class="flex gap-0.5 overflow-x-auto no-scrollbar">
                        <button v-for="tab in tabs" :key="tab.id"
                            @click="currentTab = tab.id"
                            class="inline-flex items-center gap-2 px-4 py-3 text-xs font-bold transition-all relative whitespace-nowrap shrink-0"
                            :class="currentTab === tab.id ? 'text-white' : 'text-white/50 hover:text-white/80'">
                            <component :is="tab.icon" class="w-4 h-4" />
                            {{ tab.name }}
                            <div v-if="currentTab === tab.id"
                                class="absolute bottom-0 left-0 right-0 h-0.5 bg-white rounded-t-full"></div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
            <div class="mx-0 sm:mx-6 mt-5 pb-12">
                <Transition name="fade-slide" mode="out-in">

                    <!-- Overview -->
                    <div v-if="currentTab === 'overview'" key="overview">
                        <EmployeeOverviewTab :employee="employee" />
                    </div>

                    <!-- Personal -->
                    <div v-else-if="currentTab === 'personal'" key="personal" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                        <div class="lg:col-span-2 space-y-5">
                            <EmployeePersonalTab :employee="employee" @refresh="fetchEmployee" />
                        </div>
                        <div class="space-y-5">
                            <EmployeeBankingTab :employee="employee" @refresh="fetchEmployee" />
                            <!-- Statutory -->
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
                                        Statutory Details
                                    </h3>
                                    <button @click="showStatutoryModal = true"
                                        class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-all">
                                        <PencilSquareIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div class="space-y-3">
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <p class="text-xs text-slate-400 font-medium mb-1">PAN Number</p>
                                        <p class="text-xs font-mono font-semibold text-slate-800 uppercase tracking-wider">{{ employee.pan_number || 'Not provided' }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <p class="text-xs text-slate-400 font-medium mb-1">UAN Number</p>
                                        <p class="text-xs font-mono font-semibold text-slate-800 uppercase tracking-wider">{{ employee.uan_number || 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Family -->
                    <div v-else-if="currentTab === 'family'" key="family" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-100">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <UserGroupIcon class="w-4 h-4 text-indigo-500" />
                                    Family & Dependents
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Emergency contacts and family members</p>
                            </div>
                            <button @click="openFamilyModal()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all shadow-sm">
                                <PlusIcon class="w-3.5 h-3.5" />
                                Add Member
                            </button>
                        </div>
                        <div v-if="employee.families?.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            <div v-for="member in employee.families" :key="member.id"
                                class="group/m relative bg-slate-50 hover:bg-white rounded-2xl border border-slate-100 hover:border-indigo-200 hover:shadow-md p-4 transition-all">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-sm font-bold text-indigo-600 group-hover/m:bg-indigo-50 group-hover/m:border-indigo-200 transition-colors">
                                        {{ member.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 group-hover/m:text-indigo-600 transition-colors">{{ member.name }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-lg">{{ member.relationship }}</span>
                                            <span v-if="member.is_emergency_contact" class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg">Emergency</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2 opacity-0 group-hover/m:opacity-100 transition-opacity mt-2 pt-2 border-t border-slate-100">
                                    <button @click="openFamilyModal(member)"
                                        class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                        <PencilSquareIcon class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="deleteFamilyMember(member)"
                                        class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all">
                                        <TrashIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-20 text-slate-300">
                            <UserGroupIcon class="h-12 w-12 mb-3" />
                            <p class="text-sm font-semibold text-slate-400">No family members added yet</p>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div v-else-if="currentTab === 'documents'" key="documents">
                        <EmployeeDocumentsTab :employee="employee" />
                    </div>

                    <!-- History -->
                    <div v-else-if="currentTab === 'history'" key="history" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-100">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <ClockIcon class="w-4 h-4 text-indigo-500" />
                                    Employment History
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Audit trail of all profile changes and events</p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-xs font-semibold text-slate-600">
                                {{ history?.length || 0 }} Records
                            </span>
                        </div>
                        <div v-if="history?.length" class="relative pl-6 space-y-5 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                            <div v-for="log in history" :key="log.id" class="relative group/log">
                                <div class="absolute -left-[25px] top-1 w-4 h-4 rounded-full bg-white border-2 border-indigo-300 group-hover/log:border-indigo-500 group-hover/log:bg-indigo-50 transition-all z-10"></div>
                                <div class="bg-slate-50 hover:bg-white rounded-xl border border-transparent hover:border-slate-200 hover:shadow-sm p-4 transition-all">
                                    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg">
                                            {{ new Date(log.created_at).toLocaleDateString(undefined, { month:'short', day:'numeric', year:'numeric' }) }}
                                        </span>
                                        <span class="text-xs text-slate-400 font-mono">{{ new Date(log.created_at).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' }) }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-slate-700 leading-relaxed">{{ log.description }}</p>
                                    <div v-if="log.properties" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-100">
                                        <div v-for="(val, key) in log.properties" :key="key"
                                            class="text-xs text-slate-500 bg-white border border-slate-200 rounded-lg px-2.5 py-1">
                                            <span class="text-slate-400">{{ key }}:</span> <span class="font-semibold text-slate-700">{{ val }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-20 text-slate-300">
                            <ClockIcon class="h-12 w-12 mb-3" />
                            <p class="text-sm font-semibold text-slate-400">No history records found</p>
                        </div>
                    </div>

                    <!-- Tax / TDS -->
                    <div v-else-if="currentTab === 'tax'" key="tax">
                        <EmployeeTaxTab :employee="employee" />
                    </div>

                    <!-- Expenses -->
                    <div v-else-if="currentTab === 'expenses'" key="expenses">
                        <EmployeeExpensesTab :employee="employee" />
                    </div>

                    <!-- Payslips -->
                    <div v-else-if="currentTab === 'payslips'" key="payslips">
                        <EmployeePayslipsTab :employee="employee" :payslips="payslips" />
                    </div>

                </Transition>
            </div><!-- /inner body -->
        </div><!-- /outer shell -->

        <!-- Modals -->
        <CreateLoginModal      :show="showCreateLogin"    :employee="employee"      @close="showCreateLogin = false"    @saved="fetchEmployee" />
        <PasswordResetModal    :show="showResetPassword"  :employee="employee"      @close="showResetPassword = false" />
        <BankDetailsModal      :show="showBankModal"      :employee="employee"      @close="showBankModal = false"      @saved="fetchEmployee" />
        <StatutoryDetailsModal :show="showStatutoryModal" :employee="employee"      @close="showStatutoryModal = false" @saved="fetchEmployee" />
        <CareerDnaModal        :show="showCareerDna"      :employee-id="employee.id" @close="showCareerDna = false" />
        <FamilyMemberModal     :show="showFamilyModal"    :employee="employee"      :member-data="selectedFamilyMember" @close="closeFamilyModal" @saved="fetchEmployee" />
    </MainLayout>
</template>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.2s cubic-bezier(0.16,1,0.3,1); }
.fade-slide-enter-from { opacity: 0; transform: translateY(8px); }
.fade-slide-leave-to   { opacity: 0; transform: translateY(-4px); }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>