/**
 * Global Setup for Regression Tests
 * Runs once before all tests
 */

module.exports = async () => {
  console.log('\n📋 SearchWiz Regression Test Suite Starting');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

  const now = new Date();
  console.log(`⏰ Test Start: ${now.toISOString()}`);
  console.log(`🌐 Target URL: https://wp-dev-683-php84.searchwiz.ai`);
  console.log(`🎯 Test Categories: Search, Admin, WooCommerce, Display`);
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  // Verify environment
  const url = process.env.TEST_URL || 'https://wp-dev-683-php84.searchwiz.ai';
  console.log(`✓ Environment: ${process.env.CI ? 'CI' : 'Local'}`);
  console.log(`✓ Test URL: ${url}`);
  console.log(`✓ Parallel: ${process.env.CI ? 'No (CI mode)' : 'No (sequential)'}`);
};
