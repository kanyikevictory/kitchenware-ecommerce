import { ArrowRight } from 'lucide-react'
import { Link } from 'react-router-dom'

export function SectionHeading({ eyebrow, title, description, linkTo, linkLabel = 'View all' }) {
  return <div className="mb-9 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"><div><p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">{eyebrow}</p><h2 className="mt-3 font-display text-4xl leading-none font-semibold text-navy-950 sm:text-5xl">{title}</h2>{description && <p className="mt-4 max-w-xl text-sm leading-6 text-charcoal-900/65">{description}</p>}</div>{linkTo && <Link to={linkTo} className="inline-flex shrink-0 items-center gap-2 text-sm font-bold text-navy-950 hover:text-gold-600">{linkLabel}<ArrowRight className="size-4" /></Link>}</div>
}
