import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { useAuth } from '../hooks/useAuth';
import { fetchAvailableSlots } from '../api/availability';
import { createBooking } from '../api/bookings';

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('ar-SY', { weekday: 'long', day: 'numeric', month: 'long' });
}

function formatTimeRange(startIso, endIso) {
  const opts = { hour: '2-digit', minute: '2-digit' };
  return `${new Date(startIso).toLocaleTimeString('ar-SY', opts)} - ${new Date(endIso).toLocaleTimeString('ar-SY', opts)}`;
}

function StudentBooking() {
  const { user } = useAuth();
  const [slots, setSlots] = useState(null);
  const [error, setError] = useState('');
  const [selectedSlotId, setSelectedSlotId] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    fetchAvailableSlots(user.transmission_type)
      .then(setSlots)
      .catch((err) => setError(err.message));
  }, [user.transmission_type]);

  const handleConfirm = async () => {
    setSubmitting(true);
    setError('');

    try {
      await createBooking(selectedSlotId);
      setSubmitted(true);
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  if (submitted) {
    return (
      <Layout roleLabel="طالب">
        <div className="card">
          <h2>
            <Icon name="check" size={17} />
            تم إرسال طلب الحجز بنجاح
          </h2>
          <p>سيقوم المشرف بتعيين المدرب المناسب، وستصلك رسالة تأكيد قريباً.</p>
          <Link className="back-link" to="/student">
            <Icon name="chevron" size={16} />
            العودة إلى اللوحة الرئيسية
          </Link>
        </div>
      </Layout>
    );
  }

  return (
    <Layout roleLabel="طالب">
      <h1>
        <Icon name="calendar" size={24} />
        حجز درس جديد
      </h1>
      <p className="page-subtitle">
        اختر الوقت المناسب لك. سيقوم المشرف بتعيين المدرب المناسب بعد إرسال الطلب.
      </p>

      {error && <p className="card">{error}</p>}
      {!slots && !error && <p className="page-subtitle">جاري التحميل...</p>}
      {slots && slots.length === 0 && <p className="page-subtitle">ما في أوقات متاحة حاليًا، جرّب لاحقًا.</p>}

      {slots && slots.length > 0 && (
        <>
          <div className="slots-list">
            {slots.map((slot) => (
              <button
                key={slot.id}
                className={`slot-option ${selectedSlotId === slot.id ? 'selected' : ''}`}
                onClick={() => setSelectedSlotId(slot.id)}
              >
                <span className="slot-date">
                  {formatDate(slot.start_time)} — {slot.instructor.name}
                </span>
                <span className="slot-time">{formatTimeRange(slot.start_time, slot.end_time)}</span>
              </button>
            ))}
          </div>

          <button className="confirm-button" disabled={!selectedSlotId || submitting} onClick={handleConfirm}>
            <Icon name="check" size={17} />
            {submitting ? 'جاري الإرسال...' : 'تأكيد الحجز'}
          </button>
        </>
      )}
    </Layout>
  );
}

export default StudentBooking;
