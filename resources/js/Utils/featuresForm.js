export function normalizeDigits(value) {
    return String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));
}

export function toPersianDigits(value) {
    return String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
}

export function formatDecimalInput(value) {
    const normalized = normalizeDigits(String(value ?? ''))
        .replace(/[^0-9.]/g, '')
        .replace(/(\..*)\./g, '$1');

    return normalized ? toPersianDigits(normalized) : '';
}

export function parseDecimal(value) {
    const normalized = normalizeDigits(String(value ?? ''))
        .replace(/[^0-9.]/g, '');

    return normalized ? Number(normalized) : 0;
}

export function formatIntegerInput(value) {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits).toLocaleString('fa-IR') : '';
}

export function parseInteger(value) {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits) : 0;
}

export function formatCount(value) {
    return Number(value || 0).toLocaleString('fa-IR');
}

export function formatMoney(value) {
    return `${Number(value || 0).toLocaleString('fa-IR')} تومان`;
}

export function formatPriceInput(value) {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits).toLocaleString('fa-IR') : '';
}

export function parsePrice(value) {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits) : 0;
}

export function todayIso() {
    const now = new Date();

    return [
        now.getFullYear(),
        String(now.getMonth() + 1).padStart(2, '0'),
        String(now.getDate()).padStart(2, '0'),
    ].join('-');
}

export function syncPickerDate(current, value) {
    if (!value) return current;

    if (typeof value.format === 'function') {
        const date = value.clone ? value.clone() : value;

        if (typeof date.locale === 'function') {
            date.locale('en');
        }

        return normalizeDigits(date.format('YYYY-MM-DD'));
    }

    return normalizeDigits(String(value));
}

export async function postJson(url, body) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(data.message || 'خطا در ارسال اطلاعات');
        error.status = response.status;
        error.payload = data;
        throw error;
    }

    return data;
}

const ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
const teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
const tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
const hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];

const threeDigitWords = (number) => {
    const parts = [];
    const h = Math.floor(number / 100);
    const rest = number % 100;

    if (h) parts.push(hundreds[h]);

    if (rest >= 10 && rest < 20) {
        parts.push(teens[rest - 10]);
    } else {
        const t = Math.floor(rest / 10);
        const o = rest % 10;
        if (t) parts.push(tens[t]);
        if (o) parts.push(ones[o]);
    }

    return parts.join(' و ');
};

export function numberToPersianWords(value) {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    if (!digits) return '';
    if (/^0+$/.test(digits)) return 'صفر';

    let number = Number(digits);

    if (!Number.isSafeInteger(number) || number < 0) return '';

    const parts = [];
    let scaleIndex = 0;

    while (number > 0 && scaleIndex < scales.length) {
        const chunk = number % 1000;

        if (chunk) {
            const chunkWords = threeDigitWords(chunk);
            parts.unshift(scales[scaleIndex] ? `${chunkWords} ${scales[scaleIndex]}` : chunkWords);
        }

        number = Math.floor(number / 1000);
        scaleIndex += 1;
    }

    return parts.join(' و ');
}
