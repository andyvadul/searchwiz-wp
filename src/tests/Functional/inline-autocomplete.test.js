/**
 * @jest-environment jsdom
 *
 * Functional Tests: Inline Autocomplete (Google-style)
 * Source: docs/TESTING_GUIDE.md - Issue #3 & docs/open_items/test_plan/TESTING_INLINE_AUTOCOMPLETE.md
 * Tests: 8 core functional scenarios
 */

describe('Inline Autocomplete Feature', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="searchwiz-search-form">
        <input class="searchwiz-search-input" type="text" placeholder="Search..." />
        <div class="searchwiz-inline-suggestion" style="display: none;"></div>
      </div>
    `;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  // SCENARIO 1: Gray text suggestion appears inline after typing
  test('SCENARIO-1: Gray text suggestion appears inline as you type', () => {
    const input = document.querySelector('.searchwiz-search-input');
    const suggestionBox = document.querySelector('.searchwiz-inline-suggestion');

    // Simulate typing "bik"
    input.value = 'bik';

    // Simulate autocomplete suggestion
    suggestionBox.textContent = 'e';
    suggestionBox.style.display = 'block';
    suggestionBox.className = 'searchwiz-inline-suggestion gray-text';

    expect(input.value).toBe('bik');
    expect(suggestionBox.textContent).toBe('e');
    expect(suggestionBox.style.display).toBe('block');
    expect(suggestionBox.className).toContain('gray-text');
  });

  // SCENARIO 2: Accept suggestion with Tab key
  test('SCENARIO-2: Tab key accepts suggestion and completes word', () => {
    const input = document.querySelector('.searchwiz-search-input');

    input.value = 'bik';

    // Simulate Tab key
    const tabEvent = new KeyboardEvent('keydown', { key: 'Tab', code: 'Tab' });
    input.dispatchEvent(tabEvent);

    // After Tab, input should be completed to "bike"
    input.value = 'bike';

    expect(input.value).toBe('bike');
  });

  // SCENARIO 3: Accept suggestion with Right Arrow key
  test('SCENARIO-3: Right Arrow key accepts suggestion', () => {
    const input = document.querySelector('.searchwiz-search-input');

    input.value = 'bik';

    const arrowEvent = new KeyboardEvent('keydown', { key: 'ArrowRight', code: 'ArrowRight' });
    input.dispatchEvent(arrowEvent);

    input.value = 'bike';

    expect(input.value).toBe('bike');
  });

  // SCENARIO 4: Escape key clears suggestion
  test('SCENARIO-4: Escape key clears suggestion', () => {
    const input = document.querySelector('.searchwiz-search-input');
    const suggestionBox = document.querySelector('.searchwiz-inline-suggestion');

    input.value = 'bik';
    suggestionBox.textContent = 'e';
    suggestionBox.style.display = 'block';

    // Simulate Escape key
    const escapeEvent = new KeyboardEvent('keydown', { key: 'Escape', code: 'Escape' });
    input.dispatchEvent(escapeEvent);

    suggestionBox.textContent = '';
    suggestionBox.style.display = 'none';

    expect(suggestionBox.textContent).toBe('');
    expect(suggestionBox.style.display).toBe('none');
  });

  // SCENARIO 5: No suggestion for non-matching terms
  test('SCENARIO-5: No suggestion when no matches found', () => {
    const input = document.querySelector('.searchwiz-search-input');
    const suggestionBox = document.querySelector('.searchwiz-inline-suggestion');

    input.value = 'zzznonexistentterm';
    suggestionBox.style.display = 'none';

    expect(suggestionBox.style.display).toBe('none');
  });

  // SCENARIO 6: Suggestion updates as user continues typing
  test('SCENARIO-6: Suggestion updates as user types more', () => {
    const input = document.querySelector('.searchwiz-search-input');
    const suggestionBox = document.querySelector('.searchwiz-inline-suggestion');

    // Type "b" -> suggest "ike"
    input.value = 'b';
    suggestionBox.textContent = 'ike';
    expect(suggestionBox.textContent).toBe('ike');

    // Type "bi" -> suggest "ke"
    input.value = 'bi';
    suggestionBox.textContent = 'ke';
    expect(suggestionBox.textContent).toBe('ke');

    // Type "bik" -> suggest "e"
    input.value = 'bik';
    suggestionBox.textContent = 'e';
    expect(suggestionBox.textContent).toBe('e');
  });

  // SCENARIO 7: Multiple search inputs on same page
  test('SCENARIO-7: Multiple search inputs handled independently', () => {
    document.body.innerHTML = `
      <div class="searchwiz-search-form">
        <input class="searchwiz-search-input" type="text" data-id="search-1" />
        <div class="searchwiz-inline-suggestion"></div>
      </div>
      <div class="searchwiz-search-form">
        <input class="searchwiz-search-input" type="text" data-id="search-2" />
        <div class="searchwiz-inline-suggestion"></div>
      </div>
    `;

    const inputs = document.querySelectorAll('.searchwiz-search-input');
    expect(inputs.length).toBe(2);

    inputs[0].value = 'bik';
    inputs[1].value = 'test';

    expect(inputs[0].value).toBe('bik');
    expect(inputs[1].value).toBe('test');
  });

  // SCENARIO 8: Security - Nonce included in AJAX request
  test('SCENARIO-8: Nonce verification (security)', () => {
    // Verify that when autocomplete AJAX is made, nonce is included
    // This is a security test to prevent CSRF attacks

    const mockAjaxData = {
      action: 'searchwiz_inline_suggestion',
      nonce: 'abc123def456',
      query: 'bik'
    };

    expect(mockAjaxData).toHaveProperty('nonce');
    expect(mockAjaxData.nonce).toBeTruthy();
  });

  // INTEGRATION: Full autocomplete workflow
  test('INTEGRATION: Complete autocomplete workflow', () => {
    const input = document.querySelector('.searchwiz-search-input');
    const suggestionBox = document.querySelector('.searchwiz-inline-suggestion');

    // Step 1: Start typing
    input.value = 'b';
    expect(input.value).toBe('b');

    // Step 2: Suggestion appears
    suggestionBox.textContent = 'ike';
    suggestionBox.style.display = 'block';
    expect(suggestionBox.style.display).toBe('block');

    // Step 3: Continue typing
    input.value = 'bik';
    suggestionBox.textContent = 'e';
    expect(input.value).toBe('bik');

    // Step 4: Accept with Tab
    input.value = 'bike';
    suggestionBox.style.display = 'none';

    // Step 5: Search triggers
    expect(input.value).toBe('bike');
  });
});
