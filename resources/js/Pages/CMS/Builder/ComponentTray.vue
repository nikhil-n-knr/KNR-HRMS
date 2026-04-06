<template>
    <!-- Backdrop -->
    <div v-show="isOpen" class="fixed inset-0 bg-gray-900/20 backdrop-blur-[2px] z-30" @click="$emit('close')"></div>

    <!-- Tray Drawer -->
    <div class="fixed top-0 bottom-0 left-0 w-[360px] bg-white border-r border-gray-200 shadow-2xl z-40 flex flex-col transform transition-transform duration-300"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'">

        <!-- Header -->
        <div class="h-14 bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-500 flex items-center justify-between px-5 shrink-0 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,rgba(255,255,255,.15)_10px,rgba(255,255,255,.15)_20px)]"></div>
            <div class="flex items-center gap-3 relative">
                <div class="w-8 h-8 rounded-lg bg-white/20 border border-white/30 flex items-center justify-center">
                    <i class="fas fa-th text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="font-black text-white text-sm">Section Library</h3>
                    <p class="text-sm text-emerald-100 font-bold uppercase tracking-widest mt-0.5">30+ Premium Blocks</p>
                </div>
            </div>
            <button @click="$emit('close')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center border border-white/20 relative">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Search -->
        <div class="p-3 border-b border-gray-100 bg-gray-50/50 shrink-0">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                <input v-model="search" type="text" placeholder="Search blocks..." class="w-full bg-white border border-gray-200 rounded-xl py-2 pl-8 pr-3 text-xs font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" />
            </div>
            <!-- Category Pills -->
            <div class="flex gap-1.5 mt-2.5 overflow-x-auto pb-1 custom-scrollbar">
                <button v-for="cat in categories" :key="cat.id" @click="activeCategory = cat.id"
                    class="whitespace-nowrap px-2.5 py-1 rounded-full text-sm font-black uppercase tracking-wider transition-all border shrink-0"
                    :class="activeCategory === cat.id ? 'bg-emerald-500 text-white border-emerald-600 shadow-sm shadow-emerald-500/30' : 'bg-white text-gray-500 border-gray-200 hover:border-emerald-200 hover:text-emerald-600'">
                    <i :class="[cat.icon, 'mr-1']"></i>{{ cat.label }}
                </button>
            </div>
        </div>

        <!-- Block Grid -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-4 bg-gray-50">
            <div v-for="group in filteredGroups" :key="group.category">
                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400 px-1 mb-2">{{ group.category }}</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div v-for="block in group.blocks" :key="block.id"
                        draggable="true"
                        @dragstart="onDragStartBlock(block)"
                        @click="addBlock(block)"
                        class="bg-white rounded-xl border border-gray-200 overflow-hidden cursor-pointer hover:border-emerald-400 hover:shadow-lg hover:shadow-emerald-500/10 transition-all group relative">
                        <!-- Thumbnail -->
                        <div class="h-20 flex items-center justify-center relative overflow-hidden"
                            :style="{ background: block.previewBg || '#f9fafb' }">
                            <i :class="[block.icon, 'text-2xl transition-transform group-hover:scale-110 duration-300']"
                                :style="{ color: block.iconColor || '#d1d5db' }"></i>
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 bg-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg -translate-y-1 group-hover:translate-y-0 transition-transform">
                                    <i class="fas fa-plus text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div class="p-2">
                            <p class="text-sm font-black text-gray-800 group-hover:text-emerald-600 transition-colors truncate">{{ block.name }}</p>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">{{ block.type }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty search state -->
            <div v-if="!filteredGroups.length" class="flex flex-col items-center justify-center py-12 text-gray-400">
                <i class="fas fa-search text-2xl text-gray-200 mb-2"></i>
                <p class="text-xs font-bold text-gray-500">No blocks found for "{{ search }}"</p>
            </div>

            <!-- AI Generator Card -->
            <div @click="$emit('close'); $router.push({query: {section: 'ai_builder'}})"
                class="mt-2 p-4 rounded-xl bg-gradient-to-br from-violet-50 via-indigo-50 to-purple-50 border border-indigo-100 cursor-pointer hover:shadow-md transition-all group relative overflow-hidden">
                <div class="absolute -right-2 -top-2 w-12 h-12 bg-purple-300/20 rounded-full blur-xl group-hover:bg-purple-300/40 transition-all"></div>
                <div class="flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-indigo-100 flex items-center justify-center text-indigo-500 shrink-0">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-indigo-900">Generate with AI →</h4>
                        <p class="text-sm text-indigo-500 mt-0.5">Describe a section, AI builds it.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ isOpen: { type: Boolean, default: false } });
