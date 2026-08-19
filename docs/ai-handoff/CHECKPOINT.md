# Panahi Car — Quick Checkpoint

Date: 2026-08-19

Project: **Panahi Car** — car dealership management software

Path: `/Users/macbook/Desktop/automaya`

GitHub: `https://github.com/mhfarhadi/PanahiCar` (private, default branch `main`)

Branch: `main`

## Stack

- Laravel 13, PHP 8.4, Vue 3, Inertia, Tailwind, MySQL
- Persian RTL UI (Vazirmatn)
- Light + dark showroom theme (`panahi_theme`, legacy `automaya_theme` still read)

## Domain

Dealership operations:

- **Inventory (`in_stock`)** — vehicles purchased into showroom stock
- **Announced (`announced`)** — consignment / seller-held cars
- **Sales** — cash or installment
- **Installments** — receivables tracking
- **Contacts** — sellers, buyers, colleagues
- **Organization** — branches (locations), users, role-based access (super admin only)
- **Features** — public tools: installment calculator, sale contract, inventory-based price estimate (no Divar), gold collateral, wanted cars, wanted market, check printer

### Vehicle fields

| Field | Required |
|-------|----------|
| brand, model, model_year | yes |
| mileage (km) | **yes** |
| color, transmission, fuel_type, body_condition | yes |
| insurance_months | optional |
| vin | optional |

Body condition options follow Iranian market norms (بی‌رنگ، یک/دو لکه، صافکاری بدون رنگ، دور رنگ، کامل رنگ, …).

### Organization / access

| Role | Persian label | Scope |
|------|---------------|-------|
| `super_admin` | مدیر کل (Maya) | all branches, manage users & locations |
| `manager` | مدیر شعبه | full ops in assigned branch |
| `sales` | کارشناس فروش | sales + contacts in branch |
| `inventory` | مسئول موجودی | inventory in branch |
| `accountant` | حسابداری | installments + read sales |
| `viewer` | مشاهده‌گر | read-only in branch |

Page: `/organization` (Settings → سازمان). Inventory is scoped by `location_id` on devices.

Brands (Iranian market): ایران‌خودرو، سایپا، مدیران خودرو، کرمان موتور، بهمن موتور، پارس‌خودرو، زامیاد

Demo data: `AutomayaDemoSeeder` — 20 in-stock, 20 announced, 20 cash sales, 20 installment sales, 20 wanted requests, 28 contacts. Prices aligned with Mordad 1405 Bama/market listings. Vehicle photos are model-specific (Wikimedia + studio shots). UI tiles use 3D isometric illustrations, not street photos.

## UI

- Soft mint-to-sky gradient canvas, white floating cards (32px radius), colored pill tabs (green / yellow / lavender / coral)
- Invoice-style dashboard: vertical stat pills + striped balance card + icon sub-nav
- Brand: **پناهی** (FA) / **Panahi Car** (EN)
- Compact asset-style list rows; real car photos only on inventory/sale vehicles
- Dashboard title: پناهی — photo hero of in-stock cars + metric cards + Features illustration tiles
- Page transitions: soft fade/slide/blur (`am-soft-enter`), card lift on hover
- Features tiles and empty states use 3D isometric illustrations in `public/images/illustrations/`
- Sale-day USD is not collected or shown; purchase USD snapshots remain
- Gold collateral (Features) uses a manual gram price each time; no live API rate
- Features mobile: bottom bar shows سایت + اقساط/برآورد/طلا + بیشتر (قرارداد، می‌خوام، می‌خوان، چک)
- Main app mobile tab bar includes امکانات directly; اعلامی moved under بیشتر
- Features numbers use Persian digits in inputs and results; selects inherit Vazirmatn globally
- USD/AED rates stay tiny next to the date on the dashboard
- Desktop icon rail + floating mobile tab bar (mobile-only; sidebar from `md`)
- Post-login blank page fixed (removed global `.am-page` enter animation; faster login splash on repeat visits)
- Organization link lives under Settings → سازمان (not in main nav)
- RTL login splash: gradient background, PANAHI CAR letters fade in, then login card appears from blur
- Public Features hub at `/features` (installments, contract, inventory-based price estimate, gold collateral, wanted cars, wanted market, check printer)
- PWA-ready: `viewport-fit=cover`, `public/manifest.json`

## Commands

```bash
php artisan migrate --seed
php artisan test
npm run build
php artisan serve --port=8001
```

Local dev: `APP_URL=http://127.0.0.1:8001`, MySQL database **`panahi_car`** (separate from MayaHamrah `maya_hamrah`). Default super admin after clone/seed: `maya@mhfarhadi.com`.

## Key files

- Vehicle options: `app/Support/VehicleOptions.php`
- Device CRUD: `app/Http/Controllers/DeviceController.php`
- Labels: `resources/js/Utils/vehicleLabels.js`
- Car photos: `resources/js/Utils/carPhotos.js`, `app/Support/ShowroomPhotos.php`
- Features layout: `resources/js/Layouts/FeaturesLayout.vue`
- Price estimate: `app/Services/VehiclePriceEstimateService.php` (inventory/sales only, no Divar)
- Layout: `resources/js/Layouts/AuthenticatedLayout.vue`
- Organization: `app/Http/Controllers/OrganizationController.php`, `resources/js/Pages/Organization/Index.vue`

See `AGENTS.md` for agent rules.
