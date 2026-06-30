import { Search } from 'lucide-react'
import { useNavigate } from 'react-router-dom'

export function SearchBar({ onSubmit, compact = false }) {
  const navigate = useNavigate()
  const handleSubmit = (event) => {
    event.preventDefault()
    const query = new FormData(event.currentTarget).get('q')?.trim()
    if (!query) return
    onSubmit?.()
    navigate(`/search?q=${encodeURIComponent(query)}`)
  }
  return <form role="search" onSubmit={handleSubmit} className="relative w-full"><label className="sr-only" htmlFor={compact ? 'mobile-search' : 'site-search'}>Search products and categories</label><input id={compact ? 'mobile-search' : 'site-search'} name="q" type="search" placeholder="Search cookware, knives, appliances…" className="w-full rounded-full border border-cool-gray-200 bg-ice-50 py-3 pr-12 pl-5 text-sm outline-none transition focus:border-navy-950 focus:bg-white focus:ring-3 focus:ring-navy-950/10" /><button type="submit" className="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-navy-950 p-2 text-white transition hover:bg-gold-600" aria-label="Submit search"><Search className="size-4" /></button></form>
}
