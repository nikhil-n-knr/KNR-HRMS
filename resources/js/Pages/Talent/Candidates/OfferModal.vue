<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

                <div class="relative transform overflow-hidden rounded-xl bg-white/90 backdrop-blur-xl text-left shadow-2xl transition-all w-full max-w-5xl border border-white/50 h-[85vh] flex flex-col">
                    
                    <!-- Header -->
                    <div class="bg-white/50 px-6 py-4 border-b border-gray-200/50 flex flex-col md:flex-row justify-between items-center shrink-0">
                        <div class="mb-2 md:mb-0">
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ stepTitle }} <span v-if="form.is_draft" class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">DRAFT</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Candidate: <span class="font-medium text-indigo-600">{{ candidate?.name || 'Unknown' }}</span> • {{ form.designation || 'No Role' }}
                            </p>
                        </div>
                        
                        <!-- Steps Indicator -->
                        <div class="flex items-center space-x-1">
                            <div v-for="step in 6" :key="step" class="flex items-center">
                                <div class="flex flex-col items-center cursor-pointer" @click="goToStep(step)">
                                    <div :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2',
                                        currentStep >= step ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-200 text-gray-400 hover:border-indigo-300'
                                    ]">
                                        {{ step }}
                                    </div>
                                    <span class="text-sm mt-1 font-medium hidden md:block" :class="currentStep >= step ? 'text-indigo-600' : 'text-gray-400'">
                                        {{ getStepLabel(step) }}
                                    </span>
                                </div>
                                <div v-if="step < 6" class="w-6 h-0.5 mx-1 mb-4" :class="currentStep > step ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Step Content (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-6 bg-gray-50/30">
                        
                        <!-- STEP 1: Configuration (Templates & Dates) -->
                        <div v-show="currentStep === 1" class="space-y-6 animate-fade-in-up">
                            <div class="bg-white/60 p-5 rounded-lg border border-gray-200 shadow-sm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-full">
                                        <InputLabel value="Select Offer Template(s)" />
                                        <p class="text-xs text-gray-500 mb-2">Search and select one or more templates to combine.</p>
                                        <!-- Custom Searchable MultiSelect Logic -->
                                        <div class="relative">
                                             <div class="flex flex-wrap gap-2 mb-2 bg-white p-2 border rounded-md min-h-[42px]" @click="$refs.templateSearchInput.focus()">
                                                <span v-for="id in form.template_ids" :key="id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ getTemplateName(id) }}
                                                    <button type="button" @click.stop="toggleTemplate(id)" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none">×</button>
                                                </span>
                                                <input 
                                                    ref="templateSearchInput"
                                                    type="text" 
                                                    v-model="templateSearch" 
                                                    placeholder="Type to search templates..." 
                                                    class="border-0 focus:ring-0 text-sm flex-1 min-w-[150px]"
                                                >
                                             </div>
                                             
                                             <div v-if="availableTemplates.length > 0 && templateSearch" class="absolute z-10 w-full bg-white shadow-lg max-h-60 overflow-y-auto rounded-md border mt-1">
                                                 <div 
                                                    v-for="t in availableTemplates" 
                                                    :key="t.id"
                                                    @click="toggleTemplate(t.id); templateSearch = ''"
                                                    class="px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm"
                                                 >
                                                    <div class="font-medium">{{ t.name }}</div>
                                                    <div class="text-xs text-gray-500">{{ t.type }}</div>
                                                 </div>
                                             </div>
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel for="designation" value="Designation / Role" />
                                        <TextInput id="designation" v-model="form.designation" class="mt-1 block w-full bg-white" placeholder="e.g. Senior Software Engineer" />
                                    </div>
                                    <div>
                                        <InputLabel for="offer_date" value="Offer Date" />
                                        <TextInput id="offer_date" v-model="form.offer_date" type="date" class="mt-1 block w-full bg-white" />
                                    </div>
                                    <div>
                                        <InputLabel for="joining" value="Joining Date" />
                                        <TextInput id="joining" v-model="form.joining_date" type="date" class="mt-1 block w-full bg-white" />
                                    </div>
                                    <div>
                                        <InputLabel for="expiry_date" value="Expiry Date" />
                                        <TextInput id="expiry_date" v-model="form.expiry_date" type="date" class="mt-1 block w-full bg-white" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: Financials (CTC & Logic) -->
                        <div v-show="currentStep === 2" class="space-y-8 animate-fade-in-up">
                             <div class="bg-indigo-50 rounded-xl p-6 text-center border border-indigo-100">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Annual CTC</label>
                                <div class="flex justify-center items-center">
                                     <select v-model="form.salary_currency" class="text-2xl font-bold bg-transparent border-0 text-gray-500 focus:ring-0 cursor-pointer text-right w-20 p-0 mr-2">
                                        <option>INR</option>
                                        <option>USD</option>
                                        <option>EUR</option>
                                    </select>
                                    <input 
                                        type="number" 
                                        v-model="form.salary_amount" 
                                        class="text-4xl font-bold text-indigo-600 bg-transparent border-0 border-b-2 border-indigo-200 focus:border-indigo-600 focus:ring-0 w-64 text-center placeholder-indigo-200 transition-colors" 
                                        placeholder="0.00" 
                                    >
                                </div>
                             </div>

                             <div class="max-w-md mx-auto">
                                <InputLabel value="Salary Structure Logic" class="mb-2 text-center" />
                                <div class="relative">
                                     <select
                                        v-model="form.salary_structure_id"
                                        class="block w-full rounded-lg border-gray-300 py-3 pl-4 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm shadow-sm"
                                    >
                                        <option value="">Manual Entry (No Structure)</option>
                                        <option disabled>──────────────</option>
                                        <option v-for="s in salaryStructures" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                             </div>
                        </div>

                        <!-- STEP 3: Breakdown Logic -->
                        <div v-show="currentStep === 3" class="animate-fade-in-up">
                            <!-- Detailed Summary breakdown same as before -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                                    <span class="text-xs text-indigo-500 font-bold uppercase tracking-wider">Gross Monthly</span>
                                    <div class="mt-1 flex items-baseline">
                                        <span class="text-2xl font-bold text-indigo-700">{{ form.salary_currency }} {{ salaryDetails.grossMonthly.toLocaleString('en-IN', { maximumFractionDigits: 0 }) }}</span>
                                    </div>
                                </div>
                                <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                                    <span class="text-xs text-red-500 font-bold uppercase tracking-wider">Total Deductions</span>
                                    <div class="mt-1 flex items-baseline">
                                        <span class="text-2xl font-bold text-red-700">{{ form.salary_currency }} {{ salaryDetails.deductionsMonthly.toLocaleString('en-IN', { maximumFractionDigits: 0 }) }}</span>
                                        <span class="ml-1 text-xs text-red-500">/ mo</span>
                                    </div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4 border border-green-100 shadow-sm ring-1 ring-green-200">
                                    <span class="text-xs text-green-600 font-bold uppercase tracking-wider">Net Monthly Pay</span>
                                    <div class="mt-1 flex items-baseline">
                                        <span class="text-3xl font-extrabold text-green-700">{{ form.salary_currency }} {{ salaryDetails.netMonthly.toLocaleString('en-IN', { maximumFractionDigits: 0 }) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Table Component -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                     <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Component</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Annual</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(comp, index) in form.salary_breakdown.components" :key="index" class="hover:bg-gray-50">
                                            <td class="px-6 py-2 whitespace-nowrap">
                                                <input v-if="!form.salary_structure_id" v-model="comp.name" type="text" class="border-0 bg-transparent p-0 text-sm font-medium text-gray-900 focus:ring-0 w-full" placeholder="e.g. Basic">
                                                <span v-else class="text-sm font-medium text-gray-900">{{ comp.name }}</span>
                                            </td>
                                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm text-gray-500">
                                                {{ (comp.value / 12).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                            </td>
                                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm text-gray-900 font-medium">
                                                <input v-if="!form.salary_structure_id" v-model="comp.value" type="number" class="text-right border-0 p-0 bg-transparent w-24">
                                                <span v-else>{{ Number(comp.value).toLocaleString('en-IN') }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- STEP 4: Documents (ENHANCED) -->
                        <div v-show="currentStep === 4" class="animate-fade-in-up space-y-6">
                             <div class="bg-white p-5 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900">Pre-Offer Documents (Conditional)</h4>
                                        <p class="text-xs text-gray-500">Documents required BEFORE the offer is fully released.</p>
                                    </div>
                                    <div class="relative inline-block w-12 mr-2">
                                        <input type="checkbox" id="toggle" v-model="form.is_conditional" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 cursor-pointer border-gray-300 checked:right-0 checked:border-green-400"/>
                                        <label for="toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer checked:bg-green-400"></label>
                                    </div>
                                </div>
                                <div v-if="form.is_conditional" class="bg-gray-50 p-4 rounded-md border border-gray-100">
                                    <div class="mb-3 flex flex-wrap gap-2">
                                        <!-- Quick Add Buttons -->
                                        <button type="button" @click="addDoc('pre_offer')" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-full border border-indigo-200 hover:bg-indigo-100 font-medium transition-colors">
                                            + Custom
                                        </button>
                                        <button type="button" @click="quickAdd('pre_offer', preOfferPresets)" class="text-xs bg-white text-gray-700 px-3 py-1.5 rounded-full border border-gray-300 hover:bg-gray-50 font-medium transition-colors flex items-center shadow-sm">
                                            <svg class="w-3 h-3 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Standard KYC
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        <div v-for="(doc, idx) in form.document_requests.filter(d => d.stage === 'pre_offer')" :key="'pre-'+idx" class="flex items-center gap-2 bg-white p-2 border rounded shadow-sm">
                                            <span class="text-gray-400 text-xs">📄</span>
                                            <TextInput v-model="doc.name" class="flex-1 text-sm h-8 bg-gray-50" />
                                            <button type="button" @click="removeDoc(doc)" class="text-red-400 hover:text-red-600">×</button>
                                        </div>
                                    </div>
                                </div>
                             </div>

                             <div class="bg-white p-5 rounded-lg border border-gray-200">
                                <h4 class="text-base font-bold text-gray-900 mb-2">Onboarding Documents</h4>
                                <div class="bg-gray-50 p-4 rounded-md border border-gray-100">
                                     <div class="mb-3 flex flex-wrap gap-2">
                                        <button type="button" @click="addDoc('post_offer')" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-full border border-indigo-200 hover:bg-indigo-100 font-medium transition-colors">
                                            + Custom
                                        </button>
                                        <button type="button" @click="quickAdd('post_offer', postOfferPresets)" class="text-xs bg-white text-gray-700 px-3 py-1.5 rounded-full border border-gray-300 hover:bg-gray-50 font-medium transition-colors flex items-center shadow-sm">
                                            <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                            Bank & Checklist
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        <div v-for="(doc, idx) in form.document_requests.filter(d => d.stage === 'post_offer')" :key="'post-'+idx" class="flex items-center gap-2 bg-white p-2 border rounded shadow-sm">
                                            <span class="text-gray-400 text-xs">📂</span>
                                            <TextInput v-model="doc.name" class="flex-1 text-sm h-8 bg-gray-50" />
                                            <button type="button" @click="removeDoc(doc)" class="text-red-400 hover:text-red-600">×</button>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- STEP 5: Pre-Release / Review -->
                        <div v-show="currentStep === 5" class="animate-fade-in-up space-y-6">
                            <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-100">
                                <h3 class="text-lg font-bold text-indigo-900 mb-4">Review & Background Check</h3>
                                
                                <!-- Link & PIN Box -->
                                <div class="bg-white p-4 rounded border border-indigo-200 shadow-sm mb-6">
                                    <h4 class="text-sm font-bold text-gray-700 uppercase mb-3 px-1">Public Offer Access</h4>
                                    
                                    <div v-if="form.token" class="space-y-4">
                                        <!-- Link Row -->
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-1 bg-gray-50 border border-gray-200 rounded px-3 py-2 text-xs font-mono text-gray-600 truncate select-all">
                                                {{ encryptedLink }}
                                            </div>
                                            <button @click="copyToClipboard(encryptedLink)" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded border border-gray-200" title="Copy Link">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>
                                            <a :href="encryptedLink" target="_blank" class="p-2 text-indigo-600 hover:text-white hover:bg-indigo-600 rounded border border-indigo-200 transition-colors" title="Open Live Preview">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            </a>
                                        </div>

                                        <!-- PIN Row -->
                                        <div class="flex items-center justify-between bg-yellow-50 px-4 py-3 rounded border border-yellow-200">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-yellow-800 uppercase tracking-wide">Security PIN</span>
                                                <span class="text-sm text-yellow-600">Required for candidate login</span>
                                            </div>
                                            <div class="text-xl font-mono font-bold text-gray-800 tracking-[0.2em] bg-white px-3 py-1 rounded border border-yellow-100 shadow-sm">
                                                {{ form.otp || '----' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Loading State -->
                                    <div v-else class="py-8 text-center flex flex-col items-center justify-center space-y-3">
                                        <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-gray-500 animate-pulse">Generating secure link...</span>
                                    </div>
                                </div>

                                <!-- Email Configuration for Document Request -->
                                <div class="bg-white p-5 rounded border border-gray-200">
                                    <h4 class="text-sm font-bold text-gray-900 mb-4">Document Request Email</h4>
                                    
                                     <!-- Toggle Type -->
                                    <div class="flex justify-between items-center mb-3">
                                        <div class="flex space-x-2 text-xs bg-gray-100 p-1 rounded">
                                            <button type="button" @click="form.email_type='generic'" class="px-3 py-1 rounded transition-colors" :class="form.email_type==='generic' ? 'bg-white shadow text-indigo-600 font-bold' : 'text-gray-500 hover:text-gray-700'">Default</button>
                                            <button type="button" @click="form.email_type='custom'" class="px-3 py-1 rounded transition-colors" :class="form.email_type==='custom' ? 'bg-white shadow text-indigo-600 font-bold' : 'text-gray-500 hover:text-gray-700'">Custom</button>
                                            <button type="button" @click="form.email_type='reminder'" class="px-3 py-1 rounded transition-colors" :class="form.email_type==='reminder' ? 'bg-white shadow text-indigo-600 font-bold' : 'text-gray-500 hover:text-gray-700'">Reminder</button>
                                        </div>
                                    </div>

                                    <!-- CC Field -->
                                    <div class="mb-4">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">CC / Add People</label>
                                        <div class="relative">
                                            <div class="flex flex-wrap gap-1 p-2 border rounded-md bg-white min-h-[38px]" @click="$refs.ccInputRef.focus()">
                                                <span v-for="email in form.cc_emails" :key="email" class="bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full text-xs flex items-center">
                                                    {{ email }} <button @click.stop="removeCc(email)" class="ml-1 text-gray-400 hover:text-red-500">×</button>
                                                </span>
                                                <input 
                                                    ref="ccInputRef"
                                                    v-model="ccInput" 
                                                    @keydown.enter.prevent="addCc" 
                                                    @blur="addCc"
                                                    type="text" 
                                                    placeholder="Type email or search employee..." 
                                                    class="flex-1 min-w-[150px] text-xs border-0 focus:ring-0 p-0"
                                                >
                                            </div>
                                            <!-- Employee Dropdown -->
                                            <div v-if="filteredUsers.length && ccInput" class="absolute z-10 w-full bg-white shadow-lg max-h-48 overflow-y-auto rounded-md border mt-1">
                                                <div 
                                                    v-for="user in filteredUsers" 
                                                    :key="user.id"
                                                    @mousedown.prevent="selectUserCc(user.email)"
                                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-xs flex justify-between"
                                                >
                                                    <span class="font-medium text-gray-700">{{ user.name }}</span>
                                                    <span class="text-gray-500">{{ user.email }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Live Preview & Editor -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Editor -->
                                        <div v-if="form.email_type !== 'generic'" class="animate-fade-in space-y-3">
                                             <div>
                                                 <InputLabel value="Subject" class="text-xs text-gray-500" />
                                                 <TextInput v-model="form.email_subject" class="w-full text-sm" placeholder="e.g., Action Required: Submit Documents" />
                                             </div>
                                             <div>
                                                 <InputLabel value="Body" class="text-xs text-gray-500" />
                                                 <RichTextEditor v-model="form.email_body" class="mt-1 min-h-[150px]" />
                                             </div>
                                        </div>
                                        <!-- Preview Box -->
                                        <div class="bg-gray-50 rounded border border-gray-200 p-4 flex flex-col h-full">
                                            <h5 class="text-xs font-bold text-gray-500 uppercase mb-2">Live Email Preview</h5>
                                            <div class="flex-1 bg-white border border-gray-100 p-3 rounded text-sm text-gray-800 prose prose-sm overflow-y-auto max-h-[200px]">
                                                <p class="font-bold text-gray-900 mb-1 border-b pb-1">Subject: {{ form.email_type === 'generic' ? 'Pending Documents Check' : form.email_subject }}</p>
                                                <div v-html="liveEmailPreview" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="mt-4 flex justify-end">
                                        <button 
                                            type="button" 
                                            @click="sendDocRequestEmail"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm flex items-center"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                            Send {{ form.email_type === 'reminder' ? 'Reminder' : 'Request' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 6: Release & Management (Enhanced) -->
                        <div v-show="currentStep === 6" class="animate-fade-in-up">
                             
                             <!-- A. MANAGEMENT MODE (After Release) -->
                             <div v-if="isManagementMode" class="space-y-6">
                                 <!-- 1. Offer Status Dashboard -->
                                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                    <div class="flex justify-between items-start mb-6">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                                Offer Status: 
                                                <span :class="{
                                                    'text-green-600 bg-green-50 border-green-200': form.status === 'Accepted', 
                                                    'text-blue-600 bg-blue-50 border-blue-200': form.status === 'Sent',
                                                    'text-amber-600 bg-amber-50 border-amber-200': form.status === 'Pending_Approval'
                                                }" class="px-3 py-1 rounded-full text-sm border font-bold uppercase tracking-wider">
                                                    {{ form.status }}
                                                </span>
                                            </h3>
                                            <p class="text-sm text-gray-500 mt-1">Managed by {{ $page.props.auth?.user?.name || 'Admin' }}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                             <!-- Approval Review Button -->
                                             <!-- Approval Review Button -->
                                             <a v-if="canApprove && form.status === 'Pending_Approval'" 
                                                :href="route('talent.offers.approval', candidate.id)" 
                                                target="_blank"
                                                class="flex items-center gap-2 bg-amber-600 text-white hover:bg-amber-700 px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-md hover:shadow-lg animate-pulse"
                                             >
                                                 <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                 Review & Approve
                                             </a>
                                             
                                             <button @click="loadOfferData(form.id)" class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors border border-gray-200" title="Refresh Status">
                                                 <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                             </button>

                                             <button @click="resendOffer" class="group flex items-center gap-2 text-indigo-600 hover:text-indigo-900 text-sm font-medium bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg transition-all">
                                                 <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                 Resend Email
                                             </button>
                                             <a :href="route('talent.offers.download', form.id)" v-if="form.id" target="_blank" class="flex items-center gap-2 bg-gray-900 text-white hover:bg-gray-800 px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-md hover:shadow-lg">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                 Download Signed Offer
                                             </a>
                                        </div>
                                    </div>
                                    
                                     <!-- Approval Status Section -->
                                     <div v-if="form.approver_ids?.length" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100">
                                         <h4 class="text-xs font-bold text-gray-500 uppercase mb-3 text-center sm:text-left">Approval Workflow</h4>
                                         <div class="flex items-center gap-4 flex-wrap justify-center sm:justify-start">
                                            <div v-for="uid in form.approver_ids" :key="uid" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border shadow-sm transition-all"
                                                :class="form.status === 'Approved' || form.status === 'Sent' || form.status === 'Accepted' ? 'border-green-200 bg-green-50' : 'border-gray-200'"
                                            >
                                                <!-- Icon Based on Status -->
                                                <div v-if="form.status === 'Approved' || form.status === 'Sent' || form.status === 'Accepted'" class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white text-sm">✓</div>
                                                <div v-else-if="form.status === 'Rejected'" class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center text-white text-sm">✗</div>
                                                <div v-else class="w-5 h-5 rounded-full bg-amber-400 flex items-center justify-center text-white text-sm animate-pulse">?</div>
                                                
                                                <div class="flex flex-col leading-tight">
                                                    <span class="text-xs font-bold text-gray-700">{{ getUserName(uid) }}</span>
                                                    <span class="text-sm text-gray-500 font-medium">
                                                        {{ (form.status === 'Approved' || form.status === 'Sent' || form.status === 'Accepted') ? 'Approved' : (form.status === 'Rejected' ? 'Rejected' : 'Pending') }}
                                                    </span>
                                                </div>
                                            </div>
                                         </div>
                                     </div>

                                    <!-- 2. Document Audit Table (The Request) -->
                                     <h4 class="font-bold text-gray-800 mb-3 border-b pb-2 flex items-center justify-between">
                                         <span>Document Audit Trail</span>
                                         <span class="text-xs font-normal text-gray-500">Verify uploaded files before onboarding</span>
                                     </h4>
                                    <div class="overflow-hidden border border-gray-200 rounded-lg shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Document Name</th>
                                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Stage</th>
                                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                <!-- Generated Offer -->
                                                <tr class="bg-indigo-50/40 hover:bg-indigo-50 transition-colors">
                                                    <td class="px-5 py-4 text-sm font-bold text-indigo-900 border-l-4 border-indigo-500">
                                                        Official Offer Letter
                                                    </td>
                                                    <td class="px-5 py-4 text-xs font-medium text-gray-500">Generated</td>
                                                    <td class="px-5 py-4 text-sm"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Active</span></td>
                                                    <td class="px-5 py-4 text-right text-sm">
                                                        <a :href="route('talent.offers.download', form.id)" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs bg-indigo-100 px-3 py-1.5 rounded hover:bg-indigo-200 transition-colors">Download PDF</a>
                                                    </td>
                                                </tr>
                                                <!-- Candidate Uploads -->
                                                <tr v-for="doc in form.document_requests" :key="doc.id" class="hover:bg-gray-50 group transition-all">
                                                    <td class="px-5 py-3 text-sm font-medium text-gray-900 flex flex-col">
                                                        <span>{{ doc.name }} <span v-if="doc.is_mandatory" class="text-red-500">*</span></span>
                                                        <span v-if="doc.rejection_reason" class="text-xs text-red-500 mt-0.5">Reason: {{ doc.rejection_reason }}</span>
                                                    </td>
                                                    <td class="px-5 py-3 text-xs text-gray-500 capitalize">{{ doc.stage.replace('_', ' ') }}</td>
                                                    <td class="px-5 py-3 text-sm">
                                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border" 
                                                            :class="{
                                                                'bg-yellow-50 text-yellow-700 border-yellow-200': ['Pending', 'Requested'].includes(doc.status), 
                                                                'bg-green-50 text-green-700 border-green-200': doc.status === 'Verified', 
                                                                'bg-red-50 text-red-700 border-red-200': doc.status === 'Rejected', 
                                                                'bg-blue-50 text-blue-700 border-blue-200': doc.status === 'Submitted'
                                                            }">
                                                            {{ ['Submitted'].includes(doc.status) ? 'Uploaded' : doc.status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-5 py-3 text-right text-sm space-x-2">
                                                        <template v-if="doc.file_path">
                                                            <a :href="'/storage/'+doc.file_path" target="_blank" class="inline-flex items-center text-gray-600 hover:text-indigo-600 font-medium text-xs bg-white border border-gray-300 px-2 py-1 rounded hover:border-indigo-400 transition-all shadow-sm">
                                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                                View
                                                            </a>
                                                            <!-- Verify/Reject Actions -->
                                                            <div class="inline-flex rounded-md shadow-sm ml-2" role="group">
                                                                <button @click="verifyDoc(doc, 'Verified')" class="px-2 py-1 text-xs font-bold text-white bg-green-500 hover:bg-green-600 border border-transparent rounded-l focus:z-10 focus:ring-2 focus:ring-green-400 transition-colors" title="Verify & Approve">
                                                                    ✓ Verify
                                                                </button>
                                                                <button @click="verifyDoc(doc, 'Rejected')" class="px-2 py-1 text-xs font-bold text-white bg-red-500 hover:bg-red-600 border border-transparent rounded-r focus:z-10 focus:ring-2 focus:ring-red-400 transition-colors" title="Reject & Ask for Re-upload">
                                                                    × Reject
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <span v-else class="text-xs text-gray-400 italic">Waiting for upload...</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div v-if="!form.document_requests.length" class="p-8 text-center text-gray-400 text-sm">
                                            No required documents configured for this offer.
                                        </div>
                                    </div>
                                </div>
                             </div>

                             <!-- B. RELEASE MODE (New Offer) -->
                             <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                
                                <!-- Left Column: Final Config & Approvals -->
                                <div class="lg:col-span-2 space-y-6">
                                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                            <span class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm">6</span>
                                            Finalize & Release
                                        </h4>

                                        <!-- Final Email Config -->
                                        <div class="bg-blue-50 p-5 rounded-lg border border-blue-100 mb-6">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <h5 class="text-sm font-bold text-blue-800">📩 Final Offer Email</h5>
                                                    <p class="text-xs text-blue-600 mt-1">Sent immediately to candidate. Includes Offer Letter + Attachments.</p>
                                                </div>
                                                <button type="button" @click="previewOffer" class="text-xs bg-white text-indigo-600 border border-indigo-200 px-3 py-1.5 rounded hover:bg-indigo-50 font-bold flex items-center transition-colors">
                                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                    Preview Web Offer
                                                </button>
                                            </div>
                                            
                                            <div class="space-y-4">
                                                <div>
                                                    <InputLabel value="Email Subject" class="text-xs text-gray-500" />
                                                    <TextInput v-model="form.email_subject" class="w-full text-sm font-medium bg-white" />
                                                </div>
                                                <div>
                                                    <InputLabel value="Email Body" class="text-xs text-gray-500" />
                                                    <RichTextEditor v-model="form.email_body" class="mt-1 min-h-[150px] bg-white border-gray-300" />
                                                </div>
                                                
                                                <!-- Additional Attachments -->
                                                <div>
                                                     <InputLabel value="Additional Email Attachments" class="text-xs text-gray-500" />
                                                     <div class="mt-2 flex items-center gap-4">
                                                         <label class="cursor-pointer bg-white border border-gray-300 border-dashed rounded-md px-4 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm text-gray-600 custom-file-upload transition-all hover:border-indigo-300">
                                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                             <span>Attach Files</span>
                                                             <input type="file" multiple @change="handleAttachments" class="hidden">
                                                         </label>
                                                         <div class="flex-1 flex flex-wrap gap-2">
                                                             <span v-for="(file, i) in form.attachments" :key="i" class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded border flex items-center animate-fade-in">
                                                                 {{ file.name }}
                                                                 <button @click="removeAttachment(i)" class="ml-2 text-red-500 hover:text-red-700 font-bold focus:outline-none">×</button>
                                                             </span>
                                                         </div>
                                                     </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- APPROVALS SECTION -->
                                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="text-sm font-bold text-gray-800">Requires Approval?</h5>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" v-model="needsApproval" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                                    <span class="ml-3 text-sm font-medium text-gray-700">{{ needsApproval ? 'Yes, select approvers' : 'No, direct release' }}</span>
                                                </label>
                                            </div>

                                            <div v-if="needsApproval" class="animate-fade-in space-y-4">
                                                <div>
                                                    <InputLabel value="Select Approvers (Multi-Select)" />
                                                    <!-- Approver MultiSelect -->
                                                    <div class="relative">
                                                         <div class="flex flex-wrap gap-2 mb-2 bg-white p-2 border rounded-md min-h-[42px] cursor-text" @click="$refs.approverSearchRef.focus()">
                                                            <span v-for="uid in form.approver_ids" :key="uid" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                                {{ getUserName(uid) }}
                                                                <button type="button" @click.stop="toggleApprover(uid)" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-amber-600 hover:bg-amber-200 focus:outline-none">×</button>
                                                            </span>
                                                            <input 
                                                                ref="approverSearchRef"
                                                                type="text" 
                                                                v-model="approverSearch" 
                                                                placeholder="Type name to search..." 
                                                                class="border-0 focus:ring-0 text-sm flex-1 min-w-[150px]"
                                                            >
                                                         </div>
                                                         <div v-if="approverResults.length > 0 && approverSearch" class="absolute z-10 w-full bg-white shadow-lg max-h-48 overflow-y-auto rounded-md border mt-1">
                                                             <div 
                                                                v-for="u in approverResults" 
                                                                :key="u.id"
                                                                @click="toggleApprover(u.id); approverSearch=''"
                                                                class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-xs flex justify-between group"
                                                             >
                                                                <span class="font-bold text-gray-800 group-hover:text-indigo-600">{{ u.name }}</span>
                                                                <span class="text-gray-500">{{ u.email }}</span>
                                                             </div>
                                                         </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- FORCE RELEASE BYPASS -->
                                            <div v-if="needsApproval" class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                                                <div>
                                                    <h6 class="text-xs font-bold text-red-600 uppercase tracking-widest">Emergency Bypass</h6>
                                                    <p class="text-sm text-gray-500">Force release without waiting for approvals.</p>
                                                </div>
                                                <button 
                                                    type="button" 
                                                    @click="form.is_force_release = !form.is_force_release"
                                                    :class="form.is_force_release ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                                    class="px-3 py-1.5 rounded text-xs font-bold transition-colors"
                                                >
                                                    {{ form.is_force_release ? 'Active: Force Release' : 'Enable Force Release' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Summary & Documents -->
                                <div class="space-y-6">
                                    <!-- Document Request Summary -->
                                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Documents to Request</h5>
                                        <div v-if="form.document_requests.length" class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                            <div v-for="(doc, i) in form.document_requests" :key="i" class="flex flex-col bg-white p-3 rounded border border-gray-100 shadow-sm transition hover:shadow-md">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="truncate pr-2 font-medium text-gray-800 text-sm" :title="doc.name">{{ doc.name }} <span v-if="doc.is_mandatory" class="text-red-500">*</span></span>
                                                    <span class="text-sm bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase tracking-wide border border-gray-200">{{ doc.stage }}</span>
                                                </div>
                                                
                                                <!-- Detailed Status & Actions -->
                                                <div class="flex items-center justify-between mt-1 pt-2 border-t border-gray-50">
                                                     <!-- Status Badge -->
                                                     <div class="flex items-center">
                                                         <span v-if="!doc.status || doc.status === 'Pending'" class="text-xs text-gray-400 italic flex items-center">
                                                             <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                             Pending Upload
                                                         </span>
                                                         <span v-else-if="doc.status === 'Submitted'" class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded flex items-center">
                                                             <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                             Uploaded
                                                         </span>
                                                         <span v-else-if="doc.status === 'Verified'" class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded flex items-center">
                                                             <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                             Verified
                                                         </span>
                                                          <span v-else-if="doc.status === 'Rejected'" class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded flex items-center">
                                                             <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                             Rejected
                                                         </span>
                                                     </div>

                                                     <!-- Actions -->
                                                     <div class="flex items-center space-x-1">
                                                         <a v-if="doc.file_path" :href="route('documents.stream', doc.id)" target="_blank" class="p-1 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded" title="View Document">
                                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                         </a>
                                                         
                                                         <template v-if="doc.status === 'Submitted'">
                                                             <button @click="verifyDoc(doc, 'Verified')" class="p-1 text-green-600 hover:bg-green-100 rounded" title="Approve">
                                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                             </button>
                                                             <button @click="verifyDoc(doc, 'Rejected')" class="p-1 text-red-600 hover:bg-red-100 rounded" title="Reject & Ask Reupload">
                                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                             </button>
                                                         </template>
                                                         
                                                         <!-- If not uploaded, allow remove if still draft -->
                                                         <button v-if="(!doc.status || doc.status === 'Pending') && !isManagementMode" @click="removeDoc(doc)" class="p-1 text-gray-400 hover:text-red-500 rounded" title="Remove Request">
                                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                         </button>
                                                     </div>
                                                </div>
                                                <!-- Rejection Reason Display -->
                                                <div v-if="doc.status === 'Rejected' && doc.rejection_reason" class="mt-1 text-sm text-red-600 bg-red-50 p-1.5 rounded">
                                                    Reason: {{ doc.rejection_reason }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-xs text-gray-500 italic text-center py-4 bg-white rounded border border-gray-100 border-dashed">No documents requested.</div>
                                    </div>

                                    <!-- Email Configuration -->
                                    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-4 shadow-sm space-y-4">
                                        <div class="flex items-center justify-between">
                                             <h5 class="text-xs font-bold text-gray-500 uppercase">Email Configuration</h5>
                                             <select v-model="form.email_type" class="text-xs border-gray-200 rounded p-1 focus:ring-indigo-500">
                                                 <option value="generic">Default</option>
                                                 <option value="custom">Custom</option>
                                                 <option value="reminder">Reminder</option>
                                             </select>
                                        </div>

                                        <!-- To Field (Read Only) -->
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 block mb-1">To</label>
                                            <input disabled :value="candidate?.email" class="w-full text-xs bg-gray-50 border border-gray-200 rounded px-2 py-1.5 text-gray-600">
                                        </div>

                                        <!-- CC -->
                                        <div class="animate-fade-in">
                                            <label class="text-xs font-bold text-gray-500 block mb-1">CC / Add People</label>
                                            <div class="flex flex-wrap gap-2 mb-2 bg-white p-2 border border-gray-200 rounded-md min-h-[36px] cursor-text" @click="$refs.ccInputRef?.focus()">
                                                <span v-for="email in form.cc_emails" :key="email" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ email }}
                                                    <button type="button" @click.stop="removeCc(email)" class="ml-1.5 text-gray-400 hover:text-red-500">×</button>
                                                </span>
                                                <input 
                                                    ref="ccInputRef"
                                                    type="email" 
                                                    v-model="ccInput" 
                                                    @keydown.enter.prevent="addCc"
                                                    @keydown.tab.prevent="addCc"
                                                    placeholder="Type email or search employee..." 
                                                    class="border-0 focus:ring-0 text-xs flex-1 min-w-[120px] p-0"
                                                >
                                            </div>
                                        </div>
                                        
                                        <!-- Subject/Body if Custom -->
                                        <div v-show="form.email_type !== 'generic'" class="space-y-3 animate-fade-in">
                                             <div>
                                                 <label class="text-xs text-gray-500 block mb-1">Subject</label>
                                                 <input v-model="form.email_subject" class="w-full text-xs font-bold border-gray-200 rounded px-2 py-1.5 focus:border-indigo-500 focus:ring-indigo-500">
                                             </div>
                                             <div>
                                                 <label class="text-xs text-gray-500 block mb-1">Message Body</label>
                                                 <RichTextEditor v-model="form.email_body" class="min-h-[120px]" />
                                             </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Live Email Preview -->
                                    <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col h-[380px] shadow-sm">
                                        <h5 class="text-xs font-bold text-gray-500 uppercase mb-3 flex items-center gap-2">
                                            <span>Live Email Preview</span>
                                            <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 text-sm font-bold">Encrypted Link Inside</span>
                                        </h5>
                                        <div class="flex-1 bg-gray-50 border border-gray-200 p-4 rounded-md text-sm text-gray-800 prose prose-sm overflow-y-auto custom-scrollbar">
                                            <div class="border-b border-gray-200 pb-2 mb-2">
                                                <p class="text-xs text-gray-500 mb-0.5">Subject:</p>
                                                <p class="font-bold text-gray-900">{{ form.email_subject }}</p>
                                            </div>
                                            <div v-html="liveEmailPreview" class="mt-2 text-gray-700 leading-relaxed"></div>
                                            <!-- Fake Attachments View -->
                                            <div class="mt-4 pt-3 border-t border-gray-200">
                                                <p class="text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Attached Files:</p>
                                                <div class="flex flex-col gap-1.5">
                                                    <div class="flex items-center text-xs text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-1.5 rounded w-full">
                                                        <span class="mr-2 text-lg">📄</span> 
                                                        <div class="flex flex-col">
                                                            <span class="font-bold">Offer_Letter.pdf</span>
                                                            <span class="text-sm opacity-75">Auto-Generated</span>
                                                        </div>
                                                    </div>
                                                    <div v-if="form.attachments.length" v-for="f in form.attachments" class="flex items-center text-xs text-gray-700 bg-white border border-gray-200 px-2 py-1.5 rounded w-full">
                                                        <span class="mr-2 text-lg">📎</span> {{ f.name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- STEP 5: Public Access -->
                        <div v-show="currentStep === 5" class="space-y-6 animate-fade-in-up">
                            <div class="bg-white/60 p-5 rounded-lg border border-gray-200 shadow-sm text-center py-10">
                                <h4 class="font-bold text-gray-800 text-lg mb-2">Public Offer Access</h4>
                                <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto">
                                    A public secure link and PIN will be generated for candidates to access their offer.
                                </p>
                                
                                <!-- Show if we have a valid link (Candidate ID or Token) -->
                                <!-- SUCCESS: Data Ready -->
                                <div v-if="encryptedLink && encryptedLink !== '#'" class="max-w-md mx-auto flex flex-col items-center animate-fade-in-up">
                                     <!-- Link Section -->
                                     <div class="w-full mb-6">
                                         <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Secure Link</span>
                                         <div class="flex items-center gap-2">
                                             <div class="relative flex-1 group">
                                                 <input disabled :value="encryptedLink" class="w-full text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded px-3 py-2 shadow-sm text-center">
                                             </div>
                                             <button @click="copyToClipboard(encryptedLink)" class="p-2 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded hover:bg-indigo-100 transition-colors" title="Copy Link">
                                                 {{ copied ? '✅' : '📋' }}
                                             </button>
                                         </div>
                                     </div>

                                     <!-- PIN Section -->
                                     <div class="w-full border-t border-gray-100 pt-6">
                                         <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Security PIN</span>
                                         <div class="flex items-center justify-center gap-4">
                                             <div v-for="(digit, i) in (form.otp ? String(form.otp).split('') : ['.','.','.','.','.','.'])" :key="i" 
                                                  class="w-10 h-12 flex items-center justify-center text-xl font-bold bg-white border-2 rounded-lg shadow-sm"
                                                  :class="form.otp ? 'border-indigo-100 text-indigo-700' : 'border-gray-100 text-gray-300'"
                                             >
                                                 {{ digit }}
                                             </div>
                                         </div>
                                         <p v-if="!form.otp" class="text-sm text-gray-400 mt-2 font-medium">(Generates automatically on save)</p>
                                     </div>
                                </div>

                                <!-- LOADING: Spinner -->
                                <div v-else class="max-w-md mx-auto flex flex-col items-center gap-4 py-8">
                                     <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                     <span class="text-sm font-medium text-gray-600">Preparing secure access...</span>
                                     <button @click="saveDraft(false)" class="text-xs text-indigo-500 hover:text-indigo-700 font-bold mt-2">
                                         Refresh
                                     </button>
                                </div>
                                
                                <div class="flex justify-center gap-4 mt-6">
                                    <button @click="previewOffer" class="px-4 py-2 bg-white border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Preview Offer Letter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 6: Review & Release -->
                        <div v-show="currentStep === 6" class="space-y-6 animate-fade-in-up">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Left: Offer Summary & Approvers -->
                                <div class="space-y-6">
                                    <div class="bg-white/60 p-5 rounded-lg border border-gray-200 shadow-sm">
                                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Offer Summary
                                        </h4>
                                        <dl class="grid grid-cols-2 gap-x-4 gap-y-4 text-sm">
                                            <div>
                                                <dt class="text-gray-500">Candidate</dt>
                                                <dd class="font-medium text-gray-900">{{ candidate?.name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Designation</dt>
                                                <dd class="font-medium text-gray-900">{{ form.designation }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">CTC (Annual)</dt>
                                                <dd class="font-bold text-green-600">{{ Number(form.salary_amount).toLocaleString() }} {{ form.salary_currency }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Joining Date</dt>
                                                <dd class="font-medium text-gray-900">{{ form.joining_date }}</dd>
                                            </div>
                                        </dl>
                                        
                                        <!-- Documents List -->
                                        <div class="mt-6 border-t pt-4">
                                            <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Required Documents</h5>
                                            <ul v-if="form.document_requests.length" class="space-y-1">
                                                <li v-for="doc in form.document_requests" :key="doc.name" class="flex items-center gap-2 text-sm text-gray-700">
                                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    {{ doc.name }} 
                                                    <span v-if="doc.is_mandatory" class="text-red-500 text-xs">*</span>
                                                </li>
                                            </ul>
                                            <p v-else class="text-xs text-gray-400 italic">No documents requested.</p>
                                        </div>
                                    </div>

                                    <!-- Approvers Workflow -->
                                    <div class="bg-white/60 p-5 rounded-lg border border-gray-200 shadow-sm relative">
                                        <h4 class="font-bold text-gray-800 mb-4 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                Approval Workflow
                                            </div>
                                            <!-- Bypass Option -->
                                            <label class="flex items-center gap-2 text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded cursor-pointer border border-red-100 hover:bg-red-100 transition-colors">
                                                <input type="checkbox" v-model="form.is_force_release" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                                                Force Release (Skip Approval)
                                            </label>
                                        </h4>
                                        
                                        <!-- Bypass Overlay -->
                                        <div v-if="form.is_force_release" class="absolute inset-x-0 bottom-0 top-16 bg-white/80 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center text-center p-4 border border-red-200 rounded m-2">
                                             <div class="bg-white p-3 rounded-full shadow-lg mb-2">
                                                 <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                             </div>
                                             <h5 class="text-sm font-bold text-red-800">Approval Bypassed</h5>
                                             <p class="text-xs text-red-600 max-w-[200px]">Offer will be sent directly to the candidate without internal review.</p>
                                        </div>

                                        <div class="space-y-3" :class="{'opacity-50 pointer-events-none': form.is_force_release}">
                                            <div v-for="uid in form.approver_ids" :key="uid" class="flex justify-between items-center bg-white p-2 rounded border border-gray-100">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-xs uppercase">
                                                        {{ getUserName(uid).charAt(0) }}
                                                    </div>
                                                    <span class="text-sm font-medium">{{ getUserName(uid) }}</span>
                                                </div>
                                                <button @click="toggleApprover(uid)" class="text-gray-400 hover:text-red-500">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                            
                                            <!-- Add Approver -->
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    v-model="approverSearch" 
                                                    placeholder="Search to add approver..." 
                                                    class="w-full text-sm border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500"
                                                >
                                                <div v-if="approverResults.length > 0" class="absolute z-10 w-full bg-white shadow-lg border rounded-md mt-1 max-h-40 overflow-y-auto">
                                                    <div 
                                                        v-for="u in approverResults" 
                                                        :key="u.id" 
                                                        @click="toggleApprover(u.id); approverSearch = ''"
                                                        class="px-4 py-2 hover:bg-amber-50 cursor-pointer text-sm flex items-center gap-2"
                                                    >
                                                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600">{{ u.name.charAt(0) }}</div>
                                                        <span>{{ u.name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Email & Actions -->
                                <div class="space-y-6">
                                    <div class="bg-white/60 p-5 rounded-lg border border-gray-200 shadow-sm h-full flex flex-col">
                                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            Email Preview
                                        </h4>
                                        
                                        <div class="flex-1 bg-gray-50 p-4 rounded border border-gray-200 mb-4 overflow-y-auto max-h-[300px]">
                                            <div class="mb-2 text-xs text-gray-500 border-b pb-2">
                                                <p><strong>To:</strong> {{ candidate?.email }}</p>
                                                <p><strong>Subject:</strong> {{ form.email_subject || 'Offer from ' + (form.company_name || 'Our Company') }}</p>
                                                <p v-if="form.cc_emails.length"><strong>CC:</strong> {{ form.cc_emails.join(', ') }}</p>
                                            </div>
                                            <div class="prose prose-sm prose-indigo" v-html="liveEmailPreview"></div>
                                        </div>

                                        <!-- Final Action Buttons -->
                                        <div class="grid grid-cols-2 gap-4 mt-auto">
                                            <button 
                                                @click="saveDraft(false)" 
                                                :disabled="form.processing"
                                                class="px-4 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all"
                                            >
                                                Save as Draft
                                            </button>
                                            
                                            <button 
                                                v-if="form.is_force_release"
                                                @click="submitOffer('release')" 
                                                :disabled="form.processing"
                                                class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-lg shadow-red-200 font-bold transition-all flex items-center justify-center gap-2 border border-red-500"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                Force Release
                                            </button>

                                            <button 
                                                v-else-if="form.approver_ids.length > 0"
                                                @click="submitOffer('approval')" 
                                                :disabled="form.processing"
                                                class="px-4 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 shadow-lg shadow-amber-200 font-bold transition-all flex items-center justify-center gap-2"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Request Approval
                                            </button>
                                            
                                            <button 
                                                v-else
                                                @click="submitOffer('release')" 
                                                :disabled="form.processing"
                                                class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 font-bold transition-all flex items-center justify-center gap-2"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                Release Offer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-between pt-4 border-t border-gray-100">
                             <div class="flex items-center space-x-2">
                                <button type="button" @click="prevStep" :disabled="currentStep === 1 || isManagementMode" class="px-4 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Back</button>
                                <button v-if="!isManagementMode" @click="saveDraft(false)" type="button" class="text-xs text-gray-500 underline hover:text-indigo-600">Save Draft</button>
                             </div>
                             
                             <div class="flex items-center space-x-3">
                                <button type="button" @click="$emit('close')" class="text-sm font-medium text-gray-500 hover:text-gray-900">Cancel</button>
                                <PrimaryButton v-if="currentStep < 6 && !isManagementMode" @click="nextStep" class="w-24 justify-center">Next</PrimaryButton>
                                
                                <div v-if="currentStep === 6" class="flex gap-2">
                                     <PrimaryButton 
                                        v-if="form.status === 'Approved'"
                                        @click="submitOffer('release')"
                                        class="bg-green-600 hover:bg-green-700 justify-center gap-1"
                                     >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        Release Offer
                                     </PrimaryButton>
                                     
                                     <PrimaryButton 
                                        v-else-if="!isManagementMode"
                                        @click="submitOffer(needsApproval ? 'approval' : 'release')" 
                                        class="bg-indigo-600 hover:bg-indigo-700 justify-center"
                                     >
                                        {{ needsApproval ? 'Request Approval' : 'Release Offer' }}
                                     </PrimaryButton>
                                    <!-- Management buttons handled in stage 6 content area -->
                                </div>
                             </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';

const props = defineProps({
    show: Boolean,
    candidate: Object,
    applicationId: Number,
    templates: { type: Array, default: () => [] },
    salaryStructures: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] }, 
    designation: String,
    offerSummary: Object
});

const emit = defineEmits(['close', 'success']);

const currentStep = ref(1);
const templateSearch = ref('');
const attachments = ref([]);
const preOfferPresets = ['Aadhar Card', 'PAN Card', 'Last 3 Payslips', 'Relieving Letter'];
const postOfferPresets = ['Bank Details', 'Signed NDA', 'IT Declaration', 'PF Transfer Form'];

const form = useForm({
    job_application_id: props.applicationId,
    template_ids: [],
    salary_structure_id: '',
    offer_mode: 'template',
    salary_currency: 'INR',
    salary_amount: '',
    designation: '',
    offer_date: new Date().toISOString().split('T')[0],
    joining_date: '',
    expiry_date: '',
    salary_breakdown: { components: [] },
    is_conditional: false,
    document_requests: [],
    approver_ids: [],
    is_force_release: false,
    email_type: 'generic', // generic, custom, reminder
    cc_emails: [],
    email_subject: 'Official Job Offer',
    email_body: '', // will be auto-filled if custom
    is_draft: false,
    attachments: [],
    // Edit Fields
    id: null,
    status: 'Draft',
    otp: null
});

const ccInput = ref('');
const ccInputRef = ref(null);
const copied = ref(false);

const addCc = () => {
    if (ccInput.value && /^\S+@\S+\.\S+$/.test(ccInput.value)) {
        if (!form.cc_emails.includes(ccInput.value)) form.cc_emails.push(ccInput.value);
        ccInput.value = '';
    }
};
const removeCc = (email) => form.cc_emails = form.cc_emails.filter(e => e !== email);

const encryptedLink = computed(() => {
    // User requested format: URL/uuid (candidate.id) + encrypted email
    // This allows link generation even before Token is fully synced if we have candidate ID.
    // However, if we have a specific Token, that is safer/more direct.
    // OfferPortalController::resolveOffer supports both.
    
    // Fallback to Candidate ID if Token missing (solves "stuck" UI)
    const identifier = form.token || props.candidate?.id;
    if (!identifier) return '#';
    
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    
    // Basic encoding for "e" param to match user expectation (just base64 for display/obfuscation)
    // Real security relies on the PIN.
    const email = props.candidate?.email || '';
    const encodedEmail = btoa(email); 
    
    // Route: portal.offer.login -> /portal/offer/{token}/login
    // We use 'identifier' (candidate ID or token)
    return `${origin}/portal/offer/${identifier}/login?e=${encodedEmail}`;
});

const genericEmailPreview = computed(() => {
    const link = encryptedLink.value !== '#' ? encryptedLink.value : '(Link generated after save)';
    const pin = form.otp || '(Generated on Save)';
    
    return `<p>Dear ${props.candidate?.name || 'Candidate'},</p>
    <p>We are pleased to share your offer letter. Please click the link below to view details and accept.</p>
    <p><a href="${link}" target="_blank">View Offer</a></p>
    <p><strong>Security PIN:</strong> ${pin}</p>`;
});

// Send Document Request Email
const sendDocRequestEmail = () => {
    if (!confirm('This will send an email asking the candidate to upload required documents. Continue?')) return;
    
    // Ensure we send content even if in 'Default' mode
    const finalBody = (form.email_type === 'generic' || !form.email_body) 
        ? genericEmailPreview.value 
        : form.email_body;

    router.post(route('talent.offers.send', form.id), {
        email_subject: form.email_subject,
        email_body: finalBody,
        cc_emails: form.cc_emails
    }, {
        onSuccess: () => alert('Document request email sent.')
    });
};

// Load Existing Offer
const loadOfferData = async (id) => {
    try {
        const res = await axios.get(route('talent.offers.show', id));
        const o = res.data.offer;
        
        form.id = o.id;
        form.job_application_id = o.job_application_id;
        form.template_ids = (o.template_ids && o.template_ids.length) ? o.template_ids : [o.document_template_id];
        form.salary_structure_id = o.salary_structure_id;
        form.salary_amount = o.salary_amount;
        form.salary_currency = o.salary_currency;
        form.designation = o.designation;
        form.offer_date = o.offer_date ? new Date(o.offer_date).toISOString().split('T')[0] : '';
        form.joining_date = o.joining_date ? new Date(o.joining_date).toISOString().split('T')[0] : '';
        form.expiry_date = o.expiry_date ? new Date(o.expiry_date).toISOString().split('T')[0] : '';
        form.salary_breakdown = o.salary_breakdown || { components: [] };
        form.is_conditional = !!o.is_conditional;
        
        // Map documents
        form.document_requests = (o.documents || []).map(d => ({
            id: d.id,
            name: d.name,
            stage: d.stage,
            is_mandatory: !!d.is_mandatory,
            status: d.status,
            file_path: d.file_path,
            rejection_reason: d.rejection_reason
        }));
        
        form.approver_ids = o.approvers || [];
        form.approval_data = o.approval_data || {}; // Store approval details
        form.status = o.status;
        form.otp = o.otp;
        
        if (o.email_config) {
            form.email_type = o.email_config.type || 'generic';
            form.email_subject = o.email_config.subject;
            form.email_body = o.email_config.body;
            form.cc_emails = o.email_config.cc_emails || [];
        }

        // Jump to logic
         if (['Sent', 'Accepted', 'Pending_Approval', 'Approved', 'Rejected'].includes(o.status)) {
             currentStep.value = 6;
         }
    } catch (e) {
        console.error(e);
        alert('Failed to load offer details.');
    }
};

// Load Existing Offer
watch(() => props.offerSummary, async (summary) => {
    if (summary) {
        await loadOfferData(summary.id);
    }
}, { immediate: true });

// Handle New Offer Initialization (When opening without existing summary)
watch(() => props.show, (val) => {
    if (val && !props.offerSummary) {
        form.reset();
        form.clearErrors();
        form.job_application_id = props.applicationId;
        currentStep.value = 1;
        // Set default template if only one exists and nothing selected
        if (props.templates.length === 1) {
            form.template_ids = [props.templates[0].id];
        }
    }
});

const isManagementMode = computed(() => form.id && ['Sent', 'Accepted', 'Pending_Approval', 'Pending_Docs', 'Approved', 'Rejected'].includes(form.status));
const canApprove = computed(() => {
    const uid = usePage().props.auth.user.id;
    return form.approver_ids.includes(uid);
});
const stepTitle = computed(() => {
    if (isManagementMode.value && currentStep.value === 6) return 'Offer Management';
    return ['Config', 'Financials', 'Breakdown', 'Docs', 'Public Access', 'Release'][currentStep.value - 1];
});

// Financial Logic
const salaryDetails = computed(() => {
    const ctc = parseFloat(form.salary_amount) || 0;
    const components = form.salary_breakdown?.components || []; // Safe access
    let grossParams = 0;
    let deductions = 0;
    components.forEach(c => {
        const val = parseFloat(c.value) || 0;
        if (c.type === 'deduction') deductions += val;
        else if (!c.name.toLowerCase().includes('employer')) grossParams += val;
    });
    return { grossMonthly: grossParams / 12, deductionsMonthly: deductions / 12, netMonthly: (grossParams - deductions) / 12 };
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
         copied.value = true;
         alert('Link copied to clipboard!'); // Fallback alert, though checkbox helps
         setTimeout(() => copied.value = false, 2000);
    });
};

const liveEmailPreview = computed(() => {
    if (form.email_type !== 'generic') {
        // Simple variable replacement for preview
        let content = form.email_body || '';
        content = content.replace(/{name}/g, props.candidate?.name || 'Candidate');
        content = content.replace(/{link}/g, `<a href="${encryptedLink.value}">Link</a>`);
        return content || '<span class="text-gray-400 italic">Start typing to preview...</span>';
    }
    return genericEmailPreview.value;
});

const availableTemplates = computed(() => {
    if (!templateSearch.value) return [];
    const temps = Array.isArray(props.templates) ? props.templates : [];
    return temps.filter(t => t.name.toLowerCase().includes(templateSearch.value.toLowerCase()) && !form.template_ids.includes(t.id));
});
const filteredUsers = computed(() => {
    if (!ccInput.value) return [];
    const lower = ccInput.value.toLowerCase();
    // Exclude already added emails
    return (props.users || []).filter(u => 
        (u.name.toLowerCase().includes(lower) || u.email.toLowerCase().includes(lower)) && 
        !form.cc_emails.includes(u.email)
    ).slice(0, 5);
});

const selectUserCc = (email) => {
    if (email && !form.cc_emails.includes(email)) {
        form.cc_emails.push(email);
    }
    ccInput.value = '';
};


const getTemplateName = (id) => props.templates.find(t => t.id === id)?.name || id;

// Actions
const goToStep = (step) => { if (step < currentStep.value || isManagementMode.value) currentStep.value = step; };
const nextStep = () => {
    if (currentStep.value === 1) {
        if (!form.offer_mode) return alert('Select mode');
        if (form.offer_mode === 'template' && !form.template_ids.length && !form.document_template_id) return alert('Select a template');
    }
    if (currentStep.value === 2) {
         if (!form.salary_amount) return alert('Enter CTC');
         // Basic Date Logic
         if (form.joining_date && form.offer_date && new Date(form.joining_date) < new Date(form.offer_date)) {
             return alert('Joining Date cannot be before Offer Date');
         }
    }
    
    // Auto-Save when entering Stage 5 (Pre-Check)
    if (currentStep.value === 4) { // Moving from 4 to 5
        saveDraft(true); // Silent save
    }

    if (currentStep.value < 6) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};const toggleTemplate = (id) => {
    const idx = form.template_ids.indexOf(id);
    if (idx > -1) form.template_ids.splice(idx, 1);
    else form.template_ids.push(id);
};

// Docs
const addDoc = (stage) => form.document_requests.push({ name: '', is_mandatory: true, stage });
const removeDoc = (doc) => { const i = form.document_requests.indexOf(doc); if (i > -1) form.document_requests.splice(i, 1); };
const quickAdd = (stage, presets) => {
    presets.forEach(p => {
        if (!form.document_requests.some(d => d.name === p && d.stage === stage)) form.document_requests.push({ name: p, is_mandatory: true, stage });
    });
};

// Attachments
const handleAttachments = (e) => {
    const files = Array.from(e.target.files);
    files.forEach(f => {
        if(f.size > 5 * 1024 * 1024) return alert(`File ${f.name} is too large (Max 5MB)`);
        form.attachments.push(f);
    });
    e.target.value = '';
};
const removeAttachment = (i) => form.attachments.splice(i, 1);

// Submit
const submitOffer = (action) => {
    form.is_draft = false;
    // Set status based on action
    if (action === 'approval') {
        form.status = 'Pending_Approval';
    } else if (action === 'release') {
        form.status = 'Sent';
    }
    
    // We might need to pass this 'action' to backend explicitly if status isn't enough
    // But usually saving with status='Pending_Approval' triggers the notification flow in Controller.
    
    form.post(route('talent.offers.store'), {
        onSuccess: () => { 
            // alert('Offer ' + (action === 'approval' ? 'submitted for approval!' : 'released successfully!'));
            emit('success'); 
            emit('close'); 
        },
        onError: (e) => alert('Failed to submit: ' + JSON.stringify(e))
    });
};

const saveDraft = (silent = false) => {
    form.is_draft = true;
    form.post(route('talent.offers.store'), {
         preserveScroll: true,
         onSuccess: (page) => { 
             const newOffer = page.props.flash?.offer_data;
             if (newOffer) {
                 form.id = newOffer.id;
                 form.token = newOffer.token;
                 form.otp = newOffer.otp;
                 
                 // Sync Documents (Updated IDs)
                 if (newOffer.documents) {
                     form.document_requests = newOffer.documents.map(d => ({
                        id: d.id,
                        name: d.name,
                        stage: d.stage,
                        is_mandatory: !!d.is_mandatory,
                        status: d.status,
                        file_path: d.file_path,
                        rejection_reason: d.rejection_reason
                     }));
                 }
             }
             if (!silent) {
                 emit('success'); 
                 emit('close');
             }
         },
         onError: (errors) => {
             console.error('Draft Save Failed:', errors);
             if (!silent) alert('Failed to save draft:\n' + Object.values(errors).join('\n'));
         }
    });
};

const previewOffer = () => {
    // Reusing the form submission logic for preview new tab
    const tempForm = document.createElement('form');
    // HARDCODED URL to prevent Ziggy issues if route not found
    tempForm.action = `${window.location.origin}/talent/offers/preview`;
    tempForm.method = 'POST';
    tempForm.target = '_blank';
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const input = (n, v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; tempForm.appendChild(i); };
    
    input('_token', token);
    input('job_application_id', form.job_application_id);
    input('salary_amount', form.salary_amount);
    input('salary_currency', form.salary_currency);
    input('designation', form.designation);
    input('joining_date', form.joining_date);
    input('offer_date', form.offer_date);
    input('expiry_date', form.expiry_date);
    input('salary_structure_id', form.salary_structure_id || ''); // Add structure ID
    
    // Pass Template IDs (Join as string)
    if (form.template_ids && form.template_ids.length > 0) {
        input('template_ids', form.template_ids.join(','));
        input('document_template_id', form.template_ids[0]); // Fallback/Primary
    } else {
         input('document_template_id', form.document_template_id || '');
    }
    
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
};

const needsApproval = ref(false);
const approverSearch = ref('');

const approverResults = computed(() => {
    if (!approverSearch.value) return [];
    const lower = approverSearch.value.toLowerCase();
    return (props.users || []).filter(u => u.name.toLowerCase().includes(lower) && !form.approver_ids.includes(u.id)).slice(0, 5);
});

const toggleApprover = (id) => {
    const idx = form.approver_ids.indexOf(id);
    if (idx > -1) form.approver_ids.splice(idx, 1);
    else form.approver_ids.push(id);
};

const getUserName = (id) => props.users.find(u => u.id === id)?.name || id;

// Verify Doc Logic (Enhanced)
const verifyDoc = (doc, status) => {
    let reason = null;
    if (status === 'Rejected') {
        reason = prompt('Please enter the reason for rejection (this will be visible to the candidate):');
        if (reason === null) return; // Cancelled
        if (!reason.trim()) return alert('Rejection reason is required.');
    }
    
    // Optimistic Update
    const originalStatus = doc.status;
    doc.status = status; 
    
    router.post(route('talent.offers.documents.verify', doc.id), {
        status,
        reason
    }, {
        onSuccess: () => {
             // Success feedback
        },
        onError: () => {
            doc.status = originalStatus; // Revert
            alert('Failed to update document status.');
        }
    });
};

// End of helpers

const resendOffer = () => {
    if (!confirm('Resend offer email to candidate?')) return;
    router.post(route('talent.offers.resend', form.id), {
        cc_emails: form.cc_emails
    }, {
        onSuccess: () => alert('Email resent successfully.')
    });
};

const getStepLabel = (s) => ['Config', 'CTC', 'Breakdown', 'Docs', 'Review', 'Release'][s - 1];

// Watchers
watch(currentStep, (step) => {
    // Auto-switch Subject based on Step Context
    if (step === 4) { // Docs / Pre-Check
        if (!form.email_subject || form.email_subject === 'Official Job Offer') {
            form.email_subject = 'Pending Documents Check';
            form.email_type = 'generic'; // Reset to default body logic
        }
    } else if (step === 6) { // Release
        if (form.email_subject === 'Pending Documents Check') {
            form.email_subject = 'Official Job Offer';
            form.email_type = 'generic';
        }
    }
});

watch(() => props.show, (val) => {
    if (val) {
        form.reset();
        // Don't reset if editing immediately? 
        // Watcher on offerSummary handles population.
        // But if creating NEW, we need reset.
        // Logic: if offerSummary is null, reset.
        if (!props.offerSummary) {
            form.job_application_id = props.applicationId;
            if (props.designation) form.designation = props.designation;
            currentStep.value = 1;

            // Generate Token Immediately (Auto-Create Draft)
            // Use setTimeout to allow form population and avoid cycle
            setTimeout(() => {
                if (!form.id && form.job_application_id) {
                    saveDraft(true);
                }
            }, 500);
        } else {
             // Populate Existing Offer
             const o = props.offerSummary;
             form.id = o.id;
             form.status = o.status; // Critical for mode detection
             form.job_application_id = o.job_application_id;
             form.template_ids = o.template_ids || [];
             form.salary_structure_id = o.salary_structure_id;
             form.salary_amount = o.salary_amount;
             form.salary_currency = o.salary_currency;
             form.designation = o.designation;
             form.offer_date = o.offer_date;
             form.joining_date = o.joining_date;
             form.expiry_date = o.expiry_date;
             form.salary_breakdown = o.salary_breakdown || { components: [] };
             form.email_subject = o.email_subject || '';
             form.email_body = o.email_body || '';
             form.approver_ids = o.approvers || [];
             form.is_force_release = !!o.is_force_release;
             form.cc_emails = o.cc_emails || [];

             // Load Documents
             if (o.documents) {
                 form.document_requests = o.documents.map(d => ({
                     id: d.id,
                     name: d.name,
                     stage: d.stage,
                     is_mandatory: !!d.is_mandatory,
                     status: d.status,
                     file_path: d.file_path,
                     rejection_reason: d.rejection_reason
                 }));
             }
             
             // If active offer, jump to Management Dashboard (Stage 6)
             if (['Sent', 'Accepted', 'Pending_Approval', 'Pending_Docs', 'Viewed'].includes(o.status)) {
                 currentStep.value = 6;
             } else {
                 currentStep.value = 1; // Draft
             }
        }
    }
});

watch([() => form.salary_structure_id, () => form.salary_amount], ([id, amt]) => {
    if (!id) return;
    const s = props.salaryStructures.find(x => x.id === id);
    if (s && s.components) {
        form.salary_breakdown.components = s.components.map(c => ({
            name: c.name,
            value: c.calculation_type === 'percentage' ? (amt * c.value / 100) : c.value,
            type: c.type
        }));
    }
});

</script>

<style scoped>
.toggle-checkbox:checked { right: 0; border-color: #68D391; }
.toggle-checkbox:checked + .toggle-label { background-color: #68D391; }
</style>
