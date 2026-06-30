import { MailCheck } from 'lucide-react'
import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { Button } from '../../components/common/Button'
import { Alert } from '../../components/common/Feedback'
import { useAuth } from '../../hooks/useAuth'
import { resendVerification } from '../../services/authService'

export default function VerifyEmailPage() {
  const { user, refreshUser } = useAuth()
  const navigate = useNavigate()
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [isSending, setIsSending] = useState(false)
  const checkStatus = async () => { setError(''); try { const current = await refreshUser(); if (current.email_verified_at) navigate('/account', { replace: true }); else setError('Your email is not verified yet.') } catch (requestError) { setError(requestError.message) } }
  const resend = async () => { setIsSending(true); setError(''); try { const response = await resendVerification(); setMessage(response.message) } catch (requestError) { setError(requestError.message) } finally { setIsSending(false) } }
  if (user?.email_verified_at) return <Navigate to="/account" replace />
  return <div className="text-center"><div className="mx-auto grid size-16 place-items-center rounded-full bg-amber-100 text-gold-600"><MailCheck className="size-8" /></div><h1 className="mt-6 font-display text-5xl font-semibold text-navy-950">Check your inbox</h1><p className="mt-4 leading-7 text-charcoal-900/65">We sent a verification link to <strong className="text-navy-950">{user?.email}</strong>. Open it, then return here to continue.</p>{message && <div className="mt-6 text-left"><Alert tone="success">{message}</Alert></div>}{error && <div className="mt-6 text-left"><Alert tone="error">{error}</Alert></div>}<div className="mt-8 flex flex-col gap-3"><Button onClick={checkStatus}>I’ve verified my email</Button><Button variant="ghost" isLoading={isSending} onClick={resend}>Resend verification email</Button></div></div>
}
