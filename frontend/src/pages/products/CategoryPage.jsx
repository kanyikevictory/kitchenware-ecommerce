import { useParams } from 'react-router-dom'
import { ErrorState } from '../../components/common/Feedback'
import { Skeleton } from '../../components/common/Surface'
import { Breadcrumbs } from '../../components/layout/Breadcrumbs'
import { ProductListing } from '../../components/products/ProductListing'
import { useCategory } from '../../hooks/useProducts'

export default function CategoryPage() {
  const { slug } = useParams()
  const category = useCategory(slug)
  if (category.error) return <div className="mx-auto max-w-4xl px-4 py-20"><ErrorState message={category.error.message} onRetry={category.retry} /></div>
  return <><Breadcrumbs items={[{ label: 'Categories', to: '/categories' }, { label: category.data?.name ?? 'Category' }]} /><section className="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">{category.isLoading ? <><Skeleton className="h-16 w-72" /><Skeleton className="mt-4 h-5 w-full max-w-xl" /></> : <header className="mb-10 border-b border-cool-gray-200 pb-10"><p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">Category</p><h1 className="mt-3 font-display text-6xl font-semibold text-navy-950">{category.data.name}</h1>{category.data.description && <p className="mt-4 max-w-2xl leading-7 text-charcoal-900/65">{category.data.description}</p>}</header>}<ProductListing fixedParams={{ category_id: category.data?.id }} /></section></>
}
