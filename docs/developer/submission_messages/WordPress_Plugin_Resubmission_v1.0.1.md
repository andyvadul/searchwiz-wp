# SearchWiz Plugin Resubmission - v1.0.1

**Date:** December 7, 2025
**Submission File:** `build/searchwiz-v1.0.1.zip`
**Previous Review Date:** December 1, 2025

---

## Summary

Thank you for your detailed review of SearchWiz. We have addressed all four issues identified in your feedback. Below is a comprehensive breakdown of each issue and the corresponding fix.

---

## Issue 1: Hardcoded Ajax Endpoints

**Original Concern:**
> The built JavaScript file contained hardcoded fallback URLs for AJAX endpoints, which violates WordPress.org guidelines requiring all URLs to be dynamically generated via `wp_localize_script()`.

**Location:** `assets/build/index.js`

**Fix Applied:**
Rebuilt the minified JavaScript to remove all hardcoded fallback URLs. The code now requires `window.searchwizConfig.ajaxUrl` to be set via `wp_localize_script()` and displays a console error if not properly configured.

**Before:**
```javascript
const config = window.searchwizConfig || {
    restUrl: "/wp-json/searchwiz/v1/",
    nonce: "",
    ajaxUrl: "/wp-admin/admin-ajax.php"
};
```

**After:**
```javascript
const config = window.searchwizConfig || {};
if (!config.ajaxUrl) {
    console.error('SearchWiz: ajaxUrl not configured. Please ensure wp_localize_script is properly set up.');
    return;
}
```

**Files Changed:**
- `assets/build/index.js` - Rebuilt from source
- `assets/src/index.js` - Source file (already correct, no hardcoded URLs)

---

## Issue 2: GitHub Repository Not Accessible

**Original Concern:**
> The GitHub repository URL in readme.txt pointed to a private repository, making source code unavailable for review.

**Location:** `readme.txt`

**Fix Applied:**
Updated the readme.txt to point to the public repository.

**Before:**
```
https://github.com/andyvadul/search_wiz
```

**After:**
```
https://github.com/andyvadul/searchwiz-wp
```

**Files Changed:**
- `readme.txt` (line 16)

**Verification:**
The public repository is accessible at: https://github.com/andyvadul/searchwiz-wp

---

## Issue 3: CSS Variables Not Properly Escaped

**Original Concern:**
> Color values from settings were output directly into inline CSS without proper sanitization, creating potential security vulnerabilities.

**Location:** `public/class-sw-public.php`

**Fix Applied:**
Wrapped all color values with `sanitize_hex_color()` and added null checks before output.

**Before:**
```php
$css .= "--searchwiz-primary-color: {$settings['primary_color']} !important;";
```

**After:**
```php
$color = sanitize_hex_color( $settings['primary_color'] );
if ( $color ) {
    $css .= "--searchwiz-primary-color: {$color} !important;";
}
```

**Files Changed:**
- `public/class-sw-public.php`
  - `generate_custom_css()` method - fixed `primary_color`, `border_color`, `focus_color`
  - `get_menu_style_css()` method - fixed `menu_magnifier_color`

---

## Issue 4: Generic 'is' Prefix Usage

**Original Concern:**
> The code used a hardcoded string `'is_search_form'` instead of the class constant, which could cause issues and violates the plugin's own naming conventions.

**Location:** `includes/class-sw-search-form.php`

**Fix Applied:**
Replaced the hardcoded string with the class constant reference.

**Before:**
```php
get_page_by_path( 'default-search-form', OBJECT, 'is_search_form' )
```

**After:**
```php
get_page_by_path( 'default-search-form', OBJECT, self::post_type )
```

**Files Changed:**
- `includes/class-sw-search-form.php` (line 215)

---

## Testing Performed

1. **Automated Testing:** All CI workflows passing (PHP Lint, PHPCS, JavaScript Lint, CSS Lint, PHPUnit)
2. **Manual Testing:** Verified on WordPress 6.8 with PHP 8.2
3. **Security Review:** Confirmed all user inputs are properly sanitized before output

---

## Commits Reference

All fixes are available in the public repository:
- Repository: https://github.com/andyvadul/searchwiz-wp
- Branch: master

---

## Contact

If you have any questions about these fixes or need additional information, please contact:
- Email: andy@searchwiz.ai
- GitHub: https://github.com/andyvadul/searchwiz-wp/issues

Thank you for your time and consideration.
