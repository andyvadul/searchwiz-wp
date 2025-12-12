<?php
/**
 * Unit tests for SearchWiz_Ajax class
 *
 * @package SearchWiz
 * @subpackage Tests\Unit
 */

namespace SearchWiz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;

/**
 * Test SearchWiz_Ajax functionality
 *
 * Tests the AJAX handler class methods with TDD approach
 */
class SearchWizAjaxTest extends TestCase {

	/**
	 * Instance of SearchWiz_Ajax
	 *
	 * @var \SearchWiz_Ajax
	 */
	private $ajax;

	/**
	 * Setup before each test
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Define ABSPATH if not defined
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/' );
		}

		// Load the class file
		require_once dirname( dirname( __DIR__ ) ) . '/public/class-sw-ajax.php';

		// Reset singleton instance for each test
		$reflection = new \ReflectionClass( 'SearchWiz_Ajax' );
		$instance = $reflection->getProperty( '_instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, null );
		$instance->setAccessible( false );
	}

	/**
	 * Teardown after each test
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test getInstance returns singleton instance
	 *
	 * @covers SearchWiz_Ajax::getInstance
	 */
	public function test_get_instance_returns_singleton() {
		$instance1 = \SearchWiz_Ajax::getInstance();
		$instance2 = \SearchWiz_Ajax::getInstance();

		$this->assertInstanceOf( 'SearchWiz_Ajax', $instance1 );
		$this->assertSame( $instance1, $instance2, 'getInstance should return same instance' );
	}

	/**
	 * Test getInstance creates new instance if none exists
	 *
	 * @covers SearchWiz_Ajax::getInstance
	 */
	public function test_get_instance_creates_instance_when_none_exists() {
		$instance = \SearchWiz_Ajax::getInstance();

		$this->assertInstanceOf( 'SearchWiz_Ajax', $instance );
	}

	/**
	 * Test ajax_test sends success response
	 *
	 * @covers SearchWiz_Ajax::ajax_test
	 */
	public function test_ajax_test_sends_success_response() {
		$called = false;
		$passed_data = null;

		Functions\expect( 'wp_send_json_success' )
			->once()
			->with( Mockery::on( function( $data ) use ( &$called, &$passed_data ) {
				$called = true;
				$passed_data = $data;
				return isset( $data['message'] ) && $data['message'] === 'Test endpoint works!';
			} ) );

		$this->ajax = \SearchWiz_Ajax::getInstance();
		$this->ajax->ajax_test();

		$this->assertTrue( $called, 'wp_send_json_success should be called' );
		$this->assertArrayHasKey( 'message', $passed_data );
		$this->assertEquals( 'Test endpoint works!', $passed_data['message'] );
	}

