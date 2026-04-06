<template>
    <span :class="badgeClass">
        <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="dotClass"></span>
        <slot>{{ label }}</slot>
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: String,
    stage: String
});

const label = computed(() => {
    if (props.status === 'Pending' || props.status === 'Processing') {
        return props.stage ? `Processing (${props.stage})` : props.status;
    }
    return props.status;
});

const colorMap = {
    'Pending': { bg: 'bg-yellow-100', text: 'text-yellow-800', dot: 'bg-yellow-500' },
    'Processing': { bg: 'bg-blue-100', text: 'text-blue-800', dot: 'bg-blue-500' },
    'Approved': { bg: 'bg-green-100', text: 'text-green-800', dot: 'bg-green-500' },
    'Paid': { bg: 'bg-emerald-100', text: 'text-emerald-800', dot: 'bg-emerald-500' },
    'Rejected': { bg: 'bg-red-100', text: 'text-red-800', dot: 'bg-red-500' },
    'Draft': { bg: 'bg-gray-100', text: 'text-gray-800', dot: 'bg-gray-500' },
};

const currentStyle = computed(() => colorMap[props.status] || colorMap['Draft']);

const badgeClass = computed(() => `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${currentStyle.value.bg} ${currentStyle.value.text}`);
const dotClass = computed(() => currentStyle.value.dot);
</script>
