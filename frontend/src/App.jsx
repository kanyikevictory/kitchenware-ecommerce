import { Suspense } from 'react'
import { RouterProvider } from 'react-router-dom'
import { LoadingScreen } from './components/common/LoadingScreen'
import { router } from './routes/router'

function App() {
  return (
    <Suspense fallback={<LoadingScreen />}>
      <RouterProvider router={router} />
    </Suspense>
  )
}

export default App
