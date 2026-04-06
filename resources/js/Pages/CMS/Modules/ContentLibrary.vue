<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Media Library</h2>
                <p class="text-xs text-gray-500 mt-0.5">Upload, organise, and insert images, videos, and documents.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input v-model="search" type="text" placeholder="Search media..." class="w-52 bg-gray-50 border border-gray-200 rounded-xl py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-indigo-400" />
                </div>
                <!-- Type filter -->
                <div class="flex bg-gray-100 rounded-xl p-0.5 gap-0.5">
                    <button v-for="f in typeFilters" :key="f.val" @click="typeFilter = f.val"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="typeFilter === f.val ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                        {{ f.label }}
                    </button>
                </div>
                <!-- Upload button -->
                <label class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload
                    <input type="file" multiple accept="image/*,video/*,.pdf,.doc,.docx" class="hidden" @change="handleUpload" />
                </label>
            </div>
        </div>

        <!-- Drop zone overlay -->
        <div v-if="isDragging" class="fixed inset-0 bg-indigo-600/20 backdrop-blur-sm z-50 flex items-center justify-center"
            @dragover.prevent @drop.prevent="handleDrop" @dragleave="isDragging = false">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-dashed border-indigo-400 p-16 text-center">
                <i class="fas fa-cloud-upload-alt text-5xl text-indigo-500 mb-4"></i>
                <p class="text-lg font-black text-indigo-700">Drop files to upload</p>
                <p class="text-sm text-gray-500 mt-1">Images, videos, PDFs, documents</p>
            </div>
        </div>

        <!-- Upload progress -->
        <div v-if="uploads.length" class="px-8 py-3 bg-white border-b border-gray-200 flex gap-3 overflow-x-auto shrink-0">
            <div v-for="u in uploads" :key="u.name" class="flex items-center gap-2 px-3 py-2 bg-indigo-50 border border-indigo-200 rounded-xl shrink-0">
                <i class="fas fa-spinner fa-spin text-indigo-500 text-xs"></i>
                <span class="text-xs font-bold text-indigo-700 max-w-[120px] truncate">{{ u.name }}</span>
                <div class="w-16 h-1 bg-indigo-200 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full transition-all" :style="{width: u.progress + '%'}"></div>
                </div>
            </div>
        </div>

        <!-- Storage stats -->
        <div class="px-8 py-3 border-b border-gray-100 bg-white flex items-center gap-6 shrink-0">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fas fa-database text-indigo-400"></i>
                <span class="font-bold">{{ mediaList.length }} files</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fas fa-image text-emerald-400"></i>
                <span class="font-bold">{{ mediaList.filter(m => m.file_type === 'image').length }} images</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fas fa-video text-purple-400"></i>
                <span class="font-bold">{{ mediaList.filter(m => m.file_type === 'video').length }} videos</span>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <!-- View toggle -->
                <button v-for="v in ['grid','list']" :key="v" @click="viewMode = v"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                    :class="viewMode === v ? 'bg-indigo-100 text-indigo-600' : 'text-gray-400 hover:bg-gray-100'">
                    <i :class="v === 'grid' ? 'fas fa-th' : 'fas fa-list'" class="text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Media Grid -->
        <div class="flex-1 overflow-y-auto p-6"
            @dragover.prevent="isDragging = true"
            @dragleave.self="isDragging = false">

            <!-- GRID VIEW -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-4 xl:grid-cols-6 gap-3">
                <div v-for="item in filteredMedia" :key="item.id"
                    @click="selectMedia(item)"
                    class="group relative rounded-2xl overflow-hidden bg-white border-2 aspect-square cursor-pointer transition-all hover:shadow-md"
                    :class="selectedIds.includes(item.id) ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <!-- Image -->
                    <img v-if="item.file_type === 'image'" :src="item.url" class="w-full h-full object-cover" :alt="item.alt" />
                    <!-- Video -->
                    <div v-else-if="item.file_type === 'video'" class="w-full h-full bg-purple-50 flex flex-col items-center justify-center">
                        <i class="fas fa-video text-2xl text-purple-300 mb-1"></i>
                        <p class="text-sm text-purple-400 font-bold">VIDEO</p>
                    </div>
                    <!-- Document -->
                    <div v-else class="w-full h-full bg-amber-50 flex flex-col items-center justify-center">
                        <i class="fas fa-file-alt text-2xl text-amber-300 mb-1"></i>
                        <p class="text-sm text-amber-400 font-bold uppercase">{{ item.mime_type?.split('/')[1] }}</p>
                    </div>
                    <!-- Hover overlay -->
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-end">
                        <div class="w-full px-2 py-1.5 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-all">
                            <p class="text-white text-sm font-bold truncate">{{ item.file_name }}</p>
                            <p class="text-white/60 text-xs">{{ formatSize(item.file_size) }}</p>
                        </div>
                    </div>
                    <!-- Selected check -->
                    <div v-if="selectedIds.includes(item.id)" class="absolute top-1.5 right-1.5 w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center shadow">
                        <i class="fas fa-check text-white text-xs"></i>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="!filteredMedia.length" class="col-span-full flex flex-col items-center justify-center py-24 text-gray-400">
                    <i class="fas fa-photo-video text-4xl text-gray-200 mb-4"></i>
                    <p class="font-bold text-gray-500">No media files</p>
                    <p class="text-sm mt-1">Drag files here or click Upload.</p>
                </div>
            </div>

            <!-- LIST VIEW -->
            <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-500 w-10"><input type="checkbox" @change="toggleAll" /></th>
                            <th class="py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-500">File</th>
                            <th class="py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-500">Type</th>
                            <th class="py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-500">Size</th>
                            <th class="py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-500">Date</th>
                            <th class="py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in filteredMedia" :key="item.id" class="hover:bg-gray-50 group">
                            <td class="py-2.5 px-4"><input type="checkbox" :checked="selectedIds.includes(item.id)" @change="selectMedia(item)" class="accent-indigo-600" /></td>
                            <td class="py-2.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                                        <img v-if="item.file_type === 'image'" :src="item.url" class="w-full h-full object-cover" />
                                        <i v-else :class="item.file_type === 'video' ? 'fas fa-video text-purple-400' : 'fas fa-file-alt text-amber-400'" class="text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900 truncate max-w-[200px]">{{ item.file_name }}</p>
                                        <p class="text-sm text-gray-400">{{ item.alt || 'No alt text' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-4"><span class="text-sm font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 uppercase">{{ item.file_type }}</span></td>
                            <td class="py-2.5 px-4 text-xs text-gray-600">{{ formatSize(item.file_size) }}</td>
                            <td class="py-2.5 px-4 text-xs text-gray-500">{{ new Date(item.created_at).toLocaleDateString('en-IN') }}</td>
                            <td class="py-2.5 px-4">
                                <div class="flex justify-end gap-1">
                                    <a :href="item.url" target="_blank" class="action-btn hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50"><i class="fas fa-external-link-alt text-sm"></i></a>
                                    <button @click="copyUrl(item)" class="action-btn hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50"><i class="fas fa-link text-sm"></i></button>
                                    <button @click="deleteMedia(item)" class="action-btn hover:text-red-600 hover:border-red-200 hover:bg-red-50"><i class="fas fa-trash text-sm"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom action bar when items selected -->
        <div v-if="selectedIds.length" class="h-12 bg-indigo-600 text-white flex items-center justify-between px-8 shrink-0">
            <p class="text-sm font-bold">{{ selectedIds.length }} selected</p>
            <div class="flex gap-2">
                <button @click="bulkDelete" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-bold hover:bg-red-600">
                    <i class="fas fa-trash mr-1"></i>Delete Selected
                </button>
                <button @click="selectedIds = []" class="px-3 py-1.5 bg-white/20 text-white rounded-lg text-xs font-bold hover:bg-white/30">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ media: { type: Array, default: () => [] } });
