import { AlertCircle, CheckCircle2, Info, PackageOpen, RefreshCw, TriangleAlert } from 'lucide-react'
import { Button } from './Button'

const tones = {
  info: ['border-blue-200 bg-blue-50 text-blue-900', Info],
  success: ['border-green-200 bg-green-50 text-green-900', CheckCircle2],
  warning: ['border-amber-200 bg-amber-50 text-amber-950', TriangleAlert],
  error: ['border-red-200 bg-red-50 text-red-900', AlertCircle],
}

export function Alert({ children, title, tone = 'info' }) {
  const [styles, Icon] = tones[tone]
  return <div className={`flex gap-3 rounded-lg border p-4 ${styles}`} role={tone === 'error' ? 'alert' : 'status'}><Icon className="mt-0.5 size-5 shrink-0" /><div>{title && <p className="font-bold">{title}</p>}<div className="mt-0.5 text-sm leading-6">{children}</div></div></div>
}

export function EmptyState({ title = 'Nothing here yet', message, action }) {
  return <div className="rounded-xl border border-dashed border-cool-gray-200 bg-ice-50 px-6 py-14 text-center"><PackageOpen className="mx-auto size-10 text-gold-600" /><h2 className="mt-4 font-display text-3xl font-semibold text-navy-950">{title}</h2>{message && <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-charcoal-900/65">{message}</p>}{action && <div className="mt-6">{action}</div>}</div>
}

export function ErrorState({ title = 'We hit a snag', message = 'Please try again in a moment.', onRetry }) {
  return <div className="rounded-xl border border-red-200 bg-red-50 px-6 py-14 text-center" role="alert"><AlertCircle className="mx-auto size-10 text-error-600" /><h2 className="mt-4 font-display text-3xl font-semibold text-navy-950">{title}</h2><p className="mx-auto mt-2 max-w-md text-sm text-charcoal-900/65">{message}</p>{onRetry && <Button className="mt-6" onClick={onRetry}><RefreshCw className="size-4" />Try again</Button>}</div>
}
