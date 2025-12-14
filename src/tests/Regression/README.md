# SearchWiz Regression Test Suite with Playwright

**Complete implementation of 40 regression tests (RT-001 through RT-040) using Playwright.**

---

## Overview

This regression test suite validates SearchWiz functionality across:
- **Search functionality** (basic, infinite scroll, autocomplete, highlighting)
- **Admin interface** (page load, navigation, persistence, form fields)
- **WooCommerce integration** (products, metadata, layout)
- **Display customization** (colors, toggles, layout options, responsiveness)
- **Edge cases** (special characters, long queries, rapid input, caching)
- **Performance** (load times, memory, DOM size, network requests)
- **Security** (XSS protection, error recovery, session stability)
- **Reliability** (console errors, timeout handling)

---

## Test Structure

```
tests/Regression/
├── README.md                    # This file
├── playwright.config.js         # Playwright configuration
├── test-helpers.js              # Shared utilities and fixtures
├── global-setup.js              # Global setup before all tests
├── critical-tests.spec.js       # Critical tests (RT-001-015) ⛔ Must pass 100%
└── extended-tests.spec.js       # Extended tests (RT-016-040) ⚠️ 95% pass allowed
```

---

## Test Categories

### Critical Tests (RT-001 through RT-015)
**Requirement: 100% pass rate - blocks deployment on failure**
**Target: <30 seconds**

#### Search Functionality (4 tests)
- **RT-001**: Basic search returns results
- **RT-002**: Infinite scroll loads more results
- **RT-003**: Autocomplete suggests completions
- **RT-004**: Search terms highlighted in results

#### Admin Interface (4 tests)
- **RT-005**: Admin pages load without errors
- **RT-006**: Tab navigation works correctly
- **RT-007**: Settings changes persist
- **RT-008**: Form fields visible and functional

#### WooCommerce Integration (3 tests)
- **RT-009**: Products display in results
- **RT-010**: Product metadata visible (price, stock)
- **RT-011**: Multi-column layout displays correctly

#### Display Customization (4 tests)
- **RT-012**: Color settings apply
- **RT-013**: Display toggles work
- **RT-014**: Layout switching functions
- **RT-015**: Mobile responsive design

### Extended Tests (RT-016 through RT-040)
**Requirement: 95% pass rate - alerts on failure, allows 1 failure for investigation**
**Target: <90 seconds**

#### Edge Cases (10 tests)
- **RT-016**: Empty results handling
- **RT-017**: Special character handling
- **RT-018**: Long query handling
- **RT-019**: Rapid typing input
- **RT-020**: Browser back/forward navigation
- **RT-021**: Cache invalidation
- **RT-022**: Multiple consecutive searches
- **RT-023**: Search clear function
- **RT-024**: Page refresh stability
- **RT-025**: Scroll stability

#### Performance & Compatibility (10 tests)
- **RT-026**: Page load performance (<5s)
- **RT-027**: Search performance (<3s)
- **RT-028**: Memory stability
- **RT-029**: DOM size reasonable
- **RT-030**: Network requests minimal
- **RT-031**: Large result sets
- **RT-032**: Missing image handling
- **RT-033**: Filter functionality
- **RT-034**: Concurrent operations
- **RT-035**: Data consistency

#### Security & Reliability (5 tests)
- **RT-036**: XSS protection
- **RT-037**: Error recovery
- **RT-038**: Session stability
- **RT-039**: No console errors
- **RT-040**: Timeout handling

---

## Quick Start

### Installation

```bash
cd src/searchwiz

# Install Playwright (already in devDependencies)
npm install

# Install browsers
npx playwright install
```

### Running Tests

```bash
# Run all regression tests (critical + extended)
npm run test:regression

# Run only critical tests (RT-001-015)
npm run test:regression:critical

# Run only extended tests (RT-016-040)
npm run test:regression:extended

# Debug mode with inspector
npm run test:regression:debug

# UI mode (visual test runner)
npm run test:regression:ui

# View HTML report
npm run test:regression:report
```

---

## Environment Configuration

### Test URL

Tests run against: `https://wp-dev-683-php84.searchwiz.ai`

To use a different URL, set environment variable:
```bash
TEST_URL=https://your-site.local npm run test:regression
```

### WordPress Credentials

For admin login tests, set:
```bash
WP_PASSWORD=your_password npm run test:regression
```

---

## Test Helpers

Shared utilities in `test-helpers.js`:

```javascript
// Navigation
navigateToShop(page)          // Go to shop and wait for search
navigateToSettings(page, tab) // Go to admin settings (frontend/backend)

// Search operations
performSearch(page, query)    // Search and wait for results
getSearchResults(page)        // Get array of result elements

// Element checks
isElementVisible(page, selector)
waitForElement(page, selector)
hasClass(page, selector, className)

// Interactions
clickWithRetry(page, selector)
toggleCheckbox(page, selector)
setColorPickerValue(page, selector, color)

// Utilities
getPerformanceMetrics(page)
getComputedStyle(page, selector, property)
formatDuration(ms)
```

---

## Test Execution Flow

### Before All Tests
- `global-setup.js` displays test banner
- Verifies environment and test URL
- Logs test category information

### During Tests
- Each test logs execution time in milliseconds
- Tests run sequentially (not parallel) for stability
- Failures capture screenshots and videos
- Test traces retained for investigation

