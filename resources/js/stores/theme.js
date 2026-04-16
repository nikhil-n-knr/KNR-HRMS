import { defineStore } from 'pinia';
import { reactive } from 'vue';

const STORAGE_KEY = 'knr-theme-v1';

const defaultTheme = {
    mode: 'light',
    colors: {
        primary: '#4F46E5',
        secondary: '#10B981',
        background: '#F8FAFC',
        text: '#0F172A',
        headerBg: '#FFFFFF',
        headerText: '#0F172A',
        sidebarBg: '#FFFFFF',
        sidebarText: '#0F172A',
        buttonPrimaryBg: '#10B981',
        buttonPrimaryText: '#FFFFFF',
        buttonSecondaryBg: '#E2E8F0',
        buttonSecondaryText: '#0F172A'
    },
    typography: {
        fontFamily: 'Poppins',
        fontSize: 'medium'
    },
    components: {
        buttonStyle: 'rounded',
        cardStyle: 'shadow',
        borderRadius: 12
    },
    layout: {
        sidebarStyle: 'expanded',
        layoutWidth: 'full',
        navbarStyle: 'sticky'
    }
};

const cloneTheme = (theme) => JSON.parse(JSON.stringify(theme));
const mergeTheme = (base, incoming) => {
    const merged = cloneTheme(base);
    if (incoming && typeof incoming === 'object') {
        merged.mode = incoming.mode || merged.mode;
        merged.colors = { ...merged.colors, ...(incoming.colors || {}) };
        merged.typography = { ...merged.typography, ...(incoming.typography || {}) };
        merged.components = { ...merged.components, ...(incoming.components || {}) };
        merged.layout = { ...merged.layout, ...(incoming.layout || {}) };
    }
    return merged;
};

export const useThemeStore = defineStore('theme', () => {
    const currentTheme = reactive(cloneTheme(defaultTheme));
    const tempTheme = reactive(cloneTheme(defaultTheme));

    const hexToRgb = (hex) => {
        if (!hex) return '79, 70, 229';
        const value = hex.replace('#', '');
        const bigint = parseInt(value.length === 3 ? value.split('').map(c => c + c).join('') : value, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `${r}, ${g}, ${b}`;
    };

    const applyTheme = (theme) => {
        const root = document.documentElement;
        const body = document.body;

        root.style.setProperty('--bg-page', theme.colors.background);
        root.style.setProperty('--bg-card', theme.mode === 'dark' ? '#111827' : '#FFFFFF');
        root.style.setProperty('--text-primary', theme.colors.text);
        root.style.setProperty('--text-secondary', theme.mode === 'dark' ? '#94A3B8' : '#475569');
        root.style.setProperty('--border-color', theme.mode === 'dark' ? '#1F2937' : '#E2E8F0');
        root.style.setProperty('--primary-color', theme.colors.primary);
        root.style.setProperty('--secondary-color', theme.colors.secondary);
        root.style.setProperty('--primary-rgb', hexToRgb(theme.colors.primary));
        root.style.setProperty('--header-bg', theme.colors.headerBg || (theme.mode === 'dark' ? '#0F172A' : '#FFFFFF'));
        root.style.setProperty('--header-text', theme.colors.headerText || theme.colors.text);
        root.style.setProperty('--sidebar-bg', theme.colors.sidebarBg || (theme.mode === 'dark' ? '#0B1220' : '#FFFFFF'));
        root.style.setProperty('--sidebar-text', theme.colors.sidebarText || theme.colors.text);
        root.style.setProperty('--btn-primary-bg', theme.colors.buttonPrimaryBg || theme.colors.primary);
        root.style.setProperty('--btn-primary-text', theme.colors.buttonPrimaryText || '#FFFFFF');
        root.style.setProperty('--btn-secondary-bg', theme.colors.buttonSecondaryBg || '#E2E8F0');
        root.style.setProperty('--btn-secondary-text', theme.colors.buttonSecondaryText || '#0F172A');

        const fontStack = theme.typography.fontFamily === 'System'
            ? 'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif'
            : `${theme.typography.fontFamily}, sans-serif`;
        root.style.setProperty('--font-sans', fontStack);

        const baseFont = theme.typography.fontSize === 'small' ? '14px' : theme.typography.fontSize === 'large' ? '18px' : '16px';
        root.style.setProperty('--base-font-size', baseFont);

        const radius = Math.max(0, Math.min(24, theme.components.borderRadius || 12));
        const btnRadius = theme.components.buttonStyle === 'square' ? 6 : Math.max(6, radius);
        root.style.setProperty('--radius-card', `${radius}px`);
        root.style.setProperty('--radius-btn', `${btnRadius}px`);

        const cardShadow = theme.components.cardStyle === 'flat'
            ? 'none'
            : '0 8px 28px rgba(15, 23, 42, 0.08)';
        root.style.setProperty('--card-shadow', cardShadow);

        body.classList.toggle('theme-dark', theme.mode === 'dark');
    };

    const init = () => {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const parsed = JSON.parse(saved);
                const merged = mergeTheme(defaultTheme, parsed);
                Object.assign(currentTheme, merged);
                Object.assign(tempTheme, cloneTheme(merged));
            }
        } catch (e) {
            // ignore corrupted storage
        }
        applyTheme(currentTheme);
    };

    const saveTheme = () => {
        Object.assign(currentTheme, cloneTheme(tempTheme));
        localStorage.setItem(STORAGE_KEY, JSON.stringify(currentTheme));
        applyTheme(currentTheme);
    };

    const discardChanges = () => {
        Object.assign(tempTheme, cloneTheme(currentTheme));
    };

    const resetTheme = () => {
        Object.assign(currentTheme, cloneTheme(defaultTheme));
        Object.assign(tempTheme, cloneTheme(defaultTheme));
        localStorage.removeItem(STORAGE_KEY);
        applyTheme(currentTheme);
    };

    return {
        currentTheme,
        tempTheme,
        init,
        saveTheme,
        discardChanges,
        resetTheme,
        applyTheme
    };
});
