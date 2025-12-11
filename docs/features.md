# SearchWiz Features Guide

Complete documentation of all SearchWiz features.

## Core Features

### Instant Ajax Search

SearchWiz provides real-time search results as you type, without page reloads.

**How it works:**
- Results appear after typing minimum characters (default: 3)
- Results update as you continue typing
- Click any result to navigate directly

**Configuration:**
- Settings → SearchWiz → General → Enable Ajax Search
- Adjust minimum characters for your needs

### Search Term Highlighting

Matching search terms are automatically highlighted in results.

**Features:**
- Bold highlighting of exact, full-word matches
- Works in titles and excerpts
- Customizable highlight style
- Note: Full word matches are highlighted (e.g., "bike" highlights in results). Partial word matches (e.g., "bik") currently do not highlight, though autocomplete suggestions still work.

**Configuration:**
- Settings → SearchWiz → Display → Highlight Search Terms

### Customizable Results Display

Control how search results appear to your visitors.

**Options:**
- List or grid layout
- Show/hide featured images
- Custom excerpt length
- Show/hide dates and authors

**Configuration:**
- Settings → SearchWiz → Display

## Integrations

### WooCommerce Support

Enhanced product search for WooCommerce stores.

**Features:**
- Search products by name, SKU, and description
- Show prices in results
- Filter by product category

**Requirements:**
- WooCommerce 3.0 or higher

### TablePress Integration

Search within TablePress tables.

**Features:**
- Include table content in search
- Link to pages containing tables

**Requirements:**
- TablePress plugin installed

### Theme Compatibility

SearchWiz works with all properly coded themes. Tested with:

- Storefront
- Astra
- GeneratePress
- OceanWP
- Twenty Twenty-Three
- Twenty Twenty-Four

## Advanced Features

### Post Type Selection

Choose which content types to search.

**Available types:**
- Posts
- Pages
- Products (WooCommerce)
- Custom post types

**Configuration:**
- Settings → SearchWiz → Advanced → Post Types

### Category/Tag Exclusion

Hide specific categories or tags from search.

**Use cases:**
- Exclude internal categories
- Hide draft content categories
- Filter out specific tags

**Configuration:**
- Settings → SearchWiz → Advanced → Exclusions

### Debug Mode

Troubleshoot search issues.

**What it shows:**
- Search queries being executed
- Results found
- Performance timing

**Warning:** Disable in production for performance.

**Configuration:**
- Settings → SearchWiz → Advanced → Debug Mode

## Shortcodes

### Search Form

Add a search form anywhere:

```
[searchwiz_form]
```

**Attributes:**
- `placeholder` - Custom placeholder text
- `button_text` - Custom button text

Example:
```
[searchwiz_form placeholder="Search products..." button_text="Find"]
```

### Search Results

Display search results in a custom location:

```
[searchwiz_results]
```

## Widgets

### SearchWiz Search Widget

Add an enhanced search box to any widget area.

1. Go to Appearance → Widgets
2. Find "SearchWiz Search"
3. Drag to your desired widget area
4. Configure title and options

## Performance

SearchWiz is designed for speed:

- Minimal CSS/JS footprint
- Assets load only when needed
- Optimized database queries
- Compatible with caching plugins

## Accessibility

SearchWiz follows WCAG 2.1 guidelines:

- Keyboard navigation support
- Screen reader compatible
- Proper ARIA labels
- Focus management
