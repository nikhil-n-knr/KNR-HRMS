<template>
    <div class="h-full flex flex-col bg-gray-50">
        <!-- Header Actions -->
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm z-10">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Theme Engine</h2>
                <p class="text-xs text-gray-500 mt-0.5">Control global branding, automated tokens, and scaling typography.</p>
            </div>
            
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-bold shadow-md hover:bg-black transition-all flex items-center gap-2">
                <i class="fas fa-save text-emerald-500"></i> Compile Theme
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="max-w-6xl mx-auto flex gap-8">
                
                <!-- Theme Control Sidebar -->
                <div class="w-80 shrink-0 space-y-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <h3 class="text-sm font-black tracking-widest uppercase text-gray-400 mb-4 border-b border-gray-100 pb-2">Global Branding</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Primary Color</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500 shadow-inner border border-emerald-600"></div>
                                    <input type="text" value="#10B981" class="w-full bg-gray-50 border border-gray-200 rounded py-1 px-2 text-xs font-mono uppercase text-gray-600 focus:ring-1 focus:ring-emerald-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Secondary Action Background</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600 shadow-inner border border-indigo-700"></div>
                                    <input type="text" value="#4F46E5" class="w-full bg-gray-50 border border-gray-200 rounded py-1 px-2 text-xs font-mono uppercase text-gray-600 focus:ring-1 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <h3 class="text-sm font-black tracking-widest uppercase text-gray-400 mb-4 border-b border-gray-100 pb-2">Typography Setup</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Heading Font (<span class="text-sm text-emerald-500">Google Fonts API</span>)</label>
                                <select class="w-full bg-gray-50 border border-gray-200 rounded py-1 text-xs cursor-pointer focus:ring-1 focus:ring-emerald-500 font-bold text-gray-600">
                                    <option>Outfit (Sans-Serif)</option>
                                    <option>Inter</option>
                                    <option>Playfair Display</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Body Font</label>
                                <select class="w-full bg-gray-50 border border-gray-200 rounded py-1 text-xs cursor-pointer focus:ring-1 focus:ring-emerald-500 font-bold text-gray-600">
                                    <option>Inter</option>
                                    <option>Roboto</option>
                                    <option>Open Sans</option>
                                </select>
                            </div>
                            
                            <div class="pt-2 border-t border-gray-100">
                                <label class="text-xs font-bold text-gray-700 block mb-2 flex items-center justify-between">
                                    Base Scale Ratio 
                                    <span class="text-sm text-gray-400 bg-gray-100 px-1 py-0.5 rounded">1.250 (Major Third)</span>
                                </label>
                                <input type="range" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Live Preview Pane -->
                <div class="flex-1 bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden flex flex-col relative">
                    <div class="h-8 bg-gray-100 border-b border-gray-200 flex items-center px-4 gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                        <span class="text-sm font-bold text-gray-400 uppercase tracking-widest mx-auto flex items-center gap-2"><i class="fas fa-lock text-xs"></i> Live CSS Rendering Preview</span>
                    </div>
                    
                    <div class="flex-1 p-10 overflow-y-auto">
                        <div class="mb-10 text-center">
                            <span class="text-emerald-500 font-bold tracking-widest uppercase text-sm mb-2 block">Typography Preview scale</span>
                            <h1 class="text-6xl font-black text-gray-900 tracking-tight leading-none mb-4" style="font-family: 'Outfit', sans-serif;">Build the Future.</h1>
                            <h2 class="text-3xl font-bold text-gray-800 mb-4" style="font-family: 'Outfit', sans-serif;">The 100K Page Builder is Here</h2>
                            <p class="text-lg text-gray-600 max-w-2xl mx-auto" style="font-family: 'Inter', sans-serif;">
                                Typography scaling adjusts all font sizes simultaneously based on your base ratio setting. This ensures the design system never breaks.
                            </p>
                        </div>
                        
                        <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100">
                            <h3 class="text-sm font-black tracking-widest uppercase text-gray-400 mb-6">Component Examples</h3>
                            
                            <div class="flex gap-4 mb-6">
                                <button class="px-6 py-3 bg-emerald-500 text-white rounded-lg font-bold shadow-lg shadow-emerald-500/20">Primary Button</button>
                                <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-bold shadow-lg shadow-indigo-500/20">Secondary Action</button>
                                <button class="px-6 py-3 bg-white text-gray-800 border border-gray-300 rounded-lg font-bold shadow-sm">Outline Default</button>
                            </div>
                            
                            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex gap-4 text-emerald-800">
                                <i class="fas fa-info-circle text-emerald-500 mt-0.5"></i>
                                <div>
                                    <div class="font-bold mb-1">Active Alert Configuration</div>
                                    <div class="text-sm opacity-80">This alert box uses semantic scaling derived from the primary brand color.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    active_theme: {
        type: Object,
        default: null
    },
    themes: {
        type: Array,
        default: () => []
    }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #94a3b8;
}
</style>
