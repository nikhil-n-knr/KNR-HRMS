<template>
    <div class="p-6 max-w-7xl mx-auto space-y-8 font-inter">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Knowledge Vault Management</h1>
                <p class="text-sm font-medium text-slate-500">Curate and publish technical intelligence for client projects.</p>
            </div>
            <button @click="openCategoryModal()" class="px-5 py-2.5 bg-slate-900 text-white rounded-lg text-xs font-bold shadow-sm hover:bg-slate-800 transition-colors flex items-center gap-2">
                <PlusIcon class="w-4 h-4" />
                New Category
            </button>
        </div>

        <!-- Categories List -->
        <div v-if="categories.length === 0" class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-12 text-center">
            <BookOpenIcon class="w-12 h-12 text-slate-300 mx-auto mb-4" />
            <h3 class="text-sm font-bold text-slate-900">No Vault Categories</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Create a category to start building the knowledge base.</p>
            <button @click="openCategoryModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-bold shadow-sm hover:bg-slate-50 transition-colors">Add Category</button>
        </div>

        <div v-else class="space-y-8">
            <div v-for="category in categories" :key="category.id" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Category Header -->
                <div class="bg-slate-50/50 p-5 flex items-center justify-between border-b border-slate-200">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 bg-white rounded-lg border border-slate-200 flex items-center justify-center shadow-sm">
                            <component :is="getIcon(category.icon)" class="w-5 h-5 text-slate-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                {{ category.name }}
                                <span class="px-2 py-0.5 bg-slate-200/50 text-slate-600 rounded text-sm uppercase font-bold tracking-widest">{{ category.project?.name || 'Global' }}</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ category.articles.length }} Blueprint(s) Published</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button @click="openArticleModal(category.id)" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-md text-xs font-bold hover:bg-indigo-100 transition-colors flex items-center gap-1">
                            <DocumentTextIcon class="w-3.5 h-3.5" />
                            Add Article
                        </button>
                        <button @click="openCategoryModal(category)" class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors rounded-md hover:bg-slate-100">
                            <PencilSquareIcon class="w-4 h-4" />
                        </button>
                        <button @click="deleteCategory(category.id)" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors rounded-md hover:bg-slate-100">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Articles Table -->
                <div v-if="category.articles.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100/50 text-sm font-bold text-slate-400 uppercase tracking-widest bg-white">
                                <th class="py-3 px-5 font-medium">Title</th>
                                <th class="py-3 px-5 font-medium w-32">Status</th>
                                <th class="py-3 px-5 font-medium w-24">Order</th>
                                <th class="py-3 px-5 font-medium w-20 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr v-for="article in category.articles" :key="article.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors group">
                                <td class="py-3 px-5">
                                    <p class="font-semibold text-slate-900 truncate max-w-[400px]">{{ article.title }}</p>
                                    <p class="text-xs text-slate-500 truncate max-w-[400px] mt-0.5">{{ article.excerpt }}</p>
                                </td>
                                <td class="py-3 px-5">
                                    <div class="flex gap-2">
                                        <span v-if="article.is_client_visible" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-sm font-bold uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                            <EyeIcon class="w-3 h-3" /> Visible
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-sm font-bold uppercase tracking-widest bg-slate-100 text-slate-500 border border-slate-200">
                                            <EyeSlashIcon class="w-3 h-3" /> Draft
                                        </span>
                                        <span v-if="article.is_featured" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-sm font-bold uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200/50" title="Featured on Dashboard">
                                            <StarIcon class="w-3 h-3" />
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-5 text-slate-500 font-medium">{{ article.order }}</td>
                                <td class="py-3 px-5 text-right opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openArticleModal(category.id, article)" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors">
                                        <PencilSquareIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteArticle(article.id)" class="p-1 text-slate-400 hover:text-rose-600 transition-colors ml-1">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-8 px-5 text-center text-sm text-slate-500 font-medium">
                    No articles in this category yet.
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <Dialog :open="isCategoryModalOpen" @close="closeCategoryModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" aria-hidden="true" />
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <DialogTitle class="text-lg font-bold text-slate-900">{{ formCategory.id ? 'Edit Category' : 'New Category' }}</DialogTitle>
                        <button @click="closeCategoryModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCategory" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Target Project</label>
                            <select v-model="formCategory.project_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900" required>
                                <option value="" disabled>Select Project</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Category Name</label>
                            <input v-model="formCategory.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900" placeholder="e.g. API Documentation" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">HeroIcon Name</label>
                                <input v-model="formCategory.icon" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900 placeholder-slate-300" placeholder="e.g. DocumentTextIcon" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Sort Order</label>
                                <input v-model="formCategory.order" type="number" min="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900" required />
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="closeCategoryModal" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Cancel</button>
                            <button type="submit" :disabled="formCategory.processing" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-50">
                                {{ formCategory.id ? 'Save Changes' : 'Create Category' }}
                            </button>
                        </div>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>

        <!-- Article Modal -->
        <Dialog :open="isArticleModalOpen" @close="closeArticleModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" aria-hidden="true" />
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 shadow-xl border border-slate-100 custom-scrollbar">
                    <div class="flex items-center justify-between mb-8">
                        <DialogTitle class="text-xl font-black text-slate-900 tracking-tight">{{ formArticle.id ? 'Edit Blueprint' : 'Author New Blueprint' }}</DialogTitle>
                        <button @click="closeArticleModal" class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-all">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitArticle" class="space-y-6">
                        <div class="grid grid-cols-3 gap-6">
                            <div class="col-span-2 space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Title</label>
                                    <input v-model="formArticle.title" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-semibold text-slate-900" placeholder="Technical Title..." required />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Excerpt Summary</label>
                                    <textarea v-model="formArticle.excerpt" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900 resize-none custom-scrollbar" placeholder="Brief description..."></textarea>
                                </div>
                            </div>
                            <div class="col-span-1 space-y-6 bg-slate-50 p-5 rounded-xl border border-slate-200">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Status</label>
                                    <select v-model="formArticle.is_client_visible" class="w-full bg-white border border-slate-200 rounded-lg text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900">
                                        <option :value="true">Published (Client Visible)</option>
                                        <option :value="false">Draft (Internal Only)</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" id="is_featured" v-model="formArticle.is_featured" class="w-4 h-4 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500">
                                    <label for="is_featured" class="text-sm font-bold text-slate-700 cursor-pointer">Feature on Dashboard</label>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Sort Order</label>
                                    <input v-model="formArticle.order" type="number" min="0" class="w-full bg-white border border-slate-200 rounded-lg text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-900" required />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-1">Content Body (HTML)</label>
                            <!-- Minimal WYSIWYG or Textarea for now because of environment limitations, using textarea -->
                            <textarea v-model="formArticle.content" rows="12" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm p-5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-900 font-mono custom-scrollbar" placeholder="<h2>Start writing...</h2>" required></textarea>
                            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-2">Supports full HTML layout</p>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                            <button type="button" @click="closeArticleModal" class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Cancel</button>
                            <button type="submit" :disabled="formArticle.processing" class="px-8 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-lg shadow-md hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-50">
                                {{ formArticle.id ? 'Save Content' : 'Publish Blueprint' }}
                            </button>
                        </div>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>

    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { 
    PlusIcon, 
    BookOpenIcon, 
    PencilSquareIcon, 
    TrashIcon,
    DocumentTextIcon,
    XMarkIcon,
    EyeIcon,
    EyeSlashIcon,
    StarIcon,
    CpuChipIcon,
    SparklesIcon,
    ArchiveBoxIcon,
    FolderIcon
} from '@heroicons/vue/24/outline';
import * as HeroIcons from '@heroicons/vue/24/outline'; // For dynamic loading

