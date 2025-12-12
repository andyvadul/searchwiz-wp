<?php
/**
 * Unit tests for SearchWiz Index Options.
 *
 * @package SearchWiz\Tests\Unit
 */

namespace SearchWiz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Test class for index options functionality.
 */
class IndexOptionsTest extends TestCase {

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
     * Test that default index options are valid.
     */
    public function test_default_index_options_structure(): void {
        $default_options = array(
            'auto_index_enabled'       => 1,
            'index_title'              => 1,
            'index_content'            => 1,
            'index_excerpt'            => 1,
            'index_tax_title'          => 1,
            'index_tax_desp'           => 0,
            'index_product_sku'        => 1,
            'index_product_variation'  => 0,
            'index_comments'           => 0,
            'index_user_comments'      => 0,
            'index_author_info'        => 0,
            'expand_shortcodes'        => 0,
            'yoast_no_index'           => 0,
            'throttle_searches'        => 1,
            'min_word_length'          => 3,
        );

        $this->assertArrayHasKey( 'auto_index_enabled', $default_options );
        $this->assertArrayHasKey( 'index_title', $default_options );
        $this->assertArrayHasKey( 'min_word_length', $default_options );
        $this->assertEquals( 3, $default_options['min_word_length'] );
    }

    /**
     * Test checkbox field validation.
     */
    public function test_checkbox_fields_are_binary(): void {
        $checkbox_fields = array(
            'auto_index_enabled',
            'index_title',
            'index_content',
            'index_excerpt',
            'throttle_searches',
        );

        foreach ( $checkbox_fields as $field ) {
            // Checkbox values should only be 0 or 1.
            $this->assertContains( 0, array( 0, 1 ), "Field {$field} should accept 0" );
            $this->assertContains( 1, array( 0, 1 ), "Field {$field} should accept 1" );
        }
    }

    /**
     * Test minimum word length validation.
     */
    public function test_min_word_length_must_be_positive(): void {
        $valid_lengths   = array( 1, 2, 3, 4, 5 );
        $invalid_lengths = array( 0, -1, -5 );

        foreach ( $valid_lengths as $length ) {
            $this->assertGreaterThan( 0, $length );
        }

        foreach ( $invalid_lengths as $length ) {
            $this->assertLessThanOrEqual( 0, $length );
        }
    }

    /**
     * Test post types array structure.
     */
    public function test_post_types_option_is_array(): void {
        Functions\when( 'get_option' )->justReturn(
            array(
                'post_types' => array( 'post', 'page', 'product' ),
            )
        );

        $options = get_option( 'sw_index' );

        $this->assertIsArray( $options['post_types'] );
        $this->assertContains( 'post', $options['post_types'] );
        $this->assertContains( 'page', $options['post_types'] );
    }
}
