<template>
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-10 pointer-events-none">
        <!-- Backdrop -->
        <transition name="fade">
            <div v-if="show" class="absolute inset-0 bg-slate-900/60 backdrop-blur-3xl pointer-events-auto" @click="$emit('close')"></div>
        </transition>

        <!-- Main Vault Panel -->
        <transition name="pop-in">
            <div v-if="show" class="bg-white w-full max-w-6xl h-full max-h-[90vh] rounded-[4rem] shadow-[0_40px_100px_rgba(0,0,0,0.2)] border border-white/50 relative overflow-hidden flex flex-col pointer-events-auto">
                
                <!-- Luxury Header -->
                <div class="p-12 pb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-8 relative overflow-hidden">
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                                <BookOpenIcon class="h-5 w-5 text-white" />
                            </div>
                            <span class="text-base font-black text-indigo-500 uppercase tracking-[0.3em]">Operational Intelligence</span>
                        </div>
                        <h2 class="text-5xl font-black text-slate-900 tracking-tighter">Knowledge Vault</h2>
                    </div>

                    <div class="flex items-center gap-4 relative z-10 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-64">
                            <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Search blueprints..." 
                                class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-12 pr-6 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all"
                            />
                        </div>
                        <button @click="$emit('close')" class="h-14 w-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-200 transition-all hover:rotate-90">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Flowing Glows -->
                    <div class="absolute -top-20 -right-20 h-64 w-64 bg-indigo-50 blur-[80px] rounded-full pointer-events-none"></div>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto px-12 pb-12 custom-scrollbar">
                    
                    <!-- Featured Section -->
                    <div v-if="featured.length > 0 && !searchQuery" class="mb-16">
                        <div class="flex items-center gap-4 mb-8">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Featured Signals</h3>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div v-for="article in featured" :key="article.id" 
                                 @click="openArticle(article)"
                                 class="bg-indigo-900 p-8 rounded-[2.5rem] shadow-2xl shadow-indigo-950/20 relative overflow-hidden group cursor-pointer hover:translate-y-[-4px] transition-all">
                                <div class="relative z-10 space-y-6 h-full flex flex-col">
                                    <div class="p-3 bg-white/10 rounded-xl w-fit">
                                        <SparklesIcon class="h-5 w-5 text-indigo-300" />
                                    </div>
                                    <h4 class="text-2xl font-black text-white leading-tight flex-1">{{ article.title }}</h4>
                                    <div class="flex items-center justify-between mt-4">
                                        <span class="text-sm font-black text-indigo-300 uppercase tracking-widest">{{ article.category?.name }}</span>
                                        <ArrowRightIcon class="h-5 w-5 text-white/40 group-hover:text-white group-hover:translate-x-1 transition-all" />
                                    </div>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent pointer-events-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Explorer -->
                    <div class="space-y-16">
                        <div v-for="category in categories" :key="category.id" class="space-y-8">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-black text-slate-900 uppercase tracking-[0.25em]">{{ category.name }}</span>
                                <div class="h-px bg-slate-100 flex-1"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div v-for="article in category.articles" :key="article.id" 
                                     @click="openArticle(article)"
                                     class="bg-white border border-slate-100 p-6 rounded-[2rem] hover:shadow-xl hover:shadow-slate-200/50 transition-all cursor-pointer group">
                                    <div class="space-y-4">
                                        <div class="h-10 w-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all">
                                            <component :is="getIcon(article)" class="h-5 w-5" />
                                        </div>
                                        <h5 class="font-black text-slate-900 leading-snug group-hover:text-indigo-600 transition-all">{{ article.title }}</h5>
                                        <p class="text-xs text-slate-400 line-clamp-2 font-medium">{{ article.excerpt }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Empty State -->
                    <div v-if="searchQuery && filteredCount === 0" class="py-20 text-center space-y-6">
                        <div class="h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto">
                            <DocumentMagnifyingGlassIcon class="h-10 w-10 text-slate-300" />
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-2xl font-black tracking-tighter">No blueprints found</h3>
                            <p class="text-slate-400 font-medium">Try different keywords or browse categories.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer / Support Sign-off -->
                <div class="p-10 border-t border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-6">
                    <p class="text-base font-bold text-slate-400 flex items-center gap-2">
                        <ShieldCheckIcon class="h-4 w-4 text-emerald-500" />
                        Classified Intelligence Hub &copy; {{ new Date().getFullYear() }}
                    </p>
                    <button class="px-6 py-3 bg-slate-50 text-slate-900 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Request Specific Guide
                    </button>
                </div>

                <!-- Article Modal (Nested or Separate? Let's do Nested for now for speed) -->
                <transition name="slide-up">
                    <div v-if="selectedArticle" class="absolute inset-0 bg-white z-[210] flex flex-col custom-scrollbar">
                        <div class="sticky top-0 bg-white/80 backdrop-blur-3xl border-b border-slate-50 p-8 flex items-center justify-between z-20">
                            <button @click="selectedArticle = null" class="flex items-center gap-2 text-slate-400 hover:text-slate-900 font-black text-sm uppercase tracking-widest transition-all">
                                <ArrowLeftIcon class="h-4 w-4" />
                                Back to Explorer
                            </button>
                            <div class="flex items-center gap-3">
                                <button class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-all">
                                    <ShareIcon class="h-4 w-4" />
                                </button>
                                <button @click="selectedArticle = null" class="h-10 w-10 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 max-w-4xl mx-auto w-full px-8 py-20 space-y-12">
                            <div class="space-y-6">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-black uppercase tracking-widest">{{ selectedArticle.category?.name }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">{{ formatDate(selectedArticle.created_at) }}</span>
                                </div>
                                <h1 class="text-6xl font-black text-slate-900 tracking-tighter leading-[0.9]">{{ selectedArticle.title }}</h1>
                                <p class="text-xl text-slate-500 font-medium leading-relaxed">{{ selectedArticle.excerpt }}</p>
                            </div>

                            <div class="h-px bg-slate-100"></div>

                            <div class="prose prose-slate max-w-none prose-headings:font-black prose-headings:tracking-tighter prose-p:text-slate-600 prose-p:leading-relaxed prose-p:text-lg prose-strong:text-slate-900" 
                                 v-html="selectedArticle.content">
                            </div>

                            <div class="bg-slate-50 rounded-[3rem] p-12 mt-20 flex flex-col items-center text-center space-y-6">
                                <div class="h-16 w-16 bg-white rounded-2xl flex items-center justify-center shadow-lg shadow-slate-200">
                                    <QuestionMarkCircleIcon class="h-8 w-8 text-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <h4 class="text-2xl font-black tracking-tighter">Need deeper clarification?</h4>
                                    <p class="text-slate-400 font-medium">Our technical leads are available via the broadcast terminal.</p>
                                </div>
                                <button @click="$emit('close'); selectedArticle = null" class="px-10 py-4 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-2xl">Create Session</button>
                            </div>
                        </div>
                    </div>
                </transition>

            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
    XMarkIcon, 
    BookOpenIcon, 
    MagnifyingGlassIcon,
    SparklesIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    DocumentTextIcon,
    ShieldCheckIcon,
    ShareIcon,
    DocumentMagnifyingGlassIcon,
    QuestionMarkCircleIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import dayjs from 'dayjs';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close']);

const categories = ref([]);
const featured = ref([]);
const searchQuery = ref('');
const selectedArticle = ref(null);
const loading = ref(true);

const fetchVault = async () => {
    try {
        const { data } = await axios.get(route('portal.vault'));
        categories.value = data.categories;
        featured.value = data.featured;
    } catch (e) {
        console.error("Vault retrieval offline", e);
    } finally {
        loading.value = false;
    }
};

const openArticle = async (article) => {
    try {
        const { data } = await axios.get(route('portal.vault.article', article.slug));
        selectedArticle.value = data;
    } catch (e) {
        console.error("Signal lost", e);
    }
};

const getIcon = (article) => {
    // Logic to return different icons based on content type or title
    return DocumentTextIcon;
};

const formatDate = (date) => {
    return dayjs(date).format('MMMM DD, YYYY');
};

const filteredCount = computed(() => {
    if (!searchQuery.value) return 1; // Not used then
    let count = 0;
    categories.value.forEach(cat => {
        cat.articles.forEach(art => {
            if (art.title.toLowerCase().includes(searchQuery.value.toLowerCase())) count++;
        })
    });
    return count;
});

onMounted(fetchVault);
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.pop-in-enter-active, .pop-in-leave-active { transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); }
.pop-in-enter-from, .pop-in-leave-to { opacity: 0; transform: scale(0.9) translateY(20px); }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
