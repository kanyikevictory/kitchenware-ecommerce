import { useEffect, useId } from 'react'
import { X } from 'lucide-react'

function useOverlay(isOpen, onClose) {
  useEffect(() => {
    if (!isOpen) return undefined
    const overflow = document.body.style.overflow
    const onKeyDown = (event) => event.key === 'Escape' && onClose()
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeyDown)
    return () => { document.body.style.overflow = overflow; document.removeEventListener('keydown', onKeyDown) }
  }, [isOpen, onClose])
}

export function Modal({ isOpen, onClose, title, children, footer }) {
  const titleId = useId()
  useOverlay(isOpen, onClose)
  if (!isOpen) return null
  return <div className="fixed inset-0 z-50 grid place-items-center bg-navy-950/55 p-4 backdrop-blur-sm" onMouseDown={(event) => event.target === event.currentTarget && onClose()}><section role="dialog" aria-modal="true" aria-labelledby={titleId} className="max-h-[90vh] w-full max-w-lg overflow-auto rounded-xl bg-white shadow-2xl"><header className="flex items-center justify-between border-b border-cool-gray-200 p-5"><h2 id={titleId} className="font-display text-2xl font-semibold text-navy-950">{title}</h2><button onClick={onClose} className="rounded-full p-2 hover:bg-ice-50" aria-label="Close dialog"><X className="size-5" /></button></header><div className="p-5">{children}</div>{footer && <footer className="flex justify-end gap-3 border-t border-cool-gray-200 p-5">{footer}</footer>}</section></div>
}

export function Drawer({ isOpen, onClose, title, children, side = 'right' }) {
  const titleId = useId()
  useOverlay(isOpen, onClose)
  if (!isOpen) return null
  return <div className="fixed inset-0 z-50 bg-navy-950/55 backdrop-blur-sm" onMouseDown={(event) => event.target === event.currentTarget && onClose()}><aside role="dialog" aria-modal="true" aria-labelledby={titleId} className={`absolute inset-y-0 ${side === 'left' ? 'left-0' : 'right-0'} flex w-[min(90vw,26rem)] flex-col bg-white shadow-2xl`}><header className="flex items-center justify-between border-b border-cool-gray-200 p-5"><h2 id={titleId} className="font-display text-2xl font-semibold text-navy-950">{title}</h2><button onClick={onClose} className="rounded-full p-2 hover:bg-ice-50" aria-label="Close drawer"><X className="size-5" /></button></header><div className="flex-1 overflow-y-auto p-5">{children}</div></aside></div>
}
