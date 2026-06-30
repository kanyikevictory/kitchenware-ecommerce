import { Link } from 'react-router-dom';

export default function NotFoundPage() {
  return (
    <main className="mx-auto flex min-h-[65vh] max-w-3xl flex-col items-center justify-center px-6 text-center">
      <p className="font-sans text-sm uppercase tracking-[0.3em] text-navy">404</p>
      <h1 className="mt-4 font-serif text-5xl text-navy">This shelf is empty.</h1>
      <p className="mt-5 max-w-lg text-charcoal/70">The page you were looking for may have moved, but the kitchen is still open.</p>
      <Link to="/shop" className="mt-8 rounded-full bg-terracotta px-7 py-3 font-medium text-white">Browse the collection</Link>
    </main>
  );
}
