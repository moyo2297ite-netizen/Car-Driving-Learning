import { apiGet, apiPost, unwrap } from './client';

export async function fetchPendingPayments() {
  return unwrap(await apiGet('/payments/pending'));
}

export async function confirmPayment(paymentId) {
  return unwrap(await apiPost(`/payments/${paymentId}/confirm`));
}

export async function recordPayment(bookingId, method, amount) {
  return unwrap(await apiPost(`/bookings/${bookingId}/payments`, { method, amount }));
}

export async function fetchShamCashLink() {
  return apiGet('/payments/sham-cash-link'); // {link: string|null} مش ملفوفة بـ {data: ...}
}
