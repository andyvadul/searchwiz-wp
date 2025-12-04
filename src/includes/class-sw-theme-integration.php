<?php
/**
 * Theme Integration Bootstrap
 *
 * Loads the new theme integration architecture
 *
 * @package SearchWiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load abstract base class
require_once SEARCHWIZ_PLUGIN_DIR . 'includes/abstracts/class-sw-theme-base.php';

// Load theme classes
require_once SEARCHWIZ_PLUGIN_DIR . 'includes/themes/class-sw-theme-generic.php';
require_once SEARCHWIZ_PLUGIN_DIR . 'includes/themes/class-sw-theme-astra.php';

// Load registry
require_once SEARCHWIZ_PLUGIN_DIR . 'includes/themes/class-sw-theme-registry.php';

/**
 * Compatibility class for settings page
 * Wraps the new registry-based architecture
 */
class SearchWiz_Theme_Integration {
	private $registry;

	public function __construct() {
		$this->registry = SearchWiz_Theme_Registry::get_instance();
	}

	public function get_theme_info() {
		$theme = wp_get_theme();
		return array(
			'name'      => $theme->get( 'Name' ),
			'slug'      => $theme->get_stylesheet(),
			'version'   => $theme->get( 'Version' ),
			'parent'    => $theme->parent() ? $theme->parent()->get( 'Name' ) : '',
		);
	}

	public function is_theme_supported() {
		return $this->registry->is_theme_supported();
	}

	public function get_current_handler() {
		return $this->registry->get_current_handler();
	}
}

// Initialize theme integration using singleton
SearchWiz_Theme_Registry::get_instance();
