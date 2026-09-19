import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { fetchSupervisorDashboard } from '../api/dashboard';
import { approveSlot, rejectSlot, fetchAvailableSlots } from '../api/availability';
import { assignBooking, cancelBooking } from '../api/bookings';
import { confirmPayment } from '../api/payments';

const methodLabels = { cash: 'نقدًا', sham_cash: 'شام كاش' };

function formatDateTime(iso) {
  return new Date(iso).toLocaleString('ar-SY', { dateStyle: 'medium', timeStyle: 'short' });
}

function SupervisorDashboard() {
  const [data, setData] = useState(null);
  const [error, setError] = useState('');
  const [assigningBookingId, setAssigningBookingId] = useState(null);
  const [assignOptions, setAssignOptions] = useState(null);

  const load = () => {
    fetchSupervisorDashboard()
      .then(setData)
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const handleApproveSlot = async (id) => {
    try {
      await approveSlot(id);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleRejectSlot = async (id) => {
    const reason = window.prompt('سبب الرفض؟');
    if (!reason) return;
    try {
      await rejectSlot(id, reason);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleConfirmPayment = async (id) => {
    try {
      await confirmPayment(id);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const openAssignPicker = async (booking) => {
    if (assigningBookingId === booking.id) {
      setAssigningBookingId(null);
      return;
    }
    setAssigningBookingId(booking.id);
    setAssignOptions(null);
    try {
      const options = await fetchAvailableSlots(booking.student.transmission_type);
      setAssignOptions(options);
    } catch (err) {
      setError(err.message);
    }
  };

  const handleAssign = async (bookingId, slotId) => {
    try {
      await assignBooking(bookingId, slotId);
      setAssigningBookingId(null);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleCancel = async (bookingId) => {
    if (!window.confirm('متأكد إنك بدك تلغي هاد الحجز؟')) return;
    try {
      await cancelBooking(bookingId);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const renderBookingRow = (booking) => (
    <div key={booking.id} className="action-row">
      <span>
        {formatDateTime(booking.slot.start_time)} — {booking.student.name}
        {booking.status === 'confirmed' ? ` مع ${booking.slot.instructor.name}` : ' (بانتظار تعيين مدرب)'}
        {' — '}
        <span className={`status-tag ${booking.status === 'confirmed' ? 'done' : 'pending'}`}>
          {booking.status === 'confirmed' ? 'مؤكد' : 'معلّق'}
        </span>
      </span>

      <div className="action-buttons" style={{ position: 'relative' }}>
        {booking.status === 'pending' && (
          <>
            <button className="approve-btn" onClick={() => openAssignPicker(booking)}>
              <Icon name="users" size={14} />
              تعيين مدرب
            </button>

            {assigningBookingId === booking.id && (
              <div className="card" style={{ position: 'absolute', top: '110%', left: 0, zIndex: 5, width: 260 }}>
                {!assignOptions && <p style={{ fontSize: 13 }}>جاري التحميل...</p>}
                {assignOptions && assignOptions.length === 0 && (
                  <p style={{ fontSize: 13 }}>ما في أوقات معتمدة متاحة حاليًا.</p>
                )}
                {assignOptions?.map((slot) => (
                  <button
                    key={slot.id}
                    className="slot-option"
                    style={{ marginBottom: 6, width: '100%' }}
                    onClick={() => handleAssign(booking.id, slot.id)}
                  >
                    <span className="slot-date">{slot.instructor.name}</span>
                    <span className="slot-time">{formatDateTime(slot.start_time)}</span>
                  </button>
                ))}
              </div>
            )}
          </>
        )}
        <button className="reject-btn" onClick={() => handleCancel(booking.id)}>
          <Icon name="x" size={14} />
          إلغاء
        </button>
      </div>
    </div>
  );

  return (
    <Layout roleLabel="مشرف">
      <h1>
        <Icon name="gauge" size={24} />
        الشاشة اليومية
      </h1>
      <p className="page-subtitle">كل شي بحاجة قرار منك اليوم: حجوزات، توفر، ودفعات.</p>

      <div className="quick-links" style={{ marginBottom: 20 }}>
        <Link to="/admin/content">
          <Icon name="book" />
          المحتوى التعليمي
        </Link>
        <Link to="/supervisor/messages">
          <Icon name="bell" />
          رسائل الطلاب
        </Link>
      </div>

      {error && <p className="card">{error}</p>}
      {!data && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {data && (
        <>
          <div className="card">
            <h2>
              <Icon name="calendar" size={17} />
              حجوزات اليوم ({data.today_bookings.length})
            </h2>
            {data.today_bookings.length === 0 ? <p>لا توجد حجوزات اليوم.</p> : data.today_bookings.map(renderBookingRow)}
          </div>

          <div className="card">
            <h2>
              <Icon name="calendar" size={17} />
              حجوزات قادمة ({data.upcoming_bookings.length})
            </h2>
            {data.upcoming_bookings.length === 0 ? (
              <p>لا توجد حجوزات قادمة.</p>
            ) : (
              data.upcoming_bookings.map(renderBookingRow)
            )}
          </div>

          <div className="card">
            <h2>
              <Icon name="clock" size={17} />
              طلبات توفر بانتظار الاعتماد ({data.pending_slots.length})
            </h2>
            {data.pending_slots.length === 0 ? (
              <p>لا توجد طلبات معلّقة.</p>
            ) : (
              data.pending_slots.map((a) => (
                <div key={a.id} className="action-row">
                  <span>
                    {a.instructor.name} — {formatDateTime(a.start_time)}
                  </span>
                  <div className="action-buttons">
                    <button className="approve-btn" onClick={() => handleApproveSlot(a.id)}>
                      <Icon name="check" size={14} />
                      اعتماد
                    </button>
                    <button className="reject-btn" onClick={() => handleRejectSlot(a.id)}>
                      <Icon name="x" size={14} />
                      رفض
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>

          <div className="card">
            <h2>
              <Icon name="wallet" size={17} />
              دفعات بحاجة تأكيد ({data.pending_payments.length})
            </h2>
            {data.pending_payments.length === 0 ? (
              <p>لا توجد دفعات معلّقة.</p>
            ) : (
              data.pending_payments.map((p) => (
                <div key={p.id} className="action-row">
                  <span>
                    {p.booking.student.name} — {p.amount}$ ({methodLabels[p.method]})
                  </span>
                  <div className="action-buttons">
                    <button className="approve-btn" onClick={() => handleConfirmPayment(p.id)}>
                      <Icon name="check" size={14} />
                      تأكيد الاستلام
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        </>
      )}
    </Layout>
  );
}

export default SupervisorDashboard;
