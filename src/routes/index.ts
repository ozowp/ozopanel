/**
 * All common routes
 *
 * @since 1.0.0
 */
import { lazy } from "react";
const Dashboard = lazy(() => import("@pages/dashboard"));
const Settings = lazy(() => import("@pages/settings"));
const Users = lazy(() => import("@pages/users"));
const UsersForm = lazy(() => import("@pages/users/Form"));
const Roles = lazy(() => import("@pages/roles"));

const routes = [
    {
        path: '/',
        element: Dashboard,
    },
    {
        path: '/users',
        element: Users,
    },
    {
        path: '/users/add',
        element: UsersForm,
    },
    {
        path: '/users/:id/edit',
        element: UsersForm,
    },
    {
        path: '/roles',
        element: Roles,
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