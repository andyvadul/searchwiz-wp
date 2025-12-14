/**
 * SearchWiz Regression Test Helpers
 * Common utilities and fixtures for all regression tests
 */

const TEST_URL = process.env.TEST_URL || 'https://wp-dev-683-php84.searchwiz.ai';
const ADMIN_URL = TEST_URL + '/wp-admin';
const SHOP_URL = TEST_URL + '/shop';

/**
 * Wait for element with optional timeout
 */
async function waitForElement(page, selector, timeout = 5000) {
  try {
    await page.waitForSelector(selector, { timeout });
    return await page.$(selector);
  } catch (e) {
    throw new Error(`Element not found: ${selector}`);
  }
}

/**
 * Login to WordPress admin
 */
async function loginToAdmin(page, username = 'admin', password = process.env.WP_PASSWORD || 'password') {
  await page.goto(ADMIN_URL);

  // Wait for login form
  await page.waitForSelector('input[name="log"]', { timeout: 10000 });

  // Enter credentials
  await page.fill('input[name="log"]', username);
  await page.fill('input[name="pwd"]', password);

  // Submit
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.click('input[type="submit"]'),
  ]);
}

/**
 * Navigate to SearchWiz settings page
 */
async function navigateToSettings(page, tab = 'frontend') {
  const pageParam = tab === 'frontend' ? 'searchwiz-search-frontend' : 'searchwiz-search-backend';
  await page.goto(`${ADMIN_URL}/admin.php?page=${pageParam}`);
  await page.waitForLoadState('networkidle');
}

/**
 * Navigate to shop and wait for search to load
 */
async function navigateToShop(page) {
  await page.goto(SHOP_URL);
  await page.waitForLoadState('domcontentloaded');

  // Wait for search widget to load
  await page.waitForSelector('[data-searchwiz-search], .searchwiz-search', {
    timeout: 10000,
  });
}

/**
 * Perform search and wait for results
 */
async function performSearch(page, query, resultSelector = '[data-searchwiz-results], .searchwiz-results') {
  // Click search input
  const searchInput = await page.$('[data-searchwiz-search] input, .searchwiz-search input');
  if (!searchInput) {
    throw new Error('Search input not found');
  }

  // Type query
  await searchInput.focus();
  await page.keyboard.type(query);

  // Wait for results
  await page.waitForSelector(resultSelector, { timeout: 10000 });

  return await page.locator(resultSelector).first();
}

/**
 * Get search result items
 */
async function getSearchResults(page, resultSelector = '[data-searchwiz-result-item], .searchwiz-result-item') {
  const results = await page.locator(resultSelector).all();
  return results;
}

/**
 * Wait for and check element visibility
 */
async function isElementVisible(page, selector, timeout = 5000) {
  try {
    await page.waitForSelector(selector, { timeout });
    const element = await page.locator(selector).first();
    return await element.isVisible();
  } catch {
    return false;
  }
}

/**
 * Get computed style of element
 */
async function getComputedStyle(page, selector, property) {
  return await page.locator(selector).first().evaluate((el, prop) => {
    return window.getComputedStyle(el).getPropertyValue(prop);
  }, property);
}

/**
 * Wait for and verify text content
 */
async function waitForText(page, selector, text, timeout = 5000) {
  await page.waitForSelector(selector, { timeout });
  await page.waitForFunction(
    (sel, txt) => document.querySelector(sel)?.textContent?.includes(txt),
    selector,
    text,
    { timeout }
  );
}

/**
 * Scroll element into view
 */
async function scrollToElement(page, selector) {
  await page.locator(selector).first().scrollIntoViewIfNeeded();
}

/**
 * Click element with retry
 */
async function clickWithRetry(page, selector, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      await page.locator(selector).first().click();
      return;
    } catch (e) {
      if (i === maxRetries - 1) throw e;
      await page.waitForTimeout(100);
    }
  }
}

/**
 * Get page performance metrics
 */
async function getPerformanceMetrics(page) {
  return await page.evaluate(() => ({
    loadTime: performance.getEntriesByType('navigation')[0]?.loadEventEnd || 0,
    domReady: performance.getEntriesByType('navigation')[0]?.domContentLoadedEventEnd || 0,
    firstPaint: performance.getEntriesByName('first-paint')[0]?.startTime || 0,
    firstContentfulPaint: performance.getEntriesByName('first-contentful-paint')[0]?.startTime || 0,
  }));
}

/**
 * Check if element has class
 */
async function hasClass(page, selector, className) {
  const element = await page.locator(selector).first();
  return await element.evaluate((el, cls) => el.classList.contains(cls), className);
}

/**
 * Set color picker value
 */
async function setColorPickerValue(page, selector, color) {
  const input = await page.locator(`${selector} input[type="color"]`).first();
  if (input) {
    await input.fill(color);
  } else {
    // Fallback for text input
    const textInput = await page.locator(`${selector} input[type="text"]`).first();
    if (textInput) {
      await textInput.clear();
      await textInput.fill(color);
    }
  }
}

/**
 * Toggle checkbox
 */
async function toggleCheckbox(page, selector) {
  const checkbox = await page.locator(`${selector} input[type="checkbox"]`).first();
  if (!checkbox) {
    throw new Error(`Checkbox not found: ${selector}`);
  }
  await checkbox.click();
}

/**
 * Get table cell value
 */
async function getTableCellValue(page, row, column) {
  const cell = await page.locator(`table tr:nth-child(${row}) td:nth-child(${column})`).first();
  return await cell.textContent();
}

/**
 * Wait for network idle
 */
async function waitForNetworkIdle(page, timeout = 5000) {
  await page.waitForLoadState('networkidle', { timeout });
}

/**
 * Format test duration
 */
function formatDuration(ms) {
  return `${(ms / 1000).toFixed(2)}s`;
}

module.exports = {
  TEST_URL,
  ADMIN_URL,
  SHOP_URL,
  waitForElement,
  loginToAdmin,
  navigateToSettings,
  navigateToShop,
  performSearch,
  getSearchResults,
  isElementVisible,
  getComputedStyle,
  waitForText,
  scrollToElement,
  clickWithRetry,
  getPerformanceMetrics,
  hasClass,
  setColorPickerValue,
  toggleCheckbox,
  getTableCellValue,
  waitForNetworkIdle,
  formatDuration,
};
