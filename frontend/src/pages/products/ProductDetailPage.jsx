import { Heart, Minus, Plus, ShieldCheck, ShoppingBag, Truck } from 'lucide-react'
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import { Button } from '../../components/common/Button'
import { ErrorState } from '../../components/common/Feedback'
import { Skeleton } from '../../components/common/Surface'
import { CatalogSkeleton, ProductCard } from '../../components/home/CatalogCards'
import { Breadcrumbs } from '../../components/layout/Breadcrumbs'
import { ProductGallery } from '../../components/products/ProductGallery'
import { ProductReviews } from '../../components/products/ProductReviews'
import { useProduct, useProductReviews, useProducts } from '../../hooks/useProducts'
import { useToast } from '../../hooks/useToast'
import { formatMoney } from '../../utils/formatMoney'

export default function ProductDetailPage() {
  const { slug } = useParams()
  const product = useProduct(slug)
  const [quantity, setQuantity] = useState(1)
  const [reviewPage, setReviewPage] = useState(1)
  const reviews = useProductReviews(slug, reviewPage)
  const related = useProducts({ category_id: product.data?.category?.id, per_page: 4, sort: 'newest' })
  const { showToast } = useToast()

  if (product.error) return <div className="mx-auto max-w-4xl px-4 py-20"><ErrorState title="Product unavailable" message={product.error.message} onRetry={product.retry} /></div>
  if (product.isLoading) return <div className="mx-auto grid max-w-7xl gap-12 px-4 py-16 lg:grid-cols-2"><Skeleton className="aspect-square w-full" /><div><Skeleton className="h-5 w-28" /><Skeleton className="mt-5 h-16 w-4/5" /><Skeleton className="mt-6 h-8 w-36" /><Skeleton className="mt-8 h-28 w-full" /></div></div>

  const item = product.data
  const effectivePrice = item.effective_price ?? item.discount_price ?? item.price
  const addPlaceholder = (action) => showToast(`${action} becomes active with the cart and wishlist in Phase 6.`, { tone: 'info' })
  return <><Breadcrumbs items={[{ label: 'Shop', to: '/shop' }, ...(item.category ? [{ label: item.category.name, to: `/category/${item.category.slug}` }] : []), { label: item.name }]} /><section className="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8"><div className="grid gap-12 lg:grid-cols-2 lg:gap-16"><ProductGallery images={item.images} productName={item.name} /><div className="lg:pt-4"><p className="text-xs font-bold uppercase tracking-[0.22em] text-gold-600">{item.brand ?? item.category?.name ?? 'Kitchen Store'}</p><h1 className="mt-4 font-display text-5xl leading-none font-semibold text-navy-950 sm:text-6xl">{item.name}</h1><div className="mt-6 flex items-baseline gap-3"><span className="text-2xl font-bold text-navy-950">{formatMoney(effectivePrice)}</span>{item.discount_price && <><span className="text-sm text-charcoal-900/45 line-through">{formatMoney(item.price)}</span><span className="rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-900">Save {Math.round((1 - Number(item.discount_price) / Number(item.price)) * 100)}%</span></>}</div><p className={`mt-5 text-sm font-bold ${item.in_stock ? 'text-success-600' : 'text-error-600'}`}>{item.in_stock ? `${item.stock_quantity} in stock` : 'Currently out of stock'}</p>{item.description && <p className="mt-7 leading-7 text-charcoal-900/70">{item.description}</p>}<div className="mt-8 flex flex-wrap gap-3"><div className="flex min-h-12 items-center rounded-md border border-cool-gray-200"><button onClick={() => setQuantity((value) => Math.max(1, value - 1))} className="grid size-11 place-items-center" aria-label="Decrease quantity"><Minus className="size-4" /></button><span className="w-8 text-center text-sm font-bold">{quantity}</span><button onClick={() => setQuantity((value) => Math.min(item.stock_quantity, value + 1))} className="grid size-11 place-items-center" aria-label="Increase quantity"><Plus className="size-4" /></button></div><Button disabled={!item.in_stock} className="flex-1" onClick={() => addPlaceholder('Add to cart')}><ShoppingBag className="size-4" />Add to cart</Button><Button variant="secondary" aria-label="Add to wishlist" onClick={() => addPlaceholder('Wishlist')}><Heart className="size-5" /></Button></div><div className="mt-8 grid gap-3 border-t border-cool-gray-200 pt-7 sm:grid-cols-2"><p className="flex items-center gap-3 text-sm font-semibold text-navy-950"><Truck className="size-5 text-gold-600" />Delivery across Uganda</p><p className="flex items-center gap-3 text-sm font-semibold text-navy-950"><ShieldCheck className="size-5 text-gold-600" />Quality guaranteed</p></div></div></div><div className="mt-20 grid gap-12 border-t border-cool-gray-200 pt-16 lg:grid-cols-[0.7fr_1.3fr]"><div><p className="text-xs font-bold uppercase tracking-[0.22em] text-gold-600">Product details</p><h2 className="mt-3 font-display text-4xl font-semibold text-navy-950">Specifications</h2></div><dl className="grid sm:grid-cols-2">{[['SKU', item.sku], ['Brand', item.brand], ['Category', item.category?.name], ['Availability', item.in_stock ? 'In stock' : 'Out of stock']].filter(([, value]) => value).map(([label, value]) => <div key={label} className="border-b border-cool-gray-200 py-4"><dt className="text-xs font-bold uppercase tracking-wider text-charcoal-900/45">{label}</dt><dd className="mt-1 font-semibold text-navy-950">{value}</dd></div>)}</dl></div><section className="mt-20"><h2 className="mb-8 font-display text-4xl font-semibold text-navy-950">Customer reviews</h2><ProductReviews response={reviews.data} isLoading={reviews.isLoading} page={reviewPage} onPageChange={setReviewPage} /></section><section className="mt-20"><h2 className="mb-8 font-display text-4xl font-semibold text-navy-950">You may also like</h2><div className="grid grid-cols-2 gap-4 lg:grid-cols-4">{related.isLoading ? <CatalogSkeleton count={4} /> : related.data?.data?.filter((relatedItem) => relatedItem.id !== item.id).slice(0, 4).map((relatedItem) => <ProductCard key={relatedItem.id} product={relatedItem} />)}</div></section></section></>
}
