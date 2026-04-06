<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    queue: Object, // { items: [], expiring_vendors: [] }
    registry: Object, // Paginated
    stats: Object,
    templates: Array
});

const tab = ref('queue'); // queue, designer, registry, assets, external
const tabs = [
    { id: 'queue', label: 'Production Queue', icon: 'fas fa-inbox' },
    { id: 'designer', label: 'Card Designer', icon: 'fas fa-palette' },
    { id: 'assets', label: 'Asset Management', icon: 'fas fa-images' },
    { id: 'registry', label: 'Registry & Validation', icon: 'fas fa-shield-alt' },
    { id: 'external', label: 'Vendor Specifics', icon: 'fas fa-briefcase' }
];

const selectedQueueItems = ref([]);
const selectedTemplateId = ref(null);
const isSettlingDefault = ref(false);

const bulkUploadForm = useForm({
    images: []
});

const individualUploadForm = useForm({
    image: null
});

const handleFileSelect = (e) => {
    bulkUploadForm.images = Array.from(e.target.files);
};

const handleIndividualUpload = (e, employeeId) => {
    const file = e.target.files[0];
    if (!file) return;

    individualUploadForm.image = file;
    individualUploadForm.post(route('identity.assets.individual-upload', employeeId), {
        onSuccess: () => {
            individualUploadForm.reset();
            alert('Profile photo updated.');
        }
    });
};

const submitBulkUpload = () => {
    bulkUploadForm.post(route('identity.assets.bulk-upload'), {
        onSuccess: () => {
            bulkUploadForm.reset();
            alert('Bulk upload completed.');
        }
    });
};

const selectedTemplate = computed(() => {
    return props.templates?.find(t => t.id === selectedTemplateId.value);
});

const initializeCards = () => {
    if (!selectedQueueItems.value.length) return;
    
    router.post(route('identity.batch-store'), {
        type: 'Employee',
        personnel: selectedQueueItems.value,
        template_id: selectedTemplateId.value
    }, {
        onSuccess: () => {
            selectedQueueItems.value = [];
        }
    });
};

const advanceLifecycle = (item, nextStatus) => {
    if (!item.card_id) {
        alert('Initialize card first.');
        return;
    }
    router.put(route('identity.update-status', item.card_id), {
        status: nextStatus
    });
};

const revokeCard = (id) => {
    if (confirm('Are you sure? This will immediately invalidate the QR code.')) {
        router.post(route('identity.revoke', id));
    }
};

const printCard = (id) => {
    router.post(route('identity.batch-print'), {
        cards: [id],
        template_id: selectedTemplateId.value
    });
};

const batchPrint = () => {
    const activeCardIds = props.registry.data
        .filter(c => c.status === 'Active')
        .map(c => c.id);

    if (!activeCardIds.length) {
        alert('No active cards to print on this page.');
        return;
    }

    router.post(route('identity.batch-print'), {
        cards: activeCardIds,
        template_id: selectedTemplateId.value
    });
};

