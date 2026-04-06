<template>
    <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-2xl shadow-indigo-500/10 rounded-[2rem] border border-gray-100 p-8 md:p-12 max-w-4xl mx-auto font-inter">
        <div class="mb-10">
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Record Operational Signal</h2>
            <p class="text-slate-400 text-sm font-black uppercase tracking-[0.2em] mt-1">Initialize Forensic Bug Tracking Ticket</p>
        </div>

        <form @submit.prevent="submitBug" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Project Selection -->
                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Infrastructure Focus</label>
                    <select v-model="form.project_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:bg-white focus:border-indigo-500 transition-all shadow-sm" required>
                            <option value="" disabled>Select Target Project</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.project_id" />
                </div>
                <!-- Module Selection -->
                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Zone Identifier (Optional)</label>
                    <TextInput v-model="form.module_id" type="number" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:bg-white transition-all shadow-sm" placeholder="e.g. 101" />
                     <InputError :message="form.errors.module_id" />
                </div>
            </div>

            <!-- Subject -->
            <div class="space-y-3">
                <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Operational Subject</label>
                <TextInput v-model="form.subject" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-6 text-lg font-black tracking-tight focus:ring-4 focus:ring-indigo-500/5 focus:bg-white transition-all shadow-sm" placeholder="Signal Summary" required />
                <InputError :message="form.errors.subject" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="space-y-3">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Severity Index</label>
                        <select v-model="form.severity" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-rose-500/5 focus:bg-white focus:border-rose-500 transition-all shadow-sm">
                            <option value="low">L1 - Low</option>
                            <option value="medium">L2 - Medium</option>
                            <option value="high">L3 - High</option>
                            <option value="critical">L4 - Critical</option>
                        </select>
                </div>
                <div class="space-y-3">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Priority Level</label>
                        <select v-model="form.priority" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-amber-500/5 focus:bg-white focus:border-amber-500 transition-all shadow-sm">
                            <option value="low">Routine</option>
                            <option value="normal">Standard</option>
                            <option value="high">Elevated</option>
                            <option value="urgent">Immediate</option>
                        </select>
                </div>
                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Routing Target</label>
                     <select v-model="form.assignee_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-emerald-500/5 focus:bg-white focus:border-emerald-500 transition-all shadow-sm">
                        <option :value="null">Triage Center</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-3">
                <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Analysis & Forensics</label>
                <textarea v-model="form.description" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/5 focus:bg-white focus:border-indigo-500 transition-all shadow-sm min-h-[160px]" placeholder="Detailed technical observation..."></textarea>
                 <InputError :message="form.errors.description" />
            </div>

            <!-- Steps -->
            <div class="space-y-3">
                <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Replication Protocol</label>
                <textarea v-model="form.steps_to_reproduce" class="w-full bg-slate-50 border-transparent rounded-2xl px-5 py-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/5 focus:bg-white focus:border-indigo-500 transition-all shadow-sm min-h-[120px]" placeholder="1. Navigate to..."></textarea>
            </div>

            <!-- Attachments -->
             <div class="space-y-3">
                <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Evidence Assets</label>
                <div class="mt-1 flex justify-center px-6 pt-10 pb-10 border-2 border-slate-100 border-dashed rounded-[2rem] bg-slate-50/50 hover:bg-slate-50 transition-colors group">
                    <div class="space-y-4 text-center">
                        <div class="mx-auto h-16 w-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-slate-300 group-hover:text-indigo-500 transition-colors">
                            <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="text-xs text-slate-600">
                            <label for="file-upload" class="relative cursor-pointer font-black text-indigo-600 hover:text-indigo-500 focus-within:outline-none uppercase tracking-widest">
                                <span>Inject Files</span>
                                <input id="file-upload" name="file-upload" type="file" multiple @change="form.attachments = $event.target.files" class="sr-only" />
                            </label>
                            <p class="mt-1 text-slate-400 font-medium">PNG, JPG, PDF up to 10MB</p>
                        </div>
                    </div>
                </div>
                 <div v-if="form.attachments.length" class="mt-4 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-black uppercase tracking-widest inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    {{ form.attachments.length }} Payloads Stage
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-8 border-t border-slate-100">
                 <button type="button" @click="router.visit(route('bugs.index'))" class="w-full sm:w-auto px-8 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all">
                    Cancel
                </button>
                <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-10 py-4 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-50">
                    Engage Ticket
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps(['projects']);

const form = useForm({
    project_id: '',
    module_id: '',
    subject: '',
    description: '',
    steps_to_reproduce: '',
    severity: 'medium',
    priority: 'normal',
    assignee_id: null,
    attachments: [] 
});

const submitBug = () => {
    form.post(route('bugs.store'), {
        onSuccess: () => {
            // Redirect to Tracker list
             router.visit(route('bugs.index', { tab: 'tracker' }));
        }
    });
};
</script>
