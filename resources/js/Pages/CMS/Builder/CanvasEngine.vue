<template>
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-gray-100">

        <!-- Canvas Top Bar -->
        <div class="h-12 bg-white border-b border-gray-200 flex items-center justify-between px-4 shrink-0 shadow-sm z-10">
            <!-- Left: Tray + Undo/Redo -->
            <div class="flex items-center gap-1">
                <button @click="$emit('toggle-tray')"
                    class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 hover:bg-emerald-100 transition-colors border border-emerald-200"
                    title="Add Block (A)">
                    <i class="fas fa-plus text-xs"></i>
                </button>
                <div class="h-4 border-r border-gray-200 mx-1"></div>
                <button @click="undo" :disabled="historyIndex <= 0"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition-colors border"
                    :class="historyIndex > 0 ? 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'"
                    title="Undo (Ctrl+Z)">
                    <i class="fas fa-undo"></i>
                </button>
                <button @click="redo" :disabled="historyIndex >= history.length - 1"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition-colors border"
                    :class="historyIndex < history.length - 1 ? 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'"
                    title="Redo (Ctrl+Y)">
                    <i class="fas fa-redo"></i>
                </button>
                <div class="h-4 border-r border-gray-200 mx-1"></div>
                <!-- Device Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-0.5 border border-gray-200">
                    <button v-for="d in devices" :key="d.id" @click="activeDevice = d.id"
                        class="w-8 h-7 flex items-center justify-center rounded-md text-base transition-all"
                        :class="activeDevice === d.id ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400 hover:text-gray-700'"
                        :title="d.label">
                        <i :class="d.icon"></i>
                    </button>
                </div>
            </div>

            <!-- Center: Page Name + Save indicator -->
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></div>
                    <span class="text-sm font-black tracking-widest uppercase text-gray-600">
                        {{ activePage?.title || 'Home Page' }}
                    </span>
                    <span v-if="isDirty" class="text-sm font-bold text-amber-500 ml-1">• Unsaved</span>
                </div>
                <!-- Zoom -->
                <div class="flex items-center gap-1 text-sm font-bold text-gray-500">
                    <button @click="zoom = Math.max(0.5, zoom - 0.1)" class="w-5 h-5 rounded hover:bg-gray-100 flex items-center justify-center">−</button>
                    <span class="w-12 text-center">{{ Math.round(zoom * 100) }}%</span>
                    <button @click="zoom = Math.min(2, zoom + 0.1)" class="w-5 h-5 rounded hover:bg-gray-100 flex items-center justify-center">+</button>
                </div>
            </div>

            <!-- Right: Preview + Save + Publish -->
            <div class="flex items-center gap-2">
                <button @click="window.open('' + (activePage?.slug === '/' ? '/' : '/' + (activePage?.slug || '')) + '?preview_site=' + siteSlug, '_blank')"
                    class="px-3 py-1.5 rounded-lg text-sm font-bold text-gray-500 hover:bg-gray-100 transition-colors border border-gray-200 flex items-center gap-1.5">
                    <i class="fas fa-eye text-emerald-500"></i> Preview
                </button>
                <button @click="$emit('save')"
                    class="px-3 py-1.5 rounded-lg text-sm font-black uppercase tracking-wider bg-white border border-gray-200 text-gray-700 hover:border-indigo-400 transition-all flex items-center gap-1.5">
                    <i class="fas fa-save text-indigo-500"></i> Save
                </button>
                <button @click="$emit('publish')" class="px-3 py-1.5 rounded-lg text-sm font-black uppercase tracking-wider bg-emerald-500 text-white hover:bg-emerald-600 border border-emerald-600 transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1.5">
                    <i class="fas fa-rocket"></i> Publish
                </button>
            </div>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 overflow-y-auto overflow-x-auto p-6 flex justify-center custom-scrollbar"
            @click.self="$emit('select-block', null)">

            <!-- Device frame wrapper -->
            <div class="transition-all duration-300 relative"
                :style="{ width: deviceWidth, transform: `scale(${zoom})`, transformOrigin: 'top center', marginBottom: `${(1 - zoom) * -100}%` }">

                <!-- Page Document -->
                <div class="bg-white shadow-2xl shadow-gray-300/50 min-h-screen relative"
                    :class="activeDevice === 'mobile' ? 'rounded-3xl border-4 border-gray-800 overflow-hidden' : activeDevice === 'tablet' ? 'rounded-xl border-2 border-gray-400 overflow-hidden' : ''"
                    @click.self="$emit('select-block', null)">

                    <!-- Drag-and-Drop Block List -->
                    <div ref="canvasRef" @dragover.prevent @drop="onDrop">
                        <template v-for="(block, index) in localBlocks" :key="block.id">
                            <!-- Drop Zone Above -->
                            <div class="h-1 transition-all"
                                :class="dragOverIndex === index ? 'h-8 bg-emerald-100 border-2 border-dashed border-emerald-400 mx-4 rounded-lg my-1' : ''"
                                @dragover.prevent="dragOverIndex = index"
                                @dragleave="dragOverIndex = null">
                            </div>

                            <!-- Block Wrapper -->
                            <div
                                :id="`block-${block.id}`"
                                draggable="true"
                                @dragstart="onDragStart(index)"
                                @dragend="dragOverIndex = null"
                                @click.stop="$emit('select-block', block)"
                                class="relative group border-2 border-transparent transition-all"
                                :class="selectedBlock?.id === block.id ? 'border-indigo-500 ring-2 ring-indigo-500/20 z-10' : 'hover:border-emerald-300/60'"
                            >
                                <!-- Block Label Bar -->
                                <div class="absolute -top-[26px] left-0 bg-indigo-600 text-white text-sm font-black tracking-widest uppercase px-2 py-1 rounded-t-md flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all z-30 pointer-events-none"
                                    :class="{'opacity-100': selectedBlock?.id === block.id}">
                                    <i :class="[getBlockIcon(block.type), 'text-indigo-300']"></i>
                                    <span>{{ block.name }}</span>
                                </div>

                                <!-- Block Quick Actions -->
                                <div class="absolute top-1 right-1 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all z-30"
                                    :class="{'opacity-100': selectedBlock?.id === block.id}">
                                    <div class="flex bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                        <button @click.stop="moveBlock(index, -1)" :disabled="index === 0"
                                            class="w-6 h-6 flex items-center justify-center text-sm text-gray-500 hover:bg-gray-100 border-r border-gray-100 disabled:opacity-30" title="Move Up">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button @click.stop="moveBlock(index, 1)" :disabled="index === localBlocks.length - 1"
                                            class="w-6 h-6 flex items-center justify-center text-sm text-gray-500 hover:bg-gray-100 border-r border-gray-100 disabled:opacity-30" title="Move Down">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                        <button @click.stop="duplicateBlock(index)"
                                            class="w-6 h-6 flex items-center justify-center text-sm text-gray-500 hover:bg-blue-50 hover:text-blue-600 border-r border-gray-100" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button @click.stop="$emit('delete-block', block.id)"
                                            class="w-6 h-6 flex items-center justify-center text-sm text-gray-500 hover:bg-red-50 hover:text-red-600" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Drag Handle -->
                                <div class="absolute left-1 top-1/2 -translate-y-1/2 cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-all z-20 bg-white border border-gray-200 rounded p-1 shadow-sm">
                                    <i class="fas fa-grip-vertical text-gray-400 text-sm"></i>
                                </div>

                                <!-- Block Renderer -->
                                <div class="w-full" :style="getBlockStyles(block)">
                                    <component
                                        :is="getBlockComponent(block.type)"
                                        v-if="getBlockComponent(block.type)"
                                        :block="block"
                                        :dynamicData="dynamicData"
                                    />
                                    <UnknownBlock v-else :block="block" />
                                </div>
                            </div>
                        </template>

                        <!-- Final Drop Zone -->
                        <div class="h-1"
                            :class="dragOverIndex === localBlocks.length ? 'h-8 bg-emerald-100 border-2 border-dashed border-emerald-400 mx-4 rounded-lg my-1' : ''"
                            @dragover.prevent="dragOverIndex = localBlocks.length"
                            @dragleave="dragOverIndex = null">
                        </div>
                    </div>

                    <!-- Add Block Placeholder -->
                    <div class="h-40 border-2 border-dashed border-gray-200 rounded-xl m-8 flex flex-col items-center justify-center gap-3
                                hover:bg-emerald-50/30 hover:border-emerald-300 transition-all cursor-pointer group"
                        @click="$emit('toggle-tray')">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-100 group-hover:scale-110 transition-all shadow-sm">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-base font-black uppercase tracking-widest text-gray-400 group-hover:text-emerald-600">Add a Section</p>
                            <p class="text-sm text-gray-300 mt-0.5">Click to browse 30+ blocks or press [A]</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, shallowRef, defineAsyncComponent, onMounted, onUnmounted } from 'vue';
