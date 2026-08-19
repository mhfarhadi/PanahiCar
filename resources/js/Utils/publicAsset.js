const base = import.meta.env.BASE_URL || '/';

export function publicAsset(path) {
    const normalizedBase = base.endsWith('/') ? base.slice(0, -1) : base;
    const normalizedPath = path.startsWith('/') ? path.slice(1) : path;

    if (!normalizedBase || normalizedBase === '/') {
        return `/${normalizedPath}`;
    }

    return `${normalizedBase}/${normalizedPath}`;
}
