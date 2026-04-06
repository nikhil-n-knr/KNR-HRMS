<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm z-10">
            <div class="flex items-center gap-4">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Products</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Manage your catalogue, pricing, and inventory.</p>
                </div>
                <!-- Selection Indicator -->
                <div v-if="selectedIds.length" class="flex items-center gap-2 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-xl ml-2">
                    <span class="text-xs font-black text-indigo-700">{{ selectedIds.length }} selected</span>
                    <button @click="openBulkDiscount" class="text-sm font-bold text-indigo-600 hover:underline">Apply Discount</button>
                    <div class="w-px h-3 bg-indigo-200 mx-1"></div>
                    <button @click="bulkAction('delete')" class="text-sm font-bold text-red-600 hover:underline">Delete</button>
                    <button @click="selectedIds = []" class="text-gray-400 hover:text-gray-600 ml-1"><i class="fas fa-times text-sm"></i></button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-gray-50 border border-gray-200 rounded-xl px-2 gap-2 h-10">
                    <i class="fas fa-search text-gray-400 text-xs ml-1"></i>
                    <input v-model="search" type="text" placeholder="Search..." class="w-40 bg-transparent border-0 text-sm focus:ring-0 outline-none p-0" />
                    <div class="w-px h-5 bg-gray-200 mx-1"></div>
                    <select v-model="filters.category" class="bg-transparent border-0 text-xs font-bold text-gray-600 focus:ring-0 outline-none p-0 pr-6">
                        <option value="">All Categories</option>
                        <option v-for="c in flattenedCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                
                <button @click="openImport" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl text-sm font-bold shadow-sm hover:border-indigo-400 hover:text-indigo-600 transition-all flex items-center gap-2">
                    <i class="fas fa-file-import"></i>Bulk Import
                </button>
                <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i>Add Product
                </button>
            </div>
        </div>

        <!-- KPIs -->
    <div class="px-8 py-4 grid grid-cols-4 gap-4 shrink-0">
        <div v-for="k in kpis" :key="k.label" @click="k.action ? k.action() : null"
            class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3 shadow-sm"
            :class="k.action ? 'cursor-pointer hover:border-indigo-300 transition-all' : ''">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :style="{background: k.bg, color: k.color}">
                <i :class="k.icon" class="text-sm"></i>
            </div>
            <div>
                <p class="text-xl font-black text-gray-900">{{ k.value }}</p>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ k.label }}</p>
            </div>
        </div>
    </div>

        <!-- Table -->
        <div class="flex-1 overflow-y-auto px-8 pb-8">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 w-10">
                                <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            </th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Product</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Price</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Stock</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Status</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in filtered" :key="p.id" class="hover:bg-gray-50 transition-colors group" :class="selectedIds.includes(p.id) ? 'bg-indigo-50/30' : ''">
                            <td class="py-3.5 px-5">
                                <input type="checkbox" :checked="selectedIds.includes(p.id)" @change="toggleSelection(p.id)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center border border-gray-200">
                                        <img v-if="firstImage(p)" :src="firstImage(p)" class="w-full h-full object-cover" />
                                        <i v-else class="fas fa-image text-gray-300 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm group-hover:text-indigo-600 transition-colors">{{ p.name }}</p>
                                        <p class="text-sm text-gray-400 font-mono">{{ p.sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                <p class="font-black text-gray-900 text-sm">₹{{ Number(p.price).toLocaleString('en-IN') }}</p>
                                <p v-if="p.mrp && p.mrp > p.price" class="text-sm text-gray-400 line-through">₹{{ Number(p.mrp).toLocaleString('en-IN') }}</p>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold" :class="(p.stock_qty || 0) < 10 ? 'text-red-500' : 'text-gray-700'">
                                        {{ p.stock_qty ?? '∞' }}
                                    </span>
                                    <div v-if="(p.stock_qty || 0) < 10 && p.stock_qty !== null" class="h-1 w-8 bg-red-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-red-500" :style="{width: (p.stock_qty * 10) + '%'}"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                <button @click="toggleProduct(p)" class="transition-transform active:scale-95">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-sm font-black border"
                                        :class="p.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="p.is_active ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                        {{ p.is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </button>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editProduct(p)" class="action-btn hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50"><i class="fas fa-pen text-sm"></i></button>
                                    <button @click="deleteProduct(p)" class="action-btn hover:text-red-600 hover:border-red-200 hover:bg-red-50"><i class="fas fa-trash text-sm"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filtered.length && !loading">
                            <td colspan="6" class="py-16 text-center text-gray-400 border-t border-gray-100">
                                <i class="fas fa-search text-3xl text-gray-200 mb-3 block"></i>
                                <p class="font-bold">No products match your criteria</p>
                                <button @click="resetFilters" class="mt-2 text-xs text-indigo-600 font-bold hover:underline">Clear all filters</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="loading" class="py-10 text-center text-gray-400">
                <i class="fas fa-spinner fa-spin text-xl"></i>
            </div>
        </div>

        <!-- Product Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="modal = null">
            <div class="bg-white rounded-2xl shadow-2xl w-[800px] max-h-[95vh] overflow-y-auto p-7">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-gray-900">{{ modal.id ? 'Edit Product' : 'Add Product' }}</h3>
                    <button @click="modal = null" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
                </div>
                
                <div class="grid grid-cols-12 gap-6">
                    <!-- Left Column: Core Info -->
                    <div class="col-span-7 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2"><label class="field-label">Product Name</label><input v-model="modal.name" type="text" class="field-input text-lg font-bold" placeholder="Blue Widget Pro" /></div>
                            <div><label class="field-label">SKU</label><input v-model="modal.sku" type="text" class="field-input font-mono" placeholder="BWP-001" /></div>
                            <div><label class="field-label">Brand</label><input v-model="modal.brand" type="text" class="field-input" placeholder="Acme Corp" /></div>
                            <div><label class="field-label">Category</label>
                                <select v-model="modal.category_id" class="field-input">
                                    <option :value="null">Uncategorized</option>
                                    <option v-for="c in flattenedCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Gender</label>
                                <select v-model="modal.gender" class="field-input">
                                    <option :value="null">N/A</option>
                                    <option value="men">Men</option>
                                    <option value="women">Women</option>
                                    <option value="unisex">Unisex</option>
                                    <option value="kids">Kids</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div><label class="field-label">Price (₹)</label><input v-model="modal.price" type="number" class="field-input font-bold" /></div>
                            <div><label class="field-label">MRP (₹)</label><input v-model="modal.mrp" type="number" class="field-input text-gray-400" /></div>
                            <div><label class="field-label">Stock Qty</label><input v-model="modal.stock_quantity" type="number" class="field-input" /></div>
                        </div>

                        <div><label class="field-label">Short Description</label><textarea v-model="modal.short_description" rows="2" class="field-input resize-none" placeholder="One-line product pitch..."></textarea></div>
                        <div><label class="field-label">Full Description</label><textarea v-model="modal.description" rows="5" class="field-input resize-none"></textarea></div>
                        
                        <!-- Variants Loop -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-black uppercase text-gray-500">Product Variants</label>
                                <button @click="addVariant" class="text-sm bg-white border border-gray-200 px-2 py-1 rounded-lg font-black text-indigo-600 hover:border-indigo-400">+ Add Variant</button>
                            </div>
                            <div class="space-y-2">
                                <div v-for="(v, i) in modal.variants" :key="i" class="grid grid-cols-12 gap-2 bg-white p-2 rounded-lg border border-gray-100 shadow-sm relative group">
                                    <input v-model="v.name" placeholder="Size/Color" class="col-span-4 field-input !py-1 text-base" />
                                    <input v-model="v.sku" placeholder="SKU" class="col-span-3 field-input !py-1 font-mono text-sm" />
                                    <input v-model="v.price" type="number" placeholder="Price" class="col-span-3 field-input !py-1 text-base" />
                                    <input v-model="v.stock" type="number" placeholder="Qty" class="col-span-2 field-input !py-1 text-base" />
                                    <button @click="removeVariant(i)" class="absolute -right-2 -top-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-lg"><i class="fas fa-times"></i></button>
                                </div>
                                <p v-if="!modal.variants?.length" class="text-sm text-gray-400 italic text-center py-2">No variants created. Add size, color, etc.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Media & Meta -->
                    <div class="col-span-5 space-y-6">
                        <!-- Product Images Loop -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="field-label !mb-0">Product Images</label>
                                <button @click="addImage" class="text-sm font-black text-indigo-600 hover:text-indigo-700">+ Add URL</button>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="(img, idx) in modal.images" :key="idx" class="relative group aspect-square rounded-xl bg-gray-50 border border-gray-200 overflow-hidden">
                                    <img v-if="img.url" :src="img.url" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 p-3 text-center">
                                        <i class="fas fa-image text-gray-200 text-2xl"></i>
                                        <input v-model="img.url" type="url" placeholder="Paste URL..." class="w-full text-sm border-b border-gray-200 focus:border-indigo-400 outline-none bg-transparent" />
                                    </div>
                                    <button @click="removeImage(idx)" class="absolute top-1 right-1 w-6 h-6 bg-white/90 backdrop-blur rounded-lg shadow-md text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all border border-red-50">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                                <button @click="addImage" class="aspect-square rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 text-gray-300 hover:border-indigo-300 hover:text-indigo-300 transition-all">
                                    <i class="fas fa-plus text-xl"></i>
                                    <span class="text-sm font-black uppercase">Add Image</span>
                                </button>
                            </div>
                        </div>

                        <!-- Color Swatches Loop -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="field-label !mb-0">Color Swatches</label>
                                <button @click="addSwatch" class="text-sm font-black text-emerald-600 hover:text-emerald-700">+ Add Swatch</button>
                            </div>
                            <div class="space-y-2">
                                <div v-for="(s, i) in modal.colour_swatches" :key="i" class="flex items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-100 group relative">
                                    <input type="color" v-model="s.hex" class="w-8 h-8 rounded-lg overflow-hidden border-0 cursor-pointer p-0" />
                                    <input v-model="s.name" placeholder="Color Name" class="flex-1 field-input !py-1 text-base" />
                                    <input v-model="s.stock" type="number" placeholder="Qty" class="w-16 field-input !py-1 text-base" />
                                    <button @click="removeSwatch(i)" class="w-6 h-6 bg-white text-red-500 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity shadow-sm border border-red-50 flex items-center justify-center font-black"><i class="fas fa-times text-sm"></i></button>
                                </div>
                                <p v-if="!modal.colour_swatches?.length" class="text-sm text-gray-400 italic">No color swatches defined.</p>
                            </div>
                        </div>

                        <!-- Visibility & Status -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="relative flex items-center justify-between cursor-pointer">
                                <div>
                                    <p class="text-xs font-black text-gray-900">Visibility Status</p>
                                    <p class="text-sm text-gray-500">Toggle website visibility.</p>
                                </div>
                                <input type="checkbox" v-model="modal.is_active" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-1 after:left-1 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 mt-8 pt-6 border-t border-gray-100">
                    <button @click="modal = null" class="px-8 py-3 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all">Discard Changes</button>
                    <button @click="saveProduct" :disabled="saving" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-sm font-black shadow-xl shadow-indigo-500/20 hover:bg-indigo-700 disabled:opacity-50 flex justify-center gap-3 items-center transition-all">
                        <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-cloud-upload-alt"></i>
                        {{ modal.id ? 'Push Update' : 'Publish Product' }}
                    </button>
                </div>
            </div>
        </div>
 
        <!-- Bulk Discount Modal -->
        <div v-if="bulkDiscountModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-[400px] p-6">
                <h3 class="text-lg font-black text-gray-900 mb-4 font-outfit">Apply Bulk Discount</h3>
                <p class="text-xs text-gray-500 mb-6">This will update the price of {{ selectedIds.length }} selected products.</p>
                
                <div class="space-y-4">
                    <div class="flex gap-2 p-1 bg-gray-50 rounded-xl border border-gray-100">
                        <button @click="bulkDiscount.type = 'pct'" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all" :class="bulkDiscount.type === 'pct' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400'">Percentage (%)</button>
                        <button @click="bulkDiscount.type = 'fixed'" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all" :class="bulkDiscount.type === 'fixed' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400'">Fixed Value (₹)</button>
                    </div>
                    <div>
                        <label class="field-label">Discount Value</label>
                        <input v-model="bulkDiscount.value" type="number" class="field-input" placeholder="e.g. 10" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Starts At</label>
                            <input v-model="bulkDiscount.starts_at" type="date" class="field-input text-xs" />
                        </div>
                        <div>
                            <label class="field-label">Ends At</label>
                            <input v-model="bulkDiscount.ends_at" type="date" class="field-input text-xs" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button @click="bulkDiscountModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50">Cancel</button>
                    <button @click="applyBulkDiscount" :disabled="!bulkDiscount.value" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-black hover:bg-indigo-700 disabled:opacity-50">Apply Now</button>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="importModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-[600px] p-7">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-black text-gray-900">Bulk Import Products</h3>
                    <button @click="importModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <p class="text-xs text-gray-500 mb-4">Paste your CSV data below. Format: <code class="bg-gray-100 px-1 rounded text-red-500">name, sku, price, mrp, stock_qty</code></p>
                
                <textarea v-model="importCsv" rows="10" class="field-input font-mono text-base leading-relaxed mb-4" placeholder="T-Shirt, TS-01, 499, 999, 50"></textarea>
                
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button @click="importModal = false" class="px-6 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-500">Cancel</button>
                    <button @click="processImport" :disabled="!importCsv.trim() || importing" class="flex-1 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-black hover:bg-emerald-700 disabled:opacity-50 shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                        <i v-if="importing" class="fas fa-spinner fa-spin"></i>
                        Process Import
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Discount Modal -->
        <div v-if="bulkDiscountModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="bulkDiscountModal = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-7">
                <h3 class="text-base font-black text-gray-900 mb-2">Apply Bulk Discount</h3>
                <p class="text-xs text-gray-500 mb-5">Apply a specific discount to {{ selectedIds.length }} selected products.</p>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Type</label>
                            <select v-model="bulkDiscount.type" class="field-input">
                                <option value="pct">Percentage (%)</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Value</label>
                            <input v-model="bulkDiscount.value" type="number" min="0" class="field-input" placeholder="e.g. 10" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Starts At</label>
                            <input v-model="bulkDiscount.starts_at" type="datetime-local" class="field-input text-xs" />
                        </div>
                        <div>
                            <label class="field-label">Ends At</label>
                            <input v-model="bulkDiscount.ends_at" type="datetime-local" class="field-input text-xs" />
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button @click="bulkDiscountModal = false" class="px-5 py-2.5 border border-gray-200 rounded-xl text-xs font-bold text-gray-500">Cancel</button>
                    <button @click="applyBulkDiscount" :disabled="!bulkDiscount.value" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 disabled:opacity-50">Apply</button>
                </div>
            </div>
        </div>

        <!-- Category Manager Modal -->
        <div v-if="catModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="catModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-[600px] flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="font-black text-gray-900">Manage Categories</h3>
                        <p class="text-xs text-gray-500">Organize your catalogue into categories & subcategories.</p>
                    </div>
                    <button @click="catModal = false" class="text-gray-400 hover:bg-gray-100 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-gray-50/50">
                    <!-- Create new -->
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
                        <h4 class="text-xs font-black uppercase text-gray-600 mb-3">{{ editCat.id ? 'Edit Category' : 'Create New Category' }}</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div><input v-model="editCat.name" type="text" class="field-input" placeholder="e.g. Mens Wear" /></div>
                            <div>
                                <select v-model="editCat.parent_id" class="field-input">
                                    <option :value="null">-- No Parent (Top Level) --</option>
                                    <option v-for="c in flattenedCategories.filter(c => c.id !== editCat.id)" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button @click="saveCategory" :disabled="savingCat || !editCat.name" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 disabled:opacity-50 w-full flex justify-center items-center gap-2">
                                <i v-if="savingCat" class="fas fa-spinner fa-spin text-sm"></i>
                                {{ editCat.id ? 'Update Category' : 'Add Category' }}
                            </button>
                            <button v-if="editCat.id" @click="resetEditCat" class="px-4 py-2 border border-gray-200 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>

                    <!-- Recursive Category List -->
                    <div class="space-y-2">
                        <component v-for="cat in categoryTree" :key="cat.id" :is="'CategoryItem'" :cat="cat" @edit="setEditCat" @delete="deleteCategory" />
                        <div v-if="!categoryTree.length" class="text-center py-8 text-gray-400 text-sm font-bold">No categories exist yet.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, watch, defineComponent, h, onMounted } from 'vue';

// Define recursive component for categories
const CategoryItem = defineComponent({
    name: 'CategoryItem',
    props: ['cat'],
    emits: ['edit', 'delete'],
    render() {
        return h('div', { class: 'bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-2' }, [
            h('div', { class: 'px-4 py-3 flex items-center justify-between group bg-white' }, [
                h('div', { class: 'flex items-center gap-2' }, [
                    h('i', { class: 'fas fa-folder text-indigo-400' }),
                    h('span', { class: 'font-bold text-gray-900 text-sm' }, this.cat.name)
                ]),
                h('div', { class: 'flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity' }, [
                    h('button', { onClick: () => this.$emit('edit', this.cat), class: 'action-btn hover:text-indigo-600' }, h('i', { class: 'fas fa-pen text-sm' })),
                    h('button', { onClick: () => this.$emit('delete', this.cat), class: 'action-btn hover:text-red-600 hover:bg-red-50' }, h('i', { class: 'fas fa-trash text-sm' }))
                ])
            ]),
            this.cat.children?.length ? h('div', { class: 'border-t border-gray-50 bg-gray-50/30 p-2 pl-6' }, 
                this.cat.children.map(child => h(CategoryItem, { 
                    cat: child, 
                    onEdit: (c) => this.$emit('edit', c), 
                    onDelete: (c) => this.$emit('delete', c) 
                }))
            ) : null
        ]);
    }
});
import axios from 'axios';

const props = defineProps({ 
    products: { type: Array, default: () => [] }, 
    categories: { type: Array, default: () => [] },
    activeSite: { type: Object, default: null } 
});
const list   = ref([...(props.products || [])]);
const categoriesList = ref([...(props.categories || [])]);
const loading = ref(false);

const fetchData = async () => {
    if (!props.activeSite?.id) return;
    loading.value = true;
    try {
        const siteId = props.activeSite.id;
        const [pRes, cRes] = await Promise.all([
            axios.get(route('cms.products.index'), { params: { site_id: siteId } }),
            axios.get(route('cms.product-categories.index'), { params: { site_id: siteId } })
        ]);
        list.value = Array.isArray(pRes.data) ? pRes.data : (pRes.data.data || []);
        categoriesList.value = cRes.data;
    } catch (e) { console.error('Failed to fetch products', e); }
    finally { loading.value = false; }
};

onMounted(() => {
    selectedIds.value = []; // Reset choice on mount
});

watch(() => props.activeSite?.id, (newId) => {
    if (newId) fetchData();
});

watch(() => props.products, (newProducts) => {
    // Only update if not loading and actually different
    if (!loading.value && (newProducts?.length !== list.value.length || newProducts?.[0]?.id !== list.value[0]?.id)) {
        list.value = [...(newProducts || [])];
    }
}, { deep: true });

const search = ref('');
const filters = ref({ category: '' });
const modal  = ref(null);
const saving = ref(false);

const catModal = ref(false);
const savingCat = ref(false);
const editCat = ref({ id: null, name: '', parent_id: null });

// Multi-select
const selectedIds = ref([]);
const allSelected = computed(() => filtered.value.length > 0 && selectedIds.value.length === filtered.value.length);
const toggleSelection = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) selectedIds.value.splice(idx, 1);
    else selectedIds.value.push(id);
};
const toggleAll = () => {
    if (allSelected.value) selectedIds.value = [];
    else selectedIds.value = filtered.value.map(p => p.id);
};

// Filters
const filtered = computed(() => {
    let res = list.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        res = res.filter(p => p.name?.toLowerCase().includes(q) || p.sku?.includes(search.value));
    }
    if (filters.value.category) {
        res = res.filter(p => p.category_id == filters.value.category);
    }
    return res;
});

