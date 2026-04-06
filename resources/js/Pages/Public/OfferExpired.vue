<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Offer Unavailable</h2>
                <p class="text-gray-600">{{ message }}</p>
                <div class="mt-6">
                    <p class="text-xs text-gray-400">Please contact the HR department for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    message: String,
    type: String
});
</script>
