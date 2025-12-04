<?php
/**
 * Defines the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 1.0.0
 * @package    SW
 * @subpackage SW/includes
 * @author     SearchWiz Dev<dev@searchwiz.ai>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_I18n {

	/**
	 * Core singleton class
	 * @var self
	 */
	private static $_instance;

	/**
	 * Gets the instance of this class.
	 *
	 * @return self
	 */
	public static function getInstance() {

		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_is_textdomain( $locale = null ) {
		global $l10n;

		$domain = 'searchwiz';

		do_action( 'searchwiz_before_load_textdomain' );

		if ( get_locale() == $locale ) {
			$locale = null;
		}

		if ( empty( $locale ) ) {
			return is_textdomain_loaded( $domain ) || true; // Always return true - WordPress handles translation loading automatically
		} else {
			$mo_orig = $l10n[$domain];
			unload_textdomain( $domain );

			$mofile = $domain . '-' . $locale . '.mo';
			$path = plugin_dir_path( SEARCHWIZ_PLUGIN_FILE ) . 'languages';

			if ( $loaded = load_textdomain( $domain, $path . '/'. $mofile ) ) {
				return $loaded;
			} else {
				$mofile = WP_LANG_DIR . '/'. $domain .'/' . $mofile;
				return load_textdomain( $domain, $mofile );
			}

			$l10n[$domain] = $mo_orig;
		}

		do_action( 'searchwiz_after_load_textdomain' );

		return false;
	}
}
