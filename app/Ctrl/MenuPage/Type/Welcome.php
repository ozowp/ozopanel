<?php
namespace WAM\Ctrl\MenuPage\Type;

class Welcome
{

	public function __construct()
	{
		add_action('admin_menu', [$this, 'add_menu'], 30);
		add_action('admin_head', function() {
			echo "<style>
				li.toplevel_page_ndpv-welcome {
					display: none;
				}
			</style>";
		});
	}

	public function add_menu()
	{
		add_menu_page(
			esc_html__('Propovoice Welcome', 'propovoice'),
			esc_html__('Propovoice Welcome', 'propovoice'),
			'manage_options',
			'ndpv-welcome',
			array($this, 'render')
		);
	}

	function render()
	{
		echo '<div class="wrap" id="ndpv-welcome"></div>';
	}
}
