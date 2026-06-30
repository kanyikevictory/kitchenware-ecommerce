const tones = { navy: 'bg-navy-950 text-white', gold: 'bg-amber-100 text-amber-900', success: 'bg-green-100 text-green-800', error: 'bg-red-100 text-red-800', neutral: 'bg-ice-50 text-charcoal-900' }

export function Card({ children, className = '', as: Component = 'div', ...props }) {
  return <Component className={`rounded-xl border border-cool-gray-200 bg-white shadow-soft ${className}`} {...props}>{children}</Component>
}
export function Badge({ children, tone = 'neutral', className = '' }) {
  return <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-bold ${tones[tone]} ${className}`}>{children}</span>
}
export function Skeleton({ className = '' }) {
  return <span className={`block animate-pulse rounded-md bg-cool-gray-200/70 ${className}`} aria-hidden="true" />
}
