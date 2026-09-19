import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Icon from '../components/Icon';
import { register } from '../api/auth';

// هاي الصفحة بس للطالب — المدرب والمشرف حساباتهم بينشئهم الأدمن من
// لوحته (شوف AdminStaff.jsx)، ما إلهم Sign Up مستقل.
function SignUp() {
  const [form, setForm] = useState({
    name: '',
    phone: '',
    email: '',
    password: '',
    passwordConfirmation: '',
    transmissionType: 'manual',
  });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [done, setDone] = useState(false);

  const navigate = useNavigate();

  const handleChange = (field, value) => setForm((prev) => ({ ...prev, [field]: value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSubmitting(true);

    try {
      await register(form);
      setDone(true);
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  if (done) {
    return (
      <div className="auth-screen">
        <div className="auth-form-side" style={{ gridColumn: '1 / -1' }}>
          <div className="login-container">
            <div className="card">
              <h2>
                <Icon name="check" size={17} />
                تم إنشاء حسابك بنجاح
              </h2>
              <p>تقدر هلق تسجّل دخولك برقم هاتفك وكلمة السر.</p>
              <button className="confirm-button" style={{ marginTop: 16 }} onClick={() => navigate('/')}>
                الذهاب لتسجيل الدخول
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="auth-screen">
      <aside className="auth-brand">
        <div className="auth-brand-mark">
          <Icon name="steering" size={26} />
          <span>سويق</span>
        </div>

        <div className="auth-brand-copy">
          <h1>
            سجّل حسابك، وابدأ <em>أول درس</em> بأسبوع.
          </h1>
          <p>نوع الغيار (عادي أو أوتوماتيك) بيتحدد مرة وحدة بالتسجيل، وبيبقى ثابت لكل دروسك.</p>
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
        </div>
      </aside>

      <div className="auth-form-side">
        <div className="login-container">
          <p className="eyebrow">إنشاء حساب طالب</p>
          <h1>أهلاً فيك بمدرسة سويق</h1>
          <p>عبّي بياناتك لتبلش رحلتك.</p>

          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label>الاسم الكامل</label>
              <input value={form.name} onChange={(e) => handleChange('name', e.target.value)} required />
            </div>

            <div className="form-group">
              <label>رقم الهاتف</label>
              <input
                type="tel"
                value={form.phone}
                onChange={(e) => handleChange('phone', e.target.value)}
                placeholder="09xxxxxxxx"
                required
              />
            </div>

            <div className="form-group">
              <label>البريد الإلكتروني (اختياري)</label>
              <input type="email" value={form.email} onChange={(e) => handleChange('email', e.target.value)} />
            </div>

            <div className="form-group">
              <label>كلمة السر</label>
              <input
                type="password"
                value={form.password}
                onChange={(e) => handleChange('password', e.target.value)}
                required
              />
            </div>

            <div className="form-group">
              <label>تأكيد كلمة السر</label>
              <input
                type="password"
                value={form.passwordConfirmation}
                onChange={(e) => handleChange('passwordConfirmation', e.target.value)}
                required
              />
            </div>

            <div className="form-group">
              <label>نوع الغيار (ثابت بعد التسجيل)</label>
              <div className="role-picker" style={{ gridTemplateColumns: '1fr 1fr' }}>
                <button
                  type="button"
                  className={`role-option ${form.transmissionType === 'manual' ? 'active' : ''}`}
                  onClick={() => handleChange('transmissionType', 'manual')}
                >
                  عادي
                </button>
                <button
                  type="button"
                  className={`role-option ${form.transmissionType === 'automatic' ? 'active' : ''}`}
                  onClick={() => handleChange('transmissionType', 'automatic')}
                >
                  أوتوماتيك
                </button>
              </div>
            </div>

            {error && <p style={{ color: 'var(--accent)', fontSize: 13, marginBottom: 14 }}>{error}</p>}

            <button type="submit" disabled={submitting}>
              {submitting ? 'جاري الإنشاء...' : 'إنشاء الحساب'}
            </button>
          </form>

          <p style={{ marginTop: 18, fontSize: 13 }}>
            عندك حساب؟{' '}
            <Link to="/" style={{ color: 'var(--accent)', fontWeight: 600 }}>
              سجّل دخولك
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

export default SignUp;
