<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Product Intelligence Matrix</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left">Your product catalog is the foundation of every transaction. Structure your offerings across physical, digital, and service layers with dynamic pricing and hierarchy control.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Tiered Product Hierarchy</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Baseline & Cost Optimization</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Product Catalog</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                            <i class="fas fa-barcode text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">{{ products.total || products.length || 0 }} global SKUs indexed</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                         <button @click="openImportModal" class="px-7 py-3.5 bg-gray-100 text-gray-600 rounded-2xl hover:bg-gray-200 transition-all font-black text-sm tracking-widest uppercase flex items-center group active:scale-95 text-left font-sans border border-gray-200">
                            <i class="fas fa-file-import mr-2 text-xs group-hover:translate-y-[-2px] transition-transform text-left"></i>
                            IMPORT DATA
                        </button>
                        <button @click="openCreateModal" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans">
                            <i class="fas fa-plus mr-2 text-xs group-hover:scale-125 transition-transform text-left"></i>
                            Define New Product
                        </button>
                        <button class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-file-excel text-sm text-left font-sans"></i>
                        </button>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Toolbar -->
            <div class="px-8 py-5 border-b border-gray-200 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-40 no-print text-left font-sans">
                <div class="flex items-center gap-4 flex-1 w-full max-w-4xl text-left font-sans">
                    <div class="relative w-full max-w-md group text-left font-sans">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans"></i>
                        <input 
                            v-model="search" 
                            @input="debouncedSearch"
                            type="text" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans" 
                            placeholder="Search by SKU, Name or Category..."
                        >
                    </div>

                    <!-- Category Hierarchy Dropdown -->
                    <div class="relative text-left font-sans" @click.away="showCategoryDropdown = false">
                        <button @click="showCategoryDropdown = !showCategoryDropdown" class="px-5 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center gap-3 group shadow-inner text-left font-sans">
                            <i class="fas fa-layer-group text-gray-300 group-hover:text-indigo-500 text-left font-sans"></i>
                            {{ selectedCategoryName || 'FILTER BY TAXONOMY' }}
                            <i class="fas fa-chevron-down text-xs opacity-40 text-left font-sans"></i>
                        </button>
                        
                        <div v-if="showCategoryDropdown" class="absolute left-0 top-14 w-80 bg-white rounded-[32px] shadow-2xl border border-gray-100 p-6 z-50 text-left font-sans animate-in slide-in-from-top-2 duration-200">
                             <div class="max-h-[400px] overflow-y-auto custom-scrollbar text-left font-sans">
                                 <CategoryItem 
                                     v-for="cat in categories" 
                                     :key="cat.id" 
                                     :category="cat" 
                                     :active-id="selectedCategoryId"
                                     @select="selectCategory" 
                                 />
                                 <div v-if="!categories.length" class="p-8 text-center text-left font-sans">
                                     <p class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans">No taxonomies defined.</p>
                                 </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 text-left font-sans">
                    <div v-for="product in (products.data || products)" :key="product.id" 
                         @click="editProduct(product)"
                         class="bg-white rounded-[40px] border border-gray-100 p-8 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all cursor-pointer group flex flex-col min-h-[460px] text-left relative overflow-hidden text-left font-sans">
                        
                        <!-- Header: SKU & Type -->
                        <div class="flex justify-between items-start mb-6 text-left font-sans">
                            <span class="text-sm font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 shadow-sm text-left uppercase tracking-widest text-left font-sans">{{ product.sku }}</span>
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-gray-900 group-hover:text-white transition-all text-left font-sans">
                                <i :class="[getTypeIcon(product.type), 'text-xs text-left font-sans']"></i>
                            </div>
                        </div>

                        <!-- Product Content -->
                        <div class="flex-1 text-left font-sans">
                            <h3 class="text-xl font-black text-gray-900 leading-tight mb-2 group-hover:text-indigo-600 transition-colors text-left font-sans">{{ product.name }}</h3>
                            <p class="text-base font-bold text-gray-400 line-clamp-3 mb-6 text-left font-sans">{{ product.description || 'Global entity definition with specialized logic units.' }}</p>
                            
                            <div class="flex flex-wrap gap-2 text-left font-sans">
                                <span class="px-3 py-1 bg-gray-50 text-sm font-black text-gray-400 uppercase tracking-widest rounded-lg border border-gray-100 text-left font-sans">{{ product.category?.name || 'Artifact' }}</span>
                            </div>
                        </div>

                        <!-- Pricing Matrix -->
                        <div class="pt-8 mt-6 border-t border-gray-50 text-left font-sans">
                            <div class="grid grid-cols-2 gap-4 text-left font-sans">
                                <div class="text-left font-sans">
                                    <span class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-1 text-left font-sans">Base Price</span>
                                    <span class="text-lg font-black text-gray-900 text-left font-sans">{{ formatCurrency(product.base_price) }}</span>
                                </div>
                                <div class="text-right text-left font-sans">
                                    <span class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-1 text-left font-sans">Stock Level</span>
                                    <div class="flex items-center justify-end gap-2 text-left font-sans">
                                        <span :class="['text-lg font-black text-left font-sans', product.stock_qty < product.reorder_point ? 'text-rose-600' : 'text-emerald-600']">{{ product.stock_qty }}</span>
                                        <div :class="['w-2 h-2 rounded-full shadow-lg text-left font-sans', product.stock_qty < product.reorder_point ? 'bg-rose-500 shadow-rose-500/50 animate-pulse' : 'bg-emerald-500 shadow-emerald-500/50']"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!(products.data || products).length" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans">
                    <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left font-sans">
                         <i class="fas fa-barcode text-3xl opacity-20 text-left font-sans"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans">Inventory Void</h3>
                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans">No products match your current taxonomy or search criteria.</p>
                </div>

                <!-- Hierarchy Strategy Map (Bottom Sticky / Section) -->
                <div class="mt-20 text-left font-sans">
                    <div class="flex items-center gap-4 mb-8 text-left font-sans">
                        <div class="h-[1px] flex-1 bg-gray-200 text-left font-sans"></div>
                        <h4 class="text-base font-black text-gray-400 uppercase tracking-[0.3em] text-left font-sans">Taxonomy Architecture</h4>
                        <div class="h-[1px] flex-1 bg-gray-200 text-left font-sans"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6 text-left font-sans">
                        <div v-for="cat in categories" :key="cat.id" class="bg-indigo-900 text-white p-6 rounded-[35px] shadow-xl hover:shadow-2xl transition-all text-left font-sans group">
                             <div class="flex items-center gap-3 mb-4 text-left font-sans">
                                 <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-indigo-400 text-left font-sans group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                     <i :class="[cat.icon || 'fa-folder', 'fas text-xs text-left font-sans font-sans']"></i>
                                 </div>
                                 <div class="text-left font-sans">
                                     <h5 class="text-xs font-black text-white tracking-tight text-left font-sans truncate w-24">{{ cat.name }}</h5>
                                     <p class="text-xs font-bold text-indigo-400 uppercase text-left font-sans">{{ (cat.children?.length || 0) }} SUB-TYPES</p>
                                 </div>
                             </div>
                             <div v-if="cat.children && cat.children.length > 0" class="space-y-2 pl-4 border-l border-white/10 text-left font-sans">
                                 <div v-for="child in cat.children.slice(0, 3)" :key="child.id" class="text-sm font-medium text-indigo-200 hover:text-white cursor-pointer text-left font-sans">
                                     {{ child.name }}
                                 </div>
                                 <div v-if="cat.children.length > 3" class="text-xs font-black text-indigo-500 hover:underline uppercase text-left font-sans cursor-pointer">+ {{ cat.children.length - 3 }} MORE</div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE/EDIT MODAL -->
        <div v-if="showModal" @click.self="showModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 text-left font-sans">
             <div class="relative bg-white rounded-[45px] shadow-2xl max-w-5xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left font-sans">
                    <div class="flex items-center text-left font-sans">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left font-sans">
                            <i class="fas fa-barcode text-xl text-left font-sans"></i>
                        </div>
                        <div class="text-left font-sans">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans">{{ editingProduct ? 'Modify Definition' : 'Define Product' }}</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left font-sans font-sans">Global Registry Console</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-200 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90 text-left font-sans">
                        <i class="fas fa-times text-left font-sans text-left font-sans"></i>
                    </button>
                </div>

                <div class="grid grid-cols-12 h-[600px] text-left font-sans">
                    <div class="col-span-8 overflow-y-auto p-10 space-y-8 border-r border-gray-100 text-left font-sans custom-scrollbar">
                        <div class="grid grid-cols-2 gap-8 text-left font-sans">
                            <div class="space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Primary Label</label>
                                <input v-model="form.name" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 px-6 font-semibold shadow-inner text-left font-sans" placeholder="e.g. AI-Powered Enterprise Server">
                            </div>
                            <div class="space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">SKU / Identifier</label>
                                <input v-model="form.sku" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 px-6 font-bold shadow-inner text-left font-sans" placeholder="PRO-X100">
                            </div>
                            <div class="col-span-2 space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Strategic Definition</label>
                                <textarea v-model="form.description" rows="3" class="w-full bg-gray-50 border-gray-100 rounded-[28px] focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 px-6 font-semibold shadow-inner text-left font-sans" placeholder="Comprehensive product brief..."></textarea>
                            </div>

                            <div class="space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Baseline Ledger (Price)</label>
                                <div class="relative text-left font-sans font-sans">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-left font-sans">$</span>
                                    <input v-model="form.base_price" type="number" step="0.01" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 pl-10 pr-6 font-black shadow-inner text-left font-sans">
                                </div>
                            </div>
                            <div class="space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Acquisition Cost</label>
                                <div class="relative text-left font-sans font-sans">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-left font-sans">$</span>
                                    <input v-model="form.cost_price" type="number" step="0.01" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 pl-10 pr-6 font-black shadow-inner text-left font-sans">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-4 bg-gray-50/50 p-10 flex flex-col text-left font-sans">
                        <div class="flex-1 space-y-10 text-left font-sans">
                            <div class="space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Classification</label>
                                <select v-model="form.category_id" class="w-full bg-white border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 py-4.5 px-6 text-sm font-black shadow-sm text-left cursor-pointer appearance-none">
                                    <option :value="null">UNCATEGORIZED</option>
                                    <optgroup v-for="cat in categories" :label="cat.name" :key="cat.id" class="text-left font-sans">
                                        <option :value="cat.id">{{ cat.name }}</option>
                                        <option v-for="child in cat.children" :value="child.id" :key="child.id">-- {{ child.name }}</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="space-y-3 text-left font-sans">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Artifact Type</label>
                                <div class="grid grid-cols-1 gap-2 text-left font-sans">
                                    <button v-for="t in ['physical', 'service', 'digital']" :key="t" @click="form.type = t" type="button" 
                                            :class="['px-6 py-4 rounded-2xl text-sm font-black uppercase tracking-widest transition-all text-left flex items-center justify-between', form.type === t ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100 scale-[1.02]' : 'bg-white text-gray-400 border border-gray-100 hover:border-indigo-200']">
                                        {{ t }}
                                        <i :class="[getTypeIcon(t), 'ml-2 text-left font-sans']"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-10 text-left font-sans">
                            <button @click="submit" :disabled="form.processing" 
                                    class="w-full bg-gray-900 text-white py-5 rounded-[28px] font-black text-base tracking-widest uppercase shadow-2xl hover:bg-indigo-600 transition-all flex items-center justify-center active:scale-95 disabled:opacity-50 text-left font-sans">
                                <i class="fas fa-save mr-3 text-left font-sans"></i>
                                {{ form.processing ? 'SYNCHRONIZING...' : (editingProduct ? 'UPDATE REGISTRY' : 'COMMIT REGISTRY') }}
                            </button>
                        </div>
                    </div>
                </div>
             </div>
        </div>

        <!-- IMPORT MODAL -->
        <div v-if="showImportModal" @click.self="showImportModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 text-left font-sans">
            <div class="bg-white rounded-[45px] shadow-2xl max-w-md w-full p-12 text-center text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-inner text-left font-sans">
                    <i class="fas fa-file-excel text-2xl text-left font-sans"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight text-left font-sans">Batch Registry Import</h3>
                <p class="text-xs text-gray-500 font-medium leading-relaxed mb-10 text-left font-sans">Upload your product taxonomy in CSV/Excel format to synchronize your global inventory matrix.</p>
                
                <div class="space-y-6 text-left font-sans">
                    <div @click="$refs.fileInput.click()" class="border-3 border-dashed border-gray-100 rounded-[35px] p-10 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all cursor-pointer group text-left font-sans">
                        <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-200 group-hover:text-indigo-500 transition-colors mb-4 text-left font-sans"></i>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans group-hover:text-indigo-900 transition-colors">{{ importForm.file ? importForm.file.name : 'Select Master File' }}</p>
                    </div>
                    
                    <button @click="submitImport" :disabled="importForm.processing || !importForm.file" 
                            class="w-full bg-indigo-600 text-white py-5 rounded-[28px] font-black text-base tracking-widest uppercase hover:bg-indigo-700 transition-all shadow-xl active:scale-95 disabled:opacity-50 text-left font-sans">
                        <i class="fas fa-microchip mr-3 text-left font-sans"></i>
                        Execute Data Injection
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import CategoryItem from './CategoryItem.vue';

