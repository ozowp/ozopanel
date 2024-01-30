<?php

namespace OzoPanel\Ctrl\Hook\Type\Filter\Type;

use OzoPanel\Model\AdminColumn as ModelAdminColumn;

/**
 * Filte Hook Type: AdminColumn
 *
 * @since 0.1.0
 */
class AdminColumn {

    public function __construct() {
		$this->manage_columns();
    }

	/**
	 * Modify default wp column
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function manage_columns() {
		foreach ( ModelAdminColumn::screens() as $group ) {
			foreach ( $group['options'] as $option ) {
				$screen = $option['value'];

				if ( $group['group'] === 'post_type' ) {
					add_filter(
                        'manage_' . $screen . '_posts_columns', function ( $columns ) use ( $screen ) {
							return $this->add_headings( $columns, $screen );
						}, 999
                    );
				} else {
					$screen_id = $group['screen_id'];
					add_filter(
                        'manage_' . $screen_id . '_columns', function ( $columns ) use ( $screen ) {
							return $this->add_headings( $columns, $screen );
						}, 999
                    );
				}
			}
		}
	}

    /**
	 * Add heading
	 * 
	 * @since 0.1.0
	 * 
	 * @param $columns = default column
	 * @param $screen = screen id
	 *
	 * @return array
	 */
	public function add_headings( $columns, $screen ) {
		if ( empty( $columns ) ) {
			return $columns;
		}

		$get_columns = get_option( 'ozopanel_admin_column_' . $screen, [] );
		if ( $get_columns ) {
			$custom_column = [];
			if ( isset( $columns['cb'] ) ) {
				$custom_column['cb'] = $columns['cb'];
			}

			foreach ( $get_columns as $value ) {
				$custom_column[ $value['id'] ] = $value['label'];
			}

			return apply_filters( 'ozopanel_headings', $custom_column, 'post' );
		}
		return $columns;
	}
}
