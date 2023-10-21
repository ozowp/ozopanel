/**
 * All common routes
 *
 * @since 1.0.0
 */
import { lazy } from "react";
const Dashboard = lazy(() => import("@pages/dashboard"));
const Setting = lazy(() => import("@pages/settings"));

const routes = [
    {
        path: '/',
        element: Dashboard,
    },
    {
        path: '/settings',
        element: Setting,
    },
    {
        path: '/settings/:tab',
        element: Setting,
    },
    {
        path: '/settings/:tab/:subtab',
        element: Setting,
    }
];

export default routes;

