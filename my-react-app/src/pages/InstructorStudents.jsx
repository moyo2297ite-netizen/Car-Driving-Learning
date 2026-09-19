import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchInstructorStudents } from '../api/instructorStudents';

function InstructorStudents() {
  const [students, setStudents] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchInstructorStudents()
      .then(setStudents)
      .catch((err) => setError(err.message));
  }, []);

  return (
    <Layout roleLabel="مدرب">
      <h1>
        <Icon name="users" size={24} />
        طلابي
      </h1>
      <p className="page-subtitle">اضغط على أي طالب لتقييم مهاراته العملية.</p>

      {error && <p className="card">{error}</p>}
      {!students && !error && <p className="page-subtitle">جاري التحميل...</p>}
      {students && students.length === 0 && <p className="page-subtitle">ما في طلاب مرتبطين فيك لسا.</p>}

      {students && students.length > 0 && (
        <div className="content-list">
          {students.map((student) => (
            <Link
              key={student.id}
              to={`/instructor/students/${student.id}/skills`}
              className="content-item student-link"
            >
              <span>{student.name}</span>
              <span className="badge">
                {student.transmission_type === 'manual' ? 'عادي' : 'أوتوماتيك'}
              </span>
            </Link>
          ))}
        </div>
      )}
    </Layout>
  );
}

export default InstructorStudents;
