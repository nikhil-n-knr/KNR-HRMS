<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import { 
    CommandLineIcon, 
    ArrowLeftIcon, 
    PlusIcon, 
    TrashIcon, 
    AdjustmentsHorizontalIcon,
    SparklesIcon,
    BoltIcon,
    CubeIcon,
    TableCellsIcon,
    MapPinIcon,
    ShieldCheckIcon,
    InformationCircleIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    InboxIcon,
    HomeIcon,
    BuildingOfficeIcon,
    Square3Stack3DIcon
} from '@heroicons/vue/24/solid';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    locations: Array
});

const showModal = ref(false);
const parentNode = ref(null);
const form = useForm({
    name: '',
    type: 'Room',
    parent_id: null
});

const openAddModal = (parent = null) => {
    parentNode.value = parent;
    form.reset();
    form.parent_id = parent ? parent.id : null;
    form.type = parent ? suggestType(parent.type) : 'Room';
    showModal.value = true;
};

const suggestType = (parentType) => {
    const hierarchy = { 'Room': 'Cabinet', 'Cabinet': 'Rack', 'Rack': 'Shelf', 'Shelf': 'Bin' };
    return hierarchy[parentType] || 'Bin';
};

const submitNode = () => {
    form.post(route('admin.physical-documents.locations.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const deleteNode = (node) => {
    if (confirm(`CRITICAL_ACTION: Permanently delete ${node.name} and all nested sub-nodes?`)) {
        router.delete(route('admin.physical-documents.locations.destroy', node.id));
    }
};

const getIcon = (type) => {
    switch(type) {
        case 'Room': return HomeIcon;
        case 'Cabinet': return BuildingOfficeIcon;
        case 'Rack': return InboxIcon;
        case 'Shelf': return CommandLineIcon;
        case 'Bin': return CubeIcon;
        default: return Square3Stack3DIcon;
    }
};
</script>

<template>
    <Head title="Storage Matrix Configuration" />
    
    <div class="h-full flex flex-col font-outfit italic -m-8 p-12 bg-slate-50 min-h-screen relative overflow-hidden animate-in fade-in duration-1000">
        <!-- AI Grid Background -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:40px_40px] pointer-events-none"></div>
        <div class="absolute -right-32 -top-32 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none italic"></div>

        <!-- Strategic Header Terminal -->
        <header class="bg-slate-950 rounded-[3.5rem] border border-white/5 p-12 shadow-3xl mb-12 relative overflow-hidden group">
            <div class="absolute -right-24 -top-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
            
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-10 relative z-10">
                <div class="flex items-center gap-10 italic text-left">
                    <Link :href="route('admin.physical-documents.index')" 
                        class="w-20 h-20 bg-white/5 border border-white/10 rounded-[2rem] flex items-center justify-center text-white hover:bg-white/10 hover:border-indigo-500/50 transition-all active:scale-90 shadow-2xl shrink-0 italic">
                        <ArrowLeftIcon class="w-8 h-8" />
                    </Link>
                    <div class="italic">
                        <div class="flex items-center gap-6 italic">
                            <h1 class="text-5xl font-black text-white uppercase tracking-tighter italic leading-none">Storage Builder</h1>
                            <div class="px-5 py-2 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center gap-3 italic">
                                <div class="w-2.5 h-2.5 bg-indigo-400 rounded-full animate-pulse shadow-[0_0_12px_#818cf8]"></div>
                                <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest italic">MATRIX_CONFIG_MODE</span>
                            </div>
                        </div>
                        <p class="text-[11px] font-black text-slate-500 uppercase tracking-[0.6em] mt-5 italic leading-none drop-shadow-sm truncate uppercase">PHYSICAL_STRUCTURE_ARCHITECT_v1.0</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 italic">
                    <button @click="openAddModal(null)" 
                        class="h-20 px-12 bg-white text-slate-950 rounded-[2rem] text-[11px] font-black uppercase tracking-[0.3em] shadow-2xl hover:bg-indigo-500 hover:text-white transition-all flex items-center gap-6 active:scale-95 group italic border-none">
                        <PlusIcon class="w-8 h-8 group-hover:rotate-180 transition-transform duration-700 italic" />
                        <span>Initialize Root Zone</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Architect Workspace -->
        <main class="flex-1 bg-white/80 backdrop-blur-3xl rounded-[4.5rem] border border-slate-200 p-16 shadow-3xl relative overflow-hidden z-10 transition-all duration-1000 italic scroll-smooth overflow-y-auto custom-scrollbar">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_var(--tw-gradient-stops))] from-indigo-50/50 via-transparent to-transparent pointer-events-none italic"></div>
            
            <div v-if="locations.length === 0" class="h-full flex flex-col items-center justify-center text-center py-40 italic">
                 <div class="w-40 h-40 bg-slate-50 border-4 border-white rounded-[4rem] shadow-inner flex items-center justify-center text-slate-200 mb-10 group hover:scale-110 transition-transform duration-700 italic">
                     <CubeIcon class="w-20 h-20 group-hover:rotate-12 transition-transform italic" />
                 </div>
                 <h3 class="text-3xl font-black text-slate-950 uppercase tracking-tighter italic leading-none">Matrix Void Detected</h3>
                 <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.5em] mt-6 italic px-10 max-w-lg leading-loose">No storage protocols defined. Initialize your physical repository structure to begin tracking.</p>
            </div>

            <!-- Recursive Protocol Tree -->
            <div class="max-w-5xl mx-auto space-y-12 italic pb-20">
                <div v-for="root in locations" :key="root.id" class="group/root space-y-8 italic">
                    <!-- Root Node Card -->
                    <div class="relative bg-slate-950 rounded-[3.5rem] p-8 flex items-center justify-between shadow-2xl border border-white/5 overflow-hidden group/card hover:bg-slate-900 transition-all duration-700 italic">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/10 via-transparent to-transparent pointer-events-none opacity-40"></div>
                        
                        <div class="flex items-center gap-10 relative z-10 italic">
                            <div class="w-20 h-20 bg-white/5 border border-white/10 rounded-[2rem] flex items-center justify-center text-teal-400 shadow-xl group-hover/card:rotate-6 transition-transform duration-700 italic">
                                <component :is="getIcon(root.type)" class="w-10 h-10" />
                            </div>
                            <div class="italic">
                                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest italic mb-2">{{ root.type }}</p>
                                <h3 class="text-3xl font-black text-white uppercase tracking-tighter italic leading-none">{{ root.name }}</h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 relative z-10 opacity-0 group-hover/card:opacity-100 transition-all translate-x-10 group-hover/card:translate-x-0 italic">
                             <button @click="openAddModal(root)" class="h-14 px-8 bg-white/5 border border-white/10 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl hover:bg-indigo-600 hover:border-indigo-500 transition-all italic">
                                + ADD_CHILD
                             </button>
                             <button @click="deleteNode(root)" class="w-14 h-14 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center text-rose-400 hover:bg-rose-500 hover:text-white transition-all italic">
                                <TrashIcon class="w-6 h-6" />
                             </button>
                        </div>
                    </div>

                    <!-- L2 Nesting -->
                    <div v-if="root.children && root.children.length" class="pl-20 space-y-6 italic relative">
                        <div class="absolute left-10 top-0 bottom-0 w-1 bg-slate-100 rounded-full"></div>
                        
                        <div v-for="child in root.children" :key="child.id" class="space-y-6 italic">
                             <div class="relative bg-white border-2 border-slate-50 rounded-[3rem] p-8 flex items-center justify-between shadow-sm hover:shadow-2xl hover:border-indigo-100 transition-all duration-700 italic group/child">
                                <div class="flex items-center gap-8 italic">
                                    <div class="w-14 h-14 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-indigo-500 group-hover/child:bg-slate-950 group-hover/child:text-teal-400 transition-all italic">
                                        <component :is="getIcon(child.type)" class="w-7 h-7" />
                                    </div>
                                    <div class="italic">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic mb-1.5">{{ child.type }}</p>
                                        <h4 class="text-2xl font-black text-slate-950 uppercase tracking-tighter italic leading-none">{{ child.name }}</h4>
                                    </div>
                                </div>

                                <div class="flex items-center gap-5 opacity-0 group-hover/child:opacity-100 transition-all translate-x-10 group-hover/child:translate-x-0 italic">
                                    <button @click="openAddModal(child)" class="h-12 px-6 bg-slate-50 border border-slate-200 text-[9px] font-black text-slate-500 uppercase tracking-widest rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all italic">
                                        + NESTED
                                    </button>
                                    <button @click="deleteNode(child)" class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center text-slate-300 hover:bg-rose-50 hover:text-rose-500 transition-all italic">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                             </div>

                             <!-- L3 Nesting -->
                             <div v-if="child.children && child.children.length" class="pl-20 grid grid-cols-1 md:grid-cols-2 gap-6 italic">
                                <div v-for="gc in child.children" :key="gc.id" 
                                    class="bg-indigo-50/30 border-2 border-white rounded-[2.5rem] p-6 flex items-center justify-between group/gc hover:bg-white hover:shadow-xl hover:border-indigo-100 transition-all duration-500 italic">
                                    <div class="flex items-center gap-6 italic">
                                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 group-hover/gc:bg-slate-950 group-hover/gc:text-teal-400 transition-all italic">
                                            <component :is="getIcon(gc.type)" class="w-5 h-5" />
                                        </div>
                                        <div class="italic">
                                            <p class="text-[7px] font-black text-indigo-400 uppercase tracking-widest italic leading-none mb-1">{{ gc.type }}</p>
                                            <span class="text-lg font-black text-slate-950 uppercase tracking-tighter italic">{{ gc.name }}</span>
                                        </div>
                                    </div>
                                    <button @click="deleteNode(gc)" class="w-10 h-10 bg-white/50 border border-white rounded-xl flex items-center justify-center text-slate-300 hover:text-rose-500 transition-all italic opacity-0 group-hover/gc:opacity-100">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Node Acquisition Modal -->
    <PremiumModal :show="showModal" @close="showModal = false" title="Node Acquisition" subtitle="Expand physical archiving structure with new storage terminal">
        <form @submit.prevent="submitNode" class="p-8 space-y-10 italic text-left">
             <div class="p-6 bg-slate-50 rounded-[2.5rem] border-4 border-white shadow-inner flex items-center gap-8 italic mb-10" v-if="parentNode">
                 <div class="w-14 h-14 bg-slate-950 rounded-2xl flex items-center justify-center text-teal-400 shrink-0 italic">
                      <component :is="getIcon(parentNode.type)" class="w-8 h-8" />
                 </div>
                 <div class="italic">
                      <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] mb-1 italic">PARENT_NODE</p>
                      <h5 class="text-xl font-black text-slate-950 uppercase tracking-tighter italic leading-none">{{ parentNode.name }}</h5>
                 </div>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-10 italic">
                <div class="space-y-4 italic">
                    <InputLabel value="Terminal Classification" />
                    <BaseSelect v-model="form.type">
                        <option>Room</option>
                        <option>Cabinet</option>
                        <option>Rack</option>
                        <option>Shelf</option>
                        <option>Bin</option>
                        <option>Safe</option>
                        <option>Offsite</option>
                    </BaseSelect>
                </div>
                <div class="space-y-4 italic">
                    <InputLabel value="Ref Descriptor (Name)" />
                    <TextInput v-model="form.name" placeholder="E.G. ZONE_A, BIN_402" required />
                </div>
             </div>

             <div class="flex items-center justify-between pt-12 border-t border-slate-100 mt-10">
                <button @click="showModal = false" type="button" class="text-[11px] font-black uppercase tracking-[0.4em] text-slate-300 hover:text-rose-500 transition-all italic">Abort Protocol</button>
                <button type="submit" :disabled="form.processing" class="h-20 px-12 bg-slate-950 text-white rounded-[1.8rem] text-[11px] font-black uppercase tracking-[0.3em] shadow-2xl hover:bg-indigo-600 transition-all flex items-center gap-6 active:scale-95 disabled:opacity-50 group italic border-4 border-white/5">
                    <ArrowPathIcon v-if="form.processing" class="w-8 h-8 animate-spin italic" />
                    <CheckCircleIcon v-else class="w-8 h-8 text-teal-400 group-hover:scale-125 transition-transform duration-500 italic" />
                    <span>{{ form.processing ? 'SYNCING_MATRIX...' : 'INIT_STORAGE_NODE' }}</span>
                </button>
             </div>
        </form>
    </PremiumModal>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.05);
    border-radius: 20px;
}
.shadow-3xl {
    box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.15);
}
</style>
