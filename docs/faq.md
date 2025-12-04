# SearchWiz FAQ

Frequently asked questions about SearchWiz.

## General Questions

### What is SearchWiz?

SearchWiz is a WordPress plugin that enhances your site's search functionality with instant Ajax results, search term highlighting, and customizable display options.

### Is SearchWiz free?

Yes, SearchWiz is free and open source, released under the GPL v2 license.

### What are the requirements?

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6+ or MariaDB 10.0+

## Compatibility

### Does SearchWiz work with my theme?

SearchWiz works with any properly coded WordPress theme. It has been tested with popular themes including:
- Storefront
- Astra
- GeneratePress
- OceanWP
- Twenty Twenty-Three
- Twenty Twenty-Four

### Does SearchWiz work with WooCommerce?

Yes! SearchWiz includes built-in WooCommerce support for enhanced product search.

### Does SearchWiz work with page builders?

SearchWiz is compatible with major page builders:
- Elementor
- Beaver Builder
- Divi
- WPBakery

Use the `[searchwiz_form]` shortcode to place search forms anywhere.

### Will SearchWiz conflict with other plugins?

SearchWiz is designed to be lightweight and conflict-free. If you experience issues, see our [Troubleshooting Guide](troubleshooting.md).

## Performance

### Will SearchWiz slow down my site?

No. SearchWiz is optimized for performance:
- Minimal CSS/JS footprint
- Assets load only when needed
- Optimized database queries
- Compatible with caching plugins

### Does SearchWiz work with caching plugins?

Yes, SearchWiz is compatible with popular caching plugins including WP Super Cache, W3 Total Cache, and WP Rocket.

## Features

### Can I customize the search results appearance?

Yes! Go to Settings → SearchWiz → Display to customize:
- Layout (list or grid)
- Show/hide featured images
- Excerpt length
- Show/hide dates and authors

### Can I exclude certain content from search?

Yes. Go to Settings → SearchWiz → Advanced → Exclusions to:
- Exclude specific categories
- Exclude specific tags
- Exclude certain post types

### Does SearchWiz support custom post types?

Yes. Go to Settings → SearchWiz → Advanced → Post Types to enable search for any registered custom post type.

### Can I add the search form to widgets?

Yes. Go to Appearance → Widgets and add the "SearchWiz Search" widget to any widget area.

## Technical

### Where is the plugin data stored?

SearchWiz stores settings in the WordPress options table. No external databases or services are required.

### Does SearchWiz make external requests?

No. SearchWiz operates entirely on your server. No data is sent to external services.

### Is SearchWiz translation-ready?

Yes. SearchWiz is fully internationalized and ready for translation. Translation files are in the `languages` folder.

### How do I report a bug?

Report bugs on our [GitHub repository](https://github.com/andyvadul/searchwiz-wp/issues).

## Shortcodes

### What shortcodes are available?

**Search Form:**
```
[searchwiz_form]
[searchwiz_form placeholder="Search..." button_text="Find"]
```

**Search Results:**
```
[searchwiz_results]
```

### Can I use shortcodes in page builders?

Yes, all page builders support shortcodes. Look for a "Shortcode" or "Text" widget/module.

