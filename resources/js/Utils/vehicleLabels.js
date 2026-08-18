export const vehicleStatusLabel = (value) => {
    const labels = {
        announced: 'اعلامی',
        in_stock: 'موجود',
        sold: 'فروخته‌شده',
    };

    return labels[value] ?? value ?? '—';
};

export const transmissionLabel = (value, labels = {}) =>
    labels[value] ?? value ?? '—';

export const fuelTypeLabel = (value, labels = {}) =>
    labels[value] ?? value ?? '—';

export const bodyConditionLabel = (value, labels = {}) =>
    labels[value] ?? value ?? '—';

export const colorLabel = (value) => value || '—';

export const formatMileage = (value) => {
    if (value === null || value === undefined) return '—';

    return `${Number(value).toLocaleString('fa-IR')} km`;
};

export const formatYear = (value) => {
    if (!value) return '—';

    return String(value);
};

export const formatInsurance = (months) => {
    if (months === null || months === undefined) return '—';

    return `${months} ماه`;
};
