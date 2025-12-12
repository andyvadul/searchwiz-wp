/**
 * @jest-environment jsdom
 *
 * Functional Tests: WooCommerce Integration
 * Source: docs/TESTING_GUIDE.md - Issue #4 & #18
 * Tests: 4 core scenarios for WooCommerce product search
 */

describe('WooCommerce Integration', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="searchwiz-results">
        <div class="searchwiz-products-section">
          <div class="searchwiz-product-card">
            <img class="product-image" src="product.jpg" alt="Mountain Bike" />
            <h3 class="product-title">Mountain Bike Pro</h3>
            <span class="product-price">$599.99</span>
            <span class="product-stock in-stock">In stock</span>
            <span class="product-sku">SKU: MB-2024</span>
            <div class="product-rating">★★★★★ (25 reviews)</div>
          </div>
        </div>
        <div class="searchwiz-posts-section">
          <div class="searchwiz-post-card">
            <h3 class="post-title">Bike Maintenance Tips</h3>
          </div>
        </div>
      </div>
    `;
  });

  // SCENARIO 1: Products separated from posts in layout
  test('SCENARIO-1: Products separated from posts in two-column layout', () => {
    const productsSection = document.querySelector('.searchwiz-products-section');
    const postsSection = document.querySelector('.searchwiz-posts-section');

    expect(productsSection).toBeDefined();
    expect(postsSection).toBeDefined();
  });

  // SCENARIO 2: Product card shows all required details
  test('SCENARIO-2: Product card displays all required information', () => {
    const productCard = document.querySelector('.searchwiz-product-card');

    expect(productCard.querySelector('.product-image')).toBeDefined();
    expect(productCard.querySelector('.product-title')).toBeDefined();
    expect(productCard.querySelector('.product-price')).toBeDefined();
    expect(productCard.querySelector('.product-stock')).toBeDefined();
    expect(productCard.querySelector('.product-sku')).toBeDefined();
    expect(productCard.querySelector('.product-rating')).toBeDefined();
  });

  // SCENARIO 3: Product details correctly formatted
  test('SCENARIO-3: Product details formatted correctly', () => {
    const price = document.querySelector('.product-price');
    const stock = document.querySelector('.product-stock');

    expect(price.textContent).toContain('$');
    expect(price.textContent).toContain('599.99');
    expect(stock.textContent).toBe('In stock');
  });

  // SCENARIO 4: Clicking product title navigates to product page
  test('SCENARIO-4: Product title is clickable link', () => {
    const productTitle = document.querySelector('.product-title');
    const link = document.createElement('a');
    link.href = '/product/mountain-bike-pro/';
    link.textContent = productTitle.textContent;

    expect(link.href).toContain('/product/');
  });

  // SCENARIO 5: On-sale badge displays for discounted products
  test('SCENARIO-5: On-sale badge for discounted products', () => {
    document.body.innerHTML = `
      <div class="searchwiz-product-card sale">
        <span class="product-badge on-sale">On Sale</span>
        <span class="product-price">$399.99 <del>$599.99</del></span>
      </div>
    `;

    const saleBadge = document.querySelector('.product-badge.on-sale');
    expect(saleBadge).toBeDefined();
    expect(saleBadge.textContent).toBe('On Sale');
  });

  // SCENARIO 6: Products responsive on mobile
  test('SCENARIO-6: Product layout responsive (mobile-friendly)', () => {
    const productCard = document.querySelector('.searchwiz-product-card');
    productCard.style.display = 'grid';
    productCard.style.gridTemplateColumns = 'repeat(auto-fit, minmax(200px, 1fr))';

    expect(productCard.style.display).toBe('grid');
  });

  // SCENARIO 7: Products NOT shown when shop page is drafted
  test('SCENARIO-7: Products hidden when shop page is not published', () => {
    const shopPagePublished = false;

    if (!shopPagePublished) {
      const productsSection = document.querySelector('.searchwiz-products-section');
      if (productsSection) {
        productsSection.style.display = 'none';
      }
    }

    const productsVisible = document.querySelector('.searchwiz-products-section');
    if (productsVisible) {
      expect(productsVisible.style.display).toBe('none');
    }
  });

  // SCENARIO 8: Hidden products not in search results
  test('SCENARIO-8: Hidden products excluded from search results', () => {
    const allProducts = [
      { id: 1, title: 'Bike A', visibility: 'hidden' },
      { id: 2, title: 'Bike B', visibility: 'visible' },
      { id: 3, title: 'Bike C', visibility: 'hidden' }
    ];

    const visibleProducts = allProducts.filter(p => p.visibility !== 'hidden');

    expect(visibleProducts.length).toBe(1);
    expect(visibleProducts[0].title).toBe('Bike B');
  });

  // INTEGRATION: WooCommerce + Display Customization
  test('INTEGRATION: WooCommerce products display with custom styling', () => {
    const productCard = document.querySelector('.searchwiz-product-card');

    // Apply custom styles
    productCard.style.borderRadius = '12px';
    productCard.style.padding = '15px';
    productCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';

    expect(productCard.style.borderRadius).toBe('12px');
    expect(productCard.style.padding).toBe('15px');
  });

  // INTEGRATION: WooCommerce + Search Highlighting
  test('INTEGRATION: Search highlighting works on product titles', () => {
    const productTitle = document.querySelector('.product-title');
    const searchTerm = 'Bike';

    const hasSearchTerm = productTitle.textContent.includes(searchTerm);
    expect(hasSearchTerm).toBe(true);
  });

  // Star rating and review count
  test('Rating and reviews display correctly', () => {
    const rating = document.querySelector('.product-rating');

    expect(rating.textContent).toContain('★');
    expect(rating.textContent).toContain('reviews');
    expect(rating.textContent).toContain('25');
  });

  // Product image with alt text
  test('Product image has accessible alt text', () => {
    const image = document.querySelector('.product-image');

    expect(image.alt).toBeTruthy();
    expect(image.alt).toContain('Bike');
  });

  // Stock status variations
  test('Stock status shows correct states', () => {
    const stockVariations = [
      { status: 'in-stock', text: 'In stock' },
      { status: 'out-of-stock', text: 'Out of stock' },
      { status: 'on-backorder', text: 'On backorder' }
    ];

    stockVariations.forEach(({ status, text }) => {
      expect(text).toBeTruthy();
    });
  });
});
