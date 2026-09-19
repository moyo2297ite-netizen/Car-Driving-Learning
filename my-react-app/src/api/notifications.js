import { apiGet, apiPost } from './client';

// /notifications مش ملفوفة بـ {data: ...} — شكلها { unread_count, notifications: [...] }
export const fetchNotifications = () => apiGet('/notifications');
export const markNotificationRead = (id) => apiPost(`/notifications/${id}/read`);
