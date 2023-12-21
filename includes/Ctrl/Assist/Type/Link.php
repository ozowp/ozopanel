<?php
namespace OzoPanel\Ctrl\Assist\Type;

class Link {

    public function __construct() {
        add_filter('plugin_action_links_' . plugin_basename(OZOPANEL_FILE), [$this, 'links']);
    }

    /**
	 * Assist links.
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function links( $links ) {
		$links[] = '<a target="_blank" href="' . esc_url( 'https://ozopanel.com/docs' ) . '">' . esc_html__('Documentation', 'ozopanel'). '</a>';

		if ( array_key_exists( 'deactivate', $links ) ) {
            $links['deactivate'] = str_replace( '<a', '<a class="ozopanel-deactivate-link"', $links['deactivate'] );
        }
		return $links;
	}
}
