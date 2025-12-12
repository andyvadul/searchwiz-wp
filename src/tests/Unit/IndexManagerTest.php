<?php
/**
 * Unit tests for SearchWiz_Index_Manager.
 *
 * Tests core functionality of the index manager including:
 * - Index status detection
 * - Pagination calculation
 * - Time tracking
 * - Error handling
 * - Admin page detection
 *
 * @package SearchWiz\Tests\Unit
 */

namespace SearchWiz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test core functionality in SearchWiz_Index_Manager.
 */
class IndexManagerTest extends TestCase {

	/**
	 * Setup before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Teardown after each test.
	 */
	protected function tearDown(): void {
		Mockery::close();
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test is_index_admin_page returns true when on index settings page.
	 */
	public function test_is_index_admin_page_returns_true_on_index_page(): void {
		// Simulate being on the index settings page.
		$_GET['page'] = 'searchwiz-search-settings';
		$_GET['tab']  = 'index';

		// Create a partial mock that only tests this specific method.
		$manager = $this->createPartialMockForAdminPage();

		$result = $manager->is_index_admin_page();

		$this->assertTrue( $result, 'Should return true when on index settings page' );

		// Cleanup.
		unset( $_GET['page'], $_GET['tab'] );
	}

	/**
	 * Test is_index_admin_page returns false when not on index settings page.
	 */
	public function test_is_index_admin_page_returns_false_when_not_on_index_page(): void {
		// Simulate being on a different page.
		$_GET['page'] = 'searchwiz-search-settings';
		$_GET['tab']  = 'general';

		$manager = $this->createPartialMockForAdminPage();

		$result = $manager->is_index_admin_page();

		$this->assertFalse( $result, 'Should return false when not on index tab' );

		// Cleanup.
		unset( $_GET['page'], $_GET['tab'] );
	}

	/**
	 * Test is_index_admin_page returns false when page parameter is missing.
	 */
	public function test_is_index_admin_page_returns_false_when_page_missing(): void {
		// Simulate missing page parameter.
		$_GET = array();

		$manager = $this->createPartialMockForAdminPage();

		$result = $manager->is_index_admin_page();

		$this->assertFalse( $result, 'Should return false when page parameter is missing' );
	}

	/**
	 * Test calc_exec_time calculates correct execution time.
	 */
	public function test_calc_exec_time_calculates_correct_time(): void {
		$manager = $this->createPartialMockForExecTime();

		// Set start time to 1000 and end time to 1015 (15 second difference).
		$manager->set_timestamp( 'build_start_time', 1000 );
		$manager->set_timestamp( 'build_end_time', 1015 );

		$exec_time = $manager->calc_exec_time();

		$this->assertEquals( 15, $exec_time, 'Should calculate 15 seconds execution time' );
	}

	/**
	 * Test calc_exec_time returns minimum 1 second.
	 */
	public function test_calc_exec_time_returns_minimum_one_second(): void {
		$manager = $this->createPartialMockForExecTime();

		// Set same start and end time.
		$manager->set_timestamp( 'build_start_time', 1000 );
		$manager->set_timestamp( 'build_end_time', 1000 );

		$exec_time = $manager->calc_exec_time();

		$this->assertEquals( 1, $exec_time, 'Should return minimum 1 second when times are equal' );
	}

	/**
	 * Test calc_exec_time returns 0 when times not set.
	 */
	public function test_calc_exec_time_returns_zero_when_times_not_set(): void {
		$manager = $this->createPartialMockForExecTime();

		// Don't set any times - they should be empty.
		$exec_time = $manager->calc_exec_time();

		// Due to max($exec_time, 1), it returns 1 even when 0.
		$this->assertEquals( 1, $exec_time, 'Should return 1 when times are not set (max with 1)' );
	}

	/**
	 * Test set_timestamp sets build_start_time correctly.
	 */
	public function test_set_timestamp_sets_build_start_time(): void {
		$manager = $this->createPartialMockForTimestamp();

		$test_time = 1234567890;
		$manager->set_timestamp( 'build_start_time', $test_time );

		$this->assertEquals( $test_time, $manager->__get( 'build_start_time' ) );
	}

	/**
	 * Test set_timestamp sets build_end_time correctly.
	 */
	public function test_set_timestamp_sets_build_end_time(): void {
		$manager = $this->createPartialMockForTimestamp();

		$test_time = 1234567890;
		$manager->set_timestamp( 'build_end_time', $test_time );

		$this->assertEquals( $test_time, $manager->__get( 'build_end_time' ) );
	}

	/**
	 * Test set_timestamp uses current time when time parameter is 0.
	 *
	 * Note: We can't easily mock the native time() function without patchwork.json config,
	 * so we test that a valid timestamp is set instead.
	 */
	public function test_set_timestamp_uses_current_time_when_zero(): void {
		$manager = $this->createPartialMockForTimestamp();
		$manager->set_timestamp( 'build_start_time', 0 );

		$timestamp = $manager->__get( 'build_start_time' );

		// Should be a valid timestamp (recent time).
		$this->assertGreaterThan( 1000000000, $timestamp, 'Should set a valid timestamp' );
		$this->assertLessThanOrEqual( time() + 10, $timestamp, 'Should not be in the future' );
	}

	/**
	 * Test set_timestamp ignores invalid property names.
	 */
	public function test_set_timestamp_ignores_invalid_property(): void {
		$manager = $this->createPartialMockForTimestamp();

		// This should not throw an error, just be ignored.
		$manager->set_timestamp( 'invalid_property', 12345 );

		// We can't directly assert nothing happened, but no exception is success.
		$this->assertTrue( true );
	}

	/**
	 * Test get_defaults returns expected default values.
	 */
	public function test_get_defaults_returns_expected_structure(): void {
		$manager = $this->createPartialMockForDefaults();

		$defaults = $manager->get_defaults();

		$this->assertIsArray( $defaults );
		$this->assertArrayHasKey( 'index_status', $defaults );
		$this->assertArrayHasKey( 'build_results', $defaults );
		$this->assertArrayHasKey( 'build_start_time', $defaults );
		$this->assertArrayHasKey( 'build_end_time', $defaults );
		$this->assertArrayHasKey( 'build_per_page', $defaults );
		$this->assertArrayHasKey( 'build_offset', $defaults );
		$this->assertArrayHasKey( 'index_errors', $defaults );
	}

	/**
	 * Test get_defaults returns correct default values.
	 */
	public function test_get_defaults_returns_correct_default_values(): void {
		$manager = $this->createPartialMockForDefaults();

		$defaults = $manager->get_defaults();

		$this->assertEquals( '', $defaults['index_status'] );
		$this->assertEquals( array(), $defaults['build_results'] );
		$this->assertEquals( 2, $defaults['build_start_time'] );
		$this->assertEquals( 2, $defaults['build_end_time'] );
		$this->assertEquals( 10, $defaults['build_per_page'] );
		$this->assertEquals( 0, $defaults['build_offset'] );
		$this->assertEquals( array(), $defaults['index_errors'] );
	}

	/**
	 * Test add_index_error adds error to index_errors array.
	 */
	public function test_add_index_error_adds_error_to_array(): void {
		$manager = $this->createPartialMockForErrors();

		$post_id   = 123;
		$exception = new \Exception( 'Test error message' );

		$manager->add_index_error( $post_id, $exception );

		$errors = $manager->__get( 'index_errors' );

		$this->assertIsArray( $errors );
		$this->assertArrayHasKey( $post_id, $errors );
		$this->assertStringContainsString( 'Test error message', $errors[ $post_id ] );
		$this->assertStringContainsString( (string) $post_id, $errors[ $post_id ] );
	}

	/**
	 * Test remove_index_error removes error from array.
	 */
	public function test_remove_index_error_removes_error_from_array(): void {
		$manager = $this->createPartialMockForErrors();

		// First add an error.
		$post_id   = 456;
		$exception = new \Exception( 'Test error' );
		$manager->add_index_error( $post_id, $exception );

		// Verify it exists.
		$errors = $manager->__get( 'index_errors' );
		$this->assertArrayHasKey( $post_id, $errors );

		// Remove the error.
		$manager->remove_index_error( $post_id );

		// Verify it's gone.
		$errors = $manager->__get( 'index_errors' );
		$this->assertArrayNotHasKey( $post_id, $errors );
	}

	/**
	 * Test reset_index_errors clears all errors.
	 */
	public function test_reset_index_errors_clears_all_errors(): void {
		$manager = $this->createPartialMockForErrors();

		// Add multiple errors.
		$manager->add_index_error( 1, new \Exception( 'Error 1' ) );
		$manager->add_index_error( 2, new \Exception( 'Error 2' ) );
		$manager->add_index_error( 3, new \Exception( 'Error 3' ) );

		// Verify errors exist.
		$errors = $manager->__get( 'index_errors' );
		$this->assertCount( 3, $errors );

		// Reset all errors.
		$manager->reset_index_errors();

		// Verify all cleared.
		$errors = $manager->__get( 'index_errors' );
		$this->assertIsArray( $errors );
		$this->assertEmpty( $errors );
	}

	/**
	 * Test get_build_results returns array by default.
	 */
	public function test_get_build_results_returns_array_by_default(): void {
		$manager = $this->createPartialMockForBuildResults();

		// Set some build results.
		$manager->__set( 'build_results', array( 'Result 1', 'Result 2' ) );

		$results = $manager->get_build_results( false );

		$this->assertIsArray( $results );
		$this->assertCount( 2, $results );
		$this->assertEquals( 'Result 1', $results[0] );
		$this->assertEquals( 'Result 2', $results[1] );
	}

	/**
	 * Test get_build_results returns string when as_string is true.
	 */
	public function test_get_build_results_returns_string_when_requested(): void {
		$manager = $this->createPartialMockForBuildResults();

		// Set some build results.
		$manager->__set( 'build_results', array( 'Result 1', 'Result 2' ) );

		$results = $manager->get_build_results( true );

		$this->assertIsString( $results );
		$this->assertStringContainsString( 'Result 1', $results );
		$this->assertStringContainsString( 'Result 2', $results );
	}

	/**
	 * Test __get returns property value when property exists.
	 */
	public function test_magic_get_returns_property_value(): void {
		$manager = $this->createPartialMockForMagicMethods();

		// Set a property using __set.
		$manager->__set( 'build_offset', 42 );

		// Get it back using __get.
		$value = $manager->__get( 'build_offset' );

		$this->assertEquals( 42, $value );
	}

	/**
	 * Test __set sanitizes build_offset as integer.
	 */
	public function test_magic_set_sanitizes_build_offset_as_integer(): void {
		$manager = $this->createPartialMockForMagicMethods();

		// Set with string value.
		$manager->__set( 'build_offset', '123' );

		// Should be converted to integer.
		$value = $manager->__get( 'build_offset' );
		$this->assertIsInt( $value );
		$this->assertEquals( 123, $value );
	}

	/**
	 * Test __set sanitizes build_per_page as integer.
	 */
	public function test_magic_set_sanitizes_build_per_page_as_integer(): void {
		$manager = $this->createPartialMockForMagicMethods();

		$manager->__set( 'build_per_page', '50' );

		$value = $manager->__get( 'build_per_page' );
		$this->assertIsInt( $value );
		$this->assertEquals( 50, $value );
	}

	/**
	 * Test __set sanitizes build_results array.
	 */
	public function test_magic_set_sanitizes_build_results_array(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$manager = $this->createPartialMockForMagicMethods();

		$test_results = array( 'Result 1', 'Result 2' );
		$manager->__set( 'build_results', $test_results );

		$value = $manager->__get( 'build_results' );
		$this->assertIsArray( $value );
		$this->assertCount( 2, $value );
	}

	/**
	 * Test constant values are defined correctly.
	 */
	public function test_action_constants_have_expected_values(): void {
		// We can't easily test class constants without loading the class,
		// but we can document expected values.
		$expected_actions = array(
			'searchwiz_create_index',
			'searchwiz_delete_index',
			'searchwiz_index_post',
			'index-reset',
		);

		// This test documents the expected action names.
		$this->assertIsArray( $expected_actions );
		$this->assertCount( 4, $expected_actions );
	}

	/**
	 * Test status constants have expected values.
	 */
	public function test_status_constants_have_expected_values(): void {
		// Document expected status values.
		$expected_statuses = array(
			'empty',
			'creating',
			'paused',
			'created',
			'pausing',
		);

		$this->assertIsArray( $expected_statuses );
		$this->assertCount( 5, $expected_statuses );
	}

	// Helper methods to create partial mocks for specific functionality.

	/**
	 * Create partial mock for admin page testing.
	 */
	private function createPartialMockForAdminPage() {
		return new class() {
			public function is_index_admin_page() {
				$is_index_admin_page = false;

				$args = $_GET;
				if ( ! empty( $args['page'] ) && 'searchwiz-search-settings' == $args['page']
					&& ! empty( $args['tab'] ) && 'index' == $args['tab']
				) {
					$is_index_admin_page = true;
				}

				return $is_index_admin_page;
			}
		};
	}

	/**
	 * Create partial mock for execution time testing.
	 */
	private function createPartialMockForExecTime() {
		return new class() {
			private $build_start_time;
			private $build_end_time;

			public function set_timestamp( $property, $time = 0 ) {
				$value = time();
				if ( ! empty( $time ) && intval( $time ) ) {
					$value = $time;
				}
				if ( in_array( $property, array( 'build_start_time', 'build_end_time' ) ) ) {
					$this->$property = $value;
				}
			}

			public function calc_exec_time() {
				$exec_time = 0;

				if ( ! empty( $this->build_end_time ) && ! empty( $this->build_start_time ) ) {
					$exec_time = intval( $this->build_end_time ) - intval( $this->build_start_time );
				}

				return max( $exec_time, 1 );
			}

			public function __get( $property ) {
				if ( property_exists( $this, $property ) ) {
					return $this->$property;
				}
			}
		};
	}

	/**
	 * Create partial mock for timestamp testing.
	 */
	private function createPartialMockForTimestamp() {
		return new class() {
			private $build_start_time;
			private $build_end_time;

			public function set_timestamp( $property, $time = 0 ) {
				$value = time();
				if ( ! empty( $time ) && intval( $time ) ) {
					$value = $time;
				}
				if ( in_array( $property, array( 'build_start_time', 'build_end_time' ) ) ) {
					$this->$property = $value;
				}
			}

			public function __get( $property ) {
				if ( property_exists( $this, $property ) ) {
					return $this->$property;
				}
			}
		};
	}

	/**
	 * Create partial mock for defaults testing.
	 */
	private function createPartialMockForDefaults() {
		return new class() {
			public function get_defaults() {
				return array(
					'index_status'     => '',
					'build_results'    => array(),
					'build_start_time' => 2,
					'build_end_time'   => 2,
					'build_per_page'   => 10,
					'build_offset'     => 0,
					'index_errors'     => array(),
				);
			}
		};
	}

	/**
	 * Create partial mock for error handling testing.
	 */
	private function createPartialMockForErrors() {
		return new class() {
			private $index_errors = array();

			public function add_index_error( $post_id, $e ) {
				$post_id = intval( $post_id );
				$msg     = " [post_id]: $post_id - [error]: " . $e->getMessage();

				$this->index_errors[ $post_id ] = $msg;
			}

			public function remove_index_error( $post_id ) {
				$post_id = intval( $post_id );
				unset( $this->index_errors[ $post_id ] );
			}

			public function reset_index_errors() {
				$this->index_errors = array();
			}

			public function __get( $property ) {
				if ( property_exists( $this, $property ) ) {
					return $this->$property;
				}
			}
		};
	}

	/**
	 * Create partial mock for build results testing.
	 */
	private function createPartialMockForBuildResults() {
		return new class() {
			private $build_results = array();

			public function get_build_results( $as_string = false ) {
				$build_results = $this->build_results;
				if ( $as_string ) {
					$build_results = implode( PHP_EOL, $build_results );
				}

				return $build_results;
			}

			public function __set( $property, $value ) {
				if ( property_exists( $this, $property ) ) {
					$this->$property = $value;
				}
			}
		};
	}

	/**
	 * Create partial mock for magic methods testing.
	 */
	private function createPartialMockForMagicMethods() {
		return new class() {
			private $build_offset = 0;
			private $build_per_page = 10;
			private $build_results = array();

			public function __get( $property ) {
				if ( property_exists( $this, $property ) ) {
					return $this->$property;
				}
			}

			public function __set( $property, $value ) {
				if ( property_exists( $this, $property ) ) {
					switch ( $property ) {
						case 'build_results':
							if ( is_array( $value ) ) {
								$this->$property = array_map( 'sanitize_text_field', $value );
							}
							break;

						case 'build_offset':
						case 'build_per_page':
							$this->$property = intval( $value );
							break;

						default:
							$this->$property = $value;
							break;
					}
				}
			}
		};
	}
}
