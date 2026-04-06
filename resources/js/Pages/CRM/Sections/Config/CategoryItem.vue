<template>
    <div class="select-none text-left">
        <div 
            @click.stop="toggle"
            class="flex items-center px-4 py-2.5 rounded-xl cursor-pointer transition-all group relative overflow-hidden"
            :class="isActive ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'hover:bg-gray-50 text-gray-600'"
        >
            <button 
                v-if="hasChildren" 
                @click.stop="isOpen = !isOpen"
                class="w-6 h-6 flex items-center justify-center mr-2 text-gray-400 hover:text-indigo-600 transition-all rounded-lg hover:bg-white shadow-sm"
            >
                <i class="fas text-sm" :class="isOpen ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <span v-else class="w-6 mr-2"></span>
            
            <i :class="[category.icon || (hasChildren ? 'fa-folder' : 'fa-tag'), 'fas mr-3 text-xs opacity-40 group-hover:opacity-100 transition-opacity']"></i>
            <span class="text-xs font-black uppercase tracking-widest flex-1 truncate">{{ category.name }}</span>
            
            <div v-if="isActive" class="w-2 h-2 rounded-full bg-indigo-500 ml-2 shadow-lg shadow-indigo-500/50 animate-pulse"></div>
        </div>

        <div v-if="isOpen && hasChildren" class="pl-6 border-l-2 border-indigo-50/50 ml-4 mt-2 space-y-1">
            <CategoryItem 
                v-for="child in category.children" 
                :key="child.id" 
                :category="child" 
                :active-id="activeId"
                @select="$emit('select', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    category: Object,
    activeId: [Number, String, null],
});

const emit = defineEmits(['select']);

const isOpen = ref(true); // Default to open for visibility

const hasChildren = computed(() => props.category.children && props.category.children.length > 0);

const isActive = computed(() => props.activeId === props.category.id);

const toggle = () => {
    emit('select', props.category.id);
    if (hasChildren.value) {
        isOpen.value = true;
    }
};
</script>
