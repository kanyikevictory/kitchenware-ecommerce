import { CheckCircle2, Info, TriangleAlert, X } from 'lucide-react'
import { useToast } from '../../hooks/useToast'

const icons = { success: CheckCircle2, error: TriangleAlert, info: Info }

export function ToastViewport() {
  const { toasts, dismissToast } = useToast()
  return <div className="fixed right-4 top-4 z-60 flex w-[min(calc(100vw-2rem),24rem)] flex-col gap-3" aria-live="polite" aria-label="Notifications">{toasts.map((toast) => { const Icon = icons[toast.tone] ?? Info; return <div key={toast.id} className="flex items-start gap-3 rounded-lg border border-cool-gray-200 bg-white p-4 text-sm text-navy-950 shadow-soft"><Icon className={`size-5 shrink-0 ${toast.tone === 'error' ? 'text-error-600' : toast.tone === 'success' ? 'text-success-600' : 'text-gold-600'}`} /><p className="flex-1 leading-5">{toast.message}</p><button onClick={() => dismissToast(toast.id)} aria-label="Dismiss notification"><X className="size-4" /></button></div> })}</div>
}
