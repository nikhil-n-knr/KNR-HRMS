<template>
    <AdminLayout>

        <Head title="Edit Template" />

        <GradientHeroHeader kicker="Document Templates" title="Edit Template"
            subtitle="Shape the template layout, content pages, and preview blocks from one workspace.">
            <template #right>
                <div
                    class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-[10px] font-black uppercase tracking-[0.35em] text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Live editor
                </div>
            </template>
        </GradientHeroHeader>

        <div class="p-6">

            <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/60">
                <div class="mx-auto max-w-full space-y-8">

                    <div
                        class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_25px_80px_-35px_rgba(15,23,42,0.28)]">
                        <form @submit.prevent="submit" class="space-y-8 p-5 sm:p-8">
                            <!-- Name & Type -->
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 sm:p-5">
                                    <InputLabel for="name" value="Template Name"
                                        class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                    <TextInput id="name" type="text" v-model="form.name"
                                        class="mt-2 block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20"
                                        required />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 sm:p-5">
                                    <InputLabel for="type" value="Type"
                                        class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                    <select id="type" v-model="form.type"
                                        class="mt-2 block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                                        <option value="offer">Offer Letter</option>
                                        <option value="appointment">Appointment Letter</option>
                                        <option value="appraisal">Appraisal / Hike Letter</option>
                                        <option value="promotion">Promotion Letter</option>
                                        <option value="warning">Warning / Disciplinary</option>
                                        <option value="relieving">Relieving / Experience Letter</option>
                                        <option value="payslip">Payslip</option>
                                        <option value="policy">Policy Document</option>
                                        <option value="letter">General / Other</option>
                                    </select>
                                    <InputError :message="form.errors.type" class="mt-2" />
                                </div>
                            </div>

                            <!-- Variable Reference -->
                            <div
                                class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-sky-50 p-5 shadow-sm transition-all hover:shadow-md">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.35em] text-indigo-500">
                                            Reference Kit</p>
                                        <h3 class="mt-1 text-lg font-black text-slate-900">Available Variables</h3>
                                    </div>
                                    <p class="text-xs text-slate-500">Click any token to copy it.</p>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                                    <div v-for="(vars, category) in variables" :key="category">
                                        <strong
                                            class="mb-2 block text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">{{
                                                category }}</strong>
                                        <ul class="space-y-2 text-xs text-slate-600">
                                            <li v-for="v in vars" :key="v"
                                                class="group relative cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white px-3 py-2 font-mono text-[11px] font-semibold text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                                                @click="copyVariable(v)">
                                                <!-- Glow -->
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-r from-indigo-500/0 via-indigo-500/5 to-cyan-500/0 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                                </div>

                                                <div class="relative z-10 flex items-center justify-between gap-3">

                                                    <!-- Variable -->
                                                    <span v-text="'{{ ' + v + ' }}'"></span>

                                                    <!-- Copied State -->
                                                    <transition enter-active-class="transition duration-200 ease-out"
                                                        enter-from-class="opacity-0 scale-90"
                                                        enter-to-class="opacity-100 scale-100"
                                                        leave-active-class="transition duration-150 ease-in"
                                                        leave-from-class="opacity-100 scale-100"
                                                        leave-to-class="opacity-0 scale-90">
                                                        <span v-if="copiedVariable === v"
                                                            class="rounded-full bg-emerald-100 px-2 py-1 text-[8px] font-black uppercase tracking-[0.18em] text-emerald-600 shadow-sm">
                                                            Copied
                                                        </span>
                                                    </transition>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                                <!-- Left: Editor & Config -->
                                <div class="space-y-6">
                                    <!-- Global Settings -->
                                    <div
                                        class="space-y-5 rounded-3xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-black uppercase tracking-[0.3em] text-slate-600">
                                                Document Layout</h3>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                <InputLabel value="Header Type"
                                                    class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                <select v-model="form.layout_config.headerType"
                                                    class="mt-2 block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                                                    <option value="html">HTML Editor</option>
                                                    <option value="image">Full Width Image</option>
                                                    <option value="structured">Logo + Text (Structured)</option>
                                                </select>

                                                <!-- Structured Settings (Nested) -->
                                                <div v-if="form.layout_config.headerType === 'structured'"
                                                    class="mt-4 space-y-3 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm">
                                                    <div>
                                                        <InputLabel value="Layout Mode"
                                                            class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-700" />
                                                        <select v-model="form.layout_config.headerLayout"
                                                            class="mt-2 block w-full rounded-xl border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                                                            <option value="logo-left">Logo Left / Text Right</option>
                                                            <option value="logo-right">Text Left / Logo Right</option>
                                                            <option value="center-spread">Logo Left / Text Center
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <InputLabel value="Logo Width (px)"
                                                            class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-700" />
                                                        <div class="flex items-center gap-2">
                                                            <input type="range" min="50" max="300"
                                                                v-model="form.layout_config.logoHeight"
                                                                class="flex-1 h-2 rounded-lg appearance-none cursor-pointer bg-indigo-200">
                                                            <span
                                                                class="w-12 text-right text-xs font-mono font-bold text-slate-600">{{
                                                                    form.layout_config.logoHeight }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                <InputLabel value="Footer Type"
                                                    class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                <select v-model="form.layout_config.footerType"
                                                    class="mt-2 block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                                                    <option value="html">HTML Editor</option>
                                                    <option value="image">Image Upload</option>
                                                    <option value="structured">Logo + Text (Structured)</option>
                                                </select>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                <InputLabel value="Watermark Type"
                                                    class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                <select v-model="form.layout_config.watermarkType"
                                                    class="mt-2 block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                                                    <option value="text">Text</option>
                                                    <option value="image">Image</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Image Uploads / HTML Editors for Header/Footer -->
                                        <div class="space-y-4 pt-2">
                                            <!-- Header Control Bar -->
                                            <div
                                                class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 text-xs sm:flex-row sm:items-center sm:justify-between">
                                                <span
                                                    class="font-black uppercase tracking-[0.35em] text-slate-400">Header
                                                    Configuration</span>
                                                <div class="flex gap-2">
                                                    <button type="button" @click="openLibrary('header')"
                                                        class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 transition-colors hover:bg-indigo-100">Load</button>
                                                    <button type="button" @click="openLibrary('header')"
                                                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 font-bold text-slate-600 transition-colors hover:bg-slate-50">Save</button>
                                                </div>
                                            </div>

                                            <!-- Header Content -->
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <!-- Header: Image Mode -->
                                                <div v-if="form.layout_config.headerType === 'image'">
                                                    <InputLabel value="Header Image (Full Width) - Max 1MB"
                                                        class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                    <input type="file" accept="image/*"
                                                        @change="e => handleImageUpload(e, 'header_image')"
                                                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-indigo-700 hover:file:bg-indigo-100" />
                                                    <p v-if="imageErrors.header_image"
                                                        class="mt-1 text-sm text-red-600">{{
                                                            imageErrors.header_image }}</p>
                                                </div>

                                                <!-- Header: Structured Mode -->
                                                <div v-else-if="form.layout_config.headerType === 'structured'"
                                                    class="space-y-4">
                                                    <div>
                                                        <InputLabel value="Header Logo - Max 1MB"
                                                            class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                        <input type="file" accept="image/*"
                                                            @change="e => handleImageUpload(e, 'header_image')"
                                                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-indigo-700 hover:file:bg-indigo-100" />
                                                        <p v-if="imageErrors.header_image"
                                                            class="mt-1 text-sm text-red-600">{{
                                                                imageErrors.header_image }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <InputLabel value="Header Text"
                                                            class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                        <div
                                                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                                            <QuillEditor theme="snow" contentType="html"
                                                                toolbar="minimal" v-model:content="form.header_html" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Header: HTML Mode -->
                                                <div v-else>
                                                    <InputLabel value="Header HTML"
                                                        class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                    <div
                                                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                                        <QuillEditor theme="snow" contentType="html" toolbar="minimal"
                                                            v-model:content="form.header_html" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Footer Control Bar -->
                                            <div
                                                class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 text-xs sm:flex-row sm:items-center sm:justify-between">
                                                <span
                                                    class="font-black uppercase tracking-[0.35em] text-slate-400">Footer
                                                    Configuration</span>
                                                <div class="flex gap-2">
                                                    <button type="button" @click="openLibrary('footer')"
                                                        class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 transition-colors hover:bg-indigo-100">Load</button>
                                                    <button type="button" @click="openLibrary('footer')"
                                                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 font-bold text-slate-600 transition-colors hover:bg-slate-50">Save</button>
                                                </div>
                                            </div>

                                            <!-- Footer Content -->
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <!-- Footer: Image Mode -->
                                                <div v-if="form.layout_config.footerType === 'image'">
                                                    <InputLabel value="Footer Image - Max 1MB"
                                                        class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                    <input type="file" accept="image/*"
                                                        @change="e => handleImageUpload(e, 'footer_image')"
                                                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-indigo-700 hover:file:bg-indigo-100" />
                                                    <p v-if="imageErrors.footer_image"
                                                        class="mt-1 text-sm text-red-600">{{
                                                            imageErrors.footer_image }}</p>
                                                </div>

                                                <!-- Footer: Structured Mode (NEW) -->
                                                <div v-else-if="form.layout_config.footerType === 'structured'">
                                                    <div
                                                        class="mb-4 space-y-3 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm">
                                                        <div>
                                                            <InputLabel value="Footer Layout"
                                                                class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-700" />
                                                            <select v-model="form.layout_config.footerLayout"
                                                                class="mt-2 block w-full rounded-xl border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                                                                <option value="logo-left">Logo Left / Text Right
                                                                </option>
                                                                <option value="center-spread">Logo Left / Text Center
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <InputLabel value="Logo Width (px)"
                                                                class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-700" />
                                                            <div class="flex items-center gap-2">
                                                                <input type="range" min="50" max="200"
                                                                    v-model="form.layout_config.footerLogoHeight"
                                                                    class="flex-1 h-2 rounded-lg appearance-none cursor-pointer bg-indigo-200">
                                                                <span
                                                                    class="w-12 text-right text-xs font-mono font-bold text-slate-600">{{
                                                                        form.layout_config.footerLogoHeight }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-4">
                                                        <div>
                                                            <InputLabel value="Footer Logo - Max 1MB"
                                                                class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                            <input type="file" accept="image/*"
                                                                @change="e => handleImageUpload(e, 'footer_image')"
                                                                class="mt-2 block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-indigo-700 hover:file:bg-indigo-100" />
                                                            <p v-if="imageErrors.footer_image"
                                                                class="mt-1 text-sm text-red-600">{{
                                                                    imageErrors.footer_image }}</p>
                                                        </div>
                                                        <div>
                                                            <InputLabel value="Footer Text"
                                                                class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                            <div
                                                                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                                                <QuillEditor theme="snow" contentType="html"
                                                                    toolbar="minimal"
                                                                    v-model:content="form.footer_html" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Footer: HTML Mode -->
                                                <div v-else>
                                                    <InputLabel value="Footer HTML"
                                                        class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                    <div
                                                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                                        <QuillEditor theme="snow" contentType="html" toolbar="minimal"
                                                            v-model:content="form.footer_html" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Watermark -->
                                            <div v-if="form.layout_config.watermarkType === 'image'">
                                                <InputLabel value="Watermark Image - Max 1MB"
                                                    class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                <input type="file" accept="image/*"
                                                    @change="e => handleImageUpload(e, 'watermark_image')"
                                                    class="mt-2 block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-indigo-700 hover:file:bg-indigo-100" />
                                                <p v-if="imageErrors.watermark_image" class="mt-1 text-sm text-red-600">
                                                    {{
                                                        imageErrors.watermark_image }}</p>
                                            </div>
                                            <div v-else>
                                                <InputLabel value="Watermark Text"
                                                    class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500" />
                                                <TextInput v-model="form.watermark_text"
                                                    class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20"
                                                    placeholder="CONFIDENTIAL" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Page Management -->
                                    <div
                                        class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p
                                                    class="text-[10px] font-black uppercase tracking-[0.35em] text-slate-400">
                                                    Pages</p>
                                                <h3 class="mt-1 text-lg font-black text-slate-900">Page Content</h3>
                                            </div>
                                            <button type="button" @click="addPage"
                                                class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-[10px] font-black uppercase tracking-[0.3em] text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95">+
                                                Add Page</button>
                                        </div>

                                        <div v-for="(page, index) in form.pages_data" :key="page.id"
                                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                            <div class="cursor-pointer border-b border-slate-100 bg-slate-50 px-4 py-3 flex items-center justify-between"
                                                @click="page.isOpen = !page.isOpen">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-bold text-slate-700">{{ page.title
                                                        }}</span>
                                                    <input type="text" v-model="page.title" @click.stop
                                                        class="rounded-full border border-transparent bg-white/0 p-0 text-xs text-slate-400 focus:border-indigo-200 focus:ring-indigo-200"
                                                        placeholder="(Rename)">
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click.stop="removePage(index)"
                                                        v-if="form.pages_data.length > 1"
                                                        class="text-xs font-black uppercase tracking-[0.25em] text-rose-500 hover:text-rose-700">Remove</button>
                                                    <svg class="w-5 h-5 text-gray-400 transform transition-transform"
                                                        :class="page.isOpen ? 'rotate-180' : ''" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <div v-show="page.isOpen !== false" class="h-[400px] bg-white p-4">
                                                <QuillEditor theme="snow" contentType="html" toolbar="essential"
                                                    v-model:content="page.content" />
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Right: Live Preview -->
                                <div class="xl:sticky xl:top-8 xl:self-start">
                                    <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm">
                                        <div class="flex items-end justify-between gap-3">
                                            <div>
                                                <p
                                                    class="text-[10px] font-black uppercase tracking-[0.35em] text-slate-400">
                                                    Preview</p>
                                                <h3 class="mt-1 text-lg font-black text-slate-900">Live Preview</h3>
                                            </div>
                                            <span
                                                class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">A4
                                                size</span>
                                        </div>
                                        <div
                                            class="mt-4 flex max-h-[800px] flex-col items-center gap-8 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-200 p-4 shadow-inner">

                                            <div v-for="(page, i) in previewPages" :key="i"
                                                class="bg-white shadow-lg w-[210mm] min-h-[297mm] px-[25mm] py-[20mm] relative text-sm text-gray-800 font-serif leading-relaxed flex flex-col justify-between overflow-hidden"
                                                id="preview-paper">

                                                <!-- Watermark -->
                                                <div
                                                    class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden z-0">
                                                    <img v-if="form.layout_config.watermarkType === 'image'"
                                                        :src="resolveImage(form.watermark_image, template.watermark_image)"
                                                        class="opacity-10 w-1/2" />
                                                    <span
                                                        v-else-if="form.layout_config.watermarkType === 'text' && previewCommon.watermark"
                                                        class="text-9xl font-bold text-gray-100 -rotate-45 select-none uppercase">{{
                                                            previewCommon.watermark }}</span>
                                                </div>

                                                <!-- Content -->
                                                <div
                                                    class="relative z-10 flex flex-col h-full bg-transparent pointer-events-none">
                                                    <!-- Header Section -->
                                                    <div class="mb-4 pointer-events-auto">
                                                        <!-- Reduced bottom margin -->
                                                        <!-- Image Mode -->
                                                        <img v-if="form.layout_config.headerType === 'image'"
                                                            :src="resolveImage(form.header_image, template.header_image, form.header_image_path)"
                                                            class="w-full max-h-[100px] object-contain" />

                                                        <!-- Structured Mode -->
                                                        <div v-else-if="form.layout_config.headerType === 'structured'"
                                                            class="flex items-center gap-4 py-2" :class="{
                                                                'flex-row': form.layout_config.headerLayout === 'logo-left' || form.layout_config.headerLayout === 'center-spread',
                                                                'flex-row-reverse': form.layout_config.headerLayout === 'logo-right'
                                                            }">

                                                            <!-- Logo -->
                                                            <div class="flex-shrink-0"
                                                                :style="{ width: (form.layout_config.logoHeight || 100) + 'px' }">
                                                                <img v-if="form.header_image || template.header_image || form.header_image_path"
                                                                    :src="resolveImage(form.header_image, template.header_image, form.header_image_path)"
                                                                    class="w-full object-contain" />
                                                                <div v-else
                                                                    class="bg-gray-100 border border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400 aspect-square rounded">
                                                                    Logo</div>
                                                            </div>

                                                            <!-- Text -->
                                                            <div class="flex-grow"
                                                                :class="{ 'text-right': form.layout_config.headerLayout === 'logo-left', 'text-left': form.layout_config.headerLayout === 'logo-right', 'text-center': form.layout_config.headerLayout === 'center-spread' }">
                                                                <div v-html="previewCommon.header"></div>
                                                            </div>
                                                        </div>

                                                        <!-- HTML Mode -->
                                                        <div v-else v-html="previewCommon.header"></div>
                                                    </div>

                                                    <!-- Body Section (Fills remaining space) -->
                                                    <div
                                                        class="flex-1 overflow-hidden pointer-events-auto page-content-wrapper relative border border-transparent hover:border-dashed hover:border-gray-300 transition-colors">
                                                        <div v-html="page.content"></div>
                                                    </div>

                                                    <!-- Footer Section -->
                                                    <div class="mt-4 pointer-events-auto"> <!-- Reduced top margin -->
                                                        <img v-if="form.layout_config.footerType === 'image'"
                                                            :src="resolveImage(form.footer_image, template.footer_image, form.footer_image_path)"
                                                            class="w-full max-h-[100px] object-contain" />
                                                        <div v-else v-html="previewCommon.footer"></div>
                                                    </div>
                                                </div>

                                                <!-- Overflow Warning Overlay -->
                                                <div v-if="overflowStates.get(i)"
                                                    class="absolute inset-x-0 bottom-0 bg-red-500/90 text-white text-center py-2 text-xs font-bold uppercase tracking-wider shadow-lg z-50 backdrop-blur-sm">
                                                    ⚠️ Content Exceeds Print Area
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <p class="mt-3 text-center text-xs text-slate-500">
                                        Multi-page layout approximation.
                                    </p>
                                </div>
                            </div>

                            <!-- Attachments -->
                            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                                <InputLabel value="Attachments (e.g. NDA, Policies)"
                                    class="text-[10px] font-black uppercase tracking-[0.35em] text-slate-500" />
                                <div
                                    class="mt-3 flex justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-6 transition-colors hover:border-indigo-400">
                                    <div class="space-y-2 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-300" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48" aria-hidden="true">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-slate-600">
                                            <label for="file-upload"
                                                class="relative cursor-pointer rounded-md font-semibold text-indigo-600 hover:text-indigo-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2">
                                                <span>Upload files</span>
                                                <input id="file-upload" name="file-upload" type="file"
                                                    accept=".pdf,.doc,.docx" ref="fileInput" class="sr-only" multiple
                                                    @change="handleFileUpload" />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-slate-500">
                                            PDF, DOC up to 5MB
                                        </p>
                                    </div>
                                </div>

                                <!-- File List -->
                                <ul v-if="form.existing_attachments.length || form.new_attachments.length"
                                    class="mt-4 space-y-2">
                                    <!-- Existing -->
                                    <li v-for="(file, index) in form.existing_attachments" :key="'existing-' + index"
                                        class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                        <span class="flex items-center truncate text-sm text-slate-600">
                                            <svg class="mr-2 h-4 w-4 text-slate-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ file.name }}
                                        </span>
                                        <button type="button" @click="removeExisting(index)"
                                            class="text-rose-500 transition-colors hover:text-rose-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </li>
                                    <!-- New -->
                                    <li v-for="(file, index) in form.new_attachments" :key="'new-' + index"
                                        class="flex items-center justify-between rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5">
                                        <span class="flex items-center truncate text-sm text-indigo-700">
                                            <svg class="mr-2 h-4 w-4 text-indigo-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ file.name }}
                                        </span>
                                        <button type="button" @click="removeNew(index)"
                                            class="text-rose-500 transition-colors hover:text-rose-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                                <Link :href="route('admin.document-templates.index')"
                                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                    Cancel</Link>
                                <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5 hover:bg-indigo-700 disabled:opacity-50"
                                    :disabled="form.processing">
                                    Save Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Library Modal -->
        <Modal :show="showLibraryModal" @close="showLibraryModal = false" :title="`Component Library (${libraryType})`">
            <div class="relative isolate bg-white opacity-100 p-4 sm:p-6 "
                :class="{ 'pointer-events-none': libraryLoading }">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                    <input type="text" v-model="saveComponentName" placeholder="Save current as new component..."
                        class="flex-1 rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20">
                    <button @click="saveComponent"
                        class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">Save
                        New</button>
                </div>

                <h4 class="mb-2 text-sm font-black uppercase tracking-[0.3em] text-slate-500">Available Components
                </h4>
                <div v-if="libraryComponents.length === 0"
                    class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm italic text-slate-500">
                    No components found. Save one to get started!</div>
                <div class="grid max-h-60 grid-cols-1 gap-3 overflow-y-auto pr-1">
                    <div v-for="comp in libraryComponents" :key="comp.id"
                        class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50"
                        @click="loadComponent(comp)">
                        <div>
                            <span class="block font-bold text-slate-900">{{ comp.name }}</span>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{
                                comp.type }} - {{ comp.settings?.headerType || comp.settings?.footerType || 'HTML'
                                }}</span>
                        </div>
                        <span v-if="comp.is_default"
                            class="rounded-full border border-blue-100 bg-blue-50 px-2 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-blue-700">Default</span>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/MainLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue'; // Add Modal import
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { computed, ref, watch } from 'vue';
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";

