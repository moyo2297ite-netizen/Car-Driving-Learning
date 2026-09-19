# توثيق الـ API — مشروع "سويق" (مدرسة تعليم قيادة)

مرجع تقني كامل لكل نقاط الـ API (58 مسار) مبني من الكود الفعلي للباك اند (Laravel 12 + Sanctum). كل ما هو مكتوب هنا تم التحقق منه بالتشغيل الفعلي، وليس تخمينًا.
اقرأ `PROJECT-OVERVIEW.md` أولًا لفهم المشروع والأدوار وقواعد العمل.

---

## 1. الأساسيات

| البند | القيمة |
|---|---|
| Base URL (محليًا) | `http://127.0.0.1:8000/api` |
| Android Emulator | `http://10.0.2.2:8000/api` (لأن `127.0.0.1` داخل المحاكي يعني المحاكي نفسه) |
| جهاز حقيقي | `http://<IP-الكمبيوتر-على-الشبكة>:8000/api` (شغّل السيرفر بـ `php artisan serve --host=0.0.0.0`) |
| الصيغة | JSON فقط (طلبات وردود) |
| المصادقة | Bearer token (Laravel Sanctum) |
| CORS | غير مهم للموبايل (يهمّ المتصفحات فقط) |

### Headers مطلوبة دائمًا
```
Accept: application/json          ← إلزامي، بدونه قد يرجع الباك اند HTML بدل JSON عند الأخطاء
Content-Type: application/json    ← مع أي طلب فيه body
Authorization: Bearer <token>     ← لكل المسارات ما عدا /register و /login
```

### المصادقة
- التسجيل والدخول يرجّعان `token` (نص مثل `12|AbCd...`). خزّنه بمكان آمن (`flutter_secure_storage`).
- **التوكن لا ينتهي صلاحيته تلقائيًا** (لا يوجد expiration مضبوط). يُلغى فقط عبر `POST /logout` (يلغي التوكن الحالي) أو حذف المستخدم.
- الدخول **برقم الهاتف** (`phone`) وليس البريد. تطابق الرقم **نصي حرفي** (`0999000004` ≠ `999000004`)، فوحّد صيغة الرقم عند العميل (أرقام فقط، بدون مسافات).
- توكن بدور معيّن يصل فقط لمسارات ذلك الدور (انظر عمود "الأدوار" في كل مسار). الوصول لمسار غير مسموح → `403`.

### شكل الردود (مهم جدًا — قاعدتان)

**القاعدة 1 — الغلاف `data`:** المسارات التي ترجع Resource مباشرة تلفّ النتيجة بـ `{"data": ...}`، والتي ترجع JSON مكتوبًا يدويًا ترجعه **بدون** غلاف. لذلك في الأقسام أدناه لكل مسار خانة "الرد" تبيّن الشكل بالضبط. الاستثناءات التي **بدون** غلاف `data`:
`/login`، `/register`، `/logout`، لوحات `/dashboard/*`، `/bookings/pending`، `/settings`، `/payments/sham-cash-link`، `/notifications`، `/contents/{id}/view`، `/quizzes/{id}/submit`، `/students/{id}/certificate-eligibility`، رسائل الحذف `{message}`.

**القاعدة 2 — مفاتيح اختيارية:** العلاقات المتداخلة (مثل `student` و`slot` و`instructor` و`payments` و`assigned_by`) **تظهر فقط إذا حمّلها الباك اند لذلك المسار**، وإلا يغيب المفتاح كليًا (ليس `null`). لذلك في Dart اجعل كل الحقول المتداخلة nullable (`UserModel? student`). أمثلة: `GET /bookings/mine` لا يحتوي `student`؛ ردّ `complete` يحتوي حقول الحجز الأساسية فقط بلا علاقات، وردّ `cancel` قد يحتوي `student` فقط (أثر جانبي) بلا `slot` → في الحالتين أعد جلب القائمة بعد العملية بدل الاعتماد على الرد.

### الأخطاء

