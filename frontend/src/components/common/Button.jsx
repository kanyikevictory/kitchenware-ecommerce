import { LoaderCircle } from 'lucide-react'

const variants = {
  primary: 'bg-navy-950 text-white hover:bg-navy-900',
  secondary: 'border border-navy-950 text-navy-950 hover:bg-navy-950 hover:text-white',
  gold: 'bg-gold-500 text-navy-950 hover:bg-gold-600 hover:text-white',
  ghost: 'text-navy-950 hover:bg-ice-50',
  danger: 'bg-error-600 text-white hover:bg-red-700',
}

export function Button({ children, className = '', variant = 'primary', isLoading = false, disabled, type = 'button', ...props }) {
  return <button type={type} className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-md px-5 py-2.5 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-55 ${variants[variant]} ${className}`} disabled={disabled || isLoading} {...props}>{isLoading && <LoaderCircle className="size-4 animate-spin" aria-hidden="true" />}{children}</button>
}
