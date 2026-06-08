
<template>
     <Head title="Document Templates" />

    <AdminLayout>
            

            <GradientHeroHeader kicker="" title="Document Templates"
                subtitle="Manage templates for offers, letters, and payslips.">
                <template #right>
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <Link :href="route('admin.document-templates.create')"
                        class="h-12 px-2 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-1 cursor-pointer active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v14m-7-7h14" />
                        </svg>
                        <span>Add Template</span>
                    </Link>
                    </div>
                </template>
            </GradientHeroHeader>

        <div class="p-6">

            <div
                class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative z-10 transition-all duration-700">
                <BaseDataTable :columns="columns" :data="templates.data" :meta="templates" @page-change="onPageChange"
                    search-placeholder="Search by name" actionsClass="w-[12%] min-w-[140px]" class="flex-1">
                    <template #cell-type="{ value }">
                        <span class="capitalize">{{ value }}</span>
                    </template>
                    <template #cell-status="{ item }">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest"
                            :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>
                    <template #rowActions="{ item }">
                        <button @click="openPreview(item)"
                            class="text-indigo-600 hover:text-indigo-900 cursor-pointer mr-4">
                            Preview
                        </button>
                        <button @click="editTemplate(item)" class="text-gray-600 hover:text-gray-900 cursor-pointer">
                            Edit
                        </button>
                    </template>
                </BaseDataTable>
            </div>

            <!-- Preview Modal -->
            <Modal :show="showModal" @close="showModal = false" title="Template Preview" maxWidth="4xl">
                <div class="flex justify-center bg-gray-100 p-4 rounded-lg overflow-y-auto max-h-[70vh]">
                    <div class="flex flex-col gap-8">
                        <div v-for="(page, i) in previewPages" :key="i"
                            class="bg-white shadow-lg w-[210mm] min-h-[297mm] p-[10mm] relative text-sm text-gray-800 font-serif leading-relaxed flex flex-col justify-between transform scale-90 origin-top">
                            <!-- Watermark -->
                            <div
                                class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden z-0">
                                <img v-if="getLayoutConfig().watermarkType === 'image'"
                                    :src="resolveImage(activeTemplate.watermark_image)" class="opacity-10 w-1/2" />
                                <span
                                    v-else-if="getLayoutConfig().watermarkType === 'text' && activeTemplate.watermark_text"
                                    class="text-9xl font-bold text-gray-100 -rotate-45 select-none uppercase">{{
                                        activeTemplate.watermark_text }}</span>
                            </div>

                            <!-- Content -->
                            <div class="relative z-10 flex flex-col h-full bg-transparent">
                                <!-- Header -->
                                <div class="mb-4">
                                    <!-- Image Mode -->
                                    <img v-if="getLayoutConfig().headerType === 'image'"
                                        :src="resolveImage(activeTemplate.header_image)"
                                        class="w-full max-h-[100px] object-contain" />

                                    <!-- Structured Mode -->
                                    <div v-else-if="getLayoutConfig().headerType === 'structured'"
                                        class="flex items-center gap-4 py-2" :class="{
                                            'flex-row': getLayoutConfig().headerLayout === 'logo-left' || getLayoutConfig().headerLayout === 'center-spread',
                                            'flex-row-reverse': getLayoutConfig().headerLayout === 'logo-right'
                                        }">

                                        <!-- Logo -->
                                        <div class="flex-shrink-0"
                                            :style="{ width: (getLayoutConfig().logoHeight || 100) + 'px' }">
                                            <img v-if="activeTemplate.header_image"
                                                :src="resolveImage(activeTemplate.header_image)"
                                                class="w-full object-contain" />
                                            <div v-else
                                                class="bg-gray-100 border border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400 aspect-square rounded">
                                                Logo</div>
                                        </div>

                                        <!-- Text -->
                                        <div class="flex-grow"
                                            :class="{ 'text-right': getLayoutConfig().headerLayout === 'logo-left', 'text-left': getLayoutConfig().headerLayout === 'logo-right', 'text-center': getLayoutConfig().headerLayout === 'center-spread' }">
                                            <div v-html="previewCommon.header"></div>
                                        </div>
                                    </div>

                                    <!-- HTML Mode -->
                                    <div v-else v-html="previewCommon.header"></div>
                                </div>

                                <!-- Body -->
                                <div class="flex-1 overflow-hidden pointer-events-auto page-content-wrapper relative">
                                    <div v-html="page.content"></div>
                                </div>

                                <!-- Footer -->
                                <div class="mt-4">
                                    <img v-if="getLayoutConfig().footerType === 'image'"
                                        :src="resolveImage(activeTemplate.footer_image)"
                                        class="w-full max-h-[100px] object-contain" />
                                    <div v-else v-html="previewCommon.footer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-between w-full">
                        <a v-if="activeTemplate"
                            :href="route('admin.document-templates.preview-pdf', activeTemplate.id)" target="_blank"
                            class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download PDF
                        </a>
                        <div class="flex gap-3">
                            <button @click="showModal = false"
                                class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 text-sm font-medium cursor-pointer">Close</button>
                            <Link v-if="activeTemplate"
                                :href="route('admin.document-templates.edit', activeTemplate.id)"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium cursor-pointer">
                                Edit Template</Link>
                        </div>
                    </div>
                </template>
            </Modal>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Modal from '@/Components/Modal.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";

