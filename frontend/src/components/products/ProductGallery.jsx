import { ChevronLeft, ChevronRight, Expand, ImageOff, X } from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'

export function ProductGallery({ images = [], productName }) {
  const ordered = [...images].sort((a, b) => Number(b.is_primary) - Number(a.is_primary) || a.sort_order - b.sort_order)
  const [activeIndex, setActiveIndex] = useState(0)
  const [isZoomed, setIsZoomed] = useState(false)
  const active = ordered[activeIndex]
  const move = useCallback((direction) => {
    setActiveIndex((index) => (index + direction + ordered.length) % ordered.length)
  }, [ordered.length])

  useEffect(() => {
    if (!isZoomed) return undefined
    const onKeyDown = (event) => { if (event.key === 'Escape') setIsZoomed(false); if (event.key === 'ArrowLeft') move(-1); if (event.key === 'ArrowRight') move(1) }
    document.addEventListener('keydown', onKeyDown)
    return () => document.removeEventListener('keydown', onKeyDown)
  }, [isZoomed, move])

  if (!active) return <div className="grid aspect-square place-items-center rounded-xl bg-ice-50 text-cool-gray-200"><ImageOff className="size-16" /><span className="sr-only">No product image available</span></div>
  return <><div><button type="button" onClick={() => setIsZoomed(true)} className="group relative block aspect-square w-full overflow-hidden rounded-xl bg-ice-50"><img src={active.url} alt={active.alt_text || productName} className="size-full object-cover" /><span className="absolute right-4 bottom-4 grid size-11 place-items-center rounded-full bg-white/90 text-navy-950 shadow-soft"><Expand className="size-5" /></span></button>{ordered.length > 1 && <div className="mt-4 grid grid-cols-5 gap-3">{ordered.map((image, index) => <button key={image.id} type="button" onClick={() => setActiveIndex(index)} aria-label={`View image ${index + 1}`} className={`aspect-square overflow-hidden rounded-md border-2 ${index === activeIndex ? 'border-gold-500' : 'border-transparent'}`}><img src={image.url} alt="" className="size-full object-cover" loading="lazy" /></button>)}</div>}</div>{isZoomed && <div className="fixed inset-0 z-[70] grid place-items-center bg-navy-950/95 p-4" role="dialog" aria-modal="true" aria-label={`${productName} image viewer`}><button onClick={() => setIsZoomed(false)} className="absolute top-5 right-5 rounded-full bg-white p-3 text-navy-950" aria-label="Close image viewer"><X className="size-5" /></button>{ordered.length > 1 && <><button onClick={() => move(-1)} className="absolute left-5 rounded-full bg-white p-3 text-navy-950" aria-label="Previous image"><ChevronLeft /></button><button onClick={() => move(1)} className="absolute right-5 rounded-full bg-white p-3 text-navy-950" aria-label="Next image"><ChevronRight /></button></>}<img src={active.url} alt={active.alt_text || productName} className="max-h-[90vh] max-w-[90vw] object-contain" /></div>}</>
}
