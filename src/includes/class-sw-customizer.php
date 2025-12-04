<?php
/**
 * Customizer
 *
 * @package SW
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SearchWiz_Customizer' ) ) :

	/**
	 * Customizer Panel
	 *
	 * @since 1.0.0
	 */
	class SearchWiz_Customizer {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Panels
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private $panels = array();

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action('customize_register', array( $this, 'customize_register' ) );
		}

            /**
	     * Add postMessage support for site title and description for the Theme Customizer.
	     *
	     * @param object $wp_customize Theme Customizer object.
	     */
	    function customize_register( $wp_customize ) 
	    {
	    	include_once SEARCHWIZ_PLUGIN_DIR . '/includes/customizer/controls/radio-image/class-sw-control-radio-image.php';

	    	// Added custom customizer controls.
	        if ( method_exists( $wp_customize, 'register_control_type' ) ) {
	            $wp_customize->register_control_type( 'SearchWiz_Control_Radio_Image' );
	        }
	    }
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	SearchWiz_Customizer::get_instance();

endif;
