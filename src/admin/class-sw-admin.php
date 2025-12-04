<?php
/**
 * The class defines all functionality for the dashboard of the plugin.
 *
 * @package SW
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Admin {
    /**
     * Stores plugin options.
     */
    public $opt;

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
        if ( !self::$_instance instanceof self ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Loads plugin javascript and stylesheet files in the admin area.
     */
    function admin_enqueue_scripts( $hook_suffix ) {
        if ( false === strpos( $hook_suffix, 'searchwiz-search' ) ) {
            return;
        }
        // Css rules for Color Picker
        wp_enqueue_style( 'wp-color-picker' );
        $min = ( defined( 'SW_DEBUG' ) && SW_DEBUG ? '' : '.min' );
        wp_enqueue_style(
            'searchwiz-admin-styles',
            plugins_url( '/admin/css/searchwiz-admin' . $min . '.css', SEARCHWIZ_PLUGIN_FILE ),
            array(),
            SEARCHWIZ_VERSION
        );

        // Add admin notice styles inline
        $notice_css = '
        /* ADMIN NOTICES */
        .is-notice { margin:20px 0; padding:0; overflow:hidden; background:#FFF;}
        .is-notice br {clear: none;}
        .is-notice-dismiss { display:block; float:right; color:#999; line-height:1; margin:0 0 0 15px; text-decoration:none; }
        .is-notice-image { float:left; margin:10px; width:90px; height:90px; background:url(' . esc_url( plugins_url( 'assets/logo.png', __FILE__ ) ) . ') no-repeat center; background-size:cover; }
        .is-notice-body { padding:15px; background:#fff; }
        .is-notice-content { margin:0 0 10px; padding:0; }
        .is-notice-links a.button { margin-right: 10px;text-decoration: none;background: #e7f2f7 !important;color: #30667b !important;box-shadow: none;text-shadow: none;}
        .is-notice-links a.btn-highlight {background: #0071a1 !important;color: #FFF !important;}
        /* UPGRADE BOX */
        .sw-upgrade-cta:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2) !important; }
        @media (max-width: 768px) { .searchwiz-upgrade-box div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; } }
        /* UPGRADE TAB */
        .searchwiz-upgrade-tab { max-width: 1000px; margin: 20px 0; }
        .searchwiz-upgrade-tab h2 { font-size: 24px; margin-bottom: 20px; color: #23282d; }
        .searchwiz-upgrade-tab .searchwiz-upgrade-intro { background: #fff; border-left: 4px solid #0073aa; padding: 20px; margin-bottom: 30px; }
        .searchwiz-upgrade-tab .searchwiz-upgrade-intro p { font-size: 16px; line-height: 1.6; margin: 10px 0; }
        .searchwiz-upgrade-tab .searchwiz-feature { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 25px; margin-bottom: 25px; }
        .searchwiz-upgrade-tab .searchwiz-feature h3 { font-size: 20px; margin-top: 0; margin-bottom: 15px; color: #0073aa; }
        .searchwiz-upgrade-tab .searchwiz-feature p { font-size: 14px; line-height: 1.6; margin-bottom: 15px; color: #555; }
        .searchwiz-upgrade-tab .searchwiz-feature-image { background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; padding: 40px; text-align: center; min-height: 300px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 14px; }
        .searchwiz-upgrade-tab .searchwiz-cta { background: #0073aa; color: #fff; padding: 12px 24px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: 600; margin-top: 10px; }
        .searchwiz-upgrade-tab .searchwiz-cta:hover { background: #005a87; color: #fff; }
        .searchwiz-upgrade-tab .searchwiz-badge { background: #ff6b6b; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-left: 10px; }
        ';
        wp_add_inline_style( 'searchwiz-admin-styles', $notice_css );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_register_script(
            'searchwiz-admin-scripts',
            plugins_url( '/admin/js/searchwiz-admin' . $min . '.js', SEARCHWIZ_PLUGIN_FILE ),
            array(
                'jquery',
                'jquery-ui-tabs',
                'jquery-ui-accordion',
                'wp-color-picker'
            ),
            SEARCHWIZ_VERSION,
            true
        );
        $args = array(
            'saveAlert' => __( "The changes you made will be lost if you navigate away from this page.", 'searchwiz' ),
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'autosaveNonce' => wp_create_nonce( 'searchwiz_autosave_nonce' ),
            'themeIntegrationNonce' => wp_create_nonce( 'searchwiz_theme_integration_nonce' ),
            'defaultSearchNonce' => wp_create_nonce( 'searchwiz_default_search_nonce' ),
            'trackUpgradeNonce' => wp_create_nonce( 'searchwiz_track_upgrade' ),
            'i18n' => array(
                'copied' => __( 'Copied!', 'searchwiz' ),
            ),
        );

        // Add debug mode to JavaScript if URL parameter is set (?searchwiz_debug=1)
        $debug_param = isset( $_GET['searchwiz_debug'] ) ? sanitize_text_field( wp_unslash( $_GET['searchwiz_debug'] ) ) : '';
        if ( '1' === $debug_param ) {
            wp_add_inline_script( 'searchwiz-admin-scripts', 'var searchwiz_debug = "1";', 'before' );
        }
        if ( $this->custom_admin_pointers_check() ) {
            wp_enqueue_script( 'wp-pointer' );
            wp_enqueue_style( 'wp-pointer' );
            // Add pointer script inline
            $pointer_script = $this->get_admin_pointers_script();
            if ( $pointer_script ) {
                wp_add_inline_script( 'wp-pointer', $pointer_script );
            }
        }
        wp_localize_script( 'searchwiz-admin-scripts', 'searchwiz_search', $args );
        wp_enqueue_script( 'searchwiz-admin-scripts' );
    }

    function custom_admin_pointers_check() {
        $admin_pointers = $this->custom_admin_pointers();
        foreach ( $admin_pointers as $pointer => $array ) {
            if ( $array['active'] ) {
                return true;
            }
        }
    }

    /**
     * Generates JavaScript for admin pointers.
     *
     * @return string JavaScript code for pointers.
     */
    function get_admin_pointers_script() {
        $admin_pointers = $this->custom_admin_pointers();
        $script_parts = array();

        foreach ( $admin_pointers as $pointer => $array ) {
            if ( $array['active'] ) {
                $content = wp_kses( $array['content'], array(
                    'h3' => array(),
                    'p'  => array(),
                ) );
                $script_parts[] = sprintf(
                    '$( %s ).pointer( {
                        content: %s,
                        position: {
                            edge: %s,
                            align: %s
                        },
                        close: function() {
                            $.post( ajaxurl, {
                                pointer: %s,
                                action: "dismiss-wp-pointer"
                            } );
                        }
                    } ).pointer( "open" );',
                    wp_json_encode( $array['anchor_id'] ),
                    wp_json_encode( $content ),
                    wp_json_encode( $array['edge'] ),
                    wp_json_encode( $array['align'] ),
                    wp_json_encode( $pointer )
                );
            }
        }

        if ( empty( $script_parts ) ) {
            return '';
        }

        return '( function($) {
            $( window ).on( "load", function() {
                ' . implode( "\n", $script_parts ) . '
            } );
        } )(jQuery);';
    }

    function custom_admin_pointers() {
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $version = '1_0';
        // replace all periods in 1.0 with an underscore
        $prefix = 'is_admin_pointers_' . $version . '_';
        $new_pointer_content = '<h3>' . __( 'Edit Search Form', 'searchwiz' ) . '</h3>';
        $new_pointer_content .= '<p>' . __( 'Click on the search form title to edit it.', 'searchwiz' ) . '</p>';
        return array(
            $prefix . 'is_pointers' => array(
                'content'   => $new_pointer_content,
                'anchor_id' => '#the-list tr:first-child a.row-title',
                'edge'      => 'left',
                'align'     => 'left',
                'active'    => !in_array( $prefix . 'is_pointers', $dismissed ),
            ),
        );
    }

    /**
     * Adds a link to the settings page in the plugins list.
     *
     * @param array  $links array of links for the plugins, adapted when the current plugin is found.
     * @param string $file  the filename for the current plugin, which the filter loops through.
     *
     * @return array $links
     */
    function plugin_action_links( $links, $file ) {
        if ( SEARCHWIZ_PLUGIN_BASE === $file ) {
            $mylinks = array('<a href="' . esc_url( menu_page_url( 'searchwiz-search', false ) ) . '">' . esc_html__( 'Settings', 'searchwiz' ) . '</a>');
            $links = array_merge( $mylinks, $links );
        }
        return $links;
    }

    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta.
     * @param mixed $file  Plugin Base file.
     *
     * @return array
     */
    function plugin_row_meta( $links, $file ) {
        if ( SEARCHWIZ_PLUGIN_BASE === $file ) {
            $row_meta = array(
                'docs'    => '<a href="https://searchwiz.ai/documentation/" aria-label="' . esc_attr__( 'View SearchWiz documentation', 'searchwiz' ) . '">' . esc_html__( 'Docs', 'searchwiz' ) . '</a>',
                'support' => '<a href="https://searchwiz.ai/support" aria-label="' . esc_attr__( 'Visit plugin customer support', 'searchwiz' ) . '">' . esc_html__( 'Support', 'searchwiz' ) . '</a>',
            );
            return array_merge( $links, $row_meta );
        }
        return (array) $links;
    }

    /**
     * Change the admin footer text on SearchWiz admin pages.
     */
    public function admin_footer_text( $footer_text ) {
        $screen = get_current_screen();
        $is_ivory = strpos( $screen->id, 'searchwiz-search' );
        // Check to make sure we're on a SearchWiz admin page.
        if ( FALSE !== $is_ivory ) {
   			// translators: %1: Product Name %2 Rating
            $footer_text = sprintf( __( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'searchwiz' ), sprintf( '<strong>%s</strong>', esc_html__( 'SearchWiz', 'searchwiz' ) ), '<a href="https://wordpress.org/support/plugin/searchwiz/reviews?rate=5#new-post" target="_blank" class="is-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'searchwiz' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
        }
        return $footer_text;
    }

    /**
     * Displays plugin configuration notice in admin area.
     */
    function all_admin_notices() {
        $isnetworkactive = is_multisite() && array_key_exists( plugin_basename( SEARCHWIZ_PLUGIN_FILE ), (array) get_site_option( 'active_sitewide_plugins' ) );
        $hascaps = ( $isnetworkactive ? is_network_admin() && current_user_can( 'manage_network_plugins' ) : current_user_can( 'manage_options' ) );
        if ( $hascaps ) {
            $screen = get_current_screen();
            $is_ivory = strpos( $screen->id, 'searchwiz-search' );
            $display_review = true;
            if ( empty( $this->opt ) ) {
                $this->opt = get_option( 'searchwiz_notices', array() );
            }
            // Don't display if dismissed
            if ( isset( $this->opt['is_notices']['review'] ) && $this->opt['is_notices']['review'] ) {
                $display_review = false;
            }
            // Don't display on secondary screens, don't be too nagging
            $get_action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
            if ( 'edit' === $get_action || $screen->action == 'add' || $screen->base == 'plugins' || $screen->base == 'widgets' ) {
                $display_review = false;
            }
            $date = get_option( 'searchwiz_install', false );
            if ( $date && $display_review ) {
                if ( strtotime( '-7 days' ) >= strtotime( $date ) ) {
                    global $current_user;
                    // Generate dismiss URL with nonce
                    $dismiss_url = wp_nonce_url( add_query_arg( 'is_dismiss', 'notice_review' ), 'searchwiz_dismiss_notice' );
                    echo '<div class="is-notice notice"><div class="is-notice-image"></div><div class="is-notice-body">';
                    echo '<a class="is-notice-dismiss" href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss', 'searchwiz' ) . '</a>';
                    echo '<div class="is-notice-content">';
                    // translators: %s: User name
                    printf( esc_html__( "Hey %s, it's the team from SearchWiz. You have used this plugin for some time now, and I hope you like it!", 'searchwiz' ), '<strong>' . esc_html( $current_user->display_name ) . '</strong>', '<strong>SearchWiz</strong>' );
                    ?><br/><br/><?php
                    // translators: %s: Product name
                    printf( esc_html__( "We have spent countless hours developing %s, and it would mean a lot to us if you support it with a quick review on WordPress.org.", 'searchwiz' ), '<strong><a target="_blank" href="https://wordpress.org/support/plugin/searchwiz/reviews/#new-post">', '</a></strong>' );
                    echo '</div>';
                    echo '<div class="is-notice-links">';
                    echo '<a href="' . esc_url( 'https://wordpress.org/support/plugin/searchwiz/reviews/#new-post' ) . '" class="button button-primary btn-highlight" target="_blank" >' . esc_html__( 'Review SearchWiz', 'searchwiz' ) . '</a>';
                    echo '<a href="' . esc_url( $dismiss_url ) . '" class="button button-primary">' . esc_html__( 'No, thanks', 'searchwiz' ) . '</a>';
                    echo '</div></div></div>';
                }
            }
        }
    }

    /**
     * Displays posts in the admin plugin options list using AJAX.
     */
    function display_posts() {
        $posts = get_posts( array(
            'post_type'      => ( isset( $_REQUEST['post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) : 'post' ),
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
        if ( !empty( $posts ) ) {
            $request_post_id = isset( $_REQUEST['post_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_id'] ) ) : '';
            $meta = ( $request_post_id && is_numeric( $request_post_id ) ? get_post_meta( absint( $request_post_id ) ) : '' );
            $request_inc_exc = isset( $_REQUEST['inc_exc'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['inc_exc'] ) ) : '';
            if ( $request_inc_exc ) {
                if ( 'includes' === $request_inc_exc && isset( $meta['_is_includes'] ) ) {
                    $meta = maybe_unserialize( $meta['_is_includes'][0] );
                } else {
                    if ( 'excludes' === $request_inc_exc && isset( $meta['_is_excludes'] ) ) {
                        $meta = maybe_unserialize( $meta['_is_excludes'][0] );
                    }
                }
            }
            foreach ( $posts as $post2 ) {
                $checked = '';
                if ( $request_inc_exc ) {
                    if ( 'includes' === $request_inc_exc ) {
                        $checked = ( isset( $meta['post__in'] ) && in_array( $post2->ID, $meta['post__in'] ) ? $post2->ID : 0 );
                    } else {
                        if ( 'excludes' === $request_inc_exc ) {
                            $checked = ( isset( $meta['post__not_in'] ) && in_array( $post2->ID, $meta['post__not_in'] ) ? $post2->ID : 0 );
                        }
                    }
                }
                $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? $post2->post_title : $post2->post_name );
                echo '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . esc_html( $post_title ) . '</option>';
            }
        } else {
            esc_html_e( 'No posts found', 'searchwiz' );
        }
        die;
    }

    /**
     * Adds scripts in the admin footer
     */
    function admin_footer() {
        // Notice styles now handled via wp_add_inline_style() in admin_enqueue_scripts()
    }

    /**
     * Registers plugin settings.
     */
    function admin_init() {
        // Handle dismiss action with nonce verification
        $is_dismiss_param = isset( $_GET['is_dismiss'] ) ? sanitize_text_field( wp_unslash( $_GET['is_dismiss'] ) ) : '';
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! empty( $is_dismiss_param ) && wp_verify_nonce( $nonce, 'searchwiz_dismiss_notice' ) ) {
            if ( empty( $this->opt ) ) {
                $this->opt = get_option( 'searchwiz_notices', array() );
            }
            $is_notices = $this->opt;
            if ( 'notice_review' === $is_dismiss_param ) {
                $is_notices['is_notices']['review'] = 1;
            }
            update_option( 'searchwiz_notices', $is_notices );
            wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'is_dismiss', '_wpnonce' ) ) ) );
            exit;
        }
        if ( empty( $GLOBALS['pagenow'] ) || 'plugins.php' != $GLOBALS['pagenow'] ) {
            if ( ! get_option( 'searchwiz_install', false ) ) {
                update_option( 'searchwiz_install', gmdate( 'Y-m-d' ) );
            }
            if ( !empty( $GLOBALS['pagenow'] ) && ('admin.php' === $GLOBALS['pagenow'] || 'options.php' === $GLOBALS['pagenow']) ) {
                $settings_fields = new SearchWiz_Settings_Fields();
                $settings_fields->register_settings_fields();
            }
            /* Creates default search form */
            $search_form = get_page_by_path( 'default-search-form', OBJECT, SearchWiz_Search_Form::post_type );
            if ( NULL == $search_form ) {
                $args['id'] = -1;
                $args['title'] = 'Default Search Form';
                $args['_is_locale'] = 'en_US';
                $args['_is_includes'] = '';
                $args['_is_excludes'] = '';
                $args['_is_settings'] = '';
                $this->save_form( $args );
            }
        }
    }

    /**
     * Maps custom capabilities.
     */
    function map_meta_cap(
        $caps,
        $cap,
        $user_id,
        $args
    ) {
        $meta_caps = array(
            'is_edit_search_form'   => SEARCHWIZ_ADMIN_READ_WRITE_CAPABILITY,
            'is_edit_search_forms'  => SEARCHWIZ_ADMIN_READ_WRITE_CAPABILITY,
            'is_read_search_forms'  => SEARCHWIZ_ADMIN_READ_CAPABILITY,
            'is_delete_search_form' => SEARCHWIZ_ADMIN_READ_WRITE_CAPABILITY,
        );
        $meta_caps = apply_filters( 'searchwiz_map_meta_cap', $meta_caps );
        $caps = array_diff( $caps, array_keys( $meta_caps ) );
        if ( isset( $meta_caps[$cap] ) ) {
            $caps[] = $meta_caps[$cap];
        }
        return $caps;
    }

    /**
     * Displays admin messages on updating search form
     */
    function admin_updated_message() {
        $request_message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '';
        if ( empty( $request_message ) ) {
            return;
        }
        if ( 'created' == $request_message ) {
            $updated_message = __( "Search form created.", 'searchwiz' );
        } elseif ( 'saved' == $request_message ) {
            $updated_message = __( "Search form saved.", 'searchwiz' );
        } elseif ( 'deleted' == $request_message ) {
            $updated_message = __( "Search form deleted.", 'searchwiz' );
        } elseif ( 'reset' == $request_message ) {
            $updated_message = __( "Search form reset.", 'searchwiz' );
        } elseif ( 'index-reset' == $request_message ) {
            $updated_message = __( "Index settings reset.", 'searchwiz' );
        }
        if ( !empty( $updated_message ) ) {
            echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
            return;
        }
        if ( 'failed' == $request_message ) {
            $updated_message = __( "There was an error saving the search form.", 'searchwiz' );
            echo sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
            return;
        }
        if ( 'invalid' == $request_message ) {
            $updated_message = __( "Validation error occurred.", 'searchwiz' );
            $includes = __( "Includes", 'searchwiz' );
            $excludes = __( "Excludes", 'searchwiz' );
            $request_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '';
            if ( $request_tab ) {
                $url = menu_page_url( 'searchwiz-search', false );
                $request_post = isset( $_REQUEST['post'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post'] ) ) : '';
                if ( $request_post && is_numeric( $request_post ) ) {
                    $url .= '&post=' . absint( $request_post ) . '&action=edit';
                }
                if ( 'excludes' == $request_tab ) {
                    $includes = '<a href="' . esc_url( $url ) . '&tab=includes">' . __( "Includes", 'searchwiz' ) . '</a>';
                } else {
                    if ( 'includes' == $request_tab ) {
                        $excludes = '<a href="' . esc_url( $url ) . '&tab=excludes">' . __( "Excludes", 'searchwiz' ) . '</a>';
                    }
                }
            }
            $temp_mes = ( isset( $_REQUEST['data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['data'] ) ) : '' );
            $updated_message2 = sprintf(
                // translators: %1: Data, %2: Includes, %3: Excludes 
                __( "Please make sure you have not selected similar %1\$s fields in the search form %2\$s and %3\$s sections.", 'searchwiz' ),
                $temp_mes,
                $includes,
                $excludes
            );
            echo sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p><p>%s</p></div>', esc_html( $updated_message ), esc_html($updated_message2) );
            return;
        }
    }

    /**
     * Registers plugin admin menu item.
     */
    function admin_menu() {
        // Main menu - parent slug for submenus
        add_menu_page(
            __( 'SearchWiz', 'searchwiz' ),
            __( 'SearchWiz', 'searchwiz' ),
            'manage_options',
            'searchwiz-search', // Keep as parent slug
            '__return_null', // No callback - will be overridden by first submenu
            'dashicons-search',
            '35.6282'
        );
        $addnew = '';
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        if ( 'searchwiz-search-new' === $current_page ) {
            $addnew = add_submenu_page(
                'searchwiz-search',
                __( 'Add New Search Form', 'searchwiz' ),
                __( 'Add New', 'searchwiz' ),
                'manage_options',
                'searchwiz-search-new',
                array($this, 'new_search_form_page')
            );
        } else {
            $addnew = add_submenu_page(
                '',
                __( 'Add New Search Form', 'searchwiz' ),
                __( 'Add New', 'searchwiz' ),
                'manage_options',
                'searchwiz-search-new',
                array($this, 'new_search_form_page')
            );
        }
        add_action( 'load-' . $addnew, array($this, 'load_admin_search_form') );

        // Front-end submenu - uses same slug as parent to replace it
        $frontend_settings = add_submenu_page(
            'searchwiz-search',
            __( 'Front-End', 'searchwiz' ),
            __( 'Front-End', 'searchwiz' ),
            'manage_options',
            'searchwiz-search', // Same as parent - will replace parent menu item
            array($this, 'settings_page')
        );
        add_action( 'load-' . $frontend_settings, array($this, 'is_settings_add_help_tab') );

        // Back-end submenu
        $backend_settings = add_submenu_page(
            'searchwiz-search',
            __( 'Back-End', 'searchwiz' ),
            __( 'Back-End', 'searchwiz' ),
            'manage_options',
            'searchwiz-search-backend',
            array($this, 'settings_page')
        );
        add_action( 'load-' . $backend_settings, array($this, 'is_settings_add_help_tab') );

        // Legacy redirects for old URLs
        // Old "Menu Search" tab URL redirect
        $page_param = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        if ( 'searchwiz-search-settings' === $page_param ) {
            $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
            if ( 'menu-search' === $tab ) {
                // Redirect to front-end settings with menu-search tab
                wp_safe_redirect( admin_url( 'admin.php?page=searchwiz-search-frontend&tab=menu-search' ) );
                exit;
            } else {
                // Default old settings page redirects to backend
                wp_safe_redirect( admin_url( 'admin.php?page=searchwiz-search-backend' ) );
                exit;
            }
        }

        // Fix settings save redirect for new page slugs
        add_action( 'admin_init', array($this, 'fix_settings_redirect'), 1 );
    }

    /**
     * Fix settings form redirect after save for new admin page slugs
     * WordPress options.php checks the referrer and redirects back to it
     */
    function fix_settings_redirect() {
        // Only run when processing settings form submission
        if ( !empty( $GLOBALS['pagenow'] ) && 'options.php' === $GLOBALS['pagenow'] ) {
            // Check if this is a searchwiz settings submission
            $option_page = isset( $_POST['option_page'] ) ? sanitize_text_field( wp_unslash( $_POST['option_page'] ) ) : '';
            if ( 'searchwiz_search' === $option_page ) {
                // Override the referrer if it's pointing to old URL
                if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
                    $referer = sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) );
                    // If referrer contains old settings page, update it
                    if ( strpos( $referer, 'page=searchwiz-search-settings' ) !== false ) {
                        // Redirect to backend by default
                        $_REQUEST['_wp_http_referer'] = admin_url( 'admin.php?page=searchwiz-search-backend' );
                    }
                }
            }
        }
    }

    /**
     * Adds help tab to settings page screen.
     */
    function is_settings_add_help_tab() {
        $current_screen = get_current_screen();
        $help_tabs = new SearchWiz_Help($current_screen);
        $help_tabs->set_help_tabs( 'settings' );
    }

    /**
     * Renders the search forms page for this plugin.
     */
    function search_forms_page() {
        /* Edits search form */
        if ( $post = SearchWiz_Search_Form::get_current() ) {
            $post_id = ( $post->initial() ? -1 : $post->id() );
            include_once 'partials/search-form.php';
            return;
        }
        $list_table = new SearchWiz_List_Table();
        $list_table->prepare_items();
        ?>
	<div class="wrap">

		<h1 class="wp-heading-inline">
			<?php 
        echo esc_html( __( 'Search Forms', 'searchwiz' ) );
        ?>
		</h1>

		<?php 
        if ( current_user_can( 'is_edit_search_forms' ) ) {
            echo sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>', esc_url( menu_page_url( 'searchwiz-search-new', false ) ), esc_html( __( 'Add New Search Form', 'searchwiz' ) ) );
        }
        if ( !empty( $_REQUEST['s'] ) ) {
            $is_search_input = sanitize_text_field( $_REQUEST['s'] );
            // translators: %s : Search Input string
            echo sprintf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'searchwiz' ) . '</span>', esc_html( $is_search_input ) );
        }
        ?>

		<hr class="wp-header-end" />

		<?php 
        do_action( 'searchwiz_admin_notices' );
        ?>

		<form method="get" action="">
			<input type="hidden" name="page" value="<?php
        echo isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : '';
        ?>" />
			<?php 
        $list_table->search_box( __( 'Find Search Forms', 'searchwiz' ), 'is-search' );
        ?>
			<?php 
        $list_table->display();
        ?>
		</form>

	</div>
	<?php 
    }

    /**
     * Renders the add new search form page for this plugin.
     */
    function new_search_form_page() {
        $post = SearchWiz_Search_Form::get_current();
        if ( !$post ) {
            $post = SearchWiz_Search_Form::get_template();
        }
        $post_id = -1;
        include_once 'partials/search-form.php';
    }

    /**
     * Renders the settings page for this plugin.
     */
    function settings_page() {
        include_once 'partials/settings-form.php';
    }

    /**
     * Performs various search forms operations.
     */
    function load_admin_search_form() {
        global $plugin_page;
        $action = ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ? sanitize_text_field( $_REQUEST['action'] ) : false );
        if ( 'save' == $action ) {
            $id = ( isset( $_POST['post_ID'] ) && is_numeric( $_POST['post_ID'] ) ? sanitize_key( $_POST['post_ID'] ) : '-1' );
            check_admin_referer( 'is-save-search-form_' . $id );
            if ( !current_user_can( 'is_edit_search_form', $id ) ) {
                wp_die( esc_html__( 'You are not allowed to edit this item.', 'searchwiz' ) );
            }
            $args = $this->sanitize_settings( $_REQUEST );
            $args['id'] = $id;
            $args['title'] = ( isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : null );
            $args['title'] = ( null != $args['title'] && 'default search form' == strtolower( $args['title'] ) ? $args['title'] . ' ( Duplicate )' : $args['title'] );
            $args['_is_locale'] = ( isset( $_POST['is_locale'] ) ? sanitize_text_field( $_POST['is_locale'] ) : null );
            $args['_is_includes'] = ( isset( $_POST['_is_includes'] ) && is_array( $_POST['_is_includes'] ) ? $this->sanitize_includes( $_POST['_is_includes'] ) : '' );
            $args['_is_excludes'] = ( isset( $_POST['_is_excludes'] ) && is_array( $_POST['_is_excludes'] ) ? $this->sanitize_excludes( $_POST['_is_excludes'] ) : '' );
            if ( isset( $_POST['_is_ajax'] ) && is_array( $_POST['_is_ajax'] ) ) {
                $args['_is_ajax'] = $this->sanitize_settings( $_POST['_is_ajax'], '', 'nothing_found_text' );
                $args['_is_ajax']['nothing_found_text'] = wp_filter_post_kses( $_POST['_is_ajax']['nothing_found_text'] );
            } else {
                $args['_is_ajax'] = '';
            }
            $args['_is_customize'] = ( isset( $_POST['_is_customize'] ) && is_array( $_POST['_is_customize'] ) ? $this->sanitize_settings( $_POST['_is_customize'] ) : '' );
            $args['_is_settings'] = ( isset( $_POST['_is_settings'] ) && is_array( $_POST['_is_settings'] ) ? $this->sanitize_settings( $_POST['_is_settings'] ) : '' );
            $args['tab'] = ( isset( $_POST['tab'] ) ? sanitize_text_field( $_POST['tab'] ) : 'includes' );
            $properties = array();
            if ( '-1' != $id ) {
                $search_form = SearchWiz_Search_Form::get_instance( $id );
                $properties = $search_form->get_properties();
            }
            if ( 'includes' === $args['tab'] && !empty( $properties['_is_excludes'] ) ) {
                $args['_is_excludes'] = $properties['_is_excludes'];
            } else {
                if ( 'excludes' === $args['tab'] && !empty( $properties['_is_includes'] ) ) {
                    $args['_is_includes'] = $properties['_is_includes'];
                }
            }
            $invalid = false;
            if ( !empty( $args['_is_includes'] ) && !empty( $args['_is_excludes'] ) ) {
                foreach ( $args['_is_includes'] as $key => $value ) {
                    if ( $invalid ) {
                        break;
                    }
                    if ( 'woo' === $key ) {
                        continue;
                    }
                    if ( isset( $args['_is_excludes'][$key] ) && !empty( $args['_is_excludes'][$key] ) ) {
                        if ( is_array( $value ) && is_array( $args['_is_excludes'][$key] ) ) {
                            foreach ( $value as $key2 => $val ) {
                                if ( $invalid ) {
                                    break;
                                }
                                if ( is_array( $val ) && isset( $args['_is_excludes'][$key][$key2] ) && is_array( $args['_is_excludes'][$key][$key2] ) ) {
                                    $similar = array_intersect( $val, $args['_is_excludes'][$key][$key2] );
                                    if ( !empty( $similar ) ) {
                                        $invalid = $key;
                                    }
                                } else {
                                    if ( in_array( $val, $args['_is_excludes'][$key] ) ) {
                                        $invalid = $key;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $query = '';
            if ( $invalid ) {
                $query = array(
                    'post' => $id,
                    'tab'  => ( isset( $_POST['tab'] ) ? sanitize_text_field( $_POST['tab'] ) : 'includes' ),
                );
                $query['message'] = 'invalid';
                $query['data'] = $invalid;
            } else {
                $search_form = $this->save_form( $args );
                $query = array(
                    'post' => ( $search_form ? $search_form->id() : 0 ),
                    'tab'  => ( isset( $_POST['tab'] ) ? sanitize_text_field( $_POST['tab'] ) : 'includes' ),
                );
                if ( !$search_form ) {
                    $query['message'] = 'failed';
                } elseif ( -1 == $id ) {
                    $query['message'] = 'created';
                } else {
                    $query['message'] = 'saved';
                }
            }
            $redirect_to = add_query_arg( $query, menu_page_url( 'searchwiz-search', false ) );
            wp_safe_redirect( esc_url_raw( $redirect_to ) );
            exit;
        }
        if ( 'reset' == $action ) {
            $id = ( empty( $_POST['post_ID'] ) ? absint( $_REQUEST['post'] ) : absint( $_POST['post_ID'] ) );
            check_admin_referer( 'is-reset-search-form_' . $id );
            if ( !current_user_can( 'is_edit_search_form', $id ) ) {
                wp_die( esc_html__( 'You are not allowed to reset this item.', 'searchwiz' ) );
            }
            $query = array();
            if ( $id ) {
                $args['id'] = $id;
                $args['title'] = ( isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : null );
                $args['_is_locale'] = null;
                $args['_is_includes'] = '';
                $args['_is_excludes'] = '';
                $args['_is_ajax'] = '';
                $args['_is_customize'] = '';
                $args['_is_settings'] = '';
                $search_form = $this->save_form( $args );
                $query['post'] = $id;
                $query['tab'] = ( isset( $_POST['tab'] ) ? sanitize_text_field( $_POST['tab'] ) : 'includes' );
                $query['message'] = 'reset';
            }
            $redirect_to = add_query_arg( $query, menu_page_url( 'searchwiz-search', false ) );
            wp_safe_redirect( esc_url_raw( $redirect_to ) );
            exit;
        }
        if ( 'copy' == $action ) {
            $id = ( empty( $_POST['post_ID'] ) ? absint( $_REQUEST['post'] ) : absint( $_POST['post_ID'] ) );
            check_admin_referer( 'is-copy-search-form_' . $id );
            if ( !current_user_can( 'is_edit_search_form', $id ) ) {
                wp_die( esc_html__( 'You are not allowed to copy this item.', 'searchwiz' ) );
            }
            $query = array();
            if ( $search_form = SearchWiz_Search_Form::get_instance( $id ) ) {
                $new_search_form = $search_form->copy();
                $new_search_form->save();
                $query['post'] = $new_search_form->id();
                $query['message'] = 'created';
            }
            $redirect_to = add_query_arg( $query, menu_page_url( 'searchwiz-search', false ) );
            wp_safe_redirect( esc_url_raw( $redirect_to ) );
            exit;
        }
        if ( 'delete' == $action ) {
            if ( !empty( $_POST['post_ID'] ) && is_numeric( $_POST['post_ID'] ) ) {
                check_admin_referer( 'is-delete-search-form_' . sanitize_key( $_POST['post_ID'] ) );
            } else {
                if ( !is_array( $_REQUEST['post'] ) ) {
                    check_admin_referer( 'is-delete-search-form_' . sanitize_key( $_REQUEST['post'] ) );
                } else {
                    check_admin_referer( 'bulk-posts' );
                }
            }
            $posts = ( empty( $_POST['post_ID'] ) ? array_map( 'sanitize_key', (array) $_REQUEST['post'] ) : array_map( 'sanitize_key', (array) $_REQUEST['post_ID'] ) );
            $deleted = 0;
            foreach ( $posts as $post ) {
                $post = SearchWiz_Search_Form::get_instance( $post );
                if ( empty( $post ) ) {
                    continue;
                }
                if ( !current_user_can( 'is_delete_search_form', $post->id() ) ) {
                    wp_die( esc_html__( 'You are not allowed to delete this item.', 'searchwiz' ) );
                }
                if ( !$post->delete() ) {
                    wp_die( esc_html__( 'Error in deleting.', 'searchwiz' ) );
                }
                $deleted += 1;
            }
            $query = array();
            if ( $deleted ) {
                $query['message'] = 'deleted';
            }
            $redirect_to = add_query_arg( $query, menu_page_url( 'searchwiz-search', false ) );
            wp_safe_redirect( esc_url_raw( $redirect_to ) );
            exit;
        }
        if ( !isset( $_GET['post'] ) ) {
            $_GET['post'] = '';
        }
        $post = null;
        if ( 'searchwiz-search-new' == $plugin_page ) {
            $post = SearchWiz_Search_Form::get_template( array(
                'locale' => ( isset( $_GET['locale'] ) ? sanitize_text_field( $_GET['locale'] ) : null ),
            ) );
        } elseif ( !empty( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
            $post = SearchWiz_Search_Form::get_instance( sanitize_key( $_GET['post'] ) );
        }
        $current_screen = get_current_screen();
        $help_tabs = new SearchWiz_Help($current_screen);
        if ( $post && current_user_can( 'is_edit_search_form', $post->id() ) ) {
            $help_tabs->set_help_tabs( 'edit' );
        } else {
            $help_tabs->set_help_tabs( 'list' );
            add_filter( 'manage_' . $current_screen->id . '_columns', array('SearchWiz_List_Table', 'define_columns') );
            add_screen_option( 'per_page', array(
                'default' => 20,
                'option'  => 'is_search_forms_per_page',
            ) );
        }
    }

    /**
     * Saves search form.
     */
    function save_form( $args = '', $context = 'save' ) {
        $args = wp_parse_args( $args, array(
            'id'            => -1,
            'title'         => null,
            '_is_locale'    => null,
            '_is_includes'  => null,
            '_is_excludes'  => null,
            '_is_ajax'      => null,
            '_is_customize' => null,
            '_is_settings'  => null,
            'tab'           => null,
        ) );
        $args['id'] = (int) $args['id'];
        $search_form = '';
        if ( -1 == $args['id'] ) {
            $search_form = SearchWiz_Search_Form::get_template();
        } else {
            $search_form = SearchWiz_Search_Form::get_instance( $args['id'] );
        }
        if ( empty( $search_form ) ) {
            return false;
        }
        if ( null !== $args['title'] ) {
            $search_form->set_title( $args['title'] );
        }
        if ( null !== $args['_is_locale'] ) {
            $search_form->set_locale( $args['_is_locale'] );
        }
        $properties = $search_form->get_properties();
        if ( null === $args['tab'] || 'includes' === $args['tab'] ) {
            if ( '' == $args['_is_includes'] ) {
                $post_types = get_post_types( array(
                    'public'              => true,
                    'exclude_from_search' => false,
                ) );
                if ( 'Default Search Form' === $args['title'] && is_array( $post_types ) && in_array( 'attachment', $post_types ) ) {
                    unset($post_types['attachment']);
                }
                $args['_is_includes'] = array(
                    'post_type'      => $post_types,
                    'search_title'   => 1,
                    'search_content' => 1,
                    'search_excerpt' => 1,
                    'post_status'    => array(
                        'publish' => 'publish',
                        'inherit' => 'inherit',
                    ),
                );
            }
            $properties['_is_includes'] = $args['_is_includes'];
        }
        if ( null === $args['tab'] || 'excludes' === $args['tab'] ) {
            $properties['_is_excludes'] = $args['_is_excludes'];
        }
        if ( null === $args['tab'] || 'options' === $args['tab'] ) {
            if ( '' == $args['_is_settings'] ) {
                $args['_is_settings'] = array(
                    'orderby' => 'date',
                    'order'   => 'DESC',
                );
            }
            $properties['_is_settings'] = $args['_is_settings'];
        }
        if ( null === $args['tab'] || 'ajax' === $args['tab'] ) {
            $properties['_is_ajax'] = $args['_is_ajax'];
        }
        if ( null === $args['tab'] || 'customize' === $args['tab'] ) {
            $properties['_is_customize'] = $args['_is_customize'];
        }
        $search_form->set_properties( $properties );
        do_action(
            'searchwiz_before_save_form',
            $search_form,
            $args,
            $context
        );
        if ( 'save' == $context ) {
            $search_form->save();
        }
        do_action(
            'searchwiz_after_save_form',
            $search_form,
            $args,
            $context
        );
        return $search_form;
    }

    /**
     * Sanitizes includes settings.
     */
    function sanitize_includes( $input, $defaults = array() ) {
        if ( null === $input ) {
            return $defaults;
        }
        $defaults = wp_parse_args( $defaults, array(
            'post_type' => get_post_types( array(
                'public' => true,
            ) ),
        ) );
        $input = wp_parse_args( $input, $defaults );
        $output = $this->sanitize_fields( $input );
        return $output;
    }

    /**
     * Sanitizes excludes settings.
     */
    function sanitize_excludes( $input, $defaults = '' ) {
        if ( null === $input ) {
            return $defaults;
        }
        $output = $this->sanitize_fields( $input );
        return $output;
    }

    /**
     * Sanitizes settings options.
     */
    function sanitize_settings( $input, $defaults = '', $exception = '' ) {
        if ( null === $input ) {
            return $defaults;
        }
        $output = $this->sanitize_fields( $input, $exception );
        return $output;
    }

    /**
     * Sanitizes fields.
     */
    function sanitize_fields( $input, $exception = '' ) {
        $output = array();
        if ( is_array( $input ) && !empty( $input ) ) {
            foreach ( $input as $key => $value ) {
                if ( is_array( $value ) ) {
                    foreach ( $value as $key2 => $value2 ) {
                        if ( is_array( $value2 ) ) {
                            foreach ( $value2 as $key3 => $value3 ) {
                                if ( $exception !== $key3 ) {
                                    $output[$key][$key2][$key3] = $this->sanitize_field( $input[$key][$key2][$key3], $key3 );
                                }
                            }
                        } else {
                            if ( $exception !== $key2 ) {
                                $output[$key][$key2] = $this->sanitize_field( $input[$key][$key2], $key2 );
                            }
                        }
                    }
                } else {
                    if ( $exception !== $key ) {
                        $output[$key] = $this->sanitize_field( $input[$key], $key );
                    }
                }
            }
        }
        return $output;
    }

    function sanitize_field( $input, $key = '' ) {
        switch ( $key ) {
            case 'description_length':
                $input = ( is_numeric( $input ) ? (int) $input : 20 );
                break;
            case 'min_no_for_search':
                $input = ( is_numeric( $input ) ? (int) $input : 1 );
                break;
            case 'result_box_max_height':
                $input = ( is_numeric( $input ) ? (int) $input : 400 );
                break;
            default:
                $input = sanitize_textarea_field( $input );
        }
        return $input;
    }

    /**
     * Displays search form save button.
     */
    function save_button( $post_id ) {
        static $button = '';
        if ( !empty( $button ) ) {
            esc_html( $button );
            return;
        }
        $onclick = "this.form._wpnonce.value = '" . wp_create_nonce( 'is-save-search-form_' . $post_id ) . "'; this.form.action.value = 'save'; return true;";
        ?>
		<input type="submit" class="button-primary" name="is_save" value="<?php 
        esc_attr_e( 'Save Form', 'searchwiz' );
        ?>" onclick="<?php 
        echo esc_js( $onclick );
        ?>" />
		<?php 
    }

    /**
     * Returns premium plugin version link.
     */
    public static function pro_link( $plan = 'pro' ) {
        return; //TODO: Replace with a nicer call to an upgrade.
    }

    /**
     * AJAX handler for saving theme integration setting
     */
    public function ajax_save_theme_integration() {
        // Verify nonce
        check_ajax_referer( 'searchwiz_theme_integration_nonce', 'nonce' );

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
        }

        // Get the enabled value
        $enabled = isset( $_POST['enabled'] ) && 'on' === $_POST['enabled'] ? 'on' : 'off';

        // Update the option
        $option = array( 'enabled' => $enabled );
        update_option( 'searchwiz_theme_integration', $option );

        // Send success response
        wp_send_json_success( array(
            'message' => 'Theme integration setting saved',
            'enabled' => $enabled
        ) );
    }

    /**
     * AJAX handler for auto-saving default search setting
     */
    public function ajax_save_default_search() {
        // Verify nonce
        check_ajax_referer( 'searchwiz_default_search_nonce', 'nonce' );

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
        }

        // Get the enabled value
        $enabled = isset( $_POST['enabled'] ) && 'on' === $_POST['enabled'];

        // Get current settings
        $sw_settings = get_option( 'searchwiz_settings', array() );

        // Update the default_search flag
        // Simple logic: checked = 1 (enabled), unchecked = 0 (disabled)
        if ( $enabled ) {
            // User wants SearchWiz as default
            $sw_settings['default_search'] = 1;
        } else {
            // User doesn't want SearchWiz as default
            $sw_settings['default_search'] = 0;
        }

        // Save settings
        update_option( 'searchwiz_settings', $sw_settings );

        // Send success response
        wp_send_json_success( array(
            'message' => 'Default search setting saved',
            'enabled' => $enabled
        ) );
    }

    /**
     * AJAX handler for auto-saving all settings
     */
    public function ajax_autosave_settings() {
        $debug_param = isset( $_GET['searchwiz_debug'] ) ? sanitize_text_field( wp_unslash( $_GET['searchwiz_debug'] ) ) : '';
        $debug = '1' === $debug_param;

        // DEBUG: Log entry point
        if ( $debug ) {
            error_log( '[SearchWiz DEBUG] ===== AUTO-SAVE REQUEST STARTED =====' );
            error_log( '[SearchWiz DEBUG] Timestamp: ' . current_time( 'mysql' ) );
        }

        // Verify nonce - sanitize before verification
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'searchwiz_autosave_nonce' ) ) {
            if ( $debug ) {
                error_log( '[SearchWiz DEBUG] ERROR: Invalid nonce' );
            }
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            if ( $debug ) {
                error_log( '[SearchWiz DEBUG] ERROR: Insufficient permissions' );
            }
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
        }

        // Parse and sanitize the form data
        $form_data = array();
        if ( isset( $_POST['form_data'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized recursively below
            $raw_form_data = wp_unslash( $_POST['form_data'] );
            parse_str( $raw_form_data, $form_data );

            // Recursively sanitize all form data
            $form_data = $this->sanitize_form_data_recursive( $form_data );
        }

        // Save different option groups
        if ( isset( $form_data['searchwiz_settings'] ) ) {
            update_option( 'searchwiz_settings', $form_data['searchwiz_settings'] );
        }
        if ( isset( $form_data['searchwiz_display_settings'] ) ) {
            // IMPORTANT: Validate display settings using the same validation function as form submit
            // This ensures color picker values and other fields are properly sanitized/validated
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sw-settings-fields.php';
            $settings_fields = new SearchWiz_Settings_Fields();
            $validated_data = $settings_fields->is_validate_display_settings( $form_data['searchwiz_display_settings'] );
            update_option( 'searchwiz_display_settings', $validated_data );

            if ( $debug ) {
                error_log( '[SearchWiz DEBUG] Display settings validated and saved' );
                error_log( '[SearchWiz DEBUG] Input: ' . print_r( $form_data['searchwiz_display_settings'], true ) );
                error_log( '[SearchWiz DEBUG] Validated: ' . print_r( $validated_data, true ) );
            }
        }
        if ( isset( $form_data['searchwiz_searchbox_settings'] ) ) {
            // Validate searchbox settings using the same validation function as form submit
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sw-settings-fields.php';
            $settings_fields = new SearchWiz_Settings_Fields();
            $validated_data = $settings_fields->is_validate_searchbox_settings( $form_data['searchwiz_searchbox_settings'] );
            update_option( 'searchwiz_searchbox_settings', $validated_data );

            if ( $debug ) {
                error_log( '[SearchWiz DEBUG] Searchbox settings validated and saved' );
                error_log( '[SearchWiz DEBUG] Input: ' . print_r( $form_data['searchwiz_searchbox_settings'], true ) );
                error_log( '[SearchWiz DEBUG] Validated: ' . print_r( $validated_data, true ) );
            }
        }
        if ( isset( $form_data['searchwiz_theme_integration'] ) ) {
            update_option( 'searchwiz_theme_integration', $form_data['searchwiz_theme_integration'] );
        }
        if ( isset( $form_data['searchwiz_default_search'] ) ) {
            update_option( 'searchwiz_default_search', $form_data['searchwiz_default_search'] );
        }
        if ( isset( $form_data['searchwiz_menu_search'] ) ) {
            update_option( 'searchwiz_menu_search', $form_data['searchwiz_menu_search'] );
        }
        if ( isset( $form_data['searchwiz_analytics'] ) ) {
            update_option( 'searchwiz_analytics', $form_data['searchwiz_analytics'] );
        }
        if ( isset( $form_data['searchwiz_index'] ) ) {
            // Merge with existing options to preserve fields not in form
            $existing = get_option( 'searchwiz_index', array() );
            $merged = array_merge( $existing, $form_data['searchwiz_index'] );

            // IMPORTANT: Handle unchecked checkboxes explicitly
            // When a checkbox is unchecked, browsers don't send it in form data
            // We always need to set missing checkbox fields to 0 when sw_index data is submitted
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

            foreach ( $checkbox_fields as $field ) {
                if ( ! isset( $form_data['searchwiz_index'][ $field ] ) ) {
                    $merged[ $field ] = 0;
                } else {
                    $merged[ $field ] = 1;
                }
            }

            update_option( 'searchwiz_index', $merged );
        }

        // Handle legacy "is_" prefixed field names (map to sw_ options)
        if ( isset( $form_data['is_display_settings'] ) ) {
            update_option( 'searchwiz_display_settings', $form_data['is_display_settings'] );
        }
        if ( isset( $form_data['is_menu_search'] ) ) {
            update_option( 'searchwiz_menu_search', $form_data['is_menu_search'] );
        }
        if ( isset( $form_data['is_analytics'] ) ) {
            update_option( 'searchwiz_analytics', $form_data['is_analytics'] );
        }

        // Save individual performance settings (not in option groups)
        if ( isset( $form_data['searchwiz_batch_size'] ) ) {
            update_option( 'searchwiz_batch_size', absint( $form_data['searchwiz_batch_size'] ) );
        }
        if ( isset( $form_data['searchwiz_auto_index'] ) ) {
            update_option( 'searchwiz_auto_index', 1 );
        } else {
            // Checkbox not checked - need to explicitly set to 0
            if ( isset( $_POST['form_data'] ) && strpos( $_POST['form_data'], 'searchwiz_auto_index' ) === false && strpos( $_POST['form_data'], 'performance' ) !== false ) {
                update_option( 'searchwiz_auto_index', 0 );
            }
        }
        if ( isset( $form_data['searchwiz_cache_results'] ) ) {
            update_option( 'searchwiz_cache_results', 1 );
        } else {
            // Checkbox not checked - need to explicitly set to 0
            if ( isset( $_POST['form_data'] ) && strpos( $_POST['form_data'], 'searchwiz_cache_results' ) === false && strpos( $_POST['form_data'], 'performance' ) !== false ) {
                update_option( 'searchwiz_cache_results', 0 );
            }
        }

        // DEBUG: Log what was saved
        if ( $debug ) {
            error_log( '[SearchWiz DEBUG] ===== SAVE OPERATIONS COMPLETED =====' );
            if ( isset( $form_data['searchwiz_display_settings'] ) ) {
                error_log( '[SearchWiz DEBUG] searchwiz_display_settings saved: ' . wp_json_encode( $form_data['searchwiz_display_settings'] ) );
                error_log( '[SearchWiz DEBUG] Retrieved from DB: ' . wp_json_encode( get_option( 'searchwiz_display_settings' ) ) );
            }
            if ( isset( $form_data['searchwiz_settings'] ) ) {
                error_log( '[SearchWiz DEBUG] searchwiz_settings saved: ' . wp_json_encode( $form_data['searchwiz_settings'] ) );
            }
            error_log( '[SearchWiz DEBUG] Sending success response' );
        }

        // Send success response
        wp_send_json_success( array(
            'message' => 'Settings saved successfully',
            'debug' => $debug ? 'Check error_log for details' : false
        ) );
    }

    /**
     * Recursively sanitize form data array.
     *
     * @since 1.0.0
     * @param array $data The data to sanitize.
     * @return array Sanitized data.
     */
    private function sanitize_form_data_recursive( $data ) {
        if ( ! is_array( $data ) ) {
            // For hex color values, preserve them
            if ( preg_match( '/^#[a-fA-F0-9]{6}$/', $data ) ) {
                return sanitize_hex_color( $data );
            }
            // For numeric values
            if ( is_numeric( $data ) ) {
                return intval( $data );
            }
            // Default text sanitization
            return sanitize_text_field( $data );
        }

        $sanitized = array();
        foreach ( $data as $key => $value ) {
            $sanitized_key = sanitize_key( $key );
            $sanitized[ $sanitized_key ] = $this->sanitize_form_data_recursive( $value );
        }

        return $sanitized;
    }

}
