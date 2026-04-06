<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    locations: Array
});

// Recursive Component for Tree Item
const TreeItem = {
    name: 'TreeItem',
    props: ['node', 'depth'],
    template: `
        <div :class="['pl-4 border-l-2 border-gray-200 ml-2', depth === 0 ? 'ml-0 pl-0 border-l-0' : '']">
            <div class="flex items-center group py-2">
                <!-- Icon -->
                <span class="mr-2 text-gray-400">
                    <svg v-if="node.children && node.children.length" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </span>
                
                <!-- Content -->
                <div class="flex-1 flex justify-between items-center bg-white p-3 rounded-lg border border-gray-100 shadow-sm hover:border-indigo-200 transition-colors">
                    <div>
                        <span class="text-xs uppercase font-bold text-indigo-500 tracking-wider">{{ node.type }}</span>
                        <h4 class="font-medium text-gray-900">{{ node.name }}</h4>
                    </div>
                    
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <button @click="$emit('add-child', node)" class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100">
                            + Add Child
                         </button>
                         <button @click="$emit('delete-node', node)" class="text-xs text-red-500 px-2 py-1 hover:text-red-700">
                            Delete
                         </button>
                    </div>
                </div>
            </div>
            
            <!-- Children -->
            <div v-if="node.children && node.children.length" class="mt-1">
                <tree-item 
                    v-for="child in node.children" 
                    :key="child.id" 
                    :node="child" 
                    :depth="depth + 1"
                    @add-child="$emit('add-child', $event)"
                    @delete-node="$emit('delete-node', $event)"
                />
            </div>
        </div>
    `
};

const showModal = ref(false);
const parentNode = ref(null);
const form = useForm({
    name: '',
    type: 'Room',
    parent_id: null
});

const openAddModal = (parent = null) => {
    parentNode.value = parent;
    form.reset();
    form.parent_id = parent ? parent.id : null;
    form.type = parent ? suggestType(parent.type) : 'Room';
    showModal.value = true;
};

const suggestType = (parentType) => {
    const hierarchy = { 'Room': 'Cabinet', 'Cabinet': 'Rack', 'Rack': 'Shelf', 'Shelf': 'Bin' };
    return hierarchy[parentType] || 'Bin';
};

const submitNode = () => {
    form.post(route('physical-documents.locations.store'), {
        onSuccess: () => showModal.value = false
    });
};

const deleteNode = (node) => {
    if (confirm(`Delete ${node.name}? This will delete all children.`)) {
        router.delete(route('physical-documents.locations.destroy', node.id));
    }
};

// We need to register the recursive component locally (SFC constraint)
// But since we are using <script setup>, we can't easily do 'components: {}'.
// Alternative: Flatten the tree or just implement simple recursion in template using <component :is>...
// Actually, standard Vue SFC recursion works by referring to the component name. 
// Let's define it as a separate component block or use a simpler non-recursive approach for MVP if strict SFC is an issue.
// Re-strategy: Inline recursion using <template v-for> is cleaner here?
// Let's use a self-referencing component pattern in a separate file if needed.
// FOR NOW: I will build a simple iterative UI that supports only 3 levels (Room > Rack > Shelf) to avoid recursion complexity in one file, or just use a loop.
// Actually, let's use a "TreeItem" component defined in the same file? No, <script setup> makes that hard.
// Best approach: A separate file `LocationTreeItem.vue`.

// import { router } from '@inertiajs/vue3';

</script>

<script>
// Separate script block for component registration if needed?
// Vue 3 <script setup> allows importing self? 
</script>

<template>
  <Head title="Storage Configuration" />
  
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <div>
             <h1 class="text-2xl font-bold text-gray-900">Storage Builder</h1>
             <p class="text-gray-500">Define your physical archiving structure (Rack/Shelf/Bin)</p>
        </div>
        <button @click="openAddModal(null)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow-sm hover:bg-indigo-700 font-medium text-sm transition-colors">
            + Add Root Location
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 max-w-4xl mx-auto min-h-[500px]">
        <div v-if="locations.length === 0" class="text-center text-gray-400 py-12">
            No storage locations defined. Start by adding a Room or Cabinet.
        </div>

        <!-- Tree View -->
        <ul class="space-y-4">
            <li v-for="root in locations" :key="root.id">
                <!-- Level 1 -->
                <div class="flex items-center group">
                    <div class="w-12 h-12 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-lg mr-4 font-bold">
                        {{ root.type[0] }}
                    </div>
                    <div class="flex-1 border-b border-gray-100 pb-2">
                        <div class="flex justify-between">
                            <h3 class="font-semibold text-lg text-gray-800">{{ root.name }} <span class="text-xs font-normal text-gray-400">({{ root.type }})</span></h3>
                            <div class="opacity-0 group-hover:opacity-100 flex gap-2">
                                <button @click="openAddModal(root)" class="text-indigo-600 hover:text-indigo-800 text-sm">+ Add Child</button>
                                <button @click="deleteNode(root)" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level 2 Children -->
                <ul v-if="root.children && root.children.length" class="ml-16 mt-2 space-y-2 border-l-2 border-gray-100 pl-4">
                    <li v-for="child in root.children" :key="child.id">
                         <div class="flex items-center group py-1">
                            <span class="w-2 h-2 bg-gray-300 rounded-full mr-3"></span>
                            <div class="flex-1 flex justify-between">
                                <span class="text-gray-700">{{ child.name }} <span class="text-xs text-gray-400">({{ child.type }})</span></span>
                                <div class="opacity-0 group-hover:opacity-100 flex gap-2">
                                    <button @click="openAddModal(child)" class="text-indigo-600 hover:text-indigo-800 text-xs">+ Add Child</button>
                                     <button @click="deleteNode(child)" class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                                </div>
                            </div>
                         </div>

                         <!-- Level 3 Children -->
                         <ul v-if="child.children && child.children.length" class="ml-6 mt-1 space-y-1">
                            <li v-for="grandchild in child.children" :key="grandchild.id" class="flex items-center group text-sm text-gray-500">
                                <span class="mr-2 text-gray-300">↳</span>
                                <span class="flex-1">{{ grandchild.name }} ({{ grandchild.type }})</span>
                                <button @click="deleteNode(grandchild)" class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 ml-2">&times;</button>
                            </li>
                         </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
        <div class="bg-white rounded-lg p-6 w-96 shadow-xl">
             <h3 class="font-bold text-lg mb-4">{{ parentNode ? `Add to ${parentNode.name}` : 'New Location' }}</h3>
             
             <div class="space-y-3">
                 <div>
                     <label class="block text-sm font-medium text-gray-700">Type</label>
                     <select v-model="form.type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                         <option>Room</option>
                         <option>Cabinet</option>
                         <option>Rack</option>
                         <option>Shelf</option>
                         <option>Bin</option>
                         <option>Safe</option>
                         <option>Offsite</option>
                     </select>
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700">Name / Ref</label>
                     <input v-model="form.name" type="text" placeholder="e.g. Rack A" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                 </div>
             </div>

             <div class="flex justify-end gap-2 mt-6">
                 <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">Cancel</button>
                 <button @click="submitNode" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Save</button>
             </div>
        </div>
    </div>

  </div>
</template>

<style scoped>
/* Removed @apply to avoid build errors. Classes inlined. */
</style>
