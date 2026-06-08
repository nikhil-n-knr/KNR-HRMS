<template>
    <div class="h-full bg-white border-r border-gray-100 flex flex-col w-56 shrink-0 overflow-y-auto custom-scrollbar">
        <!-- Logo Header -->
        <div class="p-3 sticky top-0 bg-white/98 backdrop-blur-sm z-10 border-b border-gray-100">
            <div class="flex items-center gap-2 px-2 py-1">
                <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-sm shadow-emerald-500/30">
                    <i class="fas fa-layer-group text-white text-base"></i>
                </div>
                <div>
                    <h1 class="text-base font-black tracking-wider text-gray-900 leading-none">OneHub Connect</h1>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">CMS Engine 6.0</p>
                </div>
            </div>
        </div>

        <!-- Site Mode Toggle -->
        <div class="px-3 py-2.5 border-b border-gray-100 bg-gray-50/50">
            <p class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-1.5 px-1">Site Type</p>
            <div class="flex bg-gray-100 rounded-lg p-0.5 border border-gray-200">
                <button
                    @click="emit('mode-change', 'static')"
                    class="flex-1 py-1.5 rounded-md text-sm font-black uppercase tracking-wide transition-all text-center flex items-center justify-center gap-1"
                    :class="siteMode === 'static'
                        ? 'bg-white shadow-sm text-emerald-700 border border-emerald-100'
                        : 'text-gray-400 hover:text-gray-600'"
                >
                    <i class="fas fa-file-alt text-xs"></i> Static
                </button>
                <button
                    @click="emit('mode-change', 'ecommerce')"
                    class="flex-1 py-1.5 rounded-md text-sm font-black uppercase tracking-wide transition-all text-center flex items-center justify-center gap-1"
                    :class="siteMode === 'ecommerce'
                        ? 'bg-white shadow-sm text-emerald-700 border border-emerald-100'
                        : 'text-gray-400 hover:text-gray-600'"
                >
                    <i class="fas fa-shopping-cart text-xs"></i> Ecom
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex-1 py-3 px-2 space-y-5">
            <div v-for="group in filteredNavigation" :key="group.name">
                <h3 class="px-2 text-xs font-extrabold text-gray-400 uppercase tracking-[0.2em] mb-1.5">{{ group.name }}</h3>
                <div class="space-y-0.5">
                    <Link
                        v-for="item in group.items"
                        :key="item.id"
                        :href="route('cms.hub', { section: item.id })"
                        class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg transition-all duration-150 group text-left relative"
                        :class="[
                            currentSection === item.id
                                ? 'bg-emerald-50 text-emerald-700 shadow-sm'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        <div class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-all"
                            :class="currentSection === item.id ? 'bg-emerald-100' : 'bg-gray-100 group-hover:bg-gray-200'"
                        >
                            <i :class="[item.icon, 'text-sm transition-colors', currentSection === item.id ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600']"></i>
                        </div>
                        <span class="text-sm font-bold tracking-wide flex-1 truncate">{{ item.name }}</span>
                        <!-- Badge for ecom-only modules -->
                        <span v-if="item.badge" class="text-xs font-black bg-indigo-100 text-indigo-600 px-1 py-0.5 rounded uppercase tracking-wider">{{ item.badge }}</span>
                        <!-- Active dot -->
                        <div v-if="currentSection === item.id" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Bottom: Publish Button -->
        <div class="p-3 border-t border-gray-100 bg-gray-50/50 sticky bottom-0">
            <button class="w-full py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:from-emerald-600 hover:to-teal-600 transition-all shadow-md shadow-emerald-500/20 flex items-center justify-center gap-1.5">
                <i class="fas fa-rocket text-sm"></i> Publish Site
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    currentSection: { type: String, required: true },
    siteMode: { type: String, default: 'static' },
});

const emit = defineEmits(['mode-change']);

const hasPermission = () => true; // Replace with real permission check

