<script setup>
import { computed } from 'vue';
import { useStudioStore } from '@/Stores/studioStore';
import { Image as FabricImage, filters, Shadow } from 'fabric'; 


const store = useStudioStore();

const activeObject = computed(() => store.activeObject);

const updateProp = (key, value) => {
    if (!activeObject.value) return;
    
    // Use batch update if multiple objects selected
    if (activeObject.value.type === 'activeSelection') {
        store.updateBatchProp(key, value);
    } else {
        activeObject.value.set(key, value);
        store.canvas.renderAll();
        store.pushHistory();
    }
};

// Image Filter Logic
const applyFilter = (type, value) => {
    if (!activeObject.value || activeObject.value.type !== 'image') return;
    
    const obj = activeObject.value;
    
    if (type === 'grayscale') {
        if (value) {
            obj.filters[0] = new filters.Grayscale();
        } else {
            obj.filters[0] = false; 
        }
    } else if (type === 'sepia') {
        if (value) {
            obj.filters[1] = new filters.Sepia();
        } else {
            obj.filters[1] = false;
        }
    }

    obj.applyFilters();
    store.canvas.renderAll();
    store.pushHistory();
};

// Data Binding Options
const dataBindings = [
    { label: 'None', value: null },
    { label: 'Employee Name', value: 'user.name' },
    { label: 'Designation', value: 'user.designation' },
    { label: 'Department', value: 'user.department' },
    { label: 'Employee ID', value: 'user.employee_id' },
    { label: 'Join Date', value: 'user.joining_date' },
    { label: 'Blood Group', value: 'user.blood_group' },
    { label: 'Email Address', value: 'user.email' },
    { label: 'Phone Number', value: 'user.phone' },
    { label: 'Date of Birth', value: 'user.dob' },
    { label: 'Emergency Contact', value: 'user.emergency_name' },
    { label: 'Emergency Phone', value: 'user.emergency_phone' },
    { label: 'Physical Address', value: 'user.address' },
    { label: 'QR Code', value: 'qr_code' },
    { label: 'Profile Photo', value: 'photo_placeholder' }
];

// Helper to safely get value from Fabric object
const getValue = (key) => {
    if (!activeObject.value) return null;
    
    if (activeObject.value.type === 'image') {
        if (key === 'grayscale') return !!activeObject.value.filters?.[0];
        if (key === 'sepia') return !!activeObject.value.filters?.[1];
    }

    if (key === 'shadow') return activeObject.value.shadow?.blur || 0;
    
    return activeObject.value.get(key);
};

const toggleCase = () => {
    if (!isText.value) return;
    const current = getValue('text');
    const isUpper = current === current.toUpperCase();
    updateProp('text', isUpper ? current.toLowerCase() : current.toUpperCase());
};

const updateShadow = (blur) => {
    if (!activeObject.value) return;
    const shadow = blur > 0 ? new Shadow({
        color: 'rgba(0,0,0,0.3)',
        blur: blur,
        offsetX: blur/2,
        offsetY: blur/2
    }) : null;
    updateProp('shadow', shadow);
};

const isText = computed(() => activeObject.value?.type === 'i-text' || activeObject.value?.type === 'text');
const isImage = computed(() => activeObject.value?.type === 'image');
const isRect = computed(() => activeObject.value?.type === 'rect');

const deleteActive = () => {
    store.deleteActive();
};

const alignObjects = (side) => {
    if (!store.canvas || !activeObject.value) return;
    const activeObj = activeObject.value;
    const canvas = store.canvas;
    
    if (side === 'left') activeObj.set('left', 0);
    if (side === 'right') activeObj.set('left', canvas.width - (activeObj.width * activeObj.scaleX));
    if (side === 'top') activeObj.set('top', 0);
    if (side === 'bottom') activeObj.set('top', canvas.height - (activeObj.height * activeObj.scaleY));
    
    activeObj.setCoords();
    canvas.renderAll();
    store.pushHistory();
};

// Advanced Feather Logic (Pro Methods)
const toggleCurvedText = (enabled) => {
    store.toggleCurvedText(enabled);
};

const applyFrameMask = (maskType) => {
    store.applyImageFrame(maskType);
};
</script>

