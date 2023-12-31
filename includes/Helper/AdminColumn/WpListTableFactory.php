<?php

namespace OzoPanel\Helper\AdminColumn;

use WP_Comments_List_Table;
use WP_Media_List_Table;
use WP_MS_Sites_List_Table;
use WP_MS_Users_List_Table;
use WP_Posts_List_Table;
use WP_Terms_List_Table;
use WP_Users_List_Table;

/**
 * WP Default table by sceen
 *
 * @since 1.0.0
 */
class WpListTableFactory {

	/**
	 * @param string $screen_id
	 *
	 * @return WP_Posts_List_Table
	 * @since 1.0.0
	 */
	public function get_post_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';

		return new WP_Posts_List_Table( array( 'screen' => $screen_id ) );
	}

	/**
	 * @param string $screen_id
	 *
	 * @return WP_Users_List_Table
	 * @since 1.0.0
	 */
	public function get_user_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';

		return new WP_Users_List_Table( array( 'screen' => $screen_id ) );
	}

	/**
	 * @param string $screen_id
	 *
	 * @return WP_Comments_List_Table
	 * @since 1.0.0
	 */
	public function get_comment_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-comments-list-table.php';

		$table = new WP_Comments_List_Table( array( 'screen' => $screen_id ) );

		// Since 4.4 the `floated_admin_avatar` filter is added in the constructor of the `\WP_Comments_List_Table` class.
		remove_filter( 'comment_author', array( $table, 'floated_admin_avatar' ) );

		return $table;
	}

	/**
	 * @param string $screen_id
	 *
	 * @return WP_Media_List_Table
	 * @since 1.0.0
	 */
	public function get_media_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php';

		return new WP_Media_List_Table( array( 'screen' => $screen_id ) );
	}

	/**
	 * @param string $screen_id
	 *
	 * @return WP_Terms_List_Table
	 * @since 1.0.0
	 */
	public function get_taxonomy_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-terms-list-table.php';

		return new WP_Terms_List_Table( array( 'screen' => $screen_id ) );
	}

	/**
	 * @param string $screen_id
	 *
	 * @return WP_MS_Users_List_Table
	 * @since 1.0.0
	 */
	public function get_network_user_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-ms-users-list-table.php';

		return new WP_MS_Users_List_Table( array( 'screen' => $screen_id ) );
	}

	/**
	 * @param string $screen_id
	 *
	 * @return WP_MS_Sites_List_Table
	 * @since 1.0.0
	 */
	public function get_network_site_table( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-ms-sites-list-table.php';

		return new WP_MS_Sites_List_Table( array( 'screen' => $screen_id ) );
	}
}
