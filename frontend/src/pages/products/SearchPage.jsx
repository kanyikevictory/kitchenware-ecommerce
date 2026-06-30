import { useSearchParams } from 'react-router-dom'
import { EmptyState, ErrorState } from '../../components/common/Feedback'
import { Pagination } from '../../components/common/Pagination'
import { CatalogSkeleton, CategoryCard, ProductCard } from '../../components/home/CatalogCards'
import { Breadcrumbs } from '../../components/layout/Breadcrumbs'
import { useCatalogSearch } from '../../hooks/useProducts'

export default function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const query = searchParams.get('q')?.trim() ?? ''
  const productPage = Number(searchParams.get('product_page') || 1)
  const categoryPage = Number(searchParams.get('category_page') || 1)
  const result = useCatalogSearch({ q: query, type: 'all', product_page: productPage, category_page: categoryPage })
  const products = result.data?.products ?? { data: [], meta: null }
  const categories = result.data?.categories ?? { data: [], meta: null }
  const changePage = (key, page) => { const next = new URLSearchParams(searchParams); next.set(key, page); setSearchParams(next) }
  return <>
  <Breadcrumbs items={[{ label: 'Search' }]} /><section className="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8"><header className="mb-12">
  <p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">Search results</p>
  <h1 className="mt-3 font-display text-5xl font-semibold text-navy-950">{query ? <>Results for “{query}”</> : 'What are you looking for?'}</h1></header>{!query ? <EmptyState title="Start with a search" message="Try cookware, knives, appliances, or a favourite brand." /> : result.error ? <ErrorState message={result.error.message} onRetry={result.retry} /> : result.isLoading ? <div className="grid grid-cols-2 gap-4 lg:grid-cols-4"><CatalogSkeleton count={8} /></div> : !products.data?.length && !categories.data?.length ? <EmptyState title="No matches found" message="Try a broader term or browse all products." /> : <div className="space-y-16">{categories.data?.length > 0 && 
    <section><h2 className="mb-6 font-display text-4xl font-semibold text-navy-950">Categories</h2>
    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">{categories.data.map((category) => <CategoryCard key={category.id} category={category} />)}</div>
    <Pagination meta={categories.meta} onPageChange={(page) => changePage('category_page', page)} /></section>}{products.data?.length > 0 && <section><h2 className="mb-6 font-display text-4xl font-semibold text-navy-950">Products</h2>
    <div className="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">{products.data.map((product) => <ProductCard key={product.id} product={product} />)}</div><Pagination meta={products.meta} onPageChange={(page) => changePage('product_page', page)} /></section>}</div>}</section></>
}
