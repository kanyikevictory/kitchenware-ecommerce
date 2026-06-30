import { Star } from 'lucide-react'
import { Pagination } from '../common/Pagination'
import { Skeleton } from '../common/Surface'

export function ProductReviews({ response, isLoading, page, onPageChange }) {
  if (isLoading) return <div className="space-y-4"><Skeleton className="h-32 w-full" /><Skeleton className="h-32 w-full" /></div>
  const reviews = response?.data ?? []
  const summary = response?.summary ?? response?.meta?.summary
  return <section><div className="mb-8 flex flex-wrap items-center gap-5"><div className="font-display text-5xl font-semibold text-navy-950">{Number(summary?.average_rating ?? 0).toFixed(1)}</div><div><Stars rating={Math.round(summary?.average_rating ?? 0)} /><p className="mt-1 text-sm text-charcoal-900/55">Based on {summary?.review_count ?? response?.meta?.total ?? reviews.length} reviews</p></div></div>{reviews.length ? <div className="divide-y divide-cool-gray-200 border-y border-cool-gray-200">{reviews.map((review) => <article key={review.id} className="py-7"><div className="flex items-center justify-between gap-4"><div><p className="font-bold text-navy-950">{review.user?.name ?? 'Verified customer'}</p><Stars rating={review.rating} /></div><time className="text-xs text-charcoal-900/45">{review.created_at ? new Date(review.created_at).toLocaleDateString('en-UG') : ''}</time></div>{review.title && <h3 className="mt-4 font-display text-2xl font-semibold text-navy-950">{review.title}</h3>}<p className="mt-2 leading-7 text-charcoal-900/70">{review.comment}</p></article>)}</div> : <p className="rounded-xl bg-ice-50 p-8 text-center text-charcoal-900/60">No reviews yet. This piece is waiting for its first story.</p>}<Pagination meta={response?.meta} page={page} onPageChange={onPageChange} /></section>
}

function Stars({ rating }) {
  return <div className="mt-1 flex" aria-label={`${rating} out of 5 stars`}>{Array.from({ length: 5 }, (_, index) => <Star key={index} className={`size-4 ${index < rating ? 'fill-gold-500 text-gold-500' : 'text-cool-gray-200'}`} />)}</div>
}
