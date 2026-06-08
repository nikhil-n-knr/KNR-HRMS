<template>
    <Head title="Employee Synthesis" />

    <div class="fixed inset-0 overflow-hidden z-[60] flex justify-end">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity duration-500" @click="close"></div>

        <div class="relative w-screen max-w-2xl bg-white shadow-[0_0_50px_rgba(0,0,0,0.1)] flex flex-col h-full transform transition-all duration-500 ease-out border-l border-white/20">
            
            <!-- Header -->
            <div class="px-8 py-8 bg-gradient-to-br from-indigo-600 via-indigo-700 to-slate-900 relative overflow-hidden shrink-0">
                <div class="absolute top-0 right-0 p-12 opacity-10 pointer-events-none">
                    <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                </div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-white tracking-tight">Employee Synthesis</h2>
                        <p class="text-indigo-100 text-sm font-black uppercase tracking-[0.2em] mt-2 opacity-80">Finalizing protocol for {{ candidate.first_name }} {{ candidate.last_name }}</p>
                    </div>
                    <button @click="close" class="h-12 w-12 rounded-2xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all group">
                        <svg class="h-6 w-6 group-hover:rotate-90 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8 bg-slate-50/50 space-y-8 scrollbar-hide">
                
                <form @submit.prevent="submit" class="space-y-8 pb-12">
                    
                    <!-- 1. Offer Intelligence Summary -->
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500"></div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">Offer Intelligence Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div class="space-y-1">
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest block leading-none">Designation</span>
                                <span class="text-sm font-black text-slate-800 tracking-tight">{{ offer.designation }}</span>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest block leading-none">Join Cycle</span>
                                <span class="text-sm font-black text-slate-800 tracking-tight">{{ new Date(offer.joining_date).toLocaleDateString() }}</span>
                            </div>
                            <div class="space-y-1 col-span-2 md:col-span-1">
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest block leading-none">Compensation</span>
                                <span class="text-sm font-black text-emerald-600 tracking-tight">{{ offer.salary_currency }} {{ Number(offer.salary_amount).toLocaleString() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. System Architecture -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">System Architecture</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                            <!-- Employee Code -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Personnel Identifier (Code) <span class="text-indigo-500">*</span></label>
                                <input v-model="form.employee_code" type="text" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all" placeholder="e.g. EMP-001" required>
                                <div v-if="form.errors.employee_code" class="text-sm font-bold text-rose-500 mt-2">{{ form.errors.employee_code }}</div>
                            </div>

                            <!-- Designation -->
                            <div>
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Operational Title <span class="text-indigo-500">*</span></label>
                                <input v-model="form.designation" type="text" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all" required>
                                <div v-if="form.errors.designation" class="text-sm font-bold text-rose-500 mt-2">{{ form.errors.designation }}</div>
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Assigned Sector <span class="text-indigo-500">*</span></label>
                                <select v-model="form.department_id" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all appearance-none cursor-pointer">
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <div v-if="form.errors.department_id" class="text-sm font-bold text-rose-500 mt-2">{{ form.errors.department_id }}</div>
                            </div>

                            <!-- Role -->
                            <div>
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Privilege Level <span class="text-indigo-500">*</span></label>
                                <select v-model="form.role_id" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all appearance-none cursor-pointer">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                                <div v-if="form.errors.role_id" class="text-sm font-bold text-rose-500 mt-2">{{ form.errors.role_id }}</div>
                            </div>

                             <!-- Reporting Manager -->
                             <div>
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Command Liaison (Manager)</label>
                                <select v-model="form.manager_id" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all appearance-none cursor-pointer">
                                    <option :value="null">-- Independent --</option>
                                    <option v-for="mgr in managers" :key="mgr.id" :value="mgr.id">{{ mgr.name }}</option>
                                </select>
                                <div v-if="form.errors.manager_id" class="text-sm font-bold text-rose-500 mt-2">{{ form.errors.manager_id }}</div>
                            </div>

                            <!-- Joining Date -->
                             <div class="md:col-span-2">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Activation Cycle <span class="text-indigo-500">*</span></label>
                                <input v-model="form.joining_date" type="date" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all" required>
                                <div v-if="form.errors.joining_date" class="text-sm font-bold text-rose-500 mt-2">{{ form.errors.joining_date }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Neural Access Matrix -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                            </div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Neural Access Matrix</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                            <div>
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Primary Cipher (Email) <span class="text-indigo-500">*</span></label>
                                <input v-model="form.email" type="email" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all" required>
                            </div>
                             <div>
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2 block">Access Key (Password)</label>
                                <input v-model="form.password" type="text" class="w-full h-14 px-5 rounded-2xl border-gray-200 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-700 bg-slate-50/50 transition-all" placeholder="Auto-generate via core">
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.15em] mt-3 ml-1 flex items-center gap-2">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Automated dispatch if left undefined
                                </p>
                            </div>
                        </div>
                    </div>

                     <!-- Candidate Preferences -->
                     <div v-if="offer.candidate_preferences" class="bg-indigo-600 p-8 rounded-[2rem] text-white shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                             <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <h3 class="text-sm font-black text-white/60 uppercase tracking-widest mb-6">Subject Alignment Profile</h3>
                         <div class="grid grid-cols-2 gap-y-6 gap-x-8">
                            <div v-for="(val, key) in offer.candidate_preferences" :key="key" class="space-y-1">
                                <span class="text-sm font-black text-indigo-200 uppercase tracking-widest block leading-none opacity-60">{{ key.replace('_', ' ') }}</span>
                                <span class="text-xs font-black tracking-tight text-white">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Footer -->
             <div class="px-10 py-8 bg-white border-t border-gray-100 flex items-center justify-between shrink-0">
                <button @click="close" type="button" class="text-sm font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                    Abort Protocol
                </button>
                <button @click="submit" :disabled="form.processing" class="h-14 px-8 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-xl shadow-indigo-100 disabled:opacity-50 flex items-center gap-3">
                    <span v-if="form.processing">Processing Neural Logic...</span>
                    <span v-else>Confirm & Synthesize Personnel</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    candidate: Object,
    offer: Object,
    departments: Array,
    roles: Array,
    managers: Array
});

// Initialize form with Offer Details
const form = useForm({
    employee_code: '',
    designation: props.offer.designation,
    department_id: null, 
    role_id: null,
    manager_id: null,
    joining_date: props.offer.joining_date,
    email: props.candidate.email,
    password: ''
});

const submit = () => {
    form.post(route('candidates.onboard.store', props.candidate.id), {
        onSuccess: () => {
        }
    });
};

const close = () => {
    window.history.back();
};
</script>
