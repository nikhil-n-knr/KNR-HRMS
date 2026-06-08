<template>
  <div class="space-y-6">
    <!-- Sub Tabs -->
    <div class="flex gap-4 p-1 bg-gray-100 rounded-xl w-max">
      <button 
        v-for="m in modules" 
        :key="m.id"
        @click="activeModule = m.id"
        :class="[
          'px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-all',
          activeModule === m.id ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        {{ m.name }}
      </button>
    </div>

    <!-- Module Views -->
    <div class="grid grid-cols-1 gap-6">
      <!-- 1. Multi-State Support (Branches) -->
      <div v-if="activeModule === 'branches'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gradient-to-r from-gray-50 to-white">
          <div>
            <h3 class="text-lg font-bold text-gray-800">State Configuration Dashboard</h3>
            <p class="text-xs text-gray-500">Map geographical branches and assign base state compliance logic</p>
          </div>
          <button @click="openBranchModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-lg shadow-indigo-100 hover:scale-105 transition-transform">
            + Add New State Branch
          </button>
        </div>
        <div class="p-0">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-gray-50/50 text-sm uppercase tracking-widest text-gray-400 font-bold">
                <th class="px-6 py-4">Branch/Code</th>
                <th class="px-6 py-4">State/City</th>
                <th class="px-6 py-4">Compliance Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="branch in branches" :key="branch.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-800">{{ branch.name }}</div>
                  <div class="text-sm text-gray-400 font-mono">{{ branch.code }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="px-1.5 py-0.5 bg-gray-100 rounded font-mono text-sm font-bold">{{ branch.state_code }}</span>
                    <span class="text-sm font-medium text-gray-600">{{ branch.city }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <span :class="['px-2 py-0.5 rounded-full text-sm font-black uppercase tracking-tighter', branch.pt_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400']">PT</span>
                    <span :class="['px-2 py-0.5 rounded-full text-sm font-black uppercase tracking-tighter', branch.lwf_enabled ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400']">LWF</span>
                    <span v-if="branch.is_hq" class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-sm font-black uppercase tracking-tighter">HQ</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="openBranchModal(branch)" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"><PencilSquareIcon class="w-4 h-4"/></button>
                    <button @click="deleteBranch(branch.id)" class="p-2 text-gray-400 hover:text-rose-600 transition-colors"><TrashIcon class="w-4 h-4"/></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. State Rules (Calculation Engine) -->
      <div v-if="activeModule === 'rules'" class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex h-[600px]">
          <!-- Left Sidebar -->
          <div class="w-64 border-r border-gray-100 bg-gray-50/30 p-4 space-y-2">
            <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Rule Categories</h4>
            <button 
              v-for="cat in ruleCategories" 
              :key="cat.id"
              @click="activeRuleCat = cat.id"
              :class="['w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center gap-3', activeRuleCat === cat.id ? 'bg-white shadow-sm border border-gray-100 text-emerald-600' : 'text-gray-500 hover:bg-gray-100']"
            >
              <component :is="cat.icon" class="w-4 h-4" />
              {{ cat.name }}
            </button>
          </div>
          <!-- Main Rules Content -->
          <div class="flex-1 flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
              <div class="flex items-center gap-4">
                <select v-model="selectedState" class="bg-gray-100 border-none rounded-lg text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                  <option value="KA">Karnataka (Bangalore)</option>
                  <option value="TN">Tamil Nadu (Chennai)</option>
                  <option value="TS">Telangana (Hyderabad)</option>
                  <option value="KL">Kerala (Kochi/Trivandrum)</option>
                  <option value="AP">Andhra Pradesh</option>
                  <option value="DL">Delhi (NCR)</option>
                </select>
                <div v-if="loadingRules" class="animate-spin rounded-full h-4 w-4 border-2 border-emerald-500 border-t-transparent"></div>
              </div>
              <button @click="saveRules" :disabled="savingRules" :class="['px-6 py-2 rounded-lg text-sm font-black shadow-lg transition-all flex items-center gap-2', savingRules ? 'bg-gray-400 text-white cursor-not-allowed' : 'bg-emerald-600 text-white shadow-emerald-100 hover:scale-105']">
                <span v-if="savingRules" class="animate-spin h-3 w-3 border-2 border-white/30 border-t-white rounded-full"></span>
                {{ savingRules ? 'Saving Configuration...' : 'Save Rule Configuration' }}
              </button>
            </div>
            <div class="p-8 overflow-y-auto">
              <!-- PT Rules View -->
              <div v-if="activeRuleCat === 'pt'" class="space-y-8">
                <div v-if="selectedState === 'DL'" class="p-12 text-center">
                    <NoSymbolIcon class="w-16 h-16 text-gray-200 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-gray-400">Not Applicable</h3>
                    <p class="text-sm text-gray-400 max-w-xs mx-auto">The Delhi government does not levy Professional Tax. System logic is disabled for this region.</p>
                </div>
                <div v-else class="space-y-6">
                  <div class="flex items-center justify-between">
                    <h5 class="text-sm font-bold text-gray-700">Deduction Slabs ({{ selectedState }})</h5>
                    <button @click="addSlab" class="text-xs font-bold text-indigo-600 hover:underline">+ Add Slab</button>
                  </div>
                  <div class="grid grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl text-sm font-black uppercase text-gray-400 tracking-widest">
                    <div>Gross Salary (From)</div>
                    <div>Gross Salary (To)</div>
                    <div>PT Amount (₹)</div>
                  </div>
                  <div v-for="(slab, index) in rulesData.pt?.slabs" :key="index" class="grid grid-cols-3 gap-4 group">
                    <input v-model="slab.min" type="number" class="block w-full rounded-lg border-gray-200 text-sm font-medium">
                    <input v-model="slab.max" type="number" class="block w-full rounded-lg border-gray-200 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <input v-model="slab.amount" type="number" class="block w-full rounded-lg border-gray-200 text-sm font-bold text-emerald-600">
                        <button @click="removeSlab(index)" class="p-1 text-gray-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- LWF Rules View -->
              <div v-if="activeRuleCat === 'lwf'" class="space-y-6">
                 <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Employee Share (EE)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 font-bold">₹</span>
                            <input v-model="rulesData.lwf.employee_share" type="number" class="pl-8 block w-full rounded-xl border-gray-200 text-lg font-black text-indigo-600">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Employer Share (ER)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 font-bold">₹</span>
                            <input v-model="rulesData.lwf.employer_share" type="number" class="pl-8 block w-full rounded-xl border-gray-200 text-lg font-black text-indigo-600">
                        </div>
                    </div>
                 </div>
                 <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 flex gap-4">
                    <InformationCircleIcon class="w-6 h-6 text-amber-600 shrink-0" />
                    <p class="text-xs text-amber-700 font-medium leading-relaxed">
                        In <strong>{{ selectedState }}</strong>, LWF is typically deducted {{ rulesData.lwf?.frequency || 'annually' }}. 
                        Current total liability: ₹{{ Number(rulesData.lwf?.employee_share || 0) + Number(rulesData.lwf?.employer_share || 0) }} per employee.
                    </p>
                 </div>
              </div>

              <!-- Leave Mandates View -->
              <div v-if="activeRuleCat === 'leave'" class="space-y-6">
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Privileged Leave (PL) / Earned Leave</label>
                        <input v-model="rulesData.leave.pl_days" type="number" placeholder="Days per year" class="block w-full rounded-xl border-gray-200 text-lg font-black text-emerald-600">
                    </div>
                    <div class="space-y-4">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Sick / Casual Leave (SL/CL)</label>
                        <input v-model="rulesData.leave.sl_cl_days" type="number" placeholder="Days per year" class="block w-full rounded-xl border-gray-200 text-lg font-black text-blue-600">
                    </div>
                </div>
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <p class="text-xs text-emerald-700 font-medium leading-relaxed">
                        Statutory leaves are calculated based on the <strong>Shops & Establishments Act</strong> of {{ selectedState }}. 
                        Changes here will affect new policy generation.
                    </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Licence Vault (Document Tracker) -->
      <div v-if="activeModule === 'licences'" class="space-y-6">
        <div class="grid grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Active Licences</div>
                <div class="text-3xl font-black text-emerald-600">{{ licences.length }}</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Expiring in 30 Days</div>
                <div class="text-3xl font-black text-rose-600">{{ licences.filter(l => isExpiringSoon(l.expiry_date)).length }}</div>
            </div>
            <div @click="openLicenceModal()" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 border-dashed flex items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors">
                <div class="text-center">
                    <CloudArrowUpIcon class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    <div class="text-xs font-bold text-gray-400">Upload New Licence</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-sm uppercase tracking-widest text-gray-400 font-bold">
                        <th class="px-6 py-4">Document Details</th>
                        <th class="px-6 py-4">Region</th>
                        <th class="px-6 py-4">Expiry Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="doc in licences" :key="doc.id" class="group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <DocumentIcon class="w-8 h-8 text-gray-300" />
                                <div>
                                    <div class="font-bold text-gray-800 text-sm">{{ doc.name }}</div>
                                    <div class="text-sm text-gray-400 font-medium">PDF Document • 2.4 MB</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-gray-100 rounded-lg text-sm font-black">{{ doc.state_code }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ doc.location?.city || 'Global' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div :class="['text-sm font-bold', isExpiringSoon(doc.expiry_date) ? 'text-rose-600 animate-pulse' : 'text-gray-700']">
                                {{ formatDate(doc.expiry_date) }}
                            </div>
                            <div class="text-sm text-gray-400 font-medium">{{ getDaysRemaining(doc.expiry_date) }} days left</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="deleteLicence(doc.id)" class="p-2 text-gray-400 hover:text-rose-600 transition-colors"><TrashIcon class="w-4 h-4"/></button>
                                <a :href="'/storage/' + doc.document_path" target="_blank" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity flex items-center">Download</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>

      <!-- 4. Wage Variations (Sandbox) -->
      <div v-if="activeModule === 'sandbox'" class="space-y-6">
        <div class="flex gap-4 p-1 bg-gray-800 rounded-xl w-max">
            <button @click="sandboxMode = 'calc'" :class="['px-4 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all', sandboxMode === 'calc' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/40' : 'text-gray-400 hover:text-gray-200']">Simulation</button>
            <button @click="sandboxMode = 'bench'" :class="['px-4 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all', sandboxMode === 'bench' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-gray-400 hover:text-gray-200']">Admin Benchmarks</button>
        </div>

        <div v-if="sandboxMode === 'calc'" class="bg-gray-900 rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500 rounded-full blur-[100px] opacity-20 -mr-32 -mt-32"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row gap-12">
            <div class="w-full md:w-1/2 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-2xl flex items-center justify-center border border-emerald-500/30">
                        <CurrencyRupeeIcon class="w-6 h-6 text-emerald-500" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Salary Sandbox</h3>
                        <p class="text-xs text-gray-400">Validate offer structures against Minimum Wage Laws</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-500 uppercase tracking-widest">State Region</label>
                            <select v-model="sandbox.state" class="w-full bg-gray-800 border-gray-700 rounded-xl text-sm font-bold focus:ring-emerald-500">
                                <option value="KA">Karnataka (Bangalore Zone 1)</option>
                                <option value="TN">Tamil Nadu (Chennai)</option>
                                <option value="KL">Kerala (Kochi/Trivandrum)</option>
                                <option value="TS">Telangana (Hyderabad)</option>
                                <option value="DL">Delhi (NCR)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-500 uppercase tracking-widest">Skill Level</label>
                            <select v-model="sandbox.skill" class="w-full bg-gray-800 border-gray-700 rounded-xl text-sm font-bold focus:ring-emerald-500">
                                <option>Unskilled</option>
                                <option>Semi-Skilled</option>
                                <option>Skilled</option>
                                <option>Highly Skilled</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-500 uppercase tracking-widest">Proposed Basic Salary (Monthly)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 font-bold">₹</span>
                            <input v-model="sandbox.proposed" type="number" class="w-full bg-gray-800 border-gray-700 rounded-xl pl-10 text-lg font-black text-emerald-400 focus:ring-emerald-500">
                        </div>
                    </div>
                    <button @click="validateSandbox" :disabled="valLoading" class="w-full py-4 bg-emerald-600 rounded-xl font-black uppercase tracking-[0.2em] shadow-xl shadow-emerald-900/40 hover:bg-emerald-500 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <span v-if="valLoading" class="animate-spin h-4 w-4 border-2 border-white/30 border-t-white rounded-full"></span>
                        {{ valLoading ? 'Verifying...' : 'Validate Salary Breakdown' }}
                    </button>
                </div>
            </div>

            <div class="w-full md:w-1/2 flex items-center justify-center">
                <div v-if="validationResult" :class="['w-full p-8 rounded-3xl border transition-all duration-500', validationResult.valid ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-rose-500/10 border-rose-500/30 ring-4 ring-rose-500/20 shadow-2xl shadow-rose-900/40']">
                    <div class="flex items-center gap-4 mb-6">
                        <component :is="validationResult.valid ? CheckBadgeIcon : ShieldExclamationIcon" :class="['w-10 h-10', validationResult.valid ? 'text-emerald-500' : 'text-rose-500']" />
                        <div>
                            <div :class="['text-xs font-black uppercase tracking-widest', validationResult.valid ? 'text-emerald-500' : 'text-rose-500']">Verification Result</div>
                            <div class="text-lg font-bold">{{ validationResult.valid ? 'Compliant Logic' : 'Legal Breach Detected' }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-black/40 rounded-2xl">
                            <div class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">State Required Minimum</div>
                            <div class="text-2xl font-black">₹{{ formatNumber(validationResult.min_required || 19537) }}</div>
                        </div>
                        <p class="text-sm text-gray-300 leading-relaxed font-medium">
                            {{ validationResult.message || 'The proposed salary structure falls within the legal guidelines for IT/Commercial establishments in the selected zone.' }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-center p-12 border-2 border-dashed border-gray-800 rounded-3xl w-full">
                    <FingerPrintIcon class="w-16 h-16 text-gray-800 mx-auto mb-4" />
                    <p class="text-sm text-gray-600 font-bold uppercase tracking-widest">Awaiting Input Scan</p>
                </div>
            </div>
        </div>
        </div>
        
        <div v-if="sandboxMode === 'bench'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest">Legal Minimum Wages</h4>
                <button @click="openWageModal()" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold">+ New Benchmark</button>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-sm uppercase font-black text-gray-400 tracking-widest">
                            <th class="px-6 py-4">State</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Basic</th>
                            <th class="px-6 py-4">VDA</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Effective</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="w in minWages" :key="w.id" class="text-sm font-medium text-gray-700">
                            <td class="px-6 py-4"><span class="px-2 py-0.5 bg-gray-100 rounded-lg text-sm font-black uppercase tracking-tighter">{{ w.state_code }}</span></td>
                            <td class="px-6 py-4">{{ w.skill_level }}</td>
                            <td class="px-6 py-4">₹{{ formatNumber(w.basic_wage) }}</td>
                            <td class="px-6 py-4">₹{{ formatNumber(w.vda) }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900">₹{{ formatNumber(Number(w.basic_wage) + Number(w.vda)) }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-400">{{ formatDate(w.effective_from) }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="deleteWage(w.id)" class="text-gray-300 hover:text-rose-500"><TrashIcon class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <!-- 1. Branch Modal -->
    <div v-if="showBranchModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">{{ branchForm.id ? 'Edit Branch' : 'Register New Branch' }}</h3>
                <button @click="showBranchModal = false" class="text-gray-400 hover:text-gray-600"><XMarkIcon class="w-5 h-5"/></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Branch Name</label>
                    <input v-model="branchForm.name" type="text" placeholder="e.g. Bangalore HQ" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Code</label>
                        <input v-model="branchForm.code" type="text" placeholder="BLR-HQ" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">State (ISO)</label>
                        <input v-model="branchForm.state_code" type="text" placeholder="KA" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-black text-gray-400 uppercase tracking-widest">City</label>
                    <input v-model="branchForm.city" type="text" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-2xl">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="branchForm.pt_enabled" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-gray-600">PT Enabled</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="branchForm.lwf_enabled" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-gray-600">LWF Enabled</span>
                    </label>
                </div>
            </div>
            <div class="p-6 bg-gray-50 flex gap-3">
                <button @click="showBranchModal = false" class="flex-1 py-3 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                <button @click="saveBranch" class="flex-1 py-3 bg-gray-900 text-white rounded-xl text-sm font-bold shadow-lg shadow-gray-200">
                    {{ branchForm.id ? 'Update Branch' : 'Create Branch' }}
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Licence Modal -->
    <div v-if="showLicenceModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Upload Digital Licence</h3>
                <button @click="showLicenceModal = false" class="text-gray-400 hover:text-gray-600"><XMarkIcon class="w-5 h-5"/></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Document Name</label>
                    <input v-model="licenceForm.name" type="text" placeholder="e.g. Shops & Est Act Renewal" class="w-full rounded-xl border-gray-200 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">State</label>
                        <input v-model="licenceForm.state_code" type="text" placeholder="KA" class="w-full rounded-xl border-gray-200 text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Expiry Date</label>
                        <input v-model="licenceForm.expiry_date" type="date" class="w-full rounded-xl border-gray-200 text-sm">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Document File (PDF/Image)</label>
                    <input type="file" @change="handleFileUpload" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                </div>
            </div>
            <div class="p-6 bg-gray-50 flex gap-3">
                <button @click="showLicenceModal = false" class="flex-1 py-3 text-sm font-bold text-gray-500">Cancel</button>
                <button @click="uploadLicence" :disabled="uploading" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-100">
                    {{ uploading ? 'Uploading...' : 'Store Document' }}
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Wage Benchmark Modal -->
    <div v-if="showWageModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">New Legal Benchmark</h3>
                <button @click="showWageModal = false" class="text-gray-400 hover:text-gray-600"><XMarkIcon class="w-5 h-5"/></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">State</label>
                        <input v-model="wageForm.state_code" type="text" placeholder="KA" class="w-full rounded-xl border-gray-200 text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Skill Level</label>
                        <select v-model="wageForm.skill_level" class="w-full rounded-xl border-gray-200 text-sm">
                            <option>Unskilled</option>
                            <option>Semi-Skilled</option>
                            <option>Skilled</option>
                            <option>Highly Skilled</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Basic Wage</label>
                        <input v-model="wageForm.basic_wage" type="number" class="w-full rounded-xl border-gray-200 text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-black text-gray-400 uppercase tracking-widest">VDA</label>
                        <input v-model="wageForm.vda" type="number" class="w-full rounded-xl border-gray-200 text-sm">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-black text-gray-400 uppercase tracking-widest">Effective From</label>
                    <input v-model="wageForm.effective_from" type="date" class="w-full rounded-xl border-gray-200 text-sm">
                </div>
            </div>
            <div class="p-6 bg-gray-50 flex gap-3">
                <button @click="showWageModal = false" class="flex-1 py-3 text-sm font-bold text-gray-500">Cancel</button>
                <button @click="saveWage" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100">
                    Save Benchmark
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  PencilSquareIcon, 
  UsersIcon, 
  BanknotesIcon, 
  WalletIcon, 
  LifebuoyIcon,
  ShieldCheckIcon,
  NoSymbolIcon,
  InformationCircleIcon,
  CloudArrowUpIcon,
  DocumentIcon,
  CurrencyRupeeIcon,
  CheckBadgeIcon,
  ShieldExclamationIcon,
  FingerPrintIcon,
  TrashIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';

const activeModule = ref('branches');
const loadingBranches = ref(false);
const branches = ref([]);

const props = defineProps(['activeSub']);

const modules = [
  { id: 'branches', name: 'Multi-State Support' },
  { id: 'rules', name: 'State Rules' },
  { id: 'licences', name: 'Licences' },
  { id: 'sandbox', name: 'Wage Variations' },
];

const fetchBranches = async () => {
    loadingBranches.value = true;
    try {
        const res = await axios.get(route('hr.compliance.modules.branches'));
        branches.value = res.data;
    } finally {
        loadingBranches.value = false;
    }
};

const page = usePage();

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const sub = params.get('sub');
    if (sub && ['branches', 'rules', 'licences', 'sandbox'].includes(sub)) {
        activeModule.value = sub;
    }
    
    fetchBranches();
    fetchRules();
    fetchLicences();
});

watch(() => page.url, (newUrl) => {
    const url = new URL(newUrl, window.location.origin);
    const sub = url.searchParams.get('sub');
    if (sub && ['branches', 'rules', 'licences', 'sandbox'].includes(sub)) {
        activeModule.value = sub;
    }
});

// Module 2: Rules logic
const selectedState = ref('KA');
const activeRuleCat = ref('pt');
const rulesData = ref({ pt: { slabs: [] }, lwf: { employee_share: 0, employer_share: 0, frequency: 'monthly' }, leave: { pl_days: 0, sl_cl_days: 0 } });
const loadingRules = ref(false);
const savingRules = ref(false);

const ruleCategories = [
  { id: 'pt', name: 'Professional Tax', icon: BanknotesIcon },
  { id: 'lwf', name: 'Labor Welfare Fund', icon: WalletIcon },
  { id: 'leave', name: 'Leave Mandates', icon: LifebuoyIcon },
];

const fetchRules = async () => {
    loadingRules.value = true;
    try {
        const res = await axios.get(route('hr.compliance.modules.rules', selectedState.value));
        rulesData.value = {
            pt: res.data.professional_tax?.rules || { slabs: [] },
            lwf: res.data.lwf?.rules || { employee_share: 0, employer_share: 0, frequency: 'annually' },
            leave: res.data.leave_mandate?.rules || {}
        };
    } finally {
        loadingRules.value = false;
    }
};

const saveRules = async () => {
    savingRules.value = true;
    try {
        let payload = {
            state_code: selectedState.value,
            component: activeRuleCat.value,
            rules: rulesData.value[activeRuleCat.value]
        };
        await axios.post(route('hr.compliance.modules.rules.save'), payload);
    } catch (e) {
        alert("Failed to save rules.");
    } finally {
        savingRules.value = false;
    }
};

watch(selectedState, fetchRules);
watch(activeRuleCat, () => {
    if (!rulesData.value[activeRuleCat.value]) fetchRules();
});

const addSlab = () => {
    rulesData.value.pt.slabs.push({ min: 0, max: 0, amount: 0 });
};

const removeSlab = (index) => {
    rulesData.value.pt.slabs.splice(index, 1);
};

// Module 3: Licences
const licences = ref([]);
const fetchLicences = async () => {
    const res = await axios.get(route('hr.compliance.modules.licences'));
    licences.value = res.data;
};

const isExpiringSoon = (date) => {
    const expiry = new Date(date);
    const today = new Date();
    const diff = (expiry - today) / (1000 * 60 * 60 * 24);
    return diff <= 30 && diff > 0;
};

const getDaysRemaining = (date) => {
    const expiry = new Date(date);
    const today = new Date();
    return Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
};

const formatDate = (date) => new Date(date).toLocaleDateString();

// New CRUD Logic
const showBranchModal = ref(false);
const branchForm = ref({ name: '', code: '', city: '', state_code: '', pt_enabled: false, lwf_enabled: false, is_hq: false });

const openBranchModal = (branch = null) => {
    if (branch) {
        branchForm.value = { ...branch };
    } else {
        branchForm.value = { name: '', code: '', city: '', state_code: '', pt_enabled: false, lwf_enabled: false, is_hq: false };
    }
    showBranchModal.value = true;
};

const saveBranch = async () => {
    try {
        if (branchForm.value.id) {
            await axios.put(route('hr.compliance.modules.branches.update', branchForm.value.id), branchForm.value);
        } else {
            await axios.post(route('hr.compliance.modules.branches.store'), branchForm.value);
        }
        showBranchModal.value = false;
        fetchBranches();
        alert("Branch configuration updated.");
    } catch (e) {
        alert("Error saving branch.");
    }
};

const deleteBranch = async (id) => {
    if (!confirm("Are you sure? This branch might have active employees.")) return;
    try {
        await axios.delete(route('hr.compliance.modules.branches.destroy', id));
        fetchBranches();
    } catch (e) {
        alert(e.response?.data?.message || "Error deleting branch.");
    }
};

const showLicenceModal = ref(false);
const uploading = ref(false);
const licenceForm = ref({ name: '', state_code: '', expiry_date: '', file: null });

const openLicenceModal = () => {
    licenceForm.value = { name: '', state_code: '', expiry_date: '', file: null };
    showLicenceModal.value = true;
};

const handleFileUpload = (e) => {
    licenceForm.value.file = e.target.files[0];
};

const uploadLicence = async () => {
    uploading.value = true;
    const formData = new FormData();
    formData.append('name', licenceForm.value.name);
    formData.append('state_code', licenceForm.value.state_code);
    formData.append('expiry_date', licenceForm.value.expiry_date);
    if (licenceForm.value.file) formData.append('file', licenceForm.value.file);

    try {
        await axios.post(route('hr.compliance.modules.licences.upload'), formData);
        showLicenceModal.value = false;
        fetchLicences();
    } finally {
        uploading.value = false;
    }
};

const deleteLicence = async (id) => {
    if (!confirm("Delete this licence permanently?")) return;
    await axios.delete(route('hr.compliance.modules.licences.destroy', id));
    fetchLicences();
};

// Module 4: Sandbox
const sandbox = ref({ state: 'KA', skill: 'Highly Skilled', proposed: 15000 });
const validationResult = ref(null);
const valLoading = ref(false);
const sandboxMode = ref('calc'); 

const fetchMinWages = async () => {
    const res = await axios.get(route('hr.compliance.modules.salary.minimum-wages'));
    minWages.value = res.data;
};

const minWages = ref([]);
const wageForm = ref({ state_code: '', skill_level: 'Skilled', basic_wage: 0, vda: 0, effective_from: '', zone: 'Zone 1' });
const showWageModal = ref(false);

const openWageModal = () => {
    wageForm.value = { state_code: 'KA', skill_level: 'Skilled', basic_wage: 0, vda: 0, effective_from: '', zone: 'Zone 1' };
    showWageModal.value = true;
};

const saveWage = async () => {
    try {
        await axios.post(route('hr.compliance.modules.salary.minimum-wages.save'), wageForm.value);
        showWageModal.value = false;
        fetchMinWages();
    } catch (e) {
        alert("Verification failed.");
    }
};

const deleteWage = async (id) => {
    if (!confirm("Remove this benchmark?")) return;
    await axios.delete(route('hr.compliance.modules.salary.minimum-wages.destroy', id));
    fetchMinWages();
};

const validateSandbox = async () => {
    valLoading.value = true;
    try {
        const res = await axios.post(route('hr.compliance.modules.salary.validate'), {
            state_code: sandbox.value.state,
            skill_level: sandbox.value.skill,
            proposed_basic: sandbox.value.proposed
        });
        validationResult.value = { valid: true, message: 'Structure is legally compliant.' };
    } catch (e) {
        validationResult.value = e.response.data;
    } finally {
        valLoading.value = false;
    }
};

const formatNumber = (num) => new Intl.NumberFormat('en-IN').format(num);

onMounted(() => {
    fetchBranches();
    fetchRules();
    fetchLicences();
    fetchMinWages();
});
</script>
