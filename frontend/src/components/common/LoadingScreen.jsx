export function LoadingScreen() {
  return (
    <div
      className="grid min-h-screen place-items-center bg-ice-50"
      role="status"
      aria-label="Loading page"
    >
      <span className="size-10 animate-spin rounded-full border-3 border-cool-gray-200 border-t-gold-500" />
      <span className="sr-only">Loading…</span>
    </div>
  )
}
