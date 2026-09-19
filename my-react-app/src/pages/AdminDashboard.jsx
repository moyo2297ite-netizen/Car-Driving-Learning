import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchAdminDashboard } from '../api/dashboard';

function AdminDashboard() {
  const [stats, setStats] = useState(null);
  const [error, setError] = useState('');

  // useEffect بيشتغل بعد ما رياكت يرسم الصفحة — هون منستخدمه لجلب البيانات
  // أول ما الصفحة تفتح. مصفوفة الاعتماديات [] بالآخر يعني "مرة وحدة بس".
  useEffect(() => {
    fetchAdminDashboard()
      .then(setStats)
      .catch((err) => setError(err.message));
  }, []);

  return (
    <Layout roleLabel="أدمن">
      <h1>
        <Icon name="gauge" size={24} />
        نظرة عامة
      </h1>
      <p className="page-subtitle">ملخص أداء المدرسة والعمليات المعلّقة اليوم.</p>

      {error && <p className="card">تعذّر تحميل الإحصائيات: {error}</p>}

      {!stats && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {stats && (
        <div className="stats-grid">
          <div className="card stat-card">
            <span className="stat-number">{stats.students_count}</span>
            <span className="stat-label">طالب مسجّل</span>
          </div>
          <div className="card stat-card">
            <span className="stat-number">{stats.instructors_count}</span>
            <span className="stat-label">مدرب</span>
          </div>
          <div className="card stat-card">
            <span className="stat-number">{stats.supervisors_count}</span>
            <span className="stat-label">مشرف</span>
          </div>
          <div className="card stat-card">
            <span className="stat-number">{stats.bookings_count}</span>
            <span className="stat-label">إجمالي الحجوزات</span>
          </div>
          <div className="card stat-card">
            <span className="stat-number">{stats.completed_bookings}</span>
            <span className="stat-label">درس مكتمل</span>
          </div>
          <div className="card stat-card">
            <span className="stat-number">{Number(stats.total_revenue).toLocaleString()}</span>
            <span className="stat-label">الدخل المؤكّد ($)</span>
          </div>
          <div className="card stat-card">
            <span className="stat-number">{stats.pending_payments}</span>
            <span className="stat-label">دفعة بانتظار التأكيد</span>
          </div>
        </div>
      )}

      <div className="quick-links">
        <Link to="/admin/instructors">
          <Icon name="users" />
          إدارة المدربين
        </Link>
        <Link to="/admin/supervisors">
          <Icon name="users" />
          إدارة المشرفين
        </Link>
        <Link to="/admin/content">
          <Icon name="book" />
          المحتوى التعليمي
        </Link>
        <Link to="/admin/skills">
          <Icon name="target" />
          قائمة المهارات
        </Link>
        <Link to="/admin/settings">
          <Icon name="settings" />
          الإعدادات العامة
        </Link>
      </div>
    </Layout>
  );
}

export default AdminDashboard;
