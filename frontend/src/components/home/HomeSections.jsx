import { ArrowRight, ChefHat, PackageCheck, Quote, ShieldCheck, Sparkles, Star, Truck } from 'lucide-react'
import { Link } from 'react-router-dom'
import heroImage from '../../assets/home/kitchen-hero.png'
import { useToast } from '../../hooks/useToast'
import { Button } from '../common/Button'
import { EmptyState, ErrorState } from '../common/Feedback'
import { CatalogSkeleton, CategoryCard, ProductCard } from './CatalogCards'
import { SectionHeading } from './SectionHeading'

const promises = [
  { icon: Sparkles, title: 'Considered quality', text: 'Selected for performance, durability, and timeless design.' },
  { icon: Truck, title: 'Reliable delivery', text: 'Carefully packed and delivered throughout Uganda.' },
  { icon: ShieldCheck, title: 'Shop confidently', text: 'Secure ordering and thoughtful customer support.' },
  { icon: ChefHat, title: 'Made for real cooks', text: 'Useful pieces that earn their place in your kitchen.' },
]

const reviews = [
  { quote: 'The quality feels exceptional, and every piece arrived beautifully packed. My kitchen finally feels complete.', name: 'Amina K.', detail: 'Verified customer, Kampala' },
  { quote: 'A genuinely polished shopping experience. The cookware is as practical as it is beautiful.', name: 'Daniel O.', detail: 'Verified customer, Entebbe' },
  { quote: 'Helpful service, quick delivery, and the knives have become the tools I reach for every day.', name: 'Grace N.', detail: 'Verified customer, Jinja' },
]

export function HeroSection() {
  return <section className="relative isolate min-h-172 overflow-hidden bg-ice-50"><img src={heroImage} alt="Navy cookware styled in a bright contemporary kitchen" className="absolute inset-0 -z-20 size-full object-cover object-[68%_center]" fetchPriority="high" /><div className="absolute inset-0 -z-10 bg-linear-to-r from-white via-white/90 to-white/5" /><div className="mx-auto flex min-h-172 max-w-7xl items-center px-4 py-20 sm:px-6 lg:px-8"><div className="max-w-2xl animate-rise"><p className="text-xs font-bold uppercase tracking-[0.28em] text-gold-600">Beautiful tools. Better cooking.</p><h1 className="mt-5 font-display text-6xl leading-[0.9] font-semibold text-navy-950 sm:text-7xl lg:text-8xl">Bring intention to every meal.</h1><p className="mt-7 max-w-lg text-base leading-7 text-charcoal-900/70 sm:text-lg">Premium cookware and kitchen essentials, thoughtfully chosen for memorable everyday moments.</p><div className="mt-9 flex flex-wrap gap-3"><Link to="/shop" className="inline-flex min-h-12 items-center gap-2 rounded-md bg-navy-950 px-6 py-3 text-sm font-bold text-white transition hover:bg-navy-900">Shop now<ArrowRight className="size-4" /></Link><Link to="/categories" className="inline-flex min-h-12 items-center rounded-md border border-navy-950 bg-white/70 px-6 py-3 text-sm font-bold text-navy-950 backdrop-blur transition hover:bg-white">Explore categories</Link></div><div className="mt-10 flex items-center gap-3 text-sm text-navy-950"><div className="flex" aria-label="Rated 5 out of 5">{Array.from({ length: 5 }, (_, index) => <Star key={index} className="size-4 fill-gold-500 text-gold-500" />)}</div><span className="font-semibold">Loved by home cooks across Uganda</span></div></div></div></section>
}

