import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { createSkill, deleteSkill, fetchSkills } from '../api/skills';

function AdminSkills() {
  const [skills, setSkills] = useState(null);
  const [error, setError] = useState('');
  const [name, setName] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const load = () => {
    fetchSkills()
      .then(setSkills)
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const handleAdd = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      await createSkill(name);
      setName('');
      load();
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (skill) => {
    if (!window.confirm(`متأكد إنك بدك تحذف "${skill.name}"؟`)) return;
    await deleteSkill(skill.id);
    load();
  };

  return (
    <Layout roleLabel="أدمن">
      <h1>
        <Icon name="target" size={24} />
        قائمة المهارات العملية
      </h1>
      <p className="page-subtitle">
        هاي القائمة يلي المدربين بيقيّموا عليها طلابهم — عدّلها وقت ما بدك بدون تعديل كود.
      </p>

      {error && <p className="card">{error}</p>}
      {!skills && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {skills && (
        <div className="card">
          <h2>
            <Icon name="target" size={17} />
            المهارات الحالية ({skills.length})
          </h2>
          <div className="content-list">
            {skills.map((skill) => (
              <div key={skill.id} className="content-item">
                <span>{skill.name}</span>
                <button className="reject-btn" onClick={() => handleDelete(skill)}>
                  <Icon name="x" size={14} />
                  حذف
                </button>
              </div>
            ))}
          </div>
        </div>
      )}

      <div className="card">
        <h2>
          <Icon name="check" size={17} />
          إضافة مهارة جديدة
        </h2>
        <form onSubmit={handleAdd}>
          <div className="form-group">
            <label>اسم المهارة</label>
            <input value={name} onChange={(e) => setName(e.target.value)} required />
          </div>
          <button type="submit" className="confirm-button" disabled={submitting}>
            <Icon name="check" size={17} />
            {submitting ? 'جاري الإضافة...' : 'إضافة'}
          </button>
        </form>
      </div>
    </Layout>
  );
}

export default AdminSkills;
