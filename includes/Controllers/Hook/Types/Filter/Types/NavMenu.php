<?php

namespace OzoPanel\Controllers\Hook\Types\Filter\Types;

/**
 * Filte Hook Type: NavMenu
 *
 * @since 0.1.0
 */
class NavMenu {

    public function __construct() {
        add_filter( 'wp_nav_menu_objects', [ $this, 'exclude_menu_items' ], 10, 2 );
    }

    /**
     * Hide menu depend on condition
     *
     * @since 0.1.0
     */
    public function exclude_menu_items( $items ) {

        foreach ( $items as $key => $item ) {
            // Get the custom field value for the menu item
            $who_can_see = get_post_meta( $item->ID, '_ozopanel_who_can_see', true ) ?? '';
            $ozopanel_who_can_see_roles = (array) get_post_meta( $item->ID, '_ozopanel_who_can_see_roles', true ) ?? [];

            $has_roles = false;
            if ( $who_can_see === 'roles' ) {
                //check if user roles exist
                $current_user = wp_get_current_user(); // Get the current user object
                if ( $current_user instanceof \WP_User ) {
                    $user_roles = $current_user->roles; // Get the roles of the current user

                    foreach ( $ozopanel_who_can_see_roles as $value ) {
                        if ( in_array( $value, $user_roles ) ) {
                            $has_roles = true;
                            break;
                        }
                    }
                }
            }

            // Check if the menu item should be hidden
            if (
                ( $who_can_see === 'login' && ! is_user_logged_in() ) ||
                ( $who_can_see === 'logout' && is_user_logged_in() ) ||
                (
                    $who_can_see === 'roles' && ! $has_roles
                )
            ) {
                unset( $items[ $key ] ); // Remove the menu item from the list
            }
        }

        return $items;
    }
}
