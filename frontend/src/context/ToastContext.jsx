import { useCallback, useMemo, useState } from 'react'
import { ToastContext } from './toastContextValue'

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])
  const dismissToast = useCallback((id) => setToasts((items) => items.filter((item) => item.id !== id)), [])
  const showToast = useCallback((message, options = {}) => {
    const id = crypto.randomUUID()
    setToasts((items) => [...items, { id, message, tone: options.tone ?? 'info' }])
    window.setTimeout(() => dismissToast(id), options.duration ?? 4500)
    return id
  }, [dismissToast])
  const value = useMemo(() => ({ toasts, showToast, dismissToast }), [dismissToast, showToast, toasts])
  return <ToastContext.Provider value={value}>{children}</ToastContext.Provider>
}
