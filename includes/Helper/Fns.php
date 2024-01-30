<?php
namespace OzoPanel\Helper;

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

    /**
     * Sanitize input data
     *
     * @since 0.1.0
     */
    public function sanitizeInput( $value, $type = 'text', $array_map = 'text' ) {
        $new_value = null;
        if ( isset( $value ) ) {
            if ( $type === 'text' ) {
                $new_value = sanitize_text_field( $value );
            } elseif ( $type === 'email' ) {
                $new_value = strtolower( sanitize_email( $value ) );
            } elseif ( $type === 'url' ) {
                $new_value = esc_url_raw( $value );
            } elseif ( $type === 'textarea' ) {
                // Allowing some basic HTML tags in the textarea
                $allowed_tags = [
                    'a' => [
                        'href' => [],
                        'title' => [],
                    ],
                    'br' => [],
                    'em' => [],
                    'strong' => [],
                ];
                $new_value = wp_kses( $value, $allowed_tags );
            } elseif ( $type === 'array_map' ) {
                $sanitize = '';
                switch ( $array_map ) {
                    case 'text':
                        $sanitize = 'sanitize_text_field';
                        break;

                    case 'boolean':
                        $sanitize = 'rest_sanitize_boolean';
                        break;
                }
                $new_value = array_map( $sanitize, $value );
            }
        }
        return $new_value;
    }

    /**
     * Sanitize output data
     *
     * @since 0.1.0
     */
    public function sanitizeOutput( $value, $type = 'text' ) {
        $new_value = null;
        if ( $value ) {
            if ( $type === 'text' ) {
                $new_value = esc_html( stripslashes( $value ) );
            } elseif ( $type === 'url' ) {
                $new_value = esc_url( stripslashes( $value ) );
            } elseif ( $type === 'textarea' ) {
                $new_value = esc_textarea( stripslashes( $value ) );
            } else {
                $new_value = esc_html( stripslashes( $value ) );
            }
        }
        return $new_value;
    }
}