const props = defineProps({
    template: Object,
    variables: Object
});

const copiedVariable = ref(null);

const copyVariable = async (v) => {
    const value = `{{ ${v} }}`

    await navigator.clipboard.writeText(value)

    copiedVariable.value = v

    setTimeout(() => {
        copiedVariable.value = null
    }, 1500)
}

// Initial Config (Default if new)
const defaultConfig = {
    headerType: 'html', // html | image | structured
    headerLayout: 'logo-left',
    logoHeight: 100,
    footerType: 'html',
    watermarkType: 'text', // text | image
    headerHeight: 120, // px
    footerHeight: 80,
    pageMargin: 20
};

// Initial Pages (Default 1 page)
const defaultPages = [
    { id: 1, title: 'Page 1', content: '' }
];

const form = useForm({
    name: props.template.name || '',
    type: props.template.type || 'offer',
    is_active: props.template.is_active ?? true,

    // Layout Config
    layout_config: props.template.layout_config || defaultConfig,

    // Content (Multi-page) - Check if pages_data exists AND has length, else fallback to body_html
    pages_data: (props.template.pages_data && props.template.pages_data.length > 0)
        ? props.template.pages_data
        : [{ id: 1, title: 'Page 1', content: props.template.body_html || '' }],

    // Legacy/Fallback (Syncs with Page 1)
    body_html: props.template.body_html || '',
    header_html: props.template.header_html || '',
    footer_html: props.template.footer_html || '',
    watermark_text: props.template.watermark_text || '',

    // File Uploads
    header_image: null,
    footer_image: null,
    watermark_image: null,

    // Library Paths (Hidden)
    header_image_path: null,
    footer_image_path: null,

    // Attachments
    attachments: [],
    existing_attachments: props.template.attachments || [],
    new_attachments: []
});

