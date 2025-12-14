<?php

/**
 * Plugin Name: SearchWiz
 * Plugin URI:  https://www.searchwiz.ai
 * Description: Smart WordPress search with instant AJAX results and intelligent content discovery.
 * Version:     1.0.0
 * Author:      Search Wiz
 * License:     GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages/
 * Text Domain: searchwiz
 * Source Code: https://plugins.svn.wordpress.org/searchwiz/trunk/
 * Subversion: Testing npm install fix
 *
 * WC tested up to: 9
 *
 * SearchWiz is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * SearchWiz is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SearchWiz. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

final class SearchWiz {
    private static $_instance;

    public static function getInstance() {
        if ( !self::$_instance instanceof self ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function define_constants() {
        if ( !defined( 'SEARCHWIZ_VERSION' ) ) {
            define( 'SEARCHWIZ_VERSION', '1.0.0' );
        }
        if ( !defined( 'SEARCHWIZ_PLUGIN_FILE' ) ) {
            define( 'SEARCHWIZ_PLUGIN_FILE', __FILE__ );
        }
        if ( !defined( 'SEARCHWIZ_PLUGIN_BASE' ) ) {
            define( 'SEARCHWIZ_PLUGIN_BASE', plugin_basename( SEARCHWIZ_PLUGIN_FILE ) );
        }
        if ( !defined( 'SEARCHWIZ_PLUGIN_DIR' ) ) {
            define( 'SEARCHWIZ_PLUGIN_DIR', plugin_dir_path( SEARCHWIZ_PLUGIN_FILE ) );
        }
        if ( !defined( 'SEARCHWIZ_PLUGIN_URI' ) ) {
            define( 'SEARCHWIZ_PLUGIN_URI', plugins_url( '/', SEARCHWIZ_PLUGIN_FILE ) );
        }
        if ( !defined( 'SEARCHWIZ_ADMIN_READ_CAPABILITY' ) ) {
            define( 'SEARCHWIZ_ADMIN_READ_CAPABILITY', 'edit_posts' );
        }
        if ( !defined( 'SEARCHWIZ_ADMIN_READ_WRITE_CAPABILITY' ) ) {
            define( 'SEARCHWIZ_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
        }
    }

    public function includes() {
        /**
         * Common Files
         */
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/base-functions.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-activator.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-admin-public.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-analytics.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-base-options.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-customizer-panel.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-customizer.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-deactivator.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-debug.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-form-builder.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-i18n.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-builder.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-helper.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-manager.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-match.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-matches.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-model.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-index-options.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-indexer-new.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-search-form.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-suggestion-builder.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-theme-integration.php';

        // Load theme-specific overrides early (before theme loads)
        // These files contain pluggable function overrides that must be defined before themes
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/themes/class-sw-theme-storefront.php';

        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-upgrade-tracking.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw-widget.php';
        require_once SEARCHWIZ_PLUGIN_DIR . 'includes/class-sw.php';

        // Load SearchWiz_Admin unconditionally as it's needed during plugin activation
        require_once SEARCHWIZ_PLUGIN_DIR . 'admin/class-sw-admin.php';

        if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            /**
             * Admin Files
             */
            require_once SEARCHWIZ_PLUGIN_DIR . 'admin/class-sw-editor.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'admin/class-sw-help.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'admin/class-sw-list-table.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'admin/class-sw-settings-fields.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'admin/class-sw-settings-index-fields.php';
            if ( class_exists( 'TablePress' ) ) {
                require_once SEARCHWIZ_PLUGIN_DIR . 'includes/compatibility/class-sw-tablepress-compat.php';
            }
        }

        if ( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            /**
             * Public Files - React Loader FIRST
             */
            require_once SEARCHWIZ_PLUGIN_DIR . 'public/class-sw-react-loader.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'public/class-sw-ajax.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'public/class-sw-public.php';
            require_once SEARCHWIZ_PLUGIN_DIR . 'public/class-sw-index-search.php';
        }
    }

    public function register_activ_deactiv_hooks() {
        register_activation_hook( SEARCHWIZ_PLUGIN_FILE, array('SearchWiz_Activator', 'activate') );
        register_deactivation_hook( SEARCHWIZ_PLUGIN_FILE, array('SearchWiz_Deactivator', 'deactivate') );
    }

    public function start() {
        $is_loader = SearchWiz_Loader::getInstance();
        $is_loader->load();
    }
}

function searchwiz_search_start() {
    $searchwiz_instance = SearchWiz::getInstance();
    $searchwiz_instance->start();
}

add_action( 'plugins_loaded', 'searchwiz_search_start' );

$searchwiz = SearchWiz::getInstance();
$searchwiz->define_constants();
$searchwiz->includes();
$searchwiz->register_activ_deactiv_hooks();