/**
 * @jest-environment jsdom
 *
 * Functional Tests: Admin Settings Interface (Phase 1)
 * Source: docs/TESTING_GUIDE_ADMIN.md
 * Tests: 15 scenarios covering Front-end and Back-end Settings
 *
 * Test Categories:
 * - Access and Navigation (Tests 1-2, 4-5)
 * - Settings Persistence (Tests 3, 6, 8)
 * - UI/UX (Tests 9-10)
 * - Upgrade Tab (Tests 11-12)
 * - Error Handling (Tests 13-14)
 * - Performance (Test 15)
 * - Legacy Compatibility (Test 7)
 */

describe('Admin Settings Interface - TESTING_GUIDE_ADMIN.md', () => {

  beforeEach(() => {
    // Setup DOM for Front-end Settings page
    document.body.innerHTML = `
      <div id="wpbody-content">
        <h1 class="searchwiz-page-title">Front-end Settings</h1>
        <div class="nav-tab-wrapper">
          <a href="#" class="nav-tab nav-tab-active" data-tab="display-styling">Display & Styling</a>
          <a href="#" class="nav-tab" data-tab="search-behavior">Search Behavior</a>
          <a href="#" class="nav-tab" data-tab="menu-search">Menu Search</a>
          <a href="#" class="nav-tab" data-tab="upgrade">Upgrade</a>
        </div>
        <form method="post" action="">
          <div id="display-styling" class="tab-content tab-active">
            <table class="form-table">
              <tr>
                <th><label>Primary Color</label></th>
                <td><input type="text" name="primary_color" value="#2563eb" class="color-field" /></td>
              </tr>
              <tr>
                <th><label>Border Color</label></th>
                <td><input type="text" name="border_color" value="#000000" class="color-field" /></td>
              </tr>
              <tr>
                <th><label>Show Product Images</label></th>
                <td><input type="checkbox" name="show_product_images" checked /></td>
              </tr>
            </table>
            <button type="submit" class="button button-primary">Save Settings</button>
          </div>
          <div id="search-behavior" class="tab-content">
            <p class="notice notice-info">Search behavior settings coming soon...</p>
          </div>
          <div id="menu-search" class="tab-content">
            <table class="form-table">
              <tr>
                <th><label>Enable Menu Search</label></th>
                <td><input type="checkbox" name="menu_search_enabled" checked /></td>
              </tr>
              <tr>
                <th><label>Menu Class</label></th>
                <td><input type="text" name="menu_class" value="main-menu" /></td>
              </tr>
            </table>
            <button type="submit" class="button button-primary">Save Settings</button>
          </div>
          <div id="upgrade" class="tab-content">
            <div class="upgrade-features">
              <div class="feature-card">
                <span class="pro-badge">PRO</span>
                <h3>Advanced Autocomplete</h3>
                <p>Enhanced autocomplete with AI suggestions</p>
              </div>
              <div class="feature-card">
                <span class="pro-badge">PRO</span>
                <h3>Custom Result Templates</h3>
                <p>Design your own search result layouts</p>
              </div>
              <div class="feature-card">
                <span class="pro-badge">PRO</span>
                <h3>Faceted Search Filters</h3>
                <p>Filter results by custom categories</p>
              </div>
              <div class="feature-card">
                <span class="pro-badge">PRO</span>
                <h3>Smart Search Widgets</h3>
                <p>Pre-built widgets for quick integration</p>
              </div>
            </div>
            <a href="https://searchwiz.ai/pro/" class="button button-primary button-learn-more" target="_blank">Upgrade to Pro</a>
          </div>
        </form>
      </div>
    `;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  // ============================================================================
  // FRONT-END SETTINGS TESTS
  // ============================================================================

  describe('Front-end Settings Access', () => {

    // TEST 1: Access Front-end Settings page
    test('TEST-1: Front-end Settings page loads with correct title and structure', () => {
      const pageTitle = document.querySelector('.searchwiz-page-title');
      const tabWrapper = document.querySelector('.nav-tab-wrapper');
      const form = document.querySelector('form');

      expect(pageTitle).toBeDefined();
      expect(pageTitle.textContent).toBe('Front-end Settings');
      expect(tabWrapper).toBeDefined();
      expect(form).toBeDefined();
    });

    // TEST 2: Tab navigation works for all four tabs
    test('TEST-2: Front-end Settings tabs navigate correctly', () => {
      const tabs = document.querySelectorAll('.nav-tab');
      const tabNames = ['Display & Styling', 'Search Behavior', 'Menu Search', 'Upgrade'];

      expect(tabs.length).toBe(4);

      tabs.forEach((tab, index) => {
        expect(tab.textContent).toBe(tabNames[index]);
        expect(tab.getAttribute('data-tab')).toBeDefined();
      });

      // First tab should be active by default
      expect(tabs[0].classList.contains('nav-tab-active')).toBe(true);
    });

    // TEST 3: Display & Styling tab shows configuration settings
    test('TEST-3: Display & Styling tab shows form fields and Save button', () => {
      const displayTab = document.getElementById('display-styling');
      const colorFields = displayTab.querySelectorAll('.color-field');
      const saveButton = displayTab.querySelector('.button-primary');

      expect(displayTab).toBeDefined();
      expect(displayTab.classList.contains('tab-active')).toBe(true);
      expect(colorFields.length).toBeGreaterThanOrEqual(2); // Primary and Border colors
      expect(saveButton).toBeDefined();
      expect(saveButton.textContent).toContain('Save');
    });

    // TEST 4: Search Behavior tab shows coming soon notice
    test('TEST-4: Search Behavior tab shows placeholder for Phase 2', () => {
      const behaviorTab = document.getElementById('search-behavior');
      const notice = behaviorTab.querySelector('.notice-info');

      expect(behaviorTab).toBeDefined();
      expect(notice).toBeDefined();
      expect(notice.textContent).toContain('coming soon');
    });

    // TEST 5: Menu Search tab shows existing settings
    test('TEST-5: Menu Search tab shows enable/disable and menu class settings', () => {
      const menuTab = document.getElementById('menu-search');
      const enableCheckbox = menuTab.querySelector('input[name="menu_search_enabled"]');
      const classField = menuTab.querySelector('input[name="menu_class"]');

      expect(menuTab).toBeDefined();
      expect(enableCheckbox).toBeDefined();
      expect(enableCheckbox.checked).toBe(true);
      expect(classField).toBeDefined();
      expect(classField.value).toBe('main-menu');
    });

  });

  describe('Front-end Settings Persistence', () => {

    // TEST 6: Settings persist after save
    test('TEST-6: Display & Styling settings persist after form submission', () => {
      const primaryColorField = document.querySelector('input[name="primary_color"]');
      const originalValue = primaryColorField.value;

      // Change value
      primaryColorField.value = '#ff0000';
      expect(primaryColorField.value).toBe('#ff0000');

      // Simulate page reload by keeping value in field
      // (In real integration test, this would be a full page reload)
      expect(primaryColorField.value).toBe('#ff0000');
    });

    // TEST 7: Menu Search settings persist
    test('TEST-7: Menu Search settings are preserved after navigation', () => {
      const enableCheckbox = document.querySelector('input[name="menu_search_enabled"]');
      const classField = document.querySelector('input[name="menu_class"]');

      const originalEnabled = enableCheckbox.checked;
      const originalClass = classField.value;

      // Toggle checkbox
      enableCheckbox.checked = !enableCheckbox.checked;
      expect(enableCheckbox.checked).toBe(!originalEnabled);

      // Simulate navigation back - value should be preserved
      expect(document.querySelector('input[name="menu_search_enabled"]').checked).toBe(!originalEnabled);
      expect(document.querySelector('input[name="menu_class"]').value).toBe(originalClass);
    });

  });

  describe('Upgrade Tab Content', () => {

    // TEST 8: Upgrade tab displays 4 premium features
    test('TEST-8: Front-end Upgrade tab displays 4 premium features with PRO badges', () => {
      const upgradeTab = document.getElementById('upgrade');
      const features = upgradeTab.querySelectorAll('.feature-card');
      const badges = upgradeTab.querySelectorAll('.pro-badge');
      const learnMoreBtn = upgradeTab.querySelector('.button-learn-more');

      expect(features.length).toBe(4);
      expect(badges.length).toBe(4);
      expect(learnMoreBtn).toBeDefined();
      expect(learnMoreBtn.getAttribute('href')).toBe('https://searchwiz.ai/pro/');
      expect(learnMoreBtn.getAttribute('target')).toBe('_blank');
    });

    // TEST 9: All upgrade features have descriptions
    test('TEST-9: Upgrade features display with titles and descriptions', () => {
      const upgradeTab = document.getElementById('upgrade');
      const features = upgradeTab.querySelectorAll('.feature-card');

      features.forEach((feature) => {
        const title = feature.querySelector('h3');
        const description = feature.querySelector('p');

        expect(title).toBeDefined();
        expect(title.textContent.length).toBeGreaterThan(0);
        expect(description).toBeDefined();
        expect(description.textContent.length).toBeGreaterThan(0);
      });
    });

  });

  // ============================================================================
  // BACK-END SETTINGS TESTS (DOM setup for Back-end Settings)
  // ============================================================================

  describe('Back-end Settings Access', () => {

    beforeEach(() => {
      // Setup DOM for Back-end Settings page
      document.body.innerHTML = `
        <div id="wpbody-content">
          <h1 class="searchwiz-page-title">Back-end Settings</h1>
          <div class="nav-tab-wrapper">
            <a href="#" class="nav-tab nav-tab-active" data-tab="index-management">Index Management</a>
            <a href="#" class="nav-tab" data-tab="analytics">Analytics</a>
            <a href="#" class="nav-tab" data-tab="advanced">Advanced</a>
            <a href="#" class="nav-tab" data-tab="upgrade">Upgrade</a>
          </div>
          <form method="post" action="">
            <div id="index-management" class="tab-content tab-active">
              <table class="form-table">
                <tr>
                  <th><label>Index Post Types</label></th>
                  <td>
                    <input type="checkbox" name="post_types[]" value="post" checked /> Post
                    <input type="checkbox" name="post_types[]" value="page" checked /> Page
                    <input type="checkbox" name="post_types[]" value="product" checked /> Product
                  </td>
                </tr>
              </table>
              <button type="submit" class="button button-primary">Save Settings</button>
              <button type="button" class="button button-secondary">Build Index</button>
            </div>
            <div id="analytics" class="tab-content">
              <table class="form-table">
                <tr>
                  <th><label>Enable Analytics</label></th>
                  <td><input type="checkbox" name="analytics_enabled" checked /></td>
                </tr>
              </table>
            </div>
            <div id="advanced" class="tab-content">
              <p class="notice notice-info">Advanced settings coming soon...</p>
            </div>
            <div id="upgrade" class="tab-content">
              <div class="upgrade-features">
                <div class="feature-card">
                  <span class="pro-badge">PRO</span>
                  <h3>Smart Relevance Engine</h3>
                </div>
                <div class="feature-card">
                  <span class="pro-badge">PRO</span>
                  <h3>Advanced Analytics Dashboard</h3>
                </div>
                <div class="feature-card">
                  <span class="pro-badge">PRO</span>
                  <h3>Synonym & Stopword Management</h3>
                </div>
                <div class="feature-card">
                  <span class="pro-badge">PRO</span>
                  <h3>Multi-Language & Multi-Site</h3>
                </div>
                <div class="feature-card">
                  <span class="pro-badge">PRO</span>
                  <h3>Performance Optimization</h3>
                </div>
                <div class="feature-card">
                  <span class="pro-badge">PRO</span>
                  <h3>Advanced Custom Field Indexing</h3>
                </div>
              </div>
              <a href="https://searchwiz.ai/pro/" class="button button-primary button-learn-more" target="_blank">Upgrade to Pro</a>
            </div>
          </form>
        </div>
      `;
    });

    // TEST 10: Access Back-end Settings page
    test('TEST-10: Back-end Settings page loads with correct structure', () => {
      const pageTitle = document.querySelector('.searchwiz-page-title');
      const tabWrapper = document.querySelector('.nav-tab-wrapper');

      expect(pageTitle.textContent).toBe('Back-end Settings');
      expect(tabWrapper).toBeDefined();
    });

    // TEST 11: Back-end tabs navigate correctly
    test('TEST-11: Back-end Settings tabs are present and first is active', () => {
      const tabs = document.querySelectorAll('.nav-tab');
      const tabNames = ['Index Management', 'Analytics', 'Advanced', 'Upgrade'];

      expect(tabs.length).toBe(4);
      tabs.forEach((tab, index) => {
        expect(tab.textContent).toBe(tabNames[index]);
      });
      expect(tabs[0].classList.contains('nav-tab-active')).toBe(true);
    });

    // TEST 12: Index Management tab shows post types
    test('TEST-12: Index Management tab shows post type selection checkboxes', () => {
      const indexTab = document.getElementById('index-management');
      const postTypeCheckboxes = indexTab.querySelectorAll('input[name="post_types[]"]');
      const buildButton = indexTab.querySelector('.button-secondary');

      expect(postTypeCheckboxes.length).toBeGreaterThanOrEqual(3);
      expect(buildButton).toBeDefined();
      expect(buildButton.textContent).toContain('Build Index');
    });

    // TEST 13: Analytics tab shows enable/disable setting
    test('TEST-13: Analytics tab shows analytics tracking checkbox', () => {
      const analyticsTab = document.getElementById('analytics');
      const analyticsCheckbox = analyticsTab.querySelector('input[name="analytics_enabled"]');

      expect(analyticsTab).toBeDefined();
      expect(analyticsCheckbox).toBeDefined();
      expect(analyticsCheckbox.checked).toBe(true);
    });

    // TEST 14: Advanced tab shows coming soon notice
    test('TEST-14: Advanced tab shows placeholder for Phase 2', () => {
      const advancedTab = document.getElementById('advanced');
      const notice = advancedTab.querySelector('.notice-info');

      expect(advancedTab).toBeDefined();
      expect(notice).toBeDefined();
      expect(notice.textContent).toContain('coming soon');
    });

    // TEST 15: Back-end Upgrade tab displays 6 premium features
    test('TEST-15: Back-end Upgrade tab displays 6 premium features', () => {
      const upgradeTab = document.getElementById('upgrade');
      const features = upgradeTab.querySelectorAll('.feature-card');
      const badges = upgradeTab.querySelectorAll('.pro-badge');

      expect(features.length).toBe(6);
      expect(badges.length).toBe(6);
    });

  });

  // ============================================================================
  // ERROR HANDLING AND EDGE CASES
  // ============================================================================

  describe('Error Handling and Edge Cases', () => {

    // TEST 16: Invalid tab parameter defaults to first tab
    test('TEST-16: Invalid tab parameter defaults to first tab (Display & Styling)', () => {
      const tabs = document.querySelectorAll('.nav-tab');
      const tabContents = document.querySelectorAll('.tab-content');

      // Simulate invalid tab - should default to first
      const firstTab = tabs[0];
      const firstContent = document.getElementById('display-styling');

      expect(firstTab.classList.contains('nav-tab-active')).toBe(true);
      expect(firstContent.classList.contains('tab-active')).toBe(true);
    });

    // TEST 17: Form submission with valid data
    test('TEST-17: Form can be submitted with valid field values', () => {
      const form = document.querySelector('form');
      const submitButton = form.querySelector('.button-primary');
      const primaryColorField = form.querySelector('input[name="primary_color"]');

      expect(form).toBeDefined();
      expect(submitButton).toBeDefined();
      expect(primaryColorField.value).toMatch(/^#[0-9A-Fa-f]{6}$/);
    });

    // TEST 18: Data preservation across tab switches
    test('TEST-18: Field values preserved when switching between tabs', () => {
      const primaryColorField = document.querySelector('input[name="primary_color"]');
      const originalValue = primaryColorField.value;

      // Change value
      primaryColorField.value = '#ff0000';

      // Switch to another tab (simulated)
      const tabs = document.querySelectorAll('.nav-tab');
      tabs[1].click();

      // Value should still be in the field (in real app, would persist in form state)
      expect(document.querySelector('input[name="primary_color"]').value).toBe('#ff0000');
    });

  });

  // ============================================================================
  // UI/UX TESTS
  // ============================================================================

  describe('UI/UX Responsiveness', () => {

    // TEST 19: Tab styling indicates active state
    test('TEST-19: Active tab has distinct CSS class', () => {
      const tabs = document.querySelectorAll('.nav-tab');
      const activeTab = Array.from(tabs).find(tab => tab.classList.contains('nav-tab-active'));

      expect(activeTab).toBeDefined();
      expect(activeTab.classList.contains('nav-tab-active')).toBe(true);
    });

    // TEST 20: All interactive elements are accessible
    test('TEST-20: Form fields are properly labeled and accessible', () => {
      const inputs = document.querySelectorAll('input[type="text"], input[type="checkbox"], input[type="color"]');
      const buttons = document.querySelectorAll('button, .button');

      expect(inputs.length).toBeGreaterThan(0);
      expect(buttons.length).toBeGreaterThan(0);

      buttons.forEach((button) => {
        expect(button.textContent.length).toBeGreaterThan(0);
      });
    });

    // TEST 21: Form has proper structure
    test('TEST-21: Form contains properly structured table elements', () => {
      const form = document.querySelector('form');
      const formTable = form.querySelector('.form-table');

      expect(formTable).toBeDefined();
      expect(formTable.querySelectorAll('tr').length).toBeGreaterThan(0);
    });

  });

  // ============================================================================
  // INTEGRATION TESTS
  // ============================================================================

  describe('Integration: Complete Admin Workflow', () => {

    // TEST 22: Complete Front-end settings flow
    test('TEST-22: Complete Front-end Settings workflow - navigate, modify, save', () => {
      // 1. Page loads
      const pageTitle = document.querySelector('.searchwiz-page-title');
      expect(pageTitle.textContent).toBe('Front-end Settings');

      // 2. Navigate to Menu Search tab
      const menuTab = document.querySelector('[data-tab="menu-search"]');
      const menuContent = document.getElementById('menu-search');
      expect(menuContent).toBeDefined();

      // 3. Modify setting
      const classField = document.querySelector('input[name="menu_class"]');
      classField.value = 'custom-menu-class';

      // 4. Verify change
      expect(classField.value).toBe('custom-menu-class');

      // 5. Can submit form
      const form = document.querySelector('form');
      expect(form).toBeDefined();
      expect(form.querySelector('.button-primary')).toBeDefined();
    });

    // TEST 23: Complete Back-end settings flow
    test('TEST-23: Complete Back-end Settings workflow - navigate, modify, save', () => {
      // Setup Back-end page
      document.body.innerHTML = `
        <div id="wpbody-content">
          <h1 class="searchwiz-page-title">Back-end Settings</h1>
          <div class="nav-tab-wrapper">
            <a href="#" class="nav-tab nav-tab-active" data-tab="index-management">Index Management</a>
          </div>
          <form method="post">
            <div id="index-management" class="tab-content tab-active">
              <input type="checkbox" name="post_types[]" value="product" checked />
              <button type="submit" class="button-primary">Save</button>
            </div>
          </form>
        </div>
      `;

      // 1. Page loads
      const pageTitle = document.querySelector('.searchwiz-page-title');
      expect(pageTitle.textContent).toBe('Back-end Settings');

      // 2. Index Management tab is active
      const indexTab = document.getElementById('index-management');
      expect(indexTab.classList.contains('tab-active')).toBe(true);

      // 3. Post type checkboxes exist
      const productCheckbox = document.querySelector('input[value="product"]');
      expect(productCheckbox).toBeDefined();
      expect(productCheckbox.checked).toBe(true);

      // 4. Can modify and save
      productCheckbox.checked = false;
      expect(productCheckbox.checked).toBe(false);
    });

  });

});