// Mock Data for Preview
const mockData = {
    candidate: { id: 'EMP001', name: 'John Doe', email: 'john.doe@example.com', job_title: 'Software Engineer', joining_date: '2024-05-01' },
    salary: { ctc: '₹ 12,00,000' },
    company: { name: 'Acme Corp', address: 'Tech Park' },
    date: '20 May 2024'
};

const replaceVariables = (html) => {
    if (!html) return '';
    let result = html;

    // 1. Mock Tables
    if (result.includes('{{ table.salary_simple }}')) {
        result = result.replace(new RegExp('{{ table.salary_simple }}', 'g'), generateMockTable('simple'));
    }
    if (result.includes('{{ table.salary_detailed }}')) {
        result = result.replace(new RegExp('{{ table.salary_detailed }}', 'g'), generateMockTable('detailed'));
    }

    // 2. Data Replacement
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

const generateMockTable = (type) => {
    const style = 'width: 100%; border-collapse: collapse; margin-block: 15px; font-size: 13px; font-family: sans-serif;';
    const cell = 'border: 1px solid #e5e7eb; padding: 8px;';
    const head = 'background-color: #f3f4f6; text-align: left; padding: 8px; border: 1px solid #e5e7eb;';
    const right = 'text-align: right;';

    if (type === 'simple') {
        return `
        <table style="${style}">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="${head}">Description</th>
                    <th style="${head} ${right}">Monthly</th>
                    <th style="${head} ${right}">Annual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="${cell}"><strong>Total Earnings</strong></td>
                    <td style="${cell} ${right}">1,00,000.00</td>
                    <td style="${cell} ${right}">12,00,000.00</td>
                </tr>
                <tr>
                    <td style="${cell}"><strong>Total Deductions</strong></td>
                    <td style="${cell} ${right}">12,000.00</td>
                    <td style="${cell} ${right}">1,44,000.00</td>
                </tr>
                <tr style="background-color: #f9fafb;">
                    <td style="${cell}"><strong>Net Salary</strong></td>
                    <td style="${cell} ${right} font-weight: bold;">88,000.00</td>
                    <td style="${cell} ${right} font-weight: bold;">10,56,000.00</td>
                </tr>
            </tbody>
        </table>`;
    }

    if (type === 'detailed') {
        return `
        <table style="${style}">
            <thead>
                <tr style="background-color: #e5e7eb;">
                    <th style="${head}">Component</th>
                    <th style="${head} ${right}">Monthly</th>
                    <th style="${head} ${right}">Annual</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="3" style="${cell} background: #f3f4f6; font-weight: bold; font-size: 12px;">Earnings</td></tr>
                <tr><td style="${cell}">Basic</td><td style="${cell} ${right}">50,000</td><td style="${cell} ${right}">6,00,000</td></tr>
                <tr><td style="${cell}">HRA</td><td style="${cell} ${right}">25,000</td><td style="${cell} ${right}">3,00,000</td></tr>
                <tr><td style="${cell}">Special Allowance</td><td style="${cell} ${right}">25,000</td><td style="${cell} ${right}">3,00,000</td></tr>
                
                <tr><td colspan="3" style="${cell} background: #f3f4f6; font-weight: bold; font-size: 12px;">Deductions</td></tr>
                <tr><td style="${cell}">PF</td><td style="${cell} ${right}">1,800</td><td style="${cell} ${right}">21,600</td></tr>
                <tr><td style="${cell}">Prof Tax</td><td style="${cell} ${right}">200</td><td style="${cell} ${right}">2,400</td></tr>
                
                <tr style="background-color: #374151; color: white;">
                    <td style="${cell} border-color: #374151;"><strong>Net Salary Payable</strong></td>
                    <td style="${cell} ${right} font-weight: bold; border-color: #374151;">98,000</td>
                    <td style="${cell} ${right} font-weight: bold; border-color: #374151;">11,76,000</td>
                </tr>
            </tbody>
        </table>`;
    }
    return '';
};

// Computed Preview Pages
const previewPages = computed(() => {
    return form.pages_data.map(page => ({
        ...page,
        content: replaceVariables(page.content)
    }));
});

const previewCommon = computed(() => {
    return {
        header: replaceVariables(form.header_html),
        footer: replaceVariables(form.footer_html),
        watermark: form.watermark_text
    };
});

// Actions
const addPage = () => {
    form.pages_data.push({
        id: Date.now(),
        title: `Page ${form.pages_data.length + 1}`,
        content: ''
    });
};

const removePage = (index) => {
    if (form.pages_data.length > 1) {
        form.pages_data.splice(index, 1);
    }
};

const resolveImage = (fileInput, existingPath, libraryPath = null) => {
    if (fileInput instanceof File) return URL.createObjectURL(fileInput);
    if (libraryPath) return '/storage/' + libraryPath; // Library Preview
    if (existingPath) return '/storage/' + existingPath;
    return null;
};

const imageErrors = ref({});

// File handlers
const handleImageUpload = (e, field) => {
    const file = e.target.files[0];
    imageErrors.value[field] = null; // Reset error

    if (file) {
        // Validation: Max 1MB
        if (file.size > 1024 * 1024) {
            imageErrors.value[field] = `File is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Max allowed size is 1MB.`;
            e.target.value = ''; // Clear input
            form[field] = null;
            return;
        }

        // Validation: Image Type
        if (!file.type.startsWith('image/')) {
            imageErrors.value[field] = 'Invalid file type. Please upload an image.';
            e.target.value = '';
            form[field] = null;
            return;
        }

        form[field] = file;
    }
};

const handleParamUpload = (e) => {
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;

    if (props.template.id) form.new_attachments.push(...files);
    else form.attachments.push(...files);

    e.target.value = '';
};

const handleFileUpload = handleParamUpload;

const submit = () => {
    // Sync first page to body_html for legacy support
    form.body_html = form.pages_data[0]?.content || '';

    // Serialize config as backend expects JSON in some scenarios or just send array
    // Inertia sends typical payload. We rely on standard Inertia form helper.

    // Prepare data for multipart submission
    // We stringify JSON objects to ensure they pass cleanly as strings in FormData
    const payload = {
        ...form.data(),
        _method: props.template.id ? 'PUT' : 'POST',
        layout_config: JSON.stringify(form.layout_config),
        pages_data: JSON.stringify(form.pages_data)
    };

    if (props.template.id) {
        form.transform(() => payload)
            .post(route('admin.document-templates.update', props.template.id));
    } else {
        form.transform(() => payload)
            .post(route('admin.document-templates.store'));
    }
};

// Component Library Logic
const showLibraryModal = ref(false);
const libraryType = ref('header'); // header | footer
const libraryComponents = ref([]);
const libraryLoading = ref(false);
const saveComponentName = ref('');

const openLibrary = async (type) => {
    libraryType.value = type;
    showLibraryModal.value = true;
    libraryLoading.value = true;
    try {
        const res = await axios.get(route('admin.document-templates.components.fetch', { type }));
        libraryComponents.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        libraryLoading.value = false;
    }
};

const loadComponent = (component) => {
    if (libraryType.value === 'header') {
        form.layout_config.headerType = component.settings?.headerType || 'html';
        if (component.content) form.header_html = component.content;

        // Load Settings
        if (component.settings) {
            form.layout_config.headerLayout = component.settings.headerLayout;
            form.layout_config.logoHeight = component.settings.logoHeight;
        }

        // Load Image
        if (component.image_path) {
            form.header_image_path = component.image_path; // Set hidden path
            form.header_image = null; // Clear any file input
        }
    } else {
        form.layout_config.footerType = component.settings?.footerType || 'html';
        if (component.content) form.footer_html = component.content;

        if (component.settings) {
            form.layout_config.footerLayout = component.settings.footerLayout;
            form.layout_config.footerLogoHeight = component.settings.footerLogoHeight;
        }

        if (component.image_path) {
            form.footer_image_path = component.image_path;
            form.footer_image = null;
        }
    }
    showLibraryModal.value = false;
};

const saveComponent = async () => {
    if (!saveComponentName.value) return;

    const type = libraryType.value;
    const formData = new FormData();
    formData.append('name', saveComponentName.value);
    formData.append('type', type);

    if (type === 'header') {
        formData.append('content', form.header_html);
        formData.append('settings', JSON.stringify({
            headerType: form.layout_config.headerType,
            headerLayout: form.layout_config.headerLayout,
            logoHeight: form.layout_config.logoHeight
        }));
        if (form.header_image) formData.append('image_file', form.header_image);
    } else {
        formData.append('content', form.footer_html);
        formData.append('settings', JSON.stringify({
            footerType: form.layout_config.footerType,
            footerLayout: form.layout_config.footerLayout,
            footerLogoHeight: form.layout_config.footerLogoHeight // Assume we added this
        }));
        if (form.footer_image) formData.append('image_file', form.footer_image);
    }

    try {
        await axios.post(route('admin.document-templates.components.save'), formData);
        alert('Component saved to library!');
        saveComponentName.value = '';
        showLibraryModal.value = false;
    } catch (e) {
        alert('Failed to save component.');
    }
};

const copyToClipboard = (text) => { navigator.clipboard.writeText(text); };

// Overflow Detection 
// Using a Map to track overflow state per page index
const overflowStates = ref(new Map());

const checkOverflow = () => {
    // We need to wait for DOM update
    setTimeout(() => {
        const pages = document.querySelectorAll('#preview-paper');
        pages.forEach((pageEl, index) => {
            const contentEl = pageEl.querySelector('.page-content-wrapper');
            if (contentEl) {
                // A4 Height (297mm) - Padding (20mm * 2) = ~257mm available roughly
                // But we use scrollHeight vs clientHeight of the container
                // The container .flex-col.h-full is constrained by the parent A4 height

                // Better approach: Check if content container scrollHeight > clientHeight
                const isOverflowing = contentEl.scrollHeight > contentEl.clientHeight;
                overflowStates.value.set(index, isOverflowing);
            }
        });
    }, 500);
};

// Watch for content changes (debounced ideally, but simple here)
watch(() => form.pages_data, () => {
    checkOverflow();
}, { deep: true });

watch(() => [form.header_html, form.footer_html], () => {
    checkOverflow();
});

// Initial check
watch(previewPages, () => {
    checkOverflow();
}, { immediate: true });
</script>
