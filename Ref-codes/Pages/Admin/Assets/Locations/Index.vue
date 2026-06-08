<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import { 
    MapPinIcon, 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    BuildingOfficeIcon,
    ArrowLeftIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/solid';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    locations: Array
});

const showModal = ref(false);
const editingLocation = ref(null);

const form = useForm({
    name: '',
    node_type: 'Room',
    parent_id: '',
    code: '',
    capacity: '',
    is_active: true
});

const openCreate = () => {
    editingLocation.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (location) => {
    editingLocation.value = location;
    form.name = location.name;
    form.node_type = location.node_type;
    form.parent_id = location.parent_id || '';
    form.code = location.code || '';
    form.capacity = location.capacity || '';
    form.is_active = !!location.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingLocation.value) {
        form.put(route('admin.assets.location-nodes.update', editingLocation.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.assets.location-nodes.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const archiveLocation = (location) => {
    if (confirm(`Archive location '${location.name}'? This will hide it from the system.`)) {
        router.delete(route('admin.assets.location-nodes.destroy', location.id));
    }
};

const nodeTypes = ['Branch', 'Building', 'Floor', 'Room', 'Zone', 'Locker', 'Cupboard', 'Shelf', 'Bin'];

</script>

<template>
    <Head title="Location Matrix" />

    <div class="space-y-10 pb-20 font-outfit animate-in fade-in duration-700 bg-slate-50/50 -m-8 p-8 min-h-screen">
        <!-- Strategic Header -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm relative overflow-hidden group text-left">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 relative z-10">
                <div class="flex items-center gap-6">
                    <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 shrink-0">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-4">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase leading-none">Location Matrix</h1>
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-indigo-100 leading-none">Infrastructure</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Manage deployment rooms, buildings, and specialized storage zones.</p>
                    </div>
                </div>

                <button @click="openCreate" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-md hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-3">
                    <PlusIcon class="w-4 h-4" />
                    Define New Node
                </button>
            </div>
        </div>

        <!-- Table View -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">
            <BaseDataTable
                :data="locations"
                :columns="[
                    { key: 'identity', label: 'Node Identity', sortable: true },
                    { key: 'node_type', label: 'Type', sortable: true },
                    { key: 'parent', label: 'Parent Node', sortable: true },
                    { key: 'capacity', label: 'Assigned Items', sortable: true },
                    { key: 'actions', label: '', sortable: false, align: 'right' }
                ]"
                search-placeholder="Search locations..."
            >
                <template #cell-identity="{ item }">
                    <div class="flex items-center gap-4 py-1 text-left">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-100 shrink-0">
                            <BuildingOfficeIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-950 uppercase tracking-tight leading-none">{{ item.name }}</div>
                            <div class="text-[9px] font-mono font-bold text-slate-400 mt-1 uppercase tracking-widest leading-none">CODE: {{ item.code || 'UNTAGGED' }}</div>
                        </div>
                    </div>
                </template>

                <template #cell-node_type="{ item }">
                    <span class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 text-[9px] font-bold uppercase tracking-widest">
                        {{ item.node_type }}
                    </span>
                </template>

                <template #cell-parent="{ item }">
                    <div class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                        {{ item.parent?.name || 'Root Node' }}
                    </div>
                </template>

                <template #cell-capacity="{ item }">
                    <div class="flex items-center gap-2 justify-start">
                        <span class="text-xs font-black text-slate-900 tabular-nums">{{ item.assignments_count || 0 }}</span>
                        <span v-if="item.capacity" class="text-slate-300">/</span>
                        <span v-if="item.capacity" class="text-[10px] font-bold text-slate-400 uppercase tabular-nums">{{ item.capacity }}</span>
                    </div>
                </template>

                <template #cell-actions="{ item }">
                    <div class="flex justify-end gap-3 pr-4">
                        <button @click="openEdit(item)" class="px-4 py-2 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-50 hover:text-indigo-600 transition-all inline-flex items-center gap-2">
                            <PencilIcon class="w-3.5 h-3.5" />
                            Edit
                        </button>
                        <button @click="archiveLocation(item)" class="px-4 py-2 bg-rose-50 text-rose-700 border border-rose-100 rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-rose-100 transition-all inline-flex items-center gap-2">
                            <ArchiveBoxIcon class="w-3.5 h-3.5" />
                            Archive
                        </button>
                    </div>
                </template>
            </BaseDataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-8 text-left">
                <div class="flex items-center gap-6 mb-8 border-b border-slate-100 pb-6">
                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                        <MapPinIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">
                            {{ editingLocation ? 'Update Node' : 'Initialize Node' }}
                        </h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5">Define location parameters in the matrix.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <InputLabel for="name" value="Node Name" class="px-2" />
                        <TextInput id="name" v-model="form.name" type="text" class="w-full" required placeholder="E.G. SERVER ROOM A" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="node_type" value="Type" class="px-2" />
                        <select id="node_type" v-model="form.node_type" class="w-full h-12 bg-white border border-slate-200 rounded-xl px-4 text-sm font-bold text-slate-900 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all appearance-none shadow-sm cursor-pointer">
                            <option v-for="type in nodeTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                        <InputError :message="form.errors.node_type" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="parent_id" value="Parent Location" class="px-2" />
                        <select id="parent_id" v-model="form.parent_id" class="w-full h-12 bg-white border border-slate-200 rounded-xl px-4 text-sm font-bold text-slate-900 uppercase tracking-widest focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all appearance-none shadow-sm cursor-pointer">
                            <option value="">None (Root Node)</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id" v-show="!editingLocation || loc.id !== editingLocation.id">
                                {{ loc.name }} ({{ loc.node_type }})
                            </option>
                        </select>
                        <InputError :message="form.errors.parent_id" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="code" value="Code / Label" class="px-2" />
                        <TextInput id="code" v-model="form.code" type="text" class="w-full" placeholder="E.G. SR-A" />
                        <InputError :message="form.errors.code" />
                    </div>

                    <div class="space-y-2 text-left">
                        <InputLabel for="capacity" value="Max Item Capacity" class="px-2" />
                        <TextInput id="capacity" v-model="form.capacity" type="number" class="w-full" placeholder="Optional" />
                        <InputError :message="form.errors.capacity" />
                    </div>

                    <div class="flex items-center gap-3 pt-10">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all cursor-pointer">
                        <label for="is_active" class="text-[11px] font-bold text-slate-600 uppercase tracking-widest cursor-pointer">Node is Operational</label>
                    </div>

                    <div class="col-span-full pt-8 border-t border-slate-100 flex justify-end gap-4 mt-4">
                        <button type="button" @click="closeModal" class="px-8 py-3 bg-white border border-slate-200 text-slate-400 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-10 py-3 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all active:scale-95 disabled:opacity-50">
                            {{ form.processing ? 'Syncing...' : (editingLocation ? 'Update Node' : 'Initialize Node') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
