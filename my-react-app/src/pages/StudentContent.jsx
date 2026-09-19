import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchContentCategories } from '../api/contentCategories';
import { fetchMyContentProgress } from '../api/contentProgress';
import { markContentViewed } from '../api/content';

function StudentContent() {
  const [categories, setCategories] = useState(null);
  const [progress, setProgress] = useState(null);
  const [error, setError] = useState('');

  const load = () => {
    // نجيب الشجرة (فئات + محتوى) وتقدّم الطالب بطلبين متوازيين —
    // Promise.all بتخليهم يترسلوا سوا بدل واحد ورا التاني
    Promise.all([fetchContentCategories(), fetchMyContentProgress()])
      .then(([categoriesData, progressData]) => {
        setCategories(categoriesData);
        setProgress(progressData);
      })
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const handleMarkViewed = async (contentId) => {
    try {
      await markContentViewed(contentId);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <Layout roleLabel="طالب">
      <h1>
        <Icon name="book" size={24} />
        المحتوى التعليمي
      </h1>
      <p className="page-subtitle">شاهد المحتوى وحل الكويزات المرتبطة به لفتح ما بعدها.</p>

      {error && <p className="card">{error}</p>}
      {(!categories || !progress) && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {categories &&
        progress &&
        categories.map((category) => (
          <div key={category.id} className="card">
            <h2>
              {category.name}
              {category.is_sequential && <span className="badge sequential">تسلسل إجباري</span>}
            </h2>

            {category.contents.length === 0 ? (
              <p>ما في محتوى بهاي الفئة بعد.</p>
            ) : (
              <div className="content-list">
                {category.contents.map((item, index) => {
                  const itemProgress = progress.find((p) => p.content.id === item.id);
                  const completed = itemProgress?.completed ?? false;
                  const quizPassed = item.quiz
                    ? (itemProgress?.quiz_score ?? 0) >= item.quiz.pass_score
                    : completed;

                  const previousItem = category.contents[index - 1];
                  const previousProgress = previousItem
                    ? progress.find((p) => p.content.id === previousItem.id)
                    : null;
                  const previousQuizPassed = previousItem?.quiz
                    ? (previousProgress?.quiz_score ?? 0) >= previousItem.quiz.pass_score
                    : Boolean(previousProgress?.completed);
                  const isLocked = category.is_sequential && previousItem && !previousQuizPassed;

                  return (
                    <div key={item.id} className={`content-item ${isLocked ? 'locked' : ''}`}>
                      <span>{item.title}</span>

                      <span style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        {completed ? (
                          <span className="status-tag done">
                            <Icon name="check" size={12} />
                            {quizPassed || !item.quiz ? 'تم الاطلاع' : 'شوهد — بانتظار اجتياز الكويز'}
                          </span>
                        ) : isLocked ? (
                          <span className="status-tag locked-tag">
                            <Icon name="lock" size={12} />
                            مقفل
                          </span>
                        ) : (
                          <>
                            <span className="status-tag pending">لم يُشاهَد بعد</span>
                            <button className="approve-btn" onClick={() => handleMarkViewed(item.id)}>
                              <Icon name="check" size={14} />
                              شاهدت المحتوى
                            </button>
                          </>
                        )}

                        {!isLocked && item.quiz && (!quizPassed || !completed) && (
                          <Link className="approve-btn" to={`/student/content/${item.id}/quiz`}>
                            <Icon name="target" size={14} />
                            حل الكويز
                          </Link>
                        )}
                      </span>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        ))}

      <Link className="back-link" to="/student">
        <Icon name="chevron" size={16} />
        العودة إلى اللوحة الرئيسية
      </Link>
    </Layout>
  );
}

export default StudentContent;
