import { useEffect, useState } from 'react'

export function useBackToTop(threshold = 500) {
  const [isVisible, setIsVisible] = useState(false)
  useEffect(() => {
    const update = () => setIsVisible(window.scrollY > threshold)
    update()
    window.addEventListener('scroll', update, { passive: true })
    return () => window.removeEventListener('scroll', update)
  }, [threshold])
  return { isVisible, scrollToTop: () => window.scrollTo({ top: 0, behavior: 'smooth' }) }
}
