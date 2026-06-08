<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    policy: Object,
    embedded: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const activeTab = ref('general');

// Initialize with defaults if creating, or parse if editing
const p = props.policy || {};
const rules = p.rules || {};
const wfh = p.wfh_policy || null;
const ot = p.overtime_policy || null; // Map from DB column
const ts = p.timesheet_policy || null;

const form = useForm({
    name: p.name || '',
    priority: p.priority || 0,
    sandwich_rule_enabled: p.sandwich_rule_enabled || false,
    late_mark_threshold: p.late_mark_threshold || 3,
    deduct_amount: p.deduction_rule?.deduct_leave || 0.5,
    deduct_type: p.deduction_rule?.type || 'CL',
    
    // JSON Fields - Flatted for UI, will restructure on submit
    grace_late_entry: rules.grace_late_entry || 15,
    half_day_hours: rules.half_day_hours || 4,
    
    // WFH
    wfh_enabled: !!wfh,
    wfh_min_minutes: wfh?.min_minutes || 480,
    wfh_latest_check_in: wfh?.latest_check_in || '10:00',
    
    // OT
    ot_enabled: !!ot,
    ot_min_minutes: ot?.min_minutes || 30,
    ot_multiplier: ot?.multiplier || 1.0,

    // Timesheets
    ts_enabled: !!ts,
    ts_min_hours: ts?.daily_min_hours || 8.0,
    ts_max_hours: ts?.daily_max_hours || 12.0,
    ts_allow_future: ts?.allow_future_days || false,
    ts_require_project: ts?.require_project !== false // Default to true
});

// Watch for prop changes to re-initialize form
watch(() => props.policy, (newVal) => {
    if (newVal) {
        const rules = newVal.rules || {};
        const wfh = newVal.wfh_policy || null;
        const ot = newVal.overtime_policy || null;
        const ts = newVal.timesheet_policy || null;

        form.name = newVal.name || '';
        form.priority = newVal.priority || 0;
        form.sandwich_rule_enabled = newVal.sandwich_rule_enabled || false;
        form.late_mark_threshold = newVal.late_mark_threshold || 3;
        form.deduct_amount = newVal.deduction_rule?.deduct_leave || 0.5;
        form.deduct_type = newVal.deduction_rule?.type || 'CL';
        
        form.grace_late_entry = rules.grace_late_entry || 15;
        form.half_day_hours = rules.half_day_hours || 4;
        
        form.wfh_enabled = !!wfh;
        form.wfh_min_minutes = wfh?.min_minutes || 480;
        form.wfh_latest_check_in = wfh?.latest_check_in || '10:00';
        
        form.ot_enabled = !!ot;
        form.ot_min_minutes = ot?.min_minutes || 30;
        form.ot_multiplier = ot?.multiplier || 1.0;

        form.ts_enabled = !!ts;
        form.ts_min_hours = ts?.daily_min_hours || 8.0;
        form.ts_max_hours = ts?.daily_max_hours || 12.0;
        form.ts_allow_future = ts?.allow_future_days || false;
        form.ts_require_project = ts?.require_project !== false;
    }
}, { deep: true });

// Frontend Validation Logic
const timesheetError = ref(null);

watch(() => [form.ts_min_hours, form.ts_max_hours], ([min, max]) => {
    if (parseFloat(min) > parseFloat(max)) {
        timesheetError.value = "Minimum daily hours cannot be greater than maximum.";
    } else {
        timesheetError.value = null;
    }
});

