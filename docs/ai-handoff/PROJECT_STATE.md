# MayaHamrah — Current Project State

Last updated: 2026-08-12

## Current stable checkpoint

Commit:

`8837137 — MayaHamrah: add sales filters and summary`

در زمان ثبت این سند، آخرین checkpoint تست‌شده همین commit است.

## وضعیت کلی

پروژه فعال و قابل build است.

Stack:
- Laravel 13
- PHP 8.4
- MySQL 8.4
- Inertia
- Vue 3
- Vite
- Tailwind CSS v4
- Breeze
- Pest
- Node 24
- npm
- `morilog/jalali`
- `jalaali-js`
- `vue3-persian-datetime-picker`

محیط اصلی توسعه:
- macOS Apple Silicon
- مسیر پروژه: `~/Desktop/maya-hamrah`

## اصول UI

- فارسی
- RTL
- mobile-first
- ظاهر PWA-like
- Vazirmatn
- Light / Dark / System
- theme در localStorage با key:
  `maya_theme`
- تاریخ‌های قابل مشاهده برای کاربر: شمسی
- تاریخ DB: Gregorian

فرمت موردنظر تاریخ داشبورد:
`دوشنبه | ۱۹ مرداد ۱۴۰۵`

## Domain

### Contact

جدول واحد اشخاص.

`contact_type`:
- `colleague`
- `individual`

معنی:
- colleague می‌تواند اعلام‌کننده، فروشنده یا خریدار باشد.
- individual می‌تواند فروشنده یا خریدار باشد.
- فقط colleague اجازه اعلام گوشی دارد.
- نقش seller / buyer / announcer تراکنشی است و از سابقه استخراج می‌شود.

فیلد `archived_at` وجود دارد.

### Device

وضعیت‌ها:
- `in_stock` = واقعاً توسط فروشگاه خریداری شده و موجود است.
- `announced` = نزد همکار/مالک است و فقط به فروشگاه اعلام شده.
- `sold` = فروخته شده.

فیلدهای مهم:
- brand
- model
- storage
- color
- part_number
- manufacturing_country
- sim_type
- battery_health
- battery_condition
- condition_grade
- imei
- registration_status
- status
- announced_by_id
- announced_price
- announced_at
- description
- created_by

### Purchase

- device_id
- seller_id
- purchase_price
- purchase_date
- notes
- created_by

خرید گوشی اعلامی:
همان Device حفظ می‌شود و status از announced به in_stock تغییر می‌کند.

### Sale

- device_id
- buyer_id
- sale_type
- sale_price
- down_payment
- sale_date
- notes
- created_by
- monthly_profit_rate
- installment_profit
- contract_total
- deferment finance fields

فروش فقط برای Device با status = in_stock مجاز است.
بعد از فروش status دستگاه = sold.

### Installment

هر installment در مدل فعلی نماینده یک چک مشتری است.

فیلدهای مهم:
- sale_id
- installment_number
- due_date
- amount
- paid_amount
- status
- paid_at
- notes

status:
- pending
- paid

وصول:
در markPaid، اگر قبلاً paid نباشد:
- paid_amount = amount
- status = paid
- paid_at = تاریخ انتخاب‌شده

تاریخ آینده برای paid_at عمداً مجاز شده است.

## منطق اقساط

نرخ پیش‌فرض ماهانه:
`6.5%`

سررسید استاندارد اولین قسط:
یک ماه شمسی بعد از تاریخ فروش.

اگر اولین سررسید دیرتر انتخاب شود:
سود دوره تنفس به قرارداد اقساطی اضافه می‌شود.

محاسبه:
- تعداد ماه کامل شمسی
- روزهای باقی‌مانده / 30
- سود deferment روی اصل بدهی
- به اقساط اضافه می‌شود، نه بخش نقدی

Commit مربوط:
`b450772`

## Append-only notes

یادداشت‌ها append-only هستند.

ساختار:
- entity_notes
- EntityNoteService
- EntityNoteController
- EntityNoteHistory.vue

یادداشت قبلی overwrite نمی‌شود.

Commit:
`0528097`

## Live Search

جستجوی زنده با debounce حدود 300ms در:
- Contacts
- Inventory
- Announced devices
- Sales

جستجو روی مواردی مثل:
- brand
- model
- storage
- color
- IMEI
- part number
- نام شخص
- موبایل

ارقام فارسی/عربی/لاتین normalize می‌شوند.

Commit:
`2455a85`

## Roles / Permissions

Users:
- role
- is_active

نقش‌ها:
- super_admin
- manager
- staff

کوچک‌ترین user id هنگام migration به super_admin تبدیل شد.
User id 1 قبلاً verify شده که super_admin و active است.

قواعد contact management:
- super_admin: همه
- manager: فقط individual
- staff: مدیریت existing contacts ندارد

Contact دارای history:
hard delete نمی‌شود و archive می‌شود.

Contact بدون history:
قابل hard delete است.

Public registration routes حذف شده‌اند.
inactive user هنگام login مسدود می‌شود.

UI مدیریت کاربران داخلی هنوز ساخته نشده.

