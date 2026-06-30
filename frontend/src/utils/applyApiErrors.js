export function applyApiErrors(error, setError) {
  Object.entries(error.errors ?? {}).forEach(([field, messages]) => {
    setError(field, { type: 'server', message: messages[0] })
  })
}