const submit = () => {
    if (timesheetError.value && form.ts_enabled) return;

    // Reconstruct JSON objects
    const rules = {
        grace_late_entry: form.grace_late_entry,
        half_day_hours: form.half_day_hours
    };
    
    const wfh_policy = form.wfh_enabled ? {
        min_minutes: form.wfh_min_minutes,
        latest_check_in: form.wfh_latest_check_in
    } : null;

    const overtime_policy = form.ot_enabled ? {
        min_minutes: form.ot_min_minutes,
        multiplier: form.ot_multiplier
    } : null;

    const timesheet_policy = form.ts_enabled ? {
        daily_min_hours: form.ts_min_hours,
        daily_max_hours: form.ts_max_hours,
        allow_future_days: form.ts_allow_future,
        require_project: form.ts_require_project
    } : null;

    const payload = {
        id: props.policy?.id, // Essential for backend fallback if URL ID is missing
        name: form.name,
        priority: form.priority,
        sandwich_rule_enabled: form.sandwich_rule_enabled,
        late_mark_threshold: form.late_mark_threshold,
        deduction_rule: {
            deduct_leave: form.deduct_amount,
            type: form.deduct_type
        },
        rules: rules,
        wfh_policy: wfh_policy,
        overtime_policy: overtime_policy, // Map to DB column name
        timesheet_policy: timesheet_policy
    };

    if (props.policy && props.policy.id) {
        console.log('Updating Policy (Manual URL):', props.policy.id, props.policy);
        
        // Manual URL construction to bypass potential Ziggy issues
        const url = `/admin/attendance/policies/${props.policy.id}`;
        
        form.transform(() => payload).put(url, {
            onSuccess: () => emit('close')
        });
    } else {
        console.log('Creating New Policy');
        form.transform(() => payload).post(route('admin.attendance.policies.store'), {
            onSuccess: () => emit('close')
        });
    }
};

const tabs = [
    { id: 'general', label: 'General & Timings', icon: '⚙️' },
    { id: 'wfh', label: 'Remote / WFH', icon: '🏠' },
    { id: 'ot', label: 'Overtime', icon: '⏱️' },
    { id: 'timesheets', label: 'Timesheets', icon: '📝' }
];
</script>

