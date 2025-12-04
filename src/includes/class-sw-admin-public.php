<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class defines all plugin functionality for the dashboard of the plugin and site front end.
 *
 * @package SW
 * @since 1.0.0
 */

class SearchWiz_Admin_Public {

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
	 * Added MIME support
	 *
	 * @since 1.0.0
	 * @param array $mimes Mime types.
	 */
    function add_custom_mime_types( $mimes ){
        return array_merge( $mimes, array (
            'gif' => 'image/gif',
        ));
    }

    /**
     * Customizer settings
     * 
     * Register customizer settings for each search form.
     *
     * @since 1.0.0
     * @param  object $wp_customize Customizer Object.
     * @return void
     */
	function customize_register( $wp_customize ) {

		$query_args = apply_filters( 'searchwiz_customize_register_args', array(
			'post_type'  => 'is_search_form',

			// Query performance optimization.
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'posts_per_page' => -1,
            'orderby'	 => 'Date',
            'order'		 => 'DESC',
		));

		$meta_query = new WP_Query( $query_args );

		if( $meta_query->posts ) {
			foreach ( $meta_query->posts as $key => $post_id ) {
				$option_name = 'is_search_' . $post_id;
                                
				// Section 
				$sections[ 'is_section_' . $post_id ] = array(
					'title'   => get_the_title( $post_id ),
					'options' => $this->settings( $post_id, $option_name ),
				);
			}
		}

		/* General Panel */
		SearchWiz_Customizer_Panel::get_instance()->add_panel(
			'is_search_form_panel', array(
				'title'    => __( 'SearchWiz', 'searchwiz' ),
				'sections' => $sections,
			)
		);

		// Register all panels.
		SearchWiz_Customizer_Panel::get_instance()->register_panels( $wp_customize );

	}

