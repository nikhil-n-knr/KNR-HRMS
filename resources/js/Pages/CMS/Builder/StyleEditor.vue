<template>
    <div class="w-72 bg-white border-l border-gray-200 z-20 flex flex-col h-full shrink-0 transform transition-transform duration-300"
        :class="isOpen ? 'translate-x-0' : 'translate-x-full absolute right-0'">

        <!-- Header -->
        <div class="h-11 border-b border-gray-200 flex items-center justify-between px-4 bg-white shrink-0">
            <div class="flex items-center gap-2">
                <i class="fas fa-sliders-h text-indigo-500 text-sm"></i>
                <h3 class="font-black text-gray-900 text-sm">Inspector</h3>
            </div>
            <button @click="$emit('close')" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-800">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <template v-if="localBlock">
            <!-- Tab Bar -->
            <div class="flex border-b border-gray-200 shrink-0 bg-gray-50/50">
                <button v-for="t in tabs" :key="t.id" @click="activeTab = t.id"
                    class="flex-1 py-2.5 text-sm font-black uppercase tracking-widest transition-all border-b-2"
                    :class="activeTab === t.id ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-gray-400 hover:text-gray-600'">
                    <i :class="[t.icon, 'block text-sm mb-0.5']"></i>{{ t.label }}
                </button>
            </div>

            <!-- Block badge -->
            <div class="px-4 py-2 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2 shrink-0">
                <i :class="[getBlockIcon(localBlock.type), 'text-indigo-400 text-xs w-3']"></i>
                <span class="text-sm font-black text-indigo-700 truncate">{{ localBlock.name }}</span>
                <span class="ml-auto text-xs font-bold text-indigo-400 uppercase tracking-widest">{{ localBlock.type }}</span>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar">

                <!-- ══ CONTENT TAB ══ -->
                <div v-if="activeTab === 'content'" class="p-4 space-y-4">

                    <!-- Badge (Hero only) -->
                    <div v-if="localBlock.type === 'hero'">
                        <label class="label">Badge / Small Tag</label>
                        <input v-model="localBlock.content.badge" type="text" class="input-field" placeholder="e.g. NEW FEATURE" />
                    </div>

                    <!-- Title / Heading -->
                    <div v-if="localBlock.content?.title !== undefined">
                        <div class="flex items-center justify-between mb-1">
                            <label class="label">Heading</label>
                            <button class="w-5 h-5 rounded bg-violet-50 text-violet-500 border border-violet-100 flex items-center justify-center shadow-sm" title="AI Generate Content">
                                <i class="fas fa-robot text-xs"></i>
                            </button>
                        </div>
                        <textarea v-model="localBlock.content.title" rows="3" class="input-field resize-none leading-relaxed"></textarea>
                    </div>

                    <!-- Subtitle -->
                    <div v-if="localBlock.content?.subtitle !== undefined">
                        <label class="label">Subtitle / Description</label>
                        <textarea v-model="localBlock.content.subtitle" rows="3" class="input-field resize-none leading-relaxed"></textarea>
                    </div>

                    <!-- Hero Image -->
                    <div v-if="localBlock.type === 'hero' || localBlock.content?.hero_image !== undefined">
                        <label class="label">Hero Image URL</label>
                        <div class="flex gap-2">
                             <input v-model="localBlock.content.hero_image" type="text" class="input-field font-mono text-sm" placeholder="https://..." />
                             <button class="px-3 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-lg text-xs" title="Select from Media">
                                <i class="fas fa-images"></i>
                             </button>
                        </div>
                    </div>

                    <!-- Body / Rich text -->
                    <div v-if="localBlock.content?.body !== undefined">
                        <label class="label">Body Content</label>
                        <textarea v-model="localBlock.content.body" rows="4" class="input-field resize-none font-mono text-sm"></textarea>
                    </div>

                    <!-- Primary Button -->
                    <div v-if="localBlock.content?.btn_text !== undefined">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                            <p class="text-sm font-black uppercase text-gray-400">Primary Button</p>
                            <div>
                                <label class="label mb-1">Label</label>
                                <input v-model="localBlock.content.btn_text" type="text" class="input-field" />
                            </div>
                            <div>
                                <label class="label mb-1">Link / Action</label>
                                <input v-model="localBlock.content.btn_link" type="text" placeholder="https://" class="input-field font-mono text-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Button (Hero only) -->
                    <div v-if="localBlock.type === 'hero'">
                         <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                            <p class="text-sm font-black uppercase text-gray-400">Secondary Button</p>
                            <div>
                                <label class="label mb-1">Label</label>
                                <input v-model="localBlock.content.btn2_text" type="text" class="input-field" />
                            </div>
                            <div>
                                <label class="label mb-1">Link</label>
                                <input v-model="localBlock.content.btn2_link" type="text" placeholder="https://" class="input-field font-mono text-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Trust / Checkmarks (Hero only) -->
                    <div v-if="localBlock.type === 'hero'">
                         <label class="label">Trust Indicators (Comma separated)</label>
                         <input v-model="trustIndicatorsStr" type="text" class="input-field" placeholder="e.g. Free Trial, ISO Certified" />
                    </div>

                    <!-- Layout selector -->
                    <div v-if="layoutOptions[localBlock.type]">
                        <label class="label">Layout Style</label>
                        <select v-model="localBlock.content.layout" class="input-field">
                            <option v-for="opt in layoutOptions[localBlock.type]" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>

                    <!-- CRM Data Bind -->
                    <div class="p-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-400 transition-all cursor-pointer group">
                        <div class="flex items-center gap-2">
                             <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <i class="fas fa-database text-xs"></i>
                             </div>
                             <div>
                                <p class="text-sm font-black">Fetch CRM Records</p>
                                <p class="text-sm opacity-70">Dynamically load live data</p>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- ══ STYLE TAB ══ -->
                <div v-if="activeTab === 'style'" class="p-4 space-y-5">

                    <!-- Background -->
                    <div>
                        <label class="label mb-2">Background Config</label>
                        <div class="flex bg-gray-100 rounded-lg p-0.5 border border-gray-200 mb-3">
                            <button v-for="bg in bgTypes" :key="bg.id" @click="activeBgType = bg.id"
                                class="flex-1 py-1.5 rounded-md text-sm font-black uppercase transition-all flex items-center justify-center gap-1"
                                :class="activeBgType === bg.id ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-400 hover:text-gray-600'">
                                {{ bg.label }}
                            </button>
                        </div>

                        <!-- Solid Color -->
                        <div v-if="activeBgType === 'solid'" class="flex items-center gap-2">
                            <input type="color" v-model="localBlock.styles.bgColor" class="w-10 h-10 rounded-xl border border-gray-200 p-1 cursor-pointer" />
                            <input type="text" v-model="localBlock.styles.bgColor" class="input-field font-mono flex-1 text-center" placeholder="#ffffff" />
                        </div>

                        <!-- Gradient -->
                        <div v-if="activeBgType === 'gradient'" class="space-y-3">
                            <label class="label">Gradient Expression</label>
                            <input type="text" v-model="localBlock.styles.bgGradient" class="input-field font-mono text-sm" placeholder="linear-gradient(...)" />
                            <div class="grid grid-cols-5 gap-2 italic">
                                <button v-for="g in gradientPresets" :key="g" @click="localBlock.styles.bgGradient = g"
                                    class="h-6 rounded-md shadow-sm border border-white/40 ring-1 ring-black/5"
                                    :style="{ background: g }">
                                </button>
                            </div>
                        </div>

                        <!-- Image -->
                        <div v-if="activeBgType === 'image'" class="space-y-2">
                            <label class="label">Background Image URL</label>
                            <input v-model="localBlock.styles.bgImage" type="text" class="input-field font-mono text-sm" placeholder="https://..." />
                        </div>
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label class="label mb-1">Global Text Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="localBlock.styles.textColor" class="w-10 h-10 rounded-xl border border-gray-200 p-1 cursor-pointer" />
                            <input v-model="localBlock.styles.textColor" type="text" class="input-field font-mono flex-1 text-center" />
                        </div>
                    </div>

                    <!-- Dimensional Padding -->
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="label">Vertical Padding</label>
                                <span class="text-sm font-black text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">{{ localBlock.styles?.paddingY || 0 }}px</span>
                            </div>
                            <input type="range" min="0" max="250" step="10"
                                v-model.number="localBlock.styles.paddingY"
                                class="w-full h-1.5 accent-indigo-500 rounded-lg cursor-pointer" />
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="label">Min Height (Canvas Preview)</label>
                                <span class="text-sm font-black text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">{{ localBlock.styles?.minHeight || 0 }}px</span>
                            </div>
                            <input type="range" min="0" max="1000" step="50"
                                v-model.number="localBlock.styles.minHeight"
                                class="w-full h-1.5 accent-indigo-500 rounded-lg cursor-pointer" />
                        </div>
                    </div>

                    <!-- Border Decor -->
                    <div>
                        <label class="label mb-1">Bottom Accent Border</label>
                        <select v-model="localBlock.styles.borderBottom" class="input-field">
                            <option value="">None</option>
                            <option value="1px solid #f3f4f6">Ultra Light</option>
                            <option value="1px solid #e5e7eb">Soft Gray</option>
                            <option value="2px solid #6366f1">Indigo (2px)</option>
                            <option value="4px solid #10b981">Emerald (4px)</option>
                        </select>
                    </div>
                </div>

                <!-- ══ ADVANCED TAB ══ -->
                <div v-if="activeTab === 'advanced'" class="p-4 space-y-4">
                    <div>
                        <label class="label mb-1">System Block ID</label>
                        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-200 font-mono text-sm text-gray-400 select-all">
                            {{ localBlock.id }}
                        </div>
                    </div>

                    <div>
                        <label class="label mb-1">Custom CSS Utility Classes</label>
                        <input v-model="localBlock.styles.extraClasses" type="text" class="input-field font-mono text-sm" placeholder="e.g. animate-bounce skew-y-3" />
                    </div>

                    <div>
                        <label class="label mb-2">Device Visibility</label>
                        <div class="grid grid-cols-3 gap-1">
                            <button v-for="v in ['all','desktop','mobile']" :key="v"
                                @click="localBlock.settings.visibility = v"
                                class="py-2 rounded-lg border text-sm font-black uppercase transition-all"
                                :class="(localBlock.settings?.visibility || 'all') === v ? 'bg-indigo-600 text-white border-indigo-700' : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'">
                                {{ v }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="label mb-2">Targeting Segment</label>
                        <select v-model="localBlock.settings.segment" class="input-field">
                            <option value="all">Everyone</option>
                            <option value="new">New Visitors Only</option>
                            <option value="returning">Returning Leads</option>
                            <option value="customers">Paid Customers</option>
                            <option value="region_in">Region: India Only</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <button @click="$emit('delete-block', localBlock.id)"
                            class="w-full py-2.5 rounded-xl bg-red-50 text-red-600 text-sm font-black uppercase tracking-widest hover:bg-red-100 border border-red-100 flex items-center justify-center gap-2 shadow-sm transition-all duration-150">
                            <i class="fas fa-trash-alt"></i> Delete Section
                        </button>
                    </div>
                </div>

            </div>
        </template>

        <!-- Empty State -->
        <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-gray-50/50">
            <div class="w-16 h-16 rounded-[2.5rem] bg-indigo-50 flex items-center justify-center mb-4 shadow-inner ring-4 ring-white">
                <i class="fas fa-mouse-pointer text-2xl text-indigo-400"></i>
            </div>
            <h4 class="text-sm font-black text-gray-900 mb-1">Design Intelligence</h4>
            <p class="text-xs text-gray-400 leading-relaxed px-4">Select any visual element on the canvas to configure its properties, styling, and logic.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    block:  { type: Object, default: null },
});
const emit = defineEmits(['close', 'delete-block', 'update:block']);