const resetFilters = () => {
    search.value = '';
    filters.value.category = '';
};

// Bulk Discount
const bulkDiscountModal = ref(false);
const bulkDiscount = ref({ type: 'pct', value: '', starts_at: '', ends_at: '' });

const openBulkDiscount = () => { bulkDiscountModal.value = true; };
const applyBulkDiscount = async () => {
    if (!selectedIds.value.length) return;
    try {
        await axios.post(route('cms.products.bulk-action'), {
            action: 'apply_discount',
            ids: selectedIds.value,
            type: bulkDiscount.value.type,
            value: bulkDiscount.value.value,
            starts_at: bulkDiscount.value.starts_at,
            ends_at: bulkDiscount.value.ends_at
        });
        bulkDiscountModal.value = false;
        fetchData();
        selectedIds.value = [];
    } catch (e) { alert('Failed to apply discount'); }
};

const bulkAction = async (action) => {
    if (!confirm(`Perform ${action} on ${selectedIds.value.length} items?`)) return;
    try {
        await axios.post(route('cms.products.bulk-action'), { action, ids: selectedIds.value });
        if (action === 'delete') list.value = list.value.filter(p => !selectedIds.value.includes(p.id));
        selectedIds.value = [];
        fetchData();
    } catch (e) { alert('Action failed'); }
};

