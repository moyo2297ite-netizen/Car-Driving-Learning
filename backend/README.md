# مدرسة تعليم القيادة — Backend API

Laravel API (بدون Blade views) لمشروع تطبيق مدرسة تعليم قيادة. التفاصيل الكاملة للمواصفات موجودة في `driving-school-app-spec (1).md`.

## المكدس التقني

- Laravel 12 + PHP 8.2
- المصادقة: Laravel Sanctum (Bearer tokens — مناسب لتطبيق React ويب و Flutter موبايل)
- قاعدة بيانات: SQLite افتراضياً (قابلة للتغيير عبر `.env`)

## التشغيل محلياً

```bash
composer install
cp .env.example .env   # إذا لم يكن موجوداً
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

بعد التشغيل، الـ API متاح على `http://127.0.0.1:8000/api`.

### حسابات تجريبية (من `DemoUsersSeeder`)

تسجيل الدخول برقم الهاتف (`phone`) وليس بالبريد:

| الدور | رقم الهاتف | كلمة المرور |
|---|---|---|
| Admin | 0999000001 | password |
| Supervisor | 0999000002 | password |
| Instructor | 0999000003 | password |
| Student | 0999000004 | password |

## توثيق الـ API

مجموعة Postman كاملة (61 طلب تغطي كل الـ 58 مسار) موجودة في [`docs/postman_collection.json`](docs/postman_collection.json):

1. استورد الملف في Postman.
2. نفّذ طلبات `1. Auth > Login (...)` لكل دور — يحفظ التوكن تلقائياً في متغيرات المجموعة.
3. باقي الطلبات تستخدم التوكن المناسب تلقائياً.

لعرض كل المسارات المسجّلة مباشرة من الكود:

```bash
php artisan route:list --path=api
```

## البنية

- `app/Enums` — الحالات الصريحة (booking status, slot status, roles...)
- `app/Models` — Eloquent models والعلاقات
- `app/Services` — منطق العمل الأساسي (قواعد الحجز، الإلغاء، التقدم، إلخ) — مستقل عن HTTP
- `app/Http/Controllers/Api` — طبقة رفيعة تربط الطلبات بالـ Services
- `app/Http/Requests` — التحقق من صحة المدخلات
- `app/Http/Resources` — تنسيق استجابات JSON
- `app/Notifications` — إشعارات (تأكيد حجز، إلغاء، تذكير، اعتماد/رفض وقت، تأكيد دفع) عبر قناتي database + mail
- `app/Http/Middleware/EnsureUserHasRole` — صلاحيات الوصول حسب الدور (`role:admin,supervisor`)

## الاختبارات

```bash
php vendor/bin/phpunit
```

46 اختبار Feature تغطي: التسجيل/الدخول، دورة الحجز الكاملة، تطابق نوع الغيار، حساب غرامة الإلغاء، قفل المحتوى المتسلسل، إخفاء إجابات الكويز عن الطلاب، إدارة الطاقم، المهارات وشهادة الإتمام، الرسائل، الإعدادات، ولوحات التحكم الثلاث.

## تنسيق الكود

```bash
php vendor/bin/pint
```

## التذكير التلقائي بالحجوزات

أمر مجدول (`bookings:send-reminders`) يرسل تذكيراً لكل طالب لديه حجز مؤكد خلال 24 ساعة القادمة. مسجّل في `routes/console.php` ليعمل كل ساعة عند تشغيل `php artisan schedule:work` (أو عبر cron في الإنتاج).
