import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import {
  AuthHeading,
  Field,
  PasswordField,
} from '../../components/auth/AuthForm'
import { Button } from '../../components/common/Button'
import { Alert } from '../../components/common/Feedback'
import { useAuth } from '../../hooks/useAuth'
import { applyApiErrors } from '../../utils/applyApiErrors'

export default function RegisterPage() {
  const { signUp } = useAuth()
  const navigate = useNavigate()
  const [submitError, setSubmitError] = useState('')

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm()

  const onSubmit = async (values) => {
    setSubmitError('')

    try {
      await signUp(values)
      navigate('/verify-email', { replace: true })
    } catch (caughtError) {
      if (caughtError instanceof Error) {
        applyApiErrors(caughtError, setError)
        setSubmitError(caughtError.message)
      } else {
        setSubmitError('Unable to create your account. Please try again.')
      }
    }
  }

  return (
    <>
      <AuthHeading
        eyebrow="Join the table"
        title="Create account"
        description="Save favourites and enjoy a smoother checkout."
      />

      {submitError && (
        <div className="mb-5">
          <Alert tone="error">{submitError}</Alert>
        </div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <Field
          label="Full name"
          name="name"
          autoComplete="name"
          register={register}
          error={errors.name}
        />

        <Field
          label="Email address"
          name="email"
          type="email"
          autoComplete="email"
          register={register}
          error={errors.email}
        />

        <Field
          label="Phone number"
          name="phone"
          type="tel"
          autoComplete="tel"
          register={register}
          error={errors.phone}
          required={false}
          placeholder="+256 700 000 000"
        />

        <PasswordField
          register={register}
          error={errors.password}
          autoComplete="new-password"
          rules={{
            minLength: {
              value: 8,
              message: 'Use at least 8 characters.',
            },
          }}
        />

        <PasswordField
          label="Confirm password"
          name="password_confirmation"
          register={register}
          error={errors.password_confirmation}
          autoComplete="new-password"
          rules={{
            validate: (value, formValues) =>
              value === formValues.password ||
              'Passwords do not match.',
          }}
        />

        <Button
          type="submit"
          isLoading={isSubmitting}
          className="w-full"
        >
          Create account
        </Button>
      </form>

      <p className="mt-7 text-center text-sm text-charcoal-900/60">
        Already have an account?{' '}
        <Link to="/login" className="font-bold text-navy-950">
          Sign in
        </Link>
      </p>
    </>
  )
}