// Import
const importModal = ref(false);
const importCsv = ref('');
const importing = ref(false);

const openImport = () => { importCsv.value = ''; importModal.value = true; };
const processImport = async () => {
    const rows = importCsv.value.split('\n').filter(r => r.trim());
    const products = rows.map(r => {
        const [name, sku, price, mrp, stock_qty] = r.split(',').map(c => c.trim());
        return { name, sku, price, mrp, stock_qty };
    });

    importing.value = true;
    try {
        await axios.post(route('cms.products.import'), { products });
        importModal.value = false;
        fetchData();
    } catch (e) { alert('Import failed'); }
    finally { importing.value = false; }
};

const kpis = computed(() => [
    { label: 'All Products', value: list.value.length,                              icon: 'fas fa-box',           color: '#6366f1', bg: '#eef2ff', action: resetFilters },
    { label: 'Active',    value: list.value.filter(p => p.is_active).length,     icon: 'fas fa-check-circle',  color: '#10b981', bg: '#ecfdf5' },
    { label: 'Low Stock', value: list.value.filter(p => (p.stock_qty || 0) < 10 && p.stock_qty !== null).length, icon: 'fas fa-exclamation-triangle', color: '#ef4444', bg: '#fef2f2' },
    { label: 'Value',     value: '₹' + list.value.reduce((s,p) => s + Number(p.price||0) * Number(p.stock_qty||0), 0).toLocaleString('en-IN'), icon: 'fas fa-rupee-sign', color: '#10b981', bg: '#ecfdf5' },
]);

