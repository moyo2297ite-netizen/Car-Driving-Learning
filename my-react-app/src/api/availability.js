import { apiGet, apiPost, unwrap } from './client';

// الطالب — الأوقات المتاحة للحجز، مفلترة حسب نوع الغيار من الباك اند
export async function fetchAvailableSlots(transmissionType) {
  const query = transmissionType ? `?transmission_type=${transmissionType}` : '';
  return unwrap(await apiGet(`/availability-slots/available${query}`));
}

// المدرب — أوقاته هو بس (كل الحالات: proposed/approved/rejected)
export async function fetchMySlots() {
  return unwrap(await apiGet('/availability-slots/mine'));
}

export async function proposeSlot(startTime, endTime) {
  return unwrap(await apiPost('/availability-slots', { start_time: startTime, end_time: endTime }));
}

// المشرف/الأدمن
export async function fetchPendingSlots() {
  return unwrap(await apiGet('/availability-slots/pending'));
}

export async function approveSlot(slotId) {
  return unwrap(await apiPost(`/availability-slots/${slotId}/approve`));
}

export async function rejectSlot(slotId, reason) {
  return unwrap(await apiPost(`/availability-slots/${slotId}/reject`, { reason }));
}
