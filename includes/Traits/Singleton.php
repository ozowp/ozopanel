<?php
namespace OzoPanel\Traits;
/**
 * Singleton
 *
 * @since 1.0.0
 */

trait Singleton
{
    /**
     * Store the singleton object.
     * @since 1.0.0
     */
    private static $singleton = false;

    /**
     * Fetch an instance of the class.
     * @since 1.0.0
     */
    public static function init()
    {
        if (self::$singleton === false) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

}
