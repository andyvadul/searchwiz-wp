<?php
/**
 * Sample unit test to verify PHPUnit setup.
 *
 * @package SearchWiz\Tests\Unit
 */

namespace SearchWiz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Sample test class.
 */
class SampleTest extends TestCase {

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
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test that PHPUnit is working.
     */
    public function test_phpunit_is_working(): void {
        $this->assertTrue( true );
    }

    /**
     * Test basic arithmetic (placeholder for real tests).
     */
    public function test_basic_arithmetic(): void {
        $this->assertEquals( 4, 2 + 2 );
    }

    /**
     * Test that Brain Monkey can mock WordPress functions.
     */
    public function test_brain_monkey_can_mock_wp_functions(): void {
        // Mock the WordPress esc_html function.
        Functions\when( 'esc_html' )->returnArg( 1 );

        $result = esc_html( '<script>alert("test")</script>' );

        $this->assertEquals( '<script>alert("test")</script>', $result );
    }

    /**
     * Test that we can mock plugin options.
     */
    public function test_can_mock_get_option(): void {
        // Mock get_option to return test data.
        Functions\when( 'get_option' )->justReturn(
            array(
                'auto_index_enabled' => 1,
                'index_title'        => 1,
                'index_content'      => 1,
            )
        );

        $options = get_option( 'sw_index' );

        $this->assertIsArray( $options );
        $this->assertEquals( 1, $options['auto_index_enabled'] );
    }
}
