# MayaHamrah — Product & Technical Decisions

این فایل تصمیمات قطعی یا نیمه‌قطعی پروژه را ثبت می‌کند تا در session جدید دوباره از صفر بحث نشوند.

## 1. تعریف موجودی

`in_stock` یعنی دستگاه واقعاً خریداری شده و مالکیت آن با فروشگاه است.

`announced` موجودی فروشگاه نیست.

## 2. Contact واحد

Seller / buyer / announcer جدول‌های جدا نیستند.

Contact یک identity واحد است و نقش‌ها از transaction history استخراج می‌شوند.

## 3. Contact types

- colleague
- individual

فقط colleague گوشی اعلام می‌کند.

## 4. Dates

DB:
Gregorian

UI:
Jalali / Persian

محاسبات ماهانه کسب‌وکار، وقتی مفهوم «این ماه» برای کاربر دارند، باید با ماه شمسی هماهنگ باشند.

## 5. Persian numeric input

Mobile / IMEI / battery / price و ورودی‌های عددی مرتبط باید ارقام فارسی، عربی و لاتین را بپذیرند و قبل از validation/storage normalize شوند.

## 6. Prices

قیمت‌ها در UI:
- grouping سه‌رقمی
- نمایش حروفی فارسی در فرم‌های مرتبط
- واحد تومان

## 7. Samsung battery

Samsung را مانند iPhone با battery health percentage مدل نکن.

از qualitative condition استفاده شود.

## 8. Samsung manufacturing

برای Samsung به‌جای Apple-style part number، manufacturing country نمایش داده شود.

## 9. Notes

Notes تاریخچه‌ای هستند.

ویرایش یادداشت قدیمی ممنوع.
یادداشت جدید append شود.

## 10. Contact deletion

اگر history دارد:
archive

اگر history ندارد:
hard delete مجاز طبق permission.

## 11. Installment due dates

منطق ماه بر اساس Jalali باشد.

First due date قابل عقب انداختن است و deferment profit باید در محاسبه finance لحاظ شود.

## 12. Paid check

در مدل فعلی installment همان customer check است.

Paid date در آینده عمداً مجاز است چون business flow ممکن است نیاز داشته باشد.

## 13. Navigation

ناوبری باید global باشد، نه فقط dashboard.

Desktop top nav.
Mobile side drawer.

## 14. Sell flow

فروش بدون device انتخاب‌شده آغاز نمی‌شود.

Shortcut فروش:
انتخاب دستگاه موجود → فرم فروش.

## 15. UI

- RTL
- Persian
- mobile-first
- PWA-like
- Light/Dark/System
- Vazirmatn

## 16. Coding workflow

از patchهای شکننده و whitespace-sensitive دوری شود.

قبل از تغییر:
فایل واقعی inspect شود.

بعد از تغییر:
build/test/diff-check.

## 17. Destructive data actions

هیچ داده تستی یا production-like بدون اجازه مستقیم کاربر حذف نشود.

نمونه:
old invalid sale id 5 قبلاً شناخته شده ولی نباید خودسرانه حذف شود.

