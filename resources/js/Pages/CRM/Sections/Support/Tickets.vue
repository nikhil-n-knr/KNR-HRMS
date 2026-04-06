<template>
    <div class="space-y-8 text-left">
        <!-- Support Operations -->
        <div class="flex justify-between items-end pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Support Operations</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-sky-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Resolution & Service Deck</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="relative group">
                    <input
                        type="text"
                        placeholder="Search ticket IDs..."
                        class="pl-12 pr-6 py-3 bg-gray-50 border-transparent rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:bg-white focus:border-sky-500 transition-all text-sm font-semibold w-64 shadow-inner"
                    />
                    <i class="fas fa-search absolute left-5 top-4 text-gray-300 group-focus-within:text-sky-500 transition-colors"></i>
                </div>
                <button @click="showCreateModal = true" class="px-8 py-3 bg-sky-600 text-white rounded-2xl hover:bg-sky-700 transition-all font-black text-sm shadow-xl shadow-sky-100 flex items-center group">
                    <i class="fas fa-ticket-alt mr-2 text-xs group-hover:rotate-12 transition-transform"></i>
                    NEW TICKET
                </button>
            </div>
        </div>

        <!-- Tickets Matrix -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100 text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-widest">Incident Details</th>
                        <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-widest text-center">Stakeholder</th>
                        <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-widest text-center">Escalation</th>
                        <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-widest text-center">State</th>
                        <th class="px-10 py-6 text-sm font-black text-gray-400 uppercase tracking-widest text-right">Activity</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <tr v-for="ticket in (tickets.data || tickets)" :key="ticket.id" class="hover:bg-sky-50/20 transition-colors group">
                        <td class="px-10 py-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-sky-600 group-hover:text-white transition-all shadow-sm">
                                    <span class="text-sm font-black">#{{ ticket.id }}</span>
                                </div>
                                <div class="ml-5">
                                    <div class="text-sm font-black text-gray-900 leading-tight">{{ ticket.subject }}</div>
                                    <div class="text-sm font-bold text-gray-400 uppercase mt-1 tracking-widest">Origin: {{ ticket.source || 'Dashboard' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <div class="flex flex-col items-center">
                                <img :src="`https://ui-avatars.com/api/?name=${ticket.contact?.first_name}+${ticket.contact?.last_name}&background=random`" class="w-8 h-8 rounded-xl shadow-sm mb-2">
                                <span class="text-sm font-black text-gray-700 uppercase">{{ ticket.contact?.first_name }} {{ ticket.contact?.last_name }}</span>
                            </div>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <span :class="priorityClass(ticket.priority)" class="px-4 py-1.5 rounded-xl text-sm font-black uppercase tracking-[0.1em] border">
                                {{ ticket.priority }}
                            </span>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <span :class="statusClass(ticket.status)" class="px-4 py-1.5 rounded-xl text-sm font-black uppercase tracking-[0.1em] border">
                                {{ ticket.status }}
                            </span>
                        </td>
                        <td class="px-10 py-6 text-right">
                             <div class="text-xs font-black text-gray-500">{{ ticket.updated_at_human || 'Just now' }}</div>
                             <div class="text-xs font-bold text-sky-400 uppercase mt-1 tracking-widest">Live Pulse</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State -->
            <div v-if="!(tickets.data || tickets).length" class="text-center py-32 bg-gray-50/10">
                <div class="w-24 h-24 bg-white rounded-[32px] shadow-sm border border-gray-100 flex items-center justify-center mx-auto mb-8 transform rotate-6">
                    <i class="fas fa-check-double text-4xl text-gray-200"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Zero Incidents</h3>
                <p class="text-gray-500 font-medium max-w-xs mx-auto text-sm leading-relaxed">All support channels are currently clear. High stability detected in service cycles.</p>
            </div>
        </div>

        <!-- NEW TICKET MODAL -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
             <div class="relative bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white text-left">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-sky-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg shadow-sky-100">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight">Log Incident</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Resolution Protocol Initializer</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-10 space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Problem Subject</label>
                             <input v-model="form.subject" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner" placeholder="Detailed issue overview...">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Stakeholder</label>
                                <select v-model="form.contact_id" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner">
                                    <option value="">Select Contact...</option>
                                    <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                                        {{ contact.first_name }} {{ contact.last_name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Escalation Path</label>
                                <select v-model="form.priority" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner text-rose-500">
                                    <option value="low">Standard Focus</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="high">High Velocity</option>
                                    <option value="urgent">CRITICAL ENGINE</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Incident Deep Dive</label>
                             <textarea v-model="form.description" rows="4" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-sky-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner" placeholder="Provide raw logs or behavioral details..."></textarea>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full bg-sky-600 text-white py-4 rounded-3xl font-black text-sm hover:bg-sky-700 transition-all shadow-xl shadow-sky-100 mt-4 disabled:opacity-50">
                        {{ form.processing ? 'UPLOADING...' : 'PUBLISH RESOLUTION TICKET' }}
                    </button>
                </form>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    tickets: { type: [Array, Object], default: () => [] },
    contacts: { type: Array, default: () => [] }
});

const showCreateModal = ref(false);

const form = useForm({
    subject: '',
    contact_id: '',
    priority: 'low',
    description: '',
    source: 'dashboard'
});

const submit = () => {
    form.post(route('crm.tickets.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const priorityClass = (priority) => {
    switch (priority?.toLowerCase()) {
        case 'urgent': return 'bg-rose-50 text-rose-600 border-rose-100';
        case 'high': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'medium': return 'bg-sky-50 text-sky-600 border-sky-100';
        default: return 'bg-gray-50 text-gray-400 border-gray-100';
    }
};

const statusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'open': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'pending': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'resolved': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        default: return 'bg-gray-50 text-gray-400 border-gray-100';
    }
};
</script>
