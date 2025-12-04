<?php
/**
 * Form Builder
 *
 * @package SW
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Form_Builder {
    
    public function __construct() {
        add_shortcode('searchwiz_form', [$this, 'render_search_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_form_assets']);
    }
    
    /**
     * Render advanced search form
     */
    public function render_search_form($atts) {
        $defaults = [
            'style' => 'modern',
            'show_categories' => 'true',
            'show_post_types' => 'true',
            'show_date_range' => 'false',
            'ajax' => 'true',
            'placeholder' => 'Search with smart suggestions...',
            'button_text' => 'Search'
        ];
        
        $args = shortcode_atts($defaults, $atts);
        
        ob_start();
        ?>
        <div class="searchwiz-form-container <?php echo esc_attr($args['style']); ?>">
            <form class="searchwiz-search-form" method="get" action="<?php echo esc_url(home_url('/sw')); ?>">
                
                <!-- Main Search Input -->
                <div class="searchwiz-input-group">
                    <input type="text" 
                           name="s" 
                           class="searchwiz-search-input"
                           placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                           value="<?php echo get_search_query(); ?>"
                           autocomplete="off">
                    <div class="searchwiz-suggestions" style="display: none;"></div>
                </div>
                
                <!-- Advanced Filters -->
                <div class="searchwiz-filters" <?php echo $args['ajax'] === 'true' ? 'style="display: none;"' : ''; ?>>
                    
                    <?php if ($args['show_categories'] === 'true'): ?>
                    <div class="searchwiz-filter-group">
                        <label>Categories:</label>
                        <select name="searchwiz_category" class="searchwiz-select">
                            <option value="">All Categories</option>
                            <?php
                            $categories = get_categories(['hide_empty' => true]);
                            foreach ($categories as $category) {
                                $selected = (isset($_GET['searchwiz_category']) && $_GET['searchwiz_category'] == $category->term_id) ? 'selected' : '';
                                echo '<option value="' . wp_kses_post($category->term_id) . '" ' . wp_kses_post($selected) . '>' . wp_kses_post($category->name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_post_types'] === 'true'): ?>
                    <div class="searchwiz-filter-group">
                        <label>Content Type:</label>
                        <select name="searchwiz_post_type" class="searchwiz-select">
                            <option value="">All Types</option>
                            <?php
                            $post_types = get_post_types(['public' => true], 'objects');
                            foreach ($post_types as $post_type) {
                                if ($post_type->name === 'attachment') continue;
                                $selected = (isset($_GET['searchwiz_post_type']) && $_GET['searchwiz_post_type'] == $post_type->name) ? 'selected' : '';
                                echo '<option value="' . wp_kses_post($post_type->name) . '" ' . wp_kses_post($selected) . '>' . wp_kses_post($post_type->label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_date_range'] === 'true'): ?>
                    <div class="searchwiz-filter-group">
                        <label>Date Range:</label>
                        <?php
                        // Sanitize and validate the date range parameter
                        $date_range_value = '';
                        if ( isset( $_GET['searchwiz_date_range'] ) ) {
                            $raw_value = sanitize_text_field( wp_unslash( $_GET['searchwiz_date_range'] ) );
                            // Only allow valid values
                            if ( in_array( $raw_value, array( 'week', 'month', 'year' ), true ) ) {
                                $date_range_value = $raw_value;
                            }
                        }
                        ?>
                        <select name="searchwiz_date_range" class="searchwiz-select">
                            <option value="">Any Time</option>
                            <option value="week" <?php selected( $date_range_value, 'week' ); ?>>Past Week</option>
                            <option value="month" <?php selected( $date_range_value, 'month' ); ?>>Past Month</option>
                            <option value="year" <?php selected( $date_range_value, 'year' ); ?>>Past Year</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Action Buttons -->
                <div class="searchwiz-actions">
                    <button type="submit" class="searchwiz-search-button">
                        🔍 <?php echo esc_html($args['button_text']); ?>
                    </button>
                    <button type="button" class="searchwiz-toggle-filters">
                        ⚙️ Filters
                    </button>
                </div>
                
            </form>
            
            <!-- Live Results Container (for AJAX) -->
            <?php if ($args['ajax'] === 'true'): ?>
            <div class="searchwiz-live-results" style="display: none;">
                <div class="searchwiz-loading">Searching...</div>
                <div class="searchwiz-results-container"></div>
            </div>
            <?php endif; ?>
            
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Enqueue form assets
     */
    public function enqueue_form_assets() {
        if (!wp_script_is('searchwiz-form-js', 'registered')) {
            wp_enqueue_script('searchwiz-form-js', 
                SEARCHWIZ_PLUGIN_URL . 'assets/js/searchwiz-form.js', 
                ['jquery'], 
                SEARCHWIZ_VERSION, 
                true
            );
            
            wp_localize_script('searchwiz-form-js', 'searchwiz_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('searchwiz_search'),
                'strings' => [
                    'searching' => 'Searching...',
                    'no_results' => 'No results found.',
                    'error' => 'Search error. Please try again.'
                ]
            ]);
        }
        
        if (!wp_style_is('searchwiz-form-css', 'registered')) {
            wp_enqueue_style('searchwiz-form-css', 
                SEARCHWIZ_PLUGIN_URL . 'assets/css/searchwiz-form.css', 
                [], 
                SEARCHWIZ_VERSION
            );
        }
    }
}