import { v4 as uuidv4 } from 'uuid';

// ── Block Imports ──
import HeroBlock       from '../Components/CanvasBlocks/HeroBlock.vue';
import FeatureGrid     from '../Components/CanvasBlocks/FeatureGrid.vue';
import TextBlock       from '../Components/CanvasBlocks/TextBlock.vue';
import TestimonialBlock from '../Components/CanvasBlocks/TestimonialBlock.vue';
import PricingBlock    from '../Components/CanvasBlocks/PricingBlock.vue';
import GalleryBlock    from '../Components/CanvasBlocks/GalleryBlock.vue';
import StatsBlock      from '../Components/CanvasBlocks/StatsBlock.vue';
import CtaBlock        from '../Components/CanvasBlocks/CtaBlock.vue';
import FormBlock       from '../Components/CanvasBlocks/FormBlock.vue';
import ProductGrid     from '../Components/CanvasBlocks/ProductGrid.vue';
import FooterBlock     from '../Components/CanvasBlocks/FooterBlock.vue';
import NavbarBlock     from '../Components/CanvasBlocks/NavbarBlock.vue';
import TeamBlock       from '../Components/CanvasBlocks/TeamBlock.vue';
import FaqBlock        from '../Components/CanvasBlocks/FaqBlock.vue';
import VideoBlock      from '../Components/CanvasBlocks/VideoBlock.vue';
import BannerBlock     from '../Components/CanvasBlocks/BannerBlock.vue';
import UnknownBlock    from '../Components/CanvasBlocks/UnknownBlock.vue';

