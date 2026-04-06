<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, onMounted, computed, watch } from 'vue';
import { Canvas, Textbox, Rect, Circle, FabricImage } from 'fabric';
import Modal from '@/Components/Modal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    template: Object,
    existingTemplates: Array
});

// State
const showSetupModal = ref(false);
const setupConfig = ref({
    width: 85.6,
    height: 53.98,
    unit: 'mm',
    dpi: 300
});
const canvas = ref(null);
const canvasRef = ref(null);
const activeObject = ref(null);
const activeTab = ref('elements'); // elements, layers
const zoomLevel = ref(1);

const form = useForm({
    id: props.template?.id || null,
    name: props.template?.name || 'New Design',
    type: props.template?.type || 'Standard',
    dimensions: props.template?.dimensions || { width: 1011, height: 638 }, // CR80 @ 300DPI
    elements: props.template?.elements || null,
    preview_image: null
});

// Canvas Setup
onMounted(() => {
    initCanvas();
    if (props.template?.elements) {
        loadCanvas(props.template.elements);
    }
});

const initCanvas = () => {
    canvas.value = new Canvas(canvasRef.value, {
        width: 600, // Display size (scaled down from print size)
        height: 600 * (638/1011),
        backgroundColor: '#ffffff',
        preserveObjectStacking: true
    });

    // Scale Logic: Visual Canvas vs Print Canvas
    // For MVP we just work on visual scale relative to container
    
    // Event Listeners
    canvas.value.on('selection:created', (e) => activeObject.value = e.selected[0]);
    canvas.value.on('selection:updated', (e) => activeObject.value = e.selected[0]);
    canvas.value.on('selection:cleared', () => activeObject.value = null);
};

const loadCanvas = async (json) => {
    await canvas.value.loadFromJSON(json);
    canvas.value.renderAll();
};

// --- Toolkit Actions ---

const addText = (text, options = {}) => {
    const t = new Textbox(text, {
        left: 50,
        top: 50,
        fontFamily: 'Arial',
        fontSize: 20,
        fill: '#000000',
        ...options
    });
    canvas.value.add(t);
    canvas.value.setActiveObject(t);
};

const addPlaceholder = (field) => {
    addText(`{{ ${field} }}`, {
        fill: '#0044cc',
        fontStyle: 'italic',
        fontWeight: 'bold'
    });
};

const addShape = (type) => {
    let shape;
    if (type === 'rect') {
        shape = new Rect({
            left: 100, top: 100, fill: '#cccccc', width: 100, height: 50
        });
    } else if (type === 'circle') {
        shape = new Circle({
            left: 100, top: 100, fill: '#cccccc', radius: 40
        });
    }
    if (shape) {
        canvas.value.add(shape);
        canvas.value.setActiveObject(shape);
    }
};

// Double Sided Logic
const activeSide = ref('front');
const designData = ref({
    front: null,
    back: null
});

const switchSide = async (side) => {
    // Save current side
    const json = canvas.value.toJSON(['id', 'selectable', 'data']); // include custom props
    designData.value[activeSide.value] = json;
    
    // Switch state
    activeSide.value = side;
    
    // Load new side or clear
    canvas.value.clear();
    canvas.value.backgroundColor = '#ffffff';
    
    if (designData.value[side]) {
        await canvas.value.loadFromJSON(designData.value[side]);
    } else if (side === 'back' && !designData.value.back) {
        // Optional: Pre-fill back with white
    }
    
    canvas.value.renderAll();
};

const addFrame = (type) => {
    // create a clip path
    let clipPath;
    if (type === 'circle') {
        clipPath = new Circle({ radius: 50, left: -50, top: -50 });
    } else {
        clipPath = new Rect({ width: 100, height: 100, left: -50, top: -50 });
    }

    // Create a placeholder rect that uses the clipPath
    // In Fabric, styling a "Frame" usually means a Group or an Image with clipPath.
    // For MVP: We create a gray rect with the clipPath, and when user drags image on it, we replace the rect content.
    
    const frame = new Rect({
        width: 100, height: 100, fill: '#eeeeee',
        clipPath: clipPath,
        objectCaching: false
    });
    
    // Tag it so we know it's a frame
    frame.set('data', { isFrame: true, frameType: type });
    
    canvas.value.add(frame);
    canvas.value.centerObject(frame);
    canvas.value.setActiveObject(frame);
};

