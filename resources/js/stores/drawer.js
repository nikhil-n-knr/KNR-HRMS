import { defineStore } from 'pinia';
import { ref, markRaw } from 'vue';

export const useDrawerStore = defineStore('drawer', () => {
    const isOpen = ref(false);
    const view = ref(null);
    const props = ref({});
    const title = ref('');

    const open = (component, componentProps = {}, drawerTitle = '') => {
        view.value = markRaw(component);
        props.value = componentProps;
        title.value = drawerTitle;
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
        setTimeout(() => {
            view.value = null;
            props.value = {};
            title.value = '';
        }, 300); // Wait for animation
    };

    return {
        isOpen,
        view,
        props,
        title,
        open,
        close
    };
});
