<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">AI Builder</h2>
                <p class="text-xs text-gray-500 mt-0.5">Generate pages, write copy, create images, and optimise your site with AI — all in one place.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 border border-purple-200 rounded-full">
                    <div class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></div>
                    <span class="text-sm font-black text-purple-700">{{ creditsLeft }} credits left</span>
                </div>
                <button @click="tab = 'generate'" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i class="fas fa-magic text-xs"></i> Generate
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-hidden flex">
            <!-- Sidebar nav -->
            <div class="w-56 border-r border-gray-200 bg-white flex flex-col shrink-0">
                <div class="p-3 space-y-0.5">
                    <button v-for="item in navItems" :key="item.key" @click="tab = item.key"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-left"
                        :class="tab === item.key ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'text-gray-600 hover:bg-gray-50'">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                            :class="tab === item.key ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-500'">
                            <i :class="item.icon + ' text-base'"></i>
                        </div>
                        {{ item.label }}
                    </button>
                </div>
                <!-- Recent generations -->
                <div class="mt-4 px-3">
                    <p class="text-sm font-black uppercase tracking-widest text-gray-400 mb-2">Recent</p>
                    <div v-for="r in recent" :key="r.id" class="py-2 border-b border-gray-50 last:border-0 cursor-pointer hover:bg-gray-50 px-2 rounded-lg">
                        <p class="text-sm font-bold text-gray-700 truncate">{{ r.label }}</p>
                        <p class="text-sm text-gray-400">{{ r.time }}</p>
                    </div>
                </div>
            </div>

            <!-- Main content area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                <!-- Page Generator -->
                <div v-show="tab === 'generate'" class="space-y-5">
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 text-white">
                        <h3 class="font-black text-xl mb-1">Generate a Full Page</h3>
                        <p class="text-sm text-white/70">Describe your page and AI will build the layout, copy, and sections instantly.</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
                        <div>
                            <label class="field-label">Describe the page you want</label>
                            <textarea v-model="pagePrompt" rows="4" class="field-input resize-none"
                                placeholder="A modern pricing page for a SaaS product with 3 tiers – Starter, Pro, and Enterprise. Include annual/monthly toggle, FAQ section, and a CTA to book a demo."></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="field-label">Page Type</label>
                                <select v-model="pageType" class="field-input">
                                    <option v-for="t in pageTypes" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Tone</label>
                                <select v-model="tone" class="field-input">
                                    <option>Professional</option><option>Friendly</option><option>Bold</option><option>Minimal</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Target Audience</label>
                                <input v-model="audience" class="field-input" placeholder="B2B SaaS buyers" />
                            </div>
                        </div>
                        <button @click="generatePage" :disabled="generating" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-black hover:shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-60">
                            <i v-if="generating" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-magic"></i>
                            {{ generating ? 'Generating page layout...' : 'Generate Page (3 credits)' }}
                        </button>
                    </div>

                    <!-- Generated result -->
                    <div v-if="generatedPage" class="bg-white rounded-2xl border border-purple-200 p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                                <i class="fas fa-check-circle text-purple-500"></i> Generated Layout
                            </h3>
                            <div class="flex gap-2">
                                <button @click="useLayout" class="px-3 py-1.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700">Use in Builder</button>
                                <button @click="generatedPage = null" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200">Discard</button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(block, i) in generatedPage.blocks" :key="i"
                                class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 border border-purple-100">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0"><i :class="block.icon + ' text-xs'"></i></div>
                                <div class="flex-1">
                                    <p class="text-xs font-black text-gray-800 capitalize">{{ block.type }}</p>
                                    <p class="text-sm text-gray-400 truncate">{{ block.preview }}</p>
                                </div>
                                <span class="text-sm font-bold text-purple-400">Section {{ i+1 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copywriter -->
                <div v-show="tab === 'copy'" class="space-y-5">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
                        <h3 class="font-black text-gray-900 text-sm">AI Copywriter</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Content Type</label>
                                <select v-model="copyType" class="field-input">
                                    <option>Hero Headline + Subheading</option>
                                    <option>Product Description</option>
                                    <option>Email Subject Lines (5)</option>
                                    <option>CTA Button Text (10 variants)</option>
                                    <option>FAQ Section</option>
                                    <option>About Us</option>
                                    <option>Privacy Policy</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">About your product/service</label>
                                <input v-model="copyContext" class="field-input" placeholder="Cloud HR software for SMEs..." />
                            </div>
                        </div>
                        <button @click="generateCopy" :disabled="generatingCopy" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-black hover:shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-60">
                            <i v-if="generatingCopy" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-pen-fancy"></i>
                            {{ generatingCopy ? 'Writing copy...' : 'Write Copy (1 credit)' }}
                        </button>
                        <div v-if="generatedCopy" class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-black text-gray-500 uppercase tracking-wider">Generated Copy</span>
                                <button @click="copyToClipboard(generatedCopy)" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-copy mr-1"></i>Copy
                                </button>
                            </div>
                            <p class="text-sm text-gray-800 whitespace-pre-line leading-relaxed">{{ generatedCopy }}</p>
                        </div>
                    </div>
                </div>

                <!-- SEO Optimizer -->
                <div v-show="tab === 'seo'" class="space-y-5">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
                        <h3 class="font-black text-gray-900 text-sm">AI SEO Optimiser</h3>
                        <div>
                            <label class="field-label">Page URL or Topic</label>
                            <input v-model="seoTopic" class="field-input" placeholder="https://yoursite.com/pricing or 'HR software for startups'" />
                        </div>
                        <button @click="optimiseSeo" :disabled="generatingSeo" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-black flex items-center justify-center gap-2 disabled:opacity-60 hover:shadow-lg transition-all">
                            <i v-if="generatingSeo" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-search"></i>
                            {{ generatingSeo ? 'Analysing...' : 'Optimise SEO (2 credits)' }}
                        </button>
                        <div v-if="seoResult" class="space-y-3">
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                <p class="text-xs font-black text-emerald-700 mb-1">Suggested Meta Title</p>
                                <p class="text-sm font-bold text-emerald-900">{{ seoResult.title }}</p>
                            </div>
                            <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-200">
                                <p class="text-xs font-black text-indigo-700 mb-1">Meta Description</p>
                                <p class="text-xs text-indigo-800">{{ seoResult.description }}</p>
                            </div>
                            <div class="p-3 bg-purple-50 rounded-xl border border-purple-200">
                                <p class="text-xs font-black text-purple-700 mb-2">Focus Keywords</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="kw in seoResult.keywords" :key="kw" class="text-sm font-bold px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full">{{ kw }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Generator tab -->
                <div v-show="tab === 'image'" class="space-y-5">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
                        <h3 class="font-black text-gray-900 text-sm">AI Image Generator</h3>
                        <div><label class="field-label">Describe the image</label>
                            <textarea v-model="imagePrompt" rows="3" class="field-input resize-none" placeholder="A minimalist hero image for a SaaS dashboard product, dark background, glowing blue UI elements, professional..."></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="field-label">Style</label>
                                <select v-model="imageStyle" class="field-input">
                                    <option>Photorealistic</option><option>Illustrative</option><option>3D Render</option><option>Flat Design</option><option>Minimalist</option>
                                </select>
                            </div>
                            <div><label class="field-label">Aspect Ratio</label>
                                <select v-model="imageRatio" class="field-input">
                                    <option>16:9 (Banner)</option><option>1:1 (Square)</option><option>4:3 (Standard)</option><option>9:16 (Portrait)</option>
                                </select>
                            </div>
                        </div>
                        <button @click="generateImage" :disabled="generatingImage" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-black flex items-center justify-center gap-2 disabled:opacity-60 hover:shadow-lg transition-all">
                            <i v-if="generatingImage" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-image"></i>
                            {{ generatingImage ? 'Creating image...' : 'Generate Image (2 credits)' }}
                        </button>
                        <div v-if="generatedImage" class="rounded-xl overflow-hidden border border-gray-200">
                            <img :src="generatedImage" class="w-full object-cover" alt="AI generated" />
                            <div class="p-3 bg-gray-50 flex gap-2">
                                <button class="flex-1 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold">Use in Media Library</button>
                                <button @click="generatedImage = null" class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600">Discard</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const creditsLeft = ref(48);
const tab = ref('generate');

const navItems = [
    { key:'generate', label:'Page Generator', icon:'fas fa-magic'       },
    { key:'copy',     label:'Copywriter',      icon:'fas fa-pen-fancy'   },
    { key:'seo',      label:'SEO Optimiser',   icon:'fas fa-search'      },
    { key:'image',    label:'Image Gen',       icon:'fas fa-image'       },
];

const recent = ref([
    { id:1, label:'Homepage hero copy',   time:'2 min ago'  },
    { id:2, label:'Pricing page layout',  time:'1 hr ago'   },
    { id:3, label:'SEO – /about page',    time:'Yesterday'  },
]);

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
};

const addRecent = (label) => {
    recent.value.unshift({ id: Date.now(), label, time: 'Just now' });
    if (recent.value.length > 5) recent.value.pop();
};

// Page generator
const pagePrompt   = ref('');
const pageType     = ref('Landing Page');
const tone         = ref('Professional');
const audience     = ref('');
const generating   = ref(false);
const generatedPage = ref(null);
const pageTypes = ['Landing Page','Pricing Page','About Us','Contact','Blog Post','Product Detail','FAQ','Portfolio'];

const generatePage = async () => {
    if (!pagePrompt.value) return;
    generating.value = true;
    await new Promise(r => setTimeout(r, 2000));
    generatedPage.value = {
        blocks: [
            { type:'Navbar',          icon:'fas fa-bars',      preview:'Logo, links: Features, Pricing, About, CTA button' },
            { type:'Hero Section',    icon:'fas fa-image',     preview:'"Transform your ' + (audience.value || 'business') + ' workflow" + subheading + CTA' },
            { type:'Features Grid',   icon:'fas fa-th',        preview:'6 feature cards with icons and 2-line descriptions' },
            { type:'Testimonials',    icon:'fas fa-quote-left',preview:'3 customer testimonials with photos and star ratings' },
            { type:'Pricing Table',   icon:'fas fa-tag',       preview:'3-column pricing with feature list and highlight' },
            { type:'FAQ Accordion',   icon:'fas fa-question',  preview:'8 FAQs generated based on your description' },
            { type:'CTA Banner',      icon:'fas fa-bullhorn',  preview:'Bold call-to-action with gradient background' },
            { type:'Footer',          icon:'fas fa-shoe-prints',preview:'Links, social, copyright, newsletter input' },
        ],
    };
    addRecent('Generated ' + pageType.value);
    creditsLeft.value = Math.max(0, creditsLeft.value - 3);
    generating.value = false;
};

const useLayout = () => { alert('Layout sent to Site Builder! Open the builder to customise each section.'); };

// Copywriter
const copyType      = ref('Hero Headline + Subheading');
const copyContext   = ref('');
const generatingCopy = ref(false);
const generatedCopy  = ref('');

const generateCopy = async () => {
    if (!copyContext.value) return;
    generatingCopy.value = true;
    await new Promise(r => setTimeout(r, 1500));
    generatedCopy.value = `🚀 Headline: "Run Your Business Smarter, Not Harder"\n\n✨ Subheading: "${copyContext.value || 'Our platform'} gives you all the tools you need to manage your team, automate workflows, and grow revenue — in one beautifully simple dashboard."\n\n💡 Alt Headline: "Everything your team needs. Nothing they don't."`;
    addRecent('Copied: ' + copyType.value);
    creditsLeft.value = Math.max(0, creditsLeft.value - 1);
    generatingCopy.value = false;
};

// SEO
const seoTopic      = ref('');
const generatingSeo  = ref(false);
const seoResult      = ref(null);

const optimiseSeo = async () => {
    if (!seoTopic.value) return;
    generatingSeo.value = true;
    await new Promise(r => setTimeout(r, 1800));
    seoResult.value = {
        title:       `Best ${seoTopic.value || 'Business Software'} for 2026 – Try Free`,
        description: `Discover the #1 platform for modern businesses. Streamline operations, boost productivity, and save hours every week. Trusted by 10,000+ teams. Start your free trial today.`,
        keywords:    ['business software', 'team management', 'productivity tools', 'free trial', seoTopic.value].filter(Boolean),
    };
    addRecent('SEO: ' + seoTopic.value);
    creditsLeft.value = Math.max(0, creditsLeft.value - 2);
    generatingSeo.value = false;
};

// Image
const imagePrompt    = ref('');
const imageStyle     = ref('Photorealistic');
const imageRatio     = ref('16:9 (Banner)');
const generatingImage = ref(false);
const generatedImage  = ref(null);

const generateImage = async () => {
    if (!imagePrompt.value) return;
    generatingImage.value = true;
    await new Promise(r => setTimeout(r, 2000));
    generatedImage.value = `https://picsum.photos/seed/${Date.now()}/1200/675`;
    addRecent('Image Gen: ' + (imagePrompt.value.substring(0, 20) + '...'));
    creditsLeft.value = Math.max(0, creditsLeft.value - 2);
    generatingImage.value = false;
};
</script>

<style scoped>
.field-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#374151; margin-bottom:.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:.5rem; padding:.5rem .75rem; font-size:.875rem; outline:none; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,.15); }
</style>
