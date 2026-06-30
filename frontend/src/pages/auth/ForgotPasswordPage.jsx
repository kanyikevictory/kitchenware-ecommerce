import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link } from 'react-router-dom'
import { AuthHeading, Field } from '../../components/auth/AuthForm'
import { Button } from '../../components/common/Button'
import { Alert } from '../../components/common/Feedback'
import { forgotPassword } from '../../services/authService'
import { applyApiErrors } from '../../utils/applyApiErrors'

export default function ForgotPasswordPage() {
  const [message, setMessage] = useState('')
  const [submitError, setSubmitError] = useState('')
  const { register, handleSubmit, setError, formState: { errors, isSubmitting } } = useForm()
  const onSubmit = async ({ email }) => {
    setSubmitError('')
    try { const response = await forgotPassword(email); setMessage(response.message) }
    catch (error) { applyApiErrors(error, setError); setSubmitError(error.message) }
  }
  return <><AuthHeading eyebrow="Account recovery" title="Reset your password" description="Enter your email and we’ll send reset instructions if an account exists." />{message ? <Alert tone="success" title="Check your inbox">{message}</Alert> : <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">{submitError && <Alert tone="error">{submitError}</Alert>}<Field label="Email address" name="email" type="email" autoComplete="email" register={register} error={errors.email} /><Button type="submit" isLoading={isSubmitting} className="w-full">Send reset link</Button></form>}<p className="mt-7 text-center text-sm"><Link to="/login" className="font-bold text-navy-950 hover:text-gold-600">Back to sign in</Link></p></>
}
