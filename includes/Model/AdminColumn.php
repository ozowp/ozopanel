<?php
namespace OzoPanel\Model;

/**
 * AdminColumn model
 *
 * @since 1.0.0
 */
class AdminColumn {

	/**
	 * Get a list screens
	 * @return array
	 * @since 1.0.0
	 */
	public static function screens() {
		$screens = array(
			array(
				'group' => 'post_type',
				'screen_id' => 'edit',
				'label' => esc_html__( 'Post Type', 'ozopanel' ),
				'options' => array(),
			),
			array(
				'group' => 'media',
				'screen_id' => 'upload',
				'label' => esc_html__( 'Media', 'ozopanel' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Media', 'ozopanel' ),
						'value' => 'wp_media',
					),
				),
			),
			array(
				'group' => 'comment',
				'screen_id' => 'edit-comments',
				'label' => esc_html__( 'Comments', 'ozopanel' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Comments', 'ozopanel' ),
						'value' => 'wp_comments',
					),
				),
			),
		);

		if ( ! is_multisite() ) {
			$screens[] = array(
				'group' => 'user',
				'screen_id' => 'users',
				'label' => esc_html__( 'Users', 'ozopanel' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Users', 'ozopanel' ),
						'value' => 'wp_users',
					),
				),
			);
		}

		$post_types = array();
		foreach ( self::get_post_types() as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );

			if ( $post_type_object ) {
				$label = $post_type_object->labels->name;
				$post_types[] = array(
					'label' => $label,
					'value' => $post_type,
				);
			}
		}
		$screens[0]['options'] = $post_types;

		return apply_filters( 'ozopanel_admin_column_screens', $screens );
	}

    /**
	 * Get a list of post types for which Admin Columns is active
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_post_types() {

		$post_types = array();
		foreach ( array( 'post', 'page', 'wp_block' ) as $builtin ) {
			if ( post_type_exists( $builtin ) ) {
				$post_types[ $builtin ] = $builtin;
			}
		}

		$custom_post_types = get_post_types(
            array(
				'_builtin' => false,
				'show_ui'  => true, // Include post types that have UI components in the admin area
            )
        );

		foreach ( $custom_post_types as $post_type ) {
			$post_types[ $post_type ] = $post_type;
		}

		return apply_filters( 'ozopanel_post_types', $post_types );
	}
}