// Recursive category tree builder
const buildTree = (parentId = null) => {
    return categoriesList.value
        .filter(c => (parentId === null ? !c.parent_id : Number(c.parent_id) === Number(parentId)))
        .map(c => ({
            ...c,
            children: buildTree(c.id)
        }));
};
const categoryTree = computed(() => buildTree(null));

// Flattened tree for the select dropdown
const flatten = (tree, depth = 0) => {
    let result = [];
    tree.forEach(node => {
        result.push({ ...node, name: '— '.repeat(depth) + node.name });
        if (node.children?.length) {
            result = [...result, ...flatten(node.children, depth + 1)];
        }
    });
    return result;
};
const flattenedCategories = computed(() => flatten(categoryTree.value));

const parseJson = (val, def = []) => { 
    if (!val) return def;
    if (typeof val === 'object') return val;
    try { return JSON.parse(val); } catch { return def; } 
};

const firstImage = (p) => { 
    const imgs = parseJson(p.images);
    return imgs[0]?.url || null; 
};

const addImage = () => {
    if (!modal.value.images) modal.value.images = [];
    modal.value.images.push({ url: '', alt: '' });
};
const removeImage = (idx) => { modal.value.images.splice(idx, 1); };

const addVariant = () => {
    if (!modal.value.variants) modal.value.variants = [];
    modal.value.variants.push({ name: '', sku: '', price: modal.value.price, stock: 0 });
};
const removeVariant = (idx) => { modal.value.variants.splice(idx, 1); };

