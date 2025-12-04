<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Inverted Index TablePress Compatibility.
 *
 * @package SW
 * @subpackage SW/includes
 * @since 1.0.0
 */
class SearchWiz_TablePress_Compat {
	public function __construct() {
		add_action( 'init', array( $this, 'compatibility' ) );
	}

	public function compatibility() {
		$index_opt = SearchWiz_Index_Options::getInstance();
		if ( class_exists( 'TablePress' ) && $index_opt->expand_shortcodes ) {
			$this->fix_order();
			$this->include_shortcodes();
		}
	}

	/**
	 * Include Table Press shortcode in the admin.
	 *
	 * @since 1.0.0
	 */
	public function include_shortcodes() {

		if ( ! isset( TablePress::$model_options ) ) {
			include_once TABLEPRESS_ABSPATH . 'classes/class-model.php';
			include_once TABLEPRESS_ABSPATH . 'models/model-options.php';
			TablePress::$model_options = new TablePress_Options_Model();
		}
		$tb_controller = TablePress::load_controller( 'frontend' );
		$tb_controller->init_shortcodes();
	}

	/**
	 * Ensure TablePress loads before SearchWiz.
	 *
	 * Note: Previously this method modified the active_plugins option which is not
	 * allowed per WordPress.org plugin guidelines. Instead, we now use the
	 * plugin_loaded action priority to ensure proper load order.
	 *
	 * @since 1.0.0
	 */
	public function fix_order() {
		// Plugin load order is now handled via action priority in the constructor.
		// We use 'init' action which runs after all plugins are loaded, ensuring
		// TablePress is available when we need it.
		//
		// The old approach of modifying 'active_plugins' option is not allowed
		// as plugins should not change the activation status of other plugins.
	}
}

new SearchWiz_TablePress_Compat();