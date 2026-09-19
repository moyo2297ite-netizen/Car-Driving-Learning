import { apiDelete, apiGet, apiPost, apiPut, unwrap } from './client';

// الطالب — مهاراته هو بس
export async function fetchMySkills() {
  return unwrap(await apiGet('/me/skills'));
}

// المدرب/المشرف/الأدمن — مهارات طالب معيّن
export async function fetchStudentSkills(studentId) {
  return unwrap(await apiGet(`/students/${studentId}/skills`));
}

// المدرب — تبديل حالة مهارة لطالب. لاحظ: skillId هو id المهارة نفسها
// (جدول skills)، مش id صف الربط (student_skills) — الاثنين مختلفين.
export async function updateStudentSkill(studentId, skillId, isCompleted) {
  return unwrap(await apiPut(`/students/${studentId}/skills/${skillId}`, { is_completed: isCompleted }));
}

// الأدمن — إدارة قائمة المهارات العامة
export async function fetchSkills() {
  return unwrap(await apiGet('/skills'));
}

export async function createSkill(name) {
  return unwrap(await apiPost('/skills', { name }));
}

export function deleteSkill(skillId) {
  return apiDelete(`/skills/${skillId}`);
}

export function fetchCertificateEligibility(studentId) {
  return apiGet(`/students/${studentId}/certificate-eligibility`); // {eligible: boolean}
}
