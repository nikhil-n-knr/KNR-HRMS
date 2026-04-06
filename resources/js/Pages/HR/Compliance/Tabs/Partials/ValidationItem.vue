<template>
  <div class="border rounded-lg overflow-hidden transition-all duration-300" :class="statusColor">
      <div 
        @click="toggle"
        class="p-4 flex justify-between items-center cursor-pointer hover:opacity-90 transition"
        :class="bgHeader"
      >
          <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full" :class="dotColor"></div>
              <h4 class="font-medium text-sm text-gray-800">{{ title }}</h4>
          </div>
          <div class="flex items-center gap-2">
              <span v-if="count > 0" class="text-xs font-bold px-2 py-0.5 rounded-full bg-white bg-opacity-50 text-gray-800">
                  {{ count }} Issues
              </span>
              <span v-else class="text-xs font-bold text-gray-500">
                   OK
              </span>
              <svg 
                class="w-4 h-4 text-gray-500 transform transition-transform" 
                :class="isOpen ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
              >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
          </div>
      </div>
      
      <!-- List -->
      <div v-if="isOpen && count > 0" class="bg-white border-t p-2 max-h-40 overflow-y-auto">
          <div v-for="item in items" :key="item.id" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded text-xs">
              <span class="font-medium text-gray-700">{{ item.name }}</span>
               <a :href="route('employees.edit', item.id)" class="text-indigo-600 hover:underline">Fix</a>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    title: String,
    count: Number,
    type: String, // critical, warning
    items: Array
});

const isOpen = ref(false);

const toggle = () => {
    if (props.count > 0) isOpen.value = !isOpen.value;
};

const statusColor = computed(() => {
    if (props.count === 0) return 'border-green-200 bg-green-50';
    return props.type === 'critical' ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50';
});

const bgHeader = computed(() => {
    if (props.count === 0) return 'bg-green-100';
    return props.type === 'critical' ? 'bg-red-100' : 'bg-amber-100';
});

const dotColor = computed(() => {
     if (props.count === 0) return 'bg-green-500';
     return props.type === 'critical' ? 'bg-red-500' : 'bg-amber-500';
});
</script>
