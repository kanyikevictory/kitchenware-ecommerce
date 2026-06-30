import { useEffect, useMemo, useState } from 'react'

export function useCatalogQuery(query, dependencies) {
  const signature = JSON.stringify(dependencies)
  const stableDependencies = useMemo(() => dependencies, [signature]) // eslint-disable-line react-hooks/exhaustive-deps
  const [result, setResult] = useState({ signature: null, data: null, error: null })
  const [retryKey, setRetryKey] = useState(0)

  useEffect(() => {
    const controller = new AbortController()
    query(controller.signal, stableDependencies)
      .then((data) => setResult({ signature, data, error: null }))
      .catch((error) => {
        if (error.cause?.code !== 'ERR_CANCELED') setResult({ signature, data: null, error })
      })
    return () => controller.abort()
  }, [query, retryKey, signature, stableDependencies])

  return {
    data: result.signature === signature ? result.data : null,
    error: result.signature === signature ? result.error : null,
    isLoading: result.signature !== signature,
    retry: () => setRetryKey((key) => key + 1),
  }
}
