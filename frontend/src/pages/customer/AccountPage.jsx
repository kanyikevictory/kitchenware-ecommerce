import { Link } from 'react-router-dom'
import { Button } from '../../components/common/Button'
import { useAuth } from '../../hooks/useAuth'

export default function AccountPage() {
  const { user, signOut } = useAuth()
  return <section className="mx-auto max-w-4xl px-4 py-20 sm:px-6"><p className="text-xs font-bold uppercase tracking-[0.24em] text-gold-600">Your account</p><h1 className="mt-3 font-display text-5xl font-semibold text-navy-950">Welcome, {user.name.split(' ')[0]}.</h1><p className="mt-4 text-charcoal-900/65">Profile, addresses, orders, and settings arrive in Phase 8.</p>{!user.email_verified_at && <Link to="/verify-email" className="mt-6 inline-block font-bold text-gold-600">Verify your email to unlock shopping features →</Link>}<div className="mt-10"><Button variant="secondary" onClick={signOut}>Sign out</Button></div></section>
}
