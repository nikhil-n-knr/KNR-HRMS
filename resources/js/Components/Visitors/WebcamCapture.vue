<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { CameraIcon, ArrowPathIcon, CheckIcon, XMarkIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const emit = defineEmits(['captured', 'close']);

const video = ref(null);
const canvas = ref(null);
const isStreaming = ref(false);
const stream = ref(null);
const capturedImage = ref(null);
const fileInput = ref(null);

const startCamera = async () => {
    try {
        stream.value = await navigator.mediaDevices.getUserMedia({ 
            video: { width: 1280, height: 720, facingMode: 'user' }, 
            audio: false 
        });
        video.value.srcObject = stream.value;
        video.value.play();
        isStreaming.value = true;
    } catch (err) {
        console.error("Error accessing camera: ", err);
        alert("Could not access webcam. Please ensure permissions are granted.");
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
    }
    isStreaming.value = false;
};

const capturePhoto = () => {
    const context = canvas.value.getContext('2d');
    canvas.value.width = video.value.videoWidth;
    canvas.value.height = video.value.videoHeight;
    context.drawImage(video.value, 0, 0, canvas.value.width, canvas.value.height);
    
    capturedImage.value = canvas.value.toDataURL('image/png');
};

const approve = () => {
    emit('captured', capturedImage.value);
    stopCamera();
};

const retake = () => {
    capturedImage.value = null;
    if (!isStreaming.value) startCamera();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            capturedImage.value = e.target.result;
            stopCamera();
        };
        reader.readAsDataURL(file);
    }
};

onMounted(() => {
    startCamera();
});

onUnmounted(() => {
    stopCamera();
});

</script>

<template>
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
        <div class="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-2xl w-full flex flex-col">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-black text-gray-800 flex items-center gap-2">
                    <CameraIcon class="w-5 h-5 text-indigo-500" />
                    Security Photo Capture
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600"><XMarkIcon class="w-6 h-6" /></button>
            </div>

            <div class="relative bg-black flex-1 min-h-[400px] flex items-center justify-center overflow-hidden">
                <!-- Video Stream -->
                <video v-show="!capturedImage" ref="video" class="w-full h-full object-cover scale-x-[-1]"></video>
                
                <!-- Captured Image -->
                <img v-if="capturedImage" :src="capturedImage" class="w-full h-full object-cover scale-x-[-1]">

                <!-- Face Overlay (Instructional) -->
                <div v-if="!capturedImage" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-64 h-80 border-2 border-dashed border-white/50 rounded-[100px] flex flex-col items-center justify-end pb-10">
                         <span class="text-white/70 text-[10px] uppercase font-bold tracking-widest bg-black/20 px-3 py-1 rounded-full backdrop-blur-sm">Align Face Here</span>
                    </div>
                </div>

                <!-- Hidden Canvas -->
                <canvas ref="canvas" class="hidden"></canvas>
            </div>

            <div class="p-6 bg-gray-50 flex justify-center gap-4">
                <template v-if="!capturedImage">
                    <button @click="capturePhoto" class="w-16 h-16 bg-white border-4 border-indigo-600 rounded-full flex items-center justify-center shadow-xl transform active:scale-95 transition-transform">
                         <div class="w-10 h-10 bg-indigo-600 rounded-full"></div>
                    </button>
                </template>
                <template v-else>
                    <button @click="retake" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold flex items-center gap-2 hover:bg-gray-100">
                        <ArrowPathIcon class="w-5 h-5" /> Retake
                    </button>
                    <button @click="approve" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold flex items-center gap-2 hover:bg-indigo-700 shadow-lg shadow-indigo-100">
                        <CheckIcon class="w-5 h-5" /> Approve & Use
                    </button>
                </template>
            </div>
            
            <!-- Fallback: Manual Upload -->
            <div class="p-4 border-t border-gray-100 bg-white flex flex-col items-center">
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload">
                <button @click="fileInput.click()" class="text-xs font-bold text-gray-400 hover:text-indigo-600 flex items-center gap-2 transition-colors">
                    <PhotoIcon class="w-4 h-4" />
                    Or upload a photo manually from device
                </button>
            </div>
        </div>
    </div>
</template>