	/**
	 * Customizer Settings
	 *
	 * @since 1.0.0
	 * 
	 * @param  int $post_id      Post ID.
	 * @param  string $setting_name Setting name.
	 * @return array               Customizer settings.
	 */
	function settings( $post_id, $setting_name ) {

		$fields = array();

		$search_form = SearchWiz_Search_Form::get_instance( $post_id );

		if( $search_form ) {

			$_ajax = $search_form->prop( '_is_ajax' );
			if ( isset( $_ajax['enable_ajax'] ) ) {
				$fields[ $setting_name . '[loader-image]' ] = array(
					'setting' => array(
						'type'    => 'option',
						'default' => SEARCHWIZ_PLUGIN_URI . 'public/images/spinner.gif',
					),
					'control' => array(
						'class'      => 'WP_Customize_Image_Control',
						'label'      => __( 'Loader Image', 'searchwiz' ),
						'type'       => 'image',
						'capability' => 'edit_theme_options',
                        'description'=> __( 'AJAX loader image.', 'searchwiz' ),
					)
				);
			}

			// Customize options.
			$_customize = $search_form->prop('_is_customize');
			if( isset( $_customize['enable_customize'] ) || isset( $_ajax['enable_ajax'] ) || 'default-search-form' != $search_form->name() ) {
		
				$fields[ $setting_name . '[form-style]' ] = array(
					'setting' => array(
						'type'    => 'option',
						'default' => 'is-form-style-3',
					),
					'control' => array(
						'class'      => 'SearchWiz_Control_Radio_Image',
						'type'       => 'is-radio-image',
						'label'      => __( 'Search Form Style', 'searchwiz' ),
                                                'description'=> __( 'Search form submit button field style.', 'searchwiz' ),
						'capability' => 'edit_theme_options',
						'choices'  => array(
							'is-form-style-1' => array(
								'label' => __( 'Style 1', 'searchwiz' ),
								'path'  => SEARCHWIZ_PLUGIN_URI . 'includes/customizer/controls/radio-image/images/style-1.png',
							),
							'is-form-style-2' => array(
								'label' => __( 'Style 2', 'searchwiz' ),
								'path'  => SEARCHWIZ_PLUGIN_URI . 'includes/customizer/controls/radio-image/images/style-2.png',
							),
							'is-form-style-3' => array(
								'label' => __( 'Style 3', 'searchwiz' ),
								'path'  => SEARCHWIZ_PLUGIN_URI . 'includes/customizer/controls/radio-image/images/style-3.png',
							)
						),
					)
				);

				$fields[ $setting_name . '[placeholder-text]' ] = array(
					'setting' => array(
						'type'              => 'option',
						'default'           => __( 'Search here...', 'searchwiz' ),
					),
					'control' => array(
						'class'      => 'WP_Customize_Control',
						'label'      => __( 'Text Box Placeholder', 'searchwiz' ),
						'type'       => 'text',
						'capability' => 'edit_theme_options',
					)
				);

				$fields[ $setting_name . '[search-btn-text]' ] = array(
					'setting' => array(
						'type'    => 'option',
						'default' => __( 'Search', 'searchwiz' ),
					),
					'control' => array(
						'class'      => 'WP_Customize_Control',
						'label'      => __( 'Search Button', 'searchwiz' ),
						'type'       => 'text',
						'capability' => 'edit_theme_options',
					)
				);

				$colors = array(
					// Input.
					'text-box-bg'     => '',
					'text-box-text'   => '',
					'text-box-border' => '',

					// Submit.
					'submit-button-bg' 	=> '',
					'submit-button-text'  => '',
					'submit-button-border' => '',
				);
				foreach ($colors as $color_key => $default_color) {
					$color_key_modified = $color_key;
					$color_key_modified = str_replace('-h-', ' hover ', $color_key_modified);
					$color_key_modified = str_replace('-bg', ' background ', $color_key_modified);
					$color_key_modified = str_replace('-', ' ', $color_key_modified);
					$fields[ $setting_name . '['.$color_key.']' ] = array(
						'setting' => array(
							'type'              => 'option',
							'default'           => $default_color,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'control' => array(
							'class'      => 'WP_Customize_Color_Control',
							'label'      => ucwords( $color_key_modified ),
							'type'       => 'color',
							'capability' => 'edit_theme_options',
						)
					);
				}

			}

			// AJAX customizer fields.
			if ( isset( $_ajax['enable_ajax'] ) ) {

				// Suggestion Box.
				$colors = array(
					'search-results-bg'       => '',
					'search-results-hover'    => '',
					'search-results-text'     => '',
					'search-results-link'     => '',
					'search-results-border'   => '',
				);
				foreach ($colors as $color_key => $default_color) {
					$color_key_modified = 'AJAX ' . $color_key;
					$color_key_modified = str_replace('-h-', ' hover ', $color_key_modified);
					$color_key_modified = str_replace('-bg', ' background ', $color_key_modified);
					$color_key_modified = str_replace('-', ' ', $color_key_modified);
					$fields[ $setting_name . '['.$color_key.']' ] = array(
						'setting' => array(
							'type'              => 'option',
							'default'           => $default_color,
							'sanitize_callback' => 'sanitize_hex_color',
						),
						'control' => array(
							'class'      => 'WP_Customize_Color_Control',
							'label'      => ucwords( $color_key_modified ),
							'type'       => 'color',
							'capability' => 'edit_theme_options',
						)
					);
				}

			}
		}

		return apply_filters( 'searchwiz_customize_fields', $fields );
	}


	/**
	 * Executes actions on initialization.
	 */
	function init() {
		/* Registers post types */
		if ( class_exists( 'SearchWiz_Search_Form' ) ) {
			SearchWiz_Search_Form::register_post_type();
		}

		/* Add rewrite rule for SearchWiz search endpoint */
		add_rewrite_rule( '^sw/?', 'index.php?is_searchwiz_search=1', 'top' );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

		/* Add redirect from /?s= to /sw?s= when checkbox is checked */
		add_action( 'template_redirect', array( $this, 'redirect_default_search_to_sw' ) );

		/* Prevent 404 on /sw endpoint */
		add_action( 'template_redirect', array( $this, 'prevent_searchwiz_404' ), 1 );

		/* Fix breadcrumbs for /sw endpoint */
		add_filter( 'woocommerce_get_breadcrumb', array( $this, 'fix_searchwiz_breadcrumb' ), 10, 2 );

		/* Fix page title for /sw endpoint */
		add_filter( 'wp_title', array( $this, 'fix_searchwiz_page_title' ), 10, 2 );
		add_filter( 'document_title_parts', array( $this, 'fix_searchwiz_document_title' ), 10, 1 );
	}

	/**
	 * Prevent 404 error on SearchWiz endpoint (/sw).
	 */
	function prevent_searchwiz_404() {
		global $wp_query;

		// Check if this is a SearchWiz search endpoint
		if ( get_query_var( 'is_searchwiz_search' ) ) {
			// Ensure it's treated as a search page, not a 404
			$wp_query->is_search = true;
			$wp_query->is_404 = false;
			$wp_query->is_home = false;
			$wp_query->is_archive = false;
			status_header( 200 );

			// Set the search query if provided (for breadcrumbs and page title)
			if ( isset( $_GET['s'] ) ) {
				$wp_query->set( 's', sanitize_text_field( $_GET['s'] ) );
			}
		}
	}

	/**
	 * Fix page title for SearchWiz endpoint (/sw).
	 */
	function fix_searchwiz_page_title( $title, $sep ) {
		if ( get_query_var( 'is_searchwiz_search' ) ) {
			$search_query = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			if ( $search_query ) {
				/* translators: %s: search query string */
				return sprintf( __( 'Search results for "%s"', 'searchwiz' ), $search_query ) . " $sep ";
			} else {
				return __( 'Search Results', 'searchwiz' ) . " $sep ";
			}
		}
		return $title;
	}

	/**
	 * Fix document title for SearchWiz endpoint (/sw).
	 */
	function fix_searchwiz_document_title( $title_parts ) {
		if ( get_query_var( 'is_searchwiz_search' ) ) {
			$search_query = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			if ( $search_query ) {
				/* translators: %s: search query string */
				$title_parts['title'] = sprintf( __( 'Search results for "%s"', 'searchwiz' ), $search_query );
			} else {
				$title_parts['title'] = __( 'Search Results', 'searchwiz' );
			}
		}
		return $title_parts;
	}

	/**
	 * Fix breadcrumb for SearchWiz endpoint (/sw).
	 */
	function fix_searchwiz_breadcrumb( $breadcrumb, $args ) {
		// Check if this is a SearchWiz search endpoint
		if ( get_query_var( 'is_searchwiz_search' ) ) {
			$search_query = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

			// Replace "Error 404" or any 404-related breadcrumb with "Search Results"
			foreach ( $breadcrumb as $key => $crumb ) {
				if ( isset( $crumb[0] ) && (
					strpos( $crumb[0], '404' ) !== false ||
					strpos( $crumb[0], 'Error' ) !== false ||
					strpos( strtolower( $crumb[0] ), 'not found' ) !== false
				) ) {
					if ( $search_query ) {
						/* translators: %s: search query string */
						$breadcrumb[ $key ][0] = sprintf( __( 'Search results for "%s"', 'searchwiz' ), $search_query );
					} else {
						$breadcrumb[ $key ][0] = __( 'Search Results', 'searchwiz' );
					}
					// Remove the link from error breadcrumbs
					$breadcrumb[ $key ][1] = '';
				}
			}

			// If no breadcrumb was replaced, add search results breadcrumb
			$has_search_crumb = false;
			foreach ( $breadcrumb as $crumb ) {
				if ( isset( $crumb[0] ) && strpos( $crumb[0], 'Search results' ) !== false ) {
					$has_search_crumb = true;
					break;
				}
			}

			if ( ! $has_search_crumb ) {
				if ( $search_query ) {
					/* translators: %s: search query string */
					$breadcrumb[] = array( sprintf( __( 'Search results for "%s"', 'searchwiz' ), $search_query ), '' );
				} else {
					$breadcrumb[] = array( __( 'Search Results', 'searchwiz' ), '' );
				}
			}
		}
		return $breadcrumb;
	}

	/**
	 * Redirect WordPress default search (/?s=) to SearchWiz (/sw?s=) if setting is enabled.
	 */
	function redirect_default_search_to_sw() {
		// Only redirect on search pages
		if ( ! is_search() ) {
			return;
		}

		// Don't redirect if already on /sw endpoint
		if ( get_query_var( 'is_searchwiz_search' ) ) {
			return;
		}

		// Check if "Use SearchWiz as Default" is enabled
		$sw_settings = get_option( 'searchwiz_settings', array() );
		$use_searchwiz_default = ! isset( $sw_settings['default_search'] ) || 1 === (int) $sw_settings['default_search'];

		if ( $use_searchwiz_default ) {
			// Redirect /?s=query to /sw?s=query
			$search_query = get_search_query();
			if ( ! empty( $search_query ) ) {
				$redirect_url = home_url( '/sw' ) . '?' . http_build_query( array( 's' => $search_query ) );
				wp_safe_redirect( $redirect_url, 302 );
				exit;
			}
		}
	}

	/**
	 * Add custom query vars for SearchWiz search endpoint.
	 */
	function add_query_vars( $vars ) {
		$vars[] = 'is_searchwiz_search';
		return $vars;
	}

	/**
	 * Changes default search form.
	 */
	function get_search_form( $form ) {

		$is_settings = get_option( 'searchwiz_settings', array() );

		// Use SearchWiz as default if: default_search is 1 (enabled) OR not set (default enabled)
		$use_searchwiz_default = ! isset( $is_settings['default_search'] ) || 1 === (int) $is_settings['default_search'];
		if ( ! $use_searchwiz_default ) {
			return $form; // User disabled SearchWiz as default, return WordPress default form
		}

		$page = get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' );

		if ( ! empty( $page ) ) {
                        $search_form = SearchWiz_Search_Form::get_instance( $page->ID );
                        if ( $search_form ) {
                            $atts['id'] = (int) $page->ID;
                            $form  = $search_form->form_html( $atts, 'n' );
                        }
                }

		return $form;
        }


	/**
	 * Formats attributes.
	 */
	public static function format_atts( $atts ) {
		$html = '';

		$prioritized_atts = array( 'type', 'name', 'value' );

		foreach ( $prioritized_atts as $att ) {
			if ( isset( $atts[$att] ) ) {
				$value = trim( $atts[$att] );
				$html .= sprintf( ' %s="%s"', $att, esc_attr( $value ) );
				unset( $atts[$att] );
			}
		}

		foreach ( $atts as $key => $value ) {
			$key = strtolower( trim( $key ) );

			if ( ! preg_match( '/^[a-z_:][a-z_:.0-9-]*$/', $key ) ) {
				continue;
			}

			$value = trim( $value );

			if ( '' !== $value ) {
				$html .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
			}
		}

		$html = trim( $html );

		return $html;
	}

    /*
     * Declare support for WooCommerce features
     */
    public function declare_wc_features_support() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SEARCHWIZ_PLUGIN_FILE, true );
        }
    }

	/**
	 * Displays search form by processing shortcode.
	 */
	function search_form_shortcode( $args ) {

		if ( is_feed() ) {
			return '[searchwiz-search]';
		}

		$atts = shortcode_atts(
			array(
				'id'	=> 0,
				'title'	=> '',
			),
			$args, 'searchwiz-search'
		);

		$atts = array_map( 'sanitize_text_field', $atts );
		$display_id = '';
		if ( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {
			$page = get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' );

			if ( ! empty( $page ) ) {
				$atts['id'] = $page->ID;
				$display_id = 'n';
			}
		} else if ( ! is_numeric( $atts['id'] ) || 0 == $atts['id'] ) {
			return '[searchwiz-search 404 "Invalid search form ID '. esc_html( $atts['id'] ) .'"]';
		}

		$search_form = SearchWiz_Search_Form::get_instance( $atts['id'] );

		if ( ! $search_form ) {
			return '[searchwiz-search 404 "The search form '. esc_html( $atts['id'] ) .' does not exist"]';
		} else if ( 'default-search-form' == $search_form->name() ) {
			// Check if SearchWiz is disabled as default (0 = disabled)
			$use_searchwiz_default = isset( $is_settings['default_search'] ) ? (int) $is_settings['default_search'] : 1;
			if ( ! $use_searchwiz_default ) {
				$display_id = 'n'; // Don't display if user disabled SearchWiz as default
			}
		}

		$form  = $search_form->form_html( $atts, $display_id );

		return $form;
	}

}