const navigation = [
    {
        name: 'BUILD & DESIGN',
        mode: 'both',
        items: [
            { id: 'site_builder',     name: 'Site Builder',      icon: 'fas fa-house-damage',  permission: 'CMS.SiteBuilder.View' },
            { id: 'home_builder',     name: 'Canvas Editor',     icon: 'fas fa-vector-square', permission: 'CMS.HomeBuilder.View' },
            { id: 'page_manager',     name: 'Page Manager',      icon: 'fas fa-sitemap',        permission: 'CMS.PageManager.View' },
            { id: 'theme_engine',     name: 'Theme Studio',      icon: 'fas fa-paint-roller',   permission: 'CMS.ThemeEngine.View' },
            { id: 'components_hub',   name: 'Components Hub',    icon: 'fas fa-puzzle-piece',   permission: 'CMS.Components.View' },
        ]
    },
    {
        name: 'CONTENT & ASSETS',
        mode: 'both',
        items: [
            { id: 'form_builder',     name: 'Form Builder',      icon: 'fas fa-list-alt',       permission: 'CMS.Forms.View' },
            { id: 'content_library',  name: 'Media Library',     icon: 'fas fa-images',         permission: 'CMS.Media.View' },
            { id: 'ai_builder',       name: 'AI Builder',        icon: 'fas fa-robot',          permission: 'CMS.AI.View' },
        ]
    },
    {
        name: 'ECOMMERCE',
        mode: 'ecommerce',
        items: [
            { id: 'products',         name: 'Products',          icon: 'fas fa-boxes',          permission: 'CMS.Products.View',  badge: 'Ecom' },
            { id: 'orders',           name: 'Orders',            icon: 'fas fa-shopping-bag',   permission: 'CMS.Orders.View',    badge: 'Ecom' },
            { id: 'coupons',          name: 'Special Offers',    icon: 'fas fa-tag',            permission: 'CMS.Coupons.View',   badge: 'Ecom' },
            { id: 'cart_checkout',    name: 'Cart & Checkout',   icon: 'fas fa-cart-plus',      permission: 'CMS.Cart.View',      badge: 'Ecom' },
            { id: 'payments',         name: 'Payments',          icon: 'fas fa-credit-card',    permission: 'CMS.Payments.View',  badge: 'Ecom' },
        ]
    },
    {
        name: 'OPTIMIZATION',
        mode: 'both',
        items: [
            { id: 'seo_master',       name: 'SEO Master',        icon: 'fas fa-search',         permission: 'CMS.SEO.View' },
            { id: 'analytics',        name: 'Analytics',         icon: 'fas fa-chart-line',     permission: 'CMS.Analytics.View' },
            { id: 'performance',      name: 'Performance',       icon: 'fas fa-tachometer-alt', permission: 'CMS.Performance.View' },
            { id: 'ab_testing',       name: 'A/B Testing',       icon: 'fas fa-flask',          permission: 'CMS.AB.View' },
        ]
    },
    {
        name: 'CRM & DATA',
        mode: 'both',
        items: [
            { id: 'crm_integration',  name: 'CRM Integration',   icon: 'fas fa-plug',           permission: 'CMS.CRM.View' },
            { id: 'user_segments',    name: 'User Segments',     icon: 'fas fa-users',          permission: 'CMS.Segments.View' },
        ]
    },
    {
        name: 'SYSTEM',
        mode: 'both',
        items: [
            { id: 'pwa_builder',      name: 'PWA Builder',       icon: 'fas fa-bolt',           permission: 'CMS.PWA.View' },
            { id: 'version_control',  name: 'Version Control',   icon: 'fas fa-code-branch',    permission: 'CMS.Versions.View' },
            { id: 'security',         name: 'Security',          icon: 'fas fa-shield-alt',     permission: 'CMS.Security.View' },
            { id: 'multi_site',       name: 'Multi-Site',        icon: 'fas fa-globe',          permission: 'CMS.MultiSite.View' },
            { id: 'global_settings',  name: 'Settings',          icon: 'fas fa-cog',            permission: 'CMS.Settings.View' },
        ]
    },
];

const filteredNavigation = computed(() => {
    return navigation
        .filter(group => group.mode === 'both' || group.mode === props.siteMode)
        .map(group => ({
            ...group,
            items: group.items.filter(item => hasPermission(item.permission))
        }))
        .filter(group => group.items.length > 0);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #d1d5db; }
</style>
