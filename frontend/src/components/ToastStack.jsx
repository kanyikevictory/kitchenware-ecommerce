import { Check } from 'lucide-react'
import { useApp } from '../context/AppContext'

export default function ToastStack() {
  const { toasts } = useApp()
  return <div aria-live="polite" className="fixed bottom-5 right-5 z-50 flex max-w-[calc(100vw-2.5rem)] flex-col gap-2">{toasts.map((toast) => <div key={toast.id} className="animate-fade-in flex items-center gap-3 rounded-lg bg-navy px-4 py-3 text-sm text-cream shadow-md"><span className="grid h-6 w-6 place-items-center rounded-full border border-cream/30"><Check size={14} /></span>{toast.message}</div>)}</div>
}