const mediaList  = ref([...(props.media || [])]);
const search     = ref('');
const typeFilter = ref('all');
const viewMode   = ref('grid');
const selectedIds = ref([]);
const uploads    = ref([]);
const isDragging = ref(false);

const typeFilters = [
    { val:'all', label:'All' }, { val:'image', label:'Images' }, { val:'video', label:'Videos' }, { val:'document', label:'Docs' },
];

const filteredMedia = computed(() => {
    let list = mediaList.value;
    if (typeFilter.value !== 'all') list = list.filter(m => m.file_type === typeFilter.value);
    if (search.value) list = list.filter(m => m.file_name?.toLowerCase().includes(search.value.toLowerCase()) || m.alt?.toLowerCase().includes(search.value.toLowerCase()));
    return list;
});

const formatSize = (bytes) => {
    if (!bytes) return '—';
    if (bytes < 1024) return bytes + 'B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + 'KB';
    return (bytes / 1048576).toFixed(1) + 'MB';
};

const selectMedia = (item) => {
    const idx = selectedIds.value.indexOf(item.id);
    if (idx === -1) selectedIds.value.push(item.id);
    else selectedIds.value.splice(idx, 1);
};

const toggleAll = (e) => {
    selectedIds.value = e.target.checked ? filteredMedia.value.map(m => m.id) : [];
};

const copyUrl = (item) => {
    navigator.clipboard?.writeText(item.url);
};

const uploadFiles = async (files) => {
    for (const file of files) {
        const entry = { name: file.name, progress: 0 };
        uploads.value.push(entry);
        const fd = new FormData();
        fd.append('file', file);
        try {
            const { data } = await axios.post(route('cms.media.store'), fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (e) => { entry.progress = Math.round(e.loaded / e.total * 100); },
            });
            mediaList.value.unshift(data);
        } catch (e) { console.error('Upload failed', file.name); }
        finally { uploads.value = uploads.value.filter(u => u !== entry); }
    }
};

const handleUpload = (e) => uploadFiles(Array.from(e.target.files));
const handleDrop   = (e) => { isDragging.value = false; uploadFiles(Array.from(e.dataTransfer.files)); };

const deleteMedia = async (item) => {
    if (!confirm(`Delete "${item.file_name}"?`)) return;
    try { await axios.delete(route('cms.media.destroy', item.id)); mediaList.value = mediaList.value.filter(m => m.id !== item.id); } catch {}
};

const bulkDelete = async () => {
    if (!confirm(`Delete ${selectedIds.value.length} files?`)) return;
    try {
        await axios.post(route('cms.media.bulk-delete'), { ids: selectedIds.value });
        mediaList.value = mediaList.value.filter(m => !selectedIds.value.includes(m.id));
        selectedIds.value = [];
    } catch {}
};
</script>

<style scoped>
.action-btn { width:1.6rem; height:1.6rem; display:flex; align-items:center; justify-content:center; border-radius:0.4rem; border:1px solid #e5e7eb; background:#fff; color:#9ca3af; transition:all 0.15s; cursor:pointer; }
</style>
