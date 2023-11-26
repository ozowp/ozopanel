/**
 * All common routes
 *
 * @since 1.0.0
 */
import { lazy } from 'react'
import { createHashRouter } from 'react-router-dom'

const Dashboard = lazy(() => import('@pages/dashboard'))
const AdminMenuEdior = lazy(() => import('@/pages/admin-menu-editor'))
const AdminColumnEdior = lazy(() => import('@/pages/admin-column-editor'))
const Restrictions = lazy(() => import('@/pages/restrictions'))
const RestrictionsForm = lazy(() => import('@/pages/restrictions/form'))
const Settings = lazy(() => import('@pages/settings'))
const NotFound = lazy(() => import('@pages/404'))

const Router = createHashRouter([
  {
    path: '/',
    element: <Dashboard />,
  },
  {
    path: '/admin-menu-editor',
    element: <AdminMenuEdior />,
  },
  {
    path: '/admin-column-editor',
    element: <AdminColumnEdior />,
  },
  {
    path: '/admin-column-editor/:id',
    element: <AdminColumnEdior />,
  },
  {
    path: '/restrictions/:type',
    element: <Restrictions />,
  },
  {
    path: '/restrictions/:type/add',
    element: <RestrictionsForm />,
  },
  {
    path: '/restrictions/:type/:id/edit',
    element: <RestrictionsForm />,
  },
  {
    path: '/addons',
    element: <Settings />,
  },
  {
    path: '/settings',
    element: <Settings />,
  },
  {
    path: '/settings/:tab',
    element: <Settings />,
  },
  {
    path: '/settings/:tab/:subtab',
    element: <Settings />,
  },
  {
    path: '*',
    element: <NotFound />,
  },
])

export default Router
