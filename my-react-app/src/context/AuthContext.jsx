import { useEffect, useState } from 'react';
import { fetchMe, login as loginRequest, logout as logoutRequest } from '../api/auth';
import { AuthContext } from './authContextObject';

// Context هو طريقة رياكت لمشاركة قيمة (هون: المستخدم المسجّل دخوله)
// مع أي مكوّن بالشجرة، بدون ما تمررها prop-by-prop عبر كل مستوى بالنص
// (المشكلة يلي بيسموها "prop drilling"). أي مكوّن جوا <AuthProvider>
// بقدر يستدعي useAuth() (بملف hooks/useAuth.js) ويوصل مباشرة لـ
// user/login/logout.
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  // إذا في توكن محفوظ من جلسة سابقة، منضل "loading" لحد ما نتحقق منه
  // عبر /me. إذا ما في توكن أصلاً، منبلش مباشرة بـ loading=false —
  // هيك ما منحتاج نستدعي setState من جوا الـ effect بأول تشغيل.
  const [loading, setLoading] = useState(() => Boolean(localStorage.getItem('token')));

  useEffect(() => {
    if (!localStorage.getItem('token')) return;

    fetchMe()
      .then(setUser)
      .catch(() => localStorage.removeItem('token'))
      .finally(() => setLoading(false));
  }, []); // مصفوفة فاضية = هاد الكود بيشتغل مرة وحدة بس، أول ما يفتح التطبيق

  async function login(email, password) {
    const { user: loggedInUser, token } = await loginRequest(email, password);
    localStorage.setItem('token', token);
    setUser(loggedInUser);
    return loggedInUser;
  }

  async function logout() {
    try {
      await logoutRequest();
    } catch {
      // حتى لو فشل طلب تسجيل الخروج (مثلاً انقطع الاتصال)، منضل ننظّف الجلسة محليًا
    }
    localStorage.removeItem('token');
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}
