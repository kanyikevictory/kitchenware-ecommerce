import { useCallback, useEffect, useMemo, useState } from 'react'
import { setAccessTokenGetter } from '../api/apiClient'
import * as authService from '../services/authService'
import { tokenStorage } from '../services/tokenStorage'
import { AuthContext } from './authContextValue'

let accessToken = tokenStorage.get()
setAccessTokenGetter(() => accessToken)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [isInitializing, setIsInitializing] = useState(Boolean(accessToken))

  const clearSession = useCallback(() => {
    accessToken = null
    tokenStorage.clear()
    setUser(null)
  }, [])

  const establishSession = useCallback((payload) => {
    accessToken = payload.token
    tokenStorage.set(payload.token)
    setUser(payload.user)
  }, [])

  useEffect(() => {
    if (!accessToken) return undefined
    let active = true
    authService.getCurrentUser()
      .then((currentUser) => active && setUser(currentUser))
      .catch(() => active && clearSession())
      .finally(() => active && setIsInitializing(false))
    return () => { active = false }
  }, [clearSession])

  useEffect(() => {
    const onUnauthorized = () => clearSession()
    window.addEventListener('auth:unauthorized', onUnauthorized)
    return () => window.removeEventListener('auth:unauthorized', onUnauthorized)
  }, [clearSession])

  const signIn = useCallback(async (credentials) => {
    const response = await authService.login(credentials)
    establishSession(response.data)
    return response
  }, [establishSession])

  const signUp = useCallback(async (details) => {
    const response = await authService.register(details)
    establishSession(response.data)
    return response
  }, [establishSession])

  const signOut = useCallback(async () => {
    try { await authService.logout() } finally { clearSession() }
  }, [clearSession])

  const refreshUser = useCallback(async () => {
    const currentUser = await authService.getCurrentUser()
    setUser(currentUser)
    return currentUser
  }, [])

  const hasPermission = useCallback((permission) => user?.permissions?.includes(permission) ?? false, [user])
  const value = useMemo(() => ({ user, isAuthenticated: Boolean(user), isInitializing, signIn, signUp, signOut, refreshUser, hasPermission }), [hasPermission, isInitializing, refreshUser, signIn, signOut, signUp, user])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
