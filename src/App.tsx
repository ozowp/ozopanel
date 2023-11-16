import { Suspense, FC } from 'react'
import { HashRouter, Routes, Route } from 'react-router-dom'
import { ToastContainer } from 'react-toastify'
import 'react-toastify/dist/ReactToastify.css'
import routes from './routes'

import Spinner from '@components/preloader/spinner'

const App: FC = () => {
  return (
    <div className="ozop-content">
      <HashRouter>
        <ToastContainer hideProgressBar />
        <Suspense fallback={<Spinner />}>
          <Routes>
            {routes.map((route, i) => (
              <Route key={i} path={route.path} element={<route.element />} />
            ))}
          </Routes>
        </Suspense>
      </HashRouter>
    </div>
  )
}

export default App
