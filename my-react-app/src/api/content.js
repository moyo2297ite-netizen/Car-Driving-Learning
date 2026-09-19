import { apiGet, apiPost, unwrap } from './client';

export async function fetchContent(contentId) {
  return unwrap(await apiGet(`/contents/${contentId}`));
}

export async function markContentViewed(contentId) {
  return apiPost(`/contents/${contentId}/view`); // {completed: boolean}، مش ملفوفة بـ {data: ...}
}

export async function createQuiz(contentId, { passScore, questions }) {
  return unwrap(
    await apiPost(`/contents/${contentId}/quiz`, {
      pass_score: passScore,
      questions,
    })
  );
}

export async function submitQuiz(quizId, answers) {
  return apiPost(`/quizzes/${quizId}/submit`, { answers }); // {score, passed, pass_score, blocks_next}
}
