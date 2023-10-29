<?php
namespace OzoPanel\Model;
/**
 * AdminColumn model
 *
 * @since 1.0.0
 */
class AdminColumn {

    /**
	 * Get a list of post types for which Admin Columns is active
	 * @return array List of post type keys (e.g. post, page)
	 * @since 1.0.0
	 */
	public static function get_post_types() {
		$post_types = get_post_types( [
			'_builtin' => false,
			'show_ui'  => true,
		] );

		foreach ( [ 'post', 'page', 'wp_block' ] as $builtin ) {
			if ( post_type_exists( $builtin ) ) {
				$post_types[ $builtin ] = $builtin;
			}
		}

		/**
		 * Filter the post types for which Admin Columns is active
		 *
		 * @param array $post_types List of active post type names
		 *
		 * @since 2.0
		 */
		return apply_filters( 'ozopanel/post_types', $post_types );
	}
}