### Test Output

```
✓ RT-001 (1.23s) - Basic search returned 12 results
✓ RT-002 (2.45s) - Loaded 5 additional results via scroll
✓ RT-003 (0.89s) - Autocomplete suggestion detected
...
✓ All 15 Critical Regression Tests (RT-001-015) Executed
✓ Test Categories: Search (4), Admin (4), WooCommerce (3), Display (4)
```

---

## GitHub Actions Integration

The test suite integrates with `.github/workflows/regression-tests.yml`:

### On Every Build Success
```yaml
- Critical tests run (15 tests, ~30 seconds)
- 100% pass required
- Blocks deployment on failure
```

### Daily at 9 AM UTC
```yaml
- All tests run (40 tests, ~2 minutes)
- 95% pass required
- Alerts on failure but allows deployment
```

---

## Debugging Tests

### Run Specific Test
```bash
npx playwright test tests/Regression/critical-tests.spec.js -g "RT-001"
```

### Run with Inspector
```bash
npm run test:regression:debug
```

### Run with UI Mode
```bash
npm run test:regression:ui
```

### Check for Failures
```bash
npm run test:regression:report
```

---

## Adding New Tests

### Test Template

```javascript
test('RT-XXX: Test Name - Description of what it validates', async ({
  page,
}) => {
  const startTime = Date.now();

  // Setup
  await navigateToShop(page);

  // Action
  const results = await performSearch(page, 'query');

  // Assertion
  expect(results.length).toBeGreaterThan(0);

  // Log result
  console.log(`✓ RT-XXX (${formatDuration(Date.now() - startTime)}) - Success message`);
});
```

### Required Elements
1. **Test ID**: `RT-###` (e.g., RT-041)
2. **Description**: What the test validates
3. **Setup**: Navigate or prepare state
4. **Action**: Perform the operation
5. **Assertion**: Verify the result
6. **Logging**: Include duration and details

---

## Common Issues & Solutions

### Issue: Tests Timeout

**Cause**: Site is slow or unresponsive
**Solution**:
```bash
# Increase timeout
npx playwright test --timeout 60000
```

### Issue: "Search input not found"

**Cause**: Selectors don't match your DOM
**Solution**: Update selectors in `test-helpers.js`
```javascript
// Update search input selector
const searchInput = await page.$('[your-custom-selector]');
```

### Issue: Memory/Performance Warnings

**Cause**: Browser cache or background processes
**Solution**: Close other tabs, clear cache, or increase memory

### Issue: Mobile tests failing

**Cause**: Responsive behavior differs
**Solution**: Check viewport sizes in playwright.config.js

---

## Performance Targets

### Critical Tests
- **Target**: <30 seconds
- **Maximum**: 45 seconds
- **Failure action**: Block deployment

### Extended Tests
- **Target**: <90 seconds
- **Maximum**: 120 seconds
- **Failure action**: Alert, investigate, decide on deployment

### Individual Test Targets
- Navigation: <1 second
- Search: <2 seconds
- Admin: <1 second
- Form interaction: <500ms

---

## Maintenance

### Weekly
- Review test results
- Check for flaky tests (inconsistent failures)
- Update baselines if intentional changes made

### Monthly
- Update Playwright version: `npm update @playwright/test`
- Review and optimize slow tests
- Clean up old test artifacts

### Quarterly
- Review test coverage
- Add tests for new features
- Optimize test parallelization

---

## Success Criteria

| Metric | Target | Current |
|--------|--------|---------|
| Critical tests passing | 100% | ✅ |
| Extended tests passing | 95% | ✅ |
| Average execution time | <30s critical, <90s extended | ✅ |
| Mobile tests passing | 100% | ✅ |
| Admin tests passing | 100% | ✅ |
| WooCommerce tests passing | 100% | ✅ |

---

## Team Commands Reference

### For DevOps/CI
```bash
# Run in CI/CD pipeline
npm run test:regression
npm run test:regression:critical

# View results
npm run test:regression:report
```

### For QA/Testers
```bash
# Manual testing
npm run test:regression:ui

# Debug specific test
npm run test:regression:debug
```

### For Developers
```bash
# During development
npm run test:regression:critical

# Before commit
npm run test:regression
```

---

## Resources

- **Playwright Documentation**: https://playwright.dev
- **Test Plan**: `../../REGRESSION_TEST_PLAN.md`
- **DevOps Guide**: `../../DEVOPS_INTEGRATION_GUIDE.md`
- **Coverage Analysis**: `../../ADMIN_COVERAGE_ANALYSIS.md`

---

## Summary

✅ **40 Regression Tests Implemented**
- 15 Critical (RT-001-015): 100% required, <30s
- 25 Extended (RT-016-040): 95% required, <90s

✅ **Playwright Configuration**
- Multi-browser support (Chrome, Firefox, Safari)
- Mobile viewport testing
- Screenshot/video on failure
- HTML report generation

✅ **Helper Utilities**
- Navigation helpers
- Search helpers
- Element interaction helpers
- Performance helpers

✅ **Ready for CI/CD Integration**
- GitHub Actions workflow support
- JSON report output for metrics
- JUnit XML for CI systems
- HTML report for humans

**Status: Production Ready** 🚀

Generated: December 12, 2025
Test Agent - Playwright Implementation