const handleImageUpload = async (e) => {
    const reader = new FileReader();
    reader.onload = async (f) => {
        try {
            const img = await FabricImage.fromURL(f.target.result);
            
            // Check if active object is a frame
            if (activeObject.value && activeObject.value.data?.isFrame) {
                // Set image as the content, maintaining the clipPath
                // Actually easier to just Apply the clipPath to the new Image and replace the placeholder
                
                const frame = activeObject.value;
                img.set({
                    left: frame.left,
                    top: frame.top,
                    clipPath: frame.clipPath,
                    width: frame.width, // Resize image to fit frame width?
                    height: frame.height,
                    scaleX: frame.width / img.width,
                    scaleY: frame.height / img.height
                });
                
                canvas.value.remove(frame);
                canvas.value.add(img);
                canvas.value.setActiveObject(img);
                
            } else {
                img.scaleToWidth(100);
                canvas.value.add(img);
                canvas.value.setActiveObject(img);
            }
        } catch (err) {
            console.error('Error loading image', err);
        }
    };
    reader.readAsDataURL(e.target.files[0]);
};

// --- Properties Actions ---

const updateActiveProp = (prop, value) => {
    if (!activeObject.value) return;
    activeObject.value.set(prop, value);
    canvas.value.requestRenderAll();
};

const deleteActive = () => {
    if (!activeObject.value) return;
    canvas.value.remove(activeObject.value);
    activeObject.value = null;
    canvas.value.discardActiveObject();
    canvas.value.requestRenderAll();
};

const bringForward = () => {
    if (!activeObject.value) return;
    canvas.value.bringObjectForward(activeObject.value);
    canvas.value.renderAll();
};

const sendBackwards = () => {
    if (!activeObject.value) return;
    canvas.value.sendObjectBackwards(activeObject.value);
    canvas.value.renderAll();
};

// Dimensions
const updateDimensions = () => {
    if(!form.dimensions.width || !form.dimensions.height) return;
    // Resize visual canvas wrapper if needed, or just update data
    // For visual preview, we might want to reload the canvas size
    canvas.value.setDimensions({
        width: 600, 
        height: 600 * (form.dimensions.height / form.dimensions.width)
    });
    canvas.value.renderAll();
};

// --- Save & Export ---

// Setup Logic
const applyPreset = (w, h, u) => {
    setupConfig.value.width = w;
    setupConfig.value.height = h;
    setupConfig.value.unit = u;
};

const confirmSetup = () => {
    let pxW, pxH;
    
    if (setupConfig.value.unit === 'px') {
        pxW = setupConfig.value.width;
        pxH = setupConfig.value.height;
    } else if (setupConfig.value.unit === 'mm') {
        pxW = (setupConfig.value.width / 25.4) * setupConfig.value.dpi;
        pxH = (setupConfig.value.height / 25.4) * setupConfig.value.dpi;
    } else if (setupConfig.value.unit === 'in') {
        pxW = setupConfig.value.width * setupConfig.value.dpi;
        pxH = setupConfig.value.height * setupConfig.value.dpi;
    }

    form.dimensions = { width: Math.round(pxW), height: Math.round(pxH) };
    updateDimensions(); // Rescale canvas
    showSetupModal.value = false;
};

// Rules Logic
const addRule = () => {
    if (!activeObject.value) return;
    
    // Quick and dirty manual DOM read for MVP (Use v-model in real world state)
    // Refactoring to use refs would be cleaner but this works for the "Product Spec" implementation
    const field = document.getElementById('rule-field').value;
    const operator = document.getElementById('rule-operator').value;
    const value = document.getElementById('rule-value').value;
    const prop = document.getElementById('rule-prop').value;
    const result = document.getElementById('rule-result').value;

    if (!value) {
        alert('Please enter a value');
        return;
    }

    const rule = { field, operator, value, prop, result };
    
    // Ensure data object exists
    if (!activeObject.value.data) activeObject.value.set('data', {});
    
    // Ensure rules array exists
    let existingRules = activeObject.value.data.rules || [];
    existingRules.push(rule);
    
    activeObject.value.data.rules = existingRules; // Vue 3 proxy might not trigger deep update on fabric object
    activeObject.value.set('data', { ...activeObject.value.data, rules: existingRules }); // Force update
    
    // Trigger reactivity
    activeObject.value = activeObject.value; // Dumb trigger
    canvas.value.requestRenderAll();
};

