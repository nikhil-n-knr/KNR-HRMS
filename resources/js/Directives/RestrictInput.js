const RestrictInput = {
    mounted(el, binding) {
        // If element is not an input, find the input inside (standard for wrapped components)
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        const handler = (e) => {
            let regex;
            let value = e.target.value;

            // Validation Logic
            if (binding.modifiers.number) {
                // Digits only
                regex = /[^0-9]/g;
                if (regex.test(value)) {
                    e.target.value = value.replace(regex, '');
                    // Trigger input event for v-model to update
                    e.target.dispatchEvent(new Event('input'));
                }

                // Leading Zero Prevent (Only if not just "0")
                if (value.length > 1 && value.startsWith('0')) {
                    e.target.value = parseInt(value, 10).toString();
                    e.target.dispatchEvent(new Event('input'));
                }
            }
            else if (binding.modifiers.decimal) {
                // Allow digits and one dot
                regex = /[^0-9.]/g;
                if (regex.test(value)) {
                    e.target.value = value.replace(regex, '');
                    e.target.dispatchEvent(new Event('input'));
                }
            }
        };

        input.addEventListener('input', handler);
        // Store handler to remove later
        el._restrictHandler = handler;
    },
    unmounted(el) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (input && el._restrictHandler) {
            input.removeEventListener('input', el._restrictHandler);
        }
    }
};

export default RestrictInput;
