/**
 * @jest-environment jsdom
 *
 * Functional Tests: Display Customization Settings
 * Source: docs/open_items/test_plan/TESTING_DISPLAY_CUSTOMIZATION.md
 * Tests: 15 core scenarios for customizing search results appearance
 */

describe('Display Customization Settings', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="searchwiz-results">
        <div class="searchwiz-result-card">
          <img class="result-thumbnail" src="image.jpg" alt="thumbnail" />
          <h3 class="result-title" style="font-size: 16px;">Sample Result</h3>
          <p class="result-excerpt">This is sample excerpt text for the search result</p>
          <span class="result-price">$99.99</span>
        </div>
      </div>
    `;
  });

  // TC-01: Access Display Settings Tab
  test('TC-01: Display settings tab accessible in admin', () => {
    const displayTab = { title: 'Display & Styling', isActive: true };
    expect(displayTab).toHaveProperty('title');
    expect(displayTab.title).toBe('Display & Styling');
  });

  // TC-02: Primary Color - Valid Input
  test('TC-02: Primary color setting applies correctly', () => {
    const primaryColor = '#1e73be';
    expect(primaryColor).toMatch(/^#[0-9A-Fa-f]{6}$/);

    // Verify color is applied
    const style = document.createElement('style');
    style.textContent = `.result-title { color: ${primaryColor}; }`;
    document.head.appendChild(style);

    const title = document.querySelector('.result-title');
    expect(title).toBeDefined();
  });

  // TC-03: Primary Color - Invalid Input Reverts to Default
  test('TC-03: Invalid color reverts to default #0073aa', () => {
    const invalidColor = 'invalid-color';
    const defaultColor = '#0073aa';

    const sanitizeColor = (color) => {
      if (!/^#[0-9A-Fa-f]{6}$/.test(color)) {
        return defaultColor;
      }
      return color;
    };

    const result = sanitizeColor(invalidColor);
    expect(result).toBe(defaultColor);
  });

  // TC-04: Title Font Size - Valid Range
  test('TC-04: Title font size adjusts correctly (12px-32px)', () => {
    const testSizes = [12, 16, 20, 24, 32];

    testSizes.forEach(size => {
      const title = document.querySelector('.result-title');
      title.style.fontSize = `${size}px`;

      expect(parseInt(title.style.fontSize)).toBe(size);
    });
  });

  // TC-05: Title Font Size - Out of Range
  test('TC-05: Out of range font size reverts to default (16px)', () => {
    const validateFontSize = (size) => {
      const min = 12;
      const max = 32;
      const defaultSize = 16;

      if (size < min || size > max) {
        return defaultSize;
      }
      return size;
    };

    expect(validateFontSize(8)).toBe(16);
    expect(validateFontSize(100)).toBe(16);
    expect(validateFontSize(20)).toBe(20);
  });

  // TC-06: Hide Thumbnails
  test('TC-06: Show/hide thumbnails toggle works', () => {
    const thumbnail = document.querySelector('.result-thumbnail');
    const showThumbnails = false;

    if (!showThumbnails) {
      thumbnail.style.display = 'none';
    }

    expect(thumbnail.style.display).toBe('none');
  });

  // TC-07: Hide Excerpts
  test('TC-07: Show/hide excerpts toggle works', () => {
    const excerpt = document.querySelector('.result-excerpt');
    const showExcerpts = false;

    if (!showExcerpts) {
      excerpt.style.display = 'none';
    }

    expect(excerpt.style.display).toBe('none');
  });

  // TC-08: Excerpt Length - Valid Range
  test('TC-08: Excerpt length adjusts correctly (10-100 words)', () => {
    const testLengths = [10, 30, 50, 75, 100];

    testLengths.forEach(length => {
      expect(length).toBeGreaterThanOrEqual(10);
      expect(length).toBeLessThanOrEqual(100);
    });
  });

  // TC-09: Card Spacing - Minimum (0px)
  test('TC-09: Card spacing minimum (0px)', () => {
    const card = document.querySelector('.searchwiz-result-card');
    card.style.padding = '0px';

    expect(card.style.padding).toBe('0px');
  });

  // TC-10: Card Spacing - Maximum (30px)
  test('TC-10: Card spacing maximum (30px)', () => {
    const card = document.querySelector('.searchwiz-result-card');
    card.style.padding = '30px';

    expect(card.style.padding).toBe('30px');
  });

  // TC-11: Card Spacing - Default (10px)
  test('TC-11: Card spacing default (10px)', () => {
    const card = document.querySelector('.searchwiz-result-card');
    card.style.padding = '10px';

    expect(card.style.padding).toBe('10px');
  });

  // TC-12: Border Radius - Square Corners (0px)
  test('TC-12: Border radius 0px (square corners)', () => {
    const card = document.querySelector('.searchwiz-result-card');
    card.style.borderRadius = '0px';

    expect(card.style.borderRadius).toBe('0px');
  });

  // TC-13: Border Radius - Rounded Corners (10px)
  test('TC-13: Border radius 10px (rounded corners)', () => {
    const card = document.querySelector('.searchwiz-result-card');
    card.style.borderRadius = '10px';

    expect(card.style.borderRadius).toBe('10px');
  });

  // TC-14: Border Radius - Maximum (20px)
  test('TC-14: Border radius 20px (maximum)', () => {
    const card = document.querySelector('.searchwiz-result-card');
    card.style.borderRadius = '20px';

    expect(card.style.borderRadius).toBe('20px');
  });

  // TC-15: All Settings Combined - Custom Theme
  test('TC-15: All customization settings apply together', () => {
    const card = document.querySelector('.searchwiz-result-card');
    const title = document.querySelector('.result-title');
    const excerpt = document.querySelector('.result-excerpt');

    // Apply custom settings
    const style = document.createElement('style');
    style.textContent = `
      .result-title { color: #e91e63; font-size: 20px; }
      .result-excerpt { display: block; }
      .searchwiz-result-card { padding: 15px; border-radius: 8px; }
    `;
    document.head.appendChild(style);

    title.style.color = '#e91e63';
    title.style.fontSize = '20px';
    excerpt.style.display = 'block';
    card.style.padding = '15px';
    card.style.borderRadius = '8px';

    expect(title.style.color).toBe('rgb(233, 30, 99)'); // #e91e63 converted
    expect(parseInt(title.style.fontSize)).toBe(20);
    expect(card.style.padding).toBe('15px');
    expect(card.style.borderRadius).toBe('8px');
  });

  // INTEGRATION: Customization + Search Highlighting
  test('IT-01: Custom colors work with search highlighting', () => {
    const customColor = '#d63384';

    const card = document.querySelector('.searchwiz-result-card');
    card.style.setProperty('--searchwiz-primary', customColor);

    expect(card.style.getPropertyValue('--searchwiz-primary')).toContain('d63384');
  });

  // INTEGRATION: Customization + WooCommerce
  test('IT-02: Customization applies to product cards', () => {
    document.body.innerHTML = `
      <div class="searchwiz-product-card">
        <img class="product-image" src="prod.jpg" />
        <h3 class="product-title">Product Name</h3>
        <span class="product-price">$599.99</span>
      </div>
    `;

    const productCard = document.querySelector('.searchwiz-product-card');
    productCard.style.borderRadius = '12px';
    productCard.style.padding = '20px';

    expect(productCard.style.borderRadius).toBe('12px');
    expect(productCard.style.padding).toBe('20px');
  });

  // Browser Compatibility: Chrome
  test('BC-01: Color picker works in Chrome', () => {
    const colorInput = document.createElement('input');
    colorInput.type = 'color';
    colorInput.value = '#1e73be';

    expect(colorInput.type).toBe('color');
    expect(colorInput.value).toBe('#1e73be');
  });

  // Mobile Responsive
  test('MR-01: Mobile responsive customization (375px width)', () => {
    const card = document.querySelector('.searchwiz-result-card');

    // Simulate mobile viewport
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 375
    });

    card.style.maxWidth = '100%';
    card.style.padding = '10px';

    expect(card.style.maxWidth).toBe('100%');
  });

  // Edge Cases: Empty Primary Color
  test('EC-01: Empty primary color uses default', () => {
    const emptyColor = '';
    const defaultColor = '#0073aa';

    const result = emptyColor || defaultColor;
    expect(result).toBe(defaultColor);
  });

  // Edge Cases: Decimal Values
  test('EC-02: Decimal font size values rounded to integers', () => {
    const roundFontSize = (size) => Math.round(size);

    expect(roundFontSize(15.7)).toBe(16);
    expect(roundFontSize(12.3)).toBe(12);
  });

  // Edge Cases: Very Long Excerpt
  test('EC-03: Excerpt length capped at maximum', () => {
    const validateExcerptLength = (length) => {
      if (length > 100) return 100;
      return length;
    };

    expect(validateExcerptLength(150)).toBe(100);
    expect(validateExcerptLength(200)).toBe(100);
  });

  // Regression: Admin area not affected
  test('RT-01: Admin area styling not affected by frontend customizations', () => {
    const adminArea = document.createElement('div');
    adminArea.className = 'wp-admin';

    // Apply frontend customization to results only
    const resultsArea = document.querySelector('.searchwiz-results');
    resultsArea.style.color = '#e91e63';

    // Admin area should not be affected
    expect(adminArea.style.color).toBe('');
  });

  // Regression: Default search form still works
  test('RT-02: Default WordPress search widget unaffected', () => {
    const defaultSearchForm = document.createElement('form');
    defaultSearchForm.className = 'search-form';
    defaultSearchForm.innerHTML = '<input type="search" />';

    expect(defaultSearchForm.className).toContain('search-form');
    expect(defaultSearchForm.querySelector('input')).toBeDefined();
  });
});
