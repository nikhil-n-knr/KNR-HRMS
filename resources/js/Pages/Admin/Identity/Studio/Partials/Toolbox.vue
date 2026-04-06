<script setup>
import { ref, computed } from 'vue';
import { useStudioStore } from '@/Stores/studioStore';
import * as fabric from 'fabric';

const store = useStudioStore();
const searchQuery = ref('');

const isFiltered = (name) => {
    if (!searchQuery.value) return true;
    return name.toLowerCase().includes(searchQuery.value.toLowerCase());
};

// --- 1. Static Text ---
const addText = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();
    const text = new fabric.IText('Identify Matrix', {
        left: center.x, top: center.y,
        fontFamily: 'Outfit',
        fontSize: 32,
        fill: '#1e293b',
        fontWeight: '900',
        originX: 'center',
        originY: 'center',
        id: 'obj_' + Date.now()
    });
    store.addObject(text);
};

// --- 2. Static Shapes ---
const addShape = (type) => {
    if (!store.canvas) return;
    let obj;
    const center = store.canvas.getCenterPoint();
    const common = { 
        left: center.x, top: center.y, 
        fill: '#f1f5f9', 
        stroke: '#cbd5e1', 
        strokeWidth: 2,
        originX: 'center', originY: 'center', 
        id: 'obj_' + Date.now() 
    }; 
    
    if (type === 'rect') {
        obj = new fabric.Rect({ ...common, width: 200, height: 100, rx: 12, ry: 12 });
    } else if (type === 'circle') {
        obj = new fabric.Circle({ ...common, radius: 50 });
    } else if (type === 'path') {
        obj = new fabric.Path('M 0 0 Q 50 50 100 0', { 
            ...common, 
            fill: '', 
            stroke: '#10b981', 
            strokeWidth: 4,
            scaleX: 2, scaleY: 2
        });
    }

    if (obj) store.addObject(obj);
};

const addBlob = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();
    const path = new fabric.Path('M46.7,-64.6C59.6,-53.4,68.4,-37.8,73.5,-21.2C78.6,-4.5,80.1,13.1,74.5,29.3C68.9,45.4,56.3,60.1,40.7,68.7C25.1,77.3,6.3,79.8,-11.1,77.2C-28.5,74.5,-44.6,66.8,-57.1,54.7C-69.5,42.6,-78.3,26.1,-81.2,8.6C-84.1,-8.8,-81.1,-27.3,-71.4,-42.6C-61.7,-57.9,-45.3,-70.1,-28.9,-75.4C-12.4,-80.7,4.1,-79.1,19.9,-72.5C35.7,-65.9,50.7,-54.2,46.7,-64.6Z', {
        left: center.x, top: center.y,
        fill: '#10b981',
        opacity: 0.15,
        originX: 'center', originY: 'center',
        scaleX: 2, scaleY: 2,
        id: 'obj_' + Date.now()
    });
    store.addObject(path);
};

const addBadge = (textStr, color) => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();

    const text = new fabric.IText(textStr, {
        fontSize: 14, 
        fontFamily: 'Outfit',
        fontWeight: '900',
        fill: '#ffffff',
        originX: 'center', originY: 'center',
        charSpacing: 200,
        top: 1
    });

    const bg = new fabric.Rect({
        width: text.width + 40, height: Math.max(text.height + 20, 32),
        fill: color,
        rx: 16, ry: 16,
        originX: 'center', originY: 'center'
    });

    const group = new fabric.Group([bg, text], {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now(),
        subTargetCheck: true
    });
    
    store.addObject(group);
};

const addDataBlock = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();

    const iconBg = new fabric.Circle({
        radius: 18,
        fill: '#f1f5f9',
        originX: 'center', originY: 'center',
        left: -80
    });

    const icon = new fabric.IText('\uf095', { 
        fontSize: 14, 
        fontFamily: '"Font Awesome 5 Free"',
        fontWeight: '900',
        fill: '#10b981',
        originX: 'center', originY: 'center',
        left: -80,
        top: 1
    });

    const label = new fabric.IText('PRIMARY CONTACT', {
        fontSize: 8, 
        fontFamily: 'Outfit',
        fontWeight: '900',
        fill: '#94a3b8',
        originX: 'left', originY: 'bottom',
        left: -50, top: -2,
        charSpacing: 100
    });

    const value = new fabric.IText('+1 (555) 000-0000', {
        fontSize: 12, 
        fontFamily: 'Outfit',
        fontWeight: '700',
        fill: '#1e293b',
        originX: 'left', originY: 'top',
        left: -50, top: 2
    });

    const group = new fabric.Group([iconBg, icon, label, value], {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now(),
        subTargetCheck: true
    });
    
    store.addObject(group);
};

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (!file || !store.canvas) return;

    const reader = new FileReader();
    reader.onload = async (f) => {
        try {
            const img = await fabric.FabricImage.fromURL(f.target.result);
            img.set({ 
                left: store.canvas.getCenterPoint().x, 
                top: store.canvas.getCenterPoint().y,
                originX: 'center',
                originY: 'center',
                id: 'obj_' + Date.now()
            });
            img.scaleToWidth(250);
            store.addObject(img);
        } catch (err) {
            console.error('Image Load Error:', err);
        }
    };
    reader.readAsDataURL(file);
};