<template>
    <div class="bg-white rounded-lg overflow-hidden flex flex-col h-full bg-white">
        <!-- Header (Hidden in Embedded mode) -->
        <div v-if="!embedded" class="px-8 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/80 backdrop-blur-md">
            <div>
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-tight leading-none">{{ policy ? 'Edit Policy protocol' : 'New Policy protocol' }}</h3>
                <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1.5 leading-none">Governance & Compliance Architect</p>
            </div>
            <button @click="$emit('close')" class="text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg p-2 transition-all">
                <span class="sr-only">Close</span>
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="flex flex-1 min-h-[400px]">
            <!-- Sidebar Tabs -->
            <div class="w-56 bg-white border-r border-gray-100 py-4 flex flex-col gap-1">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="w-full text-left px-6 py-3.5 text-sm font-black uppercase tracking-widest transition-all flex items-center gap-3 border-l-4"
                    :class="activeTab === tab.id ? 'bg-emerald-50 text-emerald-700 border-emerald-600' : 'border-transparent text-gray-400 hover:text-gray-600 hover:bg-gray-50'"
                >
                    <span class="text-xs">{{ tab.icon }}</span>
                    {{ tab.label }}
                </button>
            </div>

            <!-- Form Area -->
            <div class="flex-1 p-6 overflow-y-auto">
                <!-- GENERAL -->
                <div v-show="activeTab === 'general'" class="space-y-6">
                    <div>
                        <InputLabel value="Policy Designation" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                        <TextInput v-model="form.name" type="text" placeholder="e.g. Standard Operations" class="w-full h-11 text-base font-black uppercase tracking-tight" />
                    </div>
                
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Priority Level (0-100)" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                            <TextInput v-model="form.priority" type="number" class="w-full h-11 text-base font-black" />
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Selection conflict resolution weight.</p>
                        </div>
                        <div>
                            <InputLabel value="Violation Threshold (Days)" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                            <TextInput v-model="form.late_mark_threshold" type="number" class="w-full h-11 text-base font-black" />
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">X violation = 1 Leave Deduction.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Temporal Grace (Mins)" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                            <TextInput v-model="form.grace_late_entry" type="number" class="w-full h-11 text-base font-black" />
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Allowed delay before marking 'Late'.</p>
                        </div>
                        <div>
                             <InputLabel value="Partial Day Matrix (Hours)" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                             <TextInput v-model="form.half_day_hours" type="number" step="0.5" class="w-full h-11 text-base font-black" />
                             <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Threshold for partial day marking.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start mt-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input v-model="form.sandwich_rule_enabled" type="checkbox" class="rounded border-gray-200 text-emerald-600 shadow-sm focus:ring-emerald-500 h-5 w-5 transition-all">
                            <div>
                                <span class="block text-sm font-black text-gray-700 uppercase tracking-widest">Enable Sandwich Protocol</span>
                                <span class="text-xs text-gray-400 font-bold uppercase tracking-widest group-hover:text-gray-500 leading-none block mt-1">Include weekends in leave if absent on boundaries.</span>
                            </div>
                        </label>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-100 font-sans">
                        <h4 class="text-sm font-black text-gray-900 mb-4 flex items-center gap-2 uppercase tracking-widest">
                            ⚖️ Deduction Matrix
                        </h4>
                        <div class="grid grid-cols-2 gap-6 bg-gray-50/50 p-5 rounded-2xl border border-gray-100">
                             <div>
                                <InputLabel value="Deduct Amount (Days)" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                <TextInput v-model="form.deduct_amount" type="number" step="0.5" class="w-full h-11 text-base font-black" />
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Penalty per violation cycle.</p>
                            </div>
                            <div>
                                <InputLabel value="Draw From Pool" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                <select v-model="form.deduct_type" class="block w-full h-11 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-black uppercase tracking-widest transition-all cursor-pointer">
                                    <option value="CL">Casual Leave (CL)</option>
                                    <option value="SL">Sick Leave (SL)</option>
                                    <option value="LWP">Leave Without Pay (LWP)</option>
                                </select>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Source leave balance for penalty.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WFH -->
                <div v-show="activeTab === 'wfh'" class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-xl border border-emerald-100 mb-6">
                        <div>
                            <h4 class="text-sm font-black text-emerald-900 uppercase tracking-widest leading-none">Remote Work Protocol</h4>
                            <p class="text-xs text-emerald-700 font-bold uppercase tracking-widest mt-1.5 leading-none">Rules applied when an operative marks "WFH".</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.wfh_enabled" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-emerald-100 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-emerald-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                        </label>
                    </div>

                    <div v-if="form.wfh_enabled" class="space-y-6 animate-fade-in-up">
                         <div class="grid grid-cols-2 gap-6">
                             <div>
                                <InputLabel value="Minimum Minutes Required" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                <TextInput v-model="form.wfh_min_minutes" type="number" class="w-full h-11 text-base font-black" />
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Total login time to count as Present.</p>
                            </div>
                             <div>
                                <InputLabel value="Latest Check-in Time" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                <TextInput v-model="form.wfh_latest_check_in" type="time" class="w-full h-11 text-base font-black" />
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Mark 'Late' if first pulse is after this.</p>
                            </div>
                         </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed border-gray-100">
                        <i class="fas fa-home-slash text-2xl mb-3 block opacity-20"></i>
                        <span class="text-sm font-black uppercase tracking-widest">WFH protocol is disabled for this node.</span>
                    </div>
                </div>

                <!-- OT -->
                <div v-show="activeTab === 'ot'" class="space-y-6">
                      <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-xl border border-emerald-100 mb-6">
                        <div>
                            <h4 class="text-sm font-black text-emerald-900 uppercase tracking-widest leading-none">Overtime Matrix</h4>
                            <p class="text-xs text-emerald-700 font-bold uppercase tracking-widest mt-1.5 leading-none">Define how supplemental hours are compensated.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.ot_enabled" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-emerald-100 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-emerald-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                        </label>
                    </div>

                     <div v-if="form.ot_enabled" class="space-y-6 animate-fade-in-up">
                         <div class="grid grid-cols-2 gap-6">
                             <div>
                                <InputLabel value="Minimum Displacement (Mins)" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                <TextInput v-model="form.ot_min_minutes" type="number" class="w-full h-11 text-base font-black" />
                                 <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Ignore displacement less than this duration.</p>
                            </div>
                             <div>
                                <InputLabel value="Yield Multiplier" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                <TextInput v-model="form.ot_multiplier" type="number" step="0.5" class="w-full h-11 text-base font-black" />
                                 <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1.5 px-1 leading-none">Multiplier for supplemental payload (e.g. 1.5x).</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed border-gray-100">
                        <i class="fas fa-clock-rotate-left text-2xl mb-3 block opacity-20"></i>
                        <span class="text-sm font-black uppercase tracking-widest">Supplemental yield calculation is disabled.</span>
                    </div>
                </div>

                <!-- Timesheets -->
                <div v-show="activeTab === 'timesheets'" class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-xl border border-emerald-100 mb-6">
                        <div>
                             <h4 class="text-sm font-black text-emerald-900 uppercase tracking-widest leading-none">Timesheet Governance</h4>
                             <p class="text-xs text-emerald-700 font-bold uppercase tracking-widest mt-1.5 leading-none">Enforce daily pulse limits and project tagging.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.ts_enabled" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-emerald-100 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-emerald-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                        </label>
                    </div>

                    <div v-if="form.ts_enabled" class="space-y-6 animate-fade-in-up">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                 <InputLabel value="Min. Spectral Hours" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                 <TextInput v-model="form.ts_min_hours" type="number" step="0.5" class="w-full h-11 text-base font-black" :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': timesheetError}" />
                            </div>
                            <div>
                                 <InputLabel value="Max. Spectral Hours" class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2 px-1" />
                                 <TextInput v-model="form.ts_max_hours" type="number" step="0.5" class="w-full h-11 text-base font-black" :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': timesheetError}" />
                            </div>
                        </div>
                        <div v-if="timesheetError" class="text-red-600 text-sm font-black uppercase tracking-widest bg-red-50 p-3 rounded-lg border border-red-100">
                            ⚠️ {{ timesheetError.toUpperCase() }}
                        </div>

                         <div class="space-y-4 pt-4 border-t border-gray-100">
                             <label class="flex items-center gap-3 cursor-pointer group">
                                  <input v-model="form.ts_require_project" type="checkbox" class="rounded border-gray-200 text-emerald-600 shadow-sm focus:ring-emerald-500 h-5 w-5 transition-all">
                                  <div>
                                      <span class="block text-sm font-black text-gray-700 uppercase tracking-widest">Require Project Designation</span>
                                      <span class="text-xs text-gray-400 font-bold uppercase tracking-widest leading-none block mt-1">Operatives must tag a node for every entry.</span>
                                  </div>
                             </label>
                             <label class="flex items-center gap-3 cursor-pointer group">
                                  <input v-model="form.ts_allow_future" type="checkbox" class="rounded border-gray-200 text-emerald-600 shadow-sm focus:ring-emerald-500 h-5 w-5 transition-all">
                                  <div>
                                      <span class="block text-sm font-black text-gray-700 uppercase tracking-widest">Allow Prospective Entries</span>
                                      <span class="text-xs text-gray-400 font-bold uppercase tracking-widest leading-none block mt-1">Can operatives log time for prospective cycles?</span>
                                  </div>
                             </label>
                         </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed border-gray-100">
                        <i class="fas fa-file-invoice text-2xl mb-3 block opacity-20"></i>
                        <span class="text-sm font-black uppercase tracking-widest">Timesheets are disabled for this protocol.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50/80 backdrop-blur-md border-t border-gray-100 flex justify-end gap-3 items-center">
             <div v-show="form.isDirty" class="text-sm text-amber-600 font-black uppercase tracking-widest mr-auto animate-pulse">
                Unsaved calibration changes
             </div>
             <SecondaryButton @click="$emit('close')" class="text-sm font-black uppercase tracking-widest h-10 px-6 rounded-lg">Abort</SecondaryButton>
             <PrimaryButton @click="submit" :disabled="form.processing || !!timesheetError" class="ml-2 h-10 px-8 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-md active:scale-95 disabled:opacity-50">
                {{ form.processing ? 'DEPLOING...' : (policy ? 'Update protocol' : 'Initialize protocol') }}
             </PrimaryButton>
        </div>
    </div>
</template>
