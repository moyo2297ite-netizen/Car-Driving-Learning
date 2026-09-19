import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchMySlots, proposeSlot } from '../api/availability';

const statusLabels = { proposed: 'قيد المراجعة', approved: 'معتمد', rejected: 'مرفوض' };
const statusClass = { proposed: 'pending', approved: 'done', rejected: 'locked-tag' };

function formatDateTime(iso) {
  return new Date(iso).toLocaleString('ar-SY', { dateStyle: 'medium', timeStyle: 'short' });
}

function InstructorAvailability() {
  const [slots, setSlots] = useState(null);
  const [error, setError] = useState('');
  const [startTime, setStartTime] = useState('');
  const [endTime, setEndTime] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const load = () => {
    fetchMySlots()
      .then(setSlots)
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const handlePropose = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      // datetime-local بيرجّع نص بدون منطقة زمنية — new Date().toISOString() بتحوّله لصيغة ISO يفهمها الباك اند
      await proposeSlot(new Date(startTime).toISOString(), new Date(endTime).toISOString());
      setStartTime('');
      setEndTime('');
      load();
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Layout roleLabel="مدرب">
      <h1>
        <Icon name="calendar" size={24} />
        أوقات توفري
      </h1>
      <p className="page-subtitle">اقترح وقت جديد — بيصير قابل للحجز بس بعد ما يعتمده المشرف.</p>

      {error && <p className="card">{error}</p>}

      <div className="card">
        <h2>
          <Icon name="calendar" size={17} />
          اقتراح وقت جديد
        </h2>
        <form onSubmit={handlePropose}>
          <div className="form-group">
            <label>من</label>
            <input type="datetime-local" value={startTime} onChange={(e) => setStartTime(e.target.value)} required />
          </div>
          <div className="form-group">
            <label>إلى</label>
            <input type="datetime-local" value={endTime} onChange={(e) => setEndTime(e.target.value)} required />
          </div>
          <button type="submit" className="confirm-button" disabled={submitting}>
            <Icon name="check" size={17} />
            {submitting ? 'جاري الإرسال...' : 'إرسال للمشرف'}
          </button>
        </form>
      </div>

      {!slots && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {slots && (
        <div className="card">
          <h2>
            <Icon name="clock" size={17} />
            أوقاتي ({slots.length})
          </h2>
          <div className="content-list">
            {slots.map((slot) => (
              <div key={slot.id} className="content-item">
                <span>
                  {formatDateTime(slot.start_time)} — {formatDateTime(slot.end_time)}
                  {slot.status === 'rejected' && slot.rejection_reason && (
                    <>
                      <br />
                      <span style={{ color: 'var(--text-faint)', fontSize: 12 }}>السبب: {slot.rejection_reason}</span>
                    </>
                  )}
                </span>
                <span className={`status-tag ${statusClass[slot.status]}`}>{statusLabels[slot.status]}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      <Link className="back-link" to="/instructor">
        <Icon name="chevron" size={16} />
        العودة إلى لوحة المدرب
      </Link>
    </Layout>
  );
}

export default InstructorAvailability;
