import { Link, useRouteError } from 'react-router-dom'

export default function NotFoundPage() {
  const routeError = useRouteError()
  const isUnexpectedError = routeError && routeError.status !== 404

  return (
    <main className="grid min-h-screen place-items-center bg-ice-50 px-6 text-center">
      <div>
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-gold-600">
          {isUnexpectedError ? 'Something went wrong' : '404 · Page not found'}
        </p>
        <h1 className="mt-4 font-display text-5xl font-semibold text-navy-950">
          {isUnexpectedError ? 'That did not go to plan.' : 'This shelf is empty.'}
        </h1>
        <p className="mx-auto mt-4 max-w-md leading-7 text-charcoal-900/70">
          {isUnexpectedError
            ? 'Please return home and try again.'
            : 'The page may have moved, or it belongs to a later build phase.'}
        </p>
        <Link
          to="/"
          className="mt-8 inline-flex rounded-md bg-navy-950 px-6 py-3 text-sm font-bold text-white transition hover:bg-navy-900"
        >
          Return home
        </Link>
      </div>
    </main>
  )
}
