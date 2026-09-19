# دليل مشروع "سويق" — شرح كامل لكل شي متعلّق برياكت

هاد الملف مرجعك الشخصي لفهم بنية المشروع وكل مفهوم رياكت استُخدم فيه.
مكتوب بترتيب منطقي: ابلش من فوق لتحت، وارجعله وقت ما بدك تتذكر شي.

---

## 1. الصورة الكبيرة

المشروع فرونت-اند لتطبيق مدرسة تعليم قيادة (اسمه "سويق")، فيه ٤ أدوار:
أدمن، مشرف، مدرب، طالب — كل واحد بشوف لوحة تحكم وصفحات مختلفة.

**الستاك:**
- **React 19** — بناء الواجهة كمكوّنات (components)
- **Vite** — أداة البناء والتشغيل (dev server سريع، بديل عن Create React App)
- **React Router v7** — التنقّل بين الصفحات بدون إعادة تحميل المتصفح
- **CSS عادي** (بدون Tailwind أو أي مكتبة) — مع نظام تصميم مبني على CSS
  Custom Properties (متغيرات CSS)
- **Laravel** (مشروع منفصل بمجلد `backend/`) — الـ API يلي الرياكت بيتكلم معه

**قاعدة أساسية بالمشروع:** الفرونت-اند لازم يطابق شكل ردود الباك اند
الحقيقية (snake_case، كائنات متداخلة زي `booking.slot.instructor.name`)
مش أسماء حقول اخترعناها إحنا. هاد بيخلي أي صفحة لسا "وهمية" (mock)
جاهزة تتحول لربط حقيقي بتغيير بسيط بس.

---

## 2. بنية المجلدات (Folder Structure)

```
my-react-app/
├── index.html              نقطة الدخول HTML، فيها خطوط Google Fonts
├── .env                     VITE_API_URL (عنوان الباك اند) — غير مرفوع لـ git
├── src/
│   ├── main.jsx             نقطة دخول رياكت — بيلف التطبيق بـ Router وAuthProvider
│   ├── App.jsx               تعريف كل المسارات (routes) بالتطبيق
│   ├── index.css             نظام التصميم كامل (متغيرات الألوان، كل الكلاسات)
│   ├── App.css               تنسيقات صغيرة على مستوى التطبيق ككل
│   │
│   ├── api/                  ← طبقة الاتصال بالباك اند (Data Access Layer)
│   │   ├── client.js          الدالة العامة يلي بترسل أي طلب HTTP
│   │   ├── auth.js            login / logout / fetchMe
│   │   ├── dashboard.js       جلب بيانات لوحات التحكم
│   │   ├── staff.js           إدارة المدربين/المشرفين
│   │   ├── contentCategories.js  المحتوى التعليمي
│   │   └── settings.js        الإعدادات العامة
│   │
│   ├── context/               ← إدارة "مين مسجّل دخول" (Identity Layer)
│   │   ├── authContextObject.js   كائن الـ Context نفسه (قيمة فاضية)
│   │   └── AuthContext.jsx        AuthProvider component (المنطق)
│   │
│   ├── hooks/
│   │   └── useAuth.js         hook بسيط لقراءة AuthContext من أي مكوّن
│   │
│   ├── components/            ← مكوّنات مشتركة بين أكتر من صفحة
│   │   ├── Layout.jsx          الشريط العلوي + إطار كل صفحة داخلية
│   │   ├── ProtectedRoute.jsx  يمنع الدخول لصفحة إلا إذا الدور مسموح
│   │   ├── Icon.jsx            كل أيقونات التطبيق (svg يدوي، مكوّن واحد)
│   │   └── ConversationView.jsx  محادثة عامة (تستخدمها صفحتين رسائل)
│   │
│   └── pages/                 ← كل صفحة = مكوّن واحد مرتبط بمسار (route)
│       ├── Login.jsx / SignUp.jsx
│       ├── AdminDashboard.jsx / AdminStaff.jsx / AdminInstructors.jsx /
│       │   AdminSupervisors.jsx / AdminContent.jsx / AdminSkills.jsx /
│       │   AdminSettings.jsx
│       ├── SupervisorDashboard.jsx / SupervisorMessages.jsx
│       ├── InstructorDashboard.jsx / InstructorStudents.jsx /
│       │   InstructorStudentSkills.jsx / InstructorAvailability.jsx
│       └── StudentDashboard.jsx / StudentBooking.jsx / StudentContent.jsx /
│           StudentSkills.jsx / StudentMessages.jsx
```