export function CategorySection({ categories, isLoading }) {
  return <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <SectionHeading eyebrow="Find your favourites" title="Shop by category" description="From everyday essentials to statement pieces, discover tools designed to make time in the kitchen a pleasure." linkTo="/categories" />
    {isLoading ? 
    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <CatalogSkeleton count={4} ratio="aspect-[4/5]" />
      </div> : categories.length ? <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">{categories.map((category) => <CategoryCard key={category.id} category={category} />)}</div> : <EmptyState title="Categories are being prepared" message="Our catalogue will appear here as soon as it is available." />}</section>
}

export function ProductSection({ eyebrow, title, description, products, isLoading, soft = false }) {
  return <section className={soft ? 'bg-ice-50' : 'bg-white'}>
    <div className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
      <SectionHeading {...{ eyebrow, title, description }} linkTo="/shop" />
      {isLoading ? <div className="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">
    <CatalogSkeleton />
    </div> : products.length ? <div className="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">{products.map((product) => <ProductCard key={product.id} product={product} />)}
    </div> : <EmptyState title="Fresh pieces are on the way" message="Check back soon for our latest kitchenware selection." />}</div></section>
}

export function WhyUsSection() {
  return <section className="bg-navy-950 text-white"><div className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"><div className="grid gap-10 lg:grid-cols-[0.7fr_1.3fr]"><div><p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-500">The Kitchen Store standard</p><h2 className="mt-4 font-display text-5xl leading-none font-semibold">Good things belong at the centre of the home.</h2><p className="mt-5 text-sm leading-7 text-white/65">We choose enduring kitchenware that balances quiet beauty with the demands of everyday cooking.</p></div><div className="grid gap-px overflow-hidden rounded-xl bg-white/10 sm:grid-cols-2">{promises.map(({ icon: Icon, title, text }) => <div key={title} className="bg-navy-950 p-7"><Icon className="size-7 text-gold-500" /><h3 className="mt-5 font-display text-2xl font-semibold">{title}</h3><p className="mt-2 text-sm leading-6 text-white/60">{text}</p></div>)}</div></div></div></section>
}

export function ReviewsSection() {
  return <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"><SectionHeading eyebrow="From their kitchens" title="What our customers say" /><div className="grid gap-5 md:grid-cols-3">{reviews.map((review) => <figure key={review.name} className="rounded-xl border border-cool-gray-200 p-7"><Quote className="size-8 text-gold-500" /><blockquote className="mt-5 font-display text-2xl leading-8 text-navy-950">“{review.quote}”</blockquote><figcaption className="mt-6 border-t border-cool-gray-200 pt-5"><p className="font-bold text-navy-950">{review.name}</p><p className="mt-1 text-xs text-charcoal-900/55">{review.detail}</p></figcaption></figure>)}</div></section>
}

export function NewsletterSection() {
  const { showToast } = useToast()
  const handleSubmit = (event) => { event.preventDefault(); event.currentTarget.reset(); showToast('Welcome to the Kitchen Store table.', { tone: 'success' }) }
  return <section className="px-4 pb-20 sm:px-6 lg:px-8"><div className="mx-auto max-w-7xl overflow-hidden rounded-2xl bg-gold-500 px-6 py-14 sm:px-12"><div className="grid items-center gap-8 lg:grid-cols-2"><div><p className="text-xs font-bold uppercase tracking-[0.24em] text-navy-950/65">A note from our kitchen</p><h2 className="mt-3 font-display text-4xl font-semibold text-navy-950 sm:text-5xl">New finds, thoughtful recipes, no clutter.</h2></div><form onSubmit={handleSubmit} className="flex flex-col gap-3 sm:flex-row"><label htmlFor="newsletter-email" className="sr-only">Email address</label><input id="newsletter-email" type="email" required placeholder="Your email address" className="min-h-12 flex-1 rounded-md border border-navy-950/15 bg-white px-4 text-sm outline-none focus:ring-3 focus:ring-navy-950/15" /><Button type="submit">Join the list</Button></form></div></div></section>
}

export function CatalogError({ error, retry }) {
  if (!error) return null
  return <div className="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8"><ErrorState title="The catalogue is taking a moment" message={error.message} onRetry={retry} /></div>
}

export function ServiceStrip() {
  return <div className="border-y border-cool-gray-200 bg-white"><div className="mx-auto grid max-w-7xl divide-y divide-cool-gray-200 px-4 sm:grid-cols-3 sm:divide-x sm:divide-y-0 sm:px-6 lg:px-8">{[[Truck, 'Delivery across Uganda'], [PackageCheck, 'Carefully packed'], [ShieldCheck, 'Secure checkout']].map(([Icon, label]) => <div key={label} className="flex items-center justify-center gap-3 py-5 text-sm font-bold text-navy-950"><Icon className="size-5 text-gold-600" />{label}</div>)}</div></div>
}
