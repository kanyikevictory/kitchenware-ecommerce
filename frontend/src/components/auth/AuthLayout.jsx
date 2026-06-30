import { ChefHat, ShieldCheck } from 'lucide-react'
import { Link, Outlet } from 'react-router-dom'

export function AuthLayout() {
  return <div className="grid min-h-[calc(100vh-9rem)] bg-white lg:grid-cols-[0.9fr_1.1fr]">
  <aside className="relative hidden overflow-hidden bg-navy-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
    <div className="absolute -top-40 -right-40 size-96 rounded-full border border-gold-500/20" /><Link to="/" className="relative font-display text-3xl font-bold">Kitchen<span className="text-gold-500">Store</span></Link><div className="relative max-w-lg">
      <ChefHat className="size-10 text-gold-500" /><p className="mt-7 font-display text-5xl leading-tight font-semibold">A beautiful kitchen begins with thoughtful choices.</p>
      <p className="mt-5 leading-7 text-white/60">Sign in to save favourites, manage your orders, and make checkout effortless.</p></div>
      <p className="relative flex items-center gap-2 text-sm text-white/60">
      <ShieldCheck className="size-5 text-gold-500" />Your details are protected and never sold.</p></aside><section className="flex items-center justify-center px-4 py-16 sm:px-8"><div className="w-full max-w-md"><Outlet /></div></section></div>
}
