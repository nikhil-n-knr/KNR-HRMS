<template>
    <div class="space-y-8">
        <!-- Header / Advanced Alert -->
        <div class="relative overflow-hidden bg-white border border-gray-100 p-8 rounded-3xl shadow-sm">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-amber-50 rounded-full blur-3xl opacity-50"></div>
            
            <div class="relative flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mr-6 shadow-sm border border-amber-200">
                        <i class="fas fa-clone text-2xl text-amber-600"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 leading-tight">Duplicate Detection</h2>
                        <div class="flex items-center mt-1">
                            <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse mr-2"></span>
                            <p class="text-sm font-medium text-amber-700">
                                <span class="font-black">{{ groups.length }} groups</span> of potential duplicates found
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button @click="runScan" 
                            :disabled="isScanning"
                            class="px-6 py-3 bg-gray-900 text-white rounded-2xl hover:bg-black transition-all font-bold shadow-xl shadow-gray-100 flex items-center disabled:opacity-50">
                        <i :class="['fas', isScanning ? 'fa-spinner fa-spin' : 'fa-search', 'mr-2']"></i>
                        {{ isScanning ? 'SCANNING...' : 'RUN NEW SCAN' }}
                    </button>
                    <button @click="ignoreAll" class="px-6 py-3 bg-white text-gray-400 border border-gray-100 rounded-2xl hover:text-gray-600 hover:bg-gray-50 transition-all font-bold text-sm">
                        IGNORE ALL
                    </button>
                </div>
            </div>
        </div>

        <!-- Duplicates List -->
        <div class="space-y-6">
            <transition-group name="list">
                <div v-for="(group, gIdx) in groups" :key="group.id" 
                     class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden group hover:shadow-xl hover:shadow-gray-50 transition-all">
                    
                    <div class="bg-gray-50/50 px-8 py-4 border-b border-gray-50 flex justify-between items-center">
                        <div class="flex items-center text-xs font-black text-gray-400 uppercase tracking-widest">
                            <i class="fas fa-fingerprint mr-2 text-blue-500"></i>
                            Match Criteria: <span class="text-gray-900 ml-2">{{ group.criteria }}</span>
                        </div>
                        <span class="bg-white px-3 py-1 rounded-full text-sm font-bold text-gray-400 border border-gray-100 shadow-sm">
                            GROUP #{{ group.id }}
                        </span>
                    </div>

                    <div class="p-8">
                        <div class="flex flex-col lg:flex-row gap-8">
                            <div class="flex-1 space-y-4">
                                <div v-for="(record, rIdx) in group.records" :key="record.id" 
                                     @click="setPrimary(gIdx, rIdx)"
                                     :class="[
                                        'relative p-6 rounded-2xl border-2 transition-all cursor-pointer group/record',
                                        record.is_primary ? 'border-blue-500 bg-blue-50/30' : 'border-gray-50 bg-white hover:border-gray-200'
                                     ]">
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div :class="['w-6 h-6 rounded-full border-2 flex items-center justify-center mr-4 transition-colors', record.is_primary ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-200 bg-white']">
                                                <i v-if="record.is_primary" class="fas fa-check text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-900 group-hover/record:text-blue-600 transition-colors">{{ record.name }}</h4>
                                                <div class="flex items-center gap-4 mt-1">
                                                    <span class="text-xs text-gray-500 flex items-center">
                                                        <i class="fas fa-envelope mr-1.5 opacity-40"></i> {{ record.email }}
                                                    </span>
                                                    <span v-if="record.phone" class="text-xs text-gray-500 flex items-center">
                                                        <i class="fas fa-phone mr-1.5 opacity-40"></i> {{ record.phone }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="record.is_primary" class="flex items-center">
                                            <span class="bg-blue-600 text-white text-sm font-black px-3 py-1 rounded-lg uppercase tracking-widest shadow-lg shadow-blue-100">Primary Record</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-gray-50/50 grid grid-cols-2 gap-4">
                                        <div class="text-sm text-gray-400 font-bold uppercase tracking-tighter">
                                            Company: <span class="text-gray-700 ml-1">{{ record.company || 'N/A' }}</span>
                                        </div>
                                        <div class="text-sm text-gray-400 font-bold uppercase tracking-tighter text-right">
                                            Owner: <span class="text-gray-700 ml-1">{{ record.owner }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="lg:w-48 flex flex-col gap-3">
                                <button @click="mergeRecords(gIdx)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black text-sm hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex flex-col items-center justify-center">
                                    <i class="fas fa-compress-alt mb-2 text-xl"></i>
                                    MERGE
                                </button>
                                <button @click="ignoreGroup(gIdx)" class="w-full py-3 bg-white text-gray-500 border border-gray-100 rounded-2xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center justify-center">
                                    <i class="fas fa-times-circle mr-2"></i> IGNORE
                                </button>
                                <p class="text-sm text-gray-300 font-bold text-center mt-2 px-2 leading-relaxed uppercase tracking-widest">Selected primary record will be preserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>

        <!-- Empty State -->
        <div v-if="groups.length === 0" class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-32 text-center relative overflow-hidden">
             <!-- Celebration Circles -->
            <div class="absolute top-0 left-0 w-64 h-64 bg-emerald-50 rounded-full blur-3xl -ml-32 -mt-32 opacity-40"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-blue-50 rounded-full blur-3xl -mr-32 -mb-32 opacity-40"></div>

            <div class="relative">
                <div class="w-24 h-24 bg-emerald-100 rounded-[32px] flex items-center justify-center mx-auto mb-8 shadow-inner transform rotate-12">
                    <i class="fas fa-check-double text-4xl text-emerald-600"></i>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4 tracking-tight">Database is Pristine!</h3>
                <p class="text-lg text-gray-500 max-w-md mx-auto leading-relaxed">No duplicate records were found. Your customer records are clean and organized.</p>
                <button @click="runScan" class="mt-12 px-10 py-4 bg-gray-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-2xl shadow-gray-200">
                    REFRESH SCAN
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    duplicates: {
        type: Object,
        default: () => ({ by_email: {}, by_phone: {}, total_duplicates: 0 })
    }
});

