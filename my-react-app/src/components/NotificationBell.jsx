import { useEffect, useState } from 'react';
import Icon from './Icon';
import { fetchNotifications, markNotificationRead } from '../api/notifications';

function formatTime(iso) {
  return new Date(iso).toLocaleString('ar-SY', { dateStyle: 'short', timeStyle: 'short' });
}

// كل نوع إشعار (type) بيوصل بشكل مختلف من الباك اند — هاي الدالة
// بتحوّله لجملة عربية مفهومة. أي نوع جديد تضيفه بالباك اند لاحقًا،
// ضيفله سطر هون.
function describeNotification(notification) {
  const { type, data } = notification;

  switch (type) {
    case 'booking_confirmed':
      return `تم تأكيد حجزك مع ${data.instructor_name} — ${formatTime(data.start_time)}`;
    case 'booking_cancelled':
      return data.penalty_amount
        ? `تم إلغاء حجزك، وفُرضت غرامة ${data.penalty_amount}$`
        : 'تم إلغاء حجزك بدون غرامة';
    case 'booking_reminder':
      return `تذكير: عندك درس ${formatTime(data.start_time)}`;
    case 'slot_reviewed':
      return data.status === 'approved'
        ? 'تم اعتماد وقت التوفر يلي اقترحته'
        : `تم رفض وقت التوفر — السبب: ${data.rejection_reason ?? '—'}`;
    case 'payment_confirmed':
      return `تم تأكيد استلام دفعة بقيمة ${data.amount}$`;
    default:
      return 'إشعار جديد';
  }
}

function NotificationBell() {
  const [open, setOpen] = useState(false);
  const [notifications, setNotifications] = useState(null);
  const [unreadCount, setUnreadCount] = useState(0);

  const load = () => {
    fetchNotifications()
      .then((data) => {
        setNotifications(data.notifications);
        setUnreadCount(data.unread_count);
      })
      .catch(() => {});
  };

  useEffect(load, []);

  const handleOpen = () => {
    setOpen((prev) => !prev);
    if (!open) load(); // نحدّث القائمة كل ما نفتحها
  };

  const handleClickNotification = async (notification) => {
    if (!notification.read_at) {
      await markNotificationRead(notification.id).catch(() => {});
      setNotifications((prev) =>
        prev.map((n) => (n.id === notification.id ? { ...n, read_at: new Date().toISOString() } : n))
      );
      setUnreadCount((prev) => Math.max(0, prev - 1));
    }
  };

  return (
    <div className="notif-bell">
      <button className="notif-bell-btn" onClick={handleOpen} aria-label="الإشعارات">
        <Icon name="bell" size={17} />
        {unreadCount > 0 && <span className="notif-badge">{unreadCount}</span>}
      </button>

      {open && (
        <div className="notif-dropdown">
          {!notifications && <p style={{ fontSize: 13, padding: 8 }}>جاري التحميل...</p>}
          {notifications && notifications.length === 0 && (
            <p style={{ fontSize: 13, padding: 8 }}>ما في إشعارات.</p>
          )}
          {notifications?.map((notification) => (
            <button
              key={notification.id}
              className={`notif-item ${notification.read_at ? '' : 'unread'}`}
              onClick={() => handleClickNotification(notification)}
            >
              {describeNotification(notification)}
              <span className="notif-item-time">{formatTime(notification.created_at)}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default NotificationBell;
