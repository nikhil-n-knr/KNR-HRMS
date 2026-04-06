<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Toolbox from './Partials/Toolbox.vue';
import CanvasArea from './Partials/CanvasArea.vue';
import PropertyPanel from './Partials/PropertyPanel.vue';
import LayerList from './Partials/LayerList.vue';
import Preview3D from './Partials/Preview3D.vue';
import ContextHUD from './Partials/ContextHUD.vue';
import { useStudioStore } from '@/Stores/studioStore';
import { watch } from 'vue';
import * as fabric from 'fabric';

defineOptions({ layout: MainLayout });

const props = defineProps({
    template: Object,
    existingTemplates: Array
});

const store = useStudioStore();
const activeTab = ref('properties'); // properties, layers
const leftActiveTab = ref('assets'); // assets, templates
const show3DModal = ref(false);
const isProcessing3D = ref(false);
const frontPreviewImage = ref('');
const backPreviewImage = ref('');
const isSaving = ref(false);
const showSaveSuccess = ref(false);

const captureSideSnapshot = async (side) => {
    if (!store.canvas) return '';
    
    const w = store.canvas.width;
    const h = store.canvas.height;
    
    // If the requested side is active, capture it directly
    if (store.activeSide === side) {
        return store.canvas.toDataURL({ format: 'png', multiplier: 2 });
    }
    
    // Otherwise, find its JSON state and render to a static canvas
    const state = side === 'front' ? store.frontState : store.backState;
    if (state) {
        let tempCanvas = new fabric.StaticCanvas(null, { width: w, height: h });
        await tempCanvas.loadFromJSON(state);
        const data = tempCanvas.toDataURL({ format: 'png', multiplier: 2 });
        tempCanvas.dispose();
        return data;
    }
    
    return '';
};

const open3DPreview = async () => {
    isProcessing3D.value = true;
    
    // Ensure current side is saved
    store.saveCurrentSide();
    
    frontPreviewImage.value = await captureSideSnapshot('front');
    backPreviewImage.value = await captureSideSnapshot('back');
    
    isProcessing3D.value = false;
    show3DModal.value = true;
};