| الكود | المعنى | الشكل |
|---|---|---|
| `401` | توكن مفقود/غير صالح | `{"message": "Unauthenticated."}` |
| `403` | الدور غير مسموح (أو محتوى مقفول للطالب) | `{"message": "ليس لديك صلاحية للوصول لهذا المورد."}` |
| `404` | العنصر غير موجود | `{"message": "No query results for model [...] 9999"}` (إنجليزي؛ في وضع التطوير يضاف `exception/file/trace`) |
| `422` (تحقق) | مدخلات ناقصة/خاطئة | `{"message": "The phone field is required. (and 2 more errors)", "errors": {"phone": ["The phone field is required."]}}` |
| `422` (قاعدة عمل) | مخالفة قاعدة عمل | `{"message": "رسالة عربية جاهزة للعرض"}` (بدون `errors`) |
| `500` | خطأ سيرفر | `{"message": "..."}` |

**اللغة:** رسائل قواعد العمل (`422` بدون `errors`) والصلاحيات (`403`) وخطأ الدخول **بالعربية** وجاهزة للعرض. أما رسائل **التحقق** (`errors` لكل حقل) و`401` و`404` فتأتي **بالإنجليزية** (الافتراضي في Laravel)؛ الأفضل أن يعرض التطبيق رسائل عربية محلية بحسب اسم الحقل (`errors.<field>`) بدل عرض النص الإنجليزي. خطأ الدخول الخاطئ: `422` مع `errors.phone = ["بيانات الدخول غير صحيحة."]` (عربي).

### أنواع البيانات
- **التواريخ**: ISO 8601 بتوقيت UTC، مثل `2026-09-18T09:00:00.000000Z`. عند الإرسال أرسل ISO مع المنطقة الزمنية. حوّل للتوقيت المحلي للعرض. ملاحظة: "اليوم" في `today_bookings` محسوب بتوقيت السيرفر (UTC).
- **الأموال**: `price` و`amount` و`penalty_amount` في الحجوزات/الدفعات تصل **نصًا** (`"15.00"`)، أما `lesson_price` في الإعدادات فتصل رقمًا (`15`). حوّل بحذر (`double.parse`).
- **لا يوجد pagination**: كل القوائم تُرجع كاملة (الإشعارات فقط محدودة بآخر 30).
- **لا يوجد realtime**: لا WebSocket ولا Push. المحادثة والإشعارات تعتمد على polling (أعد الطلب دوريًا أو عند فتح الشاشة).

---

## 2. القيم المعرّفة (Enums)

| الاسم | القيم (نصوص بحروف صغيرة) |
|---|---|
| `role` | `admin`, `supervisor`, `instructor`, `student` |
| `transmission_type` | `manual` (عادي), `automatic` (أوتوماتيك) |
| حالة وقت التوفر `AvailabilitySlot.status` | `proposed`, `approved`, `rejected` |
| حالة الحجز `Booking.status` | `pending`, `confirmed`, `completed`, `cancelled` |
| طريقة الدفع `Payment.method` | `cash`, `sham_cash` |
| حالة الدفع `Payment.status` | `pending`, `confirmed` |
| نوع المحتوى `Content.type` | `video`, `image` |

---

## 3. الكائنات (Models)

الحقول التي تظهر بشرط مذكورة بجانبها.

### User
```json
{
  "id": 4,
  "name": "الطالب",
  "email": null,                       // nullable (اختياري)
  "phone": "0999000004",               // nullable نظريًا، لكنه معرّف الدخول
  "role": "student",
  "transmission_type": "automatic",    // للطالب فقط؛ null لغيره
  "teaches_manual": true,              // يظهر للمدرب فقط
  "teaches_automatic": true,           // يظهر للمدرب فقط
  "created_at": "2026-09-16T09:48:05.000000Z"
}
```

### Skill
`{ "id": 1, "name": "ركن متوازي", "created_at": "..." }`

### AvailabilitySlot
```json
{
  "id": 1,
  "instructor": { /* User */ },        // إن حُمّل
  "start_time": "2026-09-18T09:00:00.000000Z",
  "end_time": "2026-09-18T10:00:00.000000Z",
  "status": "approved",
  "rejection_reason": null,            // نص السبب عند status=rejected
  "created_at": "..."
}
```

