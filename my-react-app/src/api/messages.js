import { apiGet, apiPost, unwrap } from './client';

// المشرف/الأدمن — قائمة الطلاب يلي راسلوهم قبل
export async function fetchThreads() {
  return unwrap(await apiGet('/messages/threads'));
}

export async function fetchConversation(userId) {
  return unwrap(await apiGet(`/messages/with/${userId}`));
}

export async function sendMessage(receiverId, body) {
  return unwrap(await apiPost('/messages', { receiver_id: receiverId, body }));
}
