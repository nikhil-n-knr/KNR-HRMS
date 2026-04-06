<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { StaticCanvas, FabricImage, Group } from 'fabric';

const props = defineProps({
    cards: Array,
    template: Object,
    templates: Array
});

// State
const renderedCanvases = ref([]);
const isProcessing = ref(true);

// A4 Utils
const A4_WIDTH_MM = 210;
const A4_HEIGHT_MM = 297;
const DPI = 300; // Physical print density
const MM_TO_PX = 3.7795; // Approx

onMounted(async () => {
    if (props.template && props.cards.length) {
        await renderCards();
        isProcessing.value = false;
    }
});

const renderCards = async () => {
    // Process cards in sequence to avoid browser freeze
    for (const [index, card] of props.cards.entries()) {
        const sides = ['front', 'back'];
        
        for (const side of sides) {
            if (!props.template.design_data || !props.template.design_data[side]) continue;
            
            const canvasId = `card-canvas-${index}-${side}`;
            
            // Wait for DOM
            await new Promise(resolve => setTimeout(resolve, 50));
            
            const fCanvas = new StaticCanvas(canvasId, {
                width: props.template.dimensions.width,
                height: props.template.dimensions.height,
                backgroundColor: '#ffffff'
            });

            try {
                const sideData = typeof props.template.design_data[side] === 'string' 
                    ? JSON.parse(props.template.design_data[side]) 
                    : props.template.design_data[side];
                    
                await fCanvas.loadFromJSON(sideData);
                
                const objects = fCanvas.getObjects();
                
                for (const obj of objects) {
                    const resolveValue = (binding, c) => {
                        if (!binding) return null;
                        const p = c.user || {};
                        const map = {
                            'user.name': p.name || c.details?.name,
                            'user.designation': c.details?.role || p.employee?.department || 'Staff',
                            'user.department': c.details?.department || p.employee?.department || 'N/A',
                            'user.employee_id': c.card_number || p.employee?.employee_code,
                            'user.blood_group': p.employee?.blood_group || c.details?.blood_group || 'N/A',
                            'user.joining_date': p.employee?.joining_date || 'N/A',
                            'user.email': p.email || p.employee?.email || 'N/A',
                            'user.phone': p.employee?.phone || 'N/A',
                            'user.dob': p.employee?.dob || 'N/A',
                            'user.emergency_name': p.employee?.emergency_contact_name || 'N/A',
                            'user.emergency_phone': p.employee?.emergency_contact_phone || 'N/A',
                            'user.address': p.employee?.address || 'N/A'
                        };
                        return map[binding] || null;
                    };

                    const processText = (textObj) => {
                        const boundVal = resolveValue(textObj.data_binding || textObj.get('data_binding'), card);
                        if (boundVal) {
                            textObj.set('text', boundVal);
                        } else {
                            let text = textObj.text || '';
                            text = text.replace(/\{\{\s*Name\s*\}\}/gi, card.user?.name || card.details?.name || '');
                            text = text.replace(/\{\{\s*Designation\s*\}\}/gi, card.user?.designation || card.details?.role || 'Employee');
                            text = text.replace(/\{\{\s*ID Number\s*\}\}/gi, card.user?.employee_id || card.card_number || '');
                            text = text.replace(/\{\{\s*Blood Group\s*\}\}/gi, card.user?.blood_group || card.details?.blood_group || 'N/A');
                            text = text.replace(/\{\{\s*Department\s*\}\}/gi, card.user?.department?.name || card.details?.department || '');
                            textObj.set('text', text);
                        }
                    };

                    if (obj.type === 'i-text' || obj.type === 'text') {
                        processText(obj);
                    } else if (obj.type === 'group') {
                        // Handle barcodes/signatures if they contain text
                        obj.getObjects().forEach(sub => {
                            if (sub.type === 'i-text' || sub.type === 'text') processText(sub);
                        });
                        
                        // Handle Barcode group specific data
                        if (obj.label === 'Barcode Node' || obj.get('data_binding') === 'user.employee_id') {
                             const employee_id = card.card_number || (card.user?.employee?.employee_code);
                             const txtNode = obj.getObjects().find(o => o.type === 'i-text' || o.type === 'text');
                             if (txtNode) txtNode.set('text', employee_id);
                        }
                    }

                    // 2. Data Bindings (Explicit tags)
                    const binding = obj.get('data_binding') || obj.data_binding;
                    if (binding === 'photo_placeholder') {
                        const photoUrl = card.user?.employee?.avatar 
                            ? `/storage/${card.user.employee.avatar}` 
                            : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(card.details.name) + '&background=random&size=300';
                        
                        try {
                            const img = await FabricImage.fromURL(photoUrl, { crossOrigin: 'anonymous' });
                            
                            // 1. Calculate the target dimensions based on the placeholder
                            const targetWidth = obj.width * (obj.scaleX || 1);
                            const targetHeight = obj.height * (obj.scaleY || 1);
                            
                            // 2. Calculate scale factors to COVER the target area
                            const scaleX = targetWidth / img.width;
                            const scaleY = targetHeight / img.height;
                            const scaleToCover = Math.max(scaleX, scaleY);
                            
                            img.set({
                                scaleX: scaleToCover,
                                scaleY: scaleToCover,
                                left: obj.left,
                                top: obj.top,
                                originX: 'center',
                                originY: 'center',
                                angle: obj.angle,
                            });
                            
                            // 3. Apply a crop/clip path to match the exact placeholder dimensions and radius
                            const clipPath = new fabric.Rect({
                                originX: 'center',
                                originY: 'center',
                                width: targetWidth / scaleToCover, // Inverse scale for the clipPath
                                height: targetHeight / scaleToCover,
                                rx: obj.rx ? (obj.rx / scaleToCover) : 0, 
                                ry: obj.ry ? (obj.ry / scaleToCover) : 0,
                            });
                            
                            img.set('clipPath', clipPath);

                            // If placeholder was centered, keep it centered
                            if (obj.originX === 'center') img.set('left', obj.left);
                            if (obj.originY === 'center') img.set('top', obj.top);

                            fCanvas.add(img);
                            fCanvas.remove(obj);
                        } catch (err) {
                            console.error('Failed to load employee photo:', err);
                        }
                    }

                    if (binding === 'qr_code') {
                         try {
                            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${card.qr_code}`;
                            const qrImg = await FabricImage.fromURL(qrUrl, { crossOrigin: 'anonymous' });
                            qrImg.set({
                                left: obj.left,
                                top: obj.top,
                                scaleX: (obj.width * (obj.scaleX || 1)) / qrImg.width,
                                scaleY: (obj.height * (obj.scaleY || 1)) / qrImg.height,
                                originX: obj.originX,
                                originY: obj.originY
                            });
                            fCanvas.add(qrImg);
                            fCanvas.remove(obj);
                         } catch (err) {
                            console.error('QR Render Error:', err);
                         }
                    }
                }

                fCanvas.renderAll();
                fCanvas.requestRenderAll();
            } catch (e) {
                console.error(`Error rendering side ${side} for card ${index}`, e);
            }
        }
    }
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head title="Structural Batch Print Output" />
    
    <div class="print:hidden h-20 bg-slate-900/95 backdrop-blur-md border-b border-white/10 flex items-center justify-between px-8 sticky top-0 z-50 font-outfit shadow-2xl">
        <div class="flex items-center gap-6">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-emerald-500/20">
                <i class="fas fa-print text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-white uppercase tracking-[0.1em] leading-none">Batch Production Engine</h1>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2 flex items-center gap-2">
                    <span class="text-emerald-400 px-2 py-0.5 bg-emerald-500/10 rounded-md ring-1 ring-emerald-500/20">{{ cards.length }} Physical Nodes</span>
                    <span class="text-slate-600">|</span>
                    <span>Stack: {{ template?.name || 'Standard Matrix' }}</span>
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div v-if="isProcessing" class="flex items-center gap-3 px-4 py-2 bg-white/5 rounded-xl border border-white/10 text-sm font-black text-emerald-400 uppercase tracking-widest animate-pulse">
                <i class="fas fa-circle-notch fa-spin"></i>
                Fabricating Matrix...
            </div>
            <div v-else class="flex items-center gap-3 px-4 py-2 bg-white/5 rounded-xl border border-white/10 text-sm font-black text-emerald-400 uppercase tracking-widest">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                Production Ready
            </div>
            <button @click="printPage" :disabled="isProcessing" class="bg-emerald-500 hover:bg-emerald-400 disabled:bg-slate-700 text-white px-10 py-3 rounded-2xl font-black text-base uppercase tracking-[0.2em] transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center gap-3">
                <i class="fas fa-rocket text-sm"></i>
                Execute Batch
            </button>
        </div>
    </div>

    <!-- Print Canvas Stage -->
    <div class="print-area bg-slate-50 min-h-screen p-12 flex flex-col items-center font-outfit">
        <!-- A4 Page Simulation -->
        <div class="a4-page bg-white shadow-[0_40px_100px_-20px_rgba(0,0,0,0.1)] mx-auto flex flex-col gap-12 p-16 content-start overflow-hidden border border-slate-200 rounded-[3rem] print:rounded-none print:border-none print:shadow-none transition-all">
            <div v-for="(card, i) in cards" :key="card.id" class="w-full flex flex-col gap-8 border-b-2 border-dashed border-slate-100 pb-12 last:border-b-0">
                <div class="flex justify-between items-center print:hidden border-l-4 border-l-emerald-500 pl-4 py-2 bg-slate-50 rounded-r-xl pr-6">
                    <div>
                        <h4 class="text-base font-black text-slate-800 uppercase tracking-tight">Personnel Object: {{ card.details.name || card.user.name }}</h4>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1">Matrix UUID: {{ card.qr_code }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-slate-300 uppercase tracking-[0.2em]">Batch Segment {{ i + 1 }}</span>
                        <div class="flex gap-1 mt-1 justify-end">
                            <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                            <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                            <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-12 justify-center">
                    <!-- Front Side Topology -->
                    <div class="flex flex-col items-center gap-4">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] print:hidden">Front Calibration</span>
                        <div class="card-wrapper shadow-2xl rounded-2xl overflow-hidden bg-white ring-1 ring-slate-100 relative"
                             :class="{ 'card-portrait': template.orientation === 'Portrait' }">
                            <div class="canvas-scaler">
                                <canvas :id="`card-canvas-${i}-front`"></canvas>
                            </div>
                        </div>
                    </div>
    
                    <!-- Back Side Topology -->
                    <div v-if="template.design_data && template.design_data.back" class="flex flex-col items-center gap-4">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] print:hidden">Back Calibration</span>
                        <div class="card-wrapper shadow-2xl rounded-2xl overflow-hidden bg-white ring-1 ring-slate-100 relative"
                             :class="{ 'card-portrait': template.orientation === 'Portrait' }">
                            <div class="canvas-scaler">
                                <canvas :id="`card-canvas-${i}-back`"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center print:hidden">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="w-8 h-px bg-slate-200"></div>
                <i class="fas fa-microchip text-slate-200"></i>
                <div class="w-8 h-px bg-slate-200"></div>
            </div>
            <p class="text-base font-black text-slate-300 uppercase tracking-[0.6em]">Structural Production Engine v4.2</p>
            <p class="text-xs font-black text-slate-200 uppercase tracking-widest mt-2">DPI CALIBRATION: {{ DPI }} | RENDERER: FABRIC_EMERALD</p>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .print\:hidden { display: none !important; }
    .print-area { background: white !important; padding: 0 !important; }
    .a4-page {
        box-shadow: none !important;
        margin: 0 !important;
        width: 210mm !important;
        min-height: 297mm !important;
        border: none !important;
        padding: 10mm !important;
    }
}

.a4-page {
    width: 210mm;
    min-height: 297mm;
    page-break-after: always;
}

.card-wrapper {
    /* CR80 Standard: 85.6mm x 54mm */
    width: 85.6mm;
    height: 54mm;
    background: white;
}

.card-portrait {
    width: 54mm;
    height: 85.6mm;
}

/* We need to scale the 1011px canvas to fit exactly into 85.6mm */
/* 85.6mm at 96dpi (CSS) = 323.5px */
/* Scale = 323.5 / 1011 = 0.32 approx */
.canvas-scaler {
    transform-origin: top left;
    transform: scale(0.32); /* Calibrated for CR80 standard */
}
</style>
