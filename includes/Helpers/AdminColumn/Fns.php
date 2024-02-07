<?php

namespace OzoPanel\Helpers\AdminColumn;

/**
 * WP Default table by sceen
 *
 * @since 0.1.0
 */
class Fns {

	/**
	 * Format Column
     *
	 * @since 0.1.0
     *
	 * @param array $data
	 */
	public static function format_column( $data ) {
        $admin_columns = [];
		foreach ( $data as $key => $value ) {
            if ( $key === 'cb' ) {
				continue;
            }
            $column = [
                'id' => '',
                'type' => '',
                'label' => '',
                'width' => '',
                'width_unit' => '%',
            ];
            $column['id'] = $key;
            $column['type'] = 'default';
            $column['label'] = $value;

            $admin_columns[] = $column;
        }
        return $admin_columns;
	}
}
