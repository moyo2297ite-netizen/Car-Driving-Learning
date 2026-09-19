import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { useAuth } from '../hooks/useAuth';
import { fetchMyBookings, cancelBooking } from '../api/bookings';
import { fetchMySkills } from '../api/skills';
import { recordPayment } from '../api/payments';

const methodLabels = { cash: 'نقدًا', sham_cash: 'شام كاش' };

function formatDateTime(iso) {
  return new Date(iso).toLocaleString('ar-SY', { dateStyle: 'medium', timeStyle: 'short' });
}

// ما في endpoint واحد اسمه "/dashboard/student" بالباك اند — هاي الصفحة
// بتركّب نفسها من ٣ مصادر: بيانات المستخدم (من AuthContext أصلاً)،
// الحجوزات، والمهارات.
function StudentDashboard() {
  const { user } = useAuth();
  const [bookings, setBookings] = useState(null);
  const [skills, setSkills] = useState(null);
  const [error, setError] = useState('');
  const [payMethod, setPayMethod] = useState('cash');
  const [paying, setPaying] = useState(false);

  const load = () => {
    Promise.all([fetchMyBookings(), fetchMySkills()])
      .then(([bookingsData, skillsData]) => {
        setBookings(bookingsData);
        setSkills(skillsData);
      })
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const nextBooking = bookings?.find((b) => b.status === 'confirmed') ?? null;
  const completedSkillsCount = skills?.filter((s) => s.is_completed).length ?? 0;
  const totalSkillsCount = skills?.length ?? 0;

  const handleCancel = async () => {
    if (!window.confirm('متأكد إنك بدك تلغي هاد الحجز؟ ممكن تترتب عليه غرامة إذا كان خارج مدة السماح.')) return;
    try {
      await cancelBooking(nextBooking.id);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const handlePay = async (e) => {
    e.preventDefault();
    setPaying(true);
    try {
      await recordPayment(nextBooking.id, payMethod, nextBooking.price);
      load();
    } catch (err) {
      setError(err.message);
    } finally {
      setPaying(false);
    }
  };

  // آخر دفعة مسجّلة لهاد الحجز (إذا في)
  const payment = nextBooking?.payments?.at(-1);

  return (
    <Layout roleLabel="طالب">
      <h1>أهلاً {user.name} 👋</h1>

      <div className="badge" style={{ marginTop: '8px', marginBottom: '20px' }}>
        نوع الغيار: {user.transmission_type === 'manual' ? 'عادي' : 'أوتوماتيك'}
      </div>

      {error && <p className="card">{error}</p>}

      <div className="card">
        <h2>
          <Icon name="calendar" size={17} />
          موعدك القادم
        </h2>
        {!bookings && !error ? (
          <p>جاري التحميل...</p>
        ) : nextBooking ? (
          <>
            <p>
              📅 {formatDateTime(nextBooking.slot.start_time)} <br />
              مع المدرب: {nextBooking.slot.instructor.name} — السعر: {nextBooking.price}$
            </p>

            <button
              className="reject-btn"
              style={{ marginTop: 12 }}
              onClick={handleCancel}
            >
              <Icon name="x" size={14} />
              إلغاء الحجز
            </button>

            <div style={{ marginTop: 16, paddingTop: 16, borderTop: '1px solid var(--border)' }}>
              {!payment ? (
                <form onSubmit={handlePay}>
                  <p className="page-subtitle" style={{ marginBottom: 10 }}>
                    ما في دفعة مسجّلة لهاد الحجز بعد.
                  </p>
                  <div className="form-group">
                    <label>طريقة الدفع</label>
                    <select value={payMethod} onChange={(e) => setPayMethod(e.target.value)}>
                      <option value="cash">نقدًا</option>
                      <option value="sham_cash">شام كاش</option>
                    </select>
                  </div>
                  <button type="submit" className="confirm-button" disabled={paying}>
                    <Icon name="wallet" size={17} />
                    {paying ? 'جاري التسجيل...' : `تسجيل دفع ${nextBooking.price}$`}
                  </button>
                </form>
              ) : (
                <p>
                  حالة الدفع ({methodLabels[payment.method]}):{' '}
                  <span className={`status-tag ${payment.status === 'confirmed' ? 'done' : 'pending'}`}>
                    {payment.status === 'confirmed' ? 'مؤكدة' : 'بانتظار تأكيد المشرف'}
                  </span>
                </p>
              )}
            </div>
          </>
        ) : (
          <p>ما في موعد محجوز حالياً.</p>
        )}
      </div>

      <div className="card">
        <h2>
          <Icon name="target" size={17} />
          تقدمك بالمهارات
        </h2>
        <p>
          {skills ? `أنجزت ${completedSkillsCount} من ${totalSkillsCount} مهارة` : 'جاري التحميل...'}
        </p>
      </div>

      <div className="quick-links">
        <Link to="/student/booking">
          <Icon name="calendar" />
          احجز درس
        </Link>
        <Link to="/student/content">
          <Icon name="book" />
          المحتوى التعليمي
        </Link>
        <Link to="/student/skills">
          <Icon name="target" />
          مهاراتي
        </Link>
        <Link to="/student/messages">
          <Icon name="bell" />
          راسل المشرف
        </Link>
      </div>
    </Layout>
  );
}

export default StudentDashboard;