const removeRule = (index) => {
    if (!activeObject.value || !activeObject.value.data?.rules) return;
    let newRules = [...activeObject.value.data.rules];
    newRules.splice(index, 1);
    activeObject.value.set('data', { ...activeObject.value.data, rules: newRules });
    canvas.value.requestRenderAll();
};

// --- Save & Export ---

const saveDesign = () => {
    // 1. Save current side to memory
    const currentJson = canvas.value.toJSON(['id', 'selectable', 'data']);
    designData.value[activeSide.value] = currentJson;

    // 2. Prepare Payload (We store object { front: ..., back: ... })
    // Existing DB expects JSON. We'll wrap it.
    form.elements = JSON.stringify(designData.value);
    
    // 3. Base64 Preview (Front side)
    // If we are on back, we might want to temporarily render front to get preview, but for now just capture current
    form.preview_image = canvas.value.toDataURL({
        format: 'png',
        quality: 0.8,
        multiplier: 0.5
    });

    form.post(route('id-card.templates.store'), {
        preserveScroll: true,
        onSuccess: () => alert('Design Saved!')
    });
};
</script>

<template>
    <Head title="ID Card Studio" />

    <div class="h-screen flex flex-col overflow-hidden bg-slate-100 font-outfit">
        <!-- Structural Intelligence Toolbar -->
        <div class="h-14 bg-white/90 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-6 z-20 flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-6">
                 <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-layer-group text-emerald-400 text-xs"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-black text-slate-800 uppercase tracking-tight leading-none">Fabrication Studio</h1>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">v2.0 Structural Designer</p>
                    </div>
                 </div>

                <!-- Side Calibration -->
                <div class="flex bg-slate-100 p-1 rounded-xl">
                    <button @click="switchSide('front')" class="px-4 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="activeSide === 'front' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-400 hover:text-slate-600'">Front</button>
                    <button @click="switchSide('back')" class="px-4 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="activeSide === 'back' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-400 hover:text-slate-600'">Back</button>
                </div>
                
                <div class="h-8 w-px bg-slate-200 mx-2"></div>

                <div class="flex flex-col">
                     <input v-model="form.name" type="text" class="text-base font-black border-none p-0 focus:ring-0 text-slate-800 uppercase tracking-tight bg-transparent" placeholder="Untitled Design">
                     <div class="flex gap-2 text-xs text-slate-400 font-bold uppercase tracking-widest items-center">
                         <input type="number" v-model="form.dimensions.width" @change="updateDimensions" class="w-8 p-0 border-none bg-transparent text-right text-xs font-black focus:ring-0" title="Width">
                         <span>x</span>
                         <input type="number" v-model="form.dimensions.height" @change="updateDimensions" class="w-8 p-0 border-none bg-transparent text-xs font-black focus:ring-0" title="Height">
                         <span class="ml-1 text-slate-300">PX •</span>
                         <span class="text-emerald-500">{{ activeObject ? activeObject.type : 'Standby' }}</span>
                     </div>
                </div>
            </div>
            <div class="flex gap-2">
                 <button @click="showSetupModal = true" class="px-4 py-2 hover:bg-slate-100 rounded-xl text-sm font-black text-slate-500 uppercase tracking-widest transition-colors">Matrix Setup</button>
                 <button class="px-4 py-2 hover:bg-slate-100 rounded-xl text-sm font-black text-slate-500 uppercase tracking-widest transition-colors">Export Logic</button>
                 <button @click="saveDesign" class="px-5 py-2 bg-slate-900 text-emerald-400 hover:bg-slate-800 rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-slate-200 transition-all flex items-center gap-2">
                     <i class="fas fa-save text-sm"></i>
                     Commit Design
                 </button>
            </div>
        </div>

        <!-- Workspace Topology -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- Tactical Toolkit -->
            <div class="w-72 bg-white border-r border-slate-200 flex flex-col shadow-xl z-10 font-outfit">
                <div class="flex border-b border-slate-100 p-2 gap-1 bg-slate-50/50">
                    <button @click="activeTab='elements'" class="flex-1 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="activeTab==='elements'?'bg-white shadow-sm text-emerald-600 border border-slate-200':'text-slate-400 hover:text-slate-600'">Nodes</button>
                    <button @click="activeTab='data'" class="flex-1 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="activeTab==='data'?'bg-white shadow-sm text-emerald-600 border border-slate-200':'text-slate-400 hover:text-slate-600'">Data</button>
                    <button @click="activeTab='logic'" class="flex-1 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="activeTab==='logic'?'bg-white shadow-sm text-emerald-600 border border-slate-200':'text-slate-400 hover:text-slate-600'">Logic</button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-5 space-y-6 custom-scrollbar">
                    
                    <div v-if="activeTab === 'elements'" class="animate-fade-in space-y-6">
                        <div>
                            <h4 class="text-xs uppercase font-black text-slate-400 tracking-[0.2em] mb-3">Core Topography</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="addText('Heading')" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl hover:border-emerald-500/30 hover:bg-white group transition-all">
                                    <div class="text-lg font-black text-slate-800 mb-1 group-hover:text-emerald-600">H1</div>
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Header</div>
                                </button>
                                <button @click="addText('Body Text', { fontSize: 14 })" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl hover:border-emerald-500/30 hover:bg-white group transition-all">
                                    <div class="text-lg font-bold text-slate-700 mb-1 group-hover:text-emerald-600">Aa</div>
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Metadata</div>
                                </button>
                            </div>
                        </div>

                         <div>
                            <h4 class="text-xs uppercase font-black text-slate-400 tracking-[0.2em] mb-3">Geometric Nodes</h4>
                             <div class="grid grid-cols-3 gap-2">
                                <button @click="addShape('rect')" class="p-3 bg-slate-50 border border-slate-100 rounded-xl hover:bg-white transition-all flex justify-center">
                                    <div class="w-6 h-4 bg-slate-300 rounded-sm"></div>
                                </button>
                                <button @click="addShape('circle')" class="p-3 bg-slate-50 border border-slate-100 rounded-xl hover:bg-white transition-all flex justify-center">
                                    <div class="w-5 h-5 rounded-full bg-slate-300"></div>
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-xs uppercase font-black text-slate-400 tracking-[0.2em] mb-3">Visual Hubs</h4>
                             <div class="grid grid-cols-2 gap-2">
                                <button @click="addFrame('circle')" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl hover:bg-white transition-all flex flex-col items-center group">
                                    <div class="w-10 h-10 rounded-full border border-dashed border-slate-300 bg-slate-100 flex items-center justify-center text-xs font-black text-slate-400 group-hover:border-emerald-400 transition-colors">PORTRAIT</div>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2">Elastic Hub</span>
                                </button>
                                <button @click="addFrame('rect')" class="p-4 bg-slate-50 border border-slate-100 rounded-2xl hover:bg-white transition-all flex flex-col items-center group">
                                    <div class="w-10 h-8 border border-dashed border-slate-300 bg-slate-100 flex items-center justify-center text-xs font-black text-slate-400 group-hover:border-emerald-400 transition-colors">PHOTO</div>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2">Fixed Hub</span>
                                </button>
                            </div>
                        </div>

                         <div class="pt-4 border-t border-slate-100">
                              <h4 class="text-xs uppercase font-black text-slate-400 tracking-[0.2em] mb-3">External Assets</h4>
                              <div class="relative group">
                                  <input type="file" @change="handleImageUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"/>
                                  <div class="w-full py-4 bg-slate-900 border border-slate-800 rounded-2xl text-sm font-black text-emerald-400 uppercase tracking-[0.2em] text-center group-hover:bg-slate-800 transition-all">
                                      Inject Media
                                  </div>
                              </div>
                         </div>
                    </div>

                    <div v-if="activeTab === 'data'" class="animate-fade-in space-y-4">
                         <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-sm font-black text-emerald-700 uppercase tracking-widest leading-relaxed">
                             <i class="fas fa-info-circle mr-2"></i>
                             Inject dynamic parameters via structural placeholders.
                         </div>
                         <div class="grid grid-cols-1 gap-2">
                             <button v-for="field in ['name', 'role', 'department', 'id_number', 'qr_code']" :key="field" @click="addPlaceholder(field)" class="w-full text-left px-4 py-3 bg-slate-50/50 hover:bg-white rounded-xl border border-slate-100 hover:border-emerald-500/20 text-sm font-black text-slate-600 uppercase tracking-[0.2em] transition-all group flex justify-between items-center">
                                 {{ field }}
                                 <i class="fas fa-plus text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                             </button>
                         </div>
                    </div>

                    <div v-if="activeTab === 'logic'" class="animate-fade-in space-y-4">
                        <div v-if="activeObject">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-bolt text-emerald-500 text-sm"></i>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Active Node Protocls</h4>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="p-4 bg-slate-900 rounded-2xl space-y-3 shadow-lg">
                                    <div class="flex gap-2 items-center">
                                        <span class="text-sm font-black text-emerald-400 uppercase tracking-widest w-6">IF</span>
                                        <select id="rule-field" class="text-sm bg-slate-800 text-white border-none rounded-lg p-2 w-full font-black uppercase tracking-widest focus:ring-emerald-500">
                                            <option value="department">Department</option>
                                            <option value="role">Role</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-6"></div>
                                        <div class="flex gap-1 w-full">
                                            <select id="rule-operator" class="text-sm bg-slate-800 text-white border-none rounded-lg p-2 w-24 font-black uppercase tracking-widest focus:ring-emerald-500">
                                                <option value="=">=</option>
                                                <option value="contains">In</option>
                                            </select>
                                            <input id="rule-value" type="text" placeholder="Value..." class="text-sm bg-slate-800 text-white border-none rounded-lg p-2 w-full font-black uppercase tracking-widest focus:ring-emerald-500 placeholder:text-slate-600">
                                        </div>
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <span class="text-sm font-black text-emerald-400 uppercase tracking-widest w-6">DO</span>
                                        <div class="flex gap-1 w-full">
                                            <select id="rule-prop" class="text-sm bg-slate-800 text-white border-none rounded-lg p-2 w-full font-black uppercase tracking-widest focus:ring-emerald-500">
                                                <option value="fill">Fill Matrix</option>
                                                <option value="opacity">Density</option>
                                            </select>
                                            <input id="rule-result" type="color" class="h-8 w-12 cursor-pointer bg-slate-800 border-none rounded-lg p-1">
                                        </div>
                                    </div>
                                    <button @click="addRule" class="w-full py-2 bg-emerald-500 text-white text-sm font-black uppercase tracking-[0.2em] rounded-xl hover:bg-emerald-400 transition-all shadow-lg shadow-emerald-500/20">Initialize Protocol</button>
                                </div>

                                <!-- Active Protocols -->
                                <div v-if="activeObject.data?.rules && activeObject.data.rules.length" class="space-y-2">
                                    <div v-for="(rule, i) in activeObject.data.rules" :key="i" class="text-xs px-3 py-2 bg-white border border-slate-100 rounded-xl flex justify-between items-center group">
                                        <span class="font-black text-slate-500 uppercase tracking-widest leading-none">
                                            <span class="text-emerald-500 mr-1">IF</span> {{ rule.field }} <span class="text-slate-300 mx-1">-></span> "{{ rule.value }}"
                                        </span>
                                        <button @click="removeRule(i)" class="text-rose-400 hover:text-rose-600 transition-colors"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12 border-2 border-dashed border-slate-100 rounded-2xl">
                            <i class="fas fa-microchip text-slate-200 text-xl mb-2"></i>
                            <p class="text-sm font-black text-slate-300 uppercase tracking-widest px-8">Select node for structural logic</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Strategic Fabrication Area -->
            <div class="flex-1 bg-slate-50 flex items-center justify-center p-12 overflow-auto custom-scrollbar">
                 <div class="shadow-[0_40px_100px_-20px_rgba(0,0,0,0.1)] bg-white p-1 rounded-[1.5rem] border border-white/50 ring-1 ring-slate-200">
                      <div class="rounded-2xl overflow-hidden">
                          <canvas ref="canvasRef"></canvas>
                      </div>
                 </div>
            </div>

            <!-- Dynamic Parameters Interface -->
            <div class="w-64 bg-white border-l border-slate-200 p-5 font-outfit z-10" v-if="activeObject">
                <div class="flex items-center gap-2 mb-6">
                    <i class="fas fa-sliders-h text-emerald-500 text-sm"></i>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Node Properties</h4>
                </div>
                
                <div class="space-y-6">
                    <!-- Layer Matrix -->
                    <div>
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block">Stack Sequence</label>
                        <div class="flex gap-2">
                            <button @click="bringForward" class="flex-1 py-2.5 bg-slate-50 hover:bg-white border border-slate-100 hover:border-emerald-500/20 rounded-xl text-sm font-black uppercase tracking-widest text-slate-600 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-chevron-up text-xs"></i> Elevate
                            </button>
                            <button @click="sendBackwards" class="flex-1 py-2.5 bg-slate-50 hover:bg-white border border-slate-100 hover:border-emerald-500/20 rounded-xl text-sm font-black uppercase tracking-widest text-slate-600 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-chevron-down text-xs"></i> Submerge
                            </button>
                        </div>
                    </div>

                    <!-- Chromaticity -->
                    <div v-if="activeObject.fill">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block">Chromatic Value</label>
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                             <input type="color" :value="activeObject.fill" @input="e => updateActiveProp('fill', e.target.value)" class="h-8 w-12 rounded-lg cursor-pointer border-none p-1 shadow-sm">
                             <span class="text-sm font-black text-slate-500 uppercase tabular-nums tracking-widest">{{ activeObject.fill }}</span>
                        </div>
                    </div>

                    <!-- Density Matrix -->
                    <div>
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block">Opacity Density</label>
                        <div class="px-2">
                            <input type="range" min="0" max="1" step="0.1" :value="activeObject.opacity" @input="e => updateActiveProp('opacity', parseFloat(e.target.value))" class="w-full accent-emerald-500">
                        </div>
                    </div>

                    <!-- Typographic Scale -->
                    <div v-if="activeObject.type === 'textbox' || activeObject.type === 'text'" class="pt-6 border-t border-slate-100">
                         <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block">Typography Engine</label>
                         <div class="flex gap-2 mb-3">
                             <input type="number" :value="activeObject.fontSize" @input="e => updateActiveProp('fontSize', parseInt(e.target.value))" class="block w-20 rounded-xl bg-slate-50 border-slate-200 text-sm font-black focus:ring-emerald-500" placeholder="Size">
                             <div class="flex-1 h-9 bg-slate-50 rounded-xl border border-slate-200 px-2 flex items-center gap-2">
                                 <input type="color" :value="activeObject.fill" @input="e => updateActiveProp('fill', e.target.value)" class="h-6 w-full rounded-lg cursor-pointer border-none p-0">
                             </div>
                         </div>
                         
                         <div class="flex gap-1 mb-4">
                             <button @click="updateActiveProp('fontWeight', activeObject.fontWeight === 'bold' ? 'normal' : 'bold')" class="flex-1 py-2 rounded-lg border text-sm font-black transition-all" :class="activeObject.fontWeight === 'bold' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-100 text-slate-400'">B</button>
                             <button @click="updateActiveProp('fontStyle', activeObject.fontStyle === 'italic' ? 'normal' : 'italic')" class="flex-1 py-2 rounded-lg border text-sm font-black italic transition-all" :class="activeObject.fontStyle === 'italic' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-100 text-slate-400'">I</button>
                             <button @click="updateActiveProp('underline', !activeObject.underline)" class="flex-1 py-2 rounded-lg border text-sm font-black underline transition-all" :class="activeObject.underline ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-100 text-slate-400'">U</button>
                         </div>

                         <div class="flex gap-1">
                             <button @click="updateActiveProp('textAlign', 'left')" class="flex-1 py-2 rounded-lg border text-xs font-black transition-all" :class="activeObject.textAlign === 'left' ? 'bg-slate-900 text-emerald-400 border-slate-800' : 'bg-slate-50 border-slate-100 text-slate-300'"><i class="fas fa-align-left"></i></button>
                             <button @click="updateActiveProp('textAlign', 'center')" class="flex-1 py-2 rounded-lg border text-xs font-black transition-all" :class="activeObject.textAlign === 'center' ? 'bg-slate-900 text-emerald-400 border-slate-800' : 'bg-slate-50 border-slate-100 text-slate-300'"><i class="fas fa-align-center"></i></button>
                             <button @click="updateActiveProp('textAlign', 'right')" class="flex-1 py-2 rounded-lg border text-xs font-black transition-all" :class="activeObject.textAlign === 'right' ? 'bg-slate-900 text-emerald-400 border-slate-800' : 'bg-slate-50 border-slate-100 text-slate-300'"><i class="fas fa-align-right"></i></button>
                         </div>
                    </div>

                    <div class="pt-8 mt-12">
                        <button @click="deleteActive" class="w-full py-3 bg-rose-50 text-rose-600 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm border border-rose-100">Purge Node Data</button>
                    </div>
                </div>
            </div>
            <div class="w-64 bg-white border-l border-slate-200 p-8 flex flex-col items-center justify-center text-center font-outfit" v-else>
                 <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 mb-4">
                     <i class="fas fa-mouse-pointer text-xl"></i>
                 </div>
                 <span class="text-slate-300 text-sm font-black uppercase tracking-widest px-4 leading-relaxed">Select structural node to modify parameters.</span>
            </div>

            <!-- Enhanced Matrix Configuration Modal -->
            <Modal :show="showSetupModal" title="Structural Configuration" @close="showSetupModal = false" :maxWidth="'lg'">
                <div class="p-8 font-outfit">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl shadow-slate-200">
                            <i class="fas fa-vector-square text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Intelligence Matrix Setup</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Define canvas topology & resolution</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Metric Base</label>
                            <select v-model="setupConfig.unit" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-black uppercase tracking-widest focus:ring-emerald-500 py-3">
                                <option value="mm">Millimeters (mm)</option>
                                <option value="px">Pixels (px)</option>
                                <option value="in">Inches (in)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Structural DPI</label>
                            <select v-model="setupConfig.dpi" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-black uppercase tracking-widest focus:ring-emerald-500 py-3">
                                <option value="72">72 (Screen Phase)</option>
                                <option value="96">96 (Web Interface)</option>
                                <option value="300">300 (Absolute Print)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">X-Axis Width</label>
                            <input type="number" v-model="setupConfig.width" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-black p-3 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Y-Axis Height</label>
                            <input type="number" v-model="setupConfig.height" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-black p-3 focus:ring-emerald-500">
                        </div>
                    </div>
                    
                    <div class="bg-slate-900 p-4 rounded-2xl mb-8 flex justify-between items-center shadow-lg">
                        <span class="text-sm font-black text-emerald-400 uppercase tracking-widest">Topology Presets:</span>
                        <div class="flex gap-2">
                             <button @click="applyPreset(85.6, 53.98, 'mm')" class="text-xs font-black uppercase tracking-widest bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg transition-all border border-white/10">CR80 (Standard ID)</button>
                             <button @click="applyPreset(105, 148, 'mm')" class="text-xs font-black uppercase tracking-widest bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg transition-all border border-white/10">A6 (Tactical Badge)</button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <button @click="showSetupModal = false" class="px-6 py-2.5 text-sm font-black uppercase tracking-widest text-slate-400">Abort</button>
                        <button @click="confirmSetup" class="px-8 py-2.5 bg-slate-900 text-emerald-400 font-black uppercase tracking-widest rounded-xl shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all">Initialize Matrix</button>
                    </div>
                </div>
            </Modal>
        </div>
    </div>
</template>
