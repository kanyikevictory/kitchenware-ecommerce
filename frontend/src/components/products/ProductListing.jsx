import { useSearchParams } from 'react-router-dom'
import { CatalogSkeleton, ProductCard } from '../home/CatalogCards'
import { EmptyState, ErrorState } from '../common/Feedback'
import { Pagination } from '../common/Pagination'
import { CatalogFilters } from './CatalogFilters'
import { CatalogToolbar } from './CatalogToolbar'
import { useCategories, useProducts } from '../../hooks/useProducts'

export function ProductListing({ fixedParams = {} }) {
  const [searchParams, setSearchParams] = useSearchParams()
  const params = {
    search: searchParams.get('search') || undefined,
    category_id: fixedParams.category_id ?? searchParams.get('category_id') ?? undefined,
    min_price: searchParams.get('min_price') || undefined,
    max_price: searchParams.get('max_price') || undefined,
    in_stock: searchParams.get('in_stock') || undefined,
    sort: fixedParams.sort ?? searchParams.get('sort') ?? 'newest',
    page: Number(searchParams.get('page') || 1),
    per_page: 12,
    ...fixedParams,
  }
  const products = useProducts(params)
  const categories = useCategories({ roots_only: true, per_page: 100 })
  const response = products.data
  const setPage = (page) => { const next = new URLSearchParams(searchParams); next.set('page', page); setSearchParams(next); window.scrollTo({ top: 0, behavior: 'smooth' }) }

  return <div className="grid gap-10 lg:grid-cols-[15rem_1fr]"><CatalogFilters categories={categories.data?.data ?? []} /><div><CatalogToolbar total={response?.meta?.total ?? 0} />{products.error ? <div className="mt-8"><ErrorState message={products.error.message} onRetry={products.retry} /></div> : products.isLoading ? <div className="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 xl:grid-cols-3"><CatalogSkeleton count={6} /></div> : response?.data?.length ? <><div className="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 xl:grid-cols-3">{response.data.map((product) => <ProductCard key={product.id} product={product} />)}</div><Pagination meta={response.meta} onPageChange={setPage} /></> : <div className="mt-8"><EmptyState title="No products found" message="Try adjusting your search or removing a filter." /></div>}</div></div>
}
