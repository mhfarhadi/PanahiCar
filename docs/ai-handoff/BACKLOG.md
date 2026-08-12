# MayaHamrah — Backlog

ترتیب این لیست الزاماً priority نهایی نیست، اما آیتم‌های شناخته‌شده آینده هستند.

## High priority

### Undo check clearance
امکان برگرداندن چکی که اشتباهی paid شده به pending.

نیاز به safeguard و audit مناسب دارد.

### Check metadata
اضافه کردن اطلاعات واقعی چک:
- شماره چک
- بانک
- شناسه صیاد
- تصویر روی چک
- تصویر پشت چک / مدارک در صورت نیاز

### Sales finance reporting
در Sales/Index سود مالی فروش اقساطی باید دقیق باشد.

در برخی بخش‌ها ممکن است profit ساده:
`sale_price - purchase_price`
باشد و finance profit کامل را منعکس نکند.

### Sold device historical view
DeviceShow برای sold باید historical-safe شود.

نباید مشاهده سوابق دستگاه فروخته‌شده صرفاً به دلیل status مسدود شود.

## Medium priority

### Sale cancellation / reversal
لغو فروش و برگشت امن state با محافظت مالی/انبار.

### User management UI
super_admin بتواند کاربران داخلی را:
- ایجاد
- role assign
- active/inactive
کند.

### Active-user middleware
اگر user بعد از login غیرفعال شد، session فعال نیز باید کنترل شود.

### Apple catalog sort cleanup
مرتب‌سازی مدل‌های Apple بررسی شود.

### Samsung model-specific manufacturing
در صورت نیاز کشورهای سازنده به مدل خاص محدود شوند.

## Data cleanup

یک sale تستی قدیمی با id 5 به‌عنوان داده نامعتبر شناخته شده است.

بدون تأیید مستقیم کاربر حذف نشود.

## Possible UX enhancement

Dashboard due labels:
- امروز
- فردا
- پس‌فردا
- معوق X روز
- X روز تا سررسید