**ليش هيك التقسيم؟** كل مجلد مسؤول عن طبقة وحدة بس (Separation of
Concerns، نفس المبدأ يلي الباك اند مبني عليه):
- `api/` بيعرف *كيف نتكلم مع السيرفر*، وما بيعرف شي عن الواجهة.
- `pages/` بتعرف *شو نعرض للمستخدم*، وما بتعرف تفاصيل fetch.
- `context/`+`hooks/` بتعرف *مين المستخدم الحالي*، وبتشاركها مع أي حدا يحتاجها.
- `components/` أي جزء واجهة تكرر باكتر من صفحة، طلع لملف واحد.

---

## 3. مفاهيم رياكت — كل وحدة وين استُخدمت بالضبط

### Component و JSX
كل ملف بـ `pages/` و`components/` هو دالة جافاسكريبت عادية بترجّع HTML
شكلي (JSX). مثال أبسط مكوّن بالمشروع: [`Icon.jsx`](src/components/Icon.jsx) —
دالة بترجع `<svg>` حسب اسم الأيقونة يلي تمرره.

### Props
القيم يلي بتمررها لمكوّن من الخارج. مثال: `<Layout roleLabel="أدمن">` —
`roleLabel` هون prop. و`children` هو prop خاص بيمثّل أي محتوى تحطه بين
وسمي المكوّن: `<Layout>هاد كله children</Layout>`.

### State — `useState`
ذاكرة محلية بالمكوّن بترجّع رياكت يرسم الواجهة من جديد لما تتغيّر.
كل صفحة فيها فورم (تسجيل الدخول، الإعدادات، إضافة مهارة...) بتستخدمه.
مثال من [`Login.jsx`](src/pages/Login.jsx):
```jsx
const [phone, setPhone] = useState('');
// ... onChange={(e) => setPhone(e.target.value)}
```

### Effects — `useEffect`
كود بيشتغل *بعد* ما رياكت يرسم الصفحة — مستخدم لجلب بيانات من السيرفر
أول ما الصفحة تفتح. أوضح مثال: [`AdminDashboard.jsx`](src/pages/AdminDashboard.jsx):
```jsx
useEffect(() => {
  fetchAdminDashboard().then(setStats).catch((err) => setError(err.message));
}, []); // مصفوفة فاضية = مرة وحدة بس، أول ما الصفحة تفتح
```
وبـ [`AuthContext.jsx`](src/context/AuthContext.jsx) لجلب `/me` أول ما
التطبيق يفتح، للتحقق من جلسة سابقة محفوظة.

### Context API + Custom Hook
طريقة لمشاركة قيمة (مين المستخدم الحالي) مع أي مكوّن بالشجرة، بدون
تمريرها prop-by-prop عبر كل مستوى (المشكلة المعروفة باسم *prop drilling*).
مقسومة على ٣ ملفات قصدًا (شوف قسم "Fast Refresh" بالأسفل ليش):
- `authContextObject.js` — الكائن نفسه (`createContext`)
- `AuthContext.jsx` — الـ `AuthProvider` (المنطق: login/logout/loading)
- `hooks/useAuth.js` — الـ hook يلي أي صفحة بتستدعيه: `const { user, login, logout } = useAuth();`

### Conditional Rendering (عرض شرطي)
بدل `if/else` تقليدي جوا الـ HTML، بتستخدم `? :` أو `&&` جوا الأقواس
المعقوفة. أمثلة بكل مكان — أوضحها بـ [`StudentContent.jsx`](src/pages/StudentContent.jsx)
لتحديد هل المحتوى مقفول، اتشاهد، أو لسا.

### Lists و `key`
أي مصفوفة بتعرضها بـ `.map()` لازم كل عنصر ياخد `key` فريد (عادةً `id`)
عشان رياكت يفرّق بين العناصر لما القائمة تتغيّر. موجود بكل صفحة فيها
قائمة: `AdminStaff`, `StudentSkills`, `SupervisorDashboard`...

