import { Link } from 'react-router-dom'

export default function Breadcrumbs({ items }) {
  return <nav aria-label="Breadcrumb" className="mx-auto flex max-w-7xl gap-2 px-5 py-5 text-xs font-medium text-navy/65 lg:px-8">
    <Link to="/" className="hover:text-navy">Home</Link>
    {items.map((item, i) => <span className="flex gap-2" key={item.label}><span>/</span>{item.to && i < items.length - 1 ? <Link to={item.to} className="hover:text-navy">{item.label}</Link> : <span className="text-navy">{item.label}</span>}</span>)}
  </nav>
}
