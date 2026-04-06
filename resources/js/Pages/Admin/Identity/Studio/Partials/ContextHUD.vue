<script setup>
import { computed } from 'vue';
import { useStudioStore } from '@/Stores/studioStore';

const store = useStudioStore();

const activeObject = computed(() => store.activeObject);

const position = computed(() => {
    if (!activeObject.value || !store.canvas) return { top: 0, left: 0 };
    
    // Calculate position relative to canvas container
    const obj = activeObject.value;
    const canvasRect = store.canvas.getElement().getBoundingClientRect();
    
    // We want it slightly above the object
    const coords = obj.getCoords();
    const topMost = Math.min(...coords.map(c => c.y));
    const leftMost = Math.min(...coords.map(c => c.x));
    
    return {
        top: topMost - 50,
        left: leftMost + (obj.width * obj.scaleX / 2)
    };
});

const isLocked = computed(() => activeObject.value?.lockMovementX);

const toggleLock = () => {
    if (!activeObject.value) return;
    const locked = !activeObject.value.lockMovementX;
    activeObject.value.set({
        lockMovementX: locked,
        lockMovementY: locked,
        lockRotation: locked,
        lockScalingX: locked,
        lockScalingY: locked,
        hasControls: !locked
    });
    store.canvas.renderAll();
    store.pushHistory();
};

const duplicate = async () => {
    await store.copy();
    await store.paste();
};
</script>

<template>
    <div v-if="activeObject" 
         class="fixed z-[100] flex items-center gap-1 bg-white/80 backdrop-blur-xl border border-slate-200 p-1.5 rounded-2xl shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] transition-all duration-200"
         :style="{ 
             top: `${position.top}px`, 
             left: `${position.left}px`,
             transform: 'translateX(-50%)'
         }">
        
        <button @click="duplicate" :disabled="isLocked" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors disabled:opacity-30" title="Duplicate">
            <i class="fas fa-copy text-sm"></i>
        </button>
        
        <button @click="store.bringForward" :disabled="isLocked" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors disabled:opacity-30" title="Bring Forward">
            <i class="fas fa-angle-up text-sm"></i>
        </button>
        
        <button @click="store.sendBackward" :disabled="isLocked" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors disabled:opacity-30" title="Send Backward">
            <i class="fas fa-angle-down text-sm"></i>
        </button>

        <div class="w-px h-4 bg-slate-200 mx-1"></div>

        <button @click="toggleLock" class="w-8 h-8 flex items-center justify-center rounded-xl transition-colors" 
                :class="isLocked ? 'text-white bg-emerald-500 shadow-lg shadow-emerald-500/20' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-600'" title="Lock/Unlock">
            <i :class="['fas', isLocked ? 'fa-lock' : 'fa-lock-open', 'text-sm']"></i>
        </button>

        <button @click="store.deleteActive" :disabled="isLocked" class="w-8 h-8 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-50 transition-colors disabled:opacity-30" title="Delete">
            <i class="fas fa-trash-alt text-sm"></i>
        </button>
    </div>
</template>

<style scoped>
.fixed {
    pointer-events: auto;
}
</style>
