import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { AuthHeading, Field, PasswordField } from '../../components/auth/AuthForm'
import { Button } from '../../components/common/Button'
import { Alert } from '../../components/common/Feedback'
import { resetPassword } from '../../services/authService'
import { applyApiErrors } from '../../utils/applyApiErrors'

export default function ResetPasswordPage() {
  const [params] = useSearchParams()
  const navigate = useNavigate()
  const [submitError, setSubmitError] = useState('')
  const { register, handleSubmit, setError, formState: { errors, isSubmitting } } = useForm({ defaultValues: { email: params.get('email') ?? '' } })
  const token = params.get('token')
  const onSubmit = async (values) => {
    if (!token) return setSubmitError('This reset link is incomplete. Please request a new one.')
    setSubmitError('')
    try { await resetPassword({ ...values, token }); navigate('/login', { replace: true, state: { reset: true } }) }
    catch (error) { applyApiErrors(error, setError); setSubmitError(error.message) }
  }
  return <><AuthHeading eyebrow="Choose something memorable" title="New password" description="Your new password will sign out other active sessions." />{submitError && <div className="mb-5"><Alert tone="error">{submitError}</Alert></div>}<form onSubmit={handleSubmit(onSubmit)} className="space-y-5"><Field label="Email address" name="email" type="email" register={register} error={errors.email} /><PasswordField register={register} error={errors.password} autoComplete="new-password" rules={{ minLength: { value: 8, message: 'Use at least 8 characters.' } }} /><PasswordField label="Confirm password" name="password_confirmation" register={register} error={errors.password_confirmation} autoComplete="new-password" rules={{ validate: (value, values) => value === values.password || 'Passwords do not match.' }} /><Button type="submit" isLoading={isSubmitting} className="w-full">Update password</Button></form><p className="mt-7 text-center text-sm"><Link to="/forgot-password" className="font-bold text-navy-950">Request another link</Link></p></>
}