	/**
	 * Test get_taxonomies returns empty array when no terms match
	 *
	 * @covers SearchWiz_Ajax::get_taxonomies
	 */
	public function test_get_taxonomies_returns_empty_when_no_match() {
		Functions\expect( 'get_terms' )
			->once()
			->with( Mockery::type( 'array' ) )
			->andReturn( [] );

		$this->ajax = \SearchWiz_Ajax::getInstance();
		$result = $this->ajax->get_taxonomies( 'category', 'nonexistent' );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_taxonomies returns matching terms (non-strict)
	 *
	 * @covers SearchWiz_Ajax::get_taxonomies
	 */
	public function test_get_taxonomies_returns_matching_terms_non_strict() {
		$mock_term = (object) [
			'term_id'  => 1,
			'name'     => 'Products',
			'slug'     => 'products',
			'taxonomy' => 'category',
			'count'    => 5,
		];

		Functions\expect( 'get_terms' )
			->once()
			->andReturn( [ $mock_term ] );

		Functions\expect( 'get_term_link' )
			->once()
			->with( $mock_term, 'category' )
			->andReturn( 'http://example.com/category/products' );

		$this->ajax = \SearchWiz_Ajax::getInstance();
		$result = $this->ajax->get_taxonomies( 'category', 'prod' );

		$this->assertCount( 1, $result );
		$this->assertEquals( 1, $result[0]['term_id'] );
		$this->assertEquals( 'Products', $result[0]['name'] );
		$this->assertEquals( 'products', $result[0]['slug'] );
	}

	/**
	 * Test get_taxonomies with strict mode only returns exact matches
	 *
	 * @covers SearchWiz_Ajax::get_taxonomies
	 */
	public function test_get_taxonomies_strict_mode_exact_match() {
		$exact_match = (object) [
			'term_id'  => 1,
			'name'     => 'Product',
			'slug'     => 'product',
			'taxonomy' => 'category',
			'count'    => 3,
		];

		$partial_match = (object) [
			'term_id'  => 2,
			'name'     => 'Products',
			'slug'     => 'products',
			'taxonomy' => 'category',
			'count'    => 5,
		];

		Functions\expect( 'get_terms' )
			->once()
			->andReturn( [ $exact_match, $partial_match ] );

		Functions\expect( 'get_term_link' )
			->once()
			->with( $exact_match, 'category' )
			->andReturn( 'http://example.com/category/product' );

		$this->ajax = \SearchWiz_Ajax::getInstance();
		$result = $this->ajax->get_taxonomies( 'category', 'product', true );

		$this->assertCount( 1, $result, 'Strict mode should only return exact matches' );
		$this->assertEquals( 'Product', $result[0]['name'] );
	}

	/**
	 * Test get_taxonomies is case insensitive
	 *
	 * @covers SearchWiz_Ajax::get_taxonomies
	 */
	public function test_get_taxonomies_case_insensitive() {
		$mock_term = (object) [
			'term_id'  => 1,
			'name'     => 'Product',
			'slug'     => 'product',
			'taxonomy' => 'category',
			'count'    => 5,
		];

		Functions\expect( 'get_terms' )
			->once()
			->andReturn( [ $mock_term ] );

		Functions\expect( 'get_term_link' )
			->once()
			->andReturn( 'http://example.com/category/product' );

		$this->ajax = \SearchWiz_Ajax::getInstance();
		$result = $this->ajax->get_taxonomies( 'category', 'PRODUCT', true );

		$this->assertCount( 1, $result, 'Should match regardless of case' );
	}

	/**
	 * Test ajax_get_inline_suggestion requires valid nonce
	 *
	 * @covers SearchWiz_Ajax::ajax_get_inline_suggestion
	 */
	public function test_ajax_get_inline_suggestion_checks_nonce() {
		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'searchwiz_nonce', 'security' )
			->andThrow( new \Exception( 'Nonce verification failed' ) );

		$this->ajax = \SearchWiz_Ajax::getInstance();

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Nonce verification failed' );

		$this->ajax->ajax_get_inline_suggestion();
	}

	/**
	 * Note: Full inline suggestion testing requires integration test due to
	 * class_exists() and require_once() complexity. The nonce check above
	 * verifies security, which is the critical functionality.
	 */

	/**
	 * Test highlight_search_terms highlights single word
	 *
	 * @covers SearchWiz_Ajax::highlight_search_terms
	 */
	public function test_highlight_search_terms_single_word() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		// Use reflection to test private method
		$reflection = new \ReflectionClass( $this->ajax );
		$method = $reflection->getMethod( 'highlight_search_terms' );
		$method->setAccessible( true );

		$text = 'This is a test product';
		$search_term = 'test';
		$result = $method->invoke( $this->ajax, $text, $search_term );

