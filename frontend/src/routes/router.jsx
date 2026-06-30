import { createElement, lazy } from 'react'
import { createBrowserRouter } from 'react-router-dom'
import { RootLayout } from '../components/layout/RootLayout'
import { AuthLayout } from '../components/auth/AuthLayout'
import { AdminRoute } from './AdminRoute'
import { ProtectedRoute } from './ProtectedRoute'

const homePage = lazy(() => import('../pages/HomePage'))
const notFoundPage = lazy(() => import('../pages/NotFoundPage'))
const loginPage = lazy(() => import('../pages/auth/LoginPage'))
const registerPage = lazy(() => import('../pages/auth/RegisterPage'))
const forgotPasswordPage = lazy(() => import('../pages/auth/ForgotPasswordPage'))
const resetPasswordPage = lazy(() => import('../pages/auth/ResetPasswordPage'))
const verifyEmailPage = lazy(() => import('../pages/auth/VerifyEmailPage'))
const accountPage = lazy(() => import('../pages/customer/AccountPage'))
const adminPage = lazy(() => import('../pages/admin/AdminPage'))
const shopPage = lazy(() => import('../pages/products/ShopPage'))
const categoriesPage = lazy(() => import('../pages/products/CategoriesPage'))
const categoryPage = lazy(() => import('../pages/products/CategoryPage'))
const productDetailPage = lazy(() => import('../pages/products/ProductDetailPage'))
const searchPage = lazy(() => import('../pages/products/SearchPage'))

export const router = createBrowserRouter([
  {
    path: '/',
    element: <RootLayout />,
    errorElement: createElement(notFoundPage),
    children: [
      { index: true, element: createElement(homePage) },
      { path: 'shop', element: createElement(shopPage) },
      { path: 'new-arrivals', element: createElement(shopPage, { newest: true }) },
      { path: 'categories', element: createElement(categoriesPage) },
      { path: 'category/:slug', element: createElement(categoryPage) },
      { path: 'product/:slug', element: createElement(productDetailPage) },
      { path: 'search', element: createElement(searchPage) },
      {
        element: <AuthLayout />,
        children: [
          { path: 'login', element: createElement(loginPage) },
          { path: 'register', element: createElement(registerPage) },
          { path: 'forgot-password', element: createElement(forgotPasswordPage) },
          { path: 'reset-password', element: createElement(resetPasswordPage) },
          { element: <ProtectedRoute />, children: [{ path: 'verify-email', element: createElement(verifyEmailPage) }] },
        ],
      },
      { element: <ProtectedRoute />, children: [{ path: 'account', element: createElement(accountPage) }] },
      { element: <AdminRoute />, children: [{ path: 'admin', element: createElement(adminPage) }] },
      { path: '*', element: createElement(notFoundPage) },
    ],
  },
])
