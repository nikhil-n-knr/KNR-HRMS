import { defineStore } from 'pinia';
import { ref, shallowRef, markRaw, watch } from 'vue';
import * as fabric from 'fabric';

export const useStudioStore = defineStore('studio', () => {

    // --- Core ---
    const canvas = ref(null); // Fabric Canvas Instance
    const activeObject = shallowRef(null); // Currently selected object
    const layers = shallowRef([]); // List of objects for layer panel

    // --- Sidebar & UI State ---
    const leftActiveTab = ref('assets'); // assets, templates
    const rightSidebarVisible = ref(true); // Toggle for Property Panel
    const zoom = ref(1);
    const isSaved = ref(true);
    const historyLimit = 100;
    const clipboard = shallowRef(null);
    const snapToGrid = ref(false);
    const gridSize = ref(10);
    const smartGuides = ref(true); // Magnetic guides

    // --- Design Metadata ---
    const designName = ref('');
    const designType = ref('Standard');

    // --- Dual Canvas State ---
    const activeSide = ref('front'); // 'front' | 'back'
    const frontState = shallowRef(null);
    const backState = shallowRef(null);
    const orientation = ref('Landscape'); // 'Landscape' | 'Portrait'
    const isCanvasReady = ref(false);

    // --- History Tracking ---
    const history = shallowRef([]);
    const historyIndex = ref(-1);
    const isSwitchingSide = ref(false);

    // --- Canvas Proportions & Presets ---
    const currentSize = ref('CR80');
    const sizes = {
        'CR80': { width: 1011, height: 638, label: 'Standard (85.6x54mm)' },
        'CR79': { width: 991, height: 602, label: 'Snug (83.9x51mm)' },
        'CR100': { width: 1181, height: 826, label: 'Oversized (100x70mm)' }
    };

    // Style Token (Premium Emerald theme)
    const premiumHandleStyle = {
        cornerColor: '#10b981',
        cornerStyle: 'circle',
        cornerSize: 8,
        transparentCorners: false,
        borderColor: '#10b981',
        padding: 8,
        borderDashArray: [3, 3]
    };

    // --- Initializer ---
    const initCanvas = (fabricCanvas) => {
        canvas.value = markRaw(fabricCanvas);

        // Global Selection Styles
        canvas.value.selectionColor = 'rgba(16, 185, 129, 0.1)';
        canvas.value.selectionBorderColor = '#10b981';
        canvas.value.selectionLineWidth = 1;

        // Event Listeners
        canvas.value.on('selection:created', updateSelection);
        canvas.value.on('selection:updated', updateSelection);
        canvas.value.on('selection:cleared', () => {
            activeObject.value = null;
        });

        canvas.value.on('object:added', updateLayers);
        canvas.value.on('object:modified', (e) => {
            if (snapToGrid.value && e.target) {
                e.target.set({
                    left: Math.round(e.target.left / gridSize.value) * gridSize.value,
                    top: Math.round(e.target.top / gridSize.value) * gridSize.value
                });
            }
            updateLayers();
            pushHistory();
        });
        canvas.value.on('object:removed', updateLayers);

        // --- Smart Guides Logic (Canva Pro: Object-to-Object) ---
        canvas.value.on('object:moving', (e) => {
            const obj = e.target;
            if (snapToGrid.value) {
                obj.set({
                    left: Math.round(obj.left / gridSize.value) * gridSize.value,
                    top: Math.round(obj.top / gridSize.value) * gridSize.value
                });
            }

            if (smartGuides.value) {
                const margin = 12;
                const canvasW = canvas.value.width;
                const canvasH = canvas.value.height;
                const objects = canvas.value.getObjects().filter(o => o !== obj && o.visible);

                // 1. Canvas Center Snapping
                const objCenterW = obj.originX === 'center' ? obj.left : obj.left + (obj.getScaledWidth() / 2);
                const objCenterH = obj.originY === 'center' ? obj.top : obj.top + (obj.getScaledHeight() / 2);

                if (Math.abs(objCenterW - (canvasW / 2)) < margin) {
                    obj.set('left', obj.originX === 'center' ? canvasW / 2 : canvasW / 2 - (obj.getScaledWidth() / 2));
                }
                if (Math.abs(objCenterH - (canvasH / 2)) < margin) {
                    obj.set('top', obj.originY === 'center' ? canvasH / 2 : canvasH / 2 - (obj.getScaledHeight() / 2));
                }

                // 2. Object-to-Object Snapping (Tier-2)
                objects.forEach(target => {
                    const tLeft = target.left;
                    const tTop = target.top;
                    const tWidth = target.getScaledWidth();
                    const tHeight = target.getScaledHeight();

                    // Snap to Target Edges / Centers
                    const targetCenterW = target.originX === 'center' ? target.left : target.left + (tWidth / 2);
                    
                    if (Math.abs(objCenterW - targetCenterW) < margin) {
                         obj.set('left', obj.originX === 'center' ? targetCenterW : targetCenterW - (obj.getScaledWidth() / 2));
                    }
                });
            }
        });

        // Load correct side on startup
        if (activeSide.value === 'front' && frontState.value) {
            loadSnapshot(frontState.value);
        } else if (activeSide.value === 'back' && backState.value) {
            loadSnapshot(backState.value);
        }

        isCanvasReady.value = true;
    };

    const updateSelection = (e) => {
        const selected = e.selected[0];
        if (selected) {
            activeObject.value = selected;
            rightSidebarVisible.value = true;
        }
    };

    const updateLayers = () => {
        if (!canvas.value || isSwitchingSide.value) return;
        layers.value = [...canvas.value.getObjects()].reverse();
        isSaved.value = false;
        saveCurrentSide();
    };

    const saveCurrentSide = () => {
        if (!canvas.value || isSwitchingSide.value) return;
        const json = canvas.value.toObject([
            'id', 'data_binding', 'lockMovementX', 'lockMovementY', 'label', 'selectable', 
            'visible', 'isGlobal', 'clipPath', 'path', 'shadow', 'angle', 'curved', 
            'radius', 'spacing', 'charSpacing', 'rx', 'ry', 'fill', 'stroke', 
            'strokeWidth', 'opacity', 'fontFamily', 'fontSize', 'fontWeight', 
            'fontStyle', 'underline', 'textAlign', 'lineHeight'
        ]);
        if (activeSide.value === 'front') frontState.value = json;
        else backState.value = json;
    };

    // --- Step-wise Reordering ---
    const bringToFront = () => {
        if (!canvas.value || !activeObject.value) return;
        activeObject.value.bringToFront();
        canvas.value.renderAll();
        updateLayers();
    };

    const bringForward = () => {
        if (!canvas.value || !activeObject.value) return;
        activeObject.value.bringForward();
        canvas.value.renderAll();
        updateLayers();
    };

    const sendToBack = () => {
        if (!canvas.value || !activeObject.value) return;
        activeObject.value.sendToBack();
        canvas.value.renderAll();
        updateLayers();
    };

    const sendBackward = () => {
        if (!canvas.value || !activeObject.value) return;
        activeObject.value.sendBackward();
        canvas.value.renderAll();
        updateLayers();
    };

    const switchSide = async (side) => {
        if (!canvas.value || side === activeSide.value) return;

        // Capture state of current side before clearing
        const oldJson = canvas.value.toObject([
            'id', 'data_binding', 'lockMovementX', 'lockMovementY', 'label', 'selectable', 
            'visible', 'isGlobal', 'clipPath', 'path', 'shadow', 'angle', 'curved', 
            'radius', 'spacing', 'charSpacing', 'rx', 'ry', 'fill', 'stroke', 
            'strokeWidth', 'opacity', 'fontFamily', 'fontSize', 'fontWeight', 
            'fontStyle', 'underline', 'textAlign', 'lineHeight'
        ]);
        
        if (activeSide.value === 'front') frontState.value = oldJson;
        else backState.value = oldJson;

        isSwitchingSide.value = true;
        canvas.value.discardActiveObject();
        canvas.value.clear();
        canvas.value.backgroundColor = '#ffffff';

        activeSide.value = side;
        const newState = side === 'front' ? frontState.value : backState.value;

        if (newState) {
            await loadSnapshot(newState);
        } else {
            layers.value = [];
            pushHistory();
            canvas.value.renderAll();
        }
        isSwitchingSide.value = false;
        updateLayers(); // Refresh UI after loading
    };

    // --- Batch Property Edit ---
    const updateBatchProp = (key, value) => {
        if (!canvas.value) return;
        const activeGroup = canvas.value.getActiveObject();
        if (!activeGroup) return;

        if (activeGroup.type === 'activeSelection') {
            activeGroup.forEachObject(obj => {
                obj.set(key, value);
            });
        } else {
            activeGroup.set(key, value);
        }
        canvas.value.renderAll();
        pushHistory();
    };

    const centerObjectH = () => {
        if (!canvas.value || !activeObject.value) return;
        canvas.value.centerObjectH(activeObject.value);
        activeObject.value.setCoords();
        canvas.value.requestRenderAll();
        pushHistory();
    };

    const centerObjectV = () => {
        if (!canvas.value || !activeObject.value) return;
        canvas.value.centerObjectV(activeObject.value);
        activeObject.value.setCoords();
        canvas.value.requestRenderAll();
        pushHistory();
    };

    const setCanvasSize = (preset) => {
        if (!sizes[preset]) return;
        currentSize.value = preset;
        let { width, height } = sizes[preset];

        if (orientation.value === 'Portrait') {
            [width, height] = [height, width];
        }

        if (canvas.value) {
            canvas.value.setDimensions({ width, height });
            canvas.value.renderAll();
        }
        pushHistory();
    };

    const toggleOrientation = () => {
        orientation.value = (orientation.value === 'Landscape' ? 'Portrait' : 'Landscape');
        setCanvasSize(currentSize.value);
    };

    const getDimensions = () => {
        let { width, height } = sizes[currentSize.value];
        if (orientation.value === 'Portrait') {
            [width, height] = [height, width];
        }
        return { width, height, preset: currentSize.value, orientation: orientation.value };
    };

    const setZoom = (value) => {
        const newZoom = Math.max(0.1, Math.min(5, value));
        zoom.value = newZoom;
    };

    const copy = async () => {
        if (!canvas.value || !activeObject.value) return;
        const cloned = await activeObject.value.clone();
        clipboard.value = cloned;
    };

    const paste = async () => {
        if (!canvas.value || !clipboard.value) return;
        const cloned = await clipboard.value.clone();
        cloned.set({
            left: cloned.left + 20,
            top: cloned.top + 20,
            evented: true,
        });
        if (cloned.type === 'activeSelection') {
            cloned.canvas = canvas.value;
            cloned.forEachObject((obj) => canvas.value.add(obj));
            cloned.setCoords();
        } else {
            canvas.value.add(cloned);
        }
        clipboard.value.left += 20;
        clipboard.value.top += 20;
        canvas.value.setActiveObject(cloned);
        canvas.value.requestRenderAll();
        pushHistory();
    };

    const addObject = (obj) => {
        if (!canvas.value) return;
        if (!obj.id) obj.set('id', 'obj_' + Date.now());
        obj.set(premiumHandleStyle);
        canvas.value.add(obj);
        canvas.value.setActiveObject(obj);
        pushHistory();
    };

    const groupSelected = () => {
        if (!canvas.value) return;
        const active = canvas.value.getActiveObject();
        if (!active || active.type !== 'activeSelection') return;
        active.toGroup();
        canvas.value.requestRenderAll();
        updateLayers();
        pushHistory();
    };

    const ungroupSelected = () => {
        if (!canvas.value || !activeObject.value || activeObject.value.type !== 'group') return;
        activeObject.value.toActiveSelection();
        canvas.value.requestRenderAll();
        updateLayers();
        pushHistory();
    };

    const applyImageFrame = (frameType) => {
        if (!canvas.value || !activeObject.value || activeObject.value.type !== 'image') return;
        const obj = activeObject.value;
        let clipPath;
        if (frameType === 'circle') {
            clipPath = new fabric.Circle({ radius: obj.width / 2, originX: 'center', originY: 'center' });
        } else if (frameType === 'hexagon') {
            clipPath = new fabric.Polyline([{ x: 50, y: 0 }, { x: 100, y: 25 }, { x: 100, y: 75 }, { x: 50, y: 100 }, { x: 0, y: 75 }, { x: 0, y: 25 }], { originX: 'center', originY: 'center', scaleX: obj.width / 100, scaleY: obj.height / 100 });
        }
        obj.set('clipPath', clipPath);
        canvas.value.renderAll();
        pushHistory();
    };

    const deleteActive = () => {
        if (!canvas.value || !activeObject.value) return;
        const active = canvas.value.getActiveObject();
        if (active.type === 'activeSelection') {
            active.forEachObject(o => canvas.value.remove(o));
            canvas.value.discardActiveObject();
        } else {
            canvas.value.remove(active);
        }
        canvas.value.renderAll();
    };

    const pushHistory = () => {
        if (!canvas.value) return;

        let newHistory = history.value;
        if (historyIndex.value < newHistory.length - 1) {
            newHistory = newHistory.slice(0, historyIndex.value + 1);
        }

        const json = canvas.value.toJSON([
            'id', 'data_binding', 'lockMovementX', 'lockMovementY', 'label', 'visible', 
            'isGlobal', 'clipPath', 'path', 'shadow', 'angle', 'curved', 'radius', 'spacing', 'charSpacing'
        ]);
        newHistory = [...newHistory, json];

        if (newHistory.length > historyLimit) {
            newHistory.shift();
        } else {
            historyIndex.value++;
        }

        history.value = newHistory;
    };

    const undo = () => {
        if (historyIndex.value <= 0) return;
        historyIndex.value--;
        loadSnapshot(history.value[historyIndex.value]);
    };

    const redo = () => {
        if (historyIndex.value >= history.value.length - 1) return;
        historyIndex.value++;
        loadSnapshot(history.value[historyIndex.value]);
    };

    const loadSnapshot = async (json) => {
        if (!canvas.value) return;
        try {
            canvas.value.off('object:added', updateLayers);
            let data = typeof json === 'string' ? JSON.parse(json) : JSON.parse(JSON.stringify(json));
            if (data) {
                await canvas.value.loadFromJSON(data);
                canvas.value.getObjects().forEach(obj => obj.set(premiumHandleStyle));
                canvas.value.renderAll();
            }
            updateLayers();
            canvas.value.on('object:added', updateLayers);
        } catch (e) {
            console.error('Snapshot Load Error:', e);
        }
    };

    const loadDesign = async (designData) => {
        if (!canvas.value || !designData) return;
        isSwitchingSide.value = true;
        try {
            const parsed = typeof designData === 'string' ? JSON.parse(designData) : designData;
            
            // Core state hydration
            if (parsed.front || parsed.back) {
                frontState.value = parsed.front;
                backState.value = parsed.back;
                const sideToLoad = (activeSide.value === 'front') ? parsed.front : parsed.back;
                if (sideToLoad) await loadSnapshot(sideToLoad);
            } else {
                await loadSnapshot(parsed);
                frontState.value = parsed;
            }
            isSaved.value = true;
        } catch (e) {
            console.error('Failed to load design:', e);
        }
        isSwitchingSide.value = false;
        updateLayers();
    };

    const toggleCurvedText = (enabled) => {
        if (!canvas.value || !activeObject.value || !isText(activeObject.value)) return;
        const obj = activeObject.value;

        if (enabled) {
            obj.set({
                id: obj.id || ('text_' + Date.now()),
                curved: true,
                radius: 200,
                spacing: 10
            });
        } else {
            obj.set('curved', false);
        }
        canvas.value.renderAll();
        pushHistory();
    };

    const isText = (obj) => obj && (obj.type === 'i-text' || obj.type === 'text');

    const isDrawingMode = ref(false);
    const brushColor = ref('#10b981');
    const brushWidth = ref(3);

    const toggleDrawingMode = (enabled) => {
        if (!canvas.value) return;
        isDrawingMode.value = enabled;
        canvas.value.isDrawingMode = enabled;

        if (enabled) {
            canvas.value.freeDrawingBrush.color = brushColor.value;
            canvas.value.freeDrawingBrush.width = brushWidth.value;
        }
    };

    // Watchers for drawing
    watch(brushColor, (newColor) => {
        if (canvas.value && isDrawingMode.value) {
            canvas.value.freeDrawingBrush.color = newColor;
        }
    });

    watch(brushWidth, (newWidth) => {
        if (canvas.value && isDrawingMode.value) {
            canvas.value.freeDrawingBrush.width = newWidth;
        }
    });

    return {
        canvas, activeObject, layers, zoom, isSaved, activeSide, frontState, backState,
        currentSize, sizes, isDrawingMode, brushColor, brushWidth, snapToGrid, gridSize,
        smartGuides, orientation, isCanvasReady, designName, designType, rightSidebarVisible, leftActiveTab,
        initCanvas, switchSide, setCanvasSize, toggleOrientation, addObject, applyImageFrame,
        groupSelected, ungroupSelected, deleteActive, bringToFront, sendToBack, bringForward, sendBackward, updateBatchProp,
        centerObjectH, centerObjectV, copy, paste, setZoom, undo, redo, loadDesign, toggleDrawingMode, toggleCurvedText,
        pushHistory, saveCurrentSide, getDimensions  // ← added getDimensions
    };
});