		$this->assertStringContainsString( '<mark>test</mark>', $result );
	}

	/**
	 * Test highlight_search_terms handles multiple words
	 *
	 * @covers SearchWiz_Ajax::highlight_search_terms
	 */
	public function test_highlight_search_terms_multiple_words() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$reflection = new \ReflectionClass( $this->ajax );
		$method = $reflection->getMethod( 'highlight_search_terms' );
		$method->setAccessible( true );

		$text = 'This is a test product description';
		$search_term = 'test product';
		$result = $method->invoke( $this->ajax, $text, $search_term );

		$this->assertStringContainsString( '<mark>test</mark>', $result );
		$this->assertStringContainsString( '<mark>product</mark>', $result );
	}

	/**
	 * Test highlight_search_terms is case insensitive
	 *
	 * @covers SearchWiz_Ajax::highlight_search_terms
	 */
	public function test_highlight_search_terms_case_insensitive() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$reflection = new \ReflectionClass( $this->ajax );
		$method = $reflection->getMethod( 'highlight_search_terms' );
		$method->setAccessible( true );

		$text = 'Product and product and PRODUCT';
		$search_term = 'product';
		$result = $method->invoke( $this->ajax, $text, $search_term );

		// Should match all three occurrences regardless of case
		$this->assertEquals( 3, substr_count( $result, '<mark>' ) );
		$this->assertStringContainsString( '<mark>Product</mark>', $result );
		$this->assertStringContainsString( '<mark>product</mark>', $result );
		$this->assertStringContainsString( '<mark>PRODUCT</mark>', $result );
	}

	/**
	 * Test highlight_search_terms returns original for empty inputs
	 *
	 * @covers SearchWiz_Ajax::highlight_search_terms
	 */
	public function test_highlight_search_terms_empty_inputs() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$reflection = new \ReflectionClass( $this->ajax );
		$method = $reflection->getMethod( 'highlight_search_terms' );
		$method->setAccessible( true );

		$text = 'Some text';
		$empty_result = $method->invoke( $this->ajax, $text, '' );
		$this->assertEquals( $text, $empty_result );

		$null_text = $method->invoke( $this->ajax, '', 'search' );
		$this->assertEquals( '', $null_text );
	}

	/**
	 * Test highlight_search_terms skips short terms
	 *
	 * @covers SearchWiz_Ajax::highlight_search_terms
	 */
	public function test_highlight_search_terms_skips_short_terms() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$reflection = new \ReflectionClass( $this->ajax );
		$method = $reflection->getMethod( 'highlight_search_terms' );
		$method->setAccessible( true );

		$text = 'A test of highlighting';
		$search_term = 'a'; // Single character should be skipped
		$result = $method->invoke( $this->ajax, $text, $search_term );

		// Should not highlight single 'a'
		$this->assertStringNotContainsString( '<mark>a</mark>', $result );
		$this->assertStringNotContainsString( '<mark>A</mark>', $result );
	}

	/**
	 * Test extend_search_to_comments returns original search if empty
	 *
	 * @covers SearchWiz_Ajax::extend_search_to_comments
	 */
	public function test_extend_search_to_comments_returns_original_if_empty() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$search = '';
		$query = Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_search' )->andReturn( true );

		$result = $this->ajax->extend_search_to_comments( $search, $query );

		$this->assertEquals( '', $result );
	}

	/**
	 * Test extend_search_to_comments returns original for non-search query
	 *
	 * @covers SearchWiz_Ajax::extend_search_to_comments
	 */
	public function test_extend_search_to_comments_skips_non_search() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$search = 'some search SQL';
		$query = Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_search' )->andReturn( false );

		$result = $this->ajax->extend_search_to_comments( $search, $query );

		$this->assertEquals( $search, $result );
	}

	/**
	 * Test join_comments_table returns original for non-search query
	 *
	 * @covers SearchWiz_Ajax::join_comments_table
	 */
	public function test_join_comments_table_skips_non_search() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$join = 'some JOIN SQL';
		$query = Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_search' )->andReturn( false );

		$result = $this->ajax->join_comments_table( $join, $query );

		$this->assertEquals( $join, $result );
	}

	/**
	 * Test join_comments_table adds comment join for search queries
	 *
	 * @covers SearchWiz_Ajax::join_comments_table
	 */
	public function test_join_comments_table_adds_join_for_search() {
		global $wpdb;
		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->comments = 'wp_comments';
		$wpdb->posts = 'wp_posts';

		$this->ajax = \SearchWiz_Ajax::getInstance();

		$join = '';
		$query = Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_search' )->andReturn( true );

		$result = $this->ajax->join_comments_table( $join, $query );

		$this->assertStringContainsString( 'wp_comments', $result );
		$this->assertStringContainsString( 'LEFT JOIN', $result );
	}

	/**
	 * Test group_by_post_id returns original for non-search query
	 *
	 * @covers SearchWiz_Ajax::group_by_post_id
	 */
	public function test_group_by_post_id_skips_non_search() {
		$this->ajax = \SearchWiz_Ajax::getInstance();

		$groupby = 'some GROUP BY SQL';
		$query = Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_search' )->andReturn( false );

		$result = $this->ajax->group_by_post_id( $groupby, $query );

		$this->assertEquals( $groupby, $result );
	}

	/**
	 * Test group_by_post_id groups by post ID for search queries
	 *
	 * @covers SearchWiz_Ajax::group_by_post_id
	 */
	public function test_group_by_post_id_groups_for_search() {
		global $wpdb;
		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->posts = 'wp_posts';

		$this->ajax = \SearchWiz_Ajax::getInstance();

		$groupby = '';
		$query = Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_search' )->andReturn( true );

		$result = $this->ajax->group_by_post_id( $groupby, $query );

		$this->assertEquals( 'wp_posts.ID', $result );
	}
}