const addSwatch = () => {
    if (!modal.value.colour_swatches) modal.value.colour_swatches = [];
    modal.value.colour_swatches.push({ name: '', hex: '#000000', stock: 0 });
};
const removeSwatch = (idx) => { modal.value.colour_swatches.splice(idx, 1); };

const openCreate = () => { 
    modal.value = { 
        name:'', sku:'', price:0, mrp:null, stock_quantity:0, is_active:true, 
        description:'', short_description:'', images:[], variants:[], 
        colour_swatches:[], tags:[], brand:'', site_id: props.activeSite?.id 
    }; 
};

const editProduct = (p) => { 
    modal.value = { 
        ...p, 
        images: parseJson(p.images),
        variants: parseJson(p.variants),
        colour_swatches: parseJson(p.colour_swatches),
        tags: parseJson(p.tags)
    }; 
};

const saveProduct = async () => {
    saving.value = true;
    try {
        const payload = { ...modal.value };
        if (modal.value.id) {
            const { data } = await axios.put(route('cms.products.update', modal.value.id), payload);
            const idx = list.value.findIndex(p => p.id === data.id); if (idx !== -1) list.value[idx] = data;
        } else {
            const { data } = await axios.post(route('cms.products.store'), payload);
            list.value.unshift(data);
        }
        modal.value = null;
    } catch (e) { alert(e?.response?.data?.message || 'Save failed'); }
    finally { saving.value = false; }
};

