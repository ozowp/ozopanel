/**
 * All common routes
 *
 * @since 0.1.0
 */

/**
 * External dependencies
 */
import { lazy } from '@wordpress/element';
import { createHashRouter } from 'react-router-dom';

/**
 * Internal dependencies
 */
const Dashboard = lazy(() => import('@pages/dashboard'));
const AdminMenuEditor = lazy(() => import('@pages/admin-menu-editor'));
const AdminColumnEditor = lazy(() => import('@pages/admin-column-editor'));
const Restrictions = lazy(() => import('@pages/restrictions'));
const RestrictionsForm = lazy(() => import('@pages/restrictions/form'));
const Settings = lazy(() => import('@pages/settings'));
const Addons = lazy(() => import('@pages/addons'));
const NotFound = lazy(() => import('@pages/404'));

const Router = createHashRouter([
	{
		path: '/',
		element: <Dashboard />,
	},
	{
		path: '/admin-menu-editor',
		element: <AdminMenuEditor />,
	},
	{
		path: '/admin-column-editor',
		element: <AdminColumnEditor />,
	},
	{
		path: '/admin-column-editor/:id',
		element: <AdminColumnEditor />,
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
		path: '/addons',
		element: <Addons />,
	},
	{
		path: '*',
		element: <NotFound />,
	},
]);

export default Router;
