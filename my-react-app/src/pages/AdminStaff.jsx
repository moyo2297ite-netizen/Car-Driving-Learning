import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { createStaff, deleteStaff, fetchStaff } from '../api/staff';

const emptyForm = {
  name: '',
  phone: '',
  email: '',
  password: '',
  passwordConfirmation: '',
  teachesManual: true,
  teachesAutomatic: true,
};

// مكوّن واحد مشترك بين صفحتي "إدارة المدربين" و"إدارة المشرفين" — نفس
// الشكل بالضبط، الفرق بس قيمة role. هاي فكرة أساسية برياكت: لما تلاقي
// نفسك بتنسخ نفس الصفحة مرتين وبتغيّر كلمة وحدة بس، اعملها مكوّن واحد
// وخد الفرق كـ prop (شوف AdminInstructors.jsx وAdminSupervisors.jsx).
function AdminStaff({ role, title, addLabel }) {
  const [staff, setStaff] = useState(null);
  const [error, setError] = useState('');
  const [form, setForm] = useState(emptyForm);
  const [formError, setFormError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const load = () => {
    fetchStaff()
      .then((all) => setStaff(all.filter((person) => person.role === role)))
      .catch((err) => setError(err.message));
  };

  useEffect(load, [role]);

  const handleChange = (field, value) => setForm((prev) => ({ ...prev, [field]: value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setFormError('');
    setSubmitting(true);

    try {
      await createStaff({ ...form, role });
      setForm(emptyForm);
      load();
    } catch (err) {
      setFormError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (person) => {
    if (!window.confirm(`متأكد إنك بدك تحذف ${person.name}؟`)) return;
    await deleteStaff(person.id);
    load();
  };

  return (
    <Layout roleLabel="أدمن">
      <h1>
        <Icon name="users" size={24} />
        {title}
      </h1>
      <p className="page-subtitle">إضافة أو حذف الحسابات، مباشرة من قاعدة البيانات الحقيقية.</p>

      {error && <p className="card">{error}</p>}

      <div className="card">
        <h2>
          <Icon name="users" size={17} />
          القائمة الحالية
        </h2>
        {!staff && !error && <p>جاري التحميل...</p>}
        {staff && staff.length === 0 && <p>ما في حسابات بعد.</p>}
        {staff && staff.length > 0 && (
          <div className="content-list">
            {staff.map((person) => (
              <div key={person.id} className="content-item">
                <span>
                  {person.name} — {person.phone}
                </span>
                <button className="reject-btn" onClick={() => handleDelete(person)}>
                  <Icon name="x" size={14} />
                  حذف
                </button>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="card">
        <h2>
          <Icon name="users" size={17} />
          {addLabel}
        </h2>
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>الاسم الكامل</label>
            <input value={form.name} onChange={(e) => handleChange('name', e.target.value)} required />
          </div>

          <div className="form-group">
            <label>رقم الهاتف (يُستخدم لتسجيل الدخول)</label>
            <input
              type="tel"
              value={form.phone}
              onChange={(e) => handleChange('phone', e.target.value)}
              placeholder="09xxxxxxxx"
              required
            />
          </div>

          <div className="form-group">
            <label>البريد الإلكتروني (اختياري، للإشعارات فقط)</label>
            <input
              type="email"
              value={form.email}
              onChange={(e) => handleChange('email', e.target.value)}
            />
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

          {role === 'instructor' && (
            <div className="form-group">
              <label>نوع الغيار يلي بيدعمه</label>
              <div style={{ display: 'flex', gap: 16 }}>
                <label style={{ display: 'flex', gap: 6, alignItems: 'center', fontWeight: 400 }}>
                  <input
                    type="checkbox"
                    checked={form.teachesManual}
                    onChange={(e) => handleChange('teachesManual', e.target.checked)}
                  />
                  عادي
                </label>
                <label style={{ display: 'flex', gap: 6, alignItems: 'center', fontWeight: 400 }}>
                  <input
                    type="checkbox"
                    checked={form.teachesAutomatic}
                    onChange={(e) => handleChange('teachesAutomatic', e.target.checked)}
                  />
                  أوتوماتيك
                </label>
              </div>
            </div>
          )}

          {formError && (
            <p style={{ color: 'var(--accent)', fontSize: 13, marginBottom: 14 }}>{formError}</p>
          )}

          <button type="submit" className="confirm-button" disabled={submitting}>
            <Icon name="check" size={17} />
            {submitting ? 'جاري الإضافة...' : addLabel}
          </button>
        </form>
      </div>
    </Layout>
  );
}

export default AdminStaff;
