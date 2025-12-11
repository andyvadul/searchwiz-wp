<?php
/**
 * Defines plugin settings fields.
 *
 * This class defines all code necessary to manage plugin settings fields.
 *
 * @package SW
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Settings_Fields {
    /**
     * Stores plugin options.
     */
    public $opt;

    /**
     * Core singleton class
     * @var self
     */
    private static $_instance;

    private $is_premium_plugin = false;

    /**
     * Instantiates the plugin by setting up the core properties and loading
     * all necessary dependencies and defining the hooks.
     *
     * The constructor uses internal functions to import all the
     * plugin dependencies, and will leverage the SearchWiz for
     * registering the hooks and the callback functions used throughout the plugin.
     */
    public function __construct() {
        if ( empty( $this->opt ) ) {
            $is_menu_search = get_option( 'searchwiz_menu_search', array() );
            $is_settings = get_option( 'searchwiz_settings', array() );
            $this->opt = array_merge( (array) $is_settings, (array) $is_menu_search );
        }
    }

    /**
     * Gets the instance of this class.
     *
     * @return self
     */
    public static function getInstance() {
        if ( !self::$_instance instanceof self ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Displays settings sections having custom markup.
     */
    public function is_do_settings_sections( $page, $sec ) {
        global $wp_settings_sections, $wp_settings_fields;
        if ( !isset( $wp_settings_sections[$page] ) ) {
            return;
        }
        $section = (array) $wp_settings_sections[$page][$sec];
        if ( $section['title'] ) {
            echo esc_html("<h2>{$section['title']}</h2>\n");
        }
        if ( $section['callback'] ) {
            call_user_func( $section['callback'], $section );
        }
        if ( !isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
            return;
        }
        ?>
		<div class="form-table search-form-editor-box">
		<?php 
        $this->is_do_settings_fields( $page, $section['id'] );
        ?>
		</div>
		<?php 
    }

    /**
     * Displays settings fields having custom markup.
     */
    public function is_do_settings_fields( $page, $section ) {
        global $wp_settings_fields;
        if ( !isset( $wp_settings_fields[$page][$section] ) ) {
            return;
        }
        foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
            $class = '';
            if ( !empty( $field['args']['class'] ) ) {
                $class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
            }
            if ( !empty( $field['args']['label_for'] ) ) {
                ?>
			<h3 scope="row"><label for="<?php 
                echo esc_attr( $field['args']['label_for'] );
                ?>"><?php 
                echo wp_kses_post( $field['title'] );
                ?></label>
			<?php 
            } else {
                ?> 
			<h3 scope="row"><?php 
                echo wp_kses_post( $field['title'] );
            }
            // Accordion functionality removed - all sections now shown expanded
            ?>
            </h3>
            <div>
			<?php 
            call_user_func( $field['callback'], $field['args'] );
            ?>
		    </div>
		    <?php 
        }
    }

    /**
     * Registers plugin settings fields.
     */
    function register_settings_fields() {
        if ( !empty( $GLOBALS['pagenow'] ) && 'options.php' === $GLOBALS['pagenow'] ) {
            global $wp_version;
            $temp_oname = 'whitelist_options';
            if ( version_compare( $wp_version, '5.5', '>=' ) ) {
                $temp_oname = 'allowed_options';
            }
            if ( isset( $_POST['searchwiz_menu_search'] ) ) {
                add_filter( $temp_oname, function ( $allowed_options ) {
                    $allowed_options['searchwiz_search'][0] = 'searchwiz_menu_search';
                    return $allowed_options;
                } );
            } else {
                if ( isset( $_POST['searchwiz_analytics'] ) ) {
                    add_filter( $temp_oname, function ( $allowed_options ) {
                        $allowed_options['searchwiz_search'][0] = 'searchwiz_analytics';
                        return $allowed_options;
                    } );
                } else {
                    if ( isset( $_POST['searchwiz_index'] ) ) {
                        add_filter( $temp_oname, function ( $allowed_options ) {
                            $allowed_options['searchwiz_search'][0] = 'searchwiz_index';
                            return $allowed_options;
                        } );
                    }
                }
            }
            // Always allow theme integration setting
            if ( isset( $_POST['searchwiz_theme_integration'] ) ) {
                add_filter( $temp_oname, function ( $allowed_options ) {
                    if ( ! isset( $allowed_options['searchwiz_search'] ) ) {
                        $allowed_options['searchwiz_search'] = array();
                    }
                    $allowed_options['searchwiz_search'][] = 'searchwiz_theme_integration';
                    return $allowed_options;
                } );
            }
        }
        // Determine which page we're on to set appropriate default tab
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $is_backend = ( 'searchwiz-search-backend' === $current_page );

        // Default tab based on section - backend defaults to 'index' for Content tab
        $tab = $is_backend ? 'index' : 'settings';

        $request_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
        if ( ! empty( $request_tab ) ) {
            switch ( $request_tab ) {
                case 'search-box':
                    $tab = 'settings'; // Search Box tab uses settings section
                    break;
                case 'results':
                    $tab = 'display';
                    break;
                case 'content':
                    $tab = 'index'; // Content tab uses index section
                    break;
                case 'display':
                    $tab = 'display';
                    break;
                case 'menu-search':
                    $tab = 'menu-search';
                    break;
                case 'analytics':
                    $tab = 'analytics';
                    break;
                case 'index':
                    $tab = 'index';
                    break;
            }
        }
        if ( 'settings' === $tab ) {
            add_settings_section(
                'searchwiz_search_settings',
                '',
                array($this, 'settings_section_desc'),
                'searchwiz_search'
            );
            // NEW: Shortcodes section
            add_settings_field(
                'searchwiz_search_shortcodes',
                __( 'Shortcodes', 'searchwiz' ),
                array($this, 'shortcodes'),
                'searchwiz_search',
                'searchwiz_search_settings'
            );

            // Search Box Appearance section
            add_settings_section(
                'searchwiz_searchbox_appearance',
                '',
                array($this, 'searchbox_appearance_section_desc'),
                'searchwiz_search'
            );
            add_settings_field(
                'searchwiz_searchbox_border_color',
                __( 'Border Color', 'searchwiz' ),
                array($this, 'searchbox_border_color_field'),
                'searchwiz_search',
                'searchwiz_searchbox_appearance'
            );
            add_settings_field(
                'searchwiz_searchbox_focus_color',
                __( 'Focus Color', 'searchwiz' ),
                array($this, 'searchbox_focus_color_field'),
                'searchwiz_search',
                'searchwiz_searchbox_appearance'
            );
            register_setting( 'searchwiz_search', 'searchwiz_searchbox_settings', array(
                'sanitize_callback' => array($this, 'is_validate_searchbox_settings')
            ));

            // Custom CSS field removed per WordPress.org plugin guidelines.
            // Plugins should not allow users to input arbitrary CSS.
            // Users can use the WordPress Customizer CSS editor instead.
            // See: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
            // Removed: Stopwords - moved to Back-End Content
            // Removed: Synonyms - moved to Back-End Content
            // Removed: Header Search - replaced by Shortcodes
            // Removed: Footer Search - replaced by Shortcodes
            // Removed: Mobile Search - replaced by Shortcodes
            // Removed: Plugin Files - not needed
            // Removed: Advanced section (disable search, easy edit) - unnecessary features
            register_setting( 'searchwiz_search', 'searchwiz_settings', array(
                'sanitize_callback' => array($this, 'is_validate_setting')
            ));

            // CONSOLIDATED: Display settings now in Configuration tab
            add_settings_section(
                'searchwiz_search_display',
                '',
                array($this, 'display_section_desc'),
                'searchwiz_search'
            );
            add_settings_field(
                'searchwiz_primary_color',
                __( 'Primary Color', 'searchwiz' ),
                array($this, 'primary_color_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_title_font_size',
                __( 'Title Font Size', 'searchwiz' ),
                array($this, 'title_font_size_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_show_thumbnails',
                __( 'Show Thumbnails', 'searchwiz' ),
                array($this, 'show_thumbnails_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_show_excerpts',
                __( 'Show Excerpts', 'searchwiz' ),
                array($this, 'show_excerpts_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_excerpt_length',
                __( 'Excerpt Length (words)', 'searchwiz' ),
                array($this, 'excerpt_length_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_card_spacing',
                __( 'Card Spacing', 'searchwiz' ),
                array($this, 'card_spacing_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_border_radius',
                __( 'Border Radius', 'searchwiz' ),
                array($this, 'border_radius_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            register_setting( 'searchwiz_search', 'searchwiz_display_settings', array(
                'sanitize_callback' => array($this, 'is_validate_display_settings')
            ));
        } else if ( 'display' === $tab ) {
            // Redirect to settings tab for backward compatibility
            $tab = 'settings';
            add_settings_section(
                'searchwiz_search_display',
                '',
                array($this, 'display_section_desc'),
                'searchwiz_search'
            );
            add_settings_field(
                'searchwiz_primary_color',
                __( 'Primary Color', 'searchwiz' ),
                array($this, 'primary_color_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_title_font_size',
                __( 'Title Font Size', 'searchwiz' ),
                array($this, 'title_font_size_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_show_thumbnails',
                __( 'Show Thumbnails', 'searchwiz' ),
                array($this, 'show_thumbnails_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_show_excerpts',
                __( 'Show Excerpts', 'searchwiz' ),
                array($this, 'show_excerpts_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_excerpt_length',
                __( 'Excerpt Length (words)', 'searchwiz' ),
                array($this, 'excerpt_length_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_card_spacing',
                __( 'Card Spacing', 'searchwiz' ),
                array($this, 'card_spacing_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            add_settings_field(
                'searchwiz_border_radius',
                __( 'Border Radius', 'searchwiz' ),
                array($this, 'border_radius_field'),
                'searchwiz_search',
                'searchwiz_search_display'
            );
            register_setting( 'searchwiz_search', 'searchwiz_display_settings', array(
                'sanitize_callback' => array($this, 'is_validate_display_settings')
            ));
        } else {
            if ( 'menu-search' === $tab ) {
                add_settings_section(
                    'searchwiz_search_section',
                    '',
                    array($this, 'menu_search_section_desc'),
                    'searchwiz_search'
                );
                add_settings_field(
                    'searchwiz_search_locations',
                    __( 'Menu Search Settings', 'searchwiz' ),
                    array($this, 'menu_settings'),
                    'searchwiz_search',
                    'searchwiz_search_section'
                );
                register_setting( 'searchwiz_search', 'searchwiz_menu_search', array(
                    'sanitize_callback' => 'sanitize_text_field'
                ));
            } else {
                if ( 'analytics' === $tab ) {
                    add_settings_section(
                        'searchwiz_search_analytics',
                        '',
                        array($this, 'analytics_section_desc'),
                        'searchwiz_search'
                    );
                    add_settings_field(
                        'searchwiz_search_analytics_fields',
                        __( 'Search Analytics', 'searchwiz' ),
                        array($this, 'analytics'),
                        'searchwiz_search',
                        'searchwiz_search_analytics'
                    );
                    register_setting( 'searchwiz_search', 'searchwiz_analytics', array(
                        'sanitize_callback' => 'sanitize_text_field'
                    ));
                } else {
                    if ( 'index' === $tab ) {
                        $index = SearchWiz_Settings_Index_Fields::getInstance();
                        add_settings_section(
                            'searchwiz_search_index',
                            '',
                            array($index, 'index_section_desc'),
                            'searchwiz_search'
                        );
                        add_settings_field(
                            'searchwiz_search_type',
                            __( 'Search Type', 'searchwiz' ),
                            array($this, 'search_type_field'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        add_settings_field(
                            'searchwiz_search_index_post_types',
                            __( 'Post Types', 'searchwiz' ),
                            array($index, 'post_types_settings'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        // User Comments toggle is now in the Post Types dropdown
                        // No need for separate section
                        add_settings_field(
                            'searchwiz_search_index_taxonomies',
                            __( 'Taxonomies', 'searchwiz' ),
                            array($index, 'taxonomies_settings'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        add_settings_field(
                            'searchwiz_search_index_meta_fields',
                            __( 'Custom Fields', 'searchwiz' ),
                            array($index, 'meta_fields_settings'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        // MOVED FROM FRONT-END: Stopwords and Synonyms
                        add_settings_field(
                            'searchwiz_search_stopwords',
                            __( 'Stopwords', 'searchwiz' ),
                            array($this, 'stopwords'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        add_settings_field(
                            'searchwiz_search_synonyms',
                            __( 'Synonyms', 'searchwiz' ),
                            array($this, 'synonyms'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        add_settings_field(
                            'searchwiz_search_index_extra_fields',
                            __( 'Extras', 'searchwiz' ),
                            array($index, 'extra_settings'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                        add_settings_field(
                            'searchwiz_search_index_advanced_fields',
                            __( 'Advanced', 'searchwiz' ),
                            array($index, 'advanced_settings'),
                            'searchwiz_search',
                            'searchwiz_search_index'
                        );
                    }
                }
            }
        }

        // IMPORTANT: Register sw_index OUTSIDE of tab conditionals
        // This ensures the sanitize_callback is registered even during POST processing
        // (when $_GET['page'] and $_GET['tab'] are not available)
        register_setting( 'searchwiz_search', 'searchwiz_index', array(
            'sanitize_callback' => array( $this, 'sanitize_index_settings' )
        ));

        // Register theme integration setting
        register_setting( 'searchwiz_search', 'searchwiz_theme_integration', array(
            'sanitize_callback' => array( $this, 'sanitize_theme_integration' ),
            'default' => array( 'enabled' => 'on' ) // Default to enabled for supported themes
        ));
    }

    /**
     * Sanitize theme integration setting
     */
    function sanitize_theme_integration( $input ) {
        $sanitized = array();
        $sanitized['enabled'] = isset( $input['enabled'] ) && 'on' === $input['enabled'] ? 'on' : 'off';
        return $sanitized;
    }

    /**
     * Sanitize index settings (Extras and Advanced options)
     */
    function sanitize_index_settings( $input ) {
        if ( ! is_array( $input ) ) {
            $input = array();
        }

        // Get existing options to merge with
        $existing = get_option( 'searchwiz_index', array() );

        // Define checkbox fields that should be sanitized as boolean
        $checkbox_fields = array(
            'auto_index_enabled',
            'index_title',
            'index_content',
            'index_excerpt',
            'index_tax_title',
            'index_tax_desp',
            'index_product_sku',
            'index_product_variation',
            'index_comments',
            'index_user_comments',
            'index_author_info',
            'expand_shortcodes',
            'yoast_no_index',
            'throttle_searches',
        );

        // Define array fields
        $array_fields = array( 'post_types', 'tax_selected', 'meta_fields_selected' );

        // Define select/radio fields with allowed values
        $select_fields = array(
            'tax_index_opt' => array( 'all', 'none', 'select' ),
            'meta_fields_opt' => array( 'all', 'visible', 'none', 'select' ),
            'hyphens' => array( 'remove', 'replace', 'keep' ),
            'quotes' => array( 'remove', 'replace', 'keep' ),
            'ampersands' => array( 'remove', 'replace', 'keep' ),
            'decimals' => array( 'remove', 'replace', 'keep' ),
        );

        // Start with existing options
        $sanitized = $existing;

        // IMPORTANT: For checkboxes, if not in $input, they were unchecked - set to 0
        // This is because unchecked checkboxes are NOT submitted by browsers
        foreach ( $checkbox_fields as $field ) {
            if ( isset( $input[ $field ] ) ) {
                $sanitized[ $field ] = 1;
            } else {
                // Field not submitted = unchecked = 0
                $sanitized[ $field ] = 0;
            }
        }

        // Process other fields from input
        foreach ( $input as $key => $value ) {
            // Skip checkbox fields - already handled above
            if ( in_array( $key, $checkbox_fields, true ) ) {
                continue;
            }

            if ( 'min_word_length' === $key ) {
                // Numeric field - ensure it's an integer within range
                $sanitized[ $key ] = absint( $value );
                if ( $sanitized[ $key ] < 1 ) {
                    $sanitized[ $key ] = 1;
                }
                if ( $sanitized[ $key ] > 40 ) {
                    $sanitized[ $key ] = 40;
                }
            } elseif ( in_array( $key, $array_fields, true ) ) {
                // Array fields - sanitize each element
                if ( is_array( $value ) ) {
                    $sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
                } else {
                    $sanitized[ $key ] = array();
                }
            } elseif ( isset( $select_fields[ $key ] ) ) {
                // Select/radio fields - validate against allowed values
                $sanitized[ $key ] = in_array( $value, $select_fields[ $key ], true ) ? $value : $select_fields[ $key ][0];
            } else {
                // Any other fields - sanitize as text
                $sanitized[ $key ] = sanitize_text_field( $value );
            }
        }

        return $sanitized;
    }

    function is_validate_setting( $args ) {
        // Custom CSS validation removed - feature deprecated per WordPress.org guidelines.
        // Remove any custom_css data from args to prevent saving.
        if ( isset( $args['custom_css'] ) ) {
            unset( $args['custom_css'] );
        }

        if ( isset( $args['stopwords'] ) && preg_match( '#</?\\w+#', $args['stopwords'] ) ) {
            add_settings_error(
                'searchwiz_settings',
                'invalid_is_stopwords',
                __( 'Invalid Stopwords', 'searchwiz' ),
                'error'
            );
            $args['stopwords'] = isset( $this->opt['stopwords'] ) ? $this->opt['stopwords'] : '';
        }
        if ( isset( $args['synonyms'] ) && preg_match( '#</?\\w+#', $args['synonyms'] ) ) {
            add_settings_error(
                'searchwiz_settings',
                'invalid_is_synonyms',
                __( 'Invalid Synonyms', 'searchwiz' ),
                'error'
            );
            $args['synonyms'] = isset( $this->opt['synonyms'] ) ? $this->opt['synonyms'] : '';
        }
        return $args;
    }

    /**
     * Displays Search To Menu section description text.
     */
    function menu_search_section_desc() {
        ?>
		<h4 class="panel-desc">
			<?php 
        esc_html_e( 'Configure Menu Search', 'searchwiz' );
        ?>
		</h4>
		<?php 
    }

    /**
     * Displays Analytics section description text.
     */
    function analytics_section_desc() {
        ?>
		<h4 class="panel-desc">
			<?php 
        esc_html_e( 'Search Analytics', 'searchwiz' );
        ?>
		</h4>
		<?php 
    }

    /**
     * Displays Settings section description text.
     */
    function settings_section_desc() {
        ?>
		<h4 class="panel-desc">
			<?php
        esc_html_e( 'Search Configuration', 'searchwiz' );
        ?>
		</h4>
		<?php
    }

    /**
     * Displays menu settings fields.
     */
    function menu_settings() {
        /**
         * Displays choose menu locations field.
         */
        $content = __( 'Display search form on selected menu locations.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $menus = get_registered_nav_menus();
        ?>
		<div>
		<?php 
        if ( !empty( $menus ) ) {
            $check_value = '';
            foreach ( $menus as $location => $description ) {
                if ( has_nav_menu( $location ) ) {
                    $check_value = ( isset( $this->opt['menus'][$location] ) ? $this->opt['menus'][$location] : 0 );
                    ?>
					<p><label for="is_menus<?php 
                    echo esc_attr( $location );
                    ?>"><input type="checkbox" class="searchwiz_search_locations" id="is_menus<?php 
                    echo esc_attr( $location );
                    ?>" name="is_menu_search[menus][<?php 
                    echo esc_attr( $location );
                    ?>]" value="<?php 
                    echo esc_attr( $location );
                    ?>" <?php 
                    checked( $location, $check_value, true );
                    ?>/>
					<span class="toggle-check-text"></span> <?php 
                    echo esc_html( $description );
                    ?> </label></p>
                <?php 
                }
            }
            if ( '' === $check_value ) {
                // translators: %1: Menu name %2: URL
                printf( esc_html__( 'No menu assigned to navigation menu location in the %1\$sMenus screen %2\$%s.', 'searchwiz' ), '<a target="_blank" href="' . esc_url(admin_url( 'nav-menus.php' )) . '">', '</a>' );
            }
        } else {
            esc_html_e( 'Navigation menu location is not registered on the site.', 'searchwiz' );
        }
        ?>
		</div><br />
  		 <?php
        /**
         * Menu Names section removed to avoid duplicate Primary Menu display
         * Only showing Menu Locations above - see issue #42
         */
        ?>
		</div>
  		 <?php 
        if ( !isset( $this->opt['menus'] ) && !isset( $this->opt['menu_name'] ) || '' === $check_value ) {
            return;
        }
        ?>
        <div class="menu-settings-container"><br /><br />
		<?php 
        /**
         * Displays search form at the beginning of menu field.
         */
        $check_value = ( isset( $this->opt['first_menu_item'] ) ? $this->opt['first_menu_item'] : 0 );
        ?>
        <div>
		<label for="first_menu_item"><input class="searchwiz_search_first_menu_item" type="checkbox" id="first_menu_item" name="is_menu_search[first_menu_item]" value="first_menu_item" <?php 
        checked( 'first_menu_item', $check_value, true );
        ?> />
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display search form at the start of the navigation menu', 'searchwiz' );
        ?></label>
		</div> <br /><br />
		<?php 
        /**
         * Displays form style field.
         */
        $content = __( 'Select menu search form style.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $styles = array(
            'default'         => __( 'Default', 'searchwiz' ),
            'dropdown'        => __( 'Dropdown', 'searchwiz' ),
            'sliding'         => __( 'Sliding', 'searchwiz' ),
            'full-width-menu' => __( 'Full Width', 'searchwiz' ),
            'popup'           => __( 'Popup', 'searchwiz' ),
        );
        $menu_close_icon = false;
        if ( empty( $this->opt ) || !isset( $this->opt['menu_style'] ) ) {
            $this->opt['menu_style'] = 'dropdown';
            $menu_close_icon = true;
        }
        $check_value = ( isset( $this->opt['menu_style'] ) ? $this->opt['menu_style'] : 'dropdown' );
        ?>
		<div class="search-form-style">
		<?php 
        foreach ( $styles as $key => $style ) {
            ?>
            <p>
			<label for="is_menu_style<?php 
            echo esc_attr( $key );
            ?>"><input class="searchwiz_search_style" type="radio" id="is_menu_style<?php 
            echo esc_attr( $key );
            ?>" name="is_menu_search[menu_style]" value="<?php 
            echo esc_attr( $key );
            ?>" <?php 
            checked( $key, $check_value, true );
            ?>/>
			<span class="toggle-check-text"></span><?php 
            echo esc_html( $style );
            ?></label>
			</p>
		<?php 
        }
        ?>
		</div><br /><br />
		<div class="form-style-dependent">
		<?php 
        /**
         * Displays menu search magnifier colorpicker field.
         */
        $color = ( isset( $this->opt['menu_magnifier_color'] ) ? $this->opt['menu_magnifier_color'] : '#848484' );
        ?>
		<input style="width: 80px;" class="menu-magnifier-color is-colorpicker" size="5" type="text" id="is-menu-magnifier-color" name="is_menu_search[menu_magnifier_color]" value="<?php 
        echo esc_attr( $color );
        ?>" />
		<br /><i> <?php 
        esc_html_e( 'Select menu magnifier icon color.', 'searchwiz' );
        ?></i><br /><br />
		<?php 
        /**
         * Displays search form close icon field.
         */
        $check_value = ( isset( $this->opt['menu_close_icon'] ) ? $this->opt['menu_close_icon'] : 0 );
        if ( !$check_value && $menu_close_icon ) {
            $check_value = 'menu_close_icon';
        }
        ?>
        <div>
		<label for="menu_close_icon"><input class="searchwiz_search_close_icon" type="checkbox" id="menu_close_icon" name="is_menu_search[menu_close_icon]" value="menu_close_icon" <?php 
        checked( 'menu_close_icon', $check_value, true );
        ?> />
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display search form close icon', 'searchwiz' );
        ?></label>
		</div> <br /><br />
		<?php 
        /**
         * Displays search menu title field.
         */
        $content = __( 'Add menu title to display in place of search icon.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $this->opt['menu_title'] = ( isset( $this->opt['menu_title'] ) ? $this->opt['menu_title'] : '' );
        ?>
		<div><input type="text" class="searchwiz_search_title" id="is_menu_title" name="is_menu_search[menu_title]" value="<?php 
        echo esc_attr( $this->opt['menu_title'] );
        ?>" />
		</div> <br /><br />
		</div>
		<?php 
        /**
         * Displays menu search form field.
         */
        $content = __( 'Select search form that will control menu search functionality.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $args = array(
            'numberposts' => -1,
            'post_type'   => SearchWiz_Search_Form::post_type,
            'order'       => 'ASC',
        );
        $posts = get_posts( $args );
        ?>
		<div>
		<?php 
        if ( !empty( $posts ) ) {
            $check_value = ( isset( $this->opt['menu_search_form'] ) ? $this->opt['menu_search_form'] : 0 );
            ?>
			<select class="searchwiz_search_form" id="menu_search_form" name="is_menu_search[menu_search_form]" >
			<option value="0"><?php 
            esc_html_e( 'None', 'searchwiz' );
            ?></option>
			<?php 
            foreach ( $posts as $post ) {
                ?>
				<option value="<?php 
                echo esc_attr( $post->ID );
                ?>" <?php 
                selected( $post->ID, $check_value, true );
                ?>><?php 
                echo esc_html( $post->post_title );
                ?></option>
			<?php 
            }
            ?>
			</select>
			<?php 
            if ( $check_value ) {
                ?>
				<a href="<?php 
                echo esc_url( menu_page_url( 'searchwiz-search', false ) . '&post=' . absint( $check_value ) . '&action=edit' );
                ?>">  <?php 
                esc_html_e( 'Edit Search Form', 'searchwiz' );
                ?></a>
			<?php 
            } else {
                ?>
				<a href="<?php 
                echo esc_url( menu_page_url( 'searchwiz-search-new', false ) );
                ?>">  <?php 
                esc_html_e( "Create New", 'searchwiz' );
                ?></a>
			<?php 
            }
        }
        ?>
		</div><br /><br />
		<?php 
        /**
         * Displays search menu classes field.
         */
        ?>
        <p><?php esc_html_e( 'Add custom CSS classes to the search menu item for advanced styling.', 'searchwiz' ); ?></p>
        <p style="font-size: 12px; color: #666;">
            <strong><?php esc_html_e( 'When to use:', 'searchwiz' ); ?></strong>
            <?php esc_html_e( 'Only needed if your theme requires specific classes for styling menu items.', 'searchwiz' ); ?>
        </p>
        <?php
        $this->opt['menu_classes'] = ( isset( $this->opt['menu_classes'] ) ? $this->opt['menu_classes'] : '' );
        ?>
		<div>
		<input type="text" class="searchwiz_search_classes" id="is_menu_classes" name="is_menu_search[menu_classes]" value="<?php
        echo esc_attr( $this->opt['menu_classes'] );
        ?>" placeholder="<?php esc_attr_e( 'e.g., menu-item-search custom-class', 'searchwiz' ); ?>" />
		<br /><label for="is_menu_classes" style="font-size: 10px;"><?php
        esc_html_e( 'Separate multiple classes with spaces.', 'searchwiz' );
        ?></label>
		</div><br /><br />
        <?php
        /**
         * Displays google cse field.
         */
        $content = __( 'Add Google Custom Search( CSE ) search form code that will replace default search form.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $this->opt['menu_gcse'] = ( isset( $this->opt['menu_gcse'] ) ? $this->opt['menu_gcse'] : '' );
        ?>
		<div>
		<input class="searchwiz_search_gcse" type="text" id="is_menu_gcse" name="is_menu_search[menu_gcse]" value="<?php 
        echo esc_attr( $this->opt['menu_gcse'] );
        ?>" />
		</div></div>
		<?php 
    }

    /**
     * Displays search analytics fields.
     */
    function analytics() {
        $is_analytics = get_option( 'searchwiz_analytics', array() );
        $check_value = ( isset( $is_analytics['disable_analytics'] ) ? $is_analytics['disable_analytics'] : 0 );
        ?>
        <div>
		<label for="is_disable_analytics"><select class="searchwiz_search_disable_analytics" id="is_disable_analytics" name="is_analytics[disable_analytics]" >
		<option value="0" <?php 
        selected( 0, $check_value, true );
        ?>><?php 
        esc_html_e( 'Enabled', 'searchwiz' );
        ?></option>
		<option value="1" <?php 
        selected( 1, $check_value, true );
        ?>><?php 
        esc_html_e( 'Disabled', 'searchwiz' );
        ?></option>
		</select> <?php 
        esc_html_e( 'Google Analytics 4 tracking for searches', 'searchwiz' );
        ?></label>
		<div class="analytics-info" <?php 
        echo ( $check_value ? 'style="display:none;"' : '' );
        ?> ><br/><br/><p><?php 
        esc_html_e( 'Search Analytics uses Google Analytics 4 to track searches.', 'searchwiz' );
        ?></p>
		<p><?php 
        // translators: %1: Feature name %2: Product name
        printf( esc_html__( "You need %1\$s Google Analytics 4 %2\$s to be installed on your site.", 'searchwiz' ), "<a target='_blank' href='https://support.google.com/tagmanager/topic/9578449'>", '</a>' );
        ?></p>
		<p><?php 
        esc_html_e( 'Data will be visible inside Google Analytics 4 \'Events\' and \'Site Search\' report.', 'searchwiz' );
        ?></p>
		<br/><p><?php 
        esc_html_e( 'Events will be as below:', 'searchwiz' );
        ?></p>
		<p><b><?php 
        esc_html_e( 'Category - Results Found / Nothing Found', 'searchwiz' );
        ?></b></p>
		<p><b><?php 
        esc_html_e( 'Action - SearchWiz - ID', 'searchwiz' );
        ?></b></p>
		<p><b><?php 
        esc_html_e( 'Label - Value of search term', 'searchwiz' );
        ?></b></p>
		<br/><p><?php 
        // translators: %1: Feature %2: Product name
        printf( esc_html__( "Need to %1\$s activate Site Search feature %2\$s inside Google Analytics to display data inside 'Site Search' report.", 'searchwiz' ), "<a target='_blank' href='https://support.google.com/analytics/answer/1012264'>", '</a>' );
        ?></p>
		<p><?php 
        esc_html_e( 'Enable Site search Tracking option in Site Search Settings and set its parameters as below.', 'searchwiz' );
        ?></p>
		<p><b><?php 
        esc_html_e( 'Query parameter - s', 'searchwiz' );
        ?></b></p>
		<p><b><?php 
        esc_html_e( 'Category parameter - id / result', 'searchwiz' );
        ?></b></p>
		</div></div>
		<?php 
    }

    /**
     * Displays search form in site header.
     */
    function header() {
        echo esc_html__( 'Select search form to display in site header( Not Menu ).', 'searchwiz' ) . '<br /><br />';
        $args = array(
            'numberposts' => -1,
            'post_type'   => SearchWiz_Search_Form::post_type,
        );
        $posts = get_posts( $args );
        ?>
		<div>
		<?php 
        if ( !empty( $posts ) ) {
            $check_value = ( isset( $this->opt['header_search'] ) ? $this->opt['header_search'] : 0 );
            ?>
			<select class="searchwiz_search_header" id="is_header_search" name="searchwiz_settings[header_search]" >
			<option value="0" <?php 
            selected( 0, $check_value, true );
            ?>><?php 
            esc_html_e( 'None', 'searchwiz' );
            ?></option>
			<?php 
            foreach ( $posts as $post ) {
                ?>
				<option value="<?php 
                echo esc_attr( $post->ID );
                ?>" <?php 
                selected( $post->ID, $check_value, true );
                ?>><?php 
                echo esc_html( $post->post_title );
                ?></option>
			<?php 
            }
            ?>
			</select>
			<?php 
            if ( $check_value && get_post_type( $check_value ) ) {
                ?>
				<a href="<?php 
                echo esc_url( menu_page_url( 'searchwiz-search', false ) . '&post=' . absint( $check_value ) . '&action=edit' );
                ?>"><?php 
                esc_html_e( "Edit", 'searchwiz' );
                ?></a>
			<?php 
            } else {
                ?>
				<a href="<?php 
                echo esc_url( menu_page_url( 'searchwiz-search-new', false ) );
                ?>"><?php 
                esc_html_e( "Create New", 'searchwiz' );
                ?></a>
			<?php 
            }
        }
        ?>
		<br/><br/><span class="is-help"><span class="is-info-warning"><?php 
        esc_html_e( 'Please note that the above option displays search form in site header and not in navigation menu.', 'searchwiz' );
        ?></span></span></div>
	<?php 
    }

    /**
     * Displays search form in site footer.
     */
    function footer() {
        esc_html_e( 'Select search form to display in site footer.', 'searchwiz' );
        ?>
		<br /><br />
		<div>
		<?php 
        $args = array(
            'numberposts' => -1,
            'post_type'   => SearchWiz_Search_Form::post_type,
        );
        $posts = get_posts( $args );
        if ( !empty( $posts ) ) {
            $check_value = ( isset( $this->opt['footer_search'] ) ? $this->opt['footer_search'] : 0 );
            ?>
			<select class="searchwiz_search_footer" id="is_footer_search" name="searchwiz_settings[footer_search]" >
			<option value="0" <?php 
            selected( 0, $check_value, true );
            ?>><?php 
            esc_html_e( 'None', 'searchwiz' );
            ?></option>
			<?php 
            foreach ( $posts as $post ) {
                ?>
				<option value="<?php 
                echo esc_attr( $post->ID );
                ?>" <?php 
                selected( $post->ID, $check_value, true );
                ?>><?php 
                echo esc_html( $post->post_title );
                ?></option>
			<?php 
            }
            ?>
			</select>
			<?php 
            if ( $check_value && get_post_type( $check_value ) ) {
                ?>
				<a href="<?php 
                echo esc_url( menu_page_url( 'searchwiz-search', false ) . '&post=' . absint( $check_value ) . '&action=edit' );
                ?>"> <?php 
                esc_html_e( "Edit", 'searchwiz' );
                ?></a>
			<?php 
            } else {
                ?>
				<a href="<?php 
                echo esc_url( menu_page_url( 'searchwiz-search-new', false ) );
                ?>">  <?php 
                esc_html_e( "Create New", 'searchwiz' );
                ?></a>
			<?php 
            }
        }
        ?>
		</div>
		<?php 
    }

    /**
     * Displays display in header field.
     */
    function menu_search_in_header() {
        $check_value = ( isset( $this->opt['header_menu_search'] ) ? $this->opt['header_menu_search'] : 0 );
        $check_string = checked( 'header_menu_search', $check_value, false );
        ?>
        <div>
		<label for="is_search_in_header"><input class="searchwiz_search_display_in_header" type="checkbox" id="is_search_in_header" name="searchwiz_settings[header_menu_search]" value="header_menu_search" <?php 
        echo esc_attr( $check_string );
        ?>/>
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'Display search form in site header on mobile devices', 'searchwiz' );
        ?></label>
		</div>
		<span class="site-uses-cache-wrapper" style="display: none;">
		<br />
		<?php 
        $content = __( 'If this site uses cache then please select the below option to display search form on mobile.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $check_value = ( isset( $this->opt['site_uses_cache'] ) ? $this->opt['site_uses_cache'] : 0 );
        $check_string = checked( 'site_uses_cache', $check_value, false );
        ?>
		<div>
		<label for="is_site_uses_cache"><input class="searchwiz_search_display_in_header" type="checkbox" id="is_site_uses_cache" name="searchwiz_settings[site_uses_cache]" value="site_uses_cache" <?php 
        echo esc_attr( $check_string );
        ?>/>
		<span class="toggle-check-text"></span><?php 
        esc_html_e( 'This site uses cache', 'searchwiz' );
        ?></label>
		</div>
		</span>
		<?php 
    }

    /**
     * Displays shortcodes section with copy buttons.
     */
    function shortcodes() {
        ?>
        <p><?php esc_html_e( 'Use these shortcodes to add SearchWiz to any page, post, or widget area.', 'searchwiz' ); ?></p>

        <div style="margin: 20px 0;">
            <table class="wp-list-table widefat" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th style="padding: 12px;"><?php esc_html_e( 'Shortcode', 'searchwiz' ); ?></th>
                        <th style="padding: 12px;"><?php esc_html_e( 'Description', 'searchwiz' ); ?></th>
                        <th style="padding: 12px; width: 100px;"><?php esc_html_e( 'Action', 'searchwiz' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px;">
                            <code class="searchwiz-shortcode" style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[searchwiz]</code>
                        </td>
                        <td style="padding: 12px;">
                            <?php esc_html_e( 'Basic search box - use anywhere in posts, pages, or widgets', 'searchwiz' ); ?>
                        </td>
                        <td style="padding: 12px;">
                            <button type="button" class="button button-small sw-copy-shortcode" data-shortcode="[searchwiz]">
                                <?php esc_html_e( 'Copy', 'searchwiz' ); ?>
                            </button>
                        </td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td style="padding: 12px;">
                            <code class="searchwiz-shortcode" style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[searchwiz placeholder="Search..."]</code>
                        </td>
                        <td style="padding: 12px;">
                            <?php esc_html_e( 'Custom placeholder text', 'searchwiz' ); ?>
                        </td>
                        <td style="padding: 12px;">
                            <button type="button" class="button button-small sw-copy-shortcode" data-shortcode='[searchwiz placeholder="Search..."]'>
                                <?php esc_html_e( 'Copy', 'searchwiz' ); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px;">
                            <code class="searchwiz-shortcode" style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[searchwiz autocomplete="off"]</code>
                        </td>
                        <td style="padding: 12px;">
                            <?php esc_html_e( 'Disable autocomplete suggestions', 'searchwiz' ); ?>
                        </td>
                        <td style="padding: 12px;">
                            <button type="button" class="button button-small sw-copy-shortcode" data-shortcode='[searchwiz autocomplete="off"]'>
                                <?php esc_html_e( 'Copy', 'searchwiz' ); ?>
                            </button>
                        </td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td style="padding: 12px;">
                            <code class="searchwiz-shortcode" style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[searchwiz show_button="true"]</code>
                        </td>
                        <td style="padding: 12px;">
                            <?php esc_html_e( 'Show search button next to input', 'searchwiz' ); ?>
                        </td>
                        <td style="padding: 12px;">
                            <button type="button" class="button button-small sw-copy-shortcode" data-shortcode='[searchwiz show_button="true"]'>
                                <?php esc_html_e( 'Copy', 'searchwiz' ); ?>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p style="margin-top: 20px; padding: 12px; background: #f0f6fc; border-left: 4px solid #0073aa;">
            <strong><?php esc_html_e( 'Theme Integration:', 'searchwiz' ); ?></strong>
            <?php esc_html_e( 'If theme integration is enabled in the Overview tab, SearchWiz automatically replaces your theme\'s default search. You only need shortcodes for custom placements.', 'searchwiz' ); ?>
        </p>
        <?php
    }

    /**
     * Custom CSS field - REMOVED per WordPress.org plugin guidelines.
     *
     * WordPress.org does not allow plugins to provide arbitrary custom CSS input fields.
     * Users should use WordPress's built-in Additional CSS feature in the Customizer instead.
     *
     * @since 1.0.0
     * @deprecated Removed for WordPress.org compliance
     */
    function custom_css() {
        // Custom CSS input removed per WordPress.org plugin guidelines.
        // Users should use the WordPress Customizer's Additional CSS feature instead.
        ?>
        <p class="description">
            <?php
            printf(
                /* translators: %s: Link to WordPress Customizer */
                esc_html__( 'To add custom CSS, please use %s in the WordPress Customizer.', 'searchwiz' ),
                '<a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Additional CSS', 'searchwiz' ) . '</a>'
            );
            ?>
        </p>
        <?php
    }

    /**
     * Displays search type field.
     */
    function search_type_field() {
        $settings = get_option( 'searchwiz_settings', array() );
        $search_type = isset( $settings['search_type'] ) ? $settings['search_type'] : 'OR';
        ?>
        <p><?php esc_html_e( 'Choose how SearchWiz matches multiple search terms:', 'searchwiz' ); ?></p>

        <p>
            <label style="display: block; margin-bottom: 5px;">
                <input type="radio" name="searchwiz_settings[search_type]" value="OR" <?php checked( $search_type, 'OR' ); ?> style="display: inline-block !important; width: auto !important; height: auto !important; position: relative !important; margin-right: 8px;" />
                <strong><?php esc_html_e( 'OR (Recommended)', 'searchwiz' ); ?></strong> -
                <?php esc_html_e( 'Find posts containing ANY of the search terms. Gives the broadest results.', 'searchwiz' ); ?>
            </label>
        </p>
        <p style="margin-left: 24px; color: #666; font-size: 12px;">
            <?php esc_html_e( 'Example: Searching for "bike helmet" will show posts with "bike" OR "helmet" OR both.', 'searchwiz' ); ?>
        </p>

        <p>
            <label style="display: block; margin-bottom: 5px;">
                <input type="radio" name="searchwiz_settings[search_type]" value="AND" <?php checked( $search_type, 'AND' ); ?> style="display: inline-block !important; width: auto !important; height: auto !important; position: relative !important; margin-right: 8px;" />
                <strong><?php esc_html_e( 'AND', 'searchwiz' ); ?></strong> -
                <?php esc_html_e( 'Find posts containing ALL of the search terms. More precise matching.', 'searchwiz' ); ?>
            </label>
        </p>
        <p style="margin-left: 24px; color: #666; font-size: 12px;">
            <?php esc_html_e( 'Example: Searching for "bike helmet" will only show posts that contain both "bike" AND "helmet".', 'searchwiz' ); ?>
        </p>

        <p class="description">
            <?php esc_html_e( 'OR search (default) provides more relevant results by requiring all search terms. Use AND search if you want broader results.', 'searchwiz' ); ?>
        </p>
        <?php
    }

    /**
     * Displays user comments indexing field.
     */
    function user_comments_field() {
        $settings = get_option( 'searchwiz_settings', array() );
        $index_comments = isset( $settings['index_user_comments'] ) ? $settings['index_user_comments'] : 0;
        ?>
        <p><?php esc_html_e( 'Control whether user-submitted comments are included in search results.', 'searchwiz' ); ?></p>

        <p>
            <label>
                <input type="checkbox" name="searchwiz_settings[index_user_comments]" value="1" <?php checked( $index_comments, 1 ); ?> />
                <?php esc_html_e( 'Index user comments in search', 'searchwiz' ); ?>
            </label>
        </p>

        <p class="description" style="color: #d63638;">
            <strong><?php esc_html_e( 'Security Note:', 'searchwiz' ); ?></strong>
            <?php esc_html_e( 'Guest users can post comments. Keep this OFF unless you moderate all comments before approval.', 'searchwiz' ); ?>
        </p>

        <p class="description">
            <?php esc_html_e( 'When enabled, approved comment content will be searchable. This is useful for community discussions but should be used with comment moderation.', 'searchwiz' ); ?>
        </p>
        <?php
    }

    /**
     * Displays stopwords field.
     */
    function stopwords() {
        ?>
        <p><?php esc_html_e( 'Stopwords are common words that will be ignored in searches to improve relevance.', 'searchwiz' ); ?></p>
        <p style="font-size: 12px; color: #666;">
            <strong><?php esc_html_e( 'Examples:', 'searchwiz' ); ?></strong>
            <?php esc_html_e( 'a, an, the, is, are, was, were, in, on, at', 'searchwiz' ); ?>
        </p>
        <?php
        $this->opt['stopwords'] = ( isset( $this->opt['stopwords'] ) ? $this->opt['stopwords'] : 'a, an, the, is, are, was, were, in, on, at' );
        ?>
		<div>
		<textarea class="searchwiz_search_stopwords" rows="4" id="stopwords" name="searchwiz_settings[stopwords]" placeholder="<?php esc_attr_e( 'a, an, the, is, are, was, were...', 'searchwiz' ); ?>"><?php
        echo esc_attr( $this->opt['stopwords'] );
        ?></textarea>
		<br /><label for="stopwords" style="font-size: 10px;"><?php
        esc_html_e( 'Separate multiple words with commas.', 'searchwiz' );
        ?></label>
		</div>
		<?php
    }

    /**
     * Displays synonyms field.
     */
    function synonyms() {
        ?>
        <p><?php esc_html_e( 'Add synonyms to help users find results with related terms.', 'searchwiz' ); ?></p>
        <p style="font-size: 12px; color: #666;">
            <strong><?php esc_html_e( 'How it works:', 'searchwiz' ); ?></strong>
            <?php esc_html_e( 'If you add "bike = bicycle, cycle", searching for "bike" will also find posts containing "bicycle" or "cycle".', 'searchwiz' ); ?>
        </p>
        <p style="font-size: 12px; color: #666;">
            <strong><?php esc_html_e( 'Format:', 'searchwiz' ); ?></strong>
            <code>key = synonym1, synonym2</code>
        </p>
        <?php
        // Default synonyms - 50 common e-commerce pairs
        $default_synonyms = 'buy = purchase, order, get
sell = vend, offer
cheap = inexpensive, affordable, budget, economical
expensive = costly, pricey, premium
fast = quick, rapid, speedy, swift
slow = sluggish, gradual
large = big, huge, giant, enormous
small = tiny, little, mini, compact
new = fresh, latest, recent, modern
old = vintage, classic, aged, antique
good = great, excellent, quality, superior
bad = poor, inferior, faulty, defective
hot = warm, heated
cold = cool, chilled, frozen
hard = firm, solid, rigid
soft = gentle, plush, cushioned
heavy = weighty, substantial
light = lightweight, airy
bright = luminous, vivid, brilliant
dark = dim, shadowy
high = tall, elevated, lofty
low = short, ground, base
thick = dense, chunky
thin = slim, slender, narrow
wide = broad, expansive
narrow = tight, confined
strong = powerful, robust, sturdy
weak = fragile, delicate
clean = pristine, spotless, pure
dirty = soiled, stained, grimy
full = complete, filled, packed
empty = vacant, hollow, bare
open = accessible, available, unlocked
closed = shut, sealed, locked
smooth = sleek, polished, even
rough = coarse, uneven, textured
sharp = pointed, keen, acute
dull = blunt, worn
safe = secure, protected, reliable
dangerous = risky, hazardous, unsafe
simple = easy, basic, straightforward
complex = complicated, intricate, advanced
rich = wealthy, affluent, prosperous
poor = needy, impoverished
happy = joyful, pleased, content
sad = unhappy, sorrowful, melancholy
young = youthful, junior
old = elderly, senior, mature
beautiful = pretty, attractive, lovely, gorgeous
ugly = unattractive, unsightly';
        $this->opt['synonyms'] = ( isset( $this->opt['synonyms'] ) ? $this->opt['synonyms'] : $default_synonyms );
        ?>
		<div>
		<textarea class="searchwiz_search_synonyms" rows="6" id="synonyms" name="searchwiz_settings[synonyms]" placeholder="<?php esc_attr_e( 'bike = bicycle, cycle&#10;car = automobile, vehicle&#10;phone = mobile, smartphone', 'searchwiz' ); ?>"><?php
        echo esc_attr( $this->opt['synonyms'] );
        ?></textarea>
		<br /><label for="synonyms" style="font-size: 10px;"><?php
        esc_html_e( 'Add each synonym pair on a new line.', 'searchwiz' );
        ?></label>
		</div>
		<?php
    }

    /**
     * Displays do not load plugin files field.
     */
    function plugin_files() {
        $content = __( 'Enable below options to disable loading of plugin CSS and JavaScript files.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $styles = array(
            'css' => __( 'Do not load plugin CSS files', 'searchwiz' ),
            'js'  => __( 'Do not load plugin JavaScript files', 'searchwiz' ),
        );
        ?> <div> <?php 
        foreach ( $styles as $key => $file ) {
            $check_value = ( isset( $this->opt['not_load_files'][$key] ) ? $this->opt['not_load_files'][$key] : 0 );
            $check_string = checked( $key, $check_value, false );
            if ( 'js' === $key ) {
                ?>
                            <br />
                        <?php 
            }
            ?>
			<br /><label for="not_load_files[<?php 
            echo esc_attr( $key );
            ?>]"><input class="not_load_files" type="checkbox" id="not_load_files[<?php 
            echo esc_attr( $key );
            ?>]" name="searchwiz_settings[not_load_files][<?php 
            echo esc_attr( $key );
            ?>]" value="<?php 
            echo esc_attr( $key );
            ?>" <?php 
            echo esc_attr( $check_string );
            ?>/>
			<span class="toggle-check-text"></span><?php 
            echo esc_html( $file );
            ?></label>
            <span class="not-load-wrapper">
			<?php 
            if ( 'css' === $key ) {
                ?>
				<br /><label for="not_load_files[<?php 
                echo esc_attr( $key );
                ?>]" style="font-size: 10px;"><?php 
                esc_html_e( 'If checked, you have to add following plugin file code into your child theme CSS file.', 'searchwiz' );
                ?></label>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo esc_url(plugins_url( '/public/css/searchwiz-search.css', SEARCHWIZ_PLUGIN_FILE ));
                ?>"><?php 
                echo esc_url(plugins_url( '/public/css/searchwiz-search.css', SEARCHWIZ_PLUGIN_FILE ));
                ?></a>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo esc_url(plugins_url( '/public/css/searchwiz-ajax-search.css', SEARCHWIZ_PLUGIN_FILE ));
                ?>"><?php 
                echo esc_url(plugins_url( '/public/css/searchwiz-ajax-search.css', SEARCHWIZ_PLUGIN_FILE ));
                ?></a>
				<br />
			<?php 
            } else {
                ?>
				<br /><label for="not_load_files[<?php 
                echo esc_attr( $key );
                ?>]" style="font-size: 10px;"><?php 
                esc_html_e( "If checked, you have to add following plugin files code into your child theme JavaScript file.", 'searchwiz' );
                ?></label>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo esc_url(plugins_url( '/public/js/searchwiz-search.js', SEARCHWIZ_PLUGIN_FILE ));
                ?>"><?php 
                echo esc_url(plugins_url( '/public/js/searchwiz-search.js', SEARCHWIZ_PLUGIN_FILE ));
                ?></a>
				<br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo esc_url(plugins_url( '/public/js/is-highlight.js', SEARCHWIZ_PLUGIN_FILE ));
                ?>"><?php 
                echo esc_url(plugins_url( '/public/js/is-highlight.js', SEARCHWIZ_PLUGIN_FILE ));
                ?></a>
                <br /><a style="font-size: 13px;" target="_blank" href="<?php 
                echo esc_url(plugins_url( '/public/js/searchwiz-ajax-search.js', SEARCHWIZ_PLUGIN_FILE ));
                ?>"><?php 
                echo esc_url(plugins_url( '/public/js/searchwiz-ajax-search.js', SEARCHWIZ_PLUGIN_FILE ));
                ?></a>
			<?php 
            }
            ?>
                </span>
		<?php 
        }
        ?>
		</div>
		<?php 
    }


    /**
     * Display settings section description.
     */
    function display_section_desc() {
        ?>
        <h4 class="panel-desc">
        <?php esc_html_e( 'Search Results Appearance', 'searchwiz' ); ?>
        </h4>
        <p><?php esc_html_e( 'Customize the appearance of search results. These settings control colors, typography, and layout.', 'searchwiz' ); ?></p>
        <?php
    }

    /**
     * Primary color field.
     */
    function primary_color_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#0073aa';

        // DEBUG: Log what we're loading
        if ( isset( $_GET['searchwiz_debug'] ) && $_GET['searchwiz_debug'] === '1' ) {
            error_log( '[SearchWiz DEBUG] Loading Primary Color field' );
            error_log( '[SearchWiz DEBUG] searchwiz_display_settings from DB: ' . print_r( $settings, true ) );
            error_log( '[SearchWiz DEBUG] primary_color value: ' . $color );
        }
        ?>
        <input style="width: 80px;" class="is-colorpicker" size="5" type="text" id="is-primary-color" name="searchwiz_display_settings[primary_color]" value="<?php echo esc_attr( $color ); ?>" data-default-color="#0073aa" />
        <br /><i><?php esc_html_e( 'Color for links, buttons, and accents in search results.', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Title font size field.
     */
    function title_font_size_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $size = isset( $settings['title_font_size'] ) ? $settings['title_font_size'] : 16;
        ?>
        <input type="number" min="12" max="32" step="1" id="is-title-font-size" name="searchwiz_display_settings[title_font_size]" value="<?php echo esc_attr( $size ); ?>" style="width: 80px;" /> px
        <br /><i><?php esc_html_e( 'Font size for result titles (12-32 pixels).', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Show thumbnails field.
     */
    function show_thumbnails_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $checked = isset( $settings['show_thumbnails'] ) ? $settings['show_thumbnails'] : 1;
        ?>
        <label for="is-show-thumbnails">
            <input type="checkbox" id="is-show-thumbnails" name="searchwiz_display_settings[show_thumbnails]" value="1" <?php checked( 1, $checked, true ); ?> />
            <span class="toggle-check-text"></span>
            <?php esc_html_e( 'Display product/post thumbnail images in search results.', 'searchwiz' ); ?>
        </label>
        <?php
    }

    /**
     * Show excerpts field.
     */
    function show_excerpts_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $checked = isset( $settings['show_excerpts'] ) ? $settings['show_excerpts'] : 1;
        ?>
        <label for="is-show-excerpts">
            <input type="checkbox" id="is-show-excerpts" name="searchwiz_display_settings[show_excerpts]" value="1" <?php checked( 1, $checked, true ); ?> />
            <span class="toggle-check-text"></span>
            <?php esc_html_e( 'Display excerpt text below result titles.', 'searchwiz' ); ?>
        </label>
        <?php
    }

    /**
     * Excerpt length field.
     */
    function excerpt_length_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $length = isset( $settings['excerpt_length'] ) ? $settings['excerpt_length'] : 20;
        ?>
        <input type="number" min="1" max="500" step="1" id="is-excerpt-length" name="searchwiz_display_settings[excerpt_length]" value="<?php echo esc_attr( $length ); ?>" style="width: 80px;" /> words
        <br /><i><?php esc_html_e( 'Number of words to display in excerpts (1-500). Use small numbers like 1-5 to test.', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Card spacing field.
     */
    function card_spacing_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $spacing = isset( $settings['card_spacing'] ) ? $settings['card_spacing'] : 10;
        ?>
        <input type="number" min="0" max="30" step="2" id="is-card-spacing" name="searchwiz_display_settings[card_spacing]" value="<?php echo esc_attr( $spacing ); ?>" style="width: 80px;" /> px
        <br /><i><?php esc_html_e( 'Padding around each search result card (0-30 pixels).', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Border radius field.
     */
    function border_radius_field() {
        $settings = get_option( 'searchwiz_display_settings', array() );
        $radius = isset( $settings['border_radius'] ) ? $settings['border_radius'] : 0;
        ?>
        <input type="number" min="0" max="20" step="1" id="is-border-radius" name="searchwiz_display_settings[border_radius]" value="<?php echo esc_attr( $radius ); ?>" style="width: 80px;" /> px
        <br /><i><?php esc_html_e( 'Rounded corners for search result cards (0-20 pixels). 0 = square corners.', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Validate display settings.
     */
    function is_validate_display_settings( $input ) {
        // IMPORTANT: Start with existing values to preserve settings not in this update
        // This is critical for auto-save where unchecked checkboxes aren't sent
        $existing = get_option( 'searchwiz_display_settings', array() );
        $output = is_array( $existing ) ? $existing : array();

        // Validate primary color
        if ( isset( $input['primary_color'] ) ) {
            $color = sanitize_text_field( $input['primary_color'] );
            // Basic hex color validation
            if ( preg_match( '/^#[a-f0-9]{6}$/i', $color ) ) {
                $output['primary_color'] = $color;
            } else {
                $output['primary_color'] = '#0073aa'; // Default
            }
        }

        // Validate title font size
        if ( isset( $input['title_font_size'] ) ) {
            $size = absint( $input['title_font_size'] );
            if ( $size >= 12 && $size <= 32 ) {
                $output['title_font_size'] = $size;
            } else {
                $output['title_font_size'] = 16; // Default
            }
        }

        // Validate checkboxes
        $output['show_thumbnails'] = isset( $input['show_thumbnails'] ) ? 1 : 0;
        $output['show_excerpts'] = isset( $input['show_excerpts'] ) ? 1 : 0;

        // Validate excerpt length
        if ( isset( $input['excerpt_length'] ) ) {
            $length = absint( $input['excerpt_length'] );
            if ( $length >= 1 && $length <= 500 ) {
                $output['excerpt_length'] = $length;
            } else {
                $output['excerpt_length'] = 20; // Default
            }
        }

        // Validate card spacing
        if ( isset( $input['card_spacing'] ) ) {
            $spacing = absint( $input['card_spacing'] );
            if ( $spacing >= 0 && $spacing <= 30 ) {
                $output['card_spacing'] = $spacing;
            } else {
                $output['card_spacing'] = 10; // Default
            }
        }

        // Validate border radius
        if ( isset( $input['border_radius'] ) ) {
            $radius = absint( $input['border_radius'] );
            if ( $radius >= 0 && $radius <= 20 ) {
                $output['border_radius'] = $radius;
            } else {
                $output['border_radius'] = 0; // Default
            }
        }

        return $output;
    }

    /**
     * Search Box Appearance section description
     */
    function searchbox_appearance_section_desc() {
        ?>
        <h2><?php esc_html_e( 'Search Box Appearance', 'searchwiz' ); ?></h2>
        <p><?php esc_html_e( 'Customize border colors for search input boxes. These colors serve as global defaults and can be overridden using shortcode parameters.', 'searchwiz' ); ?></p>
        <?php
    }

    /**
     * Search box border color field
     */
    function searchbox_border_color_field() {
        $settings = get_option( 'searchwiz_searchbox_settings', array() );
        $color = isset( $settings['border_color'] ) ? $settings['border_color'] : '#0073aa';

        // DEBUG: Log what we're loading
        if ( isset( $_GET['searchwiz_debug'] ) && $_GET['searchwiz_debug'] === '1' ) {
            error_log( '[SearchWiz DEBUG] Loading Search Box Border Color' );
            error_log( '[SearchWiz DEBUG] searchwiz_searchbox_settings from DB: ' . print_r( $settings, true ) );
            error_log( '[SearchWiz DEBUG] border_color value: ' . $color );
        }
        ?>
        <input style="width: 80px;" class="is-colorpicker" size="5" type="text" id="searchwiz-searchbox-border-color" name="searchwiz_searchbox_settings[border_color]" value="<?php echo esc_attr( $color ); ?>" data-default-color="#0073aa" />
        <br /><i><?php esc_html_e( 'Border color for search input boxes. Can be overridden with shortcode parameter: bordercolor="#your-color"', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Search box focus color field
     */
    function searchbox_focus_color_field() {
        $settings = get_option( 'searchwiz_searchbox_settings', array() );
        $color = isset( $settings['focus_color'] ) ? $settings['focus_color'] : '#005177';
        ?>
        <input style="width: 80px;" class="is-colorpicker" size="5" type="text" id="searchwiz-searchbox-focus-color" name="searchwiz_searchbox_settings[focus_color]" value="<?php echo esc_attr( $color ); ?>" data-default-color="#005177" />
        <br /><i><?php esc_html_e( 'Border color when search box is focused (clicked). Typically darker than border color.', 'searchwiz' ); ?></i>
        <?php
    }

    /**
     * Validate search box settings
     */
    function is_validate_searchbox_settings( $input ) {
        // IMPORTANT: Start with existing values to preserve settings not in this update
        $existing = get_option( 'searchwiz_searchbox_settings', array() );
        $output = is_array( $existing ) ? $existing : array();

        // Validate border color
        if ( isset( $input['border_color'] ) ) {
            $color = sanitize_text_field( $input['border_color'] );
            if ( preg_match( '/^#[a-f0-9]{6}$/i', $color ) ) {
                $output['border_color'] = $color;
            } else {
                $output['border_color'] = '#0073aa'; // Default
            }
        }

        // Validate focus color
        if ( isset( $input['focus_color'] ) ) {
            $color = sanitize_text_field( $input['focus_color'] );
            if ( preg_match( '/^#[a-f0-9]{6}$/i', $color ) ) {
                $output['focus_color'] = $color;
            } else {
                $output['focus_color'] = '#005177'; // Default
            }
        }

        return $output;
    }

}
