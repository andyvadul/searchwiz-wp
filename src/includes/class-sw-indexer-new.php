<?php
/**
 * Customizer
 *
 * @package SW
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Indexer {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'searchwiz_index';
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Auto-index on content changes
        add_action('save_post', [$this, 'index_single_post']);
        add_action('delete_post', [$this, 'remove_from_index']);

        // Admin interface
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_searchwiz_reindex', [$this, 'ajax_reindex_site']);

        // Background indexing
        add_action('searchwiz_initial_index', [$this, 'index_all_content']);
    }
    
    /**
     * Create index table on plugin activation
     */
    public function create_index_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_type varchar(20) NOT NULL,
            title text NOT NULL,
            content longtext NOT NULL,
            excerpt text,
            url varchar(500) NOT NULL,
            categories text,
            tags text,
            relevance_score float DEFAULT 1.0,
            boost_factor float DEFAULT 1.0,
            indexed_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id),
            KEY post_type (post_type),
            KEY relevance_score (relevance_score),
            FULLTEXT KEY content_search (title, content, excerpt)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Index all site content
     */
    public function index_all_content() {
        $posts = get_posts([
            'post_type' => ['post', 'page', 'product'], // Add custom post types
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ]);
        
        $total = count($posts);
        $processed = 0;
        
        foreach ($posts as $post_id) {
            $this->index_single_post($post_id);
            $processed++;
            
            // Update progress for AJAX
            if (defined('DOING_AJAX') && DOING_AJAX) {
                $progress = round(($processed / $total) * 100);
                wp_send_json([
                    'progress' => $progress,
                    'processed' => $processed,
                    'total' => $total
                ]);
            }
        }
        
        return $processed;
    }
    
    /**
     * Index single post
     */
    public function index_single_post($post_id) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            return;
        }
        
        global $wpdb;
        
        // Get categories and tags
        $categories = wp_get_post_categories($post_id, ['fields' => 'names']);
        $tags = wp_get_post_tags($post_id, ['fields' => 'names']);
        
        // Calculate relevance score
        $relevance_score = $this->calculate_relevance_score($post);
        
        // Get boost factor from post meta
        $boost_factor = get_post_meta($post_id, '_searchwiz_boost', true) ?: 1.0;
        
        $data = [
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'title' => $post->post_title,
            'content' => wp_strip_all_tags($post->post_content),
            'excerpt' => $post->post_excerpt ?: wp_trim_words($post->post_content, 55),
            'url' => get_permalink($post_id),
            'categories' => implode(',', $categories),
            'tags' => implode(',', $tags),
            'relevance_score' => $relevance_score,
            'boost_factor' => $boost_factor,
            'indexed_date' => current_time('mysql')
        ];
        
        // Insert or update
        $wpdb->replace($this->table_name, $data);
    }
    
    /**
     * Calculate relevance score based on content characteristics
     */
    private function calculate_relevance_score($post) {
        $score = 1.0;
        
        // Boost based on content length
        $content_length = strlen($post->post_content);
        if ($content_length > 2000) $score += 0.3;
        elseif ($content_length > 500) $score += 0.1;
        
        // Boost recent content
        $days_old = (time() - strtotime($post->post_date)) / DAY_IN_SECONDS;
        if ($days_old < 30) $score += 0.2;
        elseif ($days_old < 90) $score += 0.1;
        
        // Boost based on comments
        $comment_count = get_comments_number($post->ID);
        if ($comment_count > 10) $score += 0.2;
        elseif ($comment_count > 5) $score += 0.1;
        
        return round($score, 2);
    }
    
    /**
     * Remove post from index
     */
    public function remove_from_index($post_id) {
        global $wpdb;
        $wpdb->delete($this->table_name, ['post_id' => $post_id]);
    }

    /**
     * Search using FULLTEXT index with relevance scoring
     *
     * @param string $search_term Search query
     * @param array $args Search arguments (post_type, posts_per_page, paged)
     * @return array Array of post IDs with relevance scores
     */
    public function search($search_term, $args = []) {
        global $wpdb;

        // Sanitize search term
        $search_term = sanitize_text_field($search_term);
        if (empty($search_term)) {
            return [];
        }

        // Parse arguments
        $post_types = isset($args['post_type']) ? (array) $args['post_type'] : ['post', 'page', 'product'];
        $posts_per_page = isset($args['posts_per_page']) ? absint($args['posts_per_page']) : 10;
        $paged = isset($args['paged']) ? absint($args['paged']) : 1;
        $offset = ($paged - 1) * $posts_per_page;

        // Build post type clause
        $post_type_placeholders = implode(',', array_fill(0, count($post_types), '%s'));

        // FULLTEXT search with relevance scoring
        // MATCH AGAINST returns a relevance score (higher = better match)
        // Build parameters array to avoid mixing spread operator with positional args
        $params = array_merge(
            [$search_term, $search_term],  // First two MATCH AGAINST
            $post_types,                    // Post types for IN clause
            [$search_term, $posts_per_page, $offset]  // Remaining params
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name and placeholders are properly sanitized
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    post_id,
                    MATCH(title, content, excerpt) AGAINST (%s IN NATURAL LANGUAGE MODE) AS search_score,
                    relevance_score,
                    boost_factor,
                    (MATCH(title, content, excerpt) AGAINST (%s IN NATURAL LANGUAGE MODE) * relevance_score * boost_factor) AS final_score
                FROM {$this->table_name}
                WHERE post_type IN ($post_type_placeholders)
                AND MATCH(title, content, excerpt) AGAINST (%s IN NATURAL LANGUAGE MODE)
                ORDER BY final_score DESC, indexed_date DESC
                LIMIT %d OFFSET %d",
                ...$params
            )
        );

        return $results;
    }

    /**
     * Get total count for search query (for pagination)
     *
     * @param string $search_term Search query
     * @param array $args Search arguments (post_type)
     * @return int Total number of results
     */
    public function search_count($search_term, $args = []) {
        global $wpdb;

        $search_term = sanitize_text_field($search_term);
        if (empty($search_term)) {
            return 0;
        }

        $post_types = isset($args['post_type']) ? (array) $args['post_type'] : ['post', 'page', 'product'];
        $post_type_placeholders = implode(',', array_fill(0, count($post_types), '%s'));

        // Build parameters array to avoid mixing spread operator with positional args
        $params = array_merge($post_types, [$search_term]);

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and placeholders are properly sanitized
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                FROM {$this->table_name}
                WHERE post_type IN ($post_type_placeholders)
                AND MATCH(title, content, excerpt) AGAINST (%s IN NATURAL LANGUAGE MODE)",
                ...$params
            )
        );
    }
}