Commit:
`d758e27`

## Device catalog / labels

Commit:
`0a10cc4`

فایل مرکزی:
`resources/js/Utils/deviceLabels.js`

Labelهای مرکزی:
- simTypeLabel
- conditionLabel
- registrationStatusLabel
- deviceStatusLabel
- colorLabel
- batteryConditionLabel
- manufacturingCountryLabel

### Apple catalog

مدل‌های ثبت‌شده شامل:
- iPhone 13
- 13 Pro
- 13 Pro Max
- 14
- 14 Pro
- 14 Pro Max
- 15
- 15 Pro
- 15 Pro Max
- 16
- 16 Pro
- 16 Pro Max

Part number suffix options:
- ZA/A
- LL/A
- CH/A
- ZP/A
- J/A
- AE/A

### Samsung

برای Samsung از battery health percentage استفاده نمی‌شود.

battery_condition:
- excellent = عالی
- good = خوب
- poor = ضعیف
- replace = نیاز به تعویض

به‌جای part number:
manufacturing_country

گزینه‌های فعلی:
- vietnam
- india
- china
- south_korea
- indonesia

برای Samsung:
- part_number = null
- battery_health = null

برای غیر Samsung:
- manufacturing_country = null
- battery_condition = null

## Contacts profile

Contact profile شامل:
- avatar
- نوع شخص
- آمار
- گوشی‌های فروخته‌شده به فروشگاه
- گوشی‌های خریداری‌شده از فروشگاه
- گوشی‌های اعلامی
- payment-history tag
- append-only notes

## Dashboard — Current

Dashboard اکنون واقعی است، نه placeholder.

نمایش:
- inventoryCount
- فروش ماه جاری شمسی
- کل مطالبات باز
- تعداد چک‌های باز
- مبلغ معوق
- مبلغ 7 روز آینده
- نزدیک‌ترین سررسیدها

تعریف overdue:
pending و `due_date < today`

تعریف due soon:
pending از امروز تا 7 روز آینده.

در تست 2026-08-12:
- فروش ماه شمسی جاری = 8
- open checks = 21
- open receivables = 445,028,570 تومان
- overdue = 0
- due next 7 days = 0

ممکن است این اعداد با داده‌های DB بعداً تغییر کنند.

ماه فروش بر اساس Jalali محاسبه می‌شود، نه Gregorian.

## Global Navigation

از commit `9cae59f`:

AuthenticatedLayout بازطراحی شده.

Desktop:
- top navigation

Mobile:
- hamburger
- right-side drawer
- overlay

منوی اصلی شامل:
- داشبورد
- موجودی
- ثبت دستگاه
- گوشی‌های اعلامی
- فروش گوشی
- فروش‌ها
- اشخاص
- تنظیمات

پروفایل و خروج نیز حفظ شده‌اند.

## Sell flow

Route ثبت فروش:
`devices/{device}/sell`

فرم:
`resources/js/Pages/Sales/Create.vue`

فرم مشترک:
- فروش نقدی
- فروش اقساطی

Flow جدید:

Dashboard / global menu
→ فروش گوشی
→ inventory با `?mode=sell`
→ انتخاب گوشی
→ `sales.create`
→ فرم فروش نقدی یا اقساطی

در sell mode، کارت دستگاه مستقیم به sales.create می‌رود.

## Check clearance

Commit:
`9080a3a`

Sales/Show دارای جریان وصول چک و Persian date picker است.

روزهای تأخیر نمایش داده می‌شوند.

## Currency

Dashboard نرخ ارز را از Navasan می‌گیرد.

Cache keys:
- navasan:last_rates
- navasan:dashboard_rates

تنظیمات API در config/services قرار دارد.

هیچ API key نباید داخل این اسناد یا Git commit شود.

## Current important known caveats

1. `DeviceShow` فعلاً برای دستگاه sold ممکن است محدودیت داشته باشد و برای history کامل باید اصلاح شود.
2. UI مدیریت user/role هنوز ساخته نشده.
3. middleware برای user که بعد از login غیرفعال می‌شود هنوز کامل نشده.
4. Apple model sort order ممکن است نیاز به cleanup داشته باشد.
5. Samsung manufacturing countries فعلاً generic هستند، نه model-specific.
6. برگشت اشتباه یک چک از paid به pending هنوز ساخته نشده.
7. metadata کامل چک هنوز اضافه نشده.


## Sales filters

صفحه `Sales/Index` اکنون فیلترهای زیر را دارد:

نوع فروش:
- همه
- نقدی
- اقساطی

بازه زمانی:
- همه زمان‌ها
- ۷ روز گذشته
- ماه جاری شمسی
- ماه گذشته شمسی

این فیلترها با جستجوی موجود ترکیب می‌شوند.

آمار بالای صفحه نیز بر اساس نتایج فیلترشده تغییر می‌کند:
- تعداد فروش
- مجموع مبلغ فروش

این قابلیت به‌صورت دستی تست شده است.

Commit:
`8837137 — MayaHamrah: add sales filters and summary`

