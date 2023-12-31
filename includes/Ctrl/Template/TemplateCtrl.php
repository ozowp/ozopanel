<?php
namespace OzoPanel\Ctrl\Template;

class TemplateCtrl {

	public function __construct() {
		add_filter( 'theme_page_templates', array( $this, 'template_list' ), 10, 4 );
		add_filter( 'template_include', array( $this, 'template_path' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'gate_scripts' ), 9999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'gate_scripts' ), 9999 );
	}

	/**
	 * Add "Custom" template to page attirbute template section.
	 */
	function template_list( $post_templates, $wp_theme, $post, $post_type ) {
		if ( function_exists( 'ozopanelp' ) ) {
			$post_templates['ozopanel-template.php'] = esc_html__( 'OzoPanel', 'ozopanel' );
		}
		return $post_templates;
	}

	/**
	 * Check if current page has our custom template. Try to load
	 * template from theme directory and if not exist load it
	 * from root plugin directory.
	 */
	function template_path( $default ) {
		$templates = array(
			'test',
		);

		foreach ( $templates as $template ) {
			if ( get_page_template_slug() === $template . '-template.php' ) {
				$custom_template = ozopanel()->plugin_path() . '/view/template/' . $template . '-template.php';
				if ( file_exists( $custom_template ) ) {
					return $custom_template;
					break;
				}
			}
		}

		return $default;
	}

	/**
	 *  OzoPanel Project Entity Star Icon
	 *
	 * @package OzoPanel Project
	 * @since 1.0
	 */
	function gate_scripts() {
		wp_localize_script( 'ozopanel-dashboard', 'gate', apply_filters( 'ozopanel_gate', array( 'PT97' ) ) );
		wp_localize_script( 'ozopanel-dashboard', 'has_gate', array( 'ins' => function_exists( 'ozopanelp' ) ) );
	}
}
