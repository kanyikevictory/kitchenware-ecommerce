import { Breadcrumbs } from '../../components/layout/Breadcrumbs'
import { EmptyState, ErrorState } from '../../components/common/Feedback'
import { CatalogSkeleton, CategoryCard } from '../../components/home/CatalogCards'
import { useCategories } from '../../hooks/useProducts'

export default function CategoriesPage() {
  const result = useCategories({ roots_only: true, per_page: 100 })
  return <>
  <Breadcrumbs items={[{ label: 'Categories' }]} />
    <section className="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
      <header className="mb-10"><p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">Explore the collection</p><h1 className="mt-3 font-display text-6xl font-semibold text-navy-950">Shop by category</h1></header>{result.error ? <ErrorState message={result.error.message} onRetry={result.retry} /> : result.isLoading ? <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <CatalogSkeleton count={4} ratio="aspect-[4/5]" /></div> : result.data?.data?.length ? <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">{result.data.data.map((category) => <CategoryCard key={category.id} category={category} />)}</div> : <EmptyState title="No categories yet" />}</section></>
}
