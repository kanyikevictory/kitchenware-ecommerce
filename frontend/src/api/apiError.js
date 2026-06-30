export class ApiError extends Error {
  constructor(message, { status = null, errors = {}, cause } = {}) {
    super(message, { cause })
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }

  static fromAxiosError(error) {
    if (error instanceof ApiError) return error

    const response = error.response
    const message =
      response?.data?.message ??
      (error.code === 'ECONNABORTED'
        ? 'The request took too long. Please try again.'
        : 'We could not connect to the store. Please try again.')

    return new ApiError(message, {
      status: response?.status ?? null,
      errors: response?.data?.errors ?? {},
      cause: error,
    })
  }
}
