const styles = 'mt-2 w-full rounded-md border border-cool-gray-200 bg-white px-4 py-3 text-sm transition placeholder:text-charcoal-900/40 hover:border-navy-950/40 focus:border-navy-950 focus:outline-none focus:ring-3 focus:ring-navy-950/10 disabled:bg-ice-50'

function Field({ label, id, hint, error, required, children }) {
  return <div><label htmlFor={id} className="text-sm font-semibold text-navy-950">{label}{required && <span className="ml-1 text-error-600">*</span>}</label>{children}{error ? <p id={`${id}-error`} className="mt-2 text-sm text-error-600">{error}</p> : hint ? <p id={`${id}-hint`} className="mt-2 text-sm text-charcoal-900/60">{hint}</p> : null}</div>
}

export function Input({ label, id, hint, error, required, className = '', ...props }) {
  return <Field {...{ label, id, hint, error, required }}>
    <input id={id} required={required} aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} className={`${styles} ${className}`} {...props} />
    </Field>
}

export function Select({ label, id, hint, error, required, children, className = '', ...props }) {
  return <Field {...{ label, id, hint, error, required }}><select id={id} required={required} aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} className={`${styles} ${className}`} {...props}>{children}</select></Field>
}
