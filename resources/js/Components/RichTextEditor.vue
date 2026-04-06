<template>
    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500">
        <!-- Toolbar -->
        <div class="flex items-center space-x-1 p-2 border-b border-gray-200 bg-gray-50">
            <button type="button" @click="exec('bold')" class="p-1.5 rounded hover:bg-gray-200 text-gray-600" title="Bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6V4zm0 8h9a5 5 0 015 5 5 5 0 01-5 5H6v-10z"></path></svg>
            </button>
            <button type="button" @click="exec('italic')" class="p-1.5 rounded hover:bg-gray-200 text-gray-600" title="Italic">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg> 
                <!-- Using generic code icon for italic placeholder or simple path -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m-6 4h8m-8 12h8"></path>
            </button>
            <div class="w-px h-4 bg-gray-300 mx-2"></div>
            <button type="button" @click="exec('insertUnorderedList')" class="p-1.5 rounded hover:bg-gray-200 text-gray-600" title="Bullet List">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <!-- Editor -->
        <div 
            ref="editor"
            contenteditable="true"
            class="p-3 min-h-[150px] outline-none text-sm text-gray-800"
            @input="onInput"
        ></div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    modelValue: String
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);

const exec = (command) => {
    document.execCommand(command, false, null);
    editor.value.focus();
};

const onInput = () => {
    emit('update:modelValue', editor.value.innerHTML);
};

// Sync content from prop
onMounted(() => {
    if (props.modelValue) {
        editor.value.innerHTML = props.modelValue;
    }
});

watch(() => props.modelValue, (newVal) => {
    if (editor.value && newVal !== editor.value.innerHTML) {
        editor.value.innerHTML = newVal || '';
    }
});
</script>

<style scoped>
/* Basic list styling within editor */
:deep(ul) {
    list-style-type: disc;
    padding-left: 1.5em;
}
:deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5em;
}
</style>
