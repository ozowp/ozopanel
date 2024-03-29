<?php

namespace OzoPanel\Helpers;

/**
 * Manage all key strings.
 *
 * @since 0.1.0
 */
class Keys {

    /**
     * OzoPanel prefix key.
     *
     * @var string
     *
     * @since 0.1.0
     */
    const PREFIX = 'ozopanel_';

    /**
     * OzoPanel installed option key.
     *
     * @var string
     *
     * @since 0.1.0
     */
    const INSTALLED_AT = self::PREFIX . 'installed_at';

    /**
     * OzoPanel version key.
     *
     * @var string
     *
     * @since 0.1.0
     */
    const VERSION = self::PREFIX . 'version';

    /**
     * Migrations ran key.
     *
     * @var string
     *
     * @since 0.1.0
     */
    const MIGRATION_RAN_AT = self::PREFIX . 'migration_ran_at';

    /**
     * Seeder ran key.
     *
     * @var string
     *
     * @since 0.1.0
     */
    const SEEDER_RAN_AT = self::PREFIX . 'seeder_ran_at';
}
