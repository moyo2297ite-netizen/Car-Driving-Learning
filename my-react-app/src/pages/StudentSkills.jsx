import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchMySkills } from '../api/skills';

function StudentSkills() {
  const [skills, setSkills] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchMySkills()
      .then(setSkills)
      .catch((err) => setError(err.message));
  }, []);

  const completedCount = skills?.filter((s) => s.is_completed).length ?? 0;
  const totalCount = skills?.length ?? 0;
  const progressPercent = totalCount ? Math.round((completedCount / totalCount) * 100) : 0;
  const allCompleted = totalCount > 0 && completedCount === totalCount;

  return (
    <Layout roleLabel="طالب">
      <h1>
        <Icon name="target" size={24} />
        مهاراتي العملية
      </h1>
      <p className="page-subtitle">تقييم ثنائي (منجزة / غير منجزة) يحدّثه مدربك.</p>

      {error && <p className="card">{error}</p>}
      {!skills && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {skills && (
        <>
          <div className="card">
            <h2>نسبة الإنجاز</h2>
            <div className="progress-bar-track">
              <div className="progress-bar-fill" style={{ width: `${progressPercent}%` }} />
            </div>
            <p style={{ marginTop: '8px' }}>
              أنجزت {completedCount} من {totalCount} مهارة ({progressPercent}٪)
            </p>

            {allCompleted && (
              <p style={{ color: 'var(--success)', fontWeight: '600', marginTop: '10px' }}>
                🎉 أنجزت جميع المهارات المطلوبة!
              </p>
            )}
          </div>

          <div className="card">
            <h2>تفاصيل المهارات</h2>
            <div className="content-list">
              {skills.map((studentSkill) => (
                <div key={studentSkill.id} className="content-item">
                  <span>{studentSkill.skill.name}</span>
                  {studentSkill.is_completed ? (
                    <span className="status-tag done">
                      <Icon name="check" size={12} />
                      منجزة
                    </span>
                  ) : (
                    <span className="status-tag pending">غير منجزة</span>
                  )}
                </div>
              ))}
            </div>
          </div>
        </>
      )}

      <Link className="back-link" to="/student">
        <Icon name="chevron" size={16} />
        العودة إلى اللوحة الرئيسية
      </Link>
    </Layout>
  );
}

export default StudentSkills;
