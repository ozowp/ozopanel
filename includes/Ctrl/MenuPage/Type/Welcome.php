<?php
namespace OzoPanel\Ctrl\MenuPage\Type;

class Welcome {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ), 30 );
		add_action(
            'admin_head', function () {
				echo '<style>
				li.toplevel_page_ozopanel-welcome {
					display: none;
				}
			</style>';
			}
        );
	}

	public function add_menu() {
		add_menu_page(
			esc_html__( 'OzoPanel Welcome', 'ozopanel' ),
			esc_html__( 'OzoPanel Welcome', 'ozopanel' ),
			'manage_options',
			'ozopanel-welcome',
			array( $this, 'render' )
		);
	}

	public function render() {
		echo '<div class="wrap" id="ozopanel-welcome"></div>';
	}
}
