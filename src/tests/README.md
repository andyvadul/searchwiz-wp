# SearchWiz Test Suite

Comprehensive test coverage for the SearchWiz WordPress plugin, including unit tests, functional tests, and regression tests.

## Test Statistics


### Code Coverage

![Overall Coverage](https://img.shields.io/badge/coverage-18%25-red)
![Lines](https://img.shields.io/badge/lines-22%25-brightgreen)
![Statements](https://img.shields.io/badge/statements-22%25-brightgreen)
![Functions](https://img.shields.io/badge/functions-17%25-brightgreen)
![Branches](https://img.shields.io/badge/branches-12%25-brightgreen)

**Overall Coverage:** 18%
- Lines: 22%
- Statements: 22%
- Functions: 17%
- Branches: 12%

### Test Count

- **Unit Tests (JavaScript):**        9
- **Unit Tests (PHP):**        6
- **Functional/Regression Tests:**        2
- **Total Tests:** 17

### Latest Build

- **Build Number:** local
- **Commit:** 
- **Updated:** 2025-12-17 19:22:44


## Directory Structure



## Test Types

### Unit Tests (JavaScript - 110 tests)

JavaScript unit tests using Jest, covering:
- Component logic
- Utility functions
- API interactions
- State management

Run with: 

### Unit Tests (PHP - 80 tests)

PHP unit tests using PHPUnit, covering:
- Core plugin functionality
- Settings and options
- Database interactions
- Hook implementations

Run with: 

### Functional & Regression Tests (Automated)

Comprehensive integration tests ensuring:
- Feature functionality across different scenarios
- No regressions between releases
- WordPress compatibility
- Performance benchmarks

## Running Tests Locally

### JavaScript Tests


up to date, audited 1514 packages in 3s

272 packages are looking for funding
  run `npm fund` for details

14 vulnerabilities (3 low, 3 moderate, 8 high)

To address issues that do not require attention, run:
  npm audit fix

To address all issues (including breaking changes), run:
  npm audit fix --force

Run `npm audit` for details.

> searchwiz@1.0.0 test
> wp-scripts test-unit-js --config tests/jest.config.js

### PHP Tests



### All Tests


> searchwiz@1.0.0 build
> npm run build:react && npm run build:css && npm run build:js


> searchwiz@1.0.0 build:react
> wp-scripts build react/index.js --output-path=public/dist

assets by path *.css 3.89 KiB
  asset index-rtl.css 1.95 KiB [emitted] (name: index)
  asset index.css 1.94 KiB [emitted] (name: index)
asset index.js 17.3 KiB [emitted] [minimized] (name: index)
asset index.asset.php 116 bytes [emitted] (name: index)
Entrypoint index 21.3 KiB = index.css 1.94 KiB index.js 17.3 KiB index-rtl.css 1.95 KiB index.asset.php 116 bytes
orphan modules 43.5 KiB (javascript) 1.83 KiB (runtime) [orphan] 22 modules
built modules 39.4 KiB (javascript) 1.94 KiB (css/mini-extract) [built]
  ./react/index.js + 8 modules 39.4 KiB [not cacheable] [built] [code generated]
  css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[1].use[2]!./react/styles/app.css 1.35 KiB [built] [code generated]
  css ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[1].use[2]!./react/components/LoadingSpinner.css 606 bytes [built] [code generated]
webpack 5.102.1 compiled successfully in 348 ms

> searchwiz@1.0.0 build:css
> cleancss -o admin/css/searchwiz-admin.min.css admin/css/searchwiz-admin.css && cleancss -o public/css/searchwiz-ajax-search.min.css public/css/searchwiz-ajax-search.css && cleancss -o public/css/searchwiz-search.min.css public/css/searchwiz-search.css


> searchwiz@1.0.0 build:js
> terser admin/js/searchwiz-admin.js -o admin/js/searchwiz-admin.min.js -c -m && terser public/js/searchwiz-ajax-search.js -o public/js/searchwiz-ajax-search.min.js -c -m && terser public/js/searchwiz-search.js -o public/js/searchwiz-search.min.js -c -m && terser public/js/sw-highlight.js -o public/js/sw-highlight.min.js -c -m


> searchwiz@1.0.0 test
> wp-scripts test-unit-js --config tests/jest.config.js

## CI/CD Integration

Tests automatically run on:
- Pull requests to master
- Pushes to master branch
- Scheduled nightly runs
- Manual workflow dispatch

Coverage reports are generated and published with each build.

## Coverage Goals

- **Target:** 85%+ code coverage
- **Minimum:** 70% code coverage
- **Critical paths:** 100% coverage required

## Quality Metrics

These tests ensure:
✓ Code quality and maintainability
✓ Feature reliability across versions
✓ WordPress compatibility (6.6+)
✓ PHP compatibility (8.0+)
✓ Performance benchmarks met
✓ No breaking changes between releases

---

*Last updated: 2025-12-17 19:22:44*
*Build #local | Coverage: 18%*