### Booking
```json
{
  "id": 1,
  "student": { /* User */ },           // إن حُمّل
  "slot": { /* AvailabilitySlot */ },  // إن حُمّل
  "assigned_by": { /* User */ },       // إن حُمّل (المشرف الذي عيّن المدرب)
  "price": "15.00",                    // لقطة سعر وقت الطلب (لا تتغير لاحقًا)
  "status": "confirmed",
  "cancelled_by": { /* User */ },      // إن حُمّل
  "penalty_amount": null,              // "7.50" إن أُلغي متأخرًا
  "payments": [ /* Payment */ ],       // فقط في GET /bookings/mine
  "created_at": "..."
}
```

### Payment
```json
{
  "id": 1,
  "booking": { /* Booking */ },        // إن حُمّل
  "method": "sham_cash",
  "amount": "15.00",
  "status": "pending",
  "confirmed_by": { /* User */ },      // إن حُمّل (بعد التأكيد)
  "created_at": "..."
}
```

### ContentCategory
```json
{
  "id": 1,
  "name": "دروس القيادة الأساسية",
  "is_sequential": true,               // true = تسلسل إجباري
  "contents": [ /* Content */ ],       // إن حُمّل
  "created_at": "..."
}
```

### Content
```json
{
  "id": 1,
  "category_id": 1,
  "uploader": { /* User */ },          // إن حُمّل (غالبًا غائب)
  "title": "التعرف على لوحة القيادة",
  "type": "video",
  "url": "https://example.com/dashboard-intro.mp4",
  "order_index": 1,
  "quiz": { "id": 1, "pass_score": 70 },   // null إن لا يوجد كويز؛ في القوائم بدون questions
  "created_at": "..."
}
```
في `GET /contents/{id}` يحتوي `quiz` أيضًا على `questions`.

### Quiz / QuizQuestion
```json
{
  "id": 1,
  "pass_score": 70,
  "questions": [
    {
      "id": 1,
      "question": "شو بيدل عليه ضوء البنزين الأحمر؟",
      "options": ["البنزين قارب يخلص", "المكيّف شغّال", "الأنوار مفتوحة"],
      "correct_answer": "..."          // يظهر لغير الطلاب فقط. الطالب لا يراه أبدًا.
    }
  ]
}
```

### StudentSkill (ربط طالب بمهارة)
```json
{
  "id": 1,                                   // id صف الربط — ليس id المهارة!
  "skill": { "id": 1, "name": "ركن متوازي", "created_at": "..." },
  "is_completed": true,
  "updated_by": { /* User */ },              // يظهر فقط في ردّ PUT
  "skill_updated_at": "2026-09-15T13:57:54.000000Z"   // null إن لم تُقيَّم بعد
}
```

### ContentProgress
```json
{
  "id": 1,
  "content": { /* Content مع quiz{id,pass_score} */ },
  "completed": true,
  "quiz_score": 100,                   // null إن لم يحلّ كويز
  "updated_at": "..."
}
```

### Message
```json
{
  "id": 1,
  "sender": { /* User */ },
  "receiver": { /* User */ },
  "body": "نص الرسالة",
  "read_at": null,
  "created_at": "..."
}
```

### Notification
```json
{
  "id": "21cf389a-52f2-4d71-a227-a8a52ea4cd11",   // UUID نصي (ليس رقمًا)
  "type": "payment_confirmed",
  "data": { "type": "payment_confirmed", "payment_id": 1, "booking_id": 1, "amount": "15.00" },
  "read_at": null,
  "created_at": "..."
}
```
أنواع الإشعارات وحقول `data`:

| `type` | يُرسل لـ | حقول `data` |
|---|---|---|
| `booking_confirmed` | الطالب | `booking_id, start_time, instructor_name` |
| `booking_cancelled` | الطالب | `booking_id, penalty_amount` (قد يكون null) |
| `booking_reminder` | الطالب | `booking_id, start_time` (قبل 24 ساعة من الدرس، أمر مجدول كل ساعة) |
| `slot_reviewed` | المدرب | `slot_id, status (approved/rejected), rejection_reason` |
| `payment_confirmed` | الطالب | `payment_id, booking_id, amount` |

