import { getCategories, getCategory, getProduct, getProductReviews, getProducts, searchCatalog } from '../services/catalogService'
import { useCatalogQuery } from './useCatalogQuery'

const fetchProducts = (signal, params) => getProducts(params, signal)
const fetchCategories = (signal, params) => getCategories(params, signal)
const fetchProduct = (signal, slug) => getProduct(slug, signal)
const fetchCategory = (signal, slug) => getCategory(slug, signal)
const fetchReviews = (signal, [slug, page]) => getProductReviews(slug, { page, per_page: 6 }, signal)
const fetchSearch = (signal, params) => searchCatalog(params, signal)

export const useProducts = (params) => useCatalogQuery(fetchProducts, params)
export const useCategories = (params = { per_page: 100 }) => useCatalogQuery(fetchCategories, params)
export const useProduct = (slug) => useCatalogQuery(fetchProduct, slug)
export const useCategory = (slug) => useCatalogQuery(fetchCategory, slug)
export const useProductReviews = (slug, page = 1) => useCatalogQuery(fetchReviews, [slug, page])
export const useCatalogSearch = (params) => useCatalogQuery(fetchSearch, params)
