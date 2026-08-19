import '@fontsource-variable/vazirmatn';
import '@fontsource-variable/estedad';
import '@fontsource-variable/parastoo';

import { THEME_KEY } from '@/Utils/brand';

export const COLOR_KEY = 'panahi_color';
export const FONT_KEY = 'panahi_font';

export const COLOR_PALETTES = [
    {
        id: 'mint',
        label: 'نعناعی',
        swatch: ['#86efac', '#60a5fa'],
    },
    {
        id: 'lavender',
        label: 'بنفش',
        swatch: ['#c4b5fd', '#a78bfa'],
    },
    {
        id: 'coral',
        label: 'مرجانی',
        swatch: ['#fdba74', '#fb7185'],
    },
    {
        id: 'ocean',
        label: 'اقیانوس',
        swatch: ['#67e8f9', '#38bdf8'],
    },
    {
        id: 'sand',
        label: 'طلایی',
        swatch: ['#fde68a', '#fbbf24'],
    },
];

export const FONT_OPTIONS = [
    {
        id: 'vazirmatn',
        label: 'وزیرمتن',
        sample: 'پناهی کار — نمایشگاه خودرو',
        family: "'Vazirmatn Variable', Vazirmatn, sans-serif",
    },
    {
        id: 'estedad',
        label: 'استعداد',
        sample: 'پناهی کار — نمایشگاه خودرو',
        family: "'Estedad Variable', Estedad, sans-serif",
    },
    {
        id: 'parastoo',
        label: 'پرستو',
        sample: 'پناهی کار — نمایشگاه خودرو',
        family: "'Parastoo Variable', Parastoo, sans-serif",
    },
];

export const THEME_MODES = [
    { id: 'light', label: 'روشن' },
    { id: 'dark', label: 'تاریک' },
    { id: 'system', label: 'سیستم' },
];

export function getSavedColor() {
    return localStorage.getItem(COLOR_KEY) || 'mint';
}

export function getSavedFont() {
    return localStorage.getItem(FONT_KEY) || 'vazirmatn';
}

export function getSavedTheme() {
    return localStorage.getItem(THEME_KEY) || localStorage.getItem('automaya_theme') || 'system';
}

export function shouldUseDark(theme = getSavedTheme()) {
    if (theme === 'dark') {
        return true;
    }

    if (theme === 'light') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

export function applyThemeMode(theme) {
    localStorage.setItem(THEME_KEY, theme);
    document.documentElement.classList.toggle('dark', shouldUseDark(theme));
}

export function applyColorScheme(colorId) {
    const palette = COLOR_PALETTES.some((item) => item.id === colorId) ? colorId : 'mint';

    localStorage.setItem(COLOR_KEY, palette);
    document.documentElement.dataset.color = palette;
}

export function applyFontFamily(fontId) {
    const font = FONT_OPTIONS.find((item) => item.id === fontId) || FONT_OPTIONS[0];

    localStorage.setItem(FONT_KEY, font.id);
    document.documentElement.dataset.font = font.id;
    document.documentElement.style.setProperty('--ph-font', font.family);
    document.documentElement.style.fontFamily = font.family;

    if (document.body) {
        document.body.style.fontFamily = font.family;
    }
}

export function initAppearance() {
    applyColorScheme(getSavedColor());
    applyThemeMode(getSavedTheme());
    applyFontFamily(getSavedFont());

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getSavedTheme() === 'system') {
            applyThemeMode('system');
        }
    });
}
