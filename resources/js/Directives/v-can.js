import { usePage } from '@inertiajs/vue3';

export default {
    mounted(el, binding) {
        checkPermission(el, binding);
    },
    updated(el, binding) {
        checkPermission(el, binding);
    }
};

function checkPermission(el, binding) {
    const { value } = binding;
    const page = usePage();
    const user = page.props.auth?.user;

    // 1. Super Admin Bypass (Assumes user.roles is an array of strings or objects with name)
    // Check key structure in HandleInertiaRequests too
    const isSuperAdmin = user?.roles?.some(r => (typeof r === 'string' ? r : r.name) === 'Super Admin');

    if (isSuperAdmin) {
        el.style.display = '';
        return;
    }

    // 2. Permission Check
    if (!user || !user.permissions?.includes(value)) {
        el.style.display = 'none';
    } else {
        el.style.display = '';
    }
}
