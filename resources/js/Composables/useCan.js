import { usePage } from '@inertiajs/vue3';

export function useCan() {
    const page = usePage();

    /**
     * Check if user has a specific permission
     */
    const can = (permission) => {
        const user = page.props.auth?.user;
        if (!user) return false;

        // Check if user has this permission
        const permissions = user.permissions || [];
        return permissions.includes(permission);
    };

    /**
     * Check if user has any of the specified permissions
     */
    const canAny = (permissions) => {
        return permissions.some(permission => can(permission));
    };

    /**
     * Check if user has all of the specified permissions
     */
    const canAll = (permissions) => {
        return permissions.every(permission => can(permission));
    };

    return {
        can,
        canAny,
        canAll
    };
}
