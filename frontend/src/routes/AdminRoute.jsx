import { Navigate, Outlet } from 'react-router-dom'
import { LoadingScreen } from '../components/common/LoadingScreen'
import { useAuth } from '../hooks/useAuth'

export function AdminRoute() {
  const { user, isAuthenticated, isInitializing, hasPermission } = useAuth()
  if (isInitializing) return <LoadingScreen />
  if (!isAuthenticated) return <Navigate to="/login" replace />
  return user.role?.slug === 'admin' || hasPermission('admin.access') ? <Outlet /> : <Navigate to="/" replace />
}
