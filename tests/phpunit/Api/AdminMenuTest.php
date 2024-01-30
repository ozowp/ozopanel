<?php

namespace OzoPanel\Tests\Api;

use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * API AdminMenu Test.
 *
 * @since 0.1.0
 */
class AdminMenuTest extends WP_UnitTestCase {

    /**
	 * Test REST Server
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = 'ozopanel/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $base = 'admin-menus';

    /**
     * Setup test environment.
     */
    protected function setUp() : void {
        // Initialize REST Server.
        global $wp_rest_server;

        parent::setUp();

		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );
    }

    /**
     * admin menu request api exist
     *
     * @since 0.1.0
     */
    public function test_admin_menus_endpoint_exists() {
        $endpoint = '/' . $this->namespace . '/' . $this->base;

        $request  = new WP_REST_Request( 'GET', $endpoint );

        $response = $this->server->dispatch( $request );

        $this->assertEquals( 200, $response->get_status() );
	}

}
