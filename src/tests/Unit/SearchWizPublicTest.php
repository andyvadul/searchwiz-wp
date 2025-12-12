<?php
/**
 * Unit tests for SearchWiz_Public class.
 *
 * @package SearchWiz\Tests\Unit
 */

namespace SearchWiz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test class for SearchWiz_Public.
 */
class SearchWizPublicTest extends TestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var \SearchWiz_Public
	 */
	private $public;

	/**
	 * Setup before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock WordPress constants.
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/fake/path/' );
		}
		if ( ! defined( 'SEARCHWIZ_VERSION' ) ) {
			define( 'SEARCHWIZ_VERSION', '1.0.0' );
		}
		if ( ! defined( 'SEARCHWIZ_PLUGIN_FILE' ) ) {
			define( 'SEARCHWIZ_PLUGIN_FILE', '/fake/path/searchwiz.php' );
		}

		// Mock WordPress functions used in constructor.
		Functions\when( 'get_option' )->alias(
			function ( $option, $default = false ) {
				return $default;
			}
		);
		Functions\when( 'add_filter' )->justReturn( true );

		// Create instance for testing.
		require_once __DIR__ . '/../../public/class-sw-public.php';
		$this->public = new \SearchWiz_Public();
	}

	/**
	 * Teardown after each test.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test getInstance returns singleton instance.
	 */
	public function test_get_instance_returns_singleton(): void {
		$instance1 = \SearchWiz_Public::getInstance();
		$instance2 = \SearchWiz_Public::getInstance();

		$this->assertSame( $instance1, $instance2 );
		$this->assertInstanceOf( \SearchWiz_Public::class, $instance1 );
	}

	/**
	 * Test custom_excerpt_length returns default value when not set.
	 */
	public function test_custom_excerpt_length_returns_default(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array();
				}
				return $default;
			} );

		Functions\when( 'absint' )
			->returnArg();

		$result = $this->public->custom_excerpt_length( 55 );

		$this->assertEquals( 20, $result );
	}

	/**
	 * Test custom_excerpt_length returns custom value when set.
	 */
	public function test_custom_excerpt_length_returns_custom_value(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'excerpt_length' => 50 );
				}
				return $default;
			} );

		Functions\when( 'absint' )
			->returnArg();

		$result = $this->public->custom_excerpt_length( 55 );

		$this->assertEquals( 50, $result );
	}

	/**
	 * Test custom_excerpt_length with debug mode.
	 */
	public function test_custom_excerpt_length_with_debug(): void {
		$_GET['searchwiz_debug'] = '1';

		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'excerpt_length' => 30 );
				}
				return $default;
			} );

		Functions\when( 'absint' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();

		$result = $this->public->custom_excerpt_length( 20 );

		$this->assertEquals( 30, $result );

		unset( $_GET['searchwiz_debug'] );
	}

	/**
	 * Test generate_custom_css with no settings.
	 */
	public function test_generate_custom_css_empty_settings(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				return array();
			} );

		$result = $this->public->generate_custom_css();

		$this->assertEmpty( trim( $result ) );
	}

	/**
	 * Test generate_custom_css with primary color.
	 */
	public function test_generate_custom_css_with_primary_color(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'primary_color' => '#ff0000' );
				}
				return array();
			} );

		Functions\when( 'sanitize_hex_color' )
			->returnArg();

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( '#ff0000', $result );
		$this->assertStringContainsString( 'color: #ff0000', $result );
	}

	/**
	 * Test generate_custom_css with title font size.
	 */
	public function test_generate_custom_css_with_title_font_size(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'title_font_size' => 24 );
				}
				return array();
			} );

		Functions\when( 'absint' )->returnArg();

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'font-size: 24px', $result );
	}

	/**
	 * Test generate_custom_css hides thumbnails when set.
	 */
	public function test_generate_custom_css_hide_thumbnails(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'show_thumbnails' => 0 );
				}
				return array();
			} );

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'display: none', $result );
		$this->assertStringContainsString( '.left-section', $result );
	}

	/**
	 * Test generate_custom_css hides excerpts when set.
	 */
	public function test_generate_custom_css_hide_excerpts(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'show_excerpts' => 0 );
				}
				return array();
			} );

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'display: none', $result );
		$this->assertStringContainsString( '.is-search-content', $result );
	}

	/**
	 * Test generate_custom_css with card spacing.
	 */
	public function test_generate_custom_css_with_card_spacing(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'card_spacing' => 10 );
				}
				return array();
			} );

		Functions\when( 'absint' )->returnArg();

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'padding: 10px', $result );
	}

	/**
	 * Test generate_custom_css with border radius.
	 */
	public function test_generate_custom_css_with_border_radius(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_display_settings' ) {
					return array( 'border_radius' => 5 );
				}
				return array();
			} );

		Functions\when( 'absint' )->returnArg();

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'border-radius: 5px', $result );
	}

	/**
	 * Test generate_custom_css with border color.
	 */
	public function test_generate_custom_css_with_border_color(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_searchbox_settings' ) {
					return array( 'border_color' => '#00ff00' );
				}
				return array();
			} );

		Functions\when( 'sanitize_hex_color' )->returnArg();

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'border-color: #00ff00', $result );
	}

	/**
	 * Test generate_custom_css with focus color.
	 */
	public function test_generate_custom_css_with_focus_color(): void {
		Functions\when( 'get_option' )
			->alias( function ( $option, $default = false ) {
				if ( $option === 'searchwiz_searchbox_settings' ) {
					return array( 'focus_color' => '#0000ff' );
				}
				return array();
			} );

		Functions\when( 'sanitize_hex_color' )->returnArg();

		$result = $this->public->generate_custom_css();

		$this->assertStringContainsString( 'border-color: #0000ff', $result );
		$this->assertStringContainsString( ':focus', $result );
	}

	/**
	 * Test query_vars adds id to query vars.
	 */
	public function test_query_vars_adds_id(): void {
		$vars = array( 's', 'post_type' );

		$result = $this->public->query_vars( $vars );

		$this->assertContains( 'id', $result );
		$this->assertContains( 's', $result );
		$this->assertContains( 'post_type', $result );
	}

	/**
	 * Test posts_distinct_request returns DISTINCT for search queries.
	 */
	public function test_posts_distinct_request_for_search(): void {
		Functions\expect( 'is_admin' )->andReturn( false );

		$query = Mockery::mock( 'WP_Query' );
		$query->query_vars = array( 's' => 'test search' );

		$result = $this->public->posts_distinct_request( '', $query );

		$this->assertEquals( 'DISTINCT', $result );
	}

	/**
	 * Test posts_distinct_request returns empty for non-search queries.
	 */
	public function test_posts_distinct_request_for_non_search(): void {
		Functions\expect( 'is_admin' )->andReturn( false );

		$query = Mockery::mock( 'WP_Query' );
		$query->query_vars = array( 's' => '' );

		$result = $this->public->posts_distinct_request( '', $query );

		$this->assertEquals( '', $result );
	}

	/**
	 * Test posts_distinct_request returns original for admin.
	 */
	public function test_posts_distinct_request_in_admin(): void {
		Functions\expect( 'is_admin' )->andReturn( true );

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', false );
		}

		$query = Mockery::mock( 'WP_Query' );
		$query->query_vars = array( 's' => 'test' );

		$result = $this->public->posts_distinct_request( 'ORIGINAL', $query );

		$this->assertEquals( 'ORIGINAL', $result );
	}

	/**
	 * Test is_icu_regexp detects MySQL 8.0.4+.
	 */
	public function test_is_icu_regexp_mysql_8(): void {
		global $wpdb;
		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->shouldReceive( 'db_version' )
			->once()
			->andReturn( '8.0.5' );
		$wpdb->shouldReceive( 'db_server_info' )
			->once()
			->andReturn( '8.0.5-MySQL Community Server' );

		$result = $this->public->is_icu_regexp();

		$this->assertTrue( $result );
	}

	/**
	 * Test is_icu_regexp returns false for older MySQL.
	 */
	public function test_is_icu_regexp_mysql_old(): void {
		global $wpdb;
		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->shouldReceive( 'db_version' )
			->once()
			->andReturn( '5.7.0' );
		$wpdb->shouldReceive( 'db_server_info' )
			->once()
			->andReturn( '5.7.0-MySQL Community Server' );

		$result = $this->public->is_icu_regexp();

		$this->assertFalse( $result );
	}

	/**
	 * Test get_menu_style_css with no menu style.
	 */
	public function test_get_menu_style_css_empty(): void {
		$this->public->opt = array();

		$result = $this->public->get_menu_style_css();

		$this->assertEmpty( $result );
	}

	/**
	 * Test get_menu_style_css with magnifier color.
	 */
	public function test_get_menu_style_css_with_color(): void {
		$this->public->opt = array(
			'menu_style'          => 'dropdown',
			'menu_magnifier_color' => '#ff00ff',
		);

		Functions\expect( 'sanitize_hex_color' )
			->once()
			->with( '#ff00ff' )
			->andReturn( '#ff00ff' );

		$result = $this->public->get_menu_style_css();

		$this->assertStringContainsString( 'fill: #ff00ff', $result );
		$this->assertStringContainsString( 'border-color: #ff00ff', $result );
	}

	/**
	 * Test is_body_classes adds template class.
	 */
	public function test_is_body_classes_adds_template(): void {
		Functions\expect( 'get_template' )
			->once()
			->andReturn( 'twentytwentyfour' );

		$classes = array( 'home', 'page' );

		$result = $this->public->is_body_classes( $classes );

		$this->assertContains( 'twentytwentyfour', $result );
		$this->assertContains( 'home', $result );
		$this->assertContains( 'page', $result );
	}

	/**
	 * Test tablepress_content_search with empty search terms.
	 */
	public function test_tablepress_content_search_empty_terms(): void {
		$result = $this->public->tablepress_content_search( array(), '2', 'AND' );

		$this->assertEmpty( $result );
	}

	/**
	 * Test tablepress_content_search with non-array search terms.
	 */
	public function test_tablepress_content_search_invalid_terms(): void {
		$result = $this->public->tablepress_content_search( 'not an array', '2', 'AND' );

		$this->assertEmpty( $result );
	}
}
