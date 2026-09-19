import { apiGet, apiPut } from './client';

// /settings مش ملفوفة بـ {data: ...} (شوف SettingController باللارافيل)
export const fetchSettings = () => apiGet('/settings');
export const updateSettings = (payload) => apiPut('/settings', payload);
