import { apiGet } from './client';

// هاي الـ endpoints الثلاث ما ملفوفة بـ {data: ...} (شوف DashboardController
// باللارافيل)، فبترجع الكائن متل ما هو بدون unwrap.
export const fetchAdminDashboard = () => apiGet('/dashboard/admin');
export const fetchSupervisorDashboard = () => apiGet('/dashboard/supervisor');
export const fetchInstructorDashboard = () => apiGet('/dashboard/instructor');
