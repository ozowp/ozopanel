/**
 * All common routes
 *
 * @since 1.0.0
 */
import Dashboard from "@pages/dashboard";
import Restrictions from "@/pages/restrictions";
import RestrictionsForm from "@/pages/restrictions/Form";
import AdminColumns from "@/pages/admin-columns";
import AdminColumnsForm from "@/pages/admin-columns/Form";
import Settings from "@pages/settings";

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
        path: '/admin-columns',
        element: AdminColumns,
    },
    {
        path: '/admin-columns/:id/edit',
        element: AdminColumnsForm,
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