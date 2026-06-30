import { ChevronRight, Home } from 'lucide-react'
import { Link } from 'react-router-dom'

export function Breadcrumbs({ items = [] }) {
  return <nav aria-label="Breadcrumb" className="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
    <ol className="flex flex-wrap items-center gap-2 text-sm text-charcoal-900/60">
    <li><Link to="/" className="hover:text-navy-950"><Home className="size-4" /><span className="sr-only">Home</span></Link>
    </li>{items.map((item, index) => <li key={item.to ?? item.label} className="flex items-center gap-2"><ChevronRight className="size-3.5" />{item.to && index < items.length - 1 ? <Link to={item.to}>{item.label}</Link> : <span aria-current={index === items.length - 1 ? 'page' : undefined} className="font-medium text-navy-950">{item.label}</span>}</li>)}
    </ol>
    </nav>
}