// ── Block registry ──
const BLOCK_MAP = {
    hero:        HeroBlock,
    features:    FeatureGrid,
    text:        TextBlock,
    testimonial: TestimonialBlock,
    pricing:     PricingBlock,
    gallery:     GalleryBlock,
    stats:       StatsBlock,
    cta:         CtaBlock,
    form:        FormBlock,
    products:    ProductGrid,
    footer:      FooterBlock,
    navbar:      NavbarBlock,
    team:        TeamBlock,
    faq:         FaqBlock,
    video:       VideoBlock,
    banner:      BannerBlock,
};

const BLOCK_ICONS = {
    hero: 'fas fa-heading', features: 'fas fa-th', text: 'fas fa-align-left',
    testimonial: 'fas fa-quote-left', pricing: 'fas fa-tags', gallery: 'fas fa-images',
    stats: 'fas fa-chart-bar', cta: 'fas fa-hand-pointer', form: 'fas fa-list-alt',
    products: 'fas fa-shopping-cart', footer: 'fas fa-shoe-prints', navbar: 'fas fa-bars',
    team: 'fas fa-users', faq: 'fas fa-question-circle', video: 'fas fa-play-circle',
    banner: 'fas fa-rectangle-landscape',
};

// ── Props ──
const props = defineProps({
    blocks:      { type: Array, default: () => [] },
    dynamicData: { type: Object, default: () => ({}) },
    selectedBlock: { type: Object, default: null },
    activePage:  { type: Object, default: null },
    siteSlug:    { type: String, default: 'main' },
});

const emit = defineEmits(['toggle-tray', 'select-block', 'delete-block', 'update:blocks', 'save', 'publish']);

// ── Local state synced from props ──
const localBlocks = ref([...props.blocks]);

watch(() => props.blocks, (val) => { 
    if (JSON.stringify(val) !== JSON.stringify(localBlocks.value)) {
        localBlocks.value = JSON.parse(JSON.stringify(val)); 
    }
}, { deep: true });

// ── Emit block changes up ──
watch(localBlocks, (val) => { 
    if (JSON.stringify(val) !== JSON.stringify(props.blocks)) {
        emit('update:blocks', JSON.parse(JSON.stringify(val))); 
    }
}, { deep: true });

// ── Dirty flag ──
const isDirty = ref(false);
watch(localBlocks, () => { isDirty.value = true; }, { deep: true });

