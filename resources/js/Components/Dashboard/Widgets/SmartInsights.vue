<template>
    <div 
        v-if="activeInsight"
        class="relative overflow-hidden group mb-6"
    >
        <!-- Glassmorphic Background with Animated Gradient Border -->
        <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200 animate-pulse"></div>
        
        <div class="relative flex items-center justify-between p-4 bg-white/40 backdrop-blur-xl border border-white/40 rounded-2xl shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="p-2 bg-emerald-100/50 rounded-lg">
                    <SparklesIcon class="w-5 h-5 text-emerald-600 animate-bounce" />
                </div>
                <div>
                    <p class="text-sm font-medium text-emerald-900 leading-tight">
                        {{ activeInsight.text }}
                    </p>
                    <p class="text-xs text-emerald-700/60 mt-0.5">
                        Pulse Intelligence • Real-time Nudge
                    </p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <button 
                    @click="handleAction"
                    class="px-4 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-full hover:bg-emerald-700 transition shadow-lg shadow-emerald-200"
                >
                    {{ activeInsight.actionLabel }}
                </button>
                <button 
                    @click="closeInsight"
                    class="p-1 hover:bg-white/50 rounded-full transition"
                >
                    <XMarkIcon class="w-4 h-4 text-emerald-800/40" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { SparklesIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const activeInsight = ref(null);

// Mock insights generator (In prod, this would be computed from props/API)
const generateInsight = () => {
    const hours = new Date().getHours();
    const insights = [
        {
            text: "You have 3 days of expiring leave this quarter. Want to schedule a break?",
            actionLabel: "Plan Leave",
            route: '/leave'
        },
        {
            text: "Morning, Rocky! You're the top contributor this week. Keep that momentum!",
            actionLabel: "View Stats",
            route: '/performance'
        },
        {
            text: "You've clocked in consistently before 9 AM. Exceptional punctuality!",
            actionLabel: "View Logs",
            route: '/attendance'
        }
    ];

    if (hours < 12) {
        activeInsight.value = insights[1];
    } else {
        activeInsight.value = insights[0];
    }
};

const closeInsight = () => {
    activeInsight.value = null;
};

const handleAction = () => {
    // In actual implementation, we might use router.visit(activeInsight.value.route)
    console.log('Navigating to:', activeInsight.value.route);
};

onMounted(() => {
    // Artificial delay for "pop" effect
    setTimeout(generateInsight, 800);
});
</script>

<style scoped>
.animate-pulse {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.1;
    }
    50% {
        opacity: 0.3;
    }
}
</style>
