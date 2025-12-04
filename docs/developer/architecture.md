# SearchWiz Architecture

Technical overview of the SearchWiz plugin architecture.

## Design Philosophy

- **React library reused from WordPress** - No bundled React, uses WordPress's `@wordpress/element`
- **No save buttons** - Settings save automatically on change
- **Theme integration** - Works out of the box with popular themes
- **Minimal user choices** - Sensible defaults, progressive disclosure
- **TDD approach** - Test-driven development with comprehensive coverage

## Directory Structure

```
searchwiz/
├── searchwiz.php              # Main plugin file
├── includes/                  # Core classes
│   ├── class-searchwiz.php    # Main plugin class
│   ├── class-sw-loader.php    # Action/filter loader
│   ├── class-sw-i18n.php      # Internationalization
│   └── class-sw-activator.php # Activation hooks
├── admin/                     # Admin interface
│   ├── class-sw-admin.php     # Admin controller
│   ├── class-sw-settings-fields.php
│   ├── js/                    # Admin JavaScript
│   ├── css/                   # Admin styles
│   └── partials/              # Admin templates
├── public/                    # Frontend
│   ├── class-sw-public.php    # Public controller
│   ├── class-sw-ajax.php      # Ajax handler
│   ├── class-sw-search-form.php
│   ├── js/                    # Public JavaScript
│   └── css/                   # Public styles
├── tests/                     # Test files
│   ├── unit/                  # PHPUnit tests
│   └── js/                    # Jest tests
└── languages/                 # Translation files
```

## Core Classes

### SearchWiz (Main Class)

The main plugin class initializes all components and manages the plugin lifecycle.

**Responsibilities:**
- Load dependencies
- Set locale
- Register admin and public hooks
- Manage plugin activation/deactivation

### SW_Loader

Maintains lists of all hooks registered by the plugin.

**Pattern:** Observer pattern via WordPress hooks

### SW_Admin

Handles all admin-side functionality:
- Settings pages
- Option management
- Admin assets

### SW_Public

Handles all frontend functionality:
- Search form rendering
- Results display
- Public assets

### SW_Ajax

Handles all Ajax requests:
- Search queries
- Autocomplete
- Result rendering

## Data Flow

### Search Request Flow

1. User types in search form
2. JavaScript sends Ajax request
3. `SW_Ajax` receives and validates request
4. Query built with WordPress `WP_Query`
5. Results formatted and returned as JSON
6. JavaScript renders results in dropdown

### Settings Flow

1. Admin changes setting in React component
2. Setting auto-saved via REST API
3. Options stored in `wp_options` table
4. Applied to search behavior immediately

## Hooks and Filters

### Actions

| Hook | Description |
|------|-------------|
| `searchwiz_before_search` | Before search query |
| `searchwiz_after_search` | After search query |
| `searchwiz_enqueue_scripts` | When scripts load |

### Filters

| Filter | Description |
|--------|-------------|
| `searchwiz_search_args` | Modify WP_Query args |
| `searchwiz_result_item` | Modify single result |
| `searchwiz_results` | Modify all results |
| `searchwiz_form_html` | Modify form output |

## JavaScript Architecture

### Modules

- `searchwiz-search.js` - Core search logic
- `searchwiz-ajax-search.js` - Ajax functionality
- `searchwiz-admin.js` - Admin interface (React)

### Events

Custom events for extensibility:
- `searchwiz:search:start`
- `searchwiz:search:complete`
- `searchwiz:result:click`

## Security

### Nonce Verification

All Ajax requests verify WordPress nonces:
```php
check_ajax_referer( 'searchwiz_nonce', 'nonce' );
```

### Data Sanitization

All input sanitized before use:
```php
$search_term = sanitize_text_field( $_POST['search'] );
```

### Output Escaping

All output properly escaped:
```php
echo esc_html( $title );
echo esc_url( $permalink );
```

## Database

SearchWiz uses WordPress options table:

| Option Name | Description |
|-------------|-------------|
| `searchwiz_settings` | Plugin settings array |
| `searchwiz_version` | Installed version |

No custom database tables are created.

## Testing Philosophy

### Unit Tests (PHPUnit)

- Test individual class methods in isolation
- Mock WordPress functions
- Target: 85%+ code coverage
- Run: `composer test`

### JavaScript Tests (Jest)

- Test React components and utilities
- Use Testing Library for component tests
- Run: `npm test`

### Code Coverage

Coverage reports generated on each test run:
- PHP: `tests/coverage/`
- JS: `coverage/`

