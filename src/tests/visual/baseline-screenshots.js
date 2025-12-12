/**
 * Baseline Screenshots for CSS Validation
 * Captures desktop and mobile views of SearchWiz functionality
 *
 * OUTPUT: docs/screenshots/baseline/*-{desktop|mobile}.png
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'https://wp-dev-683-php84.searchwiz.ai';
const SCREENSHOT_DIR = path.join(__dirname, '../../docs/screenshots/baseline');

// Ensure screenshot directory exists
if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

/**
 * Screen dimensions for testing
 */
const VIEWPORTS = {
  desktop: { width: 1920, height: 1080 },
  mobile: { width: 375, height: 667 },
};

/**
 * Scenarios to capture
 */
const SCENARIOS = [
  {
    name: 'shop-page-empty',
    description: 'Shop page with empty search box',
    url: `${BASE_URL}/shop/`,
    wait: 2000, // Wait for page load
    beforeCapture: async (page) => {
      // Just wait for page to be ready
      await page.waitForSelector('[data-searchwiz-search]', { timeout: 5000 }).catch(() => null);
    },
  },
  {
    name: 'search-results-modal',
    description: 'Search results modal when searching for "bike"',
    url: `${BASE_URL}/shop/`,
    wait: 1000,
    beforeCapture: async (page) => {
      // Wait for search box and type "bike"
      await page.waitForSelector('[data-searchwiz-search]', { timeout: 5000 }).catch(() => null);
      await page.fill('[data-searchwiz-search]', 'bike');
      // Wait for results to load
      await page.waitForSelector('[data-searchwiz-results]', { timeout: 10000 }).catch(() => null);
      await new Promise(r => setTimeout(r, 1500)); // Wait for animation
    },
  },
  {
    name: 'search-results-with-products',
    description: 'Search results showing products with details',
    url: `${BASE_URL}/shop/`,
    wait: 1000,
    beforeCapture: async (page) => {
      // Wait for search box and type "bike"
      await page.waitForSelector('[data-searchwiz-search]', { timeout: 5000 }).catch(() => null);
      await page.fill('[data-searchwiz-search]', 'bike');
      // Wait for results to load
      await page.waitForSelector('[data-searchwiz-results]', { timeout: 10000 }).catch(() => null);
      await new Promise(r => setTimeout(r, 2000));
      // Scroll down to see more results
      await page.evaluate(() => {
        const resultsPanel = document.querySelector('[data-searchwiz-results]');
        if (resultsPanel) {
          resultsPanel.scrollTop = resultsPanel.scrollHeight / 2;
        }
      });
    },
  },
  {
    name: 'autocomplete-suggestion',
    description: 'Inline autocomplete suggestion while typing',
    url: `${BASE_URL}/shop/`,
    wait: 1000,
    beforeCapture: async (page) => {
      // Wait for search box
      await page.waitForSelector('[data-searchwiz-search]', { timeout: 5000 }).catch(() => null);
      // Type partial query to trigger autocomplete
      await page.fill('[data-searchwiz-search]', 'bik');
      // Wait for autocomplete to appear
      await new Promise(r => setTimeout(r, 1500));
    },
  },
  {
    name: 'customizer-settings',
    description: 'SearchWiz customizer panel (admin)',
    url: `${BASE_URL}/wp-admin/customize.php`,
    wait: 3000,
    beforeCapture: async (page) => {
      // Wait for customizer to load
      await page.waitForSelector('.customize-section', { timeout: 10000 }).catch(() => null);
      // Look for SearchWiz section - click if exists
      const searchWizSection = await page.$('[id*="searchwiz"], [id*="search_wiz"]').catch(() => null);
      if (searchWizSection) {
        await searchWizSection.click().catch(() => null);
        await new Promise(r => setTimeout(r, 1000));
      }
    },
  },
];

/**
 * Capture screenshots for a given scenario and viewport
 */
async function captureScreenshot(browser, scenario, viewportName, viewport) {
  let page;
  try {
    page = await browser.newPage();
    await page.setViewport(viewport);

    console.log(`  📸 ${viewportName}: ${scenario.name}`);

    // Navigate to URL
    try {
      await page.goto(scenario.url, { waitUntil: 'networkidle2', timeout: 30000 });
    } catch (err) {
      console.warn(`    ⚠️  Navigation timeout - proceeding anyway`);
    }

    // Wait initial time for page load
    await new Promise(r => setTimeout(r, scenario.wait));

    // Run pre-capture actions
    if (scenario.beforeCapture) {
      try {
        await scenario.beforeCapture(page);
      } catch (err) {
        console.warn(`    ⚠️  Pre-capture action failed: ${err.message}`);
      }
    }

    // Take screenshot
    const filename = `${scenario.name}-${viewportName}.png`;
    const filepath = path.join(SCREENSHOT_DIR, filename);

    await page.screenshot({ path: filepath, fullPage: false });
    console.log(`    ✅ Saved: ${filename}`);

    return true;
  } catch (err) {
    console.error(`    ❌ Failed: ${err.message}`);
    return false;
  } finally {
    if (page) {
      await page.close();
    }
  }
}

/**
 * Main execution
 */
async function main() {
  console.log('🎬 Capturing SearchWiz Baseline Screenshots\n');
  console.log(`📍 Test Site: ${BASE_URL}`);
  console.log(`💾 Output: ${SCREENSHOT_DIR}\n`);

  let browser;
  try {
    browser = await puppeteer.launch({ headless: true });

    for (const scenario of SCENARIOS) {
      console.log(`📋 ${scenario.description}`);

      // Capture desktop version
      await captureScreenshot(browser, scenario, 'desktop', VIEWPORTS.desktop);

      // Capture mobile version
      await captureScreenshot(browser, scenario, 'mobile', VIEWPORTS.mobile);

      console.log();
    }

    console.log('✅ Baseline screenshots complete!');
    console.log(`📁 Location: ${SCREENSHOT_DIR}\n`);

    // List captured files
    const files = fs.readdirSync(SCREENSHOT_DIR).sort();
    console.log('📸 Captured files:');
    files.forEach(file => {
      const filepath = path.join(SCREENSHOT_DIR, file);
      const stats = fs.statSync(filepath);
      const sizeKB = (stats.size / 1024).toFixed(1);
      console.log(`   • ${file} (${sizeKB} KB)`);
    });

  } catch (err) {
    console.error(`❌ Fatal error: ${err.message}`);
    process.exit(1);
  } finally {
    if (browser) {
      await browser.close();
    }
  }
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