---

## 4. المسارات

الرموز: **A**=admin، **S**=supervisor، **I**=instructor، **ST**=student، **All**=أي مستخدم مسجّل.

### 4.1 المصادقة

#### `POST /register` — بدون توكن (تسجيل طالب فقط)
Body:
```json
{ "name": "أحمد", "phone": "0999123456", "email": "a@b.com",
  "password": "password123", "password_confirmation": "password123",
  "transmission_type": "manual" }
```
- `name` مطلوب ≤255 · `phone` مطلوب فريد ≤255 · `email` اختياري فريد · `password` مطلوب ≥8 أحرف + `password_confirmation` مطابق · `transmission_type` مطلوب (`manual|automatic`).
- نوع الغيار يُحدَّد هنا مرة واحدة ويبقى ثابتًا (لا يوجد endpoint لتغييره).
- الرد `201`: `{ "user": User, "token": "..." }` (الطالب مسجّل دخوله مباشرة).

#### `POST /login` — بدون توكن
Body: `{ "phone": "0999000004", "password": "password", "device_name": "flutter" }` (`device_name` اختياري).
الرد `200`: `{ "user": User, "token": "..." }`. خطأ: `422` مع `errors.phone = ["بيانات الدخول غير صحيحة."]`.
يمكن لنفس المستخدم امتلاك عدة توكنات (جهاز لكل توكن).

#### `POST /logout` — All
يلغي التوكن الحالي. الرد: `{ "message": "تم تسجيل الخروج." }`.

#### `GET /me` — All
الرد: `{ "data": User }`. استخدمه عند فتح التطبيق للتحقق من صلاحية التوكن المحفوظ (`401` = سجّل خروج محليًا).

### 4.2 الإشعارات — All

#### `GET /notifications`
الرد (بدون `data`):
```json
{ "unread_count": 1, "notifications": [ Notification, ... ] }
```
آخر 30 إشعارًا، الأحدث أولًا.

#### `POST /notifications/{id}/read`
`{id}` = UUID الإشعار. الرد: `{ "message": "تم." }`.

### 4.3 إدارة الطاقم — A فقط

| المسار | الوصف |
|---|---|
| `GET /staff` | `{ "data": [User] }` — المشرفون والمدربون (الأحدث أولًا) |
| `POST /staff` | ينشئ مشرفًا أو مدربًا، الرد `201 { "data": User }` |
| `DELETE /staff/{userId}` | `{ "message": "تم الحذف." }` — `422` إن لم يكن مشرفًا/مدربًا |

Body إنشاء: `name`, `phone` (فريد), `email` (اختياري), `password` + `password_confirmation`, `role` (`supervisor|instructor`), `teaches_manual` (bool), `teaches_automatic` (bool).

### 4.4 أوقات توفر المدربين

| المسار | الأدوار | الوصف |
|---|---|---|
| `POST /availability-slots` | I | يقترح وقتًا (status=proposed) |
| `GET /availability-slots/mine` | I | كل أوقاتي (كل الحالات) → `{data:[Slot]}` |
| `GET /availability-slots/pending` | S,A | الأوقات المقترحة بانتظار الاعتماد (مع `instructor`) |
| `POST /availability-slots/{id}/approve` | S,A | يعتمد (`proposed` فقط) ويُشعر المدرب |
| `POST /availability-slots/{id}/reject` | S,A | Body: `{ "reason": "..." }` (مطلوب ≤500). يرفض (`proposed` فقط) ويُشعر المدرب |
| `GET /availability-slots/available` | All | الأوقات المتاحة للحجز (انظر أدناه) |

`POST /availability-slots` Body: `{ "start_time": "2026-09-20T14:00:00Z", "end_time": "2026-09-20T16:00:00Z" }` — `start_time` في المستقبل، `end_time` بعده. الرد `201 { "data": Slot }`.

