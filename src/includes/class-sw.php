<?php
/**
 * The class is the core plugin responsible for including and
 * instantiating all of the code that composes the plugin.
 *
 * The class includes an instance to the plugin
 * Loader which is responsible for coordinating the hooks that exist within the
 * plugin.
 *
 * @since 1.0.0
 * @package SW
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Loader {

	/**
	 * Core singleton class
	 * @var self
	 */
	private static $_instance;

	/**
	 * Instantiates the class.
	 *
	 * The constructor uses internal functions to import all the
	 * plugin dependencies, and will leverage the SearchWiz for
	 * registering the hooks and the callback functions used throughout the plugin.
	 */
	public function __construct() {
	}

	/**
	 * Gets instance of this class.
	 *
	 * @return self
	 */
	public static function getInstance( $is_opt = null ) {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self( $is_opt );
		}

		return self::$_instance;
	}

	/**
	 * Loads plugin functionality.
	 */
	function load() {
		if ( ! searchwiz_search_is_json_request() ) {
			$this->set_locale();

			$this->admin_public_hooks();

			// Check if this is an AJAX request for any of our search endpoints
			$post_action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			$is_search_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX && $post_action &&
				in_array( $post_action, array( 'searchwiz_ajax_load_posts', 'searchwiz_search_json', 'searchwiz_test', 'searchwiz_inline_suggestion', 'searchwiz_test_suggest', 'searchwiz_debug_test' ) );

			if ( is_admin() || $is_search_ajax ) {
				$this->admin_hooks();
			}
			if ( ! is_admin() || $is_search_ajax ) {
				$this->public_hooks();
			}
		}
		//avoid save events from meta boxes when guten blocks saves
		elseif ( empty( $_GET['meta-box-loader'] ) ) {
			//Indexing events hooks (save post for guten block save).
			$index_mgr = SearchWiz_Index_Manager::getInstance();
			$index_mgr->init_index_hooks();
		}
	}

	/**
	 * Defines the locale for this plugin for internationalization.
	 *
	 * Uses the SearchWiz_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$is_i18n = SearchWiz_I18n::getInstance();
		add_action( 'init', array( $is_i18n, 'load_is_textdomain' ) );
	}

	/**
	 * Defines the hooks and callback functions which are executed both in admin and front end areas.
	 *
	 * @access    private
	 */
	private function admin_public_hooks() {
		$admin_public = SearchWiz_Admin_Public::getInstance();
		add_action( 'init', array( $admin_public, 'init' ) );
		add_action( 'before_woocommerce_init', array( $admin_public, 'declare_wc_features_support' ) );
		add_filter( 'get_search_form', array( $admin_public, 'get_search_form' ), 9999999 );
		add_action( 'customize_register', array( $admin_public, 'customize_register' ) );
		add_filter( 'upload_mimes', array( $admin_public, 'add_custom_mime_types' ) );
	}

	/**
	 * Defines the hooks and callback functions that are used for setting up the plugin's admin options.
	 *
	 * @access    private
	 */
	private function admin_hooks() {
		$admin = SearchWiz_Admin::getInstance();

		add_action( 'all_admin_notices', array( $admin, 'all_admin_notices' ) );
		add_action( 'admin_footer', array( $admin, 'admin_footer' ), 100 );
		add_action( 'plugin_action_links', array( $admin, 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $admin, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_menu', array( $admin, 'admin_menu' ) );
		add_action( 'wp_ajax_nopriv_searchwiz_display_posts', array( $admin, 'display_posts' ) );
		add_action( 'wp_ajax_searchwiz_display_posts', array( $admin, 'display_posts' ) );
		add_action( 'wp_ajax_searchwiz_save_theme_integration', array( $admin, 'ajax_save_theme_integration' ) );
		add_action( 'wp_ajax_searchwiz_save_default_search', array( $admin, 'ajax_save_default_search' ) );
		add_action( 'wp_ajax_searchwiz_autosave_settings', array( $admin, 'ajax_autosave_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $admin, 'admin_init' ) );
		add_action( 'admin_init', array( 'SearchWiz_Activator', 'migrate_post_type' ) );
		add_action( 'is_admin_notices', array( $admin, 'admin_updated_message' ) );
		add_filter( 'map_meta_cap', array( $admin, 'map_meta_cap' ), 10, 4 );
		add_filter( 'admin_footer_text', array( $admin, 'admin_footer_text' ), 1 );

		//Build index hooks.
		$index_mgr = SearchWiz_Index_Manager::getInstance();
		$index_mgr->init_build_hooks();
		//Indexing events hooks (save post, post type, comments).
		$index_mgr->init_index_hooks();
	}

	/**
	 * Defines the hooks and callback functions that are used for executing plugin functionality
	 * in the front end of site.
	 *
	 * @access    private
	 */
	private function public_hooks() {

		$public = SearchWiz_Public::getInstance();

		add_action( 'wp_enqueue_scripts', array( $public, 'wp_enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $public, 'wp_enqueue_scripts' ), 9999999 );
		add_filter( 'query_vars', array( $public, 'query_vars' ) );
		add_filter( 'body_class', array( $public, 'is_body_classes' ) );

		$header_menu_search = isset( $public->opt['header_menu_search'] ) ? $public->opt['header_menu_search'] : 0;
		$site_cache = isset( $public->opt['site_uses_cache'] ) ? $public->opt['site_uses_cache'] : 0;
		$display_in_mobile_menu = false;
		if ( function_exists( 'wp_is_mobile' ) ) {
			$display_in_mobile_menu = $header_menu_search && wp_is_mobile() ? true : false;
		}

		if ( $display_in_mobile_menu || $site_cache ) {
			add_action( 'wp_head', array( $public, 'header_menu_search' ), 9999999 );
		}

		if ( ! $display_in_mobile_menu || $site_cache ) {
			add_filter( 'wp_nav_menu_items', array( $public, 'wp_nav_menu_items' ), 9999999, 2 );
		}

		add_action( 'init', function () {
			$public = SearchWiz_Public::getInstance();
			add_filter( 'posts_distinct_request', array( $public, 'posts_distinct_request' ), 9999999, 2 );
			add_filter( 'posts_join' , array( $public, 'posts_join' ), 9999999, 2 );
			add_filter( 'posts_search', array( $public, 'posts_search' ), 9999999, 2 );
			// Changed pre_get_posts priority from 9 to 9999999 
			// as product search restricted to category was not working in free plugin.
			add_action( 'pre_get_posts', array( $public, 'pre_get_posts' ), 9999999 );
			add_action( 'wp_footer', array( $public, 'wp_footer' ) );
			add_action( 'wp_head', array( $public, 'wp_head' ), 9999999 );
			add_action( 'parse_query', array( $public, 'parse_query' ), 9999999, 2 );
		}, 9999999 );

		$ajax = SearchWiz_Ajax::getInstance();

		// Main AJAX search endpoint
		add_action( 'wp_ajax_searchwiz_ajax_load_posts', array( $ajax, 'ajax_load_posts_json' ) );
		add_action( 'wp_ajax_nopriv_searchwiz_ajax_load_posts', array( $ajax, 'ajax_load_posts_json' ) );

		// Test endpoint (no nonce)
		add_action( 'wp_ajax_searchwiz_test', array( $ajax, 'ajax_test' ) );
		add_action( 'wp_ajax_nopriv_searchwiz_test', array( $ajax, 'ajax_test' ) );

		// JSON endpoint for React search
		add_action( 'wp_ajax_searchwiz_search_json', array( $ajax, 'ajax_load_posts_json' ) );
		add_action( 'wp_ajax_nopriv_searchwiz_search_json', array( $ajax, 'ajax_load_posts_json' ) );

		// Inline autocomplete suggestion endpoint
		add_action( 'wp_ajax_searchwiz_inline_suggestion', array( $ajax, 'ajax_get_inline_suggestion' ) );
		add_action( 'wp_ajax_nopriv_searchwiz_inline_suggestion', array( $ajax, 'ajax_get_inline_suggestion' ) );

		// Register custom WP-Cron intervals for suggestion rebuilds
		add_filter( 'cron_schedules', function( $schedules ) {
			$schedules['weekly'] = array(
				'interval' => 604800, // 7 days in seconds
				'display'  => __( 'Once Weekly', 'searchwiz' ),
			);
			$schedules['monthly'] = array(
				'interval' => 2635200, // 30.5 days in seconds
				'display'  => __( 'Once Monthly', 'searchwiz' ),
			);
			return $schedules;
		} );

		// Suggestion builder hooks
		add_action( 'searchwiz_rebuild_suggestions', array( 'SearchWiz_Suggestion_Builder', 'build_from_content' ) );
		SearchWiz_Suggestion_Builder::init_hooks(); // Auto-rebuild on content changes

		$search_form = SearchWiz_Search_Form::load_from_request();
		if ( ! empty($search_form) && $search_form->is_index_search() ) {
			//Indexing events hooks (save post, post type, comments).
			$index_mgr = SearchWiz_Index_Manager::getInstance();
			$index_mgr->init_index_hooks();
			
			$index_search = SearchWiz_Index_Search::getInstance();
			$index_search->init_hooks();
		}
	}
}