const addPhotoPlaceholder = () => {
    if (!store.canvas) return;
    
    const center = store.canvas.getCenterPoint();

    const frame = new fabric.Rect({
        width: 140, height: 180,
        fill: '#f8fafc',
        stroke: '#e2e8f0',
        strokeWidth: 2,
        strokeDashArray: [8, 4],
        rx: 16, ry: 16,
        originX: 'center', originY: 'center'
    });

    const icon = new fabric.IText('', {
        fontSize: 50, 
        fontFamily: 'Font Awesome 6 Free',
        fontWeight: '900',
        fill: '#cbd5e1',
        originX: 'center', originY: 'center',
        top: -10
    });

    const text = new fabric.IText('PROFILE FEED', {
        fontSize: 9, 
        fontFamily: 'Outfit',
        fontWeight: '900',
        fill: '#94a3b8',
        originX: 'center', originY: 'center',
        top: 40,
        charSpacing: 200
    });

    const group = new fabric.Group([frame, icon, text], {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now()
    });
    
    group.set('data_binding', 'photo_placeholder'); 
    group.set('label', 'Profile Anchor');
    
    store.addObject(group);
};

const addQrCode = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();
    
    const bg = new fabric.Rect({
        width: 100, height: 100,
        fill: '#ffffff',
        stroke: '#000000',
        strokeWidth: 1,
        rx: 8, ry: 8,
        originX: 'center', originY: 'center'
    });

    const qrIcon = new fabric.IText('', {
        fontSize: 60,
        fontFamily: 'Font Awesome 6 Free',
        fontWeight: '900',
        fill: '#000000',
        originX: 'center', originY: 'center'
    });

    const group = new fabric.Group([bg, qrIcon], {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now()
    });

    group.set('data_binding', 'qr_code');
    group.set('label', 'Access Key');
    store.addObject(group);
};

const addDynamicText = (fieldLabel) => {
    if (!store.canvas) return;
    
    const fieldMap = {
        'Name': 'user.name',
        'Designation': 'user.designation',
        'Department': 'user.department',
        'ID Number': 'user.employee_id',
        'Blood Group': 'user.blood_group',
        'Joining Date': 'user.joining_date',
        'Email': 'user.email',
        'Phone': 'user.phone',
        'DOB': 'user.dob',
        'Emergency Name': 'user.emergency_name',
        'Emergency Phone': 'user.emergency_phone',
        'Address': 'user.address'
    };

    const center = store.canvas.getCenterPoint();
    const text = new fabric.IText(fieldLabel.toUpperCase(), {
        left: center.x, top: center.y,
        fontFamily: 'Outfit',
        fontSize: 14,
        fill: '#10b981',
        fontWeight: '900',
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now(),
        charSpacing: 100
    });

    text.set('data_binding', fieldMap[fieldLabel] || null);
    text.set('label', fieldLabel + ' Signal');

    store.addObject(text);
};

const addSignature = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();
    
    const line = new fabric.Path('M0 0 L150 0', {
        stroke: '#cbd5e1',
        strokeWidth: 1,
        originX: 'center', originY: 'center',
        top: 20
    });

    const sigText = new fabric.IText('AUTHORIZED SIGNATURE', {
        fontSize: 10,
        fontFamily: 'Outfit',
        fontWeight: '900',
        fill: '#94a3b8',
        originX: 'center', originY: 'center',
        top: 40,
        charSpacing: 100
    });

    const scriptText = new fabric.IText('Executive Director', {
        fontSize: 24,
        fontFamily: 'Playfair Display',
        fontStyle: 'italic',
        fill: '#1e293b',
        originX: 'center', originY: 'center'
    });

    const group = new fabric.Group([line, sigText, scriptText], {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now()
    });

    group.set('label', 'Signature Matrix');
    store.addObject(group);
};

