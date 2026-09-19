import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import QuizForm from '../components/QuizForm';
import { createContent, createContentCategory, fetchContentCategories } from '../api/contentCategories';

const emptyCategoryForm = { name: '', isSequential: false };
const emptyContentForm = { categoryId: '', title: '', type: 'video', url: '' };

function AdminContent() {
  const [categories, setCategories] = useState(null);
  const [error, setError] = useState('');
  const [categoryForm, setCategoryForm] = useState(emptyCategoryForm);
  const [contentForm, setContentForm] = useState(emptyContentForm);
  const [submittingCategory, setSubmittingCategory] = useState(false);
  const [submittingContent, setSubmittingContent] = useState(false);
  const [quizFormContentId, setQuizFormContentId] = useState(null);

  const load = () => {
    fetchContentCategories()
      .then(setCategories)
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const handleAddCategory = async (e) => {
    e.preventDefault();
    setSubmittingCategory(true);
    setError('');

    try {
      await createContentCategory(categoryForm);
      setCategoryForm(emptyCategoryForm);
      load();
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmittingCategory(false);
    }
  };

  const handleAddContent = async (e) => {
    e.preventDefault();
    setSubmittingContent(true);
    setError('');

    try {
      await createContent(contentForm);
      setContentForm(emptyContentForm);
      load();
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmittingContent(false);
    }
  };

  return (
    <Layout roleLabel="أدمن">
      <h1>
        <Icon name="book" size={24} />
        المحتوى التعليمي
      </h1>
      <p className="page-subtitle">الفئات ديناميكية بالكامل — أضف أي عدد تحتاجه.</p>

      {error && <p className="card">{error}</p>}
      {!categories && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {categories &&
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
                {category.contents.map((item) => (
                  <div key={item.id}>
                    <div className="content-item">
                      <span>{item.title}</span>
                      <span style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <span className="status-tag pending">{item.type === 'video' ? 'فيديو' : 'صورة'}</span>
                        {item.quiz ? (
                          <span className="status-tag done">
                            <Icon name="check" size={12} />
                            عنده كويز
                          </span>
                        ) : (
                          <button
                            className="approve-btn"
                            onClick={() => setQuizFormContentId(quizFormContentId === item.id ? null : item.id)}
                          >
                            <Icon name="target" size={14} />
                            إضافة كويز
                          </button>
                        )}
                      </span>
                    </div>

                    {quizFormContentId === item.id && (
                      <QuizForm
                        contentId={item.id}
                        onCreated={() => {
                          setQuizFormContentId(null);
                          load();
                        }}
                        onCancel={() => setQuizFormContentId(null)}
                      />
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        ))}

      <div className="card">
        <h2>
          <Icon name="settings" size={17} />
          إضافة فئة جديدة
        </h2>
        <form onSubmit={handleAddCategory}>
          <div className="form-group">
            <label>اسم الفئة</label>
            <input
              value={categoryForm.name}
              onChange={(e) => setCategoryForm((prev) => ({ ...prev, name: e.target.value }))}
              required
            />
          </div>
          <div className="form-group">
            <label style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
              <input
                type="checkbox"
                checked={categoryForm.isSequential}
                onChange={(e) =>
                  setCategoryForm((prev) => ({ ...prev, isSequential: e.target.checked }))
                }
              />
              محتوى متسلسل (لازم يُشاهَد بالترتيب)
            </label>
          </div>
          <button type="submit" className="confirm-button" disabled={submittingCategory}>
            <Icon name="check" size={17} />
            {submittingCategory ? 'جاري الإضافة...' : 'إضافة الفئة'}
          </button>
        </form>
      </div>

      {categories && categories.length > 0 && (
        <div className="card">
          <h2>
            <Icon name="book" size={17} />
            إضافة محتوى لفئة موجودة
          </h2>
          <form onSubmit={handleAddContent}>
            <div className="form-group">
              <label>الفئة</label>
              <select
                value={contentForm.categoryId}
                onChange={(e) => setContentForm((prev) => ({ ...prev, categoryId: e.target.value }))}
                required
              >
                <option value="" disabled>
                  اختر فئة
                </option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>
            <div className="form-group">
              <label>العنوان</label>
              <input
                value={contentForm.title}
                onChange={(e) => setContentForm((prev) => ({ ...prev, title: e.target.value }))}
                required
              />
            </div>
            <div className="form-group">
              <label>النوع</label>
              <select
                value={contentForm.type}
                onChange={(e) => setContentForm((prev) => ({ ...prev, type: e.target.value }))}
              >
                <option value="video">فيديو</option>
                <option value="image">صورة</option>
              </select>
            </div>
            <div className="form-group">
              <label>الرابط (URL)</label>
              <input
                value={contentForm.url}
                onChange={(e) => setContentForm((prev) => ({ ...prev, url: e.target.value }))}
                placeholder="https://..."
                required
              />
            </div>
            <button type="submit" className="confirm-button" disabled={submittingContent}>
              <Icon name="check" size={17} />
              {submittingContent ? 'جاري الإضافة...' : 'إضافة المحتوى'}
            </button>
          </form>
        </div>
      )}
    </Layout>
  );
}

export default AdminContent;
