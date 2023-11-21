import { FC, Suspense, lazy } from 'react'
import { RouterProvider } from 'react-router-dom'
import { ToastContainer } from 'react-toastify'
import 'react-toastify/dist/ReactToastify.css'
import Router from './routes'

import Spinner from '@components/preloader/spinner'
const NotFound = lazy(() => import('@pages/404'))

const App: FC = () => {
  return (
    <div className="ozop-content mt-5">
      <ToastContainer hideProgressBar />
      <Suspense fallback={<Spinner />}>
        <RouterProvider router={Router} fallbackElement={<NotFound />} />
      </Suspense>
    </div>
  )
}

export default App
