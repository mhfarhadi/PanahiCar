import { publicAsset } from '@/Utils/publicAsset';

const ILLUSTRATIONS = {
    installments: publicAsset('/images/illustrations/illustration-installments.png'),
    contract: publicAsset('/images/illustrations/illustration-contract.png'),
    estimate: publicAsset('/images/illustrations/illustration-estimate.png'),
    gold: publicAsset('/images/illustrations/illustration-gold.png'),
    wanted: publicAsset('/images/illustrations/illustration-wanted.png'),
    market: publicAsset('/images/illustrations/illustration-market.png'),
    check: publicAsset('/images/illustrations/illustration-check.png'),
    showroom: publicAsset('/images/illustrations/illustration-showroom.png'),
};

const MODEL_PHOTOS = {
    'دنا پلاس': publicAsset('/images/vehicles/dena-plus.jpg'),
    تارا: publicAsset('/images/vehicles/tara.jpg'),
    شاهین: publicAsset('/images/vehicles/shahin.jpg'),
    اطلس: publicAsset('/images/vehicles/atlas.jpg'),
    'تیگو 7 پرو': publicAsset('/images/vehicles/tiggo-7.jpg'),
    'آریزو 6': publicAsset('/images/vehicles/arizo.jpg'),
    'آریزو 5': publicAsset('/images/vehicles/arizo.jpg'),
    فیدلیتی: publicAsset('/images/vehicles/fidelity.jpg'),
    دیگنیتی: publicAsset('/images/vehicles/dignity.jpg'),
    'KMC J7': publicAsset('/images/vehicles/kmc-j7.jpg'),
    'تندر 90': publicAsset('/images/vehicles/tondar.jpg'),
    'پژو 207': publicAsset('/images/vehicles/peugeot-207.jpg'),
    'کوییک S': publicAsset('/images/vehicles/quick.jpg'),
    'ساینا S': publicAsset('/images/vehicles/saina.jpg'),
    'ری‌را': publicAsset('/images/vehicles/rira.jpg'),
    X22: publicAsset('/images/vehicles/x22.jpg'),
    'JAC J4': publicAsset('/images/vehicles/j4.jpg'),
    'پژو پارس': publicAsset('/images/vehicles/pars.jpg'),
    'سمند EF7': publicAsset('/images/vehicles/samand.jpg'),
    'زامیاد Z24': publicAsset('/images/vehicles/zamyad.jpg'),
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

export { ILLUSTRATIONS, MODEL_PHOTOS };
