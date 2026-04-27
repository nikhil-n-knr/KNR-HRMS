<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import InputError from '@/Components/InputError.vue';
import { Link, useForm, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    PrinterIcon,
    PencilSquareIcon,
    WrenchScrewdriverIcon,
    IdentificationIcon,
    ClockIcon,
    CheckCircleIcon,
    CpuChipIcon,
    UserCircleIcon,
    TrashIcon,
    ArrowPathIcon,
    MapPinIcon,
    BoltIcon,
    UserPlusIcon,
    ArrowUturnLeftIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
    asset: Object,
    timeline: Array,
    qrCode: String,
    users: Array,
    categories: Array,
});

const showServiceModal = ref(false);
const showEditModal = ref(false);
const showAssignModal = ref(false);

const serviceForm = useForm({
    type: 'Repair',
    description: '',
    cost: 0,
    service_date: new Date().toISOString().split('T')[0],
});

const editForm = useForm({
    name: props.asset?.name || '',
    category_id: props.asset?.category_id || '',
    serial_number: props.asset?.serial_number || '',
    purchase_cost: props.asset?.purchase_cost || 0,
    purchase_date: props.asset?.purchase_date || '',
    status: props.asset?.status || 'Available',
    is_serialized: !!props.asset?.is_serialized,
    make: props.asset?.meta?.make || '',
    model: props.asset?.meta?.model || '',
});

const assignForm = useForm({
    user_id: '',
});