const props = defineProps({
    templates: Object
});

const columns = [
    { key: 'name', label: 'Name', class: 'w-[34%] min-w-[180px]' },
    { key: 'type', label: 'Type', class: 'w-[18%] min-w-[110px]' },
    { key: 'status', label: 'Status', class: 'w-[14%] min-w-[110px]' }
];

const onPageChange = (page) => {
    router.get(route('admin.document-templates.index'), { page }, { preserveState: true });
};

// Preview Logic
const showModal = ref(false);
const activeTemplate = ref(null);

const mockData = {
    candidate: { id: 'EMP001', name: 'John Doe', email: 'john.doe@example.com', job_title: 'Senior Software Engineer', department: 'Engineering', joining_date: '2024-05-01' },
    salary: { ctc: '₹ 12,00,000', gross_salary: '₹ 1,00,000', net_salary: '₹ 92,000' },
    company: { name: 'Acme Corp', address: '123 Tech Park, Silicon Valley, India' },
    date: new Date().toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' }),
    year: new Date().getFullYear()
};

const replaceVariables = (html) => {
    if (!html) return '';
    let result = html;
    for (const [category, data] of Object.entries(mockData)) {
        if (typeof data === 'object') {
            for (const [key, value] of Object.entries(data)) {
                result = result.replace(new RegExp(`{{\\s*${category}\\.${key}\\s*}}`, 'gi'), value);
            }
        } else {
            result = result.replace(new RegExp(`{{\\s*${category}\\s*}}`, 'gi'), data);
        }
    }
    return result;
};

const getLayoutConfig = () => {
    return activeTemplate.value?.layout_config || { headerType: 'html', footerType: 'html', watermarkType: 'text' };
};

const resolveImage = (path) => {
    if (path) return '/storage/' + path;
    return null;
};

// Computed Preview Pages
const previewPages = computed(() => {
    if (!activeTemplate.value) return [];

    // Priority: pages_data > body_html
    let pages = activeTemplate.value.pages_data;

    // Handl if pages_data is a string (legacy/edge case)
    if (typeof pages === 'string') {
        try { pages = JSON.parse(pages); } catch (e) { pages = null; }
    }

    if (pages && pages.length > 0) {
        return pages.map(page => ({
            ...page,
            content: replaceVariables(page.content)
        }));
    }

    return [{
        id: 1,
        content: replaceVariables(activeTemplate.value.body_html || '')
    }];
});

const previewCommon = computed(() => {
    if (!activeTemplate.value) return { header: '', footer: '' };
    return {
        header: replaceVariables(activeTemplate.value.header_html),
        footer: replaceVariables(activeTemplate.value.footer_html)
    };
});

const openPreview = (item) => {
    activeTemplate.value = item;
    showModal.value = true;
};

const editTemplate = (item) => {
    router.visit(route('admin.document-templates.edit', item.id));
};
</script>
