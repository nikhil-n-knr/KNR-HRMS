<template>
    <div class="space-y-6">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Ready to Launch?</h2>
            <p class="text-gray-500">Review your project architecture before initialization.</p>
        </div>

        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 text-sm space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs uppercase tracking-wider text-gray-400 font-semibold">Client</span>
                    <span class="font-medium text-gray-800">{{ getClientName(form.client_id) }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-gray-400 font-semibold">Project Code</span>
                    <span class="font-mono bg-gray-200 px-2 py-0.5 rounded text-gray-700">{{ form.code }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-gray-400 font-semibold">Visibility</span>
                    <span class="font-medium text-indigo-600 capitalize">{{ form.visibility.replace('_', ' ') }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-gray-400 font-semibold">Duration</span>
                    <span class="font-medium text-gray-800">
                        {{ form.dates.start || 'TBD' }} 
                        <span class="text-gray-400 mx-1">→</span> 
                        {{ form.dates.end || 'TBD' }}
                    </span>
                </div>
            </div>

            <!-- Module Preview -->
            <div class="pt-4 border-t border-gray-200">
                <span class="block text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">Module Structure</span>
                <div v-if="form.modules.length > 0" class="pl-2 border-l-2 border-gray-300 space-y-2">
                    <div v-for="(mod, i) in form.modules" :key="i">
                        <p class="font-semibold text-gray-700">{{ mod.name }}</p>
                        <ul v-if="mod.children.length" class="pl-4 mt-1 border-l border-indigo-200 space-y-1">
                            <li v-for="(child, j) in mod.children" :key="j" class="text-gray-600">
                                {{ child.name }}
                                <span v-if="child.children.length" class="text-xs text-indigo-500">
                                    (+{{ child.children.length }} sub)
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <p v-else class="text-gray-400 italic">No modules defined.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    form: Object,
    clients: Array
});

const getClientName = (id) => {
    return props.clients.find(c => c.id === id)?.name || 'Unknown Client';
};
</script>
