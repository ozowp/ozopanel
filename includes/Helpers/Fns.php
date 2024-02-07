<?php
namespace OzoPanel\Helpers;

/**
 * Helper functions
 *
 * @since 0.1.0
 */
class Fns {

    /**
     * Get option value
     *
     * @since 0.1.0
     */
    public static function option_value( $func_get_args ) {
        $option_field = $func_get_args[0];
        $data = get_option( $option_field );
        $func_args = $func_get_args;
        array_shift( $func_args );

        return self::access_nested_array( $data, $func_args );
    }

    /**
     * Access Nested Array
     *
     * By this function can access nested array like: get_preset('key', 'associate_key');
     *
     * @since 0.1.0
     */
    public static function access_nested_array( $data, $func_args ) {
        foreach ( $func_args as $arg ) {
            if ( is_array( $arg ) ) {
                if ( ! empty( $data[ $arg[0] ] ) ) {
                    $data = $data[ $arg[0] ];
                } else {
                    $data = $arg[1];
                }
            } elseif ( ! empty( $data[ $arg ] ) ) {
                    $data = $data[ $arg ];
			} else {
				$data = null;
            }
        }
        return $data;
    }
}
