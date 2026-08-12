# MayaHamrah — Disaster Recovery Guide

این راهنما برای زمانی است که:

- به ChatGPT یا اکانت قبلی دسترسی نداریم.
- مک قبلی خراب یا گم شده است.
- پروژه باید روی سیستم جدید ادامه پیدا کند.

## چیزهایی که باید در اختیار داشته باشیم

1. دسترسی به GitHub repository خصوصی:
   `mhfarhadi/maya-hamrah`

2. فایل Recovery Bundle رمزگذاری‌شده با پسوند:
   `.tar.gz.enc`

3. رمز Recovery Bundle که باید جدا از فایل بکاپ نگهداری شود.

Recovery Bundle شامل:

- `.env`
- backup دیتابیس MySQL
- `storage/app/public`

## 1. دریافت کد پروژه

روی سیستم جدید:

```bash
git clone https://github.com/mhfarhadi/maya-hamrah.git
cd maya-hamrah
cat docs/ai-handoff/START_HERE.md
git log --oneline -20
git status
```

## 2. باز کردن Recovery Bundle

روی سیستم جدید:

```bash
mkdir -p ~/maya-hamrah-recovery
openssl enc -d -aes-256-cbc -pbkdf2   -in /PATH/TO/maya-hamrah-recovery-YYYY-MM-DD.tar.gz.enc | tar -xzf - -C ~/maya-hamrah-recovery
```

بعد از وارد کردن رمز، باید این موارد استخراج شوند:

- `.env`
- بکاپ دیتابیس MySQL
- بکاپ `storage/app/public`

## 3. بازیابی فایل .env

از پوشه recovery:

```bash
cp ~/maya-hamrah-recovery/.env .env
```

برای اطمینان از اینکه `.env` وارد Git نمی‌شود:

```bash
git check-ignore -v .env
```


## 4. نصب وابستگی‌ها

برای PHP:

```bash
composer install
```

برای فرانت‌اند:

```bash
npm install
```


## 5. بازیابی دیتابیس

MySQL باید روی سیستم نصب و فعال باشد.

نام دیتابیس، نام کاربری و رمز را از فایل `.env` بررسی کن.

سپس بکاپ SQL را import کن:

```bash
mysql -u USERNAME -p DATABASE_NAME < /PATH/TO/maya-hamrah_db_YYYY-MM-DD_HH-MM-SS.sql
```


## 6. بازیابی فایل‌های storage

بکاپ storage را داخل ریشه پروژه استخراج کن:

```bash
tar -xzf /PATH/TO/maya-hamrah_storage_YYYY-MM-DD_HH-MM-SS.tar.gz
```

بعد symlink فایل‌های عمومی را بساز:

```bash
php artisan storage:link
```


## 7. بررسی Laravel

کش‌های Laravel را پاک کن:

```bash
php artisan optimize:clear
```

بعد وضعیت migrationها را بررسی کن:

```bash
php artisan migrate:status
```

در زمان بازیابی دیتابیس موجود، بدون بررسی وضعیت migrationها دستور destructive اجرا نکن.


## 8. Build فرانت‌اند

برای ساخت فایل‌های production:

```bash
npm run build
```


## 9. اجرای پروژه

برای اجرای محلی:

```bash
php artisan serve
```

بعد پروژه را در مرورگر باز کن و مطمئن شو صفحه login و dashboard بالا می‌آیند.


## 10. بررسی نهایی

بعد از بالا آمدن پروژه این بخش‌ها را تست کن:

- ورود به حساب
- داشبورد
- موجودی
- اشخاص
- گوشی‌های اعلامی
- فروش‌ها
- چک‌های اقساط
- تصاویر
- حالت روشن و تاریک
- تاریخ‌های شمسی

در آخر وضعیت Git را بررسی کن:

```bash
git status
```


## 11. ادامه پروژه با ChatGPT یا AI جدید

اگر به اکانت یا چت قبلی دسترسی نداشتی، فایل‌های پوشه زیر را به AI جدید بده:

`docs/ai-handoff/`

و این متن را برایش بفرست:

«هیچ حافظه‌ای از گفتگوهای قبلی فرض نکن.
ابتدا تمام فایل‌های `docs/ai-handoff/` را کامل بخوان.
بعد `git log --oneline -20` و `git status` را بررسی کن.
از آخرین checkpoint امن ادامه بده.
در ترمینال فقط یک دستور در هر مرحله بده و منتظر خروجی من بمان.
بدون بررسی فایل واقعی patch حدسی نده.»


## 12. قانون بکاپ پروژه

بعد از checkpointهای مهم:

1. تغییرات را commit کن.
2. روی GitHub private push کن.
3. از دیتابیس MySQL بکاپ بگیر.
4. از `storage/app/public` بکاپ بگیر.
5. Recovery Bundle رمزگذاری‌شده جدید بساز.
6. باز شدن فایل `.enc` را تست کن.
7. یک نسخه از فایل `.enc` را خارج از مک نگهداری کن.

رمز Recovery Bundle را در همان مک یا همان هاردی که بکاپ روی آن است، به‌تنهایی نگهداری نکن.