// ── Tab State ──
const activeTab    = ref('content');
const activeBgType = ref('solid');

// ── Local clone to prevent cursor jumping ──
const localBlock = ref(null);
let isUpdatingLocally = false;

// Initialize / Update local state from props
const syncFromProp = () => {
    if (!props.block) { localBlock.value = null; return; }
    
    // Create deep copy
    const copy = JSON.parse(JSON.stringify(props.block));
    
    // Ensure nested objects exist to avoid undefined errors in v-model
    if (!copy.content) copy.content = {};
    if (!copy.styles)  copy.styles = {};
    if (!copy.settings) copy.settings = { visibility: 'all', segment: 'all' };
    
    localBlock.value = copy;

    // Detect background type for switcher
    if (copy.styles?.bgImage)    activeBgType.value = 'image';
    else if (copy.styles?.bgGradient) activeBgType.value = 'gradient';
    else if (copy.styles?.bgColor)   activeBgType.value = 'solid';
    else activeBgType.value = 'none';
};

// Sync on mount
onMounted(syncFromProp);

// Watch for prop change (external updates like Undo/Initial Select)
watch(() => props.block?.id, (newId, oldId) => {
    if (newId !== oldId) {
        syncFromProp();
        activeTab.value = 'content';
    }
});

// Watch for content changes (for Undo specifically if the ID is same)
watch(() => props.block, (newVal) => {
    if (!newVal || !localBlock.value) return;
    if (JSON.stringify(newVal) !== JSON.stringify(localBlock.value)) {
        syncFromProp();
    }
}, { deep: true });

