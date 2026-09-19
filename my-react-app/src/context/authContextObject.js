import { createContext } from 'react';

// الكائن نفسه بملف منفصل عن المكوّن (AuthProvider) وعن الـ hook (useAuth).
// السبب تقني بحت: أداة "Fast Refresh" (تحديث المكوّنات أثناء التطوير
// بدون ما تخسر حالة الصفحة) بتشتغل صح بس إذا كل ملف بيصدّر إما مكوّنات
// بس، أو قيم عادية بس — مش خليط من الاثنين.
export const AuthContext = createContext(null);
