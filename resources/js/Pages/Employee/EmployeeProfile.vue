<script setup>
import { ref, computed } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/Layouts/MainLayout.vue';
import EmployeeDocumentsTab from './Tabs/EmployeeDocumentsTab.vue'; 
import EmployeeOverviewTab from './Tabs/EmployeeOverviewTab.vue';
import EmployeePersonalTab from './Tabs/EmployeePersonalTab.vue';
import EmployeeBankingTab from './Tabs/EmployeeBankingTab.vue';
import EmployeeExpensesTab from './Tabs/EmployeeExpensesTab.vue';
import EmployeeTaxTab from './Tabs/EmployeeTaxTab.vue';
import EmployeePayslipsTab from './Tabs/EmployeePayslipsTab.vue';
import CreateLoginModal from '@/Components/Modals/CreateLoginModal.vue';
import PasswordResetModal from '@/Components/Modals/PasswordResetModal.vue';
import StatutoryDetailsModal from '@/Components/Modals/StatutoryDetailsModal.vue';
import BankDetailsModal from '@/Components/Modals/BankDetailsModal.vue';
import CareerDnaModal from '@/Components/Analytics/CareerDnaModal.vue';
import FamilyMemberModal from '@/Components/Modals/FamilyMemberModal.vue';
import { 
    ChevronLeftIcon,
    UserCircleIcon,
    IdentificationIcon,
    UserGroupIcon,
    FolderIcon,
    ClockIcon,
    DocumentTextIcon,
    HeartIcon,
    ShieldCheckIcon,
    KeyIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    MapPinIcon,
    EnvelopeIcon,
    BriefcaseIcon,
    SparklesIcon,
    Squares2X2Icon,
    ArrowPathRoundedSquareIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();
const hasAdminRole = computed(() => {
    const r = authStore.user?.role?.name;
    return r === 'Super Admin' || r === 'Admin' || r === 'Manager';
});

const props = defineProps({
    employee: Object,
    history: Array, 
    payslips: Array,
    tab: String 
});

const currentTab = ref(props.tab || 'overview');
const showCreateLogin = ref(false);
const showResetPassword = ref(false);
const showStatutoryModal = ref(false);
const showBankModal = ref(false);
const showCareerDna = ref(false);
const showFamilyModal = ref(false);
const selectedFamilyMember = ref(null);

const tabs = [
    { id: 'overview', name: 'Overview', icon: Squares2X2Icon },
    { id: 'personal', name: 'Personal', icon: IdentificationIcon },
    { id: 'family', name: 'Family', icon: UserGroupIcon },
    { id: 'documents', name: 'Documents', icon: FolderIcon },
    { id: 'history', name: 'History', icon: ClockIcon },
    { id: 'tax', name: 'Tax / TDS', icon: DocumentTextIcon },
    { id: 'payslips', name: 'Payslips', icon: BanknotesIcon },
    { id: 'expenses', name: 'Expenses', icon: HeartIcon },
];

const getInitials = (f, l) => `${f?.[0] || ''}${l?.[0] || ''}`.toUpperCase();
const formatStatus = (s) => (s || '').split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);

const fetchEmployee = () => {
    router.reload({ only: ['employee'] });
};

const openFamilyModal = (member = null) => {
    selectedFamilyMember.value = member;
    showFamilyModal.value = true;
};

const closeFamilyModal = () => {
    showFamilyModal.value = false;
    selectedFamilyMember.value = null;
};

import axios from 'axios';
import { useToastStore } from '@/stores/toast';
const toast = useToastStore();

const deleteFamilyMember = async (member) => {
    if (!confirm("Are you sure you want to delete this family member?")) return;
    try {
        await axios.delete(`/admin/employees/${props.employee.id}/families/${member.id}`);
        toast.success("Family member deleted");
        fetchEmployee();
    } catch (e) {
        toast.error("Failed to delete member");
    }
};

