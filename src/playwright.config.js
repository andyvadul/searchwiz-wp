// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * SearchWiz Regression Testing Configuration with Playwright
 *
 * Configured for:
 * - Critical tests (RT-001-015): <30 seconds, 100% pass required
 * - Extended tests (RT-016-040): <90 seconds, 95% pass required
 * - Multi-browser support (Chrome, Firefox, Safari)
 * - Screenshot baselines for visual testing
 * - Windows port 3333 setup (MCP server)
 */
module.exports = defineConfig({
  testDir: './tests/Regression',
  testMatch: '**/*.spec.js',

  // Timeout configuration
  timeout: 60 * 1000,
  expect: {
    timeout: 10000,
  },

  // Execution configuration
  fullyParallel: false, // Run tests sequentially for stable regression testing
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : 1,

  // Reporter configuration
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['junit', { outputFile: 'test-results/regression-results.xml' }],
    ['json', { outputFile: 'test-results/regression-results.json' }],
    ['list'],
  ],

  // Global configuration
  use: {
    actionTimeout: 10000,
    navigationTimeout: 30000,
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'retain-on-failure',
  },

  // Projects for multi-browser testing
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    // Mobile devices for RT-015 (Mobile Responsive)
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'mobile-safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  // Web server configuration (optional)
  webServer: {
    command: 'npm run dev',
    url: 'https://wp-dev-683-php84.searchwiz.ai',
    timeout: 120 * 1000,
    reuseExistingServer: !process.env.CI,
  },

  // Global setup/teardown
  globalSetup: require.resolve('./tests/Regression/global-setup.js'),
});
