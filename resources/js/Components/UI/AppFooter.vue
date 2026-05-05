<script setup>
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="w-full pt-10 pb-6">
    <div class="text-center text-xs sm:text-sm text-slate-400 font-semibold">
      © {{ currentYear }} KNR. All rights reserved.
    </div>
  </footer>
</template>