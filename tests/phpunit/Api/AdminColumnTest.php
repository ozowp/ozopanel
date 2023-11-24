<?php

namespace OzoPanel\Tests\Api;

/**
 * API AdminColumn Test.
 *
 * @since 1.0.0
 */
class AdminColumnTest extends \WP_UnitTestCase {

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
    protected $base = 'admin-columns';

    /**
     * Setup test environment.
     */
    protected function setUp() : void {
        // Initialize REST Server.
        global $wp_rest_server;

        parent::setUp();

		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
    }

    /**
     * admin column request api exist
     *
     * @since 1.0.0
     */
    public function test_admin_columns_endpoint_exists() {
        $endpoint = '/' . $this->namespace . '/' . $this->base . '/posts';

        $request  = new \WP_REST_Request( 'GET', $endpoint );

        $response = $this->server->dispatch( $request );

        $this->assertEquals( 200, $response->get_status() );
	}

}
