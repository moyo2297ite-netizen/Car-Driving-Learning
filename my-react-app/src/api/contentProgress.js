import { apiGet, unwrap } from './client';

export async function fetchMyContentProgress() {
  return unwrap(await apiGet('/me/content-progress'));
}
