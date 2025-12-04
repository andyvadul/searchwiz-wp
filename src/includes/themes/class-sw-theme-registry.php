<?php
/**
 * Theme Registry and Detection
 *
 * @package SearchWiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Theme_Registry {

	private static $instance = null;
	private $current_theme_slug = '';
	private $current_theme_name = '';
	private $current_handler = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->detect_theme();
		$this->load_integration();
	}

	private function detect_theme() {
		$theme = wp_get_theme();

		if ( is_object( $theme ) && is_a( $theme, 'WP_Theme' ) ) {
			$template = $theme->get_template();
			$this->current_theme_slug = strtolower( $template );
			$this->current_theme_name = $theme->name;
		}
	}

	private function supported_themes() {
		return array(
			// Dedicated integration
			'astra' => array(
				'name'  => 'Astra',
				'class' => 'SearchWiz_Theme_Astra',
				'args'  => array(
					'mobile_breakpoint'    => 921,
					'force_mobile_overlay' => true,
				),
			),

			// Dedicated integrations
			'storefront' => array(
				'name'  => 'Storefront',
				'class' => 'SearchWiz_Theme_Storefront',
				'args'  => array(
					'mobile_breakpoint' => 959,
				),
			),

			// Generic integrations (following FiboSearch approach)
			'flatsome' => array(
				'name'  => 'Flatsome',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 959,
				),
			),
			'oceanwp' => array(
				'name'  => 'OceanWP',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 959,
				),
			),
			'woodmart' => array(
				'name'  => 'WoodMart',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1024,
				),
			),
			'avada' => array(
				'name'  => 'Avada',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 800,
				),
			),
			'divi' => array(
				'name'  => 'Divi',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 980,
				),
			),
			'enfold' => array(
				'name'  => 'Enfold',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 989,
				),
			),
			'bridge' => array(
				'name'  => 'Bridge',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1000,
				),
			),
			'thegem' => array(
				'name'  => 'TheGem',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1212,
				),
			),
			'the7' => array(
				'name'  => 'The7',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1050,
				),
			),
			'salient' => array(
				'name'  => 'Salient',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1000,
				),
			),
			'uncode' => array(
				'name'  => 'Uncode',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 959,
				),
			),
			'impreza' => array(
				'name'  => 'Impreza',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 900,
				),
			),
			'xstore' => array(
				'name'  => 'XStore',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 992,
				),
			),
			'electro' => array(
				'name'  => 'Electro',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 768,
				),
			),
			'savoy' => array(
				'name'  => 'Savoy',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 991,
				),
			),
			'shopkeeper' => array(
				'name'  => 'Shopkeeper',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 800,
				),
			),
			'konte' => array(
				'name'  => 'Konte',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1024,
				),
			),
			'kadence' => array(
				'name'  => 'Kadence',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 1024,
				),
			),
			'generatepress' => array(
				'name'  => 'GeneratePress',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 768,
				),
			),
			'neve' => array(
				'name'  => 'Neve',
				'class' => 'SearchWiz_Theme_Generic',
				'args'  => array(
					'mobile_breakpoint' => 960,
				),
			),
		);
	}

	private function load_integration() {
		// Don't load in admin
		if ( is_admin() ) {
			return;
		}

		// Check if theme integration is enabled via checkbox
		$theme_integration_option = get_option( 'searchwiz_theme_integration', array( 'enabled' => 'on' ) );
		$theme_integration_enabled = ! isset( $theme_integration_option['enabled'] ) || 'on' === $theme_integration_option['enabled'];

		if ( ! $theme_integration_enabled ) {
			// Theme integration disabled via checkbox - don't load any handlers
			return;
		}

		$themes = $this->supported_themes();

		// Check if current theme is supported
		if ( ! isset( $themes[ $this->current_theme_slug ] ) ) {
			// Use generic integration for unsupported themes
			$this->current_handler = new SearchWiz_Theme_Generic( $this->current_theme_slug, $this->current_theme_name, array() );
			return;
		}

		$theme_config = $themes[ $this->current_theme_slug ];
		$class_name = $theme_config['class'];

		// Instantiate theme integration class
		if ( class_exists( $class_name ) ) {
			$this->current_handler = new $class_name(
				$this->current_theme_slug,
				$theme_config['name'],
				isset( $theme_config['args'] ) ? $theme_config['args'] : array()
			);
		}
	}

	public function get_current_theme() {
		return array(
			'slug' => $this->current_theme_slug,
			'name' => $this->current_theme_name,
		);
	}

	public function is_theme_supported() {
		$themes = $this->supported_themes();
		return isset( $themes[ $this->current_theme_slug ] );
	}

	public function get_current_handler() {
		return $this->current_handler;
	}
}
