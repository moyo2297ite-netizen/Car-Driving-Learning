import { useNavigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import Icon from './Icon';
import NotificationBell from './NotificationBell';

// "children" هون هو الـ prop الخاص يلي بيمرره رياكت تلقائياً لأي محتوى
// تحطه بين وسمين لأي مكوّن، متل: <Layout>هاد المحتوى</Layout>
// و roleLabel prop عادي مررناه إحنا يدوياً من كل صفحة (أدمن / مشرف / مدرب / طالب)
function Layout({ children, roleLabel }) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout(); // بيلغي التوكن بالسيرفر وينظّف الجلسة محليًا
    navigate('/');
  };

  return (
    <div className="layout">
      <header className="topbar">
        <div className="topbar-brand">
          <Icon name="steering" size={22} />
          <span className="topbar-brand-name">سويق</span>
          {roleLabel && <span className="topbar-role-pill">{roleLabel}</span>}
        </div>

        <div className="topbar-actions">
          {user && <span style={{ fontSize: 13, color: 'var(--text-dim)' }}>{user.name}</span>}
          <NotificationBell />
          <button className="logout-btn" onClick={handleLogout}>
            <Icon name="logout" size={16} />
            تسجيل خروج
          </button>
        </div>
      </header>

      <main className="content">{children}</main>
    </div>
  );
}

export default Layout;
