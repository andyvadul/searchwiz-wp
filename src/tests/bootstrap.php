<?php
/**
 * PHPUnit bootstrap file for SearchWiz tests.
 *
 * @package SearchWiz\Tests
 */

// Composer autoloader - go up to plugin root, then to repo root for vendor.
// bootstrap.php is at: repo_root/src/searchwiz/tests/bootstrap.php
// plugin_dir = repo_root/src/searchwiz
// repo_root = repo_root/src/searchwiz -> src -> repo_root (2 levels up from plugin_dir)
$plugin_dir = dirname( __DIR__ );
$repo_root  = dirname( dirname( $plugin_dir ) );

require_once $repo_root . '/vendor/autoload.php';

// Load Brain Monkey for WordPress function mocking.
require_once $repo_root . '/vendor/antecedent/patchwork/Patchwork.php';

use Brain\Monkey;

/**
 * Setup before each test.
 */
Monkey\setUp();

// Define WordPress constants that plugin code might check.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'SEARCHWIZ_PLUGIN_DIR' ) ) {
    define( 'SEARCHWIZ_PLUGIN_DIR', $plugin_dir . '/' );
}

if ( ! defined( 'SEARCHWIZ_PLUGIN_FILE' ) ) {
    define( 'SEARCHWIZ_PLUGIN_FILE', SEARCHWIZ_PLUGIN_DIR . 'searchwiz.php' );
}

if ( ! defined( 'SEARCHWIZ_VERSION' ) ) {
    define( 'SEARCHWIZ_VERSION', '1.0.0' );
}

/**
 * Register shutdown function to teardown Brain Monkey.
 */
register_shutdown_function(
    function () {
        Monkey\tearDown();
    }
);
