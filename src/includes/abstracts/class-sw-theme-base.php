<?php
/**
 * Abstract Base Class for Theme Integrations
 *
 * @package SearchWiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class SearchWiz_Theme_Base {

	protected $theme_slug = '';
	protected $theme_name = '';
	protected $args = array();

	public function __construct( $theme_slug = '', $theme_name = '', $args = array() ) {
		$this->theme_slug = $theme_slug;
		$this->theme_name = $theme_name;
		$this->args = wp_parse_args( $args, array(
			'mobile_breakpoint'       => 959,
			'force_mobile_overlay'    => false,
			'always_enabled'          => false,
		) );

		if ( $this->can_integrate() ) {
			$this->init();
			$this->integrate();
		}
	}

	protected function can_integrate() {
		if ( ! empty( $this->args['always_enabled'] ) ) {
			return true;
		}
		$option = get_option( 'searchwiz_theme_integration', array() );
		return isset( $option['enabled'] ) && 'on' === $option['enabled'];
	}

	protected function init() {
		// Override in child classes
	}

	abstract public function integrate();

	protected function render_search_box() {
		return do_shortcode( '[searchwiz allow_render="yes"]' );
	}

	public function replace_search_form( $form ) {
		return $this->render_search_box();
	}

	protected function get_js_config() {
		return array(
			'theme'      => $this->theme_slug,
			'breakpoint' => ! empty( $this->args['mobile_breakpoint'] ) ? $this->args['mobile_breakpoint'] : 959,
		);
	}
}
