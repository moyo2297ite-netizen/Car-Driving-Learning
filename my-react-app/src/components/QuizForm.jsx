import { useState } from 'react';
import Icon from './Icon';
import { createQuiz } from '../api/content';

const emptyQuestion = () => ({ question: '', options: ['', ''], correctAnswer: '' });

// فورم إنشاء كويز لمحتوى معيّن. مكوّن منفصل عن AdminContent.jsx لأنه
// منطقه (أسئلة ديناميكية، خيارات ديناميكية) مستقل تمامًا عن باقي الصفحة.
function QuizForm({ contentId, onCreated, onCancel }) {
  const [passScore, setPassScore] = useState(70);
  const [questions, setQuestions] = useState([emptyQuestion()]);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  const updateQuestion = (index, changes) => {
    setQuestions((prev) => prev.map((q, i) => (i === index ? { ...q, ...changes } : q)));
  };

  const updateOption = (qIndex, oIndex, value) => {
    setQuestions((prev) =>
      prev.map((q, i) => {
        if (i !== qIndex) return q;
        const options = q.options.map((opt, j) => (j === oIndex ? value : opt));
        return { ...q, options };
      })
    );
  };

  const addOption = (qIndex) => {
    setQuestions((prev) => prev.map((q, i) => (i === qIndex ? { ...q, options: [...q.options, ''] } : q)));
  };

  const addQuestion = () => setQuestions((prev) => [...prev, emptyQuestion()]);
  const removeQuestion = (index) => setQuestions((prev) => prev.filter((_, i) => i !== index));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      await createQuiz(contentId, {
        passScore: Number(passScore),
        questions: questions.map((q) => ({
          question: q.question,
          options: q.options.filter((opt) => opt.trim() !== ''),
          correct_answer: q.correctAnswer,
        })),
      });
      onCreated();
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="card" style={{ marginTop: 10 }}>
      <div className="form-group">
        <label>علامة النجاح (%)</label>
        <input
          type="number"
          min="1"
          max="100"
          value={passScore}
          onChange={(e) => setPassScore(e.target.value)}
          required
        />
      </div>

      {questions.map((q, qIndex) => (
        <div key={qIndex} className="card" style={{ background: 'var(--surface-2)' }}>
          <div className="form-group">
            <label>السؤال {qIndex + 1}</label>
            <input
              value={q.question}
              onChange={(e) => updateQuestion(qIndex, { question: e.target.value })}
              required
            />
          </div>

          {q.options.map((option, oIndex) => (
            <div className="form-group" key={oIndex}>
              <label>خيار {oIndex + 1}</label>
              <input value={option} onChange={(e) => updateOption(qIndex, oIndex, e.target.value)} required />
            </div>
          ))}

          <button type="button" className="btn-ghost" onClick={() => addOption(qIndex)} style={{ marginBottom: 12 }}>
            + إضافة خيار
          </button>

          <div className="form-group">
            <label>الإجابة الصحيحة (انسخ نص الخيار الصحيح بالضبط)</label>
            <input
              value={q.correctAnswer}
              onChange={(e) => updateQuestion(qIndex, { correctAnswer: e.target.value })}
              required
            />
          </div>

          {questions.length > 1 && (
            <button type="button" className="reject-btn" onClick={() => removeQuestion(qIndex)}>
              <Icon name="x" size={14} />
              حذف السؤال
            </button>
          )}
        </div>
      ))}

      <button type="button" className="btn-ghost" onClick={addQuestion} style={{ marginBottom: 14 }}>
        + إضافة سؤال
      </button>

      {error && <p style={{ color: 'var(--accent)', fontSize: 13, marginBottom: 14 }}>{error}</p>}

      <div style={{ display: 'flex', gap: 8 }}>
        <button type="submit" className="confirm-button" disabled={submitting}>
          <Icon name="check" size={17} />
          {submitting ? 'جاري الحفظ...' : 'حفظ الكويز'}
        </button>
        <button type="button" className="btn-ghost" onClick={onCancel}>
          إلغاء
        </button>
      </div>
    </form>
  );
}

export default QuizForm;
