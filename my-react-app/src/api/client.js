// طبقة واحدة بس بتعرف تتكلم مع اللارافيل. كل باقي التطبيق بيستدعي دوال
// من مجلد src/api بدل ما يستخدم fetch مباشرة بكل صفحة — هيك لو تغيّر
// شكل الاستجابة أو عنوان السيرفر، بتصلحه بمكان واحد بس.

const BASE_URL = import.meta.env.VITE_API_URL;

function getToken() {
  return localStorage.getItem('token');
}

// دالة عامة بتنفّذ أي طلب HTTP. باقي الملفات (auth.js, staff.js...)
// بتستخدم apiGet/apiPost/apiPut/apiDelete تحت بدل ما تكررها.
async function request(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json' };

  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  if (body !== undefined) headers['Content-Type'] = 'application/json';

  const response = await fetch(`${BASE_URL}${path}`, {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  // 204 No Content ما إلها جسم رد نقرأه
  const data = response.status === 204 ? null : await response.json().catch(() => null);

  if (!response.ok) {
    // لارافيل بيرجّع أخطاء التحقق بشكل { message, errors: { field: [...] } }
    const error = new Error(data?.message || 'حدث خطأ غير متوقع، حاول مرة كمان.');
    error.status = response.status;
    error.fieldErrors = data?.errors ?? null;
    throw error;
  }

  return data;
}

export const apiGet = (path) => request(path);
export const apiPost = (path, body) => request(path, { method: 'POST', body });
export const apiPut = (path, body) => request(path, { method: 'PUT', body });
export const apiDelete = (path) => request(path, { method: 'DELETE' });

// بعض الـ endpoints عند اللارافيل (Resource::collection مباشرة من الـ
// controller) بترجع {"data": [...]}, وبعضها (زي الـ dashboards) بترجع
// الكائن مباشرة بدون هاد الغلاف. هاي دالة صغيرة بتشيل الغلاف إذا كان موجود.
export const unwrap = (response) => response?.data ?? response;