const props = defineProps({
    products: { type: [Array, Object], default: () => [] },
    categories: { type: Array, default: () => [] },
});

const search = ref('');
const selectedCategoryId = ref(null);
const showModal = ref(false);
const showImportModal = ref(false);
const editingProduct = ref(null);
const fileInput = ref(null);
const showCategoryDropdown = ref(false);

const form = useForm({
    name: '',
    sku: '',
    description: '',
    type: 'physical',
    base_price: 0,
    cost_price: 0,
    stock_qty: 0,
    reorder_point: 5,
    track_inventory: true,
    category_id: null,
    tax_code: '',
    images: [],
});

const importForm = useForm({
    file: null
});

// Helper to recursively find category name
const findCategoryName = (categories, id) => {
    for (const cat of categories) {
        if (cat.id === id) return cat.name;
        if (cat.children?.length) {
            const found = findCategoryName(cat.children, id);
            if (found) return found;
        }
    }
    return null;
};

const selectedCategoryName = computed(() => {
    if (!selectedCategoryId.value) return null;
    return findCategoryName(props.categories, selectedCategoryId.value);
});

const printView = () => {
    window.print();
};

const openImportModal = () => {
    importForm.reset();
    showImportModal.value = true;
};

const handleFileSelect = (e) => {
    importForm.file = e.target.files[0];
};

