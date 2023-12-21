/**
 * All common routes
 *
 * @since 1.0.0
 */
import { lazy } from 'react'
import { createHashRouter } from 'react-router-dom'


const Restrictions = lazy(() => import('@/pages/restrictions'))
const RestrictionsForm = lazy(() => import('@/pages/restrictions/form'))
const NotFound = lazy(() => import('@pages/404'))

const Router = createHashRouter([
  {
    path: '/',
    element: <Restrictions />,
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
    path: '*',
    element: <NotFound />,
  },
])

export default Router
