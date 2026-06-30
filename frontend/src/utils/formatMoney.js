export function formatMoney(value, currency = 'UGX') {
  const amount = Number(value)
  if (!Number.isFinite(amount)) return `${currency} 0`
  return `${currency} ${new Intl.NumberFormat('en-UG').format(amount)}`
}
