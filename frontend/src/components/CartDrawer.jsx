import { Minus, Plus, ShoppingBag, Trash2, X } from 'lucide-react'
import { Link, useNavigate } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { money } from '../data/catalog'

export default function CartDrawer() {
  const { cartItems, cartOpen, setCartOpen, subtotal, updateQuantity, removeFromCart } = useApp()
  const navigate = useNavigate()
  return <div className={`fixed inset-0 z-[70] ${cartOpen ? 'pointer-events-auto' : 'pointer-events-none'}`} aria-hidden={!cartOpen}>
    <button aria-label="Close cart" onClick={() => setCartOpen(false)} className={`absolute inset-0 bg-navy/45 transition ${cartOpen ? 'opacity-100' : 'opacity-0'}`} />
    <aside className={`absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-cream shadow-xl transition duration-500 ${cartOpen ? 'translate-x-0' : 'translate-x-full'}`}>
      <header className="flex items-center justify-between border-b border-navy/10 px-6 py-5"><div><p className="font-serif text-2xl text-navy">Your cart</p><p className="text-xs text-charcoal/55">{cartItems.length} thoughtfully chosen item{cartItems.length === 1 ? '' : 's'}</p></div><button onClick={() => setCartOpen(false)} className="p-2 text-navy" aria-label="Close"><X /></button></header>
      <div className="flex-1 space-y-5 overflow-y-auto p-6">{cartItems.length ? cartItems.map((item) => <div key={`${item.id}-${item.color}-${item.size}`} className="flex gap-4">
        <img src={item.image} alt={item.name} className="h-24 w-24 rounded-lg bg-sand object-cover" />
        <div className="min-w-0 flex-1"><Link onClick={() => setCartOpen(false)} to={`/product/${item.id}`} className="font-serif text-navy">{item.name}</Link><p className="mt-1 text-xs text-charcoal/50">{item.color} · {item.size}</p><div className="mt-3 flex items-center justify-between"><div className="flex items-center rounded-full border border-navy/15"><button className="p-1.5 text-navy" onClick={() => updateQuantity(item.id, item.quantity - 1)}><Minus size={14} /></button><span className="w-7 text-center text-xs">{item.quantity}</span><button className="p-1.5 text-navy" onClick={() => updateQuantity(item.id, item.quantity + 1)}><Plus size={14} /></button></div><strong className="text-sm">{money(item.price * item.quantity)}</strong></div></div>
        <button onClick={() => removeFromCart(item.id)} className="self-start p-1 text-navy/50 hover:text-navy" aria-label={`Remove ${item.name}`}><Trash2 size={16} /></button>
      </div>) : <div className="grid h-full place-content-center text-center"><ShoppingBag className="mx-auto mb-4 text-navy/30" size={48} /><p className="font-serif text-2xl text-navy">A beautiful beginning</p><p className="mt-2 text-sm text-charcoal/60">Your cart is waiting for something special.</p><Link onClick={() => setCartOpen(false)} to="/shop" className="mt-6 text-sm font-semibold text-navy underline">Explore the shop</Link></div>}</div>
      {cartItems.length > 0 && <footer className="border-t border-navy/10 bg-white p-6"><div className="mb-4 flex justify-between"><span className="text-charcoal/60">Subtotal</span><strong className="text-lg text-navy">{money(subtotal)}</strong></div><button onClick={() => { setCartOpen(false); navigate('/checkout') }} className="w-full rounded-lg bg-terracotta py-3.5 font-semibold text-white">Checkout</button><p className="mt-3 text-center text-xs text-charcoal/45">Shipping and tax calculated at checkout</p></footer>}
    </aside>
  </div>
}
