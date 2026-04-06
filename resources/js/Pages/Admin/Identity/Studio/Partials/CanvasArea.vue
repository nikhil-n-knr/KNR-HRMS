<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useStudioStore } from '@/Stores/studioStore';
import { Canvas, Point, Line, ActiveSelection } from 'fabric'; 

const props = defineProps({
    width: { type: Number, default: 1011 },
    height: { type: Number, default: 638 }
});

const canvasEl = ref(null);
const containerEl = ref(null);
const store = useStudioStore();
let fabricCanvas = null;

// --- Keyboard Logic ---
const handleKeyDown = (e) => {
    // Only handle if not typing in an input
    if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) return;

    const isCtrl = e.ctrlKey || e.metaKey;

    if (e.key === 'Delete' || e.key === 'Backspace') {
        store.deleteActive();
    } else if (isCtrl && e.key === 'z') {
        e.preventDefault();
        store.undo();
    } else if (isCtrl && e.key === 'y') {
        e.preventDefault();
        store.redo();
    } else if (isCtrl && e.key === 'c') {
        e.preventDefault();
        store.copy();
    } else if (isCtrl && e.key === 'v') {
        e.preventDefault();
        store.paste();
    } else if (isCtrl && e.key === 'a') {
        e.preventDefault();
        if (fabricCanvas) {
            fabricCanvas.discardActiveObject();
            const sel = new ActiveSelection(fabricCanvas.getObjects(), {
                canvas: fabricCanvas,
            });
            fabricCanvas.setActiveObject(sel);
            fabricCanvas.requestRenderAll();
        }
    }
};

onMounted(() => {
    if (!canvasEl.value) return;

    fabricCanvas = new Canvas(canvasEl.value, {
        width: props.width,
        height: props.height,
        backgroundColor: '#ffffff',
        preserveObjectStacking: true, 
        selection: true,
        controlsAboveOverlay: true,
    });

    // --- Wheel Zoom ---
    fabricCanvas.on('mouse:wheel', function(opt) {
        var delta = opt.e.deltaY;
        var zoom = fabricCanvas.getZoom();
        zoom *= 0.999 ** delta;
        if (zoom > 5) zoom = 5;
        if (zoom < 0.1) zoom = 0.1;
        
        // Zoom to mouse pointer
        fabricCanvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
        store.zoom = zoom;
        opt.e.preventDefault();
        opt.e.stopPropagation();
    });

    // --- Smart Guides Visuals ---
    let guideLines = [];
    
    const clearGuides = () => {
        guideLines.forEach(l => fabricCanvas.remove(l));
        guideLines = [];
    };

    fabricCanvas.on('object:moving', (e) => {
        if (!store.smartGuides) return;
        clearGuides();
        
        const obj = e.target;
        const margin = 12;
        const canvasW = fabricCanvas.width;
        const canvasH = fabricCanvas.height;
        const objects = fabricCanvas.getObjects().filter(o => o !== obj && o.visible && o.type !== 'line');

        // Center Guide
        if (Math.abs(obj.left - (canvasW / 2 - (obj.getScaledWidth() / 2))) < margin) {
            const line = new Line([canvasW/2, 0, canvasW/2, canvasH], {
                stroke: '#ff00ff', strokeWidth: 1, selectable: false, evented: false, strokeDashArray: [5, 5], opacity: 0.5
            });
            fabricCanvas.add(line);
            guideLines.push(line);
        }

        // Horizontal Center Guide
        if (Math.abs(obj.top - (canvasH / 2 - (obj.getScaledHeight() / 2))) < margin) {
            const line = new Line([0, canvasH/2, canvasW, canvasH/2], {
                stroke: '#ff00ff', strokeWidth: 1, selectable: false, evented: false, strokeDashArray: [5, 5], opacity: 0.5
            });
            fabricCanvas.add(line);
            guideLines.push(line);
        }

        // Object-to-Object guides (Simplified for edges)
        objects.forEach(target => {
            if (Math.abs(obj.left - target.left) < margin) {
                const line = new Line([target.left, 0, target.left, canvasH], {
                    stroke: '#ff00ff', strokeWidth: 0.5, selectable: false, evented: false, strokeDashArray: [2, 2], opacity: 0.3
                });
                fabricCanvas.add(line);
                guideLines.push(line);
            }
        });
    });

    fabricCanvas.on('object:moved', clearGuides);
    fabricCanvas.on('selection:cleared', clearGuides);

    store.initCanvas(fabricCanvas);
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    if (fabricCanvas) {
        fabricCanvas.dispose();
    }
    window.removeEventListener('keydown', handleKeyDown);
});

// Watch for size changes
watch(() => [props.width, props.height], ([w, h]) => {
    if (fabricCanvas) {
        fabricCanvas.setDimensions({ width: w, height: h });
        fabricCanvas.renderAll();
    }
});
</script>

<template>
    <div ref="containerEl" class="relative bg-white shadow-2xl cursor-crosshair group overflow-hidden">
        <!-- Visual Grid Overlay (Pure CSS for performance) -->
        <div 
            v-if="store.snapToGrid" 
            class="absolute inset-0 pointer-events-none opacity-20"
            :style="{
                backgroundImage: `radial-gradient(circle, #cbd5e1 1px, transparent 1px)`,
                backgroundSize: `${store.gridSize}px ${store.gridSize}px`
            }"
        ></div>

        <canvas ref="canvasEl"></canvas>

        <!-- Tooltip / Indicator -->
        <div class="absolute bottom-4 right-4 bg-slate-900/80 backdrop-blur-md px-3 py-1.5 rounded-full text-sm font-black text-white/80 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-3">
            <span class="flex items-center gap-1">
                <i class="fas fa-search"></i>
                {{ Math.round(store.zoom * 100) }}%
            </span>
            <span v-if="store.snapToGrid" class="flex items-center gap-1 text-emerald-400">
                <i class="fas fa-th"></i>
                GRID: {{ store.gridSize }}PX
            </span>
        </div>
    </div>
</template>
