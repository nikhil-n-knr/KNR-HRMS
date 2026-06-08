<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Cog8ToothIcon, 
    SquaresPlusIcon, 
    ChartBarSquareIcon, 
    QueueListIcon, 
    PlusIcon,
    TrashIcon,
    CheckCircleIcon,
    SparklesIcon,
    InformationCircleIcon,
    ArrowLeftIcon,
    SwatchIcon,
    WalletIcon,
    ClipboardDocumentCheckIcon,
    CircleStackIcon,
    ListBulletIcon,
    BoltIcon,
    TagIcon,
    ArchiveBoxIcon,
    ArrowPathIcon,
    AdjustmentsHorizontalIcon,
    CurrencyRupeeIcon,
    PencilSquareIcon,
    BuildingLibraryIcon
} from '@heroicons/vue/24/solid';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    categories: Array,
    initialConfigs: Object
});

const activeTab = ref('attributes'); 
const selectedCategory = ref(props.categories[0] || null);
const showCategoryModal = ref(false);
const editingBaseCategory = ref(null);

const categoryForm = useForm({
    name: '',
    code: '',
    is_electronic: false,
    maintenance_interval_days: 30
});

const openCreateCategory = () => {
    editingBaseCategory.value = null;
    categoryForm.reset();
    showCategoryModal.value = true;
};

const openEditCategory = (cat) => {
    editingBaseCategory.value = cat;
    categoryForm.name = cat.name;
    categoryForm.code = cat.code || '';
    categoryForm.is_electronic = !!cat.is_electronic;
    categoryForm.maintenance_interval_days = cat.maintenance_interval_days || 30;
    showCategoryModal.value = true;
};

const submitCategory = () => {
    if (editingBaseCategory.value) {
        categoryForm.put(route('admin.assets.categories.update', editingBaseCategory.value.id), {
            onSuccess: () => showCategoryModal.value = false
        });
    } else {
        categoryForm.post(route('admin.assets.categories.store'), {
            onSuccess: () => showCategoryModal.value = false
        });
    }
};

const archiveCategory = (cat) => {
    if (confirm(`Archive classification '${cat.name}'? This will prevent new assets from using it.`)) {
        router.delete(route('admin.assets.categories.destroy', cat.id));
    }
};

// --- Form Initialization ---
const attributeForm = useForm({
    custom_attributes: [],
    useful_life_years: null,
    depreciation_method: 'Straight_Line',
    scrap_value_percent: 0
});

const selectCategoryForEdit = (category) => {
    selectedCategory.value = category;
    attributeForm.custom_attributes = category.custom_attributes || [];
    attributeForm.useful_life_years = category.useful_life_years;
    attributeForm.depreciation_method = category.depreciation_method || 'Straight_Line';
    attributeForm.scrap_value_percent = category.scrap_value_percent || 0;
};

if (selectedCategory.value) {
    selectCategoryForEdit(selectedCategory.value);
}

const addAttribute = () => {
    attributeForm.custom_attributes.push({ name: '', type: 'text', required: false });
};

const removeAttribute = (index) => {
    attributeForm.custom_attributes.splice(index, 1);
};

const saveCategoryConfig = () => {
    attributeForm.put(route('admin.assets.categories.config.update', selectedCategory.value.id), {
        onSuccess: () => {
             // Success handled by Inertia
        }
    });
};

const tabs = [
    { id: 'attributes', label: 'Item Info', icon: SquaresPlusIcon },
    { id: 'depreciation', label: 'Value Loss', icon: WalletIcon },
    { id: 'workflows', label: 'Rules', icon: ListBulletIcon }
];
</script>

