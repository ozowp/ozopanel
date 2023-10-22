/**
 * All common routes
 *
 * @since 1.0.0
 */
import { lazy } from "react";
const Dashboard = lazy(() => import("@pages/dashboard"));
const Settings = lazy(() => import("@pages/settings"));
const Restrictions = lazy(() => import("@/pages/restrictions"));
const RestrictionsForm = lazy(() => import("@/pages/restrictions/Form"));

const routes = [
    {
        path: '/',
        element: Dashboard,
    },
    {
        path: '/restrictions/:type',
        element: Restrictions,
    },
    {
        path: '/restrictions/:type/add',
        element: RestrictionsForm,
    },
    {
        path: '/restrictions/:type/:id/edit',
        element: RestrictionsForm,
    },
    {
        path: '/settings',
        element: Settings,
    },
    {
        path: '/settings/:tab',
        element: Settings,
    },
    {
        path: '/settings/:tab/:subtab',
        element: Settings,
    }
];

export default routes;