### Controlled Forms (فورم متحكّم فيه)
كل `<input>` قيمته جايّة من state (`value={...}`) وتغييره بيحدّث الـ
state (`onChange={...}`) — مش الـ DOM هو يلي ماسك القيمة، رياكت هو.
هاد النمط موجود بكل فورم بالمشروع (Login, SignUp, AdminSettings...).

### Component Composition وإعادة الاستخدام
لما تلاقي حالك عم تكرر نفس الواجهة مرتين وبس تغيّر تفصيلة بسيطة،
اعملها مكوّن واحد وخد الفرق كـ prop. أمثلة حقيقية بالمشروع:
- [`AdminStaff.jsx`](src/pages/AdminStaff.jsx) — صفحة واحدة، وصفحتي
  "إدارة المدربين"/"إدارة المشرفين" ([`AdminInstructors.jsx`](src/pages/AdminInstructors.jsx),
  [`AdminSupervisors.jsx`](src/pages/AdminSupervisors.jsx)) بس بتمررله
  `role` مختلف.
- [`ConversationView.jsx`](src/components/ConversationView.jsx) — مكوّن
  محادثة واحد، بتستخدمه [`StudentMessages.jsx`](src/pages/StudentMessages.jsx)
  و[`SupervisorMessages.jsx`](src/pages/SupervisorMessages.jsx).

### التوجيه (Routing) — react-router-dom
- `<BrowserRouter>` بـ [`main.jsx`](src/main.jsx) — بيفعّل نظام التوجيه لكل التطبيق.
- `<Routes>`/`<Route path="..." element={...} />` بـ [`App.jsx`](src/App.jsx) —
  خريطة "أي مسار = أي مكوّن".
- `<Link to="...">` — تنقّل بدون إعادة تحميل الصفحة (بعكس `<a href>`).
- `useNavigate()` — تنقّل من جوا كود جافاسكريبت (بعد نجاح تسجيل الدخول مثلاً).
- `useParams()` — قراءة جزء متغيّر من المسار، زي `:studentId` بمسار
  `/instructor/students/:studentId/skills`.
- **نمط الحماية (Protected Routes):** [`ProtectedRoute.jsx`](src/components/ProtectedRoute.jsx)
  مكوّن بيلف أي صفحة محمية، وبيتحقق من `user.role` قبل ما يعرضها —
  وإلا بيرجّع المستخدم لصفحة الدخول عبر `<Navigate to="/" />`.

---

## 4. طبقة الاتصال بالباك اند (`src/api/`)

### `client.js` — نقطة الاتصال الوحيدة
كل طلب HTTP بالتطبيق بيمر من هون. مسؤول عن ٣ أشياء بمكان واحد:
1. **إضافة التوكن تلقائيًا** لكل طلب (`Authorization: Bearer ...`) من `localStorage`.
2. **معالجة الأخطاء** بشكل موحّد — لو لارافيل رجّع 422 (خطأ تحقق)، بنحوّله لـ `Error` عادي فيه `.message` جاهز للعرض.
3. **حل مشكلة `{data: ...}`** — بعض ردود لارافيل ملفوفة بمفتاح `data` (لما
   ترجّع `Resource::collection()` مباشرة من الـ controller) وبعضها لأ
   (لما ترجّع `response()->json([...])` يدوي، زي الـ dashboards). دالة
   `unwrap()` بتشيل الغلاف لو موجود، فباقي الكود ما بضطر يهتم بهاد الفرق.

### باقي ملفات `api/`
كل ملف بيغلّف مجموعة endpoints مرتبطة بموضوع واحد، وبيحوّل أسماء
الحقول بين الشكلين (الرياكت بيستخدم `camelCase` بالـ JS، والباك اند
`snake_case` بالـ JSON) بمكان واحد بس. مثال من [`staff.js`](src/api/staff.js):
```js
export async function createStaff({ name, phone, teachesManual, ... }) {
  return unwrap(await apiPost('/staff', {
    name, phone, teaches_manual: teachesManual, ...
  }));
}
```

---

## 5. طبقة الهوية (`context/` + `hooks/`)

`AuthProvider` (بيلف كل التطبيق من [`main.jsx`](src/main.jsx)) بيحتفظ
بـ:
- `user` — بيانات المستخدم المسجّل دخوله (أو `null`)
- `loading` — عم نتحقق من جلسة سابقة؟
- `login(phone, password)` — بيتصل بـ `/login`، يخزّن التوكن، يحدّث `user`
- `logout()` — بيتصل بـ `/logout`، يمسح كل شي محليًا

