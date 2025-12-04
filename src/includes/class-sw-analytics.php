<?php
/**
 * Analytcis
 *
 * @package SW
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Analytics {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'searchwiz_analytics';
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'track_search_if_query']);
        add_action('admin_menu', [$this, 'add_analytics_menu']);
    }
    
    /**
     * Create analytics table
     */
    public function create_analytics_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            search_query varchar(255) NOT NULL,
            results_count int(11) DEFAULT 0,
            user_ip varchar(45),
            user_agent text,
            referer varchar(500),
            search_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY search_query (search_query),
            KEY search_date (search_date),
            KEY results_count (results_count)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Track search query
     */
    public function track_search_if_query() {
        if (is_search() && !empty(get_search_query())) {
            $this->track_search(get_search_query());
        }
    }
    
    /**
     * Track individual search
     */
    public function track_search($query, $results_count = null) {
        global $wpdb;
        
        // Get results count if not provided
        if ($results_count === null) {
            global $wp_query;
            $results_count = $wp_query->found_posts ?? 0;
        }
        
        $data = [
            'search_query' => sanitize_text_field($query),
            'results_count' => intval($results_count),
            'user_ip' => $this->get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'], 0, 500)) : '',
            'referer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(substr($_SERVER['HTTP_REFERER'], 0, 500)) : '',
            'search_date' => current_time('mysql')
        ];
        
        $wpdb->insert($this->table_name, $data);
    }
    
    /**
     * Get analytics dashboard data
     */
    public function get_dashboard_data($days = 30) {
        global $wpdb;
        
        $since = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Popular searches
        $popular_searches = $wpdb->get_results($wpdb->prepare("
            SELECT search_query, COUNT(*) as search_count, AVG(results_count) as avg_results
            FROM {$this->table_name}
            WHERE search_date >= %s
            GROUP BY search_query
            ORDER BY search_count DESC
            LIMIT 20
        ", $since));
        
        // Zero result searches
        $zero_results = $wpdb->get_results($wpdb->prepare("
            SELECT search_query, COUNT(*) as search_count
            FROM {$this->table_name}
            WHERE search_date >= %s AND results_count = 0
            GROUP BY search_query
            ORDER BY search_count DESC
            LIMIT 10
        ", $since));
        
        // Search volume by day
        $daily_volume = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(search_date) as search_date, COUNT(*) as search_count
            FROM {$this->table_name}
            WHERE search_date >= %s
            GROUP BY DATE(search_date)
            ORDER BY search_date ASC
        ", $since));
        
        // Total stats
        $total_searches = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$this->table_name} WHERE search_date >= %s
        ", $since));
        
        $avg_results = $wpdb->get_var($wpdb->prepare("
            SELECT AVG(results_count) FROM {$this->table_name} WHERE search_date >= %s
        ", $since));
        
        return [
            'popular_searches' => $popular_searches,
            'zero_results' => $zero_results,
            'daily_volume' => $daily_volume,
            'total_searches' => $total_searches,
            'avg_results' => round($avg_results, 1),
            'zero_result_rate' => $total_searches > 0 ? round((count($zero_results) / $total_searches) * 100, 1) : 0
        ];
    }
    
    private function get_user_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[$key] ) );
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        return '127.0.0.1';
    }
}