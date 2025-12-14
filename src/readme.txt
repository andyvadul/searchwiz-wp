=== SearchWiz ===
Contributors: searchwiz, searchwizteam
Tags: search, ajax search, search form, custom search, search filter
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smart WordPress search with instant AJAX results and intelligent content discovery.

== Source Code ==

The full source code for all minified JavaScript and CSS files is available in the WordPress.org SVN repository:
https://plugins.svn.wordpress.org/searchwiz/trunk/

Build tools used: @wordpress/scripts (npm run build)

Minified files and their sources:
* public/dist/index.js - Built from react/index.js and react/components/
* public/js/searchwiz-*.min.js - Minified from public/js/searchwiz-*.js
* public/css/searchwiz-*.min.css - Minified from public/css/searchwiz-*.css
* admin/js/searchwiz-admin.min.js - Minified from admin/js/searchwiz-admin.js
* admin/css/searchwiz-admin.min.css - Minified from admin/css/searchwiz-admin.css

== Description ==

SearchWiz is a powerful, intelligent WordPress search plugin that transforms the default WordPress search into a modern, fast, and user-friendly search experience. It provides instant AJAX results, smart autocomplete, and seamless WooCommerce integration.

**Key Features:**

* **Instant AJAX Search** - See results as you type, with zero page refreshes
* **Smart Autocomplete** - Intelligent suggestions based on your content
* **WooCommerce Ready** - Complete support for product search with prices, images, and ratings
* **Easy Customization** - Use the WordPress Customizer to style your search results
* **High Performance** - Optimized for speed with intelligent indexing and caching
* **Multiple Layouts** - Grid, list, and card layouts to match your site design
* **Content Filtering** - Search by post type, category, taxonomy, and more
* **Developer Friendly** - Clean code with hooks and filters for customization

Perfect for:
- E-commerce stores running WooCommerce
- Content-heavy sites with large databases
- Knowledge bases and documentation sites
- News and magazine sites
- Any WordPress site that needs better search

**Why Choose SearchWiz?**

SearchWiz is built specifically for WordPress with a focus on performance, usability, and accessibility. Unlike bloated search plugins, SearchWiz is lightweight, fast, and easy to configure.

== Installation ==

**From WordPress.org Plugin Directory (Easiest):**
1. Go to Plugins → Add New
2. Search for "SearchWiz"
3. Click "Install Now"
4. Click "Activate"

**Manual Installation:**
1. Download the plugin zip file from WordPress.org
2. Upload to `/wp-content/plugins/` via FTP or File Manager
3. Activate the plugin through the Plugins menu

**Getting Started:**
1. After activation, go to Settings → SearchWiz
2. Configure your search preferences (content types, filters, display options)
3. Use the WordPress Customizer (Appearance → Customize → SearchWiz) to style your search results
4. Add the search widget to your sidebar or use shortcodes to display search on your pages

== Screenshots ==

1. AJAX-powered live search results appearing instantly as you type
2. Smart autocomplete suggestions based on your content
3. Customizer panel for styling your search results
4. WooCommerce product search integration

== Frequently Asked Questions ==

= Is SearchWiz free? =
Yes! The core SearchWiz plugin is completely free and available on WordPress.org.

= Does it work with WooCommerce? =
Absolutely! SearchWiz fully supports WooCommerce products in search results with complete product metadata (prices, ratings, images, and stock status).

= Can I customize the appearance? =
Yes! Use the WordPress Customizer (Appearance → Customize → SearchWiz) to change colors, fonts, layouts, and more without touching code.

= Does it support AJAX search? =
Yes! SearchWiz provides instant AJAX-powered search results that load as users type, without page refresh.

= What about performance? =
SearchWiz is highly optimized for performance. It uses intelligent indexing, caching, and lazy loading to keep your site fast.

= Is there a pro version? =
SearchWiz (free) provides all core search features. Future premium features may include advanced AI capabilities through separate plugins.

== Changelog ==

= 1.0.0 =
* Initial public release
* AJAX-powered live search with instant results
* Smart autocomplete suggestions
* WooCommerce product search integration
* Customizer support for styling search results
* Multiple layout options (grid, list, card)
* Advanced content filtering and search options
* Responsive design for mobile devices
* High performance with intelligent indexing
* Developer-friendly hooks and filters
* WordPress 6.6+ compatibility
* PHP 8.0+ required
* Full accessibility compliance