<template>
    <Head title="Control Center" />
    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-8 relative">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Strategic Header Terminal -->
        <div class="bg-white px-8 py-10 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-8 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 shrink-0">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-4">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Control Center</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-white/10 leading-relaxed">
                                Each type of equipment (like Laptops or Chairs) can have its own rules. Here you can add extra info fields (like RAM size) or set how much their value drops over time.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none">Manage types of items and their special details.</p>
                </div>
            </div>

            <div class="flex items-center gap-4 z-10">
                  <div class="px-5 py-2 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-3">
                    <BoltIcon class="w-4 h-4 text-indigo-500" />
                    <span class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest">Config Mode Active</span>
                  </div>
                  <button @click="openCreateCategory" class="h-12 px-6 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-3">
                     <PlusIcon class="w-4 h-4" />
                     Initialize Type
                  </button>
            </div>
        </div>

        <div class="flex-1 flex gap-8 min-h-0 relative z-10 overflow-hidden">
            <!-- Sidebar: Category Selection -->
            <div class="w-full lg:w-80 flex flex-col bg-white rounded-3xl border border-slate-200 p-6 min-h-0 overflow-hidden shadow-sm">
                <div class="mb-6 px-2 text-left">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Item Types</h3>
                    <div class="h-1 w-10 bg-indigo-100 rounded-full"></div>
                </div>
                
                <div class="flex-1 overflow-y-auto no-scrollbar space-y-2 pr-1">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="selectCategoryForEdit(cat)"
                        class="w-full p-4 rounded-2xl border transition-all flex items-center gap-4 relative overflow-hidden group text-left"
                        :class="selectedCategory?.id === cat.id ? 'bg-indigo-600 border-indigo-500 text-white shadow-md' : 'bg-slate-50 border-slate-100 hover:border-slate-200 text-slate-500'"
                    >
                        <div class="w-10 h-10 rounded-xl bg-black/5 border border-white/10 flex items-center justify-center text-lg font-black group-hover:rotate-6 transition-transform shrink-0">
                            {{ cat.name.charAt(0) }}
                        </div>
                        <div class="flex flex-col gap-0.5 lg:min-w-0">
                            <span class="text-sm font-bold uppercase tracking-tight truncate leading-none" :class="selectedCategory?.id === cat.id ? 'text-white' : 'text-slate-900'">{{ cat.name }}</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest opacity-60 truncate leading-none" :class="selectedCategory?.id === cat.id ? 'text-indigo-100' : 'text-slate-400'">{{ cat.assets_count || 0 }} Items Linked</span>
                        </div>
                        <div v-if="selectedCategory?.id === cat.id" class="ml-auto flex items-center gap-2">
                             <button @click.stop="openEditCategory(cat)" class="p-1.5 hover:bg-white/20 rounded-lg transition-colors">
                                <PencilSquareIcon class="w-4 h-4 text-white" />
                             </button>
                             <button @click.stop="archiveCategory(cat)" class="p-1.5 hover:bg-white/20 rounded-lg transition-colors">
                                <TrashIcon class="w-4 h-4 text-white" />
                             </button>
                             <CheckCircleIcon class="w-5 h-5 text-white ml-2" />
                        </div>
                    </button>
                </div>
            </div>

            <!-- Content Area: Configuration Terminal -->
            <div class="flex-1 bg-white rounded-3xl border border-slate-200 p-10 flex flex-col min-h-0 overflow-hidden shadow-sm">
                <div v-if="selectedCategory" class="flex flex-col h-full">
                    <!-- Internal Tabs -->
                    <div class="flex items-center gap-2 mb-10 p-1.5 bg-slate-50 border border-slate-200 rounded-2xl w-fit">
                        <button 
                            v-for="t in tabs" 
                            :key="t.id"
                            @click="activeTab = t.id"
                            class="h-10 px-6 text-[9px] font-bold uppercase tracking-widest rounded-xl transition-all flex items-center gap-3 shrink-0"
                            :class="activeTab === t.id ? 'bg-white text-slate-900 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/5 transition-all'"
                        >
                            <component :is="t.icon" class="w-4 h-4" :class="activeTab === t.id ? 'text-indigo-600' : 'text-slate-400'" />
                            {{ t.label }}
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto no-scrollbar pr-4">
                        <div class="flex items-center gap-6 mb-10 border-b border-slate-100 pb-8 text-left">
                             <div class="w-14 h-14 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center border border-slate-200 shrink-0 group hover:rotate-6 transition-transform">
                                <ArchiveBoxIcon class="w-8 h-8 text-indigo-400" />
                             </div>
                             <div>
                                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Set up rules for {{ selectedCategory.name }}</h2>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none">Customize fields and logic for this group.</p>
                             </div>
                        </div>

                        <form @submit.prevent="saveCategoryConfig" class="space-y-10 text-left">
                            <!-- Attributes Tab -->
                            <div v-if="activeTab === 'attributes'" class="space-y-6 animate-in fade-in slide-in-from-right-5 duration-500">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div v-for="(attr, idx) in attributeForm.custom_attributes" :key="idx" class="bg-slate-50 border border-slate-200 p-6 rounded-2xl space-y-4 relative group/row items-center transition-all hover:border-indigo-200 shadow-sm group/attr">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[8px] font-bold text-indigo-500 uppercase tracking-widest leading-none">Detail #{{ idx + 1 }}</span>
                                            <button type="button" @click="removeAttribute(idx)" class="text-slate-400 hover:text-rose-500 transition-colors group-hover/attr:scale-110">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-4">
                                            <div class="space-y-2 text-left">
                                                <label class="px-4 text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">Label</label>
                                                <input v-model="attr.name" type="text" class="w-full h-12 bg-white border border-slate-200 rounded-xl px-4 text-sm font-bold text-slate-900 focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm" placeholder="E.G. RAM SIZE...">
                                            </div>
                                            <div class="space-y-2 text-left">
                                                <label class="px-4 text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">Type</label>
                                                <select v-model="attr.type" class="w-full h-12 bg-white border border-slate-200 rounded-xl px-4 text-[9px] font-bold text-slate-900 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 transition-all appearance-none shadow-sm">
                                                    <option value="text">Normal Text</option>
                                                    <option value="number">Numbers Only</option>
                                                    <option value="date">Date/Calendar</option>
                                                    <option value="select">Drop-down List</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" @click="addAttribute" class="border-2 border-dashed border-slate-200 rounded-3xl p-8 flex flex-col items-center justify-center gap-4 text-slate-400 hover:border-indigo-400 hover:text-indigo-500 transition-all group/plus shadow-sm min-h-[200px]">
                                        <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center group-hover/plus:rotate-90 transition-transform">
                                            <PlusIcon class="w-8 h-8" />
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-widest">Add Extra Detail Field</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Value Loss Tab -->
                            <div v-if="activeTab === 'depreciation'" class="space-y-10 animate-in slide-in-from-bottom-10 duration-500 text-left">
                                <div class="bg-indigo-50/30 border border-indigo-100 p-8 rounded-3xl relative overflow-hidden group/dep">
                                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/5 rounded-full blur-[100px]"></div>
                                    <div class="flex items-center gap-6 mb-8 border-b border-slate-100 pb-6">
                                        <div class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-indigo-500 shadow-sm">
                                            <CurrencyRupeeIcon class="w-7 h-7" />
                                        </div>
                                        <div>
                                             <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">Money Value Loss</h4>
                                             <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest mt-2.5 leading-none">Set how fast items lose their price.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                                        <div class="space-y-3">
                                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest">Value Drop Method</label>
                                            <select v-model="attributeForm.depreciation_method" class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-[10px] font-bold text-slate-900 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 transition-all appearance-none shadow-sm">
                                                <option value="Straight_Line">Standard Drop (Same Every Year)</option>
                                                <option value="Reducing_Balance">Fast Drop (More Loss at Start)</option>
                                            </select>
                                        </div>
                                        <div class="space-y-3">
                                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest">Years it Lasts</label>
                                            <input v-model="attributeForm.useful_life_years" type="number" class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-lg font-black text-slate-900 focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all shadow-sm" placeholder="E.G. 5 YEARS">
                                        </div>
                                        <div class="space-y-3 col-span-full">
                                            <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Final Resell Value %</label>
                                            <div class="flex items-center gap-6 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                                 <input type="range" v-model="attributeForm.scrap_value_percent" min="0" max="100" class="flex-1 accent-indigo-500 h-1.5 bg-slate-100 rounded-full cursor-pointer">
                                                 <span class="text-3xl font-black text-slate-900 tabular-nums w-20 text-right">{{ attributeForm.scrap_value_percent }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rules Placeholder -->
                            <div v-if="activeTab === 'workflows'" class="py-16 text-center space-y-6 animate-in zoom-in-95 duration-700">
                                <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 mx-auto">
                                    <ListBulletIcon class="w-10 h-10" />
                                </div>
                                <h3 class="text-xl font-black text-slate-400 uppercase tracking-tight">Coming Soon</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Procedure automation based on item health triggers.</p>
                            </div>

                            <!-- Global Actions -->
                            <div class="pt-8 border-t border-slate-100 flex items-center justify-between pb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Changes saved instantly to matrix</span>
                                </div>
                                <button type="submit" :disabled="attributeForm.processing" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-emerald-600 transition-all flex items-center gap-4 active:scale-95 disabled:opacity-30 group/save border border-slate-800">
                                    <ArrowPathIcon v-if="attributeForm.processing" class="w-6 h-6 animate-spin" />
                                    <CheckCircleIcon v-else class="w-6 h-6 text-indigo-400 group-hover/save:scale-125 transition-transform" />
                                    <span>{{ attributeForm.processing ? 'Syncing...' : 'Save Rules' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-else class="flex-1 flex flex-col items-center justify-center text-center opacity-40 grayscale space-y-6">
                    <AdjustmentsHorizontalIcon class="w-24 h-24 text-slate-300 animate-[spin_10s_linear_infinite]" />
                    <div class="space-y-3">
                        <h3 class="text-xl font-black text-slate-400 uppercase tracking-tight">Selection Required</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pick an item type from the left to configure.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category CRUD Modal -->
    <Modal :show="showCategoryModal" @close="showCategoryModal = false">
        <div class="p-8 text-left">
            <div class="flex items-center gap-6 mb-8 border-b border-slate-100 pb-6">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                    <BuildingLibraryIcon class="w-8 h-8" />
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">
                        {{ editingBaseCategory ? 'Update Classification' : 'New Classification' }}
                    </h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5">Define a new type of item in the matrix.</p>
                </div>
            </div>

            <form @submit.prevent="submitCategory" class="space-y-6">
                <div class="space-y-2">
                    <InputLabel for="cat_name" value="Classification Name" class="px-2" />
                    <TextInput id="cat_name" v-model="categoryForm.name" type="text" class="w-full" required placeholder="E.G. MEDICAL EQUIPMENT" />
                    <InputError :message="categoryForm.errors.name" />
                </div>

                <div class="space-y-2">
                    <InputLabel for="cat_code" value="Code" class="px-2" />
                    <TextInput id="cat_code" v-model="categoryForm.code" type="text" class="w-full" placeholder="E.G. MED-EQ" />
                    <InputError :message="categoryForm.errors.code" />
                </div>

                <div class="flex flex-col sm:flex-row gap-6">
                    <div class="flex-1 space-y-2">
                        <InputLabel for="maintenance_interval" value="Maintenance Interval (Days)" class="px-2" />
                        <TextInput id="maintenance_interval" v-model="categoryForm.maintenance_interval_days" type="number" class="w-full" />
                        <InputError :message="categoryForm.errors.maintenance_interval_days" />
                    </div>
                    <div class="flex items-center gap-3 pt-8">
                        <input type="checkbox" id="is_elec" v-model="categoryForm.is_electronic" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all cursor-pointer">
                        <label for="is_elec" class="text-[11px] font-bold text-slate-600 uppercase tracking-widest cursor-pointer">Electronic Device</label>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 flex justify-end gap-4 mt-4">
                    <button type="button" @click="showCategoryModal = false" class="px-8 py-3 bg-white border border-slate-200 text-slate-400 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="submit" :disabled="categoryForm.processing" class="px-10 py-3 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all active:scale-95 disabled:opacity-50">
                        {{ categoryForm.processing ? 'Syncing...' : (editingBaseCategory ? 'Update Matrix' : 'Commit to Matrix') }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
