<template>
    <div class="h-screen bg-gray-50 flex overflow-hidden">
        <!-- CMS Sidebar -->
        <Sidebar
            :currentSection="section"
            :siteMode="siteMode"
            @mode-change="handleModeChange"
        />

        <div class="flex-1 flex flex-col min-w-0 bg-gray-50/50 overflow-hidden">
            <!-- CMS Header -->
            <Header :currentSection="section" :kpis="global_kpis" :siteMode="siteMode" :sites="sites" :activeSite="activeSite" />

            <!-- Dynamic Module Rendering -->
            <main class="flex-1 overflow-hidden relative">

                <!-- ── SITE BUILDER ── -->
                <div v-if="section === 'site_builder'" class="h-full"><SiteBuilder :sites="sites" /></div>
 
                <!-- ── CANVAS EDITOR (Home Builder) ── -->
                <div v-show="section === 'home_builder'" class="h-full relative flex overflow-hidden">
                    <ComponentTray :isOpen="isTrayOpen" @close="isTrayOpen = false" @add-block="handleAddBlock" />
                    <CanvasEngine
                        v-model:blocks="blocks"
                        :dynamicData="$page.props.dynamicData || {}"
                        :selectedBlock="selectedBlock"
                        :activePage="activePage"
                        :siteSlug="activeSite?.slug || ''"
                        @select-block="handleSelectBlock"
                        @delete-block="handleDeleteBlock"
                        @toggle-tray="isTrayOpen = !isTrayOpen"
                        @save="saveBlocks"
                        @publish="publishSite"
                    />
                    <StyleEditor
                        :isOpen="!!selectedBlock"
                        :block="selectedBlock"
                        @close="selectedBlock = null"
                        @update:block="handleBlockUpdate"
                        @delete-block="handleDeleteBlock"
                    />
                </div>
 
                <!-- ── PAGE MANAGER ── -->
                <div v-if="section === 'page_manager'" class="h-full"><PageManager :pages="pages" :categories="categories" :active-site="activeSite" /></div>
 
                <!-- ── THEME STUDIO ── -->
                <div v-if="section === 'theme_engine'" class="h-full"><ThemeEngine :themes="themes" :active_theme="active_theme" /></div>
 
                <!-- ── COMPONENTS HUB ── -->
                <div v-if="section === 'components_hub'" class="h-full"><ComponentsHub /></div>
 
                <!-- ── FORM BUILDER ── -->
                <div v-if="section === 'form_builder'" class="h-full"><FormBuilder :forms="forms" /></div>
 
                <!-- ── MEDIA LIBRARY ── -->
                <div v-if="section === 'content_library'" class="h-full"><ContentLibrary :media="media" /></div>
 
                <!-- ── AI BUILDER ── -->
                <div v-if="section === 'ai_builder'" class="h-full"><AiBuilder /></div>
 
                <!-- ── PRODUCTS (Ecommerce) ── -->
                <div v-if="section === 'products'" class="h-full"><ProductsModule :products="products" :categories="categories" :active-site="activeSite" /></div>
 
                <!-- ── ORDERS (Ecommerce) ── -->
                <div v-if="section === 'orders'" class="h-full"><OrdersModule :orders="orders" /></div>
 
                <!-- ── COUPONS / SPECIAL OFFERS ── -->
                <div v-if="section === 'coupons'" class="h-full"><CouponsModule :coupons="coupons" /></div>
 
                <!-- ── CART & CHECKOUT CONFIG ── -->
                <div v-if="section === 'cart_checkout'" class="h-full"><CartCheckout /></div>
 
                <!-- ── PAYMENTS ── -->
                <div v-if="section === 'payments'" class="h-full"><PaymentsModule :payment_config="payment_config" /></div>
 
                <!-- ── SEO MASTER ── -->
                <div v-if="section === 'seo_master'" class="h-full"><SeoMaster :pages="pages" :seo_audit="seo_audit" /></div>
 
                <!-- ── ANALYTICS ── -->
                <div v-if="section === 'analytics'" class="h-full"><AnalyticsDashboard :analytics="analytics" /></div>
 
                <!-- ── PERFORMANCE ── -->
                <div v-if="section === 'performance'" class="h-full"><PerformanceModule /></div>
 
                <!-- ── A/B TESTING ── -->
                <div v-if="section === 'ab_testing'" class="h-full"><AbTesting :ab_tests="ab_tests" :pages="pages" /></div>
 
                <!-- ── CRM INTEGRATION ── -->
                <div v-if="section === 'crm_integration'" class="h-full"><CrmIntegration :crm_stats="crm_stats" /></div>
 
                <!-- ── USER SEGMENTS ── -->
                <div v-if="section === 'user_segments'" class="h-full"><UserSegments /></div>
 
                <!-- ── PWA BUILDER ── -->
                <div v-if="section === 'pwa_builder'" class="h-full"><PwaBuilder :pwa_config="pwa_config" /></div>
 
                <!-- ── VERSION CONTROL ── -->
                <div v-if="section === 'version_control'" class="h-full"><VersionControl :versions="versions" :pages="pages" /></div>
 
                <!-- ── SECURITY ── -->
                <div v-if="section === 'security'" class="h-full"><SecurityModule /></div>
 
                <!-- ── MULTI-SITE ── -->
                <div v-if="section === 'multi_site'" class="h-full"><MultiSite :sites="sites" /></div>
 
                <!-- ── GLOBAL SETTINGS ── -->
                <div v-if="section === 'global_settings'" class="h-full"><GlobalSettings :settings="settings" /></div>

            </main>

            <!-- Save Status Toast -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-y-4 opacity-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="translate-y-4 opacity-0"
            >
                <div v-if="saveStatus" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-xl text-sm font-black shadow-xl border flex items-center gap-2"
                    :class="{
                        'bg-white border-gray-200 text-gray-600': saveStatus === 'saving',
                        'bg-emerald-500 border-emerald-600 text-white': saveStatus === 'saved',
                        'bg-red-500 border-red-600 text-white': saveStatus === 'error',
                    }">
                    <i v-if="saveStatus === 'saving'" class="fas fa-spinner fa-spin text-indigo-500"></i>
                    <i v-else-if="saveStatus === 'saved'" class="fas fa-check-circle"></i>
                    <i v-else class="fas fa-exclamation-circle"></i>
                    <span v-if="saveStatus === 'saving'">Saving...</span>
                    <span v-else-if="saveStatus === 'saved'">Saved! ✓</span>
                    <span v-else>Save failed — check connection</span>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Layout
