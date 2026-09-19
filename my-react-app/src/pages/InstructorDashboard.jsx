import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import { Link } from 'react-router-dom';
import Icon from '../components/Icon';
import { fetchInstructorDashboard } from '../api/dashboard';
import { completeBooking } from '../api/bookings';

function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('ar-SY', { hour: '2-digit', minute: '2-digit' });
}

function InstructorDashboard() {
  const [data, setData] = useState(null);
  const [error, setError] = useState('');

  const load = () => {
    fetchInstructorDashboard()
      .then(setData)
      .catch((err) => setError(err.message));
  };

  useEffect(load, []);

  const handleComplete = async (bookingId) => {
    try {
      await completeBooking(bookingId);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const todayBookings = data?.today_bookings ?? [];

  return (
    <Layout roleLabel="مدرب">
      <h1>
        <Icon name="gauge" size={24} />
        جدولي اليوم
      </h1>
      <p className="page-subtitle">دروسك المجدولة اليوم فقط — بدون تفاصيل الأدمن أو المشرف.</p>

      <div className="quick-links" style={{ marginBottom: 20 }}>
        <Link to="/instructor/students">
          <Icon name="users" />
          طلابي ومهاراتهم
        </Link>
        <Link to="/instructor/availability">
          <Icon name="calendar" />
          أوقات توفري
        </Link>
      </div>

      {error && <p className="card">{error}</p>}
      {!data && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {data && (
        <div className="card">
          <h2>
            <Icon name="calendar" size={17} />
            دروس اليوم ({todayBookings.length})
          </h2>
          {todayBookings.length === 0 ? (
            <p>ما عندك دروس اليوم.</p>
          ) : (
            todayBookings.map((booking) => (
              <div key={booking.id} className="action-row">
                <span>
                  🕐 {formatTime(booking.slot.start_time)} — {booking.student.name}{' '}
                  <span className={`status-tag ${booking.status === 'completed' ? 'done' : 'pending'}`}>
                    {booking.status === 'confirmed' ? 'مؤكد' : booking.status === 'completed' ? 'منتهي' : booking.status}
                  </span>
                </span>
                {booking.status === 'confirmed' && (
                  <div className="action-buttons">
                    <button className="approve-btn" onClick={() => handleComplete(booking.id)}>
                      <Icon name="check" size={14} />
                      إنهاء الدرس
                    </button>
                  </div>
                )}
              </div>
            ))
          )}
        </div>
      )}
    </Layout>
  );
}

export default InstructorDashboard;
