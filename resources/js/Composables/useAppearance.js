import { computed, ref } from 'vue';
import {
    COLOR_PALETTES,
    FONT_OPTIONS,
    THEME_MODES,
    applyColorScheme,
    applyFontFamily,
    applyThemeMode,
    getSavedColor,
    getSavedFont,
    getSavedTheme,
    shouldUseDark,
} from '@/Utils/appearance';

const colorScheme = ref(getSavedColor());
const fontFamily = ref(getSavedFont());
const pendingFont = ref(getSavedFont());
const themeMode = ref(getSavedTheme());
const isDark = ref(shouldUseDark(themeMode.value));
const fontApplied = ref(false);

function syncDarkState() {
    isDark.value = shouldUseDark(themeMode.value);
}

export function useAppearance() {
    const setThemeMode = (mode) => {
        themeMode.value = mode;
        applyThemeMode(mode);
        syncDarkState();
    };

    const toggleTheme = () => {
        const next = isDark.value ? 'light' : 'dark';
        setThemeMode(next);
    };

    const setColorScheme = (colorId) => {
        colorScheme.value = colorId;
        applyColorScheme(colorId);
    };

    const selectFont = (fontId) => {
        pendingFont.value = fontId;
        fontApplied.value = false;
    };

    const applySelectedFont = () => {
        fontFamily.value = pendingFont.value;
        applyFontFamily(pendingFont.value);
        fontApplied.value = true;
        window.setTimeout(() => {
            fontApplied.value = false;
        }, 2200);
    };

    const hasPendingFont = computed(() => pendingFont.value !== fontFamily.value);

    return {
        colorScheme,
        fontFamily,
        pendingFont,
        themeMode,
        fontApplied,
        hasPendingFont,
        isDark: computed(() => isDark.value),
        colorPalettes: COLOR_PALETTES,
        fontOptions: FONT_OPTIONS,
        themeModes: THEME_MODES,
        setThemeMode,
        toggleTheme,
        setColorScheme,
        selectFont,
        applySelectedFont,
    };
}
