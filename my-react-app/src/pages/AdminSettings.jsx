import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchSettings, updateSettings } from '../api/settings';

function AdminSettings() {
  const [settings, setSettings] = useState(null);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchSettings()
      .then(setSettings)
      .catch((err) => setError(err.message));
  }, []);

  const handleChange = (field, value) => {
    setSettings((prev) => ({ ...prev, [field]: value }));
    setSaved(false);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError('');

    try {
      const updated = await updateSettings(settings);
      setSettings(updated);
      setSaved(true);
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Layout roleLabel="أدمن">
      <h1>
        <Icon name="settings" size={24} />
        الإعدادات العامة
      </h1>
      <p className="page-subtitle">
        هذه القيم تُطبَّق على كامل النظام فوراً بعد الحفظ، ولا تؤثر على الحجوزات السابقة
        (المخزّنة كلقطة سعرية مستقلة).
      </p>

      {error && <p className="card">{error}</p>}

      {!settings && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {settings && (
        <form onSubmit={handleSave} className="card">
          <div className="form-group">
            <label>سعر الدرس الواحد ($)</label>
            <input
              type="number"
              min="0"
              value={settings.lesson_price}
              onChange={(e) => handleChange('lesson_price', Number(e.target.value))}
            />
          </div>

          <div className="form-group">
            <label>مدة السماح للإلغاء بدون غرامة (بالساعات)</label>
            <input
              type="number"
              min="0"
              value={settings.cancellation_grace_hours}
              onChange={(e) =>
                handleChange('cancellation_grace_hours', Number(e.target.value))
              }
            />
          </div>

          <div className="form-group">
            <label>نسبة الغرامة عند الإلغاء المتأخر (%)</label>
            <input
              type="number"
              min="0"
              max="100"
              value={settings.cancellation_penalty_percent}
              onChange={(e) =>
                handleChange('cancellation_penalty_percent', Number(e.target.value))
              }
            />
          </div>

          <div className="form-group">
            <label>حساب شام كاش الخاص بالمدرسة</label>
            <input
              type="text"
              value={settings.sham_cash_account ?? ''}
              onChange={(e) => handleChange('sham_cash_account', e.target.value)}
              placeholder="رقم الحساب أو المعرّف"
            />
          </div>

          <button type="submit" className="confirm-button" disabled={saving}>
            <Icon name="check" size={17} />
            {saving ? 'جاري الحفظ...' : 'حفظ الإعدادات'}
          </button>

          {saved && (
            <p style={{ color: 'var(--success)', marginTop: '10px', fontWeight: '600' }}>
              ✓ تم حفظ الإعدادات بنجاح
            </p>
          )}
        </form>
      )}
    </Layout>
  );
}

export default AdminSettings;
