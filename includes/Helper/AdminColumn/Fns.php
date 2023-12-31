<?php

namespace OzoPanel\Helper\AdminColumn;

/**
 * WP Default table by sceen
 *
 * @since 1.0.0
 */
class Fns {

	/**
	 * @param array $data
	 *
	 * @since 1.0.0
	 */
	public static function format_column( $data ) {
        $admin_columns = array();
		foreach ( $data as $key => $value ) {
            if ( $key === 'cb' ) {
				continue;
            }
            $column = array(
                'id' => '',
                'type' => '',
                'label' => '',
                'width' => '',
                'width_unit' => '%',
            );
            $column['id'] = $key;
            $column['type'] = 'default';
            $column['label'] = $value;

            $admin_columns[] = $column;
        }
        return $admin_columns;
	}
}
