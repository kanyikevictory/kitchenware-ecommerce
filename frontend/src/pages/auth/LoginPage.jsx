import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { AuthHeading, Field, PasswordField } from '../../components/auth/AuthForm'
import { Button } from '../../components/common/Button'
import { Alert } from '../../components/common/Feedback'
import { useAuth } from '../../hooks/useAuth'
import { applyApiErrors } from '../../utils/applyApiErrors'

export default function LoginPage() {
  const { signIn } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [submitError, setSubmitError] = useState('')
  const { register, handleSubmit, setError, formState: { errors, isSubmitting } } = useForm()
  const onSubmit = async (values) => {
    setSubmitError('')
    try { await signIn(values); navigate(location.state?.from?.pathname ?? '/account', { replace: true }) }
    catch (error) { applyApiErrors(error, setError); setSubmitError(error.message) }
  }
  return <>
  <AuthHeading eyebrow="Welcome back" title="Sign in" description="Access your orders, wishlist, and saved details." />
  {submitError && <div className="mb-5"><Alert tone="error">{submitError}</Alert></div>}
  <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
    <Field label="Email address" name="email" type="email" autoComplete="email" register={register} error={errors.email} />
    <div>
      <PasswordField register={register} error={errors.password} />
      <div className="mt-2 text-right"><Link to="/forgot-password" className="text-xs font-bold text-navy-950 hover:text-gold-600">Forgot password?</Link>
      </div>
      </div>
      <Button type="submit" isLoading={isSubmitting} className="w-full">Sign in</Button></form><p className="mt-7 text-center text-sm text-charcoal-900/60">New to Kitchen Store? <Link to="/register" className="font-bold text-navy-950 hover:text-gold-600">Create an account</Link></p></>
}
