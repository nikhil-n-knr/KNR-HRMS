<template>
    <div class="h-full flex flex-col">
        <!-- New Activity Input -->
        <div class="mb-8 bg-gray-50 rounded-2xl p-4 border border-gray-100">
            <div class="flex space-x-2 mb-4">
                <button 
                    v-for="type in activityTypes" 
                    :key="type.id"
                    @click="activeType = type.id"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all flex items-center gap-2',
                        activeType === type.id 
                            ? `bg-${type.color}-100 text-${type.color}-600 shadow-sm ring-1 ring-${type.color}-200` 
                            : 'bg-white text-gray-400 hover:text-gray-600 border border-transparent hover:border-gray-200'
                    ]"
                >
                    <i :class="type.icon"></i>
                    {{ type.label }}
                </button>
            </div>

            <form @submit.prevent="logActivity">
                <input 
                    v-model="form.subject"
                    type="text" 
                    :placeholder="activeTypeConfig.placeholder" 
                    class="w-full bg-white border-0 rounded-xl px-4 py-3 text-sm font-semibold shadow-sm focus:ring-2 focus:ring-blue-500 mb-2 placeholder-gray-300"
                    required
                >
                <textarea 
                    v-model="form.description"
                    placeholder="Add details (optional)..."
                    class="w-full bg-white border-0 rounded-xl px-4 py-3 text-sm resize-none h-20 shadow-sm focus:ring-2 focus:ring-blue-500 placeholder-gray-300"
                ></textarea>
                
                <div class="flex justify-between items-center mt-3">
                    <div class="flex items-center text-xs text-gray-400 font-medium">
                        <i class="fas fa-clock mr-2"></i>
                        Logged by You considering now
                    </div>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        :class="`bg-${activeTypeConfig.color}-500 hover:bg-${activeTypeConfig.color}-600 text-white px-6 py-2 rounded-xl text-xs font-bold uppercase tracking-widest shadow-lg shadow-${activeTypeConfig.color}-200 transition-all`"
                    >
                        Log {{ activeTypeConfig.label }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Timeline -->
        <div v-if="activities.length > 0" class="flex-1 overflow-y-auto space-y-6 pl-4 relative">
            <div class="absolute left-[19px] top-0 bottom-0 w-0.5 bg-gray-100"></div>

            <div v-for="activity in activities" :key="activity.id" class="relative group">
                <div class="absolute -left-[24px] mt-1.5 h-3 w-3 rounded-full border-2 border-white shadow-sm z-10"
                     :class="getTypeColor(activity.type)">
                </div>
                
                <div class="p-5 rounded-2xl border transition-all ml-4 relative"
                     :class="activity.type === 'email' && activity.direction === 'inbound' ? 'bg-indigo-50/30 border-indigo-200 shadow-sm ring-1 ring-inset ring-indigo-50 hover:shadow-md hover:bg-white' : 'bg-white border-gray-100 shadow-sm hover:shadow-md'">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2">
                             <span v-if="activity.type === 'email' && activity.direction === 'inbound'" class="text-[10px] uppercase font-black tracking-widest px-2 py-1 rounded bg-indigo-100 text-indigo-700 flex items-center gap-1 shadow-sm">
                                <i class="fas fa-inbox"></i> Incoming Email
                             </span>
                             <span v-else :class="`text-[10px] uppercase font-black tracking-widest px-2 py-1 rounded bg-gray-100 text-gray-500`">
                                {{ activity.type }}
                             </span>
                             <span class="text-[10px] text-gray-400 font-bold uppercase">
                                {{ formatDate(activity.timestamp || activity.created_at) }}
                             </span>
                             <span v-if="activity.type === 'email' && activity.status === 'unread'" class="w-2 h-2 rounded-full bg-rose-500 shadow-sm shadow-rose-200"></span>
                        </div>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                             <!-- Action buttons could go here -->
                        </div>
                    </div>
                    <h5 class="text-sm font-bold text-gray-900 mb-1">{{ activity.subject }}</h5>
                    <p class="text-xs text-gray-500 leading-relaxed font-medium">{{ activity.description }}</p>
                    
                    <div class="mt-3 flex items-center gap-2" v-if="activity.creator">
                        <div class="h-5 w-5 rounded-full bg-gray-200 border border-white text-sm flex items-center justify-center font-bold text-gray-500">
                             {{ activity.creator.name[0] }}
                        </div>
                        <span class="text-sm text-gray-400 font-bold uppercase">{{ activity.creator.name }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-history text-xl text-gray-300"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-widest">No Activity Yet</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';

const props = defineProps({
    activities: { type: Array, default: () => [] },
    parentType: { type: String, required: true }, // e.g., 'App\Models\CRM\Contact'
    parentId: { type: Number, required: true }
});

const activeType = ref('note');

const activityTypes = [
    { id: 'note', label: 'Note', icon: 'fas fa-sticky-note', color: 'yellow', placeholder: 'Jotted down...' },
    { id: 'call', label: 'Call', icon: 'fas fa-phone', color: 'green', placeholder: 'Call summary...' },
    { id: 'email', label: 'Email', icon: 'fas fa-envelope', color: 'blue', placeholder: 'Email subject...' },
    { id: 'meeting', label: 'Meeting', icon: 'fas fa-handshake', color: 'purple', placeholder: 'Meeting agenda...' },
];

const activeTypeConfig = computed(() => activityTypes.find(t => t.id === activeType.value));

const form = useForm({
    subject: '',
    description: '',
    type: 'note',
    activityable_type: props.parentType,
    activityable_id: props.parentId,
    due_date: null // Not implementing for now
});

const logActivity = () => {
    form.type = activeType.value;
    form.activityable_type = props.parentType;
    form.activityable_id = props.parentId;
    
    form.post(route('crm.activities.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('subject', 'description');
        }
    });
};

const getTypeColor = (type) => {
    const colors = {
        note: 'bg-yellow-400',
        call: 'bg-green-500',
        email: 'bg-blue-500',
        meeting: 'bg-purple-500',
        task: 'bg-red-500'
    };
    return colors[type] || 'bg-gray-400';
};

const formatDate = (date) => {
    try {
        return formatDistanceToNow(new Date(date), { addSuffix: true });
    } catch (e) {
        return date;
    }
};
</script>