const addSafetyTerms = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();

    const text = new fabric.IText('SAFEGUARD PROTOCOL\nThis credential is the property of the organization. If found, please return to any authority or mail to the central office. Tampering with this card is a violation of security protocols.', {
        fontSize: 12,
        fontFamily: 'Outfit',
        fontWeight: '500',
        fill: '#64748b',
        textAlign: 'center',
        originX: 'center', originY: 'center',
        width: 300,
        lineHeight: 1.5
    });

    text.set('id', 'obj_' + Date.now());
    text.set('label', 'Safety Protocol Block');
    store.addObject(text);
};

const addHologram = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();
    
    const poly = new fabric.Path('M50 0 L100 25 L100 75 L50 100 L0 75 L0 25 Z', {
        fill: 'transparent',
        stroke: '#10b981',
        strokeWidth: 1,
        opacity: 0.1,
        originX: 'center', originY: 'center',
        scaleX: 3, scaleY: 3
    });

    const glow = new fabric.Circle({
        radius: 100,
        fill: '#10b981',
        opacity: 0.05,
        originX: 'center', originY: 'center'
    });

    const group = new fabric.Group([poly, glow], {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now(),
        selectable: true,
        evented: true
    });

    group.set('label', 'Aesthetic Hologram');
    store.addObject(group);
};

const addBarcode = () => {
    if (!store.canvas) return;
    const center = store.canvas.getCenterPoint();
    
    // Create a series of vertical lines to simulate barcode
    const objects = [];
    const widths = [2, 4, 1, 3, 2, 5, 2, 1, 4, 2, 3, 1, 4, 2];
    let currentX = 0;
    
    widths.forEach((w, i) => {
        if (i % 2 === 0) {
            objects.push(new fabric.Rect({
                left: currentX,
                top: 0,
                width: w * 1.5,
                height: 40,
                fill: '#000'
            }));
        }
        currentX += w * 1.5;
    });

    const codeText = new fabric.IText('1234567890', {
        fontSize: 10,
        fontFamily: 'Roboto Mono',
        fontWeight: 'bold',
        fill: '#000',
        top: 45,
        left: currentX / 2,
        originX: 'center'
    });
    objects.push(codeText);

    const group = new fabric.Group(objects, {
        left: center.x, top: center.y,
        originX: 'center', originY: 'center',
        id: 'obj_' + Date.now()
    });

    group.set('data_binding', 'user.employee_id');
    group.set('label', 'Barcode Node');
    store.addObject(group);
};
</script>