const props = defineProps(['categories', 'projects']);

// Icon Mapper
const getIcon = (iconName) => {
    return iconName && HeroIcons[iconName] ? HeroIcons[iconName] : FolderIcon;
};

// --- Category State ---
const isCategoryModalOpen = ref(false);
const formCategory = useForm({
    id: null,
    project_id: '',
    name: '',
    icon: '',
    order: 0
});

const openCategoryModal = (category = null) => {
    if (category) {
        formCategory.id = category.id;
        formCategory.project_id = category.project_id;
        formCategory.name = category.name;
        formCategory.icon = category.icon;
        formCategory.order = category.order;
    } else {
        formCategory.reset();
        formCategory.order = props.categories.length; // Default to end
    }
    isCategoryModalOpen.value = true;
};

const closeCategoryModal = () => {
    isCategoryModalOpen.value = false;
    formCategory.reset();
};

const submitCategory = () => {
    if (formCategory.id) {
        formCategory.put(route('admin.vault.categories.update', formCategory.id), {
            onSuccess: () => closeCategoryModal()
        });
    } else {
        formCategory.post(route('admin.vault.categories.store'), {
            onSuccess: () => closeCategoryModal()
        });
    }
};

const deleteCategory = (id) => {
    if(confirm('Are you sure you want to delete this category and ALL its articles? This is destructive.')) {
        router.delete(route('admin.vault.categories.destroy', id), {
            preserveScroll: true
        });
    }
};

// --- Article State ---
const isArticleModalOpen = ref(false);
const formArticle = useForm({
    id: null,
    category_id: null,
    title: '',
    excerpt: '',
    content: '',
    is_featured: false,
    is_client_visible: true,
    order: 0
});

const openArticleModal = (categoryId, article = null) => {
    if (article) {
        formArticle.id = article.id;
        formArticle.category_id = article.category_id;
        formArticle.title = article.title;
        formArticle.excerpt = article.excerpt || '';
        formArticle.content = article.content || '';
        formArticle.is_featured = article.is_featured;
        formArticle.is_client_visible = article.is_client_visible;
        formArticle.order = article.order;
    } else {
        formArticle.reset();
        formArticle.category_id = categoryId;
        const cat = props.categories.find(c => c.id === categoryId);
        formArticle.order = cat ? cat.articles.length : 0;
    }
    isArticleModalOpen.value = true;
};

const closeArticleModal = () => {
    isArticleModalOpen.value = false;
    formArticle.reset();
};

const submitArticle = () => {
    if (formArticle.id) {
        formArticle.put(route('admin.vault.articles.update', formArticle.id), {
            onSuccess: () => closeArticleModal()
        });
    } else {
        formArticle.post(route('admin.vault.articles.store'), {
            onSuccess: () => closeArticleModal()
        });
    }
};

const deleteArticle = (id) => {
    if(confirm('Delete this blueprint?')) {
        router.delete(route('admin.vault.articles.destroy', id), {
            preserveScroll: true
        });
    }
};

</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
