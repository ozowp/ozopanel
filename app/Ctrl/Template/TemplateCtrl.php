<?php
namespace WAM\Ctrl\Template;

class TemplateCtrl
{
	public function __construct()
	{
		add_filter('theme_page_templates', [$this, 'template_list'], 10, 4);
		add_filter('template_include', [$this, 'template_path']);
		add_action('wp_enqueue_scripts', array($this, 'wage_scripts'), 9999);
		add_action('admin_enqueue_scripts', array($this, 'wage_scripts'), 9999);
	}

	/**
	 * Add "Custom" template to page attirbute template section.
	 */
	function template_list($post_templates, $wp_theme, $post, $post_type)
	{
		if ( function_exists('wamp') ) {
			$post_templates['test-template.php'] = esc_html__('WP Access Manager Test', 'wp-access-manager');
		}
		return $post_templates;
	}

	/**
	 * Check if current page has our custom template. Try to load
	 * template from theme directory and if not exist load it
	 * from root plugin directory.
	 */
	function template_path($default)
	{
		$templates = [
			'test'
		];

		foreach ( $templates as $template ) {
			if ( get_page_template_slug() === $template .'-template.php' ) {
				$custom_template = wam()->plugin_path() . '/view/template/' . $template . '-template.php';
				if ( file_exists($custom_template) ) {
					return $custom_template;
					break;
				}
			}
		}

		return $default;
	}

	/**
	 *  WAM Project Entity Star Icon
	 *
	 * @package WAM Project
	 * @since 1.0
	 */
	function wage_scripts()
	{
		wp_localize_script('wam-dashboard', 'wage', apply_filters('wam_wage', ['PT97'])); 
		wp_localize_script('wam-dashboard', 'has_wage', ['ins' => function_exists('wamp')]);
	}
}
