import { apiGet, apiPost, unwrap } from './client';

export async function fetchMyBookings() {
  return unwrap(await apiGet('/bookings/mine'));
}

export async function createBooking(slotId) {
  return unwrap(await apiPost('/bookings', { slot_id: slotId }));
}

export async function assignBooking(bookingId, slotId) {
  return unwrap(await apiPost(`/bookings/${bookingId}/assign`, { slot_id: slotId }));
}

export async function fetchPendingBookings() {
  return apiGet('/bookings/pending'); // مش ملفوفة بـ {data: ...}، فيها today_bookings/upcoming_bookings مباشرة
}

export async function completeBooking(bookingId) {
  return unwrap(await apiPost(`/bookings/${bookingId}/complete`));
}

export async function cancelBooking(bookingId) {
  return unwrap(await apiPost(`/bookings/${bookingId}/cancel`));
}