`GET /availability-slots/available?transmission_type=manual|automatic` — يرجع أوقات: معتمدة + في المستقبل + **بدون حجز pending/confirmed عليها** + مدربها يدعم نوع الغيار المطلوب (أي قيمة غير `manual` تُعامل كـ automatic؛ إن حذفت البارامتر يرجع الكل). مرتبة بالأقدم. كل عنصر فيه `instructor`.

### 4.5 الحجوزات

| المسار | الأدوار | الوصف |
|---|---|---|
| `POST /bookings` | ST | الطالب يطلب حجزًا على وقت متاح |
| `GET /bookings/mine` | ST | حجوزاتي (الأحدث أولًا) مع `slot.instructor` و`payments` |
| `GET /bookings/pending` | S,A | لوحة المشرف (بدون `data`): `{ today_bookings, upcoming_bookings }` |
| `POST /bookings/{id}/assign` | S,A | تعيين مدرب/وقت لحجز pending |
| `GET /bookings/today` | I | دروسي اليوم (confirmed + completed) مع `student` و`slot` |
| `GET /instructor/students` | I | `{data:[User]}` طلابي (من لهم حجز confirmed/completed معي) |
| `POST /bookings/{id}/complete` | I,S,A | إنهاء الدرس |
| `POST /bookings/{id}/cancel` | ST,S,A | إلغاء الحجز |

- **`POST /bookings`** Body `{ "slot_id": 3 }` → `201 { "data": Booking }` (مع `student` و`slot.instructor`)، الحالة `pending` والسعر = `lesson_price` الحالي (لقطة). أخطاء `422`: لم يحدد نوع غيار / الوقت غير متاح / المدرب لا يدعم نوع الغيار / الوقت محجوز مسبقًا.
- **`POST /bookings/{id}/assign`** Body `{ "slot_id": 5 }`. يشترط: الحجز `pending`، والوقت المختار `approved`، والمدرب يدعم نوع غيار الطالب. النتيجة: `slot_id` للحجز يُستبدل بالوقت المختار، `status=confirmed`، `assigned_by` = المشرف، ويُشعَر الطالب. الرد `{data: Booking}` مع `student` و`slot.instructor`. ⚠️ لا يتحقق الباك اند وقت التعيين من عدم وجود حجز آخر على نفس الوقت — اعرض للمشرف نتيجة `/availability-slots/available` فقط.
- **`GET /bookings/pending`** و`GET /dashboard/supervisor`: `today_bookings` = حجوزات (pending+confirmed) وقتها اليوم؛ `upcoming_bookings` = (pending+confirmed) بعد الآن، مرتبة بالأقدم. كل منها مع `student` و`slot.instructor`.
- **`POST /bookings/{id}/complete`**: الحجز `confirmed` فقط؛ المدرب يُنهي حجوزات جدوله فقط. الرد بدون علاقات.
- **`POST /bookings/{id}/cancel`**: غير مسموح إن كان `cancelled`/`completed`. **المدرب لا يلغي مباشرة** (`422`: يطلب من المشرف). الطالب يلغي حجوزاته فقط. **الغرامة**: إذا `الآن > (بداية الدرس − cancellation_grace_hours)` تُحسب `penalty_amount = price × cancellation_penalty_percent / 100`، وإلا `null`. الرد يحتوي `status=cancelled` و`penalty_amount` (بلا `slot`). ويُشعَر الطالب.

### 4.6 الدفع

| المسار | الأدوار | الوصف |
|---|---|---|
| `POST /bookings/{id}/payments` | ST,S,A | تسجيل دفعة (status=pending) |
| `GET /payments/sham-cash-link` | ST,S,A | `{ "link": "shamcash://pay?account=..." }` أو `{ "link": null }` إن لم يضبط الأدمن حساب شام كاش |
| `GET /payments/pending` | S,A | `{data:[Payment]}` كل دفعة مع `booking.student` |
| `POST /payments/{id}/confirm` | S,A | يؤكد استلام الدفعة ويُشعر الطالب (`422` إن كانت مؤكدة مسبقًا) |

