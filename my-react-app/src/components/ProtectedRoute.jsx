import { Navigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';

// allowedRole بتقبل دور واحد ("admin") أو أكثر من دور ("["admin","supervisor"]")
// — بعض الصفحات (متل المحتوى التعليمي) مسموحة لأكتر من دور حسب المواصفات.
function ProtectedRoute({ children, allowedRole }) {
  const { user, loading } = useAuth();
  const allowedRoles = Array.isArray(allowedRole) ? allowedRole : [allowedRole];

  // لسا عم نتحقق من الجلسة السابقة (طلب /me عم يترسل) — منستنى قبل
  // ما نقرر نرجّع المستخدم لصفحة الدخول أو لأ
  if (loading) {
    return null;
  }

  // ما في حدا مسجّل دخول أصلاً → رجّعه لصفحة الدخول
  if (!user) {
    return <Navigate to="/" replace />;
  }

  // مسجّل دخول بس بدور مش من ضمن الأدوار المسموحة لهاد المسار → رجّعه لصفحة الدخول
  if (!allowedRoles.includes(user.role)) {
    return <Navigate to="/" replace />;
  }

  // كل شي تمام → اعرض الصفحة المطلوبة
  return children;
}

export default ProtectedRoute;