const submitService = () => {
    serviceForm.post(route('admin.assets.maintenance.store', props.asset.id), {
        onSuccess: () => {
            showServiceModal.value = false;
            serviceForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.put(route('admin.assets.update', props.asset.id), {
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const submitAssign = () => {
    assignForm.post(route('admin.assets.assign', props.asset.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            assignForm.reset();
        },
    });
};

const returnAsset = () => {
    router.post(route('admin.assets.return', props.asset.id), {});
};

const deleteAsset = () => {
    if (confirm(`Delete asset '${props.asset.name}'?`)) {
        router.delete(route('admin.assets.destroy', props.asset.id));
    }
};

const getStatusStyles = (status) => {
    switch (status) {
        case 'Available':
            return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'Assigned':
            return 'bg-indigo-50 text-indigo-700 border-indigo-100';
        case 'In_Service':
            return 'bg-amber-50 text-amber-700 border-amber-100';
        case 'Lost':
            return 'bg-rose-50 text-rose-700 border-rose-100';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <Head :title="'Asset Details - ' + asset.name" />
    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-16 -m-8 p-8 bg-slate-50 min-h-screen">
            <section class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <div class="flex flex-col xl:flex-row justify-between gap-8">
                    <div class="flex items-start gap-6">
                        <div class="w-40 h-40 bg-slate-50 border border-slate-200 rounded-2xl p-4 shrink-0">
                            <div class="w-full h-full [&>svg]:w-full [&>svg]:h-full [&>svg]:text-slate-900" v-html="qrCode"></div>
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="px-3 py-1 rounded-lg border text-[10px] font-bold uppercase tracking-widest" :class="getStatusStyles(asset.status)">
                                    {{ asset.status }}
                                </span>
                                <span class="px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[10px] font-bold uppercase tracking-widest text-slate-700">
                                    {{ asset.category?.name || 'Uncategorized' }}
                                </span>
                            </div>
                            <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ asset.name }}</h1>
                            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-5 text-left">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Serial Number</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.serial_number || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Location</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">{{ asset.location?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Purchase Cost</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">₹{{ (asset.purchase_cost || 0).toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 h-fit">
                        <Link :href="route('admin.assets.label', asset.id)" class="h-11 px-4 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center gap-2">
                            <PrinterIcon class="w-4 h-4" /> Label
                        </Link>
                        <button @click="showEditModal = true" class="h-11 px-4 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-700 hover:border-indigo-200 hover:text-indigo-700 transition-all flex items-center gap-2">
                            <PencilSquareIcon class="w-4 h-4" /> Edit
                        </button>
                        <button @click="showAssignModal = true" class="h-11 px-4 bg-indigo-50 border border-indigo-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-indigo-700 hover:bg-indigo-100 transition-all flex items-center gap-2">
                            <UserPlusIcon class="w-4 h-4" /> Assign
                        </button>
                        <button @click="returnAsset" class="h-11 px-4 bg-amber-50 border border-amber-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-amber-700 hover:bg-amber-100 transition-all flex items-center gap-2">
                            <ArrowUturnLeftIcon class="w-4 h-4" /> Return
                        </button>
                        <button @click="showServiceModal = true" class="h-11 px-4 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-emerald-700 hover:bg-emerald-100 transition-all flex items-center gap-2">
                            <WrenchScrewdriverIcon class="w-4 h-4" /> Maintenance
                        </button>
                        <button @click="deleteAsset" class="h-11 px-4 bg-rose-50 border border-rose-100 rounded-lg text-[10px] font-bold uppercase tracking-widest text-rose-700 hover:bg-rose-100 transition-all flex items-center gap-2">
                            <TrashIcon class="w-4 h-4" /> Delete
                        </button>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">Lifecycle Timeline</h2>
                <div class="space-y-4">
                    <div v-for="(event, idx) in timeline" :key="idx" class="p-5 border border-slate-100 rounded-2xl bg-slate-50/50">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-black text-slate-900">{{ event.title }}</p>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ event.date || 'N/A' }}</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-2">{{ event.description || 'No description' }}</p>
                        <span class="inline-flex mt-3 px-3 py-1 rounded-lg text-[9px] font-bold uppercase tracking-widest border" :class="getStatusStyles(event.status || asset.status)">
                            {{ event.status || 'Event' }}
                        </span>
                    </div>
                    <div v-if="!timeline || !timeline.length" class="py-10 text-center text-slate-400 text-[11px] font-bold uppercase tracking-widest">
                        No timeline events yet
                    </div>
                </div>
            </section>
        </div>

        <PremiumModal :show="showServiceModal" @close="showServiceModal = false" title="Add Maintenance Log" subtitle="Record maintenance details for this asset">
            <form @submit.prevent="submitService" class="p-6 space-y-5 text-left">
                <div>
                    <InputLabel value="Type" />
                    <BaseSelect v-model="serviceForm.type">
                        <option value="Repair">Repair</option>
                        <option value="Upgrade">Upgrade</option>
                        <option value="Routine_Service">Routine Service</option>
                    </BaseSelect>
                    <InputError :message="serviceForm.errors.type" />
                </div>
                <div>
                    <InputLabel value="Description" />
                    <textarea v-model="serviceForm.description" rows="3" class="w-full rounded-xl border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <InputError :message="serviceForm.errors.description" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Cost" />
                        <TextInput v-model="serviceForm.cost" type="number" min="0" step="0.01" />
                        <InputError :message="serviceForm.errors.cost" />
                    </div>
                    <div>
                        <InputLabel value="Service Date" />
                        <TextInput v-model="serviceForm.service_date" type="date" />
                        <InputError :message="serviceForm.errors.service_date" />
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showServiceModal = false" class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-500">Cancel</button>
                    <button type="submit" :disabled="serviceForm.processing" class="h-10 px-5 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest">
                        {{ serviceForm.processing ? 'Saving...' : 'Save Maintenance' }}
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showEditModal" @close="showEditModal = false" title="Edit Asset" subtitle="Update core asset details">
            <form @submit.prevent="submitEdit" class="p-6 space-y-5 text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Name" />
                        <TextInput v-model="editForm.name" />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Category" />
                        <BaseSelect v-model="editForm.category_id">
                            <option value="">Select Category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </BaseSelect>
                        <InputError :message="editForm.errors.category_id" />
                    </div>
                    <div>
                        <InputLabel value="Serial Number" />
                        <TextInput v-model="editForm.serial_number" />
                        <InputError :message="editForm.errors.serial_number" />
                    </div>
                    <div>
                        <InputLabel value="Status" />
                        <BaseSelect v-model="editForm.status">
                            <option value="Available">Available</option>
                            <option value="Assigned">Assigned</option>
                            <option value="In_Service">In Service</option>
                            <option value="Scrapped">Scrapped</option>
                            <option value="Lost">Lost</option>
                            <option value="Draft">Draft</option>
                        </BaseSelect>
                        <InputError :message="editForm.errors.status" />
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false" class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-500">Cancel</button>
                    <button type="submit" :disabled="editForm.processing" class="h-10 px-5 bg-slate-900 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest">
                        {{ editForm.processing ? 'Updating...' : 'Update Asset' }}
                    </button>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :show="showAssignModal" @close="showAssignModal = false" title="Assign Asset" subtitle="Select user to hand over this asset">
            <form @submit.prevent="submitAssign" class="p-6 space-y-5 text-left">
                <div>
                    <InputLabel value="Assign To" />
                    <BaseSelect v-model="assignForm.user_id">
                        <option value="">Select User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </BaseSelect>
                    <InputError :message="assignForm.errors.user_id" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAssignModal = false" class="h-10 px-4 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-500">Cancel</button>
                    <button type="submit" :disabled="assignForm.processing" class="h-10 px-5 bg-indigo-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest">
                        {{ assignForm.processing ? 'Assigning...' : 'Assign Asset' }}
                    </button>
                </div>
            </form>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
