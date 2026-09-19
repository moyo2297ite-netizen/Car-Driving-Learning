import { apiGet, unwrap } from './client';

export async function fetchInstructorStudents() {
  return unwrap(await apiGet('/instructor/students'));
}
