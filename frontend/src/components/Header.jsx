import { ChevronDown, Heart, Menu, Search, ShoppingBag, User, X } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'
import { Link, NavLink, useLocation } from 'react-router-dom'
import { useApp } from '../context/AppContext'
import { categories, money, products } from '../data/catalog'

const nav = categories.map((item) => ({ label: item.name, to: `/category/${item.slug}` }))

export default function Header() {
  const { cartItems, wishlist, setCartOpen } = useApp()
  const [scrolled, setScrolled] = useState(false), [menu, setMenu] = useState(false), [searchOpen, setSearchOpen] = useState(false), [search, setSearch] = useState(''), [wishOpen, setWishOpen] = useState(false)
  const location = useLocation(), searchRef = useRef(null)
  useEffect(() => { const fn = () => setScrolled(scrollY > 80); addEventListener('scroll', fn, { passive: true }); return () => removeEventListener('scroll', fn) }, [])
  useEffect(() => { setMenu(false); setSearchOpen(false); setWishOpen(false) }, [location.pathname])
  useEffect(() => { if (searchOpen) searchRef.current?.focus() }, [searchOpen])
  const matches = search.length > 1 ? products.filter((p) => p.name.toLowerCase().includes(search.toLowerCase())).slice(0, 5) : []
  const count = cartItems.reduce((sum, item) => sum + item.quantity, 0)
  return <>
    <header className={`sticky top-0 z-50 transition-all duration-300 ${scrolled ? 'bg-cream/95 shadow-sm backdrop-blur-md' : 'bg-cream'}`}>
      <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-5 lg:px-8">
        <button onClick={() => setMenu(true)} className="text-navy lg:hidden" aria-label="Open menu"><Menu /></button>
        <Link to="/" className={`whitespace-nowrap font-serif font-bold tracking-tight text-navy transition-all ${scrolled ? 'text-xl' : 'text-2xl'}`}>Maison <span className="font-normal italic">&</span> Flame</Link>
        <nav className="hidden items-center gap-6 lg:flex">{nav.map((item) => <NavLink key={item.to} to={item.to} className={({ isActive }) => `group relative py-3 text-[13px] font-medium text-navy ${isActive ? 'font-semibold' : ''}`}>{item.label}<span className="absolute inset-x-0 bottom-1 h-px origin-left scale-x-0 bg-navy transition group-hover:scale-x-100" /></NavLink>)}</nav>
        <div className="flex items-center gap-1 sm:gap-2">
          <div className="relative hidden sm:block"><div className="flex items-center"><input ref={searchRef} value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search the collection" className={`border-b border-navy/20 bg-transparent text-sm text-navy transition-all duration-300 placeholder:text-navy/40 ${searchOpen ? 'w-48 px-2 py-2 lg:w-64' : 'w-0 p-0 opacity-0'}`} /><button onClick={() => setSearchOpen(!searchOpen)} className="p-2 text-navy" aria-label="Search"><Search size={20} /></button></div>{searchOpen && matches.length > 0 && <div className="absolute right-0 top-12 w-80 rounded-lg bg-white p-2 shadow-md">{matches.map((p) => <Link key={p.id} to={`/product/${p.id}`} className="flex items-center gap-3 rounded-md p-2 hover:bg-sand"><img src={p.image} alt="" className="h-11 w-11 rounded object-cover" /><span className="min-w-0 flex-1 truncate text-sm text-navy">{p.name}</span><span className="text-xs">{money(p.price)}</span></Link>)}</div>}</div>
          <Link to="/account" className="hidden p-2 text-navy sm:block" aria-label="Account"><User size={20} /></Link>
          <div className="relative"><button onClick={() => setWishOpen(!wishOpen)} className="relative p-2 text-navy" aria-label="Wishlist"><Heart size={20} fill={wishlist.length ? 'currentColor' : 'none'} />{wishlist.length > 0 && <span className="absolute right-0 top-0 grid h-4 min-w-4 place-items-center rounded-full bg-navy px-1 text-[9px] text-white">{wishlist.length}</span>}</button>{wishOpen && <div className="absolute right-0 top-11 w-72 rounded-lg bg-white p-4 shadow-md"><p className="mb-3 font-serif text-lg text-navy">Saved pieces</p>{wishlist.slice(0,3).map((p) => <Link key={p.id} to={`/product/${p.id}`} className="flex items-center gap-3 border-t border-navy/5 py-2"><img src={p.image} alt="" className="h-10 w-10 rounded object-cover"/><span className="text-sm text-navy">{p.name}</span></Link>)}{wishlist.length === 0 && <p className="text-sm text-charcoal/50">Nothing saved yet.</p>}<Link to="/wishlist" className="mt-3 block text-sm font-semibold text-navy underline">View all</Link></div>}</div>
          <button onClick={() => setCartOpen(true)} className="relative p-2 text-navy" aria-label="Cart"><ShoppingBag size={21} />{count > 0 && <span className="absolute right-0 top-0 grid h-4 min-w-4 place-items-center rounded-full bg-terracotta px-1 text-[9px] text-white">{count}</span>}</button>
        </div>
      </div>
    </header>
    <div className={`fixed inset-0 z-[80] bg-navy/40 transition lg:hidden ${menu ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'}`}><nav className={`h-full w-[86%] max-w-sm bg-cream p-6 transition duration-500 ${menu ? 'translate-x-0' : '-translate-x-full'}`}><div className="mb-10 flex items-center justify-between"><span className="font-serif text-2xl text-navy">Maison & Flame</span><button onClick={() => setMenu(false)}><X className="text-navy" /></button></div><Link to="/shop" className="mb-3 block border-b border-navy/10 py-3 font-serif text-xl text-navy">Shop all</Link>{nav.map((item) => <Link key={item.to} to={item.to} className="flex items-center justify-between border-b border-navy/10 py-3 font-serif text-xl text-navy">{item.label}<ChevronDown className="-rotate-90" size={17}/></Link>)}<div className="mt-8 flex gap-5 text-sm text-navy"><Link to="/about">Our story</Link><Link to="/contact">Contact</Link></div></nav></div>
  </>
}
