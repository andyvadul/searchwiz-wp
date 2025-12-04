# SearchWiz Extension Points

Guide to extending SearchWiz functionality via hooks and filters.

## Filters

### searchwiz_search_args

Modify the WP_Query arguments before search executes.

```php
add_filter( 'searchwiz_search_args', function( $args, $search_term ) {
    // Add custom meta query
    $args['meta_query'] = array(
        array(
            'key'     => 'featured',
            'value'   => '1',
            'compare' => '='
        )
    );
    return $args;
}, 10, 2 );
```

**Parameters:**
- `$args` (array) - WP_Query arguments
- `$search_term` (string) - The search query

### searchwiz_result_item

Modify individual search result items.

```php
add_filter( 'searchwiz_result_item', function( $item, $post ) {
    // Add custom field to result
    $item['custom_field'] = get_post_meta( $post->ID, 'my_field', true );
    return $item;
}, 10, 2 );
```

**Parameters:**
- `$item` (array) - Result item data
- `$post` (WP_Post) - The post object

### searchwiz_results

Modify all search results before returning.

```php
add_filter( 'searchwiz_results', function( $results, $search_term ) {
    // Add custom result to top
    array_unshift( $results, array(
        'title' => 'Custom Result',
        'url'   => '/custom-page/',
    ));
    return $results;
}, 10, 2 );
```

### searchwiz_form_html

Modify the search form HTML output.

```php
add_filter( 'searchwiz_form_html', function( $html, $atts ) {
    // Wrap form in custom container
    return '<div class="my-search-wrapper">' . $html . '</div>';
}, 10, 2 );
```

### searchwiz_highlight_term

Modify how search terms are highlighted.

```php
add_filter( 'searchwiz_highlight_term', function( $highlighted, $text, $term ) {
    // Use custom highlight markup
    return str_replace(
        $term,
        '<mark class="my-highlight">' . $term . '</mark>',
        $text
    );
}, 10, 3 );
```

## Actions

### searchwiz_before_search

Fires before search query executes.

```php
add_action( 'searchwiz_before_search', function( $search_term ) {
    // Log search queries
    error_log( 'Search: ' . $search_term );
}, 10, 1 );
```

### searchwiz_after_search

Fires after search query completes.

```php
add_action( 'searchwiz_after_search', function( $search_term, $results ) {
    // Track search analytics
    my_track_search( $search_term, count( $results ) );
}, 10, 2 );
```

### searchwiz_enqueue_scripts

Fires when SearchWiz enqueues scripts.

```php
add_action( 'searchwiz_enqueue_scripts', function() {
    // Enqueue custom script that depends on SearchWiz
    wp_enqueue_script(
        'my-search-extension',
        plugin_dir_url( __FILE__ ) . 'js/extension.js',
        array( 'searchwiz-search' ),
        '1.0.0',
        true
    );
});
```

## JavaScript Events

### searchwiz:search:start

Triggered when a search begins.

```javascript
document.addEventListener('searchwiz:search:start', function(e) {
    console.log('Searching for:', e.detail.term);
});
```

### searchwiz:search:complete

Triggered when search results are received.

```javascript
document.addEventListener('searchwiz:search:complete', function(e) {
    console.log('Found results:', e.detail.results.length);
});
```

### searchwiz:result:click

Triggered when a result is clicked.

```javascript
document.addEventListener('searchwiz:result:click', function(e) {
    console.log('Clicked:', e.detail.url);
});
```

## Template Override

Override result templates in your theme:

1. Copy `public/partials/search-results.php` to your theme
2. Place in `your-theme/searchwiz/search-results.php`
3. SearchWiz will use your template automatically

## Adding Custom Post Types

Register post types for SearchWiz:

```php
add_filter( 'searchwiz_searchable_post_types', function( $types ) {
    $types[] = 'my_custom_type';
    return $types;
});
```

## Custom Result Rendering

Override default result rendering:

```php
add_filter( 'searchwiz_render_result', function( $html, $item ) {
    // Completely custom result HTML
    return sprintf(
        '<div class="my-result">
            <a href="%s">%s</a>
        </div>',
        esc_url( $item['url'] ),
        esc_html( $item['title'] )
    );
}, 10, 2 );
```

