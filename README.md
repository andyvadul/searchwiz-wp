# SearchWiz - WordPress Search Enhancement Plugin

A lightweight, powerful search enhancement plugin for WordPress that improves your site's search experience with instant results, smart highlighting, and customizable display options.

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)

## Features

- **Instant Search Results** - Ajax-powered search with real-time results as you type
- **Smart Highlighting** - Automatic highlighting of search terms in results
- **Customizable Display** - Full control over result appearance and layout
- **Theme Integration** - Works seamlessly with popular themes (Storefront, Astra, and more)
- **WooCommerce Support** - Enhanced product search for WooCommerce stores
- **TablePress Integration** - Search within TablePress tables
- **Lightweight** - No bloat, fast performance, minimal footprint
- **Accessibility Ready** - WCAG 2.1 compliant search interface

## Installation

### From WordPress Plugin Directory
1. Go to Plugins → Add New in your WordPress admin
2. Search for "SearchWiz"
3. Click Install Now, then Activate

### Manual Installation
1. Download the plugin zip file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the zip file and click Install Now
4. Activate the plugin

## Quick Start

1. After activation, go to **Settings → SearchWiz**
2. Configure your preferred search options
3. The enhanced search is automatically applied to your default WordPress search

No coding required. Works out of the box with sensible defaults.

## Documentation

### For Users
- [Getting Started](docs/getting-started.md) - Installation and initial setup
- [Features Guide](docs/features.md) - Detailed feature documentation
- [Troubleshooting](docs/troubleshooting.md) - Common issues and solutions
- [FAQ](docs/faq.md) - Frequently asked questions

### For Developers
- [Architecture Overview](docs/developer/architecture.md) - Plugin structure and design
- [Extension Points](docs/developer/extension-points.md) - Available hooks and filters
- [Contributing Guide](docs/developer/contributing.md) - How to contribute

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher (or MariaDB 10.0+)

## Frequently Asked Questions

### Does this work with my theme?
SearchWiz works with any properly coded WordPress theme. It has been tested extensively with popular themes including Storefront, Astra, GeneratePress, and Twenty Twenty-Three.

### Will this slow down my site?
No. SearchWiz is designed for performance. It loads minimal assets and only when needed. The search index is optimized for fast queries.

### Does this work with WooCommerce?
Yes! SearchWiz includes built-in WooCommerce support, allowing visitors to search products with enhanced results.

### Is it translation-ready?
Yes. SearchWiz is fully internationalized and ready for translation. Translation files are included in the `languages` folder.

## Contributing

We welcome contributions from the community!

- **Bug Reports:** [GitHub Issues](https://github.com/andyvadul/searchwiz-wp/issues)
- **Feature Requests:** [GitHub Issues](https://github.com/andyvadul/searchwiz-wp/issues)
- **Pull Requests:** Please read our [Contributing Guide](docs/developer/contributing.md) first

## Support

- **Documentation:** See the `docs/` folder
- **WordPress.org Forums:** [Support Forum](https://wordpress.org/support/plugin/searchwiz/)
- **GitHub Issues:** [Report bugs](https://github.com/andyvadul/searchwiz-wp/issues)

## Changelog

### 1.0.0
- Initial release
- Ajax-powered instant search
- Search term highlighting
- Customizable result display
- Theme integrations (Storefront, Astra)
- WooCommerce product search support
- TablePress integration

## License

SearchWiz is free software released under the GNU General Public License v2 or later.

See [LICENSE](LICENSE) for full license text.

---

Made with care for the WordPress community.