const fallback = (value) => value || '—';

export const simTypeLabel = (value) => {
    const labels = {
        single: 'تک‌سیم',
        dual: 'دو‌سیم',
    };

    return labels[value] ?? fallback(value);
};

export const conditionLabel = (value) => {
    const labels = {
        'A+': 'در حد نو',
        A: 'بسیار تمیز',
        B: 'تمیز',
        C: 'خط و خش‌دار',
    };

    return labels[value] ?? fallback(value);
};

export const registrationStatusLabel = (value) => {
    const labels = {
        registered: 'رجیستر شده',
        unregistered: 'رجیستر نشده',
    };

    return labels[value] ?? fallback(value);
};

export const deviceStatusLabel = (value) => {
    const labels = {
        announced: 'اعلامی',
        in_stock: 'موجود',
        sold: 'فروخته‌شده',
    };

    return labels[value] ?? fallback(value);
};

export const colorLabel = (value) => {
    const labels = {
        Black: 'مشکی (Black)',
        White: 'سفید (White)',
        Blue: 'آبی (Blue)',
        Green: 'سبز (Green)',
        Pink: 'صورتی (Pink)',
        Graphite: 'گرافیتی (Graphite)',
        Gold: 'طلایی (Gold)',
        Silver: 'نقره‌ای (Silver)',
        'Sierra Blue': 'آبی سیرا (Sierra Blue)',
        'Natural Titanium': 'تیتانیوم طبیعی (Natural Titanium)',
        'Black Titanium': 'تیتانیوم مشکی (Black Titanium)',
        'Blue Titanium': 'تیتانیوم آبی (Blue Titanium)',
        'White Titanium': 'تیتانیوم سفید (White Titanium)',
        Midnight: 'نیمه‌شب (Midnight)',
        Starlight: 'استارلایت (Starlight)',
        '(PRODUCT)RED': 'قرمز ((PRODUCT)RED)',
        'Alpine Green': 'سبز آلپاین (Alpine Green)',
        'Space Black': 'مشکی فضایی (Space Black)',
        'Deep Purple': 'بنفش تیره (Deep Purple)',
        Purple: 'بنفش (Purple)',
        Yellow: 'زرد (Yellow)',
        'Desert Titanium': 'تیتانیوم صحرایی (Desert Titanium)',
        Teal: 'سبزآبی (Teal)',
        Ultramarine: 'آبی اولترامارین (Ultramarine)',
    };

    return labels[value] ?? fallback(value);
};


export const samsungBatteryConditionOptions = [
    { value: 'excellent', label: 'عالی' },
    { value: 'good', label: 'خوب' },
    { value: 'poor', label: 'ضعیف' },
    { value: 'replace', label: 'نیاز به تعویض' },
];

export const manufacturingCountryOptions = [
    { value: 'vietnam', label: 'ویتنام' },
    { value: 'india', label: 'هند' },
    { value: 'china', label: 'چین' },
    { value: 'south_korea', label: 'کره جنوبی' },
    { value: 'indonesia', label: 'اندونزی' },
];

export const batteryConditionLabel = (value) => {
    const option = samsungBatteryConditionOptions.find(
        (item) => item.value === value
    );

    return option?.label ?? value ?? '—';
};

export const manufacturingCountryLabel = (value) => {
    const option = manufacturingCountryOptions.find(
        (item) => item.value === value
    );

    return option?.label ?? value ?? '—';
};
