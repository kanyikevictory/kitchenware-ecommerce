import { Heart, Plus, ShoppingBag } from 'lucide-react'
import { Link } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { money } from '../data/catalog'
import Rating from './Rating'

export default function ProductCard({ product }) {
  const { addToCart, wishlist, toggleWishlist } = useApp()
  const saved = wishlist.some((item) => item.id === product.id)
  return <article className="group relative overflow-hidden rounded-xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div className="relative overflow-hidden bg-sand">
      <Link to={`/product/${product.id}`} aria-label={`View ${product.name}`}><img src={product.image} alt={`${product.name} in ${product.material.toLowerCase()}`} className="aspect-square w-full object-cover transition duration-700 group-hover:scale-105" /></Link>
      <button onClick={() => toggleWishlist(product)} aria-label={saved ? `Remove ${product.name} from wishlist` : `Save ${product.name}`} className={`absolute right-3 top-3 rounded-full bg-cream/95 p-2 text-navy shadow-sm transition hover:scale-110 ${saved ? 'animate-heart-pop' : ''}`}><Heart size={18} fill={saved ? 'currentColor' : 'none'} /></button>
      {product.originalPrice && <span className="absolute left-3 top-3 rounded-full bg-navy px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-cream">Sale</span>}
      <button onClick={() => addToCart(product)} className="absolute inset-x-3 bottom-3 hidden translate-y-16 items-center justify-center gap-2 rounded-lg bg-terracotta px-4 py-3 text-sm font-semibold text-white transition duration-300 group-hover:translate-y-0 md:flex"><ShoppingBag size={17} /> Add to cart</button>
    </div>
    <div className="p-4 md:p-5">
      <p className="mb-1 text-xs text-charcoal/50">{product.material}</p>
      <Link to={`/product/${product.id}`} className="font-serif text-lg text-navy hover:underline">{product.name}</Link>
      <div className="mt-3"><Rating value={product.rating} count={product.reviews} compact /></div>
      <div className="mt-3 flex items-center justify-between">
        <div><span className="font-semibold text-charcoal">{money(product.price)}</span>{product.originalPrice && <span className="ml-2 text-sm text-charcoal/40 line-through">{money(product.originalPrice)}</span>}</div>
        <button onClick={() => addToCart(product)} className="rounded-full bg-terracotta p-2 text-white md:hidden" aria-label={`Add ${product.name} to cart`}><Plus size={18} /></button>
      </div>
    </div>
  </article>
}