أي صفحة أو مكوّن بحتاج هاي المعلومات بستدعي `useAuth()` بس:
```jsx
const { user, logout } = useAuth();
```

---

## 6. الصفحات — كلها مربوطة فعليًا هلق ✅

كل صفحة بالتطبيق صارت تتكلم مع اللارافيل الحقيقي (مافي أي `mockX`
باقي بالكود). خريطة سريعة أي صفحة بتستخدم أي endpoint:

| الصفحة | Endpoint(s) |
|---|---|
| تسجيل الدخول / تسجيل حساب / `/me` / خروج | `/login`, `/register`, `/me`, `/logout` |
| لوحة الأدمن | `GET /dashboard/admin` |
| إدارة المدربين/المشرفين | `GET/POST/DELETE /staff` |
| المحتوى التعليمي (أدمن) | `GET/POST /content-categories`, `/contents` |
| قائمة المهارات (أدمن) | `GET/POST/DELETE /skills` |
| الإعدادات العامة | `GET/PUT /settings` |
| لوحة المشرف (+اعتماد توفر، تأكيد دفعات، تعيين مدرب) | `GET /dashboard/supervisor`, `/availability-slots/{id}/approve\|reject`, `/payments/{id}/confirm`, `/bookings/{id}/assign` |
| رسائل المشرف | `GET /messages/threads`, `/messages/with/:id`, `POST /messages` |
| لوحة المدرب | `GET /dashboard/instructor` |
| طلاب المدرب + تقييم مهاراتهم | `GET /instructor/students` *(endpoint جديد أضفناه، شوف قسم ٩)*, `GET/PUT /students/:id/skills` |
| أوقات توفر المدرب | `GET /availability-slots/mine`, `POST /availability-slots` |
| لوحة الطالب | مركّبة من ٣ طلبات — شوف قسم ٩ |
| حجز درس | `GET /availability-slots/available`, `POST /bookings` |
| المحتوى التعليمي (طالب) | `GET /content-categories` + `GET /me/content-progress` (مدموجين محليًا) |
| مهاراتي (طالب) | `GET /me/skills` |
| راسل المشرف | `GET /messages/with/:id`, `POST /messages` |

**النمط يلي تكرر بكل صفحة** (فرصة جيدة تراجعه بأي ملف بـ `src/pages/`
لو بدك تفهمه أعمق): `useState(null)` للبيانات + `useState('')`
للخطأ، `useEffect` بيستدعي دالة من `src/api/`، وعرض شرطي لحالة
"جاري التحميل" لحد ما توصل البيانات.

---

## 7. نظام التصميم (`index.css`)

- **كل الألوان محصورة بمتغيرات CSS** بأعلى الملف (`:root { --accent: ... }`)
  — غيّر القيمة بمكان واحد وينعكس على كل التطبيق.
- **فلسفة "لون واحد بس"**: الوصلنا لهاي بعد ما جرّبنا نسخة أولى فيها
  خلفية غامقة + توهّج + نص متدرّج (gradient text)، وطلعت هوية "معروفة
  إنها شغل أدوات AI". النسخة الحالية بيضاء مسطّحة، ولون أخضر غامق واحد
  (`--accent`) مستخدم بانضباط بالأماكن المهمة فقط (فعل أساسي، حالة
  "منجز"). التمييز البصري الباقي جاي من التايبوغرافيا والحدود مش الألوان.
- **خطّين لا خط واحد**: `Noto Kufi Arabic` (عريض، للعناوين والبراند)
  و`IBM Plex Sans Arabic` (للنصوص والفورم) — تركيبة أقل شيوعًا من خط
  واحد بكل مكان، وبتعطي شخصية للتصميم.
- **الكلاسات معاد استخدامها بقصد**: `.card`, `.badge`, `.status-tag`,
  `.content-list`, `.action-row`... موجودة بعشر صفحات مختلفة بنفس
  الأسماء، فقدرنا نغيّر شكل كل التطبيق من ملف CSS واحد بدون ما نلمس أي JSX.

---

## 8. تفصيلة تقنية: ليش `AuthContext` مقسوم لـ ٣ ملفات؟