const toggleProduct = async (p) => {
    try { await axios.post(route('cms.products.toggle', p.id)); p.is_active = !p.is_active; } catch {}
};
const deleteProduct = async (p) => {
    if (!confirm(`Delete "${p.name}"?`)) return;
    try { await axios.delete(route('cms.products.destroy', p.id)); list.value = list.value.filter(x => x.id !== p.id); } catch {}
};

// Category Management
const openCategories = () => { resetEditCat(); catModal.value = true; };
const resetEditCat = () => { editCat.value = { id: null, name: '', parent_id: null, site_id: props.activeSite?.id }; };
const setEditCat = (c) => { editCat.value = { id: c.id, name: c.name, parent_id: c.parent_id, site_id: props.activeSite?.id }; };

const saveCategory = async () => {
    savingCat.value = true;
    try {
        if (editCat.value.id) {
            const { data } = await axios.put(route('cms.product-categories.update', editCat.value.id), editCat.value);
            const idx = categoriesList.value.findIndex(c => c.id === data.id);
            if (idx !== -1) categoriesList.value[idx] = data;
        } else {
            const { data } = await axios.post(route('cms.product-categories.store'), editCat.value);
            categoriesList.value.push(data);
        }
        resetEditCat();
    } catch (e) { alert('Save failed'); }
    finally { savingCat.value = false; }
};

const deleteCategory = async (c) => {
    if (!confirm(`Delete category "${c.name}"?`)) return;
    try {
        await axios.delete(route('cms.product-categories.destroy', c.id));
        categoriesList.value = categoriesList.value.filter(x => x.id !== c.id);
    } catch (e) { alert('Delete failed - it may have products linked.'); }
};

</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
.action-btn { width:1.75rem; height:1.75rem; display:flex; align-items:center; justify-content:center; border-radius:0.5rem; border:1px solid #e5e7eb; background:#fff; color:#9ca3af; transition:all 0.15s; cursor:pointer; }
</style>
