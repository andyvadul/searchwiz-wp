<?php
/**
 * Placeholder test to prevent PHPUnit from failing on empty Integration directory.
 *
 * This file exists because PHPUnit 10+ fails when a configured testsuite
 * directory contains no test files. Once real integration tests are added,
 * this file can be removed.
 *
 * @package SearchWiz\Tests\Integration
 */

namespace SearchWiz\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Placeholder test class.
 */
class PlaceholderTest extends TestCase {

	/**
	 * Placeholder test that always passes.
	 *
	 * @return void
	 */
	public function test_placeholder(): void {
		$this->markTestSkipped( 'Placeholder test - real integration tests coming soon.' );
	}
}