<template>
    <div class="flex flex-col w-full h-full select-none gap-9 font-outfit">
        <!-- Asset Filter Nexus -->
        <div class="space-y-3 px-1">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                <input v-model="searchQuery" type="text" placeholder="Search Matrix Nodes..." class="w-full bg-slate-50 border border-slate-100 rounded-xl py-3 pl-10 pr-4 text-sm font-black uppercase tracking-widest outline-none focus:border-emerald-500/50 focus:bg-white transition-all">
            </div>
        </div>

        <!-- 1. Geometric & Base Nodes -->
        <div v-if="isFiltered('Structural Nodes Type Boundary Orbit Liquid Inject')" class="space-y-5">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] flex items-center justify-between px-1">
                <span>Structural Nodes</span>
                <i class="fas fa-shapes text-xs opacity-40"></i>
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <label v-if="isFiltered('Inject Upload Image')" class="flex flex-col items-center justify-center aspect-square rounded-2xl border-2 border-dashed border-slate-100 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all cursor-pointer group relative overflow-hidden">
                    <input type="file" class="hidden" accept="image/*" @change="handleImageUpload">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <i class="fas fa-cloud-upload-alt text-slate-200 group-hover:text-emerald-500 mb-3 text-2xl transition-all group-hover:-translate-y-1"></i>
                    <span class="text-xs font-black text-slate-300 group-hover:text-emerald-700 uppercase tracking-[0.2em]">Inject Flux</span>
                </label>
                
                <button v-if="isFiltered('Text Type Font')" @click="addText" class="flex flex-col items-center justify-center aspect-square rounded-2xl bg-white border border-slate-100 hover:border-slate-300 hover:shadow-xl hover:-translate-y-1 transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-12 h-12 bg-slate-50/50 rounded-bl-full group-hover:bg-emerald-50 transition-colors"></div>
                    <i class="fas fa-font text-slate-400 group-hover:text-slate-900 mb-3 text-xl relative z-10 transition-all"></i>
                    <span class="text-xs font-black text-slate-400 group-hover:text-slate-800 uppercase tracking-[0.2em] relative z-10">Type-Node</span>
                </button>

                <button v-if="isFiltered('Rectangle Square Boundary')" @click="addShape('rect')" class="flex flex-col items-center justify-center aspect-square rounded-2xl bg-white border border-slate-100 hover:border-slate-300 hover:shadow-xl hover:-translate-y-1 transition-all group pt-2">
                    <div class="w-10 h-6 border-[2.5px] border-slate-200 group-hover:border-emerald-500 group-hover:bg-emerald-50/30 rounded-lg mb-3 transition-all"></div>
                    <span class="text-xs font-black text-slate-400 group-hover:text-slate-800 uppercase tracking-[0.2em]">Boundary</span>
                </button>

                 <button v-if="isFiltered('Circle Orbit Round')" @click="addShape('circle')" class="flex flex-col items-center justify-center aspect-square rounded-2xl bg-white border border-slate-100 hover:border-slate-300 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-8 h-8 border-[2.5px] border-slate-200 group-hover:border-indigo-500 group-hover:bg-indigo-50/30 rounded-full mb-3 transition-all"></div>
                    <span class="text-xs font-black text-slate-400 group-hover:text-slate-800 uppercase tracking-[0.2em]">Orbit Node</span>
                </button>

                 <button v-if="isFiltered('Liquid Blob Wave Generator')" @click="addBlob" class="col-span-2 flex flex-col items-center justify-center py-4 rounded-2xl bg-white border border-slate-100 hover:border-emerald-300 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-8 h-8 bg-emerald-100 group-hover:bg-emerald-200 rounded-[40%_60%_70%_30%_/_40%_50%_60%_50%] mb-2 transition-all shadow-inner"></div>
                    <span class="text-xs font-black text-slate-400 group-hover:text-slate-800 uppercase tracking-[0.2em]">Liquid Wave Generator</span>
                </button>
            </div>
        </div>

        <!-- 2. Logic Bound Components -->
        <div v-if="isFiltered('Logic Anchors Photo Matrix Access Node Qr Code Signature Matrix')" class="space-y-5">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] flex items-center justify-between px-1">
                <span>Logic Anchors</span>
                <i class="fas fa-link text-xs opacity-40"></i>
            </h3>
            <div class="space-y-4">
                <button v-if="isFiltered('Photo Matrix Face Signal Avatar Profile')" @click="addPhotoPlaceholder" class="w-full flex items-center gap-5 p-5 rounded-[1.8rem] bg-white border border-slate-100 hover:border-emerald-500 hover:shadow-[0_20px_50px_-15px_rgba(16,185,129,0.15)] transition-all group text-left relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-50/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-inner relative z-10 transition-transform group-hover:scale-105">
                        <i class="fas fa-portrait text-xl"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="text-base font-black text-slate-900 group-hover:text-emerald-600 uppercase tracking-tight leading-none">Photo Matrix</div>
                        <div class="text-[7.5px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2">Dynamic Face Signal v5</div>
                    </div>
                </button>

                <button v-if="isFiltered('Access Node Qr Code Encrypted Object Key')" @click="addQrCode" class="w-full flex items-center gap-5 p-5 rounded-[1.8rem] bg-white border border-slate-100 hover:border-indigo-500 hover:shadow-[0_20px_50px_-15px_rgba(99,102,241,0.15)] transition-all group text-left relative overflow-hidden">
                     <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                     <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center shadow-inner relative z-10 transition-transform group-hover:scale-105">
                        <i class="fas fa-qrcode text-xl"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="text-base font-black text-slate-900 group-hover:text-indigo-600 uppercase tracking-tight leading-none">Access Node</div>
                        <div class="text-[7.5px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2">Encrypted Object Key</div>
                    </div>
                </button>

                <button v-if="isFiltered('Signature Matrix Script Note Pen Nib Authorized')" @click="addSignature" class="w-full flex items-center gap-5 p-5 rounded-[1.8rem] bg-white border border-slate-100 hover:border-slate-400 hover:shadow-[0_20px_50px_-15px_rgba(30,41,59,0.1)] transition-all group text-left relative overflow-hidden">
                     <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center shadow-inner relative z-10 transition-transform group-hover:scale-105">
                        <i class="fas fa-pen-nib text-xl"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="text-base font-black text-slate-900 uppercase tracking-tight leading-none">Signature Matrix</div>
                        <div class="text-[7.5px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2">Authorized Script Node</div>
                    </div>
                </button>

                <button v-if="isFiltered('Barcode Scanner Identity Binary')" @click="addBarcode" class="w-full flex items-center gap-5 p-5 rounded-[1.8rem] bg-white border border-slate-100 hover:border-slate-900 hover:shadow-2xl transition-all group text-left relative overflow-hidden">
                     <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-inner relative z-10 transition-transform group-hover:scale-105">
                        <i class="fas fa-barcode text-xl"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="text-base font-black text-slate-900 uppercase tracking-tight leading-none">Barcode Node</div>
                        <div class="text-[7.5px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2">Universal Asset Identifier</div>
                    </div>
                </button>
            </div>

            <!-- 2.5 Premium Blocks -->
            <div v-if="isFiltered('Premium Assets Staff Guest Visitor Pillar Data Safety Hologram')" class="pt-2 space-y-4">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] flex items-center justify-between px-1">
                    <span>Premium Assets</span>
                    <i class="fas fa-gem text-xs opacity-40 text-emerald-500"></i>
                </h3>
                <div class="grid grid-cols-2 gap-4">
                     <button v-if="isFiltered('Staff Pill Badge')" @click="addBadge('STAFF', '#10b981')" class="flex flex-col items-center justify-center py-4 rounded-2xl bg-white border border-slate-100 hover:border-emerald-300 hover:shadow-[0_10px_30px_-10px_rgba(16,185,129,0.3)] hover:-translate-y-1 transition-all group">
                        <div class="px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100/50 text-sm font-black uppercase mb-3 transition-all shadow-sm">STAFF</div>
                        <span class="text-xs font-black text-slate-400 group-hover:text-slate-800 uppercase tracking-[0.2em]">Staff Pill</span>
                    </button>
    
                     <button v-if="isFiltered('Visitor Guest Pill Badge')" @click="addBadge('VISITOR', '#f59e0b')" class="flex flex-col items-center justify-center py-4 rounded-2xl bg-white border border-slate-100 hover:border-amber-300 hover:shadow-[0_10px_30px_-10px_rgba(245,158,11,0.3)] hover:-translate-y-1 transition-all group">
                        <div class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-600 border border-amber-100/50 text-sm font-black uppercase mb-3 transition-all shadow-sm">VISITOR</div>
                        <span class="text-xs font-black text-slate-400 group-hover:text-slate-800 uppercase tracking-[0.2em]">Guest Pill</span>
                    </button>

                    <button v-if="isFiltered('Safety Protocol Block Standard Guidelines')" @click="addSafetyTerms" class="col-span-2 flex items-center gap-4 p-4 rounded-2xl bg-white border border-slate-100 hover:border-emerald-300 hover:shadow-xl hover:-translate-y-1 transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 shadow-sm">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="text-left flex-1">
                            <div class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Standard Guidelines</div>
                            <div class="text-sm font-bold text-slate-700">Safety Protocol Block</div>
                        </div>
                    </button>

                    <button v-if="isFiltered('Aesthetic Hologram Security Element')" @click="addHologram" class="col-span-2 flex items-center gap-4 p-4 rounded-2xl bg-slate-900 text-white border border-slate-800 hover:bg-slate-800 transition-all group shadow-2xl">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-400">
                             <i class="fas fa-certificate text-lg"></i>
                        </div>
                        <div class="text-left flex-1">
                            <div class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">Security Element</div>
                            <div class="text-sm font-bold">Aesthetic Hologram</div>
                        </div>
                   </button>
                </div>
            </div>

            <!-- Dynamic Fields -->
            <div v-if="isFiltered('Telemetry Extraction Dynamic Fields Name Designation ID Number Phone Email DOB Address Emergency')" class="pt-8 space-y-4">
                 <div class="flex items-center gap-4 px-1">
                    <h3 class="text-xs font-black text-slate-300 tracking-[0.4em] uppercase whitespace-nowrap">Telemetry Extraction</h3>
                    <div class="h-px w-full bg-slate-100"></div>
                 </div>
                 <div class="grid grid-cols-2 gap-2">
                     <button v-for="field in ['Name', 'Designation', 'Department', 'ID Number', 'Blood Group', 'Joining Date', 'Phone', 'Email', 'DOB', 'Emergency Name', 'Emergency Phone']" :key="field" @click="addDynamicText(field)" class="w-full flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group">
                        <div class="w-7 h-7 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center text-sm font-black group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm shrink-0">
                            <i class="fas fa-fingerprint text-[10px]"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-600 group-hover:text-slate-900 uppercase tracking-widest truncate">{{ field }}</span>
                     </button>
                  </div>
            </div>
        </div>
    </div>
</template>
