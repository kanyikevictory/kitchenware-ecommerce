import { Breadcrumbs } from '../../components/layout/Breadcrumbs'
import { ProductListing } from '../../components/products/ProductListing'

export default function ShopPage({ newest = false }) {
  return <>
  <Breadcrumbs items={[{ label: newest ? 'New arrivals' : 'Shop' }]} />
  <section className="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
    <header className="mb-10 border-b border-cool-gray-200 pb-10"><p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">Curated kitchenware</p><h1 className="mt-3 font-display text-6xl font-semibold text-navy-950">{newest ? 'New arrivals' : 'Shop all'}</h1>
    <p className="mt-4 max-w-2xl leading-7 text-charcoal-900/65">Beautiful, hardworking pieces selected for the way you really cook.</p></header>
    <ProductListing fixedParams={newest ? { sort: 'newest' } : {}} /></section></>
}