import Sidebar from './Components/Sidebar.vue';
import Header from './Components/Header.vue';

// Builder
import CanvasEngine from './Builder/CanvasEngine.vue';
import StyleEditor from './Builder/StyleEditor.vue';
import ComponentTray from './Builder/ComponentTray.vue';

// Core Modules
import PageManager from './Components/PageManager.vue';
import ThemeEngine from './Modules/ThemeEngine.vue';

// New Modules
import SiteBuilder from './Modules/SiteBuilder.vue';
import ComponentsHub from './Modules/ComponentsHub.vue';
import FormBuilder from './Modules/FormBuilder.vue';
import ContentLibrary from './Modules/ContentLibrary.vue';
import AiBuilder from './Modules/AiBuilder.vue';

// Ecommerce
import ProductsModule from './Modules/ProductsModule.vue';
import OrdersModule from './Modules/OrdersModule.vue';
import CouponsModule from './Modules/CouponsModule.vue';
import CartCheckout from './Modules/CartCheckout.vue';
import PaymentsModule from './Modules/PaymentsModule.vue';

// Analytics & SEO
import SeoMaster from './Modules/SeoMaster.vue';
import AnalyticsDashboard from './Modules/AnalyticsDashboard.vue';
import PerformanceModule from './Modules/PerformanceModule.vue';
import AbTesting from './Modules/AbTesting.vue';

// CRM & System
import CrmIntegration from './Modules/CrmIntegration.vue';
import UserSegments from './Modules/UserSegments.vue';
import PwaBuilder from './Modules/PwaBuilder.vue';
import VersionControl from './Modules/VersionControl.vue';
import SecurityModule from './Modules/SecurityModule.vue';
import MultiSite from './Modules/MultiSite.vue';
import GlobalSettings from './Modules/GlobalSettings.vue';

const props = defineProps({
    section: { type: String, required: true },
    tab: String,
    // Core
    pages: Array,
    active_theme: Object,
    themes: Array,
    versions: Array,
    global_kpis: Object,
    cms_permissions: Array,
    // Ecommerce
    products: Array,
    categories: Array,
    orders: Array,
    coupons: Array,
    payment_config: Object,
    // Content
    forms: Array,
    media: Array,
    // Analytics
    analytics: Object,
    seo_audit: Object,
    ab_tests: Array,
    // CRM & System
    crm_stats: Object,
    pwa_config: Object,
    settings: Object,
    sites: Array,
});

