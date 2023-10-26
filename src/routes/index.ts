/**
 * All common routes
 *
 * @since 1.0.0
 */
import Dashboard from "@pages/dashboard";
// import Settings from "@pages/settings";
import Restrictions from "@/pages/restrictions";
import RestrictionsForm from "@/pages/restrictions/Form";

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
    /* {
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
    } */
];

export default routes;