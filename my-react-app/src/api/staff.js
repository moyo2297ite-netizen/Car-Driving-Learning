import { apiDelete, apiGet, apiPost, unwrap } from './client';

// GET /staff بترجع المشرفين والمدربين سوا — منفلترهم حسب role بالواجهة
export async function fetchStaff() {
  return unwrap(await apiGet('/staff'));
}

export async function createStaff({ name, phone, email, password, passwordConfirmation, role, teachesManual, teachesAutomatic }) {
  return unwrap(
    await apiPost('/staff', {
      name,
      phone,
      email,
      password,
      password_confirmation: passwordConfirmation,
      role,
      teaches_manual: teachesManual,
      teaches_automatic: teachesAutomatic,
    })
  );
}

export function deleteStaff(userId) {
  return apiDelete(`/staff/${userId}`);
}
