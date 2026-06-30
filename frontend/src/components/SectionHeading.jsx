export default function SectionHeading({ eyebrow, title, copy, align = 'center' }) {
  return <div data-reveal className={`mb-10 ${align === 'left' ? 'max-w-2xl text-left' : 'mx-auto max-w-2xl text-center'}`}>
    {eyebrow && <p className="mb-3 text-xs font-semibold uppercase tracking-[.24em] text-navy/60">{eyebrow}</p>}
    <h2 className="font-serif text-4xl leading-tight text-navy md:text-5xl">{title}</h2>
    {copy && <p className="mt-4 leading-7 text-charcoal/70">{copy}</p>}
  </div>
}
