import { Search } from 'lucide-react'
import { useSearchParams } from 'react-router-dom'

export function CatalogToolbar({ total = 0 }) {
  const [searchParams, setSearchParams] = useSearchParams()
  const update = (key, value) => { const next = new URLSearchParams(searchParams); value ? next.set(key, value) : next.delete(key); next.delete('page'); setSearchParams(next) }
  const submitSearch = (event) => { event.preventDefault(); update('search', new FormData(event.currentTarget).get('search')?.trim()) }
  return <div className="flex flex-col gap-4 border-b border-cool-gray-200 pb-5 sm:flex-row sm:items-center sm:justify-between"><p className="text-sm text-charcoal-900/60"><strong className="text-navy-950">{total}</strong> products</p><div className="flex flex-col gap-3 sm:flex-row"><form onSubmit={submitSearch} className="relative"><label htmlFor="catalog-search" className="sr-only">Search products</label><input id="catalog-search" name="search" defaultValue={searchParams.get('search') ?? ''} placeholder="Search products" className="min-h-11 rounded-md border border-cool-gray-200 pr-10 pl-3 text-sm" /><button type="submit" aria-label="Search" className="absolute top-1/2 right-3 -translate-y-1/2"><Search className="size-4" /></button></form><label><span className="sr-only">Sort products</span><select value={searchParams.get('sort') ?? 'newest'} onChange={(event) => update('sort', event.target.value)} className="min-h-11 rounded-md border border-cool-gray-200 bg-white px-3 text-sm font-semibold"><option value="newest">Newest first</option><option value="price_asc">Price: low to high</option><option value="price_desc">Price: high to low</option><option value="name_asc">Name: A–Z</option><option value="name_desc">Name: Z–A</option></select></label></div></div>
}
