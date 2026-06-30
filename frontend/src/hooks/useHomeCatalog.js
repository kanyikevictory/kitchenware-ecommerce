import { useEffect, useState } from 'react'
import { getFeaturedCategories, getFeaturedProducts, getNewestProducts } from '../services/catalogService'

const initialState = { categories: [], featured: [], newest: [], isLoading: true, error: null }

export function useHomeCatalog() {
  const [state, setState] = useState(initialState)
  const [requestKey, setRequestKey] = useState(0)

  useEffect(() => {
    const controller = new AbortController()
    Promise.all([
      getFeaturedCategories(controller.signal),
      getFeaturedProducts(controller.signal),
      getNewestProducts(controller.signal),
    ]).then(([categories, featured, newest]) => {
      setState({ categories, featured, newest, isLoading: false, error: null })
    }).catch((error) => {
      if (error.cause?.code !== 'ERR_CANCELED') setState((current) => ({ ...current, isLoading: false, error }))
    })
    return () => controller.abort()
  }, [requestKey])

  const retry = () => {
    setState((current) => ({ ...current, isLoading: true, error: null }))
    setRequestKey((key) => key + 1)
  }

  return { ...state, retry }
}