const submitImport = () => {
    importForm.post(route('crm.products.import'), {
        onSuccess: () => {
            showImportModal.value = false;
        }
    });
};

const debouncedSearch = debounce(() => {
    router.get(
        route('crm.hub'),
        { 
            section: 'config', 
            tab: 'products',
            search: search.value || undefined,
            category_id: selectedCategoryId.value || undefined
        },
        { preserveState: true, replace: true }
    );
}, 300);

const selectCategory = (id) => {
    selectedCategoryId.value = selectedCategoryId.value === id ? null : id;
    debouncedSearch();
};

const openCreateModal = () => {
    editingProduct.value = null;
    form.reset();
    showModal.value = true;
};

const editProduct = (product) => {
    editingProduct.value = product;
    form.name = product.name;
    form.sku = product.sku;
    form.description = product.description;
    form.type = product.type;
    form.base_price = product.base_price;
    form.cost_price = product.cost_price;
    form.stock_qty = product.stock_qty;
    form.category_id = product.category_id;
    showModal.value = true;
};

const getTypeIcon = (type) => {
    switch (type) {
        case 'physical': return 'fas fa-box';
        case 'digital': return 'fas fa-download';
        case 'service': return 'fas fa-concierge-bell';
        default: return 'fas fa-tag';
    }
};

const submit = () => {
    if (editingProduct.value) {
        form.put(route('crm.products.update', editingProduct.value.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('crm.products.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val || 0);
};
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}

.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.1) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
     width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
     background-color: #e2e8f0;
     border-radius: 10px;
}
</style>
