import { apiGet, apiPost, unwrap } from './client';

export function login(phone, password) {
  return apiPost('/login', { phone, password, device_name: 'web' });
}

export function register({ name, phone, email, password, passwordConfirmation, transmissionType }) {
  return apiPost('/register', {
    name,
    phone,
    email,
    password,
    password_confirmation: passwordConfirmation,
    transmission_type: transmissionType,
  });
}

export function logout() {
  return apiPost('/logout');
}

export async function fetchMe() {
  return unwrap(await apiGet('/me'));
}
