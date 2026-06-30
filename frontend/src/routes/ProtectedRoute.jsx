import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { LoadingScreen } from '../components/common/LoadingScreen'
import { useAuth } from '../hooks/useAuth'

export function ProtectedRoute() {
  const { isAuthenticated, isInitializing } = useAuth()
  const location = useLocation()
  if (isInitializing) return <LoadingScreen />
  return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace state={{ from: location }} />
}
