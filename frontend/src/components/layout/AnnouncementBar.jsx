import { ShieldCheck, Truck } from 'lucide-react'

export function AnnouncementBar() {
  return <div className="bg-navy-950 px-4 py-2.5 text-white">
    <div className="mx-auto flex max-w-7xl items-center justify-center gap-2 text-center text-xs font-semibold sm:justify-between"><p className="flex items-center gap-2">
      <Truck className="size-4 text-gold-500" />Free Kampala delivery on orders over UGX 250,000</p><p className="hidden items-center gap-2 sm:flex"><ShieldCheck className="size-4 text-gold-500" />Quality guaranteed</p>
      </div>
      </div>
}