// ── Device preview ──
const devices = [
    { id: 'desktop', icon: 'fas fa-desktop', label: 'Desktop (1280px)' },
    { id: 'tablet',  icon: 'fas fa-tablet-alt', label: 'Tablet (768px)' },
    { id: 'mobile',  icon: 'fas fa-mobile-alt', label: 'Mobile (375px)' },
];
const activeDevice = ref('desktop');
const deviceWidth  = computed(() => ({
    desktop: '1280px',
    tablet:  '768px',
    mobile:  '375px',
})[activeDevice.value]);

// ── Zoom ──
const zoom = ref(1);

// ── History (Undo/Redo) ──
const history      = ref([JSON.stringify(props.blocks)]);
const historyIndex = ref(0);

const pushHistory = () => {
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(JSON.stringify(localBlocks.value));
    historyIndex.value = history.value.length - 1;
    if (history.value.length > 50) history.value.shift();
};

const undo = () => {
    if (historyIndex.value <= 0) return;
    historyIndex.value--;
    localBlocks.value = JSON.parse(history.value[historyIndex.value]);
};
const redo = () => {
    if (historyIndex.value >= history.value.length - 1) return;
    historyIndex.value++;
    localBlocks.value = JSON.parse(history.value[historyIndex.value]);
};

// ── Drag & Drop ──
const dragIndex    = ref(null);
const dragOverIndex = ref(null);

const onDragStart = (index) => { dragIndex.value = index; };
const onDrop = (e) => {
    if (dragIndex.value === null || dragOverIndex.value === null) return;
    const from = dragIndex.value;
    const to   = dragOverIndex.value > from ? dragOverIndex.value - 1 : dragOverIndex.value;
    if (from === to) { dragIndex.value = dragOverIndex.value = null; return; }
    const arr = [...localBlocks.value];
    const [item] = arr.splice(from, 1);
    arr.splice(to, 0, item);
    localBlocks.value = arr;
    pushHistory();
    dragIndex.value = dragOverIndex.value = null;
};

// ── Block actions ──
const moveBlock = (index, dir) => {
    const newIndex = index + dir;
    if (newIndex < 0 || newIndex >= localBlocks.value.length) return;
    const arr = [...localBlocks.value];
    [arr[index], arr[newIndex]] = [arr[newIndex], arr[index]];
    localBlocks.value = arr;
    pushHistory();
};

const duplicateBlock = (index) => {
    const original = localBlocks.value[index];
    const clone    = JSON.parse(JSON.stringify(original));
    clone.id       = 'block_' + Date.now();
    clone.name     = original.name + ' (Copy)';
    localBlocks.value.splice(index + 1, 0, clone);
    pushHistory();
};

// ── Helpers ──
const getBlockComponent = (type) => BLOCK_MAP[type] || null;
const getBlockIcon      = (type) => BLOCK_ICONS[type] || 'fas fa-cube';

const getBlockStyles = (block) => {
    const s = block.styles || {};
    const styles = {};
    if (s.bgColor)     styles.backgroundColor = s.bgColor;
    if (s.bgGradient)  styles.background       = s.bgGradient;
    if (s.bgImage)     { styles.backgroundImage = `url('${s.bgImage}')`; styles.backgroundSize = 'cover'; styles.backgroundPosition = 'center'; }
    if (s.paddingY)    styles.paddingTop = styles.paddingBottom = s.paddingY + 'px';
    if (s.paddingX)    styles.paddingLeft = styles.paddingRight = s.paddingX + 'px';
    if (s.minHeight)   styles.minHeight  = s.minHeight + 'px';
    if (s.textColor)   styles.color      = s.textColor;
    if (s.borderTop)   styles.borderTop  = s.borderTop;
    if (s.borderBottom) styles.borderBottom = s.borderBottom;
    return styles;
};

// ── Keyboard shortcuts ──
const handleKey = (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (e.key === 'a' || e.key === 'A') emit('toggle-tray');
    if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) { e.preventDefault(); redo(); }
    if (e.key === 'Delete' || e.key === 'Backspace') {
        if (props.selectedBlock) { emit('delete-block', props.selectedBlock.id); e.preventDefault(); }
    }
    if (e.key === 'Escape') emit('select-block', null);
};
onMounted(()  => window.addEventListener('keydown', handleKey));
onUnmounted(() => window.removeEventListener('keydown', handleKey));
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
</style>
