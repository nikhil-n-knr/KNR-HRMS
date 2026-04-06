<template>
    <div class="px-8 py-4 bg-white/60 backdrop-blur-xl border-b border-emerald-100/50 flex items-center justify-between sticky top-0 z-20 shadow-sm animate-fade-in">
        <div class="flex items-center gap-4">
            <div class="px-3 py-1 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center gap-2">
                <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[9px] font-black text-emerald-700 uppercase tracking-widest leading-none">Command Strip Active</span>
            </div>
            <div class="h-4 w-px bg-emerald-100"></div>
            <!-- Role Indicator (Optional but helpful for segmenting) -->
            <p v-if="role" class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] italic">{{ role }} Action Matrix</p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                v-for="action in actions" 
                :key="action.label"
                @click="$emit('action', action.id)"
                class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all transform active:scale-95 flex items-center gap-2 shadow-sm"
                :class="action.primary ? 'bg-emerald-600 text-white hover:bg-emerald-700 hover:shadow-emerald-200' : 'bg-white text-gray-600 border border-gray-100 hover:bg-gray-50'"
            >
                <i :class="action.icon"></i>
                {{ action.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    role: { type: String, default: 'State Admin' },
    actions: {
        type: Array,
        default: () => [
            { id: 'create-course', label: 'Create Course', icon: 'fas fa-plus', primary: true },
            { id: 'create-program', label: 'Create Program', icon: 'fas fa-layer-group', primary: false },
            { id: 'mass-enroll', label: 'Batch Enrollment', icon: 'fas fa-users-cog', primary: false },
            { id: 'certificates', label: 'Certificates Wallet', icon: 'fas fa-wallet', primary: false },
        ]
    }
});

defineEmits(['action']);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>
