import { useContext } from 'react';
import { AuthContext } from '../context/authContextObject';

// hook مخصص صغير بديل عن ما نكتب useContext(AuthContext) بكل صفحة،
// وبيعطي رسالة خطأ واضحة إذا حدا استخدمه غلط برّا <AuthProvider>
export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth لازم يُستخدم جوا <AuthProvider>');
  return ctx;
}
