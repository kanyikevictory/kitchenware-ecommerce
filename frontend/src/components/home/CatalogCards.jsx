import { Heart, ImageOff, ShoppingBag } from 'lucide-react'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Badge, Skeleton } from '../common/Surface'

function ImageWithFallback({ src, alt, className }) {
  const [failed, setFailed] = useState(false)
  if (!src || failed) return <div className={`grid place-items-center bg-ice-50 text-cool-gray-200 ${className}`}><ImageOff className="size-10" /><span className="sr-only">Image unavailable</span></div>
  return <img src={src} alt={alt} className={className} loading="lazy" onError={() => setFailed(true)} />
}

export function CategoryCard({ category }) {
  const image = category.image_url ?? category.image
  return <Link to={`/category/${category.slug}`} className="group relative isolate min-h-80 overflow-hidden rounded-xl bg-navy-950"><ImageWithFallback src={image} alt={category.name} className="absolute inset-0 -z-10 size-full object-cover opacity-80 transition duration-700 group-hover:scale-105" /><div className="absolute inset-0 -z-10 bg-linear-to-t from-navy-950 via-navy-950/15 to-transparent" /><div className="absolute inset-x-0 bottom-0 p-6 text-white"><p className="text-xs font-bold uppercase tracking-[0.2em] text-gold-500">Explore</p><h3 className="mt-2 font-display text-3xl font-semibold">{category.name}</h3></div></Link>
}

export function ProductCard({ product }) {
  const image = product.images?.find((item) => item.is_primary)?.url ?? product.images?.[0]?.url
  const price = product.discount_price ?? product.price
  return <article className="group"><Link to={`/product/${product.slug}`} className="relative block aspect-4/5 overflow-hidden rounded-xl bg-ice-50"><ImageWithFallback src={image} alt={product.name} className="size-full object-cover transition duration-700 group-hover:scale-105" />{product.discount_price && <Badge tone="gold" className="absolute top-3 left-3">Sale</Badge>}<button type="button" aria-label={`Add ${product.name} to wishlist`} onClick={(event) => event.preventDefault()} className="absolute top-3 right-3 grid size-10 place-items-center rounded-full bg-white/90 text-navy-950 opacity-0 shadow-sm transition group-hover:opacity-100 focus:opacity-100"><Heart className="size-4" /></button><span className="absolute inset-x-3 bottom-3 flex translate-y-3 items-center justify-center gap-2 rounded-md bg-navy-950 px-4 py-3 text-sm font-bold text-white opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100"><ShoppingBag className="size-4" />View product</span></Link><p className="mt-4 text-xs font-bold uppercase tracking-[0.16em] text-gold-600">{product.brand ?? product.category?.name ?? 'Kitchen Store'}</p><h3 className="mt-1 font-display text-2xl font-semibold text-navy-950"><Link to={`/product/${product.slug}`}>{product.name}</Link></h3><div className="mt-2 flex gap-2 text-sm"><span className="font-bold text-navy-950">UGX {Number(price).toLocaleString()}</span>{product.discount_price && <span className="text-charcoal-900/45 line-through">UGX {Number(product.price).toLocaleString()}</span>}</div></article>
}

export function CatalogSkeleton({ count = 4, ratio = 'aspect-[4/5]' }) {
  return Array.from({ length: count }, (_, index) => <div key={index}><Skeleton className={`${ratio} w-full rounded-xl`} /><Skeleton className="mt-4 h-3 w-20" /><Skeleton className="mt-3 h-6 w-3/4" /><Skeleton className="mt-3 h-4 w-24" /></div>)
}
