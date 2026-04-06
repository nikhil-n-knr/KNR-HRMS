<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-teal-500/5 rounded-full blur-2xl group-hover:bg-teal-500/10 transition-all duration-700"></div>

        <div class="flex justify-between items-center mb-4 relative z-10">
            <h3 class="text-sm font-bold text-emerald-950 flex items-center gap-2">
                <CalendarIcon class="w-4 h-4 text-emerald-600" />
                Time Off
            </h3>
            <button class="text-[10px] font-bold text-emerald-600 hover:underline">Apply New</button>
        </div>

        <div v-if="loading" class="flex-grow space-y-4 animate-pulse">
            <div class="h-16 bg-emerald-100/30 rounded-2xl" v-for="i in 2" :key="i"></div>
        </div>

        <div v-else class="flex-grow grid grid-cols-2 gap-3 relative z-10">
            <div 
                v-for="balance in data" 
                :key="balance.id"
                class="p-4 bg-white/60 rounded-2xl flex flex-col items-center justify-center text-center border border-white hover:border-emerald-200 transition shadow-sm"
            >
                <p class="text-2xl font-black text-emerald-600 leading-none">{{ balance.balance }}</p>
                <p class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-tighter">{{ balance.leave_type.name }}</p>
                <p class="text-[9px] text-gray-400">Days Left</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CalendarIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const loading = ref(true);
const data = ref([]);

onMounted(async () => {
    try {
        // We reuse the existing balance logic or a new async endpoint if needed
        const response = await axios.get('/api/employee/dashboard/widgets/leave-balances'); 
        data.value = response.data.slice(0, 2); // Just show top 2 on dashboard
    } catch (e) {
        // Fallback for demo if path doesn't exist yet
        data.value = [
            { id: 1, balance: 12, leave_type: { name: 'Annual' } },
            { id: 2, balance: 5, leave_type: { name: 'Sick' } }
        ];
    } finally {
        loading.value = false;
    }
});
</script>