const loadTemplate = async (tpl) => {
    if (confirm('Load "' + tpl.name + '" blueprint? This will replace your current canvas.')) {
        store.orientation = tpl.orientation || 'Landscape';
        await store.loadDesign(tpl.design_data);
        store.designName = 'Copy of ' + tpl.name;
        store.designType = tpl.type || 'Standard';
        store.isSaved = false;
        store.leftActiveTab = null; // Close the sidebar after loading
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const deleteTemplate = (tpl, e) => {
    e.stopPropagation();
    if (!confirm('Delete "' + tpl.name + '" template? This cannot be undone.')) return;
    router.delete(route('id-card.templates.destroy', tpl.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Template is removed; Inertia will refresh props
        },
        onError: (err) => {
            alert('Delete failed: ' + Object.values(err).join(', '));
        }
    });
};

const setAsDefault = (tpl, e) => {
    e.stopPropagation();
    router.post(route('id-card.templates.default', tpl.id), {}, {
        preserveScroll: true
    });
};

const getTplColor = (name) => {
    if (name.includes('Blue')) return 'from-blue-600 to-blue-800';
    if (name.includes('Crimson')) return 'from-red-600 to-red-900';
    if (name.includes('Standard')) return 'from-orange-500 to-amber-600';
    return 'from-slate-700 to-slate-900';
};

const clearCanvas = () => {
    if (confirm('Clear both front and back sides?')) {
        store.canvas.clear();
        store.frontState = null;
        store.backState = null;
        store.switchSide('front');
        store.designName = 'New Design';
    }
};

const exportPNG = () => {
    if (!store.canvas) return;
    const dataURL = store.canvas.toDataURL({
        format: 'png',
        quality: 1,
        multiplier: 4 
    });

    const link = document.createElement('a');
    link.download = `${store.designName || 'ID-Card'}-${store.activeSide}.png`;
    link.href = dataURL;
    link.click();
};

const saveDesign = async (asNew = false) => {
    if (!store.designName) {
        alert('Please specify a name for this Blueprint before committing.');
        return;
    }
    
    isSaving.value = true;
    
    // Ensure both sides are committed to states
    store.saveCurrentSide();
    
    const design = {
        front: store.frontState,
        back: store.backState,
        version: '2.0.0-PRO'
    };

    // Capture front side for the preview list
    const preview = await captureSideSnapshot('front');

    router.post(route('id-card.templates.store'), {
        id: asNew ? null : props.template?.id,
        name: store.designName,
        type: store.designType,
        design_data: design,
        orientation: store.orientation,
        dimensions: store.getDimensions(),
        preview_image: preview, // will be saved as snapshot on server
    }, {
        onFinish: () => isSaving.value = false,
        onSuccess: () => {
            store.isSaved = true;
            showSaveSuccess.value = true;
            setTimeout(() => showSaveSuccess.value = false, 3000);
        },
        onError: (err) => {
            console.error('Save failed:', err);
            alert('Error saving design: ' + Object.values(err).join(', '));
        }
    });
};

onMounted(() => {
    if (props.template) {
        store.designName = props.template.name || '';
        store.designType = props.template.type || 'Standard';
        
        if (props.template.design_data) {
            const unwatch = watch(() => store.isCanvasReady, (ready) => {
                if (ready) {
                    store.orientation = props.template.orientation || 'Landscape';
                    store.loadDesign(props.template.design_data);
                    unwatch();
                }
            }, { immediate: true });
        }
    }
});
</script>

<template>
    <div class="h-screen bg-white flex flex-col font-outfit overflow-hidden text-slate-800">
        <Head title="Identity Studio Pro" />

        <!-- High-Altitude Header -->
        <header class="h-16 bg-white/80 backdrop-blur-xl border-b border-slate-100 flex items-center justify-between px-8 z-50 shrink-0">
            <div class="flex items-center gap-6">
                <!-- Pro Emblem -->
                <div @click="router.visit(route('identity.index'))" class="flex items-center gap-3 group px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100 hover:border-emerald-200 transition-all cursor-pointer">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                        <i class="fas fa-crown text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-xs font-black uppercase tracking-tight text-slate-900 leading-none">Studio Pro</h1>
                        <p class="text-xs font-black text-emerald-600 uppercase tracking-widest mt-1">Light Fabrication Engine</p>
                    </div>
                </div>

                <!-- Strategic Inputs -->
                <div class="h-10 px-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center gap-4 hover:border-emerald-400/30 transition-all group shadow-sm focus-within:ring-2 focus-within:ring-emerald-500/10">
                    <div class="flex items-center gap-2 border-r border-slate-200 pr-4 h-5">
                        <i class="fas fa-pen-nib text-sm text-slate-400 group-hover:text-emerald-500"></i>
                        <input v-model="store.designName" placeholder="Untitled Matrix..." class="bg-transparent border-0 text-base font-black text-slate-700 outline-none w-48 uppercase tracking-tighter placeholder:text-slate-300">
                    </div>
                    <select v-model="store.designType" class="bg-transparent border-0 text-sm font-black text-slate-500 outline-none uppercase tracking-widest cursor-pointer hover:text-emerald-600 transition-colors">
                        <option value="Employee">Employee Cluster</option>
                        <option value="Vendor">Vendor Group</option>
                        <option value="Visitor">Visitor Cluster</option>
                        <option value="Standard">Standard Core</option>
                    </select>
                </div>
            </div>

            <!-- Global Action Array -->
            <div class="flex items-center gap-3">
                <button @click="open3DPreview" :disabled="isProcessing3D" class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-2 border border-slate-200 disabled:opacity-50">
                    <i :class="['fas', isProcessing3D ? 'fa-spinner fa-spin' : 'fa-cube']"></i> {{ isProcessing3D ? 'Rendering...' : 'Virtual Preview' }}
                </button>
                <div class="w-px h-6 bg-slate-200 mx-1"></div>
                <button @click="exportPNG" class="px-5 py-2.5 bg-white text-emerald-600 border border-emerald-100 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-file-export"></i> Render Snapshot
                </button>
                <button @click="saveDesign(false)" :disabled="isSaving" class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all disabled:opacity-50 flex items-center gap-2">
                    <i :class="['fas', isSaving ? 'fa-spinner fa-spin' : 'fa-cloud-upload-alt']"></i>
                    {{ isSaving ? 'Syncing...' : 'Commit' }}
                </button>
            </div>
        </header>

        <!-- Workplace Topology -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Asset Vector Assembly (Left Sidebar) -->
            <aside class="w-20 bg-white border-r border-slate-100 flex flex-col items-center py-6 gap-6 z-[60] relative shadow-[10px_0_30px_-15px_rgba(0,0,0,0.02)]">
                <button v-for="tab in ['assets', 'templates']" :key="tab"
                        @click="store.leftActiveTab = (store.leftActiveTab === tab ? null : tab)"
                        class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all group relative"
                        :class="store.leftActiveTab === tab ? 'bg-emerald-500 text-white shadow-xl shadow-emerald-500/20' : 'text-slate-300 hover:text-emerald-500 hover:bg-emerald-50'">
                    <i :class="['fas', tab === 'assets' ? 'fa-th-large' : 'fa-layer-group', 'text-sm relative z-10']"></i>
                    <div v-if="store.leftActiveTab === tab" class="absolute -right-1 w-1 h-4 bg-emerald-500 rounded-full"></div>
                </button>
            </aside>

            <!-- Fabrication Core Area -->
            <div class="flex-1 flex flex-col relative bg-slate-50 overflow-hidden">
                <!-- Sidebar Expansion Panel -->
                <transition name="slide-left">
                    <div v-if="store.leftActiveTab" class="absolute left-0 top-0 bottom-0 w-80 bg-white/95 backdrop-blur-2xl border-r border-slate-100 z-50 shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <h2 class="text-base font-black uppercase tracking-[0.3em] text-slate-800">
                                {{ store.leftActiveTab === 'assets' ? 'Blueprint Nodes' : 'Production Flux' }}
                            </h2>
                            <button @click="store.leftActiveTab = null" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-200 transition-colors">
                                <i class="fas fa-times text-sm text-slate-400"></i>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-5 custom-scrollbar">
                            <div v-if="store.leftActiveTab === 'templates'" class="grid grid-cols-1 gap-5 pb-10">
                                <div v-for="tpl in existingTemplates" :key="tpl.id" 
                                    @click="loadTemplate(tpl)"
                                    class="group relative overflow-hidden rounded-2xl border border-slate-100 hover:border-emerald-500 transition-all cursor-pointer bg-slate-50 hover:shadow-2xl hover:-translate-y-1 duration-500">
                                    <div class="aspect-[1.6] relative bg-slate-200 overflow-hidden">
                                        <!-- Show actual preview if saved, otherwise gradient fallback -->
                                        <img v-if="tpl.preview_url" :src="tpl.preview_url" class="absolute inset-0 w-full h-full object-cover" />
                                        <div v-else :class="['absolute inset-0 bg-gradient-to-br opacity-80', getTplColor(tpl.name)]"></div>
                                        
                                        <!-- Floating Action Array -->
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-3 backdrop-blur-[2px] z-10">
                                            <button @click="loadTemplate(tpl)" class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all shadow-xl" title="Load Blueprint">
                                                <i class="fas fa-folder-open"></i>
                                            </button>
                                            <button @click="setAsDefault(tpl, $event)" 
                                                    class="w-10 h-10 rounded-xl flex items-center justify-center transition-all shadow-xl"
                                                    :class="tpl.is_default ? 'bg-amber-500 text-white' : 'bg-white text-slate-400 hover:text-amber-500'"
                                                    title="Set as Default">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            <button @click="deleteTemplate(tpl, $event)" class="w-10 h-10 rounded-xl bg-white text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-xl" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>

                                        <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent z-0">
                                            <p class="text-sm font-black text-white uppercase tracking-tighter">{{ tpl.name }}</p>
                                            <p class="text-[10px] text-white/60 font-bold uppercase tracking-widest mt-0.5">{{ tpl.type }} · {{ tpl.orientation }}</p>
                                        </div>

                                        <!-- Default Indicator -->
                                        <div v-if="tpl.is_default" class="absolute top-3 left-3 px-2 py-0.5 rounded-lg bg-amber-500/90 backdrop-blur text-[8px] font-black text-white uppercase tracking-[0.2em] shadow-lg flex items-center gap-1.5 z-20">
                                            <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                                            Default
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="space-y-6">
                                <Toolbox />
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Telemetry HUD Bar (Canvas Toolbar) -->
                <div class="h-14 bg-white border-b border-slate-100 flex items-center justify-between px-6 z-40 shrink-0">
                    <div class="flex items-center gap-4">
                        <!-- Side Toggle -->
                        <div class="flex p-1 bg-slate-50 border border-slate-100 rounded-xl shadow-inner">
                            <button v-for="side in ['front', 'back']" :key="side"
                                    @click="store.switchSide(side)"
                                    class="px-4 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all"
                                    :class="store.activeSide === side ? 'bg-white text-emerald-600 shadow-md ring-1 ring-emerald-500/10' : 'text-slate-400 hover:text-slate-600'">
                                {{ side }} Axis
                            </button>
                        </div>

                        <div class="w-px h-6 bg-slate-200 mx-2"></div>

                        <!-- Orientation Switch -->
                        <button @click="store.toggleOrientation" class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-emerald-200 transition-all group" title="Toggle Orientation">
                            <i :class="['fas', store.orientation === 'Landscape' ? 'fa-id-card' : 'fa-portrait', 'text-sm text-slate-400 group-hover:text-emerald-500']"></i>
                        </button>

                        <!-- Grid Control -->
                        <button @click="store.snapToGrid = !store.snapToGrid" class="p-2.5 rounded-xl border transition-all" 
                                :class="store.snapToGrid ? 'bg-emerald-50 border-emerald-200 text-emerald-500 shadow-inner' : 'bg-slate-50 border-slate-100 text-slate-300 hover:text-slate-500'" title="Toggle Grid">
                            <i class="fas fa-th text-sm"></i>
                        </button>

                        <div class="w-px h-6 bg-slate-200 mx-2"></div>

                        <!-- Drawing Mode -->
                        <button @click="store.toggleDrawingMode(!store.isDrawingMode)" class="p-2.5 rounded-xl border transition-all" 
                                :class="store.isDrawingMode ? 'bg-indigo-50 border-indigo-200 text-indigo-500 shadow-inner' : 'bg-slate-50 border-slate-100 text-slate-300 hover:text-indigo-500'" title="Annotation Mode">
                            <i class="fas fa-pencil-alt text-sm"></i>
                        </button>
                    </div>

                    <!-- Right HUD Components -->
                    <div class="flex items-center gap-4">
                        <!-- History Array -->
                        <div class="flex items-center gap-1.5 p-1.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <button @click="store.undo" class="w-9 h-9 rounded-lg hover:bg-white text-slate-400 hover:text-emerald-500 transition-all flex items-center justify-center border border-transparent hover:border-slate-100" title="Undo Analysis">
                                <i class="fas fa-undo text-sm"></i>
                            </button>
                            <button @click="store.redo" class="w-9 h-9 rounded-lg hover:bg-white text-slate-400 hover:text-emerald-500 transition-all flex items-center justify-center border border-transparent hover:border-slate-100" title="Redo Pulse">
                                <i class="fas fa-redo text-sm"></i>
                            </button>
                        </div>

                        <div class="w-px h-6 bg-slate-200 mx-1"></div>

                        <!-- Global Action -->
                        <button @click="clearCanvas" class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-rose-200 hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-all group" title="Purge Workspace">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Strategic Fabrication Workspace -->
                <div class="flex-1 relative flex items-center justify-center p-12 overflow-auto custom-scrollbar perspective-1000">
                    <div class="relative transition-all duration-1000 transform active:scale-[0.98] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.15)] hover:shadow-[0_80px_150px_-30px_rgba(0,0,0,0.2)] rotate-x-6"
                         :style="{ zoom: store.zoom }">
                        <CanvasArea 
                            :width="store.orientation === 'Landscape' ? store.sizes[store.currentSize].width : store.sizes[store.currentSize].height"
                            :height="store.orientation === 'Landscape' ? store.sizes[store.currentSize].height : store.sizes[store.currentSize].width"
                        />
                        <ContextHUD />
                    </div>
                </div>

                <!-- Status Calibration Hub -->
                <footer class="h-10 bg-white border-t border-slate-100 flex items-center justify-between px-8 z-50 shrink-0">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-glow shadow-emerald-500/50"></span>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Operational Matrix Sync: Active</span>
                        </div>
                        <div class="flex items-center gap-2 border-l border-slate-100 pl-6 h-4">
                            <span class="text-xs font-black text-slate-300 uppercase tracking-widest leading-none">V5.1.0_CANVA_PRO_FLUX</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                            <i class="fas fa-shield-alt text-xs text-emerald-500"></i>
                            <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Pre-flight: Ready</span>
                        </div>
                    </div>
                </footer>
            </div>

            <!-- Dynamic Property Interface (Right Sidebar) -->
            <aside 
                v-if="store.rightSidebarVisible"
                class="w-80 flex-shrink-0 border-l border-slate-100 bg-white flex flex-col z-40 shadow-2xl relative transition-all duration-500 transform"
            >
                <div class="flex p-3 gap-2 bg-slate-50 relative">
                    <button v-for="t in ['properties', 'layers']" :key="t"
                            @click="activeTab = t" 
                            class="flex-1 py-2.5 text-sm font-black uppercase tracking-widest rounded-xl transition-all border"
                            :class="activeTab === t ? 'bg-emerald-500 text-white border-emerald-400 shadow-lg shadow-emerald-500/20' : 'text-slate-400 border-slate-100 hover:text-slate-600 hover:bg-white'">
                        {{ t === 'properties' ? 'Matrix' : 'Topology' }}
                    </button>
                    <!-- Close Property Panel -->
                    <button @click="store.rightSidebarVisible = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-400 transition-all">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50/30">
                    <div class="animate-fade-in">
                        <PropertyPanel v-if="activeTab === 'properties'" />
                        <LayerList v-else />
                    </div>
                </div>
            </aside>

            <!-- Floating Open Property Panel Button -->
            <button 
                v-else
                @click="store.rightSidebarVisible = true"
                class="absolute right-6 bottom-16 w-14 h-14 bg-white border border-slate-200 shadow-2xl rounded-2xl flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:scale-110 transition-all z-[100] group"
            >
                <i class="fas fa-sliders-h text-lg group-hover:rotate-180 transition-transform duration-700"></i>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white animate-pulse"></div>
            </button>

            <Preview3D 
                :isOpen="show3DModal" 
                :frontImage="frontPreviewImage" 
                :backImage="backPreviewImage" 
                :orientation="store.orientation"
                @close="show3DModal = false" 
            />
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.05); 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(16, 185, 129, 0.2); 
}

@keyframes scale-in {
    from { opacity: 0; transform: scale(0.95) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.animate-scale-in {
    animation: scale-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Slide Transitions */
.slide-left-enter-active,
.slide-left-leave-active {
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.slide-left-enter-from,
.slide-left-leave-to {
    opacity: 0;
    transform: translateX(-100%);
}

.perspective-1000 {
    perspective: 1500px;
}

.rotate-x-6 {
    transform: rotateX(6deg);
}
</style>