<template>
    <div v-if="activeObject" class="space-y-4 select-none font-outfit animate-fade-in text-slate-800">
        <!-- Structural Header -->
        <div class="flex items-center justify-between bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 shadow-md shadow-emerald-500/20 flex items-center justify-center text-white">
                    <i :class="['fas', isText ? 'fa-font' : isImage ? 'fa-image' : 'fa-shapes', 'text-sm']"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none">
                        {{ getValue('label') || activeObject.type }}
                    </h3>
                    <p class="text-[6px] font-black text-slate-400 uppercase tracking-widest mt-1 flex items-center gap-1.5">
                        <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                        ID: {{ activeObject.id?.toString().slice(-6).toUpperCase() }}
                    </p>
                </div>
            </div>
            <button @click="deleteActive" class="w-7 h-7 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all border border-rose-100 flex items-center justify-center">
                <i class="fas fa-trash-alt text-sm"></i>
            </button>
        </div>

        <!-- 1. Coordinate Nexus -->
        <div class="bg-white p-4 rounded-2xl border border-slate-100 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Coordinates</h4>
                <div class="flex gap-1">
                    <button @click="store.bringToFront" class="w-6 h-6 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-indigo-500 transition-all flex items-center justify-center" title="Bring to Front"><i class="fas fa-angle-double-up text-sm"></i></button>
                    <button @click="store.bringForward" class="w-6 h-6 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-indigo-500 transition-all flex items-center justify-center" title="Bring Forward"><i class="fas fa-angle-up text-sm"></i></button>
                    <button @click="store.sendBackward" class="w-6 h-6 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-rose-500 transition-all flex items-center justify-center" title="Send Backward"><i class="fas fa-angle-down text-sm"></i></button>
                    <button @click="store.sendToBack" class="w-6 h-6 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-rose-500 transition-all flex items-center justify-center" title="Send to Back"><i class="fas fa-angle-double-down text-sm"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-2 py-1.5 border border-slate-100">
                    <span class="text-xs font-black text-slate-400">X</span>
                    <input type="number" :value="Math.round(getValue('left'))" @input="e => updateProp('left', Number(e.target.value))" class="w-full bg-transparent text-sm font-black text-slate-700 outline-none tabular-nums text-right">
                </div>
                <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-2 py-1.5 border border-slate-100">
                    <span class="text-xs font-black text-slate-400">Y</span>
                    <input type="number" :value="Math.round(getValue('top'))" @input="e => updateProp('top', Number(e.target.value))" class="w-full bg-transparent text-sm font-black text-slate-700 outline-none tabular-nums text-right">
                </div>
            </div>

            <!-- Alignment Matrix -->
            <div class="grid grid-cols-4 gap-1.5">
                <button v-for="side in ['left', 'centerH', 'centerV', 'right']" :key="side" 
                    @click="side.startsWith('center') ? (side === 'centerH' ? store.centerObjectH() : store.centerObjectV()) : alignObjects(side)" 
                    class="py-1.5 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-emerald-500 hover:border-emerald-500/30 transition-all flex items-center justify-center">
                    <i :class="`fas ${side === 'centerH' ? 'fa-arrows-alt-h' : side === 'centerV' ? 'fa-arrows-alt-v' : 'fa-align-' + side} text-sm`"></i>
                </button>
            </div>
        </div>

        <!-- 2. Visual Matrix -->
        <div class="bg-white p-4 rounded-2xl border border-slate-100 space-y-4 shadow-sm">
             <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Appearance</h4>
             
             <div class="grid grid-cols-2 gap-3">
                <div class="flex items-center justify-between bg-slate-50 p-1.5 rounded-xl border border-slate-100">
                    <span class="text-xs font-black text-slate-400 ml-1">FILL</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-black text-slate-500 uppercase">{{ getValue('fill') }}</span>
                        <input type="color" :value="getValue('fill')" @input="e => updateProp('fill', e.target.value)" class="w-5 h-5 border-0 p-0 cursor-pointer rounded-lg bg-transparent">
                    </div>
                </div>
                <div v-if="!isImage" class="flex items-center justify-between bg-slate-50 p-1.5 rounded-xl border border-slate-100">
                    <span class="text-xs font-black text-slate-400 ml-1">STRK</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-black text-slate-500 uppercase">{{ getValue('stroke') || '#000' }}</span>
                        <input type="color" :value="getValue('stroke')" @input="e => updateProp('stroke', e.target.value)" class="w-5 h-5 border-0 p-0 cursor-pointer rounded-lg bg-transparent">
                    </div>
                </div>
             </div>

             <div v-if="!isImage" class="grid grid-cols-2 gap-3">
                 <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-400">STRK W.</label>
                        <span class="text-xs font-black text-emerald-600 tabular-nums">{{ getValue('strokeWidth') }}</span>
                    </div>
                    <input type="range" min="0" max="20" :value="getValue('strokeWidth')" @input="e => updateProp('strokeWidth', Number(e.target.value))" class="w-full h-1 bg-slate-100 rounded-full appearance-none">
                 </div>
                 <div v-if="isRect" class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-400">RADIUS</label>
                        <span class="text-xs font-black text-emerald-600 tabular-nums">{{ getValue('rx') }}</span>
                    </div>
                    <input type="range" min="0" max="100" :value="getValue('rx')" @input="e => { updateProp('rx', Number(e.target.value)); updateProp('ry', Number(e.target.value)); }" class="w-full h-1 bg-slate-100 rounded-full appearance-none">
                 </div>
             </div>

             <div class="grid grid-cols-2 gap-3">
                 <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-400">OPACITY</label>
                        <span class="text-xs font-black text-emerald-600 tabular-nums">{{ Math.round(getValue('opacity') * 100) }}%</span>
                    </div>
                    <input type="range" min="0" max="1" step="0.1" :value="getValue('opacity')" @input="e => updateProp('opacity', Number(e.target.value))" class="w-full h-1 bg-slate-100 rounded-full appearance-none">
                 </div>
                 <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-400">SHADOW</label>
                        <span class="text-xs font-black text-emerald-600 tabular-nums">{{ getValue('shadow') || 0 }}</span>
                    </div>
                    <input type="range" min="0" max="50" :value="getValue('shadow') || 0" @input="e => updateShadow(Number(e.target.value))" class="w-full h-1 bg-slate-100 rounded-full appearance-none">
                 </div>
             </div>
        </div>

        <!-- 3. Type Calibration -->
        <div v-if="isText" class="bg-white p-4 rounded-2xl border border-slate-100 space-y-4 shadow-sm">
             <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Typography</h4>
             
             <!-- Font Family & Size -->
             <div class="flex gap-2">
                 <div class="relative flex-1 group">
                     <select :value="getValue('fontFamily')" @change="e => updateProp('fontFamily', e.target.value)" class="w-full h-8 bg-slate-50 border border-slate-100 rounded-xl text-sm font-black px-3 text-slate-700 outline-none focus:border-emerald-500/50 appearance-none relative z-10 cursor-pointer text-ellipsis">
                         <option value="Outfit">Outfit</option>
                         <option value="Inter">Inter</option>
                         <option value="Roboto">Roboto</option>
                         <option value="Playfair Display">Playfair</option>
                         <option value="Montserrat">Montserrat</option>
                         <option value="Lato">Lato</option>
                         <option value="Poppins">Poppins</option>
                         <option value="Oswald">Oswald</option>
                         <option value="Raleway">Raleway</option>
                         <option value="Nunito">Nunito</option>
                         <option value="Ubuntu">Ubuntu</option>
                         <option value="Merriweather">Merriweather</option>
                         <option value="Fira Code">Fira Code</option>
                     </select>
                     <i class="fas fa-chevron-down absolute right-3 top-2.5 text-xs text-slate-400 pointer-events-none group-hover:text-emerald-500 transition-colors"></i>
                 </div>
                 
                 <div class="flex items-center bg-slate-50 border border-slate-100 rounded-xl overflow-hidden h-8 overflow-hidden shrink-0">
                     <button @click="updateProp('fontSize', Math.max(8, getValue('fontSize') - 1))" class="w-7 h-full hover:bg-slate-200 text-slate-500 transition-colors flex items-center justify-center font-black">-</button>
                     <input type="number" :value="Math.round(getValue('fontSize'))" @input="e => updateProp('fontSize', Math.max(8, Number(e.target.value)))" class="w-9 h-full bg-transparent text-sm font-black text-center text-slate-700 outline-none tabular-nums appearance-none m-0 border-x border-slate-100 px-0">
                     <button @click="updateProp('fontSize', getValue('fontSize') + 1)" class="w-7 h-full hover:bg-slate-200 text-slate-500 transition-colors flex items-center justify-center font-black">+</button>
                 </div>
             </div>

             <!-- Styles & Alignment -->
             <div class="grid grid-cols-2 gap-3">
                 <div class="flex gap-1">
                     <button @click="updateProp('fontWeight', getValue('fontWeight') === 'bold' ? 'normal' : 'bold')" class="flex-1 py-1.5 rounded-lg border text-sm font-black transition-all" :class="getValue('fontWeight') === 'bold' ? 'bg-emerald-500 text-white border-emerald-400' : 'bg-slate-50 border-slate-100 text-slate-500 hover:bg-slate-100'">B</button>
                     <button @click="updateProp('fontStyle', getValue('fontStyle') === 'italic' ? 'normal' : 'italic')" class="flex-1 py-1.5 rounded-lg border text-sm italic font-black transition-all" :class="getValue('fontStyle') === 'italic' ? 'bg-emerald-500 text-white border-emerald-400' : 'bg-slate-50 border-slate-100 text-slate-500 hover:bg-slate-100'">I</button>
                     <button @click="updateProp('underline', !getValue('underline'))" class="flex-1 py-1.5 rounded-lg border text-sm underline font-black transition-all" :class="getValue('underline') ? 'bg-emerald-500 text-white border-emerald-400' : 'bg-slate-50 border-slate-100 text-slate-500 hover:bg-slate-100'">U</button>
                     <button @click="toggleCase" class="flex-1 py-1.5 rounded-lg border text-sm font-black transition-all bg-slate-50 border-slate-100 text-slate-500 hover:bg-slate-100" title="Case Toggle">aA</button>
                 </div>
                 <div class="flex gap-1 border border-slate-100 p-0.5 rounded-xl bg-slate-50">
                     <button v-for="align in ['left', 'center', 'right', 'justify']" :key="align" 
                         @click="updateProp('textAlign', align)" 
                         class="flex-1 py-1 rounded-lg text-sm transition-all flex items-center justify-center" 
                         :class="getValue('textAlign') === align ? 'bg-white shadow-sm text-emerald-600 font-bold border border-slate-100' : 'text-slate-400 hover:text-slate-600'">
                         <i :class="`fas fa-align-${align}`"></i>
                     </button>
                 </div>
             </div>

             <div class="grid grid-cols-2 gap-3">
                 <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-400">LETTER SP.</label>
                        <span class="text-xs font-black text-emerald-600 tabular-nums">{{ getValue('charSpacing') }}</span>
                    </div>
                    <input type="range" min="-100" max="1000" step="10" :value="getValue('charSpacing')" @input="e => updateProp('charSpacing', Number(e.target.value))" class="w-full h-1 bg-slate-100 rounded-full appearance-none">
                 </div>
                 <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-400">LINE HT.</label>
                        <span class="text-xs font-black text-emerald-600 tabular-nums">{{ Number(getValue('lineHeight') || 1.16).toFixed(1) }}</span>
                    </div>
                    <input type="range" min="0.5" max="3" step="0.1" :value="getValue('lineHeight') || 1.16" @input="e => updateProp('lineHeight', Number(e.target.value))" class="w-full h-1 bg-slate-100 rounded-full appearance-none">
                 </div>
             </div>

             <!-- Curved Text -->
             <div class="pt-2 border-t border-slate-50 flex items-center justify-between">
                 <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Curve Topology</label>
                 <button @click="toggleCurvedText(!getValue('curved'))" class="text-xs font-black px-2 py-1 rounded border transition-all" :class="getValue('curved') ? 'bg-emerald-500 text-white border-emerald-600' : 'text-emerald-600 bg-emerald-50 border-emerald-100 hover:bg-emerald-100'">{{ getValue('curved') ? 'Active' : 'Enable' }}</button>
             </div>
        </div>

        <!-- 4. Image Matrix / Frames (Feather Feature) -->
        <div v-if="isImage" class="bg-white p-5 rounded-3xl border border-slate-100 space-y-5 shadow-sm">
             <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em]">Image Matrix</h4>
             <div class="space-y-4">
                 <div class="flex items-center justify-between">
                     <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Structural Frames</label>
                 </div>
                 <div class="grid grid-cols-4 gap-2">
                     <button v-for="frame in ['circle', 'square', 'hexagon', 'badge']" :key="frame"
                             @click="applyFrameMask(frame)"
                             class="aspect-square rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center hover:border-emerald-400 transition-all text-slate-300 hover:text-emerald-500">
                         <i :class="['fas', frame === 'circle' ? 'fa-circle' : frame === 'square' ? 'fa-square' : frame === 'hexagon' ? 'fa-certificate' : 'fa-id-badge', 'text-sm']"></i>
                     </button>
                 </div>
                 
                 <div class="pt-4 border-t border-slate-50 space-y-4">
                     <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Cine-Filters</label>
                     <div class="flex gap-2">
                        <button @click="applyFilter('grayscale', !getValue('grayscale'))" class="flex-1 py-2 rounded-xl text-sm font-black uppercase tracking-widest transition-all" :class="getValue('grayscale') ? 'bg-slate-800 text-white' : 'bg-slate-50 text-slate-400 border border-slate-100'">B&W</button>
                        <button @click="applyFilter('sepia', !getValue('sepia'))" class="flex-1 py-2 rounded-xl text-sm font-black uppercase tracking-widest transition-all" :class="getValue('sepia') ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-50 text-slate-400 border border-slate-100'">Retro</button>
                     </div>
                 </div>
             </div>
        </div>

        <!-- 5. Logic Binding -->
        <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50 relative">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-database text-sm text-indigo-400"></i>
                <h4 class="text-xs font-black text-indigo-500 uppercase tracking-[0.2em]">Data Binding</h4>
            </div>
            <select :value="getValue('data_binding')" @change="e => updateProp('data_binding', e.target.value)" class="w-full bg-white border border-indigo-100 rounded-xl text-sm font-black px-3 py-2 text-slate-700 outline-none uppercase cursor-pointer">
                <option v-for="opt in dataBindings" :key="opt.label" :value="opt.value">{{ opt.label }}</option>
            </select>
        </div>
    </div>
    
    <!-- Global Assembly Panel -->
    <div v-else class="space-y-4 select-none font-outfit animate-fade-in text-slate-800">
        <div class="text-center py-6 relative">
            <h3 class="text-base font-black text-slate-900 uppercase tracking-tighter leading-none">Matrix Framework</h3>
        </div>

        <div class="space-y-4 relative z-10">
            <!-- Drawing Interface -->
            <div v-if="store.isDrawingMode" class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50 space-y-4 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 blur-[50px] pointer-events-none"></div>
                <div class="flex items-center gap-2 mb-2 relative z-10">
                    <i class="fas fa-magic text-sm text-indigo-400"></i>
                    <h4 class="text-xs font-black text-indigo-500 uppercase tracking-[0.2em]">Brush Telemetry</h4>
                </div>
                
                <div class="grid grid-cols-2 gap-3 relative z-10">
                    <div class="flex items-center justify-between bg-white p-1.5 rounded-xl border border-indigo-100/50">
                        <span class="text-xs font-black text-slate-400 ml-1">INK</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-black text-slate-500 uppercase">{{ store.brushColor }}</span>
                            <input type="color" v-model="store.brushColor" class="w-5 h-5 border-0 p-0 cursor-pointer rounded-lg bg-transparent">
                        </div>
                    </div>
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-black text-slate-400">WEIGHT</label>
                            <span class="text-xs font-black text-indigo-600 tabular-nums">{{ store.brushWidth }}</span>
                        </div>
                        <input type="range" min="1" max="50" v-model.number="store.brushWidth" class="w-full h-1 bg-white rounded-full appearance-none">
                    </div>
                </div>
            </div>

            <!-- Design Identity -->
            <div class="p-4 bg-white rounded-2xl border border-slate-100 space-y-4 shadow-sm">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Project Meta</h4>
                <div class="space-y-3">
                    <input v-model="store.designName" type="text" placeholder="Blueprint Label" class="w-full bg-slate-50 border border-slate-100 rounded-xl text-sm font-black px-3 py-2 text-slate-700 outline-none uppercase placeholder:text-slate-300">
                    <select v-model="store.designType" class="w-full bg-slate-50 border border-slate-100 rounded-xl text-sm font-black px-3 py-2 text-slate-700 outline-none uppercase cursor-pointer">
                        <option value="Employee">Employee Stack</option>
                        <option value="Vendor">Vendor Stack</option>
                        <option value="Visitor">Visitor Cluster</option>
                        <option value="Standard">Standard Matrix</option>
                    </select>
                </div>
            </div>

            <!-- Precise Alignment -->
            <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Grid Snap</h4>
                    <label class="relative inline-flex items-center cursor-pointer scale-75 transform origin-right">
                        <input type="checkbox" v-model="store.snapToGrid" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-100 rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full border border-slate-200"></div>
                    </label>
                </div>
                <div v-if="store.snapToGrid" class="flex items-center gap-3">
                    <span class="text-xs font-black text-emerald-600 w-6">{{ store.gridSize }}</span>
                    <input type="range" min="5" max="50" step="5" v-model.number="store.gridSize" class="flex-1 h-1 bg-slate-100 rounded-full appearance-none">
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 18px;
  width: 18px;
  border-radius: 50%;
  background: #10b981; 
  cursor: pointer;
  margin-top: -7px;
  box-shadow: 0 4px 10px rgba(16,185,129,0.3);
  border: 4px solid #fff;
}
input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 4px;
  cursor: pointer;
  background: #f1f5f9; 
  border-radius: 999px;
}
.animate-fade-in {
    animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
