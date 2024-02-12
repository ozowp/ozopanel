<?php

namespace OzoPanel\Ajax;

use OzoPanel\Ajax\Types\{
    Feedback
};

/**
 * Class Ajax Manager
 *
 * Manager for registering ajax request.
 *
 * @since 0.1.0
 */
class Manager {

    /**
     * Ajax Manager constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        new Feedback();
    }
}