$searchwiz_admin_public = SearchWiz_Admin_Public::getInstance();
// Removed backwards compatibility for [searchwiz-search] - use [searchwiz] instead

/**
 * Simple searchwiz shortcode - works without parameters
 * Supports positioning and styling parameters
 *
 * Usage: [searchwiz] or [searchwiz x=20 y=10 width=40]
 */
function searchwiz_simple_shortcode( $atts ) {
	// Prevent multiple renders on same page load when theme integration is active
	static $theme_integration_rendered = false;

	// Normalize shortcode attributes first
	$atts = shortcode_atts(
		array(
			'x'                  => 0,     // % from left edge (0-100)
			'y'                  => 0,     // % from top edge (0-100)
			'width'              => 40,    // % width (1-100)
			'height'             => 5,     // % height (1-100)
			'x_resultbox'        => 0,     // % result box from left (0-100)
			'y_resultbox'        => 0,     // % result box from top (0-100)
			'border'             => 2,     // Border width in px
			'bordercolor'        => '#0073aa', // Border color
			'result_border'      => 2,     // Result border width
			'result_bordercolor' => '#0073aa', // Result border color
			'minimalist'         => 'no',  // Show only icon (yes/no)
			'allow_render'       => 'no',  // Allow rendering when theme integration active (yes/no)
		),
		$atts,
		'searchwiz'
	);

	// Check if theme integration is active for ANY supported theme
	// If so, don't render standalone shortcode - only render when explicitly allowed
	$theme_integration = get_option( 'searchwiz_theme_integration', array() );

	if ( isset( $theme_integration['enabled'] ) && 'on' === $theme_integration['enabled'] ) {
		// Check if this shortcode is being rendered from theme integration
		// Theme integration will pass 'allow_render' => 'yes' parameter
		if ( $atts['allow_render'] !== 'yes' ) {
			// Don't render standalone shortcode when theme integration is active
			return '';
		}

		// Prevent duplicate renders - only render once per page load for theme integration
		if ( $theme_integration_rendered ) {
			return '';
		}
		$theme_integration_rendered = true;
	}

	// Detect active theme and add theme-specific CSS classes
	$theme_classes = '';
	if ( isset( $theme_integration['enabled'] ) && 'on' === $theme_integration['enabled'] ) {
		$theme = wp_get_theme();
		$theme_slug = strtolower( $theme->get_template() );

		// Add theme-specific CSS classes so theme's JavaScript can find our elements
		switch ( $theme_slug ) {
			case 'astra':
				// Astra expects: ast-search-box for the container
				$theme_classes = ' ast-search-box';
				break;
			case 'flatsome':
				$theme_classes = ' search-box';
				break;
			case 'oceanwp':
				$theme_classes = ' oceanwp-searchform-wrap';
				break;
			// Add more theme-specific classes as we test each theme
		}
	}

	// Sanitize and validate parameters
	$x = max(0, min(100, intval($atts['x'])));
	$y = max(0, min(100, intval($atts['y'])));
	$width = max(1, min(100 - $x, intval($atts['width'])));
	$height = max(1, min(100 - $y, intval($atts['height'])));
	$x_result = max(0, min(100, intval($atts['x_resultbox'])));
	$y_result = max(0, min(100, intval($atts['y_resultbox'])));
	$border = max(0, intval($atts['border']));
	$bordercolor = sanitize_hex_color($atts['bordercolor']) ?: '#0073aa';
	$result_border = max(0, intval($atts['result_border']));
	$result_bordercolor = sanitize_hex_color($atts['result_bordercolor']) ?: '#0073aa';
	$minimalist = strtolower(trim($atts['minimalist'])) === 'yes';

	// Calculate positioning
	$container_style = '';
	if ($x > 0 || $y > 0) {
		$container_style = sprintf(
			'position: absolute; left: %d%%; top: %d%%; z-index: 1000;',
			$x,
			$y
		);
	}

	// Build the search form HTML with submit button
	if ($minimalist) {
		// Minimalist mode: Only show icon, input expands on click
		$form_html = sprintf(
			'<div class="searchwiz-shortcode-container searchwiz-minimalist%s" style="%s">
				<form role="search" method="get" action="%s" class="searchwiz-simple-form" style="display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
					<input
						type="search"
						class="search-field searchwiz-search-input searchwiz-minimalist-input"
						placeholder="%s"
						name="s"
						style="flex: 1; padding: %dpx 12px; font-size: 16px; border: %dpx solid %s; border-radius: 4px; box-sizing: border-box; width: 0; opacity: 0; transition: all 0.3s ease;"
						data-result-border="%d"
						data-result-bordercolor="%s"
						data-result-x="%d"
						data-result-y="%d"
					/>
					<button type="button" class="is-search-submit searchwiz-minimalist-toggle" onclick="this.parentElement.classList.toggle(\'expanded\'); var input = this.previousElementSibling; if(input.style.width === \'0px\' || input.style.width === \'0\' || input.style.width === \'\') { input.style.width = \'250px\'; input.style.opacity = \'1\'; input.focus(); this.type = \'submit\'; } else { input.style.width = \'0\'; input.style.opacity = \'0\'; this.type = \'button\'; }" style="background: transparent; border: none; padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
						<svg focusable="false" aria-label="Search" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px" style="fill: #666;">
							<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
						</svg>
					</button>
				</form>
			</div>',
			esc_attr($theme_classes),
			esc_attr($container_style),
			esc_url(home_url('/sw')),
			esc_attr(__('Search...', 'searchwiz')),
			$height * 2, // Convert % to approximate px padding
			$border,
			esc_attr($bordercolor),
			$result_border,
			esc_attr($result_bordercolor),
			$x_result,
			$y_result
		);
	} else {
		// Normal mode: Show full search box
		$form_html = sprintf(
			'<div class="searchwiz-shortcode-container%s" style="%s">
				<form role="search" method="get" action="%s" class="searchwiz-simple-form" style="display: flex; align-items: center; gap: 8px;">
					<input
						type="search"
						class="search-field searchwiz-search-input"
						placeholder="%s"
						name="s"
						style="flex: 1; padding: %dpx 12px; font-size: 16px; border: %dpx solid %s; border-radius: 4px; box-sizing: border-box;"
						data-result-border="%d"
						data-result-bordercolor="%s"
						data-result-x="%d"
						data-result-y="%d"
					/>
					<button type="submit" class="is-search-submit" style="background: transparent; border: none; padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
						<svg focusable="false" aria-label="Search" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px" style="fill: #666;">
							<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
						</svg>
					</button>
				</form>
			</div>',
			esc_attr($theme_classes),
			esc_attr($container_style),
			esc_url(home_url('/sw')),
			esc_attr(__('Search...', 'searchwiz')),
			$height * 2, // Convert % to approximate px padding
			$border,
			esc_attr($bordercolor),
			$result_border,
			esc_attr($result_bordercolor),
			$x_result,
			$y_result
		);
	}

	return $form_html;
}

add_shortcode( 'searchwiz', 'searchwiz_simple_shortcode' );