// Emit changes to parent
watch(localBlock, (newVal) => {
    if (!newVal || !props.block) return;
    if (JSON.stringify(newVal) !== JSON.stringify(props.block)) {
        emit('update:block', JSON.parse(JSON.stringify(newVal)));
    }
}, { deep: true });

// ── Special handling for CSV fields ──
const trustIndicatorsStr = computed({
    get: () => localBlock.value?.content?.trust?.join(', ') || '',
    set: (val) => {
        if (!localBlock.value.content) localBlock.value.content = {};
        localBlock.value.content.trust = val.split(',').map(s => s.trim()).filter(s => s);
    }
});

// ── Config ──
const tabs = [
    { id: 'content',  icon: 'fas fa-pen-nib',  label: 'Content' },
    { id: 'style',    icon: 'fas fa-swatchbook', label: 'Style' },
    { id: 'advanced', icon: 'fas fa-dna',  label: 'Advanced' },
];

const bgTypes = [
    { id: 'solid',    label: 'Color' },
    { id: 'gradient', label: 'Gradient' },
    { id: 'image',    label: 'Image' },
    { id: 'none',     label: 'None' },
];

const gradientPresets = [
    'linear-gradient(135deg,#667eea,#764ba2)', 'linear-gradient(135deg,#6d28d9,#ec4899)',
    'linear-gradient(135deg,#0ea5e9,#10b981)', 'linear-gradient(135deg,#f59e0b,#ef4444)',
    'linear-gradient(135deg,#0f172a,#23395d)'
];

const layoutOptions = {
    hero: [
        { value: 'split',    label: 'Standard Split' },
        { value: 'centered', label: 'Centered Focus' },
        { value: 'minimal',  label: 'Minimalist' }
    ],
    features: [
        { value: 'grid3', label: '3-Column Grid' },
        { value: 'zigzag', label: 'Zig-Zag alternating' }
    ]
};

const BLOCK_ICONS = {
    hero: 'fas fa-star', features: 'fas fa-rocket', text: 'fas fa-paragraph',
};
const getBlockIcon = (type) => BLOCK_ICONS[type] || 'fas fa-cube';
</script>

<style scoped>
.label {
    display: block;
    font-size: 0.6rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #9ca3af;
    margin-bottom: 0.35rem;
}
.input-field {
    width: 100%;
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 0.6rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.input-field:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    background-color: #ffffff;
}
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #d1d5db; }
</style>
