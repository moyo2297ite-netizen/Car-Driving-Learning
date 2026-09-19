import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import Icon from '../components/Icon';

const roleRedirects = {
  admin: '/admin',
  supervisor: '/supervisor',
  instructor: '/instructor',
  student: '/student',
};

function Login() {
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  // submitting/error هيك حالة بسيطة منحتاجها بأي فورم بيتكلم مع سيرفر:
  // وقت الإرسال منعطّل الزر ومنبيّن "جاري الدخول"، وإذا رجع خطأ منعرضه للمستخدم
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSubmitting(true);

    try {
      const user = await login(phone, password);
      navigate(roleRedirects[user.role] ?? '/');
    } catch (err) {
      // لارافيل بيرجّع 422 مع رسالة "بيانات الدخول غير صحيحة" لو الرقم/الباسورد غلط
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="auth-screen">
      <aside className="auth-brand">
        <div className="auth-brand-mark">
          <Icon name="steering" size={26} />
          <span>سويق</span>
        </div>

        <div className="auth-brand-copy">
          <h1>
            رحلتك لترخيص <em>القيادة</em>، منظّمة من أول درس لآخر مهارة.
          </h1>
          <p>
            حجز الدروس، المحتوى التعليمي، وتتبع تقدّمك العملي — كل شي
            بمكان واحد لطلاب ومدربي مدرسة القيادة.
          </p>
        </div>

        <div className="auth-brand-features">
          <div>
            <Icon name="calendar" size={16} />
            جدولة حجوزات يعتمدها المشرف تلقائياً
          </div>
          <div>
            <Icon name="book" size={16} />
            محتوى تعليمي متسلسل مع كويزات
          </div>
          <div>
            <Icon name="target" size={16} />
            تتبّع مهارات عملية لحظة بلحظة
          </div>
        </div>
      </aside>

      <div className="auth-form-side">
        <div className="login-container">
          <p className="eyebrow">تسجيل الدخول</p>
          <h1>أهلاً فيك من جديد</h1>
          <p>سجّل دخولك لمتابعة حسابك حسب دورك بالمدرسة.</p>

          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label>رقم الهاتف</label>
              <input
                type="tel"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                placeholder="09xxxxxxxx"
                required
              />
            </div>

            <div className="form-group">
              <label>كلمة السر</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>

            {error && (
              <p style={{ color: 'var(--accent)', fontSize: 13, marginBottom: 14 }}>{error}</p>
            )}

            <button type="submit" disabled={submitting}>
              {submitting ? 'جاري الدخول...' : 'دخول'}
            </button>
          </form>

          <p style={{ marginTop: 18, fontSize: 13 }}>
            طالب جديد؟{' '}
            <Link to="/register" style={{ color: 'var(--accent)', fontWeight: 600 }}>
              أنشئ حسابك
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

export default Login;
