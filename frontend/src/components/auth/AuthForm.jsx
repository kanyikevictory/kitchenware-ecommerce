import { Eye, EyeOff } from 'lucide-react'
import { useState } from 'react'

const fieldStyles = 'mt-2 w-full rounded-md border border-cool-gray-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-charcoal-900/35 focus:border-navy-950 focus:ring-3 focus:ring-navy-950/10'

export function AuthHeading({ eyebrow, title, description }) {
  return <header className="mb-8">
    <p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">{eyebrow}</p>
    <h1 className="mt-3 font-display text-5xl leading-none font-semibold text-navy-950">{title}</h1>
    {description && <p className="mt-4 text-sm leading-6 text-charcoal-900/60">{description}</p>}
    </header>
}

export function Field({ label, name, register, error, type = 'text', autoComplete, required = true, ...props }) {
  return <div>
    <label htmlFor={name} className="text-sm font-semibold text-navy-950">{label}</label>
    <input id={name} type={type} autoComplete={autoComplete} aria-invalid={Boolean(error)} aria-describedby={error ? `${name}-error` : undefined} className={fieldStyles} {...register(name, { required: required ? `${label} is required.` : false })} {...props} />{error && <p id={`${name}-error`} className="mt-1.5 text-xs text-error-600">{error.message}</p>}
    </div>
}

export function PasswordField({ label = 'Password', name = 'password', register, error, autoComplete = 'current-password', rules = {} }) {
  const [visible, setVisible] = useState(false)
  return <div>
    <label htmlFor={name} className="text-sm font-semibold text-navy-950">{label}</label>
    <div className="relative">
      <input id={name} type={visible ? 'text' : 'password'} autoComplete={autoComplete} aria-invalid={Boolean(error)} aria-describedby={error ? `${name}-error` : undefined} className={`${fieldStyles} pr-12`} {...register(name, { required: `${label} is required.`, ...rules })} />
    <button type="button" onClick={() => setVisible((value) => !value)} className="absolute top-1/2 right-3 mt-1 -translate-y-1/2 rounded p-1 text-charcoal-900/55 hover:text-navy-950" aria-label={visible ? 'Hide password' : 'Show password'}>{visible ? <EyeOff className="size-5" /> : <Eye className="size-5" />}</button></div>{error && <p id={`${name}-error`} className="mt-1.5 text-xs text-error-600">{error.message}</p>}
    </div>
}
