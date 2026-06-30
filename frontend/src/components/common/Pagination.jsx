import { ChevronLeft, ChevronRight } from 'lucide-react'

export function Pagination({ meta, onPageChange }) {
  if (!meta || meta.last_page <= 1) return null
  const current = meta.current_page
  const pages = Array.from({ length: meta.last_page }, (_, index) => index + 1).filter((page) => page === 1 || page === meta.last_page || Math.abs(page - current) <= 1)
  return <nav className="mt-12 flex items-center justify-center gap-2" aria-label="Pagination"><PageButton disabled={current === 1} onClick={() => onPageChange(current - 1)} label="Previous page"><ChevronLeft className="size-4" /></PageButton>{pages.map((page, index) => <span key={page} className="contents">{index > 0 && page - pages[index - 1] > 1 && <span className="px-1">…</span>}<PageButton active={page === current} onClick={() => onPageChange(page)} label={`Page ${page}`}>{page}</PageButton></span>)}<PageButton disabled={current === meta.last_page} onClick={() => onPageChange(current + 1)} label="Next page"><ChevronRight className="size-4" /></PageButton></nav>
}

function PageButton({ children, active, disabled, onClick, label }) {
  return <button type="button" aria-label={label} aria-current={active ? 'page' : undefined} disabled={disabled} onClick={onClick} className={`grid min-h-10 min-w-10 place-items-center rounded-md border px-3 text-sm font-bold transition disabled:opacity-35 ${active ? 'border-navy-950 bg-navy-950 text-white' : 'border-cool-gray-200 bg-white text-navy-950 hover:border-navy-950'}`}>{children}</button>
}
