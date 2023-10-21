<?php
namespace WAM\Ctrl\MenuPage\Type;

class Welcome
{

	public function __construct()
	{
		add_action('admin_menu', [$this, 'add_menu'], 30);
		add_action('admin_head', function() {
			echo "<style>
				li.toplevel_page_wam-welcome {
					display: none;
				}
			</style>";
		});
	}

	public function add_menu()
	{
		add_menu_page(
			esc_html__('Propovoice Welcome', 'wp-access-manager'),
			esc_html__('Propovoice Welcome', 'wp-access-manager'),
			'manage_options',
			'wam-welcome',
			array($this, 'render')
		);
	}

	function render()
	{
		echo '<div class="wrap" id="wam-welcome"></div>';
	}
}
