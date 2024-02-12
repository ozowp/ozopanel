<?php
namespace OzoPanel\Hooks\Types\Filter;

use OzoPanel\Hooks\Types\Filter\Types\ActionLink;
use OzoPanel\Hooks\Types\Filter\Types\AdminColumn;
use OzoPanel\Hooks\Types\Filter\Types\NavMenu;

/**
 * WP Filter hook
 *
 * @since 0.1.0
 */
class FilterCtrl {

    public function __construct() {
        new NavMenu();
        new AdminColumn();
        new ActionLink();
        add_filter( 'body_class', [ $this, 'body_class' ] );
        add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );
    }

    public function body_class( $classes ) {
        if (
            is_page_template(
                [
					'test-template.php',
                ]
            )
        ) {
            $classes[] = 'ozopanel';
            $classes[] = get_option( 'template' ) . '-theme';
        }
        return $classes;
    }

    public function admin_body_class( $classes ) {
        if (
            // It getting from admin menu page URL, no need to check NonceVerification
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ozopanel' ) || ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ozopanel-welcome' )
        ) {
            $classes .= ' ozopanel ' . get_option( 'template' ) . '-theme';
        }

        return $classes;
    }
}
