import { ref } from 'vue';

// Global state to allow child components to push actions to the layout
const actions = ref([]);

export function useHeaderActions() {

    // Child component calls this to set buttons
    // buttons = [{ label: 'Save', onClick: () => {}, variant: 'primary' }]
    const setActions = (buttons) => {
        actions.value = buttons;
    };

    const clearActions = () => {
        actions.value = [];
    };

    return {
        actions,
        setActions,
        clearActions
    };
}