// ── Active site & page ──
const activeSite = computed(() => props.sites?.find(s => s.is_active_in_hub) ?? props.sites?.[0] ?? null);
const siteMode   = ref(activeSite.value?.type || 'static');
const saveStatus = ref(null); // null | 'saving' | 'saved' | 'error'
let   saveTimer  = null;

// ── Persist mode toggle to backend ──
const handleModeChange = async (mode) => {
    siteMode.value = mode;
    if (activeSite.value) {
        try {
            await axios.post(route('cms.sites.toggle-mode', activeSite.value.id));
        } catch (e) {
            console.warn('Mode toggle failed:', e.message);
        }
    }
};

// ── Publish site ──
const publishSite = async () => {
    if (!activeSite.value) return;
    try {
        await axios.post(route('cms.sites.publish', activeSite.value.id));
        setSaveStatus('saved', 'Site published!');
        router.reload({ only: ['sites', 'global_kpis'] });
    } catch (e) {
        setSaveStatus('error');
    }
};

// Expose publish to header via provide
import { provide } from 'vue';
provide('publishSite', publishSite);

// ── Builder state ──
const isTrayOpen   = ref(false);
const selectedBlock = ref(null);

// Load blocks from active page's saved layout_data
const activePage = computed(() => props.pages?.find(p => p.slug === '/') ?? props.pages?.[0] ?? null);
const blocks = ref([]);

// Watch for page change to sync blocks
watch(activePage, (newPage) => {
    if (newPage?.layout_data?.blocks) {
        blocks.value = [...newPage.layout_data.blocks];
    } else {
        // High impact default blocks
        blocks.value = [
            {
                id: 'hero_1', type: 'hero', name: 'Main Hero Cover',
                content: {
                    badge: 'AI Powered Site Engine',
                    title: 'Empower Your Teams with Connected Data',
                    subtitle: 'Deploy 100K pages per tenant — natively integrated with your CRM.',
                    btn_text: 'Get Started Free', btn_link: '#contact',
                    btn2_text: 'View Demo', btn2_link: '#demo',
                    trust: ['ISO 27001', 'PCI-DSS Compliant', 'GDPR Ready'],
                    layout: 'split',
                    hero_image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=2426',
                },
                styles: { paddingY: 120, bgColor: '#0f172a', textColor: '#ffffff' },
                settings: { visibility: 'all', segment: 'all' }
            },
            {
                id: 'feat_1', type: 'features', name: 'Core Features Grid',
                content: { layout: 'grid3' },
                styles: { paddingY: 80, bgColor: '#ffffff' },
                settings: { visibility: 'all', segment: 'all' }
            },
        ];
    }
}, { immediate: true });

// ── Canvas actions ──
const handleAddBlock    = (newBlock) => { blocks.value.push(newBlock); scheduleAutoSave(); };
const handleSelectBlock = (block)   => { selectedBlock.value = block; };
const handleDeleteBlock = (blockId) => {
    blocks.value = blocks.value.filter(b => b.id !== blockId);
    if (selectedBlock.value?.id === blockId) selectedBlock.value = null;
    scheduleAutoSave();
};

// ── Auto-save (debounced 3s) ──
const scheduleAutoSave = () => {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => saveBlocks(), 3000);
};

// ── Save blocks to backend ──
const saveBlocks = async () => {
    if (!activePage.value) return;
    saveStatus.value = 'saving';
    try {
        await axios.post(route('cms.pages.save-blocks', activePage.value.id), {
            blocks: blocks.value,
        });
        setSaveStatus('saved');
    } catch (e) {
        setSaveStatus('error');
        console.error('Save failed:', e);
    }
};

const setSaveStatus = (status, message) => {
    saveStatus.value = status;
    clearTimeout(saveTimer);
    if (status === 'saved' || status === 'error') {
        saveTimer = setTimeout(() => { saveStatus.value = null; }, 2500);
    }
};

// ── Ctrl+S keyboard shortcut ──
const handleKeyDown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveBlocks();
    }
};
onMounted(()  => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));
// ── Live block update from StyleEditor ──
const handleBlockUpdate = (updatedBlock) => {
    const idx = blocks.value.findIndex(b => b.id === updatedBlock.id);
    if (idx !== -1) {
        blocks.value[idx] = { ...blocks.value[idx], ...updatedBlock };
        scheduleAutoSave();
        // Keep selectedBlock in sync
        if (selectedBlock.value?.id === updatedBlock.id) {
            selectedBlock.value = blocks.value[idx];
        }
    }
};
</script>