`POST /bookings/{id}/payments` Body: `{ "method": "cash", "amount": 15 }` (`method` = `cash|sham_cash`، `amount` رقم ≥0). الطالب يسجل لحجوزاته فقط. الرد `201 { "data": Payment }`. ⚠️ لا يوجد منع لتكرار الدفعات لنفس الحجز؛ اعرض آخر دفعة في `payments` واعتبر الحجز مدفوعًا إذا وُجدت دفعة `confirmed`.
كل الدفع في v1 **يدوي**: شام كاش لا يوجد له تكامل API، التطبيق يولّد الرابط فقط والمشرف يؤكد الاستلام يدويًا.

### 4.7 المحتوى التعليمي

| المسار | الأدوار | الوصف |
|---|---|---|
| `GET /content-categories` | All | كل الفئات مع محتواها وكويز كل عنصر (بدون questions) |
| `GET /content-categories/{id}` | All | فئة واحدة مع `contents.quiz` |
| `POST /content-categories` | S,A | Body: `{ "name": "...", "is_sequential": false }` |
| `PUT /content-categories/{id}` | S,A | نفس الحقول (كلها اختيارية) |
| `DELETE /content-categories/{id}` | S,A | `{message}` |
| `GET /contents/{id}` | All | محتوى مع `quiz.questions` (أو `403` إن كان مقفولًا للطالب) |
| `POST /contents` | S,A | Body: `{ "category_id", "title", "type": "video|image", "url", "order_index"? }` |
| `PUT /contents/{id}` | S,A | نفس الحقول اختيارية |
| `DELETE /contents/{id}` | S,A | `{message}` |
| `POST /contents/{id}/quiz` | S,A | إنشاء كويز |
| `POST /contents/{id}/view` | ST | تأشير المحتوى كمُشاهَد → `{ "completed": true }` |
| `POST /quizzes/{id}/submit` | ST | تسليم إجابات كويز |
| `GET /me/content-progress` | ST | `{data:[ContentProgress]}` (للسجلات الموجودة فقط) |
| `GET /students/{id}/content-progress` | I,S,A | نفسه لطالب معيّن |

- `order_index` إن لم يُرسل يُعيَّن تلقائيًا (آخر رقم في الفئة + 1). **هو الذي يحدد الترتيب** في الفئات المتسلسلة، ولا يوجد ترتيب مضمون في ردّ القائمة، فرتّب أنت حسب `order_index`.
- `POST /contents/{id}/quiz` Body:
```json
{ "pass_score": 70,
  "questions": [
    { "question": "...", "options": ["أ", "ب", "ج"], "correct_answer": "أ" } ] }
```
  `pass_score` 1–100، سؤال واحد على الأقل، خياران على الأقل لكل سؤال، و`correct_answer` نص يطابق أحد الخيارات **حرفيًا** (غير مفحوص في الباك اند). الرد `201 { data: Quiz }` (يتضمن `correct_answer` للمشرف). ⚠️ لا يمنع إنشاء كويز ثانٍ لنفس المحتوى.
- `POST /quizzes/{id}/submit` Body: `{ "answers": { "<questionId>": "<نص الخيار المختار>", ... } }` — كائن مفاتيحه رقم السؤال (كنص في JSON) وقيمته **نص الخيار** (لا رقمه). أسئلة بلا إجابة تُحسب خطأ. الرد (بدون `data`):
```json
{ "score": 100, "passed": true, "pass_score": 70, "blocks_next": false }
```
  `score` نسبة مئوية صحيحة. المحاولات **غير محدودة**. `blocks_next=true` عندما تكون الفئة متسلسلة والطالب لم ينجح.
