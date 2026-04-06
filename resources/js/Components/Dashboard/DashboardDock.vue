<template>
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <div class="flex items-center gap-2 p-2 bg-white/20 backdrop-blur-2xl border border-white/30 rounded-full shadow-2xl scale-110 hover:scale-125 transition-all duration-500 group">
            <div v-for="item in dockItems" :key="item.label" class="relative group/item">
                <button 
                    @click="handleAction(item)"
                    :class="[
                        'p-3 rounded-full transition-all duration-300 relative overflow-hidden',
                        item.active ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white/40 text-emerald-900 hover:bg-emerald-500 hover:text-white'
                    ]"
                    v-tooltip="item.label"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    <!-- Subtle notification dot -->
                    <span v-if="item.notif" class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                </button>
                
                <!-- Tooltip (Custom) -->
                <span class="absolute -top-12 left-1/2 -translate-x-1/2 px-2 py-1 bg-emerald-900 text-white text-[10px] rounded opacity-0 group-hover/item:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                    {{ item.label }}
                </span>
            </div>

            <!-- Separator -->
            <div class="w-px h-6 bg-white/30 mx-1"></div>

            <!-- Contextual Quick Action (Clock In/Out) -->
            <button 
                @click="toggleClock"
                :class="[
                    'flex items-center gap-2 px-5 py-3 rounded-full font-bold text-xs transition-all duration-300 shadow-lg',
                    isClockedIn ? 'bg-red-500 text-white hover:bg-red-600 shadow-red-200' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-200'
                ]"
            >
                <ClockIcon class="w-4 h-4" />
                <span>{{ isClockedIn ? 'Clock Out' : 'Clock In' }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { 
    HomeIcon, 
    CalendarIcon, 
    ClipboardDocumentCheckIcon, 
    CurrencyDollarIcon,
    ChatBubbleLeftRightIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';

const isClockedIn = ref(false);

const dockItems = ref([
    { label: 'Dashboard', icon: HomeIcon, active: true },
    { label: 'Leave Planner', icon: CalendarIcon, active: false },
    { label: 'Approvals', icon: ClipboardDocumentCheckIcon, active: false, notif: true },
    { label: 'Payroll', icon: CurrencyDollarIcon, active: false },
    { label: 'Messages', icon: ChatBubbleLeftRightIcon, active: false },
]);

const handleAction = (item) => {
    dockItems.value.forEach(i => i.active = false);
    item.active = true;
    console.log('Dock Action:', item.label);
};

const toggleClock = () => {
    isClockedIn.value = !isClockedIn.value;
    // Emit to parent or handle via API
};
</script>

<style scoped>
/* Reflection effect on dock */
.group::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 10%;
    right: 10%;
    height: 10px;
    background: linear-gradient(to bottom, rgba(255,255,255,0.2), transparent);
    filter: blur(5px);
    border-radius: 50%;
}
</style>
