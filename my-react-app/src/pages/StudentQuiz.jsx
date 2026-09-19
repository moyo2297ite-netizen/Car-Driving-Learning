import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchContent, submitQuiz } from '../api/content';

function StudentQuiz() {
  const { contentId } = useParams();
  const [content, setContent] = useState(null);
  const [error, setError] = useState('');
  const [answers, setAnswers] = useState({});
  const [submitting, setSubmitting] = useState(false);
  const [result, setResult] = useState(null);

  useEffect(() => {
    fetchContent(contentId)
      .then(setContent)
      .catch((err) => setError(err.message));
  }, [contentId]);

  const handleAnswer = (questionId, option) => {
    setAnswers((prev) => ({ ...prev, [questionId]: option }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      const data = await submitQuiz(content.quiz.id, answers);
      setResult(data);
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  if (error && !content) {
    return (
      <Layout roleLabel="طالب">
        <p className="card">{error}</p>
      </Layout>
    );
  }

  return (
    <Layout roleLabel="طالب">
      <h1>
        <Icon name="target" size={24} />
        كويز: {content?.title ?? '...'}
      </h1>
      <p className="page-subtitle">
        {content?.quiz && `تحتاج ${content.quiz.pass_score}٪ على الأقل للنجاح، والمحاولات غير محدودة.`}
      </p>

      {error && <p className="card">{error}</p>}
      {!content && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {content && !content.quiz && <p className="card">هاد المحتوى ما إله كويز.</p>}

      {content?.quiz && !result && (
        <form onSubmit={handleSubmit}>
          {content.quiz.questions.map((question, index) => (
            <div key={question.id} className="card">
              <h2>
                {index + 1}. {question.question}
              </h2>
              <div className="content-list">
                {question.options.map((option) => (
                  <label
                    key={option}
                    className="content-item"
                    style={{ cursor: 'pointer', fontWeight: 400 }}
                  >
                    <span>{option}</span>
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      checked={answers[question.id] === option}
                      onChange={() => handleAnswer(question.id, option)}
                    />
                  </label>
                ))}
              </div>
            </div>
          ))}

          <button
            type="submit"
            className="confirm-button"
            disabled={submitting || Object.keys(answers).length < content.quiz.questions.length}
          >
            <Icon name="check" size={17} />
            {submitting ? 'جاري التصحيح...' : 'إرسال الإجابات'}
          </button>
        </form>
      )}

      {result && (
        <div className="card">
          <h2>
            <Icon name={result.passed ? 'check' : 'x'} size={17} />
            {result.passed ? 'نجحت! 🎉' : 'لسا ما وصلت للعلامة المطلوبة'}
          </h2>
          <p>
            علامتك: {result.score}٪ (المطلوب: {result.pass_score}٪)
          </p>
          {!result.passed && (
            <p style={{ marginTop: 8 }}>المحاولات غير محدودة — جرّب مرة كمان وقت ما بدك.</p>
          )}
          {!result.passed && (
            <button
              className="confirm-button"
              style={{ marginTop: 12 }}
              onClick={() => {
                setResult(null);
                setAnswers({});
              }}
            >
              حاول مرة كمان
            </button>
          )}
        </div>
      )}

      <Link className="back-link" to="/student/content">
        <Icon name="chevron" size={16} />
        العودة إلى المحتوى التعليمي
      </Link>
    </Layout>
  );
}

export default StudentQuiz;
