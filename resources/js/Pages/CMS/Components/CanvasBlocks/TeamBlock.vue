<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div v-if="block.content?.title" class="text-center mb-12">
                <h2 class="text-4xl font-black mb-4">{{ block.content.title }}</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div v-for="(member, i) in members" :key="i" class="text-center">
                    <div class="w-20 h-20 rounded-2xl mx-auto mb-4 overflow-hidden bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center text-white text-2xl font-black shadow-lg">
                        <img v-if="member.img" :src="member.img" class="w-full h-full object-cover" />
                        <span v-else>{{ member.name?.charAt(0) || '?' }}</span>
                    </div>
                    <h3 class="font-black text-gray-900 text-sm">{{ member.name || 'Team Member' }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ member.role || 'Role' }}</p>
                    <div class="flex justify-center gap-2 mt-3">
                        <a href="#" class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-indigo-100 hover:text-indigo-600 transition-all"><i class="fab fa-linkedin text-sm"></i></a>
                        <a href="#" class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-sky-100 hover:text-sky-600 transition-all"><i class="fab fa-twitter text-sm"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const members = computed(() => props.block?.content?.members?.length ? props.block.content.members : [
    {name:'Aarav Shah', role:'CEO & Founder'},{name:'Priya Nair', role:'CTO'},{name:'Rahul Dev', role:'Head of Design'},{name:'Swati Jain', role:'Product Lead'},
]);
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#fff', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>
