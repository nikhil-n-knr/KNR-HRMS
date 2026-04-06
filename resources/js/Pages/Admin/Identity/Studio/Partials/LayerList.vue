<script setup>
import { computed } from 'vue';
import { useStudioStore } from '@/Stores/studioStore';

const store = useStudioStore();

const layers = computed(() => store.layers);
const activeObject = computed(() => store.activeObject);

const selectLayer = (obj) => {
    if (!store.canvas) return;
    store.canvas.setActiveObject(obj);
    store.canvas.renderAll();
};

const toggleVisibility = (obj) => {
    if (!store.canvas) return;
    obj.set('visible', !obj.visible);
    store.canvas.renderAll();
};

const toggleLock = (obj) => {
    if (!store.canvas) return;
    const isLocked = !obj.lockMovementX;
    obj.set({
        lockMovementX: isLocked,
        lockMovementY: isLocked,
        lockRotation: isLocked,
        lockScalingX: isLocked,
        lockScalingY: isLocked
    });
    store.canvas.renderAll();
};

const getIconType = (obj) => {
    if (obj.type === 'i-text' || obj.type === 'text') return 'fas fa-font';
    if (obj.type === 'image') return 'fas fa-image';
    if (obj.data_binding === 'qr_code') return 'fas fa-qrcode';
    if (obj.data_binding === 'photo_placeholder') return 'fas fa-user-circle';
    return 'fas fa-shapes';
};

// Reorder helpers — must select the object first before calling store methods
// because bringForward/sendBackward act on activeObject.value
const bringLayerForward = (obj) => {
    if (!store.canvas) return;
    store.canvas.setActiveObject(obj);
    store.bringForward();
};

const sendLayerBackward = (obj) => {
    if (!store.canvas) return;
    store.canvas.setActiveObject(obj);
    store.sendBackward();
};
</script>

<template>
    <div class="space-y-1 font-outfit select-none">
        <div 
            v-for="(obj, index) in layers" 
            :key="index"
            @click="selectLayer(obj)"
            class="group flex items-center justify-between p-2.5 rounded-xl cursor-pointer transition-all border border-transparent"
            :class="activeObject === obj ? 'bg-white shadow-xl shadow-slate-200/50 border-emerald-500/30' : 'hover:bg-slate-50'"
        >
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-all bg-slate-50 group-hover:bg-white" :class="activeObject === obj ? 'text-emerald-500 bg-white ring-1 ring-emerald-500/20 shadow-sm' : 'text-slate-400 group-hover:text-slate-600'">
                    <i :class="getIconType(obj)" class="text-sm"></i>
                </div>
                
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-black uppercase tracking-tight truncate w-32 transition-colors" :class="activeObject === obj ? 'text-slate-800' : 'text-slate-500 group-hover:text-slate-800'">
                        {{ (obj.type === 'i-text' ? obj.text : null) || obj.label || obj.data_binding || (obj.type.charAt(0).toUpperCase() + obj.type.slice(1)) }}
                    </span>
                    <span class="text-xs font-black text-slate-300 uppercase tracking-widest mt-0.5">
                        {{ obj.type.toUpperCase() }} NODE
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all transform translate-x-1 group-hover:translate-x-0">
                <button @click.stop="bringLayerForward(obj)" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" title="Bring Forward">
                    <i class="fas fa-angle-up text-sm"></i>
                </button>
                <button @click.stop="sendLayerBackward(obj)" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all" title="Send Backward">
                    <i class="fas fa-angle-down text-sm"></i>
                </button>
                <div class="w-px h-3 bg-slate-200 mx-0.5"></div>
                <button @click.stop="toggleLock(obj)" class="w-6 h-6 flex items-center justify-center rounded-lg transition-all" :class="obj.lockMovementX ? 'text-emerald-500 bg-emerald-50' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-200/50'" title="Lock Topology">
                    <i :class="obj.lockMovementX ? 'fas fa-lock' : 'fas fa-lock-open'" class="text-sm"></i>
                </button>
                
                <button @click.stop="toggleVisibility(obj)" class="w-6 h-6 flex items-center justify-center rounded-lg transition-all" :class="!obj.visible ? 'text-slate-200 bg-slate-50' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-200/50'" title="Toggle Visibility">
                    <i :class="obj.visible ? 'fas fa-eye' : 'fas fa-eye-slash'" class="text-sm"></i>
                </button>
            </div>
        </div>
        
        <div v-if="layers.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400">
            <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mb-4 text-slate-200">
                <i class="fas fa-layer-group text-xl opacity-20"></i>
            </div>
            <p class="text-sm font-black uppercase tracking-widest text-slate-300">Null Topology Stack</p>
        </div>
    </div>
</template>
