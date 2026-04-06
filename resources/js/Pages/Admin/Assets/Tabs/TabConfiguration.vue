<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    SquaresPlusIcon, 
    UserGroupIcon, 
    MapPinIcon,
    ArrowRightIcon,
    PencilSquareIcon,
    TrashIcon,
    BoltIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
    vendors: Array
});

const activeSubTab = ref('categories'); // categories, vendors

// Category Form
const showCategoryModal = ref(false);
const categoryForm = useForm({
    name: '',
    description: '',
    is_electronic: false
});

const submitCategory = () => {
    console.log('Creating category', categoryForm);
    showCategoryModal.value = false;
};

// Vendor Form
const showVendorModal = ref(false);
const vendorForm = useForm({
    name: '',
    email: '',
    phone: '',
    service_type: ''
});
</script>

<template>
    <div class="flex flex-col md:flex-row gap-10 min-h-[600px] font-outfit">
        <!-- Sidebar Settings Nav -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white/50 backdrop-blur-xl p-3 rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 space-y-1">
                <button @click="activeSubTab = 'categories'" 
                    class="w-full flex items-center justify-between px-5 py-4 rounded-2xl text-sm font-black uppercase tracking-[0.2em] transition-all group"
                    :class="activeSubTab === 'categories' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-white hover:text-slate-900'">
                    <div class="flex items-center gap-3">
                        <SquaresPlusIcon class="w-4 h-4" />
                        Asset Classes
                    </div>
                    <ArrowRightIcon v-if="activeSubTab === 'categories'" class="w-3 h-3" />
                </button>
                <button @click="activeSubTab = 'vendors'" 
                    class="w-full flex items-center justify-between px-5 py-4 rounded-2xl text-sm font-black uppercase tracking-[0.2em] transition-all group"
                    :class="activeSubTab === 'vendors' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-white hover:text-slate-900'">
                    <div class="flex items-center gap-3">
                        <UserGroupIcon class="w-4 h-4" />
                        Supply Nodes
                    </div>
                    <ArrowRightIcon v-if="activeSubTab === 'vendors'" class="w-3 h-3" />
                </button>
                 <button  class="w-full flex items-center justify-between px-5 py-4 rounded-2xl text-sm font-black uppercase tracking-[0.2em] text-slate-300 cursor-not-allowed opacity-50">
                    <div class="flex items-center gap-3">
                        <MapPinIcon class="w-4 h-4" />
                        Locations (Pro)
                    </div>
                </button>
            </div>

            <div class="mt-8 p-6 bg-indigo-600 rounded-[2rem] text-white shadow-2xl shadow-indigo-200 group relative overflow-hidden">
                <BoltIcon class="absolute -right-4 -bottom-4 w-24 h-24 opacity-20 group-hover:scale-125 transition-transform duration-700" />
                <h4 class="text-sm font-black uppercase tracking-[0.2em] mb-4">Architecture Intel</h4>
                <p class="text-base font-medium leading-relaxed opacity-90">Manage the core resource hierarchy and external supply chains to maintain operational efficiency.</p>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 space-y-8">
            
            <!-- Categories Panel -->
            <Transition name="fade-slide" mode="out-in">
                <div v-if="activeSubTab === 'categories'" :key="'categories'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/80 p-6 rounded-[2rem] border border-white shadow-sm">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Asset Class Registry</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Resource categorization nodes</p>
                        </div>
                        <button @click="showCategoryModal = true" class="h-11 px-6 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all flex items-center gap-3">
                            <PlusIcon class="w-4 h-4" />
                            Initialize Class
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div v-for="cat in categories" :key="cat.id" class="bg-white p-6 rounded-[2rem] border border-slate-100 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-500/5 transition-all group flex items-center justify-between">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 font-black text-[14px] group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                    {{ cat.name.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="text-base font-black text-slate-800 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ cat.name }}</h4>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ cat.assets_count || 0 }} NODES_LINKED</span>
                                        <span v-if="cat.is_electronic" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-blue-50 text-blue-600 border border-blue-100 uppercase tracking-widest">ELECTRONIC</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                <button class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all"><PencilSquareIcon class="w-4 h-4" /></button>
                                <button class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all"><TrashIcon class="w-4 h-4" /></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendors Panel -->
                <div v-else-if="activeSubTab === 'vendors'" :key="'vendors'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/80 p-6 rounded-[2rem] border border-white shadow-sm">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Supply Network Index</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Verified external supply channels</p>
                        </div>
                        <button @click="showVendorModal = true" class="h-11 px-6 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all flex items-center gap-3">
                            <PlusIcon class="w-4 h-4" />
                            Map Vendor Node
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="vendor in vendors" :key="vendor.id" class="bg-white p-6 rounded-[2rem] border border-slate-100 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-500/5 transition-all group flex items-center justify-between relative overflow-hidden">
                            <div class="absolute left-0 top-0 w-1.5 h-full bg-slate-900 group-hover:bg-indigo-600 transition-colors"></div>
                            <div class="flex items-center gap-6 pl-2">
                                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-white transition-colors relative overflow-hidden">
                                    <UserGroupIcon class="w-7 h-7 text-slate-200 group-hover:text-indigo-100 transition-colors" />
                                    <span class="absolute inset-0 flex items-center justify-center text-sm font-black text-slate-500 uppercase">{{ vendor.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-base font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ vendor.name }}</h4>
                                    <div class="flex items-center gap-4 mt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ vendor.contact_person || 'GENERIC_CONTACT' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ vendor.phone || 'NO_COMMS_INDEX' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-8">
                                <div class="hidden sm:block">
                                    <div class="text-2xl font-black text-slate-900 tabular-nums leading-none">{{ vendor.assets_count || 0 }}</div>
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2">Nodes Linked</div>
                                </div>
                                <div class="flex flex-col gap-2">
                                     <button class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition-all"><PencilSquareIcon class="w-4 h-4" /></button>
                                     <button class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all"><TrashIcon class="w-4 h-4" /></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </div>

    <!-- Category Initialization Modal -->
    <PremiumModal 
        :show="showCategoryModal" 
        @close="showCategoryModal = false" 
        title="Initialize Asset Class" 
        subtitle="Define Logic Node Segment"
        icon="fa-cubes"
    >
        <form @submit.prevent="submitCategory" class="space-y-6 pt-4">
             <div class="space-y-2.5">
                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Class Designation</label>
                <input v-model="categoryForm.name" type="text" class="w-full h-12 bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm" required placeholder="CLASS_IDENTIFIER...">
            </div>
             <div class="space-y-2.5">
                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Logic Segment Description</label>
                <textarea v-model="categoryForm.description" rows="3" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm" placeholder="SEGMENT_GOVERNANCE_DETAILS..."></textarea>
            </div>
            
            <div class="flex items-center gap-3 bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                <input type="checkbox" v-model="categoryForm.is_electronic" id="is_electronic" class="w-5 h-5 rounded-lg border-2 border-blue-200 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 transition-all cursor-pointer">
                <label for="is_electronic" class="text-sm font-black text-blue-700 uppercase tracking-[0.1em] cursor-pointer selection:bg-transparent">Electronic Hardware Protocol Active</label>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-8">
                <button @click="showCategoryModal = false" type="button" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort Initialization</button>
                <button type="submit" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200">
                    <PlusIcon class="w-4 h-4" />
                    <span>Confirm Indexing</span>
                </button>
            </div>
        </form>
    </PremiumModal>
</template>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { 
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-slide-enter-from { 
    opacity: 0; 
    transform: translateY(20px);
}
.fade-slide-leave-to { 
    opacity: 0; 
    transform: translateY(-20px);
}
</style>
