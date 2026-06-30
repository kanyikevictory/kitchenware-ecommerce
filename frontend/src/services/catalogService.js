import apiClient from '../api/apiClient'

export async function getFeaturedCategories(signal) {
  const { data } = await apiClient.get('/categories', { params: { roots_only: true, per_page: 4 }, signal })
  return data.data ?? []
}

export async function getFeaturedProducts(signal) {
  const { data } = await apiClient.get('/products', { params: { featured: true, per_page: 8 }, signal })
  return data.data ?? []
}

export async function getNewestProducts(signal) {
  const { data } = await apiClient.get('/products', { params: { sort: 'newest', per_page: 4 }, signal })
  return data.data ?? []
}

export async function getProducts(params = {}, signal) {
  const { data } = await apiClient.get('/products', { params, signal })
  return data
}

export async function getProduct(slug, signal) {
  const { data } = await apiClient.get(`/products/${slug}`, { signal })
  return data.data
}

export async function getCategories(params = {}, signal) {
  const { data } = await apiClient.get('/categories', { params, signal })
  return data
}

export async function getCategory(slug, signal) {
  const { data } = await apiClient.get(`/categories/${slug}`, { signal })
  return data.data
}

export async function getProductReviews(slug, params = {}, signal) {
  const { data } = await apiClient.get(`/products/${slug}/reviews`, { params, signal })
  return data
}

export async function searchCatalog(params = {}, signal) {
  const { data } = await apiClient.get('/search', { params, signal })
  return data
}
