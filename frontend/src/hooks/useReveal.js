import { useEffect } from 'react'

export function useReveal() {
  useEffect(() => {
    const nodes = document.querySelectorAll('[data-reveal]:not(.is-revealed)')
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (entry.isIntersecting) { entry.target.classList.add('is-revealed'); observer.unobserve(entry.target) }
    }), { threshold: 0.12 })
    nodes.forEach((node) => { node.style.setProperty('--reveal-delay', `${node.dataset.revealDelay || 0}ms`); observer.observe(node) })
    return () => observer.disconnect()
  })
}
