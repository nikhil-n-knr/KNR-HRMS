<template>
    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ template.id ? 'Edit Template' : 'Create Template' }}</h1>
            
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-6 sm:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name & Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="name" value="Template Name" />
                            <TextInput id="name" type="text" v-model="form.name" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="type" value="Type" />
                            <select id="type" v-model="form.type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                    <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 transition-all hover:bg-indigo-50">
                        <h3 class="font-bold text-indigo-900 mb-2 text-sm uppercase tracking-wide">Available Variables</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="(vars, category) in variables" :key="category">
                                <strong class="block text-indigo-800 text-xs uppercase mb-1">{{ category }}</strong>
                                <ul class="text-gray-600 text-xs space-y-1">
                                    <li v-for="v in vars" :key="v" class="cursor-pointer hover:text-indigo-600 hover:bg-white p-1 rounded transition-colors" @click="copyToClipboard('{{ ' + v + ' }}')">
                                        <span v-text="'{{ ' + v + ' }}'"></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-indigo-400">Click a variable to copy it to clipboard.</p>
                    </div>

                    <div class="flex flex-col xl:flex-row gap-8">
                        <!-- Left: Editor & Config -->
                        <div class="flex-1 space-y-6">
                            <!-- Global Settings -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-4">
                                <h3 class="font-bold text-gray-700">Document Layout</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Header Type" />
                                        <select v-model="form.layout_config.headerType" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="html">HTML Editor</option>
                                            <option value="image">Full Width Image</option>
                                            <option value="structured">Logo + Text (Structured)</option>
                                        </select>
                                        
                                        <!-- Structured Settings (Nested) -->
                                        <div v-if="form.layout_config.headerType === 'structured'" class="mt-3 space-y-3 bg-indigo-50 p-3 rounded-md border border-indigo-100 text-sm">
                                            <div>
                                                <InputLabel value="Layout Mode" class="text-xs text-indigo-800" />
                                                <select v-model="form.layout_config.headerLayout" class="mt-1 block w-full text-xs rounded border-indigo-200">
                                                    <option value="logo-left">Logo Left / Text Right</option>
                                                    <option value="logo-right">Text Left / Logo Right</option>
                                                    <option value="center-spread">Logo Left / Text Center</option>
                                                </select>
                                            </div>
                                            <div>
                                                <InputLabel value="Logo Width (px)" class="text-xs text-indigo-800" />
                                                <div class="flex items-center gap-2">
                                                    <input type="range" min="50" max="300" v-model="form.layout_config.logoHeight" class="flex-1 h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer">
                                                    <span class="text-xs font-mono w-8">{{ form.layout_config.logoHeight }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <InputLabel value="Footer Type" />
                                        <select v-model="form.layout_config.footerType" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="html">HTML Editor</option>
                                            <option value="image">Image Upload</option>
                                            <option value="structured">Logo + Text (Structured)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel value="Watermark Type" />
                                        <select v-model="form.layout_config.watermarkType" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="text">Text</option>
                                            <option value="image">Image</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Image Uploads / HTML Editors for Header/Footer -->
                                <div class="space-y-4 pt-2">
                                    <!-- Header Control Bar -->
                                    <div class="flex justify-between items-center bg-gray-100 p-2 rounded text-xs">
                                        <span class="font-bold text-gray-500 uppercase">Header Configuration</span>
                                        <div class="flex gap-2">
                                            <button type="button" @click="openLibrary('header')" class="text-indigo-600 hover:underline">Load from Library</button>
                                            <span class="text-gray-300">|</span>
                                            <button type="button" @click="openLibrary('header')" class="text-indigo-600 hover:underline">Save to Library</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Header Content -->
                                    <div class="p-2 border-l-2 border-indigo-200 pl-4">
                                        <!-- Header: Image Mode -->
                                        <div v-if="form.layout_config.headerType === 'image'">
                                            <InputLabel value="Header Image (Full Width) - Max 1MB" />
                                            <input type="file" accept="image/*" @change="e => handleImageUpload(e, 'header_image')" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                            <p v-if="imageErrors.header_image" class="mt-1 text-sm text-red-600">{{ imageErrors.header_image }}</p>
                                        </div>
                                        
                                        <!-- Header: Structured Mode -->
                                        <div v-else-if="form.layout_config.headerType === 'structured'" class="space-y-4">
                                             <div>
                                                <InputLabel value="Header Logo - Max 1MB" />
                                                <input type="file" accept="image/*" @change="e => handleImageUpload(e, 'header_image')" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                                <p v-if="imageErrors.header_image" class="mt-1 text-sm text-red-600">{{ imageErrors.header_image }}</p>
                                            </div>
                                            <div>
                                                <InputLabel value="Header Text" />
                                                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                                                    <QuillEditor theme="snow" contentType="html" toolbar="minimal" v-model:content="form.header_html" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Header: HTML Mode -->
                                        <div v-else>
                                             <InputLabel value="Header HTML" />
                                             <div class="bg-white rounded border border-gray-300 overflow-hidden"><QuillEditor theme="snow" contentType="html" toolbar="minimal" v-model:content="form.header_html" /></div>
                                        </div>
                                    </div>

                                    <!-- Footer Control Bar -->
                                    <div class="flex justify-between items-center bg-gray-100 p-2 rounded text-xs mt-6">
                                        <span class="font-bold text-gray-500 uppercase">Footer Configuration</span>
                                        <div class="flex gap-2">
                                            <button type="button" @click="openLibrary('footer')" class="text-indigo-600 hover:underline">Load from Library</button>
                                            <span class="text-gray-300">|</span>
                                             <button type="button" @click="openLibrary('footer')" class="text-indigo-600 hover:underline">Save to Library</button>
                                        </div>
                                    </div>
                                    
                                     <!-- Footer Content -->
                                    <div class="p-2 border-l-2 border-indigo-200 pl-4">
                                        <!-- Footer: Image Mode -->
                                        <div v-if="form.layout_config.footerType === 'image'">
                                            <InputLabel value="Footer Image - Max 1MB" />
                                            <input type="file" accept="image/*" @change="e => handleImageUpload(e, 'footer_image')" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                            <p v-if="imageErrors.footer_image" class="mt-1 text-sm text-red-600">{{ imageErrors.footer_image }}</p>
                                        </div>
                                        
                                        <!-- Footer: Structured Mode (NEW) -->
                                        <div v-else-if="form.layout_config.footerType === 'structured'">
                                             <div class="mb-3 space-y-3 bg-indigo-50 p-3 rounded-md border border-indigo-100 text-sm">
                                                <div>
                                                    <InputLabel value="Footer Layout" class="text-xs text-indigo-800" />
                                                    <select v-model="form.layout_config.footerLayout" class="mt-1 block w-full text-xs rounded border-indigo-200">
                                                        <option value="logo-left">Logo Left / Text Right</option>
                                                        <option value="center-spread">Logo Left / Text Center</option>
                                                    </select>
                                                </div>
                                                 <div>
                                                    <InputLabel value="Logo Width (px)" class="text-xs text-indigo-800" />
                                                    <div class="flex items-center gap-2">
                                                        <input type="range" min="50" max="200" v-model="form.layout_config.footerLogoHeight" class="flex-1 h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer">
                                                        <span class="text-xs font-mono w-8">{{ form.layout_config.footerLogoHeight }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                             <div class="space-y-4">
                                                 <div>
                                                    <InputLabel value="Footer Logo - Max 1MB" />
                                                    <input type="file" accept="image/*" @change="e => handleImageUpload(e, 'footer_image')" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                                    <p v-if="imageErrors.footer_image" class="mt-1 text-sm text-red-600">{{ imageErrors.footer_image }}</p>
                                                </div>
                                                <div>
                                                    <InputLabel value="Footer Text" />
                                                    <div class="bg-white rounded border border-gray-300 overflow-hidden">
                                                        <QuillEditor theme="snow" contentType="html" toolbar="minimal" v-model:content="form.footer_html" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer: HTML Mode -->
                                        <div v-else>
                                             <InputLabel value="Footer HTML" />
                                             <div class="bg-white rounded border border-gray-300 overflow-hidden"><QuillEditor theme="snow" contentType="html" toolbar="minimal" v-model:content="form.footer_html" /></div>
                                        </div>
                                    </div>

                                    <!-- Watermark -->
                                     <div v-if="form.layout_config.watermarkType === 'image'">
                                        <InputLabel value="Watermark Image - Max 1MB" />
                                        <input type="file" accept="image/*" @change="e => handleImageUpload(e, 'watermark_image')" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                        <p v-if="imageErrors.watermark_image" class="mt-1 text-sm text-red-600">{{ imageErrors.watermark_image }}</p>
                                    </div>
                                    <div v-else>
                                         <InputLabel value="Watermark Text" />
                                         <TextInput v-model="form.watermark_text" class="w-full" placeholder="CONFIDENTIAL" />
                                    </div>
                                </div>
                            </div>

                            <!-- Page Management -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-bold text-gray-700">Page Content</h3>
                                    <button type="button" @click="addPage" class="text-sm bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg hover:bg-indigo-200">+ Add Page</button>
                                </div>
                                
                                <div v-for="(page, index) in form.pages_data" :key="page.id" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                    <div class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer border-b border-gray-200" @click="page.isOpen = !page.isOpen">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-700">{{ page.title }}</span>
                                            <input type="text" v-model="page.title" @click.stop class="text-xs border-none bg-transparent focus:ring-0 p-0 text-gray-500" placeholder="(Rename)">
                                        </div>
                                        <div class="flex items-center gap-2">
                                             <button type="button" @click.stop="removePage(index)" v-if="form.pages_data.length > 1" class="text-red-500 hover:text-red-700 text-xs font-semibold">Remove</button>
                                             <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="page.isOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    
                                    <div v-show="page.isOpen !== false" class="p-4 bg-white h-[400px]">
                                        <QuillEditor theme="snow" contentType="html" toolbar="essential" v-model:content="page.content" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right: Live Preview -->
                        <div class="xl:w-[45%]">
                            <h3 class="font-bold text-gray-800 mb-2">Live Preview (A4 Size)</h3>
                            <div class="bg-gray-200 p-4 rounded-xl overflow-y-auto max-h-[800px] border border-gray-300 shadow-inner flex flex-col items-center gap-8">
                                
                                <div v-for="(page, i) in previewPages" :key="i" class="bg-white shadow-lg w-[210mm] min-h-[297mm] px-[25mm] py-[20mm] relative text-sm text-gray-800 font-serif leading-relaxed flex flex-col justify-between overflow-hidden" id="preview-paper">
                                    
                                    <!-- Watermark -->
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden z-0">
                                         <img v-if="form.layout_config.watermarkType === 'image'" :src="resolveImage(form.watermark_image, template.watermark_image)" class="opacity-10 w-1/2" />
                                         <span v-else-if="form.layout_config.watermarkType === 'text' && previewCommon.watermark" class="text-9xl font-bold text-gray-100 -rotate-45 select-none uppercase">{{ previewCommon.watermark }}</span>
                                    </div>

                                    <!-- Content -->
                                    <div class="relative z-10 flex flex-col h-full bg-transparent pointer-events-none">
                                        <!-- Header Section -->
                                        <div class="mb-4 pointer-events-auto"> <!-- Reduced bottom margin -->
                                             <!-- Image Mode -->
                                             <img v-if="form.layout_config.headerType === 'image'" :src="resolveImage(form.header_image, template.header_image, form.header_image_path)" class="w-full max-h-[100px] object-contain" />
                                             
                                             <!-- Structured Mode -->
                                             <div v-else-if="form.layout_config.headerType === 'structured'" class="flex items-center gap-4 py-2" 
                                                :class="{
                                                    'flex-row': form.layout_config.headerLayout === 'logo-left' || form.layout_config.headerLayout === 'center-spread',
                                                    'flex-row-reverse': form.layout_config.headerLayout === 'logo-right'
                                                }">
                                                
                                                <!-- Logo -->
                                                <div class="flex-shrink-0" :style="{ width: (form.layout_config.logoHeight || 100) + 'px' }">
                                                    <img v-if="form.header_image || template.header_image || form.header_image_path" :src="resolveImage(form.header_image, template.header_image, form.header_image_path)" class="w-full object-contain" />
                                                    <div v-else class="bg-gray-100 border border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400 aspect-square rounded">Logo</div>
                                                </div>

                                                <!-- Text -->
                                                <div class="flex-grow" :class="{ 'text-right': form.layout_config.headerLayout === 'logo-left', 'text-left': form.layout_config.headerLayout === 'logo-right', 'text-center': form.layout_config.headerLayout === 'center-spread' }">
                                                    <div v-html="previewCommon.header"></div>
                                                </div>
                                             </div>

                                             <!-- HTML Mode -->
                                             <div v-else v-html="previewCommon.header"></div>
                                        </div>
                                        
                                        <!-- Body Section (Fills remaining space) -->
                                        <div class="flex-1 overflow-hidden pointer-events-auto page-content-wrapper relative border border-transparent hover:border-dashed hover:border-gray-300 transition-colors">
                                            <div v-html="page.content"></div>
                                        </div>
                                        
                                        <!-- Footer Section -->
                                        <div class="mt-4 pointer-events-auto"> <!-- Reduced top margin -->
                                             <img v-if="form.layout_config.footerType === 'image'" :src="resolveImage(form.footer_image, template.footer_image, form.footer_image_path)" class="w-full max-h-[100px] object-contain" />
                                             <div v-else v-html="previewCommon.footer"></div>
                                        </div>
                                    </div>

                                    <!-- Overflow Warning Overlay -->
                                    <div v-if="overflowStates.get(i)" class="absolute inset-x-0 bottom-0 bg-red-500/90 text-white text-center py-2 text-xs font-bold uppercase tracking-wider shadow-lg z-50 backdrop-blur-sm">
                                        ⚠️ Content Exceeds Print Area
                                    </div>
                                </div>

                            </div>
                            <p class="text-center text-xs text-gray-500 mt-2">
                                Multi-page layout approximation.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Attachments -->
                    <div>
                        <InputLabel value="Attachments (e.g. NDA, Policies)" />
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload files</span>
                                        <input id="file-upload" name="file-upload" type="file" ref="fileInput" class="sr-only" multiple @change="handleFileUpload" />
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PDF, DOC up to 5MB
                                </p>
                            </div>
                        </div>
                        
                        <!-- File List -->
                        <ul v-if="form.existing_attachments.length || form.new_attachments.length" class="mt-4 space-y-2">
                             <!-- Existing -->
                            <li v-for="(file, index) in form.existing_attachments" :key="'existing-'+index" class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="flex items-center text-sm text-gray-600 truncate">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" /></svg>
                                    {{ file.name }}
                                </span>
                                <button type="button" @click="removeExisting(index)" class="text-red-500 hover:text-red-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </li>
                             <!-- New -->
                            <li v-for="(file, index) in form.new_attachments" :key="'new-'+index" class="flex items-center justify-between py-2 px-3 bg-indigo-50 rounded-lg border border-indigo-200">
                                <span class="flex items-center text-sm text-indigo-700 truncate">
                                     <svg class="h-4 w-4 mr-2 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    {{ file.name }}
                                </span>
                                <button type="button" @click="removeNew(index)" class="text-red-500 hover:text-red-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                         <Link :href="route('admin.document-templates.index')" class="mr-4 text-sm text-gray-600 hover:text-gray-900">Cancel</Link>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5" :disabled="form.processing">
                            Save Template
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Library Modal -->
            <Modal :show="showLibraryModal" @close="showLibraryModal = false" :title="`Component Library (${libraryType})`">
                <div class="p-4" :class="{ 'opacity-50 pointer-events-none': libraryLoading }">
                    <div class="mb-6 flex gap-2">
                         <input type="text" v-model="saveComponentName" placeholder="Save current as new component..." class="flex-1 rounded-md border-gray-300 shadow-sm sm:text-sm">
                         <button @click="saveComponent" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">Save New</button>
                    </div>
                    
                    <h4 class="font-bold text-gray-700 mb-2">Available Components</h4>
                    <div v-if="libraryComponents.length === 0" class="text-gray-500 italic text-sm">No components found. Save one to get started!</div>
                    <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto">
                        <div v-for="comp in libraryComponents" :key="comp.id" class="border p-3 rounded hover:bg-gray-50 flex justify-between items-center cursor-pointer" @click="loadComponent(comp)">
                            <div>
                                <span class="font-medium text-gray-900 block">{{ comp.name }}</span>
                                <span class="text-xs text-gray-500 uppercase">{{ comp.type }} - {{ comp.settings?.headerType || comp.settings?.footerType || 'HTML' }}</span>
                            </div>
                            <span v-if="comp.is_default" class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Default</span>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
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

const props = defineProps({
    template: Object,
    variables: Object
});

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
    const files = Array.from(e.target.files);
    if (props.template.id) form.new_attachments.push(...files);
    else form.attachments.push(...files);
};

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