const getStatusStyles = (status) => {
    switch (status) {
        case 'active': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'probation': return 'bg-blue-50 text-blue-600 border-blue-100 shadow-blue-500/5';
        case 'notice_period': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'terminated':
        case 'resigned': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        case 'on_leave': return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <Head :title="employee.first_name + ' ' + employee.last_name + ' | Profile'" />
    <MainLayout>
        <div class="max-w-[96%] mx-auto px-4 xl:px-8 space-y-8 font-outfit pb-20">
            <!-- Strategic Profile Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                <!-- Premium Background Accents -->
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-50 rounded-full opacity-30 group-hover:scale-110 transition-transform duration-1000"></div>
                
                <div class="relative z-10 p-8 md:p-10">
                    <!-- Back Controller (Admin only) -->
                    <Link 
                        v-if="hasAdminRole"
                        :href="route('admin.employees.index')"
                        class="inline-flex items-center gap-2 mb-8 text-xs font-black text-slate-400 uppercase tracking-[0.2em] hover:text-indigo-600 transition-colors"
                    >
                        <ChevronLeftIcon class="w-4 h-4" />
                        Return to Registry
                    </Link>

                    <div class="flex flex-col lg:flex-row justify-between items-start gap-10">
                        <div class="flex flex-col md:flex-row gap-10 items-center md:items-start text-center md:text-left">
                            <!-- Avatar Terminal -->
                            <div class="relative group/avatar">
                                <div class="h-32 w-32 rounded-[2.5rem] bg-slate-900 flex items-center justify-center text-4xl font-black text-indigo-400 border-4 border-white shadow-2xl shadow-slate-300 transform group-hover/avatar:rotate-6 transition-transform">
                                    {{ getInitials(employee.first_name, employee.last_name) }}
                                </div>
                                <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-emerald-500 border-4 border-white rounded-2xl flex items-center justify-center text-white shadow-lg" title="Operative Status: Active">
                                    <ShieldCheckIcon class="w-5 h-5" />
                                </div>
                            </div>
                            
                            <!-- Identity Metadata -->
                            <div class="space-y-6">
                                <div>
                                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-4">
                                        <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight uppercase leading-none">{{ employee.first_name }} {{ employee.last_name }}</h1>
                                        <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-[0.2em] border shadow-sm transition-all" :class="getStatusStyles(employee.status)">
                                            {{ formatStatus(employee.status) }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap justify-center md:justify-start gap-4">
                                        <span class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                                            <BriefcaseIcon class="w-3.5 h-3.5 text-indigo-500" />
                                            {{ employee.designation }}
                                        </span>
                                        <span class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 font-mono">
                                            <IdentificationIcon class="w-3.5 h-3.5 text-blue-500" />
                                            {{ employee.employee_code }}
                                        </span>
                                        <span class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                                            <EnvelopeIcon class="w-3.5 h-3.5 text-emerald-500" />
                                            {{ employee.email || 'No email set' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Controls -->
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-64">
                            <button 
                                v-if="!employee.user_id"
                                @click="showCreateLogin = true"
                                class="flex-1 h-14 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 active:scale-95 group/btn"
                            >
                                <KeyIcon class="w-4 h-4 text-indigo-400 group-hover/btn:rotate-12 transition-transform" />
                                Create Login
                            </button>
                            <button 
                                v-else
                                @click="showResetPassword = true"
                                class="flex-1 h-14 bg-white border-2 border-slate-100 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-50 hover:text-amber-600 transition-all flex items-center justify-center gap-3 active:scale-95 group/btn shadow-sm"
                            >
                                <ArrowPathRoundedSquareIcon class="w-4 h-4 text-amber-500" />
                                Reset Password
                            </button>
                            
                            <div class="flex gap-3">
                                <button class="flex-1 h-14 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-2xl text-xs font-black uppercase tracking-[0.2em] hover:bg-indigo-100 transition-all flex items-center justify-center gap-2 active:scale-95">
                                    <SparklesIcon class="w-4 h-4" />
                                    AI Profile Audit
                                </button>
                                <button @click="showCareerDna = true" class="w-14 h-14 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-white transition-all shadow-sm">
                                    <HeartIcon class="w-6 h-6" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Neural Navigation System -->
                <div class="bg-slate-50/50 border-t border-slate-100 p-2 overflow-x-auto no-scrollbar">
                    <div class="flex gap-1 min-w-max justify-center">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.id"
                            @click="currentTab = tab.id"
                            class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-[0.2em] transition-all duration-300 flex items-center gap-3 group relative overflow-hidden"
                            :class="[
                                currentTab === tab.id
                                ? 'bg-white text-indigo-600 shadow-xl shadow-slate-200 border border-indigo-50'
                                : 'text-slate-400 hover:text-slate-900 hover:bg-white/50'
                            ]"
                        >
                             <component :is="tab.icon" class="w-4 h-4 transition-transform group-hover:scale-110" :class="currentTab === tab.id ? 'text-indigo-600' : 'text-slate-300'" />
                             {{ tab.name }}
                             <div v-if="currentTab === tab.id" class="absolute bottom-0 left-0 w-full h-1 bg-indigo-600"></div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contextual Data Payload -->
            <div class="min-h-[600px] relative">
                <Transition name="fade-slide" mode="out-in">
                    <!-- 1. Overview Tab -->
                    <div v-if="currentTab === 'overview'" :key="'overview'" class="animate-in fade-in slide-in-from-bottom-5 duration-500">
                        <EmployeeOverviewTab :employee="employee" />
                    </div>

                    <!-- 2. Personal Detail/Master Tab -->
                    <div v-else-if="currentTab === 'personal'" :key="'personal'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-8">
                            <EmployeePersonalTab :employee="employee" @refresh="fetchEmployee" />
                        </div>
                        <div class="space-y-8">
                            <EmployeeBankingTab :employee="employee" @refresh="fetchEmployee" />
                            
                            <!-- Statutory Compliance Module -->
                            <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                                <div class="absolute right-0 top-0 w-1.5 h-full bg-emerald-500 group-hover:w-2 transition-all"></div>
                                <div class="flex justify-between items-center mb-8">
                                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                                        Statutory Details
                                        <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
                                    </h3>
                                    <button @click="showStatutoryModal = true" class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm">
                                        <PencilSquareIcon class="w-4 h-4" />
                                    </button>
                                </div>
                                
                                <div class="space-y-6">
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-2">PAN Number</span>
                                        <span class="text-xs font-black text-slate-900 uppercase font-mono tracking-tighter">{{ employee.pan_number || 'Not provided' }}</span>
                                    </div>
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-2">UAN Number</span>
                                        <span class="text-xs font-black text-slate-900 uppercase font-mono tracking-tighter">{{ employee.uan_number || 'Not provided' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Family Tab -->
                    <div v-else-if="currentTab === 'family'" :key="'family'" class="bg-white rounded-[2.5rem] border border-slate-100 p-8 md:p-10 shadow-2xl shadow-slate-200/40 min-h-[500px]">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-6 pb-6 border-b border-slate-50">
                            <div>
                                <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] mb-2 flex items-center gap-3">
                                    <UserGroupIcon class="w-5 h-5 text-indigo-500" />
                                    Family & Dependents
                                </h3>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Emergency contacts and family members</p>
                            </div>
                            <button @click="openFamilyModal()" class="h-12 px-8 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all flex items-center gap-3 active:scale-95 group">
                                <PlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-12 transition-transform" />
                                Add Member
                            </button>
                        </div>

                        <div v-if="employee.families && employee.families.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div v-for="member in employee.families" :key="member.id" class="p-6 rounded-[2rem] border border-slate-100 bg-slate-50/50 relative group/member hover:border-indigo-200 hover:bg-white hover:shadow-2xl hover:shadow-indigo-500/5 transition-all">
                                <div class="flex items-center gap-5">
                                    <div class="h-14 w-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 font-black text-sm group-hover/member:bg-indigo-50 group-hover/member:text-indigo-600 transition-colors">
                                        {{ member.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight group-hover/member:text-indigo-600 transition-colors">{{ member.name }}</h4>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-xs font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ member.relationship }}</span>
                                            <span v-if="member.is_emergency_contact" class="text-xs font-black text-amber-600 uppercase tracking-widest border border-amber-200 px-2 py-0.5 rounded-full bg-white shadow-sm italic">Emergency Contact</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 pt-6 border-t border-slate-50 flex justify-end gap-2 opacity-0 group-hover/member:opacity-100 translate-y-2 group-hover/member:translate-y-0 transition-all">
                                     <button @click="openFamilyModal(member)" class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all shadow-sm"><PencilSquareIcon class="w-4 h-4" /></button>
                                     <button @click="deleteFamilyMember(member)" class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all shadow-sm"><TrashIcon class="w-4 h-4" /></button>
                                </div>
                            </div>
                        </div>
                        <!-- Empty State -->
                        <div v-else class="flex flex-col items-center justify-center py-32 text-slate-300 opacity-20 grayscale animate-pulse">
                            <UserGroupIcon class="h-20 w-20 mb-6" />
                            <p class="text-xs font-black uppercase tracking-[0.4em]">No family members added yet</p>
                        </div>
                    </div>

                    <!-- 4. Documents Tab -->
                    <div v-else-if="currentTab === 'documents'" :key="'documents'">
                        <EmployeeDocumentsTab :employee="employee" />
                    </div>

                    <!-- 5. History Tab -->
                    <div v-else-if="currentTab === 'history'" :key="'history'" class="bg-white rounded-[2.5rem] border border-slate-100 p-8 md:p-10 shadow-2xl shadow-slate-200/40 relative overflow-hidden group min-h-[600px]">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-12 gap-6 relative z-10">
                            <div>
                                <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                                    <ClockIcon class="w-5 h-5 text-indigo-500" />
                                    Employment History
                                </h3>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2">Audit trail of all profile changes and events</p>
                            </div>
                            <div class="px-6 py-2.5 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.3em] shadow-xl shadow-slate-200">
                                {{ history?.length || 0 }} Records
                            </div>
                        </div>

                        <!-- High-Fidelity Timeline -->
                        <div v-if="history && history.length > 0" class="relative z-10 space-y-12 before:absolute before:inset-0 before:ml-6 before:h-full before:w-0.5 before:bg-slate-100 before:shadow-inner">
                            <div v-for="log in history" :key="log.id" class="relative pl-14 group/log">
                                <!-- Specialized Marker -->
                                <div class="absolute left-0 top-1.5 h-12 w-12 rounded-2xl bg-white border-4 border-slate-50 shadow-xl flex items-center justify-center text-slate-300 group-hover/log:border-indigo-100 group-hover/log:text-indigo-600 transition-all duration-500 z-10 group-hover/log:rotate-12 group-hover/log:scale-110">
                                    <ClockIcon class="w-6 h-6" />
                                </div>
                                
                                <div class="bg-slate-50/50 p-6 rounded-[2rem] border border-transparent hover:border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500">
                                    <div class="flex flex-col sm:flex-row justify-between items-start mb-4 gap-3">
                                        <span class="text-xs font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full uppercase tracking-[0.2em] border border-indigo-100 shadow-sm">{{ new Date(log.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest opacity-60 font-mono">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                                    </div>
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-relaxed italic opacity-85">" {{ log.description }} "</h4>
                                    
                                    <div v-if="log.properties" class="mt-5 flex flex-wrap gap-2 pt-5 border-t border-slate-50">
                                        <div v-for="(val, key) in log.properties" :key="key" class="px-3 py-1 bg-white rounded-xl text-xs font-black text-slate-400 border border-slate-100 uppercase tracking-widest shadow-inner">
                                            {{ key }}: <span class="text-slate-700">{{ val }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="flex flex-col items-center justify-center py-32 text-slate-300 opacity-20 grayscale animate-pulse">
                            <ClockIcon class="h-20 w-20 mb-6" />
                            <p class="text-xs font-black uppercase tracking-[0.4em]">No history records found</p>
                        </div>
                    </div>

                    <!-- 6. Tax / TDS Tab -->
                    <div v-else-if="currentTab === 'tax'" :key="'tax'">
                          <EmployeeTaxTab :employee="employee" />
                    </div>

                    <!-- 7. Expenses Tab -->
                    <div v-else-if="currentTab === 'expenses'" :key="'expenses'">
                          <EmployeeExpensesTab :employee="employee" />
                    </div>

                    <!-- 8. Payslips Tab -->
                    <div v-else-if="currentTab === 'payslips'" :key="'payslips'">
                          <EmployeePayslipsTab :employee="employee" :payslips="payslips" />
                    </div>
                </Transition>
            </div>
        </div>

        <!-- System Modals Grid -->
        <CreateLoginModal :show="showCreateLogin" :employee="employee" @close="showCreateLogin = false" @saved="fetchEmployee" />
        <PasswordResetModal :show="showResetPassword" :employee="employee" @close="showResetPassword = false" />
        
        <BankDetailsModal 
            :show="showBankModal"
            :employee="employee"
            @close="showBankModal = false"
            @saved="fetchEmployee"
        />

        <StatutoryDetailsModal
            :show="showStatutoryModal"
            :employee="employee"
            @close="showStatutoryModal = false"
            @saved="fetchEmployee"
        />
        
        <CareerDnaModal :show="showCareerDna" :employee-id="employee.id" @close="showCareerDna = false" />

        <FamilyMemberModal 
            :show="showFamilyModal"
            :employee="employee"
            :member-data="selectedFamilyMember"
            @close="closeFamilyModal"
            @saved="fetchEmployee"
        />
    </MainLayout>
</template>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { 
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-slide-enter-from { 
    opacity: 0; 
    transform: translateY(20px);
}
.fade-slide-leave-to { 
    opacity: 0; 
    transform: translateY(-20px);
}
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
