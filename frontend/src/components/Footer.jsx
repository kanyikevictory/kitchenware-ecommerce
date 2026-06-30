import { ArrowUp, Facebook, Instagram, Youtube } from 'lucide-react'
import { Link } from 'react-router-dom'

const columns = [
  ['Shop', [['All products','/shop'],['Cookware','/category/cookware'],['Bakeware','/category/bakeware'],['Tableware','/category/tableware']]],
  ['Customer care', [['Help & FAQ','/faq'],['Shipping & returns','/shipping-returns'],['Contact','/contact'],['Track order','/account']]],
  ['Company', [['Our story','/about'],['Journal','/about'],['Craftsmanship','/about'],['Careers','/contact']]],
  ['Legal', [['Privacy','/privacy'],['Terms','/terms'],['Accessibility','/privacy']]],
]
export default function Footer() {
  return <footer className="bg-navy text-cream"><div className="mx-auto grid max-w-7xl gap-12 px-5 py-16 md:grid-cols-2 lg:grid-cols-6 lg:px-8"><div className="lg:col-span-2"><Link to="/" className="font-serif text-3xl">Maison & Flame</Link><p className="mt-3 font-serif italic text-cream/70">Cook beautifully.</p><p className="mt-6 max-w-xs text-sm leading-6 text-cream/55">Considered kitchenware for homes where cooking is an act of care.</p><div className="mt-6 flex gap-4"><Instagram size={19}/><Facebook size={19}/><Youtube size={20}/></div></div>{columns.map(([title,links]) => <div key={title}><h3 className="mb-4 text-xs font-semibold uppercase tracking-[.18em]">{title}</h3><ul className="space-y-3 text-sm text-cream/60">{links.map(([label,to]) => <li key={label}><Link className="transition hover:text-cream" to={to}>{label}</Link></li>)}</ul></div>)}</div><div className="border-t border-cream/10"><div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-6 text-xs text-cream/45 sm:flex-row lg:px-8"><span>© {new Date().getFullYear()} Maison & Flame. All rights reserved.</span><div className="flex items-center gap-3"><span className="rounded border border-cream/20 px-2 py-1">VISA</span><span className="rounded border border-cream/20 px-2 py-1">Mobile Money</span><span className="rounded border border-cream/20 px-2 py-1">COD</span></div><button onClick={() => scrollTo({top:0,behavior:'smooth'})} className="flex items-center gap-2 text-cream">Back to top <ArrowUp size={14}/></button></div></div></footer>
}
