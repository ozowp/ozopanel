import { lazy, Suspense } from 'react';
import {
  HashRouter,
  Routes,
  Route,
} from "react-router-dom";

import Spinner from '@blocks/preloader/spinner';

const Dashboard = lazy(() => import('@components/dashboard'));
const Setting = lazy(() => import('@components/setting'));
const ProModal = lazy(() => import('@blocks/pro-alert/modal'));

const Home = () => {

  return (
    <HashRouter>
      <Suspense fallback={''}>
        <ProModal />
      </Suspense>

      <div className='wam-right-content-data'>
            <Suspense fallback={<Spinner />}>
              <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/setting" element={<Setting />} />
                <Route path="/setting/:tab" element={<Setting />} />
                <Route path="/setting/:tab/:subtab" element={<Setting />} />
                <Route path="/setting/:tab/:subtab/:insubtab" element={<Setting />} />
              </Routes>
            </Suspense>
          </div>
    </HashRouter>
  )
}
export default Home