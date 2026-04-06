<template>
    <div class="space-y-8 text-left">
        <!-- Intelligence Command -->
        <div class="flex justify-between items-end pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Intelligence Base</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-indigo-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Global Self-Service Framework</p>
                </div>
            </div>
            <div class="flex gap-4">
                <button @click="showCategoryModal = true" class="px-8 py-3 bg-white text-indigo-600 border border-indigo-100 rounded-2xl hover:bg-indigo-50 transition-all font-black text-sm shadow-sm flex items-center group">
                    <i class="fas fa-folder-plus mr-2 text-xs group-hover:scale-110 transition-transform"></i>
                    NEW CATEGORY
                </button>
                <button @click="showArticleModal = true" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm shadow-xl shadow-indigo-100 flex items-center group">
                    <i class="fas fa-plus mr-2 text-xs group-hover:rotate-12 transition-transform"></i>
                    WRITE ARTICLE
                </button>
            </div>
        </div>

        <!-- Categories Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="category in localCategories" :key="category.id" 
                 class="bg-white p-10 rounded-[45px] shadow-sm border border-gray-100 hover:shadow-2xl transition-all cursor-pointer group relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-50 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-700"></div>
                
                <div class="relative">
                    <div class="flex justify-between items-start mb-10">
                        <div class="w-16 h-16 bg-indigo-600 text-white rounded-3xl flex items-center justify-center shadow-lg shadow-indigo-100 group-hover:rotate-6 transition-transform">
                            <i class="fas fa-book-reader text-2xl"></i>
                        </div>
                        <span class="bg-gray-50 text-gray-400 text-sm font-black px-4 py-2 rounded-xl uppercase tracking-widest border border-gray-100">
                            {{ category.articles_count || 0 }} Blueprints
                        </span>
                    </div>
                    
                    <h3 class="font-black text-2xl text-gray-900 mb-3 tracking-tight group-hover:text-indigo-600 transition-colors">{{ category.name }}</h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed mb-10 line-clamp-2">
                        {{ category.description || 'Explore optimized documentation and strategic guides for ' + category.name + '.' }}
                    </p>
                    
                    <div class="flex items-center justify-between pt-6 border-t border-gray-50">
                        <span class="text-xs font-black text-indigo-600 uppercase tracking-widest group-hover:translate-x-2 transition-transform">Access Intel</span>
                        <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create New Placeholder -->
            <button @click="showCategoryModal = true" class="bg-gray-50/50 border-2 border-dashed border-gray-200 rounded-[45px] p-10 flex flex-col items-center justify-center text-gray-400 hover:border-indigo-500 hover:text-indigo-600 transition-all min-h-[350px] group/new">
                <div class="w-20 h-20 rounded-[28px] bg-white shadow-sm border border-gray-100 flex items-center justify-center mb-6 group-hover/new:scale-110 transition-transform">
                    <i class="fas fa-layer-group text-3xl"></i>
                </div>
                <span class="text-lg font-black tracking-tight">Expand Intel</span>
                <span class="text-sm font-bold uppercase tracking-widest mt-2 opacity-60">Add Knowledge Node</span>
            </button>
        </div>

        <!-- CATEGORY MODAL -->
        <div v-if="showCategoryModal" @click.self="showCategoryModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
             <div class="relative bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white text-left p-10">
                 <div class="flex justify-between items-start mb-8">
                     <div class="w-16 h-16 bg-indigo-600 text-white rounded-3xl flex items-center justify-center shadow-lg shadow-indigo-100">
                         <i class="fas fa-folder-plus text-2xl"></i>
                     </div>
                     <button @click="showCategoryModal = false" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-gray-900 flex items-center justify-center transition-all hover:rotate-90">
                         <i class="fas fa-times"></i>
                     </button>
                 </div>
                 <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Knowledge Cluster</h3>
                 <p class="text-sm text-gray-500 font-medium leading-relaxed mb-10">Define a new category to organize support blueprints and intelligence nodes.</p>
                 
                 <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1">Cluster Identity</label>
                        <input v-model="categoryForm.name" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-5 text-sm font-semibold shadow-inner" placeholder="e.g. Technical Engineering">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1">Cluster Description</label>
                        <textarea v-model="categoryForm.description" rows="3" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-5 text-sm font-semibold shadow-inner" placeholder="Describe this intelligence node..."></textarea>
                    </div>
                    <button @click="createCategory" :disabled="loading" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all disabled:opacity-50">
                        <span v-if="loading">INITIALIZING...</span>
                        <span v-else>INITIALIZE CLUSTER</span>
                    </button>
                 </div>
             </div>
        </div>

        <!-- ARTICLE MODAL -->
        <div v-if="showArticleModal" @click.self="showArticleModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
             <div class="relative bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white text-left">
                 <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg shadow-indigo-100">
                            <i class="fas fa-feather-alt"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight">Intelligence Node</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Strategic Blueprint Composer</p>
                        </div>
                    </div>
                    <button @click="showArticleModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                 </div>

                 <div class="p-10 space-y-8">
                     <div class="grid grid-cols-2 gap-8">
                         <div class="space-y-2">
                             <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1">Node Title</label>
                             <input v-model="articleForm.title" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-5 text-sm font-semibold shadow-inner" placeholder="e.g. Resolving SMTP Latency">
                         </div>
                         <div class="space-y-2">
                             <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1">Cluster Assignment</label>
                             <select v-model="articleForm.category_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-5 text-sm font-bold shadow-inner">
                                 <option value="" disabled>Select Cluster</option>
                                 <option v-for="c in localCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                             </select>
                         </div>
                     </div>
                      <div class="space-y-2">
                         <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1">Raw Intel / Body</label>
                         <textarea v-model="articleForm.content" rows="6" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all py-4 px-5 text-sm font-semibold shadow-inner" placeholder="Compose high-impact resolution logic..."></textarea>
                      </div>
                      <button @click="createArticle" :disabled="loading" class="w-full bg-indigo-600 text-white py-4 rounded-3xl font-black text-sm shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all disabled:opacity-50">
                         <span v-if="loading">PUBLISHING...</span>
                         <span v-else>PUBLISH TO HUB</span>
                      </button>
                 </div>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] }
});

const showCategoryModal = ref(false);
const showArticleModal = ref(false);
const loading = ref(false);

const localCategories = ref([...props.categories]);

// Category Form
const categoryForm = ref({
    name: '',
    description: ''
});

// Article Form
const articleForm = ref({
    title: '',
    category_id: '',
    content: '',
    status: 'published'
});

const fetchCategories = async () => {
    try {
        const response = await axios.get(route('crm.kb.categories'));
        localCategories.value = response.data;
    } catch (error) {
        console.error('Failed to fetch categories', error);
    }
};

const createCategory = async () => {
    if (!categoryForm.value.name) return;
    loading.value = true;
    try {
        await axios.post(route('crm.kb.categories.store'), categoryForm.value);
        categoryForm.value = { name: '', description: '' };
        showCategoryModal.value = false;
        await fetchCategories();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to create category');
    } finally {
        loading.value = false;
    }
};

const createArticle = async () => {
    if (!articleForm.value.title || !articleForm.value.category_id || !articleForm.value.content) {
        alert('Please fill all required fields');
        return;
    }
    loading.value = true;
    try {
        await axios.post(route('crm.kb.articles.store'), articleForm.value);
        articleForm.value = { title: '', category_id: '', content: '', status: 'published' };
        showArticleModal.value = false;
        await fetchCategories(); // Refresh to update article counts
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to create article');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (!props.categories.length) {
        fetchCategories();
    }
});
</script>
