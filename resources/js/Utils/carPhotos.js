const ILLUSTRATIONS = {
    installments: '/images/illustrations/illustration-installments.png',
    contract: '/images/illustrations/illustration-contract.png',
    estimate: '/images/illustrations/illustration-estimate.png',
    gold: '/images/illustrations/illustration-gold.png',
    wanted: '/images/illustrations/illustration-wanted.png',
    market: '/images/illustrations/illustration-market.png',
    check: '/images/illustrations/illustration-check.png',
    showroom: '/images/illustrations/illustration-showroom.png',
};

const MODEL_PHOTOS = {
    'دنا پلاس': '/images/vehicles/dena-plus.jpg',
    تارا: '/images/vehicles/tara.jpg',
    شاهین: '/images/vehicles/shahin.jpg',
    اطلس: '/images/vehicles/atlas.jpg',
    'تیگو 7 پرو': '/images/vehicles/tiggo-7.jpg',
    'آریزو 6': '/images/vehicles/arizo.jpg',
    'آریزو 5': '/images/vehicles/arizo.jpg',
    فیدلیتی: '/images/vehicles/fidelity.jpg',
    دیگنیتی: '/images/vehicles/dignity.jpg',
    'KMC J7': '/images/vehicles/kmc-j7.jpg',
    'تندر 90': '/images/vehicles/tondar.jpg',
    'پژو 207': '/images/vehicles/peugeot-207.jpg',
    'کوییک S': '/images/vehicles/quick.jpg',
    'ساینا S': '/images/vehicles/saina.jpg',
    'ری‌را': '/images/vehicles/rira.jpg',
    X22: '/images/vehicles/x22.jpg',
    'JAC J4': '/images/vehicles/j4.jpg',
    'پژو پارس': '/images/vehicles/pars.jpg',
    'سمند EF7': '/images/vehicles/samand.jpg',
    'زامیاد Z24': '/images/vehicles/zamyad.jpg',
};

export function illustration(name = 'showroom') {
    return ILLUSTRATIONS[name] || ILLUSTRATIONS.showroom;
}

export function showroomPhoto(index = 0) {
    const keys = Object.keys(ILLUSTRATIONS);

    return ILLUSTRATIONS[keys[Math.abs(Number(index) || 0) % keys.length]];
}

export function modelPhoto(model) {
    return MODEL_PHOTOS[model] || MODEL_PHOTOS['دنا پلاس'];
}

export function mediaUrl(path, fallbackIndex = 0) {
    if (path && /^https?:\/\//.test(path)) {
        return path;
    }

    if (path && path.startsWith('/')) {
        return path;
    }

    if (path) {
        return `/storage/${path}`;
    }

    return modelPhoto(null);
}

export function vehiclePhoto(vehicle, fallbackIndex = 0) {
    if (vehicle?.cover_image) {
        return mediaUrl(vehicle.cover_image, fallbackIndex);
    }

    return modelPhoto(vehicle?.model);
}

export { ILLUSTRATIONS, MODEL_PHOTOS };
