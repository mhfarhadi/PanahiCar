import { publicAsset } from '@/Utils/publicAsset';

const ILLUSTRATION_FILES = {
    installments: 'illustration-installments.png',
    contract: 'illustration-contract.png',
    estimate: 'illustration-estimate.png',
    gold: 'illustration-gold.png',
    wanted: 'illustration-wanted.png',
    market: 'illustration-market.png',
    check: 'illustration-check.png',
    showroom: 'illustration-showroom.png',
};

const MODEL_FILES = {
    'دنا پلاس': 'dena-plus.jpg',
    تارا: 'tara.jpg',
    شاهین: 'shahin.jpg',
    اطلس: 'atlas.jpg',
    'تیگو 7 پرو': 'tiggo-7.jpg',
    'آریزو 6': 'arizo.jpg',
    'آریزو 5': 'arizo.jpg',
    فیدلیتی: 'fidelity.jpg',
    دیگنیتی: 'dignity.jpg',
    'KMC J7': 'kmc-j7.jpg',
    'تندر 90': 'tondar.jpg',
    'پژو 207': 'peugeot-207.jpg',
    'کوییک S': 'quick.jpg',
    'ساینا S': 'saina.jpg',
    'ری‌را': 'rira.jpg',
    X22: 'x22.jpg',
    'JAC J4': 'j4.jpg',
    'پژو پارس': 'pars.jpg',
    'سمند EF7': 'samand.jpg',
    'زامیاد Z24': 'zamyad.jpg',
};

function illustrationPath(name) {
    const file = ILLUSTRATION_FILES[name] || ILLUSTRATION_FILES.showroom;

    return publicAsset(`/images/illustrations/${file}`);
}

function modelPath(model) {
    const file = MODEL_FILES[model] || MODEL_FILES['دنا پلاس'];

    return publicAsset(`/images/vehicles/${file}`);
}

export function illustration(name = 'showroom') {
    return illustrationPath(name);
}

export function showroomPhoto(index = 0) {
    const keys = Object.keys(ILLUSTRATION_FILES);

    return illustrationPath(keys[Math.abs(Number(index) || 0) % keys.length]);
}

export function modelPhoto(model) {
    return modelPath(model);
}

export function mediaUrl(path, fallbackIndex = 0) {
    if (path && /^https?:\/\//.test(path)) {
        return path;
    }

    if (path && path.startsWith('/')) {
        return publicAsset(path);
    }

    if (path) {
        return publicAsset(`/storage/${path}`);
    }

    return modelPhoto(null);
}

export function vehiclePhoto(vehicle, fallbackIndex = 0) {
    if (vehicle?.cover_image) {
        return mediaUrl(vehicle.cover_image, fallbackIndex);
    }

    return modelPhoto(vehicle?.model);
}

export { ILLUSTRATION_FILES, MODEL_FILES };
