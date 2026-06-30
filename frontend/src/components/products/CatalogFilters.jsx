import { SlidersHorizontal, X } from 'lucide-react'
import { useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { Button } from '../common/Button'
import { Drawer } from '../common/Overlay'

export function CatalogFilters({ categories = [] }) {
  const [searchParams, setSearchParams] = useSearchParams()
  const [isOpen, setIsOpen] = useState(false)
  const signature = searchParams.toString()
  const apply = (event) => {
    event.preventDefault()
    const form = new FormData(event.currentTarget)
    const next = new URLSearchParams(searchParams)
    for (const key of ['category_id', 'min_price', 'max_price', 'in_stock']) {
      const value = form.get(key)
      value ? next.set(key, value) : next.delete(key)
    }
    next.delete('page')
    setSearchParams(next)
    setIsOpen(false)
  }
  const clear = () => {
    const next = new URLSearchParams()
    if (searchParams.get('search')) next.set('search', searchParams.get('search'))
    if (searchParams.get('sort')) next.set('sort', searchParams.get('sort'))
    setSearchParams(next)
  }
  const form = <form key={signature} onSubmit={apply} className="space-y-7"><FilterGroup label="Category"><select name="category_id" defaultValue={searchParams.get('category_id') ?? ''} className="w-full rounded-md border border-cool-gray-200 bg-white px-3 py-2.5 text-sm"><option value="">All categories</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></FilterGroup><FilterGroup label="Price range"><div className="grid grid-cols-2 gap-2"><input name="min_price" type="number" min="0" defaultValue={searchParams.get('min_price') ?? ''} placeholder="Minimum" className="w-full rounded-md border border-cool-gray-200 px-3 py-2.5 text-sm" /><input name="max_price" type="number" min="0" defaultValue={searchParams.get('max_price') ?? ''} placeholder="Maximum" className="w-full rounded-md border border-cool-gray-200 px-3 py-2.5 text-sm" /></div></FilterGroup><label className="flex cursor-pointer items-center gap-3 text-sm font-semibold text-navy-950"><input name="in_stock" value="true" type="checkbox" defaultChecked={searchParams.get('in_stock') === 'true'} className="size-4 accent-navy-950" />In-stock products only</label><div className="grid grid-cols-2 gap-2"><Button type="submit">Apply</Button><Button type="button" variant="ghost" onClick={clear}>Clear</Button></div></form>
  return <><Button variant="secondary" className="lg:hidden" onClick={() => setIsOpen(true)}><SlidersHorizontal className="size-4" />Filters</Button><aside className="hidden lg:block"><div className="mb-6 flex items-center justify-between"><h2 className="font-display text-2xl font-semibold text-navy-950">Filters</h2><button onClick={clear} className="text-xs font-bold text-charcoal-900/55 hover:text-error-600"><X className="inline size-3" /> Clear</button></div>{form}</aside><Drawer title="Filter products" side="left" isOpen={isOpen} onClose={() => setIsOpen(false)}>{form}</Drawer></>
}

function FilterGroup({ label, children }) {
  return <fieldset><legend className="mb-3 text-sm font-bold text-navy-950">{label}</legend>{children}</fieldset>
}
