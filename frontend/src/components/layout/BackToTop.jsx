import { ArrowUp } from 'lucide-react'
import { useBackToTop } from '../../hooks/useBackToTop'

export function BackToTop() {
  const { isVisible, scrollToTop } = useBackToTop()
  return <button onClick={scrollToTop} aria-label="Back to top" className={`fixed right-5 bottom-5 z-30 grid size-11 place-items-center rounded-full bg-navy-950 text-white shadow-soft transition duration-300 hover:-translate-y-1 hover:bg-gold-600 ${isVisible ? 'translate-y-0 opacity-100' : 'pointer-events-none translate-y-4 opacity-0'}`}><ArrowUp className="size-5" /></button>
}