const emit  = defineEmits(['close', 'add-block']);

const search = ref('');
const activeCategory = ref('all');

const categories = [
    { id: 'all',        icon: 'fas fa-th',           label: 'All' },
    { id: 'hero',       icon: 'fas fa-heading',      label: 'Heroes' },
    { id: 'features',   icon: 'fas fa-star',         label: 'Features' },
    { id: 'content',    icon: 'fas fa-align-left',   label: 'Content' },
    { id: 'ecom',       icon: 'fas fa-shopping-cart', label: 'Ecommerce' },
    { id: 'social',     icon: 'fas fa-quote-left',   label: 'Social' },
    { id: 'forms',      icon: 'fas fa-list-alt',     label: 'Forms' },
    { id: 'media',      icon: 'fas fa-images',       label: 'Media' },
    { id: 'nav',        icon: 'fas fa-bars',         label: 'Nav/Footer' },
];

const blockCatalog = [
    // Heroes
    { id:'h1', category:'Heroes', cat:'hero', name:'Split Hero – Dark', type:'hero', icon:'fas fa-heading', iconColor:'#6366f1', previewBg:'linear-gradient(135deg,#1e1b4b,#312e81)', defaults:{ styles:{bgColor:'#0f172a',textColor:'#fff',paddingY:120}, content:{title:'Build Smarter with AI',subtitle:'Connected CRM, CMS, and Analytics in one platform.',btn_text:'Get Started Free',btn_link:'#',layout:'split'} } },
    { id:'h2', category:'Heroes', cat:'hero', name:'Centered Hero – Light', type:'hero', icon:'fas fa-align-center', iconColor:'#10b981', previewBg:'linear-gradient(135deg,#ecfdf5,#f0fdf4)', defaults:{ styles:{bgColor:'#ffffff',paddingY:100}, content:{title:'Welcome to Our World',subtitle:'Beautifully crafted for your brand.',btn_text:'Explore',layout:'centered'} } },
    { id:'h3', category:'Heroes', cat:'hero', name:'Gradient Mesh Hero', type:'hero', icon:'fas fa-magic', iconColor:'#a855f7', previewBg:'linear-gradient(135deg,#6d28d9,#ec4899)', defaults:{ styles:{bgGradient:'linear-gradient(135deg,#6d28d9 0%,#ec4899 100%)',paddingY:120,textColor:'#fff'}, content:{title:'Extraordinary Experiences',subtitle:'Where design meets performance.'} } },
    { id:'h4', category:'Heroes', cat:'hero', name:'Video Background Hero', type:'video', icon:'fas fa-play-circle', iconColor:'#f59e0b', previewBg:'#1c1917', defaults:{ styles:{paddingY:120,textColor:'#fff'}, content:{title:'Motion Speaks Louder',video_url:'https://example.com/hero.mp4'} } },

    // Features
    { id:'f1', category:'Features', cat:'features', name:'3-Column Icon Grid', type:'features', icon:'fas fa-th', iconColor:'#10b981', previewBg:'#f0fdf4', defaults:{ styles:{paddingY:80}, content:{title:'Everything You Need',items:[{icon:'fas fa-rocket',title:'Fast Deploy',body:'Ship in days not months'},{icon:'fas fa-brain',title:'AI Powered',body:'Gemini integrated'},{icon:'fas fa-shield-alt',title:'Secure',body:'SOC2 ready'}] } } },
    { id:'f2', category:'Features', cat:'features', name:'Zig-Zag Feature Layout', type:'features', icon:'fas fa-stream', iconColor:'#6366f1', previewBg:'#eef2ff', defaults:{ styles:{paddingY:80}, content:{layout:'zigzag',items:[{title:'Feature 1',body:'Description',image:''}]} } },
    { id:'f3', category:'Features', cat:'features', name:'Stats / Numbers Grid', type:'stats', icon:'fas fa-chart-bar', iconColor:'#f59e0b', previewBg:'#fffbeb', defaults:{ styles:{paddingY:60,bgColor:'#0f172a',textColor:'#fff'}, content:{stats:[{number:'100K+',label:'Customers'},{number:'₹2Cr+',label:'GMV Processed'},{number:'99.9%',label:'Uptime'},{number:'40+',label:'Integrations'}]} } },
    { id:'f4', category:'Features', cat:'features', name:'Process / Steps', type:'features', icon:'fas fa-arrow-right', iconColor:'#06b6d4', previewBg:'#ecfeff', defaults:{ styles:{paddingY:80}, content:{layout:'steps',items:[{step:1,title:'Sign Up'},{step:2,title:'Connect CRM'},{step:3,title:'Go Live'}]} } },

    // Content
    { id:'c1', category:'Content', cat:'content', name:'Rich Text + Image', type:'text', icon:'fas fa-align-left', iconColor:'#64748b', previewBg:'#f8fafc', defaults:{ styles:{paddingY:80}, content:{layout:'left-text',title:'Our Story',body:'<p>Body content here.</p>'} } },
    { id:'c2', category:'Content', cat:'content', name:'Text + CTA Button', type:'cta', icon:'fas fa-hand-pointer', iconColor:'#10b981', previewBg:'#f0fdf4', defaults:{ styles:{paddingY:80,bgColor:'#f0fdf4'}, content:{title:'Ready to Get Started?',subtitle:'Join 10,000+ teams today.',btn_text:'Start Free Trial',btn_link:'#'} } },
    { id:'c3', category:'Content', cat:'content', name:'FAQ Accordion', type:'faq', icon:'fas fa-question-circle', iconColor:'#8b5cf6', previewBg:'#f5f3ff', defaults:{ styles:{paddingY:80}, content:{title:'Frequently Asked Questions',faqs:[{q:'How does pricing work?',a:'We charge per site, per month.'},{q:'Can I import from WordPress?',a:'Yes — full migration support.'}]} } },
    { id:'c4', category:'Content', cat:'content', name:'Team Grid', type:'team', icon:'fas fa-users', iconColor:'#0ea5e9', previewBg:'#f0f9ff', defaults:{ styles:{paddingY:80}, content:{title:'Meet the Team',members:[{name:'CEO',role:'Founder',img:''},{name:'CTO',role:'Tech Lead',img:''}]} } },
    { id:'c5', category:'Content', cat:'content', name:'Banner / Alert Strip', type:'banner', icon:'fas fa-bullhorn', iconColor:'#f59e0b', previewBg:'#fef3c7', defaults:{ styles:{bgColor:'#fbbf24',textColor:'#1c1917',paddingY:12}, content:{text:'🚀 New feature launch! Read the announcement →',link:'#'} } },

    // Ecommerce
    { id:'e1', category:'Ecommerce', cat:'ecom', name:'Product Grid (Live CRM)', type:'products', icon:'fas fa-shopping-cart', iconColor:'#10b981', previewBg:'#f0fdf4', defaults:{ styles:{paddingY:80}, content:{title:'Our Products',source:'crm',limit:6} } },
    { id:'e2', category:'Ecommerce', cat:'ecom', name:'Featured Product Hero', type:'products', icon:'fas fa-star', iconColor:'#f59e0b', previewBg:'#fffbeb', defaults:{ styles:{paddingY:80}, content:{layout:'featured'} } },
    { id:'e3', category:'Ecommerce', cat:'ecom', name:'Cart Button / Sticky', type:'cta', icon:'fas fa-shopping-bag', iconColor:'#6366f1', previewBg:'#eef2ff', defaults:{ styles:{}, content:{layout:'cart_sticky'} } },
    { id:'e4', category:'Ecommerce', cat:'ecom', name:'Flash Sale Countdown', type:'banner', icon:'fas fa-bolt', iconColor:'#ef4444', previewBg:'#fef2f2', defaults:{ styles:{bgColor:'#ef4444',textColor:'#fff',paddingY:16}, content:{type:'flash_sale',text:'Flash Sale Ends In:',countdown_to:'2026-03-15T23:59:59'} } },

    // Social Proof
    { id:'s1', category:'Social Proof', cat:'social', name:'Testimonials Carousel', type:'testimonial', icon:'fas fa-quote-left', iconColor:'#6366f1', previewBg:'#eef2ff', defaults:{ styles:{paddingY:80}, content:{title:'What Our Customers Say',items:[{name:'Priya S.',company:'Startup India',quote:'Game changing CRM!'},{name:'Ravi K.',company:'TechPark Pvt',quote:'Best decision we made.'}]} } },
    { id:'s2', category:'Social Proof', cat:'social', name:'Logo Cloud / Partners', type:'features', icon:'fas fa-award', iconColor:'#64748b', previewBg:'#f8fafc', defaults:{ styles:{paddingY:40}, content:{layout:'logos',title:'Trusted by 100+ Companies',logos:[]} } },
    { id:'s3', category:'Social Proof', cat:'social', name:'Star Ratings / Reviews', type:'testimonial', icon:'fas fa-star-half-alt', iconColor:'#f59e0b', previewBg:'#fffbeb', defaults:{ styles:{paddingY:60}, content:{layout:'reviews',avg_rating:4.8} } },

    // Forms
    { id:'fm1', category:'Forms', cat:'forms', name:'Contact / Lead Form', type:'form', icon:'fas fa-envelope', iconColor:'#6366f1', previewBg:'#eef2ff', defaults:{ styles:{bgColor:'#f8fafc',paddingY:80}, content:{title:'Get In Touch',form_type:'contact',fields:['name','email','phone','message'],btn_text:'Send Message'} } },
    { id:'fm2', category:'Forms', cat:'forms', name:'Newsletter Subscribe', type:'form', icon:'fas fa-paper-plane', iconColor:'#10b981', previewBg:'#f0fdf4', defaults:{ styles:{paddingY:60}, content:{form_type:'newsletter',title:'Stay in the loop',placeholder:'Enter your email'} } },
    { id:'fm3', category:'Forms', cat:'forms', name:'Multi-Step Wizard Form', type:'form', icon:'fas fa-list-ol', iconColor:'#8b5cf6', previewBg:'#f5f3ff', defaults:{ styles:{paddingY:80}, content:{form_type:'wizard',steps:['Personal Info','Requirements','Submit']} } },
    { id:'fm4', category:'Forms', cat:'forms', name:'Booking / Appointment', type:'form', icon:'fas fa-calendar-check', iconColor:'#0ea5e9', previewBg:'#f0f9ff', defaults:{ styles:{paddingY:80}, content:{form_type:'booking',title:'Book a Demo',fields:['name','email','date','time']} } },

    // Media
    { id:'m1', category:'Media', cat:'media', name:'Image Gallery Grid', type:'gallery', icon:'fas fa-images', iconColor:'#10b981', previewBg:'#f0fdf4', defaults:{ styles:{paddingY:60}, content:{layout:'masonry',images:[]} } },
    { id:'m2', category:'Media', cat:'media', name:'Autoplay Video Section', type:'video', icon:'fas fa-film', iconColor:'#6366f1', previewBg:'#eef2ff', defaults:{ styles:{paddingY:80}, content:{title:'See It In Action',video_url:'',autoplay:true} } },

    // Nav / Footer
    { id:'n1', category:'Nav / Footer', cat:'nav', name:'Sticky Top Navbar', type:'navbar', icon:'fas fa-bars', iconColor:'#374151', previewBg:'#f9fafb', defaults:{ styles:{bgColor:'#ffffff'}, content:{logo_text:'KNR Office',links:[{label:'Features',href:'#'},{label:'Pricing',href:'#'},{label:'Contact',href:'#'}],btn_text:'Get Started',btn_link:'#'} } },
    { id:'n2', category:'Nav / Footer', cat:'nav', name:'Mega Footer', type:'footer', icon:'fas fa-shoe-prints', iconColor:'#64748b', previewBg:'#0f172a', defaults:{ styles:{bgColor:'#0f172a',textColor:'#94a3b8'}, content:{logo_text:'KNR Office',tagline:'Built for growing teams.',columns:[{heading:'Product',links:[]},{heading:'Company',links:[]},{heading:'Legal',links:[]}],socials:[{icon:'fab fa-twitter',url:'#'},{icon:'fab fa-linkedin',url:'#'}]} } },
    { id:'n3', category:'Nav / Footer', cat:'nav', name:'Simple Footer Strip', type:'footer', icon:'fas fa-minus', iconColor:'#9ca3af', previewBg:'#f9fafb', defaults:{ styles:{bgColor:'#f9fafb',paddingY:24}, content:{layout:'simple',copyright:'© 2026 KNR Office. All rights reserved.'} } },
];

const filteredGroups = computed(() => {
    const q = search.value.toLowerCase();
    const filtered = blockCatalog.filter(b =>
        (activeCategory.value === 'all' || b.cat === activeCategory.value) &&
        (!q || b.name.toLowerCase().includes(q) || b.type.toLowerCase().includes(q))
    );

    const groups = {};
    filtered.forEach(b => {
        if (!groups[b.category]) groups[b.category] = [];
        groups[b.category].push(b);
    });
    return Object.entries(groups).map(([category, blocks]) => ({ category, blocks }));
});

const onDragStartBlock = (blockDef) => {
    // Could integrate HTML5 drag to canvas in future
};

const addBlock = (blockDef) => {
    const newBlock = {
        id:      'block_' + Date.now(),
        type:    blockDef.type,
        name:    blockDef.name,
        styles:  { ...(blockDef.defaults?.styles || {}) },
        content: { ...(blockDef.defaults?.content || {}) },
        settings: { visibility: 'all' },
    };
    emit('add-block', newBlock);
    emit('close');
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