لما جرّبنا الأول نحط كل شي (الكائن + الـ Provider + الـ hook) بملف
واحد، ESLint رفضها بقاعدة `react-refresh/only-export-components`.
السبب: أداة "Fast Refresh" (تحديث الكود أثناء التطوير بدون ما تخسر
حالة الصفحة اللي فاتحها بالمتصفح) بتشتغل صح بس إذا كل ملف بيصدّر إما
**مكوّنات فقط** أو **قيم عادية فقط** — مش خليط من الاثنين. فصلنا:
- قيمة عادية (`createContext`) → `authContextObject.js`
- مكوّن (`AuthProvider`) → `AuthContext.jsx`
- دالة عادية (`useAuth`) → `hooks/useAuth.js`

هاي تفصيلة صغيرة بس بتوضح قاعدة عامة مفيدة: **لما ESLint يشتكي، غالبًا
عم يحميك من مشكلة حقيقية لاحقًا، مش بس "قاعدة تنسيق".**

---

## 9. فجوات وقرارات بالباك اند صادفناها وقت الربط

| الموضوع | شو صار |
|---|---|
| ما كان في `GET /students` (طلاب مدرب معيّن) | ✅ اتحل — ضفنا `GET /instructor/students` (route + controller method + service method جديدين) |
| ما في `GET /dashboard/student` | باقي فجوة مقصودة — `StudentDashboard.jsx` بتركّب نفسها من ٣ طلبات منفصلة (`useAuth().user`, `/bookings/mine`, `/me/skills`) بدل طلب واحد |
| `/availability-slots/available` كانت بترجع أوقات محجوزة مسبقًا | 🐛 اتصلحت — `AvailabilitySlotService::listApprovedForTransmission` صار يستثني الأوقات يلي إلها حجز pending/confirmed |
| `BookingResource` ما كانت بترجّع الدفعات رغم إنه الـ controller بيعمل eager-load إلهم | 🐛 اتصلحت — ضفنا `payments` لـ `BookingResource::toArray()`، وهيك صار فيه شكل عرض "سجّل دفعة" بلوحة الطالب |
| `GET /content-categories` (القائمة) ما كانت بترجّع الكويز مع كل محتوى (خلافًا لـ `show()`) | 🐛 اتصلحت — صار eager-load لـ `contents.quiz` بمكانين متوافقين |
| ما في endpoint لعرض إشعارات المستخدم رغم إنه Laravel Notifications شغّالة وبتخزن بقاعدة البيانات | ✅ اتحل — ضفنا `NotificationController` كامل (`GET /notifications`, `POST /notifications/:id/read`) |
| صفحات الرسائل بتفترض "مشرف واحد" بس | مقبول لـ v1 (مدرسة بمشرف واحد حسب المواصفات) — الـ id مكتوب صراحة بأعلى `StudentMessages.jsx` |
| تسجيل الدخول تحوّل من إيميل لرقم هاتف | غيّرنا `LoginRequest`/`AuthController`، أضفنا عمود `phone` (unique, nullable)، خلّينا `email` اختياري |

كل تعديل بالباك اند اتغطى باختبارات (`php artisan test` — 46 اختبار
ناجح)، وبيانات تجريبية (`DemoBookingsSeeder`, `DemoContentSeeder`)
عشان الصفحات ما تكون فاضية وقت التجربة — فيها حتى محتوى بكويز حقيقي
جاهز تجرّبه فورًا (طالب) وتضيف كويز جديد (أدمن).

### ميزات دورة حياة الحجز/المحتوى المكتملة بهاي الجولة
إنهاء الدرس (مدرب)، إلغاء الحجز (طالب/مشرف)، تسجيل دفعة (طالب)،
تأشير المحتوى كمُشاهَد، حل الكويز، إنشاء كويز (أدمن)، فحص أهلية
الشهادة، وجرس إشعارات حقيقي بأعلى كل صفحة.

---

## 10. خلاصة سريعة (لو بدك تتذكر شي واحد بس)

> كل صفحة = مكوّن. كل مكوّن بياخد بياناته من **state محلي** (`useState`)
> أو **state مشترك** (`useAuth()`)، وبيجيبها من **دالة بـ `src/api/`**
> عبر `useEffect`. التوجيه بين الصفحات بـ `react-router-dom`، والحماية
> بـ `ProtectedRoute`. والتصميم كله من متغيرات CSS بملف واحد.
