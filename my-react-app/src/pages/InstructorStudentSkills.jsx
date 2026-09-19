import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchCertificateEligibility, fetchStudentSkills, updateStudentSkill } from '../api/skills';

function InstructorStudentSkills() {
  const { studentId } = useParams();
  const [skills, setSkills] = useState(null);
  const [error, setError] = useState('');
  const [eligible, setEligible] = useState(false);

  useEffect(() => {
    fetchStudentSkills(studentId)
      .then(setSkills)
      .catch((err) => setError(err.message));

    fetchCertificateEligibility(studentId)
      .then((data) => setEligible(data.eligible))
      .catch(() => {});
  }, [studentId]);

  // التحديث بياخد skill_id (id المهارة نفسها) مش id صف الربط
  const toggleSkill = async (studentSkill) => {
    const nextValue = !studentSkill.is_completed;
    // تفاؤلي (optimistic): نحدّث الواجهة فورًا، وإذا فشل الطلب نرجّعها
    setSkills((prev) =>
      prev.map((s) => (s.id === studentSkill.id ? { ...s, is_completed: nextValue } : s))
    );

    try {
      await updateStudentSkill(studentId, studentSkill.skill.id, nextValue);
      fetchCertificateEligibility(studentId)
        .then((data) => setEligible(data.eligible))
        .catch(() => {});
    } catch (err) {
      setError(err.message);
      setSkills((prev) =>
        prev.map((s) => (s.id === studentSkill.id ? { ...s, is_completed: !nextValue } : s))
      );
    }
  };

  return (
    <Layout roleLabel="مدرب">
      <h1>
        <Icon name="target" size={24} />
        تقييم المهارات — الطالب رقم {studentId}
      </h1>
      <p className="page-subtitle">اضغط على أي مهارة لتبديل حالتها بين منجزة وغير منجزة.</p>

      {error && <p className="card">{error}</p>}
      {!skills && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {eligible && (
        <div className="card" style={{ borderColor: 'var(--accent)' }}>
          <h2>
            <Icon name="check" size={17} />
            مؤهّل لشهادة الإتمام 🎉
          </h2>
          <p>أنجز هاد الطالب كل المهارات المطلوبة.</p>
        </div>
      )}

      {skills && (
        <div className="card">
          <div className="content-list">
            {skills.map((studentSkill) => (
              <button
                key={studentSkill.id}
                className="skill-toggle-row"
                onClick={() => toggleSkill(studentSkill)}
              >
                <span>{studentSkill.skill.name}</span>
                {studentSkill.is_completed ? (
                  <span className="status-tag done">
                    <Icon name="check" size={12} />
                    منجزة
                  </span>
                ) : (
                  <span className="status-tag pending">غير منجزة</span>
                )}
              </button>
            ))}
          </div>
        </div>
      )}

      <Link className="back-link" to="/instructor/students">
        <Icon name="chevron" size={16} />
        العودة إلى قائمة الطلاب
      </Link>
    </Layout>
  );
}

export default InstructorStudentSkills;