- **قفل المحتوى المتسلسل** (فئة `is_sequential=true`): عنصر مقفول للطالب ما لم يكن **كل** العناصر السابقة (ذات `order_index` أصغر) "مفتوحة"، أي: `completed=true` **و** (إن كان لها كويز) `quiz_score >= pass_score`. الطالب المقفول: `GET /contents/{id}` و`/view` و`/submit` يرجعون `403`/`422` بـ"يجب إكمال المحتوى السابق أولاً.". في الفئات غير المتسلسلة كل شيء مفتوح والكويز للمراجعة فقط.
- ⚠️ في فئة متسلسلة، **الرسوب** بكويز يضبط `completed=false` لذلك المحتوى؛ النجاح لاحقًا يعيده `true`. في فئة غير متسلسلة `completed` يصير `true` عند أي تسليم.
- قواعد عرض مقترحة للعميل: "مفتوح" = ما سبق؛ "مقفول" = فئة متسلسلة وعنصر سابق غير مفتوح. استنتج ذلك من `GET /content-categories` + `GET /me/content-progress` (تُدمج محليًا حسب `content.id`)؛ الباك اند لا يرجع حالة القفل جاهزة.

### 4.8 المهارات العملية

| المسار | الأدوار | الوصف |
|---|---|---|
| `GET /skills` | All | `{data:[Skill]}` مرتبة بالاسم (القائمة العامة) |
| `POST /skills` | A | Body `{ "name": "..." }` |
| `PUT /skills/{id}` | A | Body `{ "name": "..." }` |
| `DELETE /skills/{id}` | A | `{message}` |
| `GET /me/skills` | ST | `{data:[StudentSkill]}` |
| `GET /students/{id}/skills` | I,S,A | نفسه لطالب معيّن (`404` إن لم يكن طالبًا) |
| `PUT /students/{studentId}/skills/{skillId}` | I | تقييم مهارة: Body `{ "is_completed": true }` → `{data: StudentSkill}` مع `updated_by` |
| `GET /students/{id}/certificate-eligibility` | I,S,A | `{ "eligible": true/false }` |

- ⚠️ `skillId` في مسار PUT هو **id المهارة** (`skill.id`) وليس `id` صف الـ StudentSkill.
- ⚠️ صفوف `StudentSkill` تُنشأ **عند أول تقييم فقط**. الطالب الجديد يرجع له `GET /me/skills` **قائمة فارغة** حتى يقيّم المدرب أول مهارة. لعرض كل المهارات (منجزة/غير منجزة) ادمج `GET /skills` مع `GET /me/skills` محليًا: المهارة غير الموجودة بالردّ = غير منجزة.
- التقييم ثنائي (منجزة/غير منجزة) فقط ومستقل عن الحجوزات. `eligible=true` عندما عدد المهارات المنجزة ≥ عدد كل المهارات (ولا يكون صفرًا).

### 4.9 الرسائل (طالب ↔ مشرف/أدمن فقط)

| المسار | الأدوار | الوصف |
|---|---|---|
| `POST /messages` | ST,S,A | Body `{ "receiver_id": 2, "body": "..." }` (body ≤5000) → `201 {data: Message}` |
| `GET /messages/with/{userId}` | ST,S,A | المحادثة مع مستخدم، الأقدم أولًا → `{data:[Message]}` |
| `POST /messages/{id}/read` | ST,S,A | تأشير كمقروءة (المستقبِل فقط) → `{data: Message}` |
| `GET /messages/threads` | S,A | `{data:[User]}` الطلاب الذين بينهم وبيني رسائل |

القيد: أحد الطرفين طالب والآخر مشرف/أدمن، وإلا `422` ("التواصل مسموح فقط بين الطالب والمشرف/الأدمن"). **المدرب لا يستطيع المراسلة إطلاقًا.** الطالب لا يملك endpoint لمعرفة "من هو المشرف": في v1 (مشرف واحد) استخدم `id` المشرف المعروف (عنده `2` في البيانات التجريبية) أو اطلب من الباك اند إضافة endpoint لذلك.

### 4.10 الإعدادات

