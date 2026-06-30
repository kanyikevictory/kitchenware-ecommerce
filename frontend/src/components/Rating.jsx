import { Star } from 'lucide-react'

export default function Rating({ value = 5, count, compact = false }) {
  return <div className="flex items-center gap-2" aria-label={`${value} out of 5 stars`}>
    <div className="flex text-gold">{[1,2,3,4,5].map((star) => <Star key={star} size={compact ? 13 : 15} fill={star <= Math.round(value) ? 'currentColor' : 'none'} strokeWidth={1.6} />)}</div>
    {count !== undefined && <span className="text-xs text-charcoal/55">({count})</span>}
  </div>
}
