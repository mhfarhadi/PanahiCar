export function appBasePath() {
    if (typeof window !== 'undefined' && window.__APP_BASE_PATH__ !== undefined) {
        return String(window.__APP_BASE_PATH__ || '');
    }

    return '';
}

export function publicAsset(path) {
    const base = appBasePath();
    const normalizedPath = path.startsWith('/') ? path.slice(1) : path;

    if (!base) {
        return `/${normalizedPath}`;
    }

    const normalizedBase = base.endsWith('/') ? base.slice(0, -1) : base;

    return `${normalizedBase}/${normalizedPath}`;
}