const setDefaultTemplate = (id) => {
    isSettlingDefault.value = true;
    router.post(route('id-card.templates.default', id), {}, {
        onFinish: () => isSettlingDefault.value = false
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Draft': return 'bg-slate-100 text-slate-600 border-slate-200';
        case 'Pending Photo': return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'In Production': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'Active': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        default: return 'bg-rose-50 text-rose-700 border-rose-200';
    }
};

// Live Preview Logic
import { onMounted, watch } from 'vue';
import { StaticCanvas, FabricImage } from 'fabric';

const previewCanvas = ref(null);
const activePreviewSide = ref('front');
let fabricPreview = null;

// Registry 3D Preview State
import Preview3D from './Studio/Partials/Preview3D.vue';
const show3DModal = ref(false);
const frontCardImg = ref('');
const backCardImg = ref('');
const previewOrientation = ref('Landscape');
const isGenerating3D = ref(false);

const updateLivePreview = async () => {
    if (!selectedTemplate.value || !previewCanvas.value) return;
    
    // Use the first person in queue as the preview model
    const person = props.queue.items[0] || { 
        name: 'Sample User', 
        department: 'Executive', 
        employee_code: 'EMP-001',
        employee: {
            employee_code: 'EMP-001',
            blood_group: 'O+',
            joining_date: '2024-01-01',
            email: 'sample@company.com',
            phone: '+1 555-001',
            dob: '1990-05-15',
            emergency_contact_name: 'Contact A',
            emergency_contact_phone: '999-001'
        }
    };

    const resolveValue = (binding, p) => {
        if (!binding) return null;
        const map = {
            'user.name': p.name,
            'user.designation': p.department,
            'user.department': p.department,
            'user.employee_id': p.employee_code || p.employee?.employee_code,
            'user.blood_group': p.employee?.blood_group || 'N/A',
            'user.joining_date': p.employee?.joining_date || 'N/A',
            'user.email': p.employee?.email || 'N/A',
            'user.phone': p.employee?.phone || 'N/A',
            'user.dob': p.employee?.dob || 'N/A',
            'user.emergency_name': p.employee?.emergency_contact_name || 'N/A',
            'user.emergency_phone': p.employee?.emergency_contact_phone || 'N/A',
            'user.address': p.employee?.address || 'City, Country'
        };
        return map[binding] || null;
    };

    if (fabricPreview) fabricPreview.dispose();
    
    fabricPreview = new StaticCanvas(previewCanvas.value, {
        width: selectedTemplate.value.dimensions?.width || 1011,
        height: selectedTemplate.value.dimensions?.height || 638,
        backgroundColor: '#ffffff'
    });

    try {
        const design = selectedTemplate.value.design_data;
        const sideData = activePreviewSide.value === 'front' ? design.front : design.back;
        const json = typeof sideData === 'string' ? JSON.parse(sideData) : sideData;
        
        if (!json) {
             fabricPreview.clear();
             return;
        }

        await fabricPreview.loadFromJSON(json);

        // Populate details
        fabricPreview.getObjects().forEach(obj => {
            if (obj.type === 'i-text' || obj.type === 'text') {
                const boundVal = resolveValue(obj.data_binding, person);
                if (boundVal) {
                    obj.set('text', boundVal);
                } else {
                    let txt = obj.text;
                    txt = txt.replace(/\{\{\s*Name\s*\}\}/gi, person.name);
                    txt = txt.replace(/\{\{\s*Designation\s*\}\}/gi, person.department);
                    txt = txt.replace(/\{\{\s*ID Number\s*\}\}/gi, person.employee_code);
                    obj.set('text', txt);
                }
            }
            if (obj.data_binding === 'photo_placeholder') {
                obj.set('visible', true); // In real production, we swap with image
            }
        });
        
        fabricPreview.renderAll();
    } catch (e) {
        console.error('Preview Render Fail:', e);
    }
};

watch([selectedTemplateId, activePreviewSide], () => {
    setTimeout(updateLivePreview, 100);
});

watch(tab, (newTab) => {
    if (newTab === 'designer') setTimeout(updateLivePreview, 200);
});

const generateCardImages = async (card) => {
    isGenerating3D.value = true;
    const template = card.template || props.templates.find(t => t.id === card.template_id);
    if (!template) {
        alert('Design not found for this personnel token.');
        isGenerating3D.value = false;
        return;
    }

    previewOrientation.value = template.orientation || 'Landscape';
    const dims = template.dimensions || { width: 1011, height: 638 };
    
    const staticF = new StaticCanvas(null, { width: dims.width, height: dims.height });
    
        // Logic to populate a person
        const populate = async (side) => {
            const data = template.design_data[side];
            if (!data) return '';
            const json = typeof data === 'string' ? JSON.parse(data) : data;
            await staticF.loadFromJSON(json);
            
            const p = card.user || {};
            const personData = {
                name: card.details?.name || p.name,
                department: card.details?.role || p.employee?.department || 'Staff',
                employee_code: card.card_number,
                employee: p.employee || {}
            };

            staticF.getObjects().forEach(obj => {
                if (obj.type === 'i-text' || obj.type === 'text') {
                    const boundVal = resolveValue(obj.data_binding, personData);
                    if (boundVal) {
                         obj.set('text', boundVal);
                    } else {
                        let txt = obj.text;
                        txt = txt.replace(/\{\{\s*Name\s*\}\}/gi, personData.name);
                        txt = txt.replace(/\{\{\s*Designation\s*\}\}/gi, personData.department);
                        txt = txt.replace(/\{\{\s*ID Number\s*\}\}/gi, personData.employee_code);
                        obj.set('text', txt);
                    }
                }
            });
        
        const url = staticF.toDataURL({ format: 'png', multiplier: 0.5 });
        staticF.clear();
        return url;
    };

    frontCardImg.value = await populate('front');
    backCardImg.value = await populate('back');
    
    staticF.dispose();
    isGenerating3D.value = false;
    show3DModal.value = true;
};
</script>

<template>
    <Head title="Identity & Access" />

    <div class="flex flex-col h-screen overflow-hidden bg-white font-outfit">
        <!-- Structural Header -->
        <div class="px-6 py-4 flex-shrink-0 z-10 border-b border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-id-card text-emerald-400"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">Identity & Access Management</h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none">Lifecycle & Credential Fabrication</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="px-4 py-2 bg-slate-900 text-emerald-400 rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-slate-200">
                        Operational: {{ stats.active }} Active | {{ stats.pending }} In-Flow
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6 -mb-4 pt-4">
                <button 
                    v-for="t in tabs" 
                    :key="t.id"
                    @click="tab = t.id"
                    class="pb-3 text-sm font-black uppercase tracking-widest border-b-2 transition-all flex items-center gap-2"
                    :class="tab === t.id ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-400 hover:text-slate-600'"
                >
                    <i :class="t.icon" class="text-sm"></i>
                    {{ t.label }}
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
            
            <!-- Tab 1: Queue -->
            <div v-if="tab === 'queue'" class="space-y-6 animate-fade-in max-w-7xl mx-auto">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex justify-between items-center mb-5">
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Production & Lifecycle Queue</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Status: Mixed (Manual Override Available)</p>
                        </div>
                        <div class="flex gap-3">
                            <button @click="initializeCards" :disabled="!selectedQueueItems.length" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-slate-200 hover:bg-emerald-600 disabled:opacity-50 transition-all">
                                Initialize Selected ({{ selectedQueueItems.length }})
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="queue.items.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                             <thead>
                                 <tr class="bg-slate-50/50">
                                     <th class="px-4 py-3 w-10"></th>
                                     <th class="px-4 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Personnel</th>
                                     <th class="px-4 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Department</th>
                                     <th class="px-4 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Lifecycle Status</th>
                                     <th class="px-4 py-3 text-right text-sm font-black text-slate-400 uppercase tracking-widest">Protocol Actions</th>
                                 </tr>
                             </thead>
                             <tbody class="divide-y divide-slate-50">
                                 <tr v-for="item in queue.items" :key="item.id" class="hover:bg-slate-50 transition-colors group">
                                     <td class="px-4 py-3">
                                         <input v-if="!item.card_id" type="checkbox" :value="item.id" v-model="selectedQueueItems" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                         <div v-else class="w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center">
                                             <i class="fas fa-check text-xs text-emerald-600"></i>
                                         </div>
                                     </td>
                                     <td class="px-4 py-3">
                                         <div class="flex items-center gap-3">
                                             <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 relative group/avatar">
                                                 <img v-if="item.avatar" :src="`/storage/${item.avatar}`" class="w-full h-full object-cover">
                                                 <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                                                     <i class="fas fa-user text-xs"></i>
                                                 </div>
                                                 
                                                 <!-- Individual Upload Overlay -->
                                                 <label :for="`upload-${item.employee_id}`" class="absolute inset-0 bg-slate-900/60 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity cursor-pointer">
                                                     <i class="fas fa-camera text-sm text-white"></i>
                                                     <input type="file" :id="`upload-${item.employee_id}`" class="hidden" @change="(e) => handleIndividualUpload(e, item.employee_id)">
                                                 </label>

                                                 <div v-if="item.action_required" class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 rounded-full border-2 border-white animate-ping"></div>
                                             </div>
                                             <div>
                                                 <div class="text-base font-black text-slate-700 uppercase tracking-tight">{{ item.name }}</div>
                                                 <div class="text-sm font-bold text-slate-400 uppercase tabular-nums tracking-tighter">{{ item.employee_code }}</div>
                                             </div>
                                         </div>
                                     </td>
                                     <td class="px-4 py-3">
                                         <span class="text-sm font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded">{{ item.department }}</span>
                                     </td>
                                     <td class="px-4 py-3">
                                         <span class="px-2 py-1 text-xs font-black uppercase rounded border transition-all" :class="getStatusColor(item.status)">
                                             {{ item.status }}
                                         </span>
                                         <p v-if="item.action_required" class="text-xs font-black text-rose-600 uppercase mt-1 tracking-widest">Action Required: Missing Photo</p>
                                     </td>
                                     <td class="px-4 py-3 text-right">
                                         <div class="flex justify-end gap-2 text-right items-center">
                                             <button v-if="item.status === 'Draft' && !item.action_required" @click="advanceLifecycle(item, 'In Production')" class="px-3 py-1.5 bg-slate-900 text-emerald-400 rounded-lg text-sm font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">Push to Prod</button>
                                             <button v-if="item.status === 'In Production'" @click="advanceLifecycle(item, 'Active')" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-sm">Activate</button>
                                             
                                             <div v-if="item.status === 'Pending Photo' || item.action_required" class="flex flex-col items-end">
                                                 <label :for="`inline-upload-${item.employee_id}`" class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-sm font-black uppercase tracking-widest cursor-pointer hover:bg-amber-600 transition-all shadow-md shadow-amber-200/50 flex items-center gap-2">
                                                     <i class="fas fa-upload text-xs"></i>
                                                     Upload Photo
                                                     <input type="file" :id="`inline-upload-${item.employee_id}`" class="hidden" @change="(e) => handleIndividualUpload(e, item.employee_id)">
                                                 </label>
                                             </div>
                                         </div>
                                     </td>
                                 </tr>
                             </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-slate-300 text-sm font-black uppercase tracking-[0.2em] border-2 border-dashed border-slate-100 rounded-xl">Queue Clear</div>
                </div>
            </div>

            <!-- Tab 3: Assets (Bulk Upload) -->
            <div v-if="tab === 'assets'" class="animate-fade-in max-w-4xl mx-auto">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
                    <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 mx-auto mb-6">
                        <i class="fas fa-cloud-upload-alt text-3xl"></i>
                    </div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Bulk Profile Asset Synchronization</h3>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2 mb-8">Naming Convention: EMP_CODE.jpg (e.g. EMP001.jpg)</p>

                    <form @submit.prevent="submitBulkUpload" class="space-y-6">
                        <div class="border-2 border-dashed border-slate-200 rounded-3xl p-12 transition-all hover:border-emerald-500/50 group bg-slate-50/50">
                            <input type="file" multiple @change="handleFileSelect" class="hidden" id="asset-upload">
                            <label for="asset-upload" class="cursor-pointer block">
                                <span class="text-base font-black text-slate-600 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">Select Data Packets</span>
                                <p class="text-sm text-slate-400 font-bold uppercase mt-2">JPEG, PNG up to 2MB per entity</p>
                            </label>
                            
                            <div v-if="bulkUploadForm.images.length" class="mt-6 flex flex-wrap justify-center gap-2">
                                <div v-for="img in bulkUploadForm.images.slice(0, 10)" :key="img.name" class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs font-black text-slate-500 uppercase">
                                    {{ img.name }}
                                </div>
                                <div v-if="bulkUploadForm.images.length > 10" class="px-3 py-1 bg-slate-900 text-white rounded-full text-xs font-black uppercase">
                                    +{{ bulkUploadForm.images.length - 10 }} More
                                </div>
                            </div>
                        </div>

                        <button type="submit" :disabled="bulkUploadForm.processing || !bulkUploadForm.images.length" class="w-full py-4 bg-slate-900 text-emerald-400 rounded-2xl text-base font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-emerald-600 hover:text-white disabled:opacity-50 transition-all flex items-center justify-center gap-3">
                            <i v-if="!bulkUploadForm.processing" class="fas fa-bolt"></i>
                            <i v-else class="fas fa-spinner fa-spin"></i>
                            {{ bulkUploadForm.processing ? 'Syncing Network...' : 'Commence Global Branding Sync' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tab 2: Designer (Selection & Live Preview) -->
            <div v-if="tab === 'designer'" class="animate-fade-in grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-7xl mx-auto">
                <div class="flex flex-col gap-6">
                    <div class="bg-slate-900 rounded-[2.5rem] p-10 flex flex-col items-center justify-center min-h-[450px] shadow-2xl relative overflow-hidden group border border-white/5">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-indigo-500/10 opacity-50"></div>
                        <div class="relative z-10 text-center flex flex-col items-center">
                            <div class="w-20 h-20 bg-emerald-500 text-white rounded-[2rem] flex items-center justify-center mb-8 shadow-[0_20px_50px_-15px_rgba(16,185,129,0.5)] group-hover:scale-110 transition-transform">
                                <i class="fas fa-microchip text-3xl"></i>
                            </div>
                            <Link :href="route('id-card.studio')" class="px-10 py-5 bg-white text-slate-900 rounded-2xl font-black text-lg uppercase tracking-[0.2em] shadow-2xl hover:bg-emerald-500 title-hover hover:text-white transition-all flex items-center gap-4">
                                Launch Fabrication Studio
                                <i class="fas fa-bolt text-emerald-400"></i>
                            </Link>
                            <p class="mt-6 text-slate-500 text-xs font-black uppercase tracking-[0.4em] opacity-60">Neural Design Environment v5.0</p>
                        </div>
                    </div>

                    <!-- Template Hub -->
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Active Schematics</h3>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Topology Registry</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black uppercase text-slate-400 border border-slate-200">
                                    {{ templates.length }} Blueprints
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            <div 
                                v-for="t in templates" 
                                :key="t.id"
                                @click="selectedTemplateId = t.id"
                                class="group flex items-center justify-between p-4 rounded-2xl cursor-pointer border transition-all"
                                :class="selectedTemplateId === t.id ? 'bg-slate-900 border-slate-800 text-white shadow-2xl shadow-slate-900/20' : 'bg-slate-50 border-slate-100 hover:border-emerald-200 hover:bg-white'"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-white overflow-hidden flex items-center justify-center border border-slate-100">
                                        <img v-if="t.preview_image" :src="t.preview_url" class="w-full h-full object-cover">
                                        <i v-else class="fas fa-layer-group text-slate-200"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black uppercase tracking-tight">{{ t.name }}</div>
                                        <div class="text-[10px] font-black opacity-50 uppercase tracking-widest mt-0.5">{{ t.type }} Matrix</div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <div v-if="t.is_default" class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded text-[9px] font-black uppercase">Standard Default</div>
                                    <button 
                                        v-else 
                                        @click.stop="setDefaultTemplate(t.id)"
                                        class="opacity-0 group-hover:opacity-100 px-3 py-1 bg-white/10 hover:bg-emerald-500 border border-white/20 rounded-lg text-[9px] font-black uppercase text-white transition-all"
                                    >
                                        Set Default
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-6">
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 flex-1 p-8 flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                             <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Production Output Preview</h3>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Personnel Data Injection Check</p>
                            </div>
                            <div v-if="selectedTemplate" class="flex items-center gap-4">
                                <div class="flex p-1 bg-slate-900/5 rounded-xl border border-slate-900/10">
                                    <button v-for="side in ['front', 'back']" :key="side"
                                            @click="activePreviewSide = side"
                                            class="px-4 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all"
                                            :class="activePreviewSide === side ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                                        {{ side }}
                                    </button>
                                </div>
                                <div class="text-right border-l border-slate-100 pl-4">
                                    <span class="text-[10px] font-black text-slate-300 uppercase block leading-none">Status</span>
                                    <span class="text-sm font-bold text-emerald-500 leading-none">1:1 LIVE</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 flex items-center justify-center bg-slate-50/50 rounded-[2rem] border-2 border-dashed border-slate-100 relative overflow-hidden p-6 min-h-[400px]">
                            <div v-if="!selectedTemplate" class="text-center opacity-40">
                                <i class="fas fa-id-card-alt text-6xl text-slate-200 mb-6 block"></i>
                                <p class="text-sm font-black text-slate-300 uppercase tracking-[0.4em]">Initialize Topology Selection</p>
                            </div>
                            <div v-else class="relative w-full flex justify-center">
                                <div class="relative shadow-[0_50px_100px_-20px_rgba(0,0,0,0.15)] rounded-2xl overflow-hidden ring-1 ring-slate-200">
                                    <div class="origin-top-left transform scale-[0.4] sm:scale-[0.5] md:scale-[0.6] lg:scale-[0.45] xl:scale-[0.55]">
                                        <canvas ref="previewCanvas"></canvas>
                                    </div>
                                </div>
                                
                                <div class="absolute -bottom-4 right-4 bg-slate-900 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-2xl flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Live Matrix Stream
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedTemplate" class="mt-8 grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Topology Profile</span>
                                <div class="text-sm font-black text-slate-700 mt-1">{{ selectedTemplate.name }}</div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Dimensions (DPI 300)</span>
                                <div class="text-sm font-black text-slate-700 mt-1">{{ selectedTemplate.dimensions?.width }} x {{ selectedTemplate.dimensions?.height }}px</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Registry -->
            <div v-if="tab === 'registry'" class="animate-fade-in max-w-7xl mx-auto">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                      <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                          <div>
                              <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Access Token Registry</h3>
                              <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Ready for Physical Output</p>
                          </div>
                          <button @click="batchPrint" class="text-sm font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-widest">Generate Batch Output</button>
                      </div>
                      <table class="min-w-full divide-y divide-slate-100">
                          <thead class="bg-slate-50/50">
                              <tr>
                                  <th class="px-6 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Token ID</th>
                                  <th class="px-6 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Class</th>
                                  <th class="px-6 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Personnel</th>
                                  <th class="px-6 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Template</th>
                                  <th class="px-6 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Status</th>
                                  <th class="px-6 py-4 text-right text-sm font-black text-slate-400 uppercase tracking-widest">Actions</th>
                              </tr>
                          </thead>
                          <tbody class="divide-y divide-slate-50">
                              <tr v-for="card in registry.data" :key="card.id" class="hover:bg-slate-50 transition-colors group">
                                  <td class="px-6 py-4 font-mono text-base font-bold text-slate-400 tracking-tighter">{{ card.card_number }}</td>
                                  <td class="px-6 py-4">
                                      <span class="px-2 py-0.5 text-xs font-black uppercase rounded border bg-slate-900 text-emerald-400 border-emerald-500/20">
                                          {{ card.type }}
                                      </span>
                                  </td>
                                  <td class="px-6 py-4">
                                      <div class="flex items-center gap-3">
                                          <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0">
                                              <img v-if="card.user?.employee?.avatar" :src="`/storage/${card.user.employee.avatar}`" class="w-full h-full object-cover shadow-inner">
                                              <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                                                  <i class="fas fa-user text-xs"></i>
                                              </div>
                                          </div>
                                          <div class="text-base font-black text-slate-700 uppercase tracking-tight group-hover:text-emerald-700">
                                              {{ card.details.name || 'Unknown Entity' }}
                                          </div>
                                      </div>
                                  </td>
                                  <td class="px-6 py-4">
                                      <div class="flex items-center gap-2">
                                          <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-ellipsis overflow-hidden max-w-[120px]">{{ card.template?.name || 'Standard' }}</div>
                                          <div v-if="card.template?.is_default" class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></div>
                                      </div>
                                  </td>
                                  <td class="px-6 py-4">
                                      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-black uppercase tracking-widest bg-emerald-50 text-emerald-700">
                                          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                          {{ card.status }}
                                      </span>
                                  </td>
                                   <td class="px-6 py-4 text-right flex items-center justify-end gap-3">
                                       <button @click="generateCardImages(card)" :disabled="isGenerating3D" class="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all flex items-center justify-center border border-transparent hover:border-indigo-200" title="3D Virtual Render">
                                            <i :class="['fas', isGenerating3D ? 'fa-spinner fa-spin' : 'fa-cube', 'text-xs']"></i>
                                       </button>
                                       <button @click="printCard(card.id)" class="w-8 h-8 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all flex items-center justify-center border border-transparent hover:border-emerald-200">
                                            <i class="fas fa-print text-xs"></i>
                                       </button>
                                       <button @click="revokeCard(card.id)" class="px-4 py-1.5 text-sm font-black text-rose-600 uppercase tracking-widest border border-rose-100 bg-rose-50/50 rounded-lg hover:bg-rose-600 hover:text-white transition-all">
                                           Purge
                                       </button>
                                   </td>
                              </tr>
                          </tbody>
                      </table>
                 </div>
            </div>

        </div>

        <!-- 3D Preview Modal -->
        <Preview3D 
            :isOpen="show3DModal" 
            :frontImage="frontCardImg" 
            :backImage="backCardImg" 
            :orientation="previewOrientation"
            @close="show3DModal = false" 
        />
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