| المسار | الأدوار | الوصف |
|---|---|---|
| `GET /settings` | A,S | الرد (بدون `data`): `{ "lesson_price": 15, "cancellation_grace_hours": 24, "cancellation_penalty_percent": 50, "sham_cash_account": "" }` |
| `PUT /settings` | A | Body: أي مجموعة من الحقول السابقة (كلها اختيارية) `lesson_price` رقم ≥0، `cancellation_grace_hours` صحيح ≥0، `cancellation_penalty_percent` صحيح 0–100، `sham_cash_account` نص/null. الرد: الإعدادات كاملة بعد التحديث |

### 4.11 لوحات التحكم (بدون غلاف `data`)

| المسار | الأدوار | الرد |
|---|---|---|
| `GET /dashboard/admin` | A | `{ students_count, instructors_count, supervisors_count, bookings_count, completed_bookings, total_revenue, pending_payments }` (كلها أرقام؛ `total_revenue` = مجموع الدفعات المؤكدة) |
| `GET /dashboard/supervisor` | S,A | `{ today_bookings: [Booking], upcoming_bookings: [Booking], pending_slots: [Slot], pending_payments: [Payment] }` |
| `GET /dashboard/instructor` | I | `{ today_bookings: [Booking], my_slots: [Slot] }` |

لا يوجد `/dashboard/student`: ركّب شاشة الطالب من `GET /me` + `GET /bookings/mine` + `GET /me/skills` (+ `GET /skills`).

---

## 5. جدول الصلاحيات المختصر

| القدرة | ST | I | S | A |
|---|:-:|:-:|:-:|:-:|
| تسجيل ذاتي | ✔ | | | |
| إنشاء مشرفين/مدربين | | | | ✔ |
| اقتراح أوقات توفر | | ✔ | | |
| اعتماد/رفض الأوقات | | | ✔ | ✔ |
| طلب حجز | ✔ | | | |
| تعيين مدرب للحجز | | | ✔ | ✔ |
| إنهاء الدرس | | ✔ | ✔ | ✔ |
| إلغاء حجز | ✔ | ✗ (يطلب من المشرف) | ✔ | ✔ |
| تسجيل دفعة | ✔ | | ✔ | ✔ |
| تأكيد دفعة | | | ✔ | ✔ |
| رفع محتوى/كويزات | | | ✔ | ✔ |
| مشاهدة المحتوى وحل الكويز | ✔ | | | |
| تقييم مهارات الطلاب | | ✔ | | |
| إدارة قائمة المهارات | | | | ✔ |
| مراسلة | ✔ (مع S/A) | ✗ | ✔ | ✔ |
| تعديل الإعدادات | | | | ✔ (S يقرأ فقط) |

---

## 6. ملاحظات لمطور Flutter

1. **Models nullable**: بسبب "المفاتيح الاختيارية" (القاعدة 2) اجعل الحقول المتداخلة `nullable` واستخدم `json['student'] != null ? User.fromJson(...) : null`.
2. **غلاف `data`**: اكتب دالة مساعدة `unwrap(json) => json is Map && json.containsKey('data') ? json['data'] : json`، لكن انتبه أن `/login` و`/notifications` و`/dashboard/*` لا يحتويان `data`.
3. **Interceptor موحّد** (Dio): يضيف `Authorization` و`Accept`، ويحوّل `422` إلى استثناء يحمل `message` و`errors`، ويعالج `401` بمسح التوكن والعودة لشاشة الدخول.
4. **Android HTTP**: للتطوير بـ `http://` (ليس https) أضف `android:usesCleartextTraffic="true"` في `AndroidManifest.xml`.
5. **RTL**: واجهة التطبيق عربية بالكامل (اتجاه من اليمين لليسار).
6. **Postman**: `postman_collection.json` المرفق فيه كل المسارات وحسابات الاختبار، ويحفظ التوكنات تلقائيًا بعد تنفيذ طلبات Login.
7. **حسابات تجريبية** (كلمة السر `password` للجميع، الدخول بالهاتف): admin `0999000001` · supervisor `0999000002` · instructor `0999000003` · student `0999000004`.
8. لا Push Notifications في v1 (مؤجَّلة لـ v2 عبر Firebase حسب المواصفات). المتاح الآن: إشعارات داخل التطبيق عبر `GET /notifications` + إيميل.