const isScanning = ref(false);

const computedGroups = computed(() => {
    const groups = [];
    let id = 1;

    // Process email duplicates
    Object.entries(props.duplicates?.by_email || {}).forEach(([email, records]) => {
        groups.push({
            id: id++,
            criteria: `Email: ${email}`,
            records: records.map((r, idx) => ({
                ...r,
                name: `${r.first_name} ${r.last_name}`,
                owner: r.creator?.name || 'System',
                is_primary: idx === 0
            }))
        });
    });

    // Process phone duplicates
    Object.entries(props.duplicates?.by_phone || {}).forEach(([phone, records]) => {
        groups.push({
            id: id++,
            criteria: `Phone: ${phone}`,
            records: records.map((r, idx) => ({
                ...r,
                name: `${r.first_name} ${r.last_name}`,
                owner: r.creator?.name || 'System',
                is_primary: idx === 0
            }))
        });
    });

    return groups;
});

const groups = ref(computedGroups.value);

const runScan = () => {
    isScanning.value = true;
    router.reload({
        onFinish: () => isScanning.value = false
    });
};

const setPrimary = (gIdx, rIdx) => {
    groups.value[gIdx].records.forEach((r, i) => {
        r.is_primary = (i === rIdx);
    });
};

const mergeRecords = (gIdx) => {
    const primary = groups.value[gIdx].records.find(r => r.is_primary);
    if(confirm(`Are you sure you want to merge these records? ${primary.name} will be kept as primary.`)) {
        groups.value.splice(gIdx, 1);
        // Backend merge logic would go here
    }
};

const ignoreGroup = (gIdx) => {
    groups.value.splice(gIdx, 1);
};

const ignoreAll = () => {
    if(confirm('Ignore all detected duplicate groups for this session?')) {
        groups.value = [];
    }
};
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
