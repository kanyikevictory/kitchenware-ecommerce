import { Outlet, ScrollRestoration } from 'react-router-dom'
import { ToastProvider } from '../../context/ToastContext'
import { ToastViewport } from '../common/ToastViewport'
import { AnnouncementBar } from './AnnouncementBar'
import { BackToTop } from './BackToTop'
import { Footer } from './Footer'
import { Navbar } from './Navbar'

export function RootLayout() {
  return <ToastProvider><a href="#main-content" className="fixed left-4 top-4 z-70 -translate-y-24 rounded-md bg-navy-950 px-4 py-2 text-sm font-semibold text-white transition-transform focus:translate-y-0">
    Skip to content</a><AnnouncementBar /><Navbar /><main id="main-content" tabIndex="-1"><Outlet /></main><Footer /><BackToTop />
    <ToastViewport />
    <ScrollRestoration />
    </ToastProvider>
}
