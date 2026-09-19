import { apiGet, apiPost, unwrap } from './client';

export async function fetchContentCategories() {
  return unwrap(await apiGet('/content-categories'));
}

export async function createContentCategory({ name, isSequential }) {
  return unwrap(await apiPost('/content-categories', { name, is_sequential: isSequential }));
}

export async function createContent({ categoryId, title, type, url, orderIndex }) {
  return unwrap(
    await apiPost('/contents', {
      category_id: categoryId,
      title,
      type,
      url,
      order_index: orderIndex ?? null,
    })
  );
}
