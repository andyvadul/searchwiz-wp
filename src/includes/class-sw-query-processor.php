<?php
/**
 * Query Processor
 *
 * @package SW
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Query_Processor {
    
    private $stop_words;
    private $boost_words;
    
    public function __construct() {
        $this->load_word_lists();
        add_filter('searchwiz_search_query', [$this, 'process_search_query']);
    }
    
    private function load_word_lists() {
        // Default stop words
        $default_stop_words = 'the,and,or,but,in,on,at,to,for,of,with,by,a,an,is,are,was,were,been,be,have,has,had,do,does,did,will,would,could,should,may,might,can,shall';
        
        $this->stop_words = array_map('trim', explode(',',
            get_option('searchwiz_stop_words', $default_stop_words)
        ));

        $this->boost_words = array_map('trim', explode(',',
            get_option('searchwiz_boost_words', '')
        ));
    }
    
    /**
     * Process search query - remove stop words, identify boost words
     */
    public function process_search_query($query) {
        $original_query = $query;
        
        // Clean and split query
        $query = strtolower(trim($query));
        $words = preg_split('/\s+/', $query);
        
        // Remove stop words
        $filtered_words = array_diff($words, $this->stop_words);
        
        // Identify boost words
        $processed_words = [];
        foreach ($filtered_words as $word) {
            $processed_words[] = [
                'word' => $word,
                'boost' => in_array($word, $this->boost_words) ? 2.0 : 1.0,
                'original' => $word
            ];
        }
        
        return [
            'original' => $original_query,
            'processed_words' => $processed_words,
            'filtered_query' => implode(' ', $filtered_words),
            'has_boost_words' => !empty(array_intersect($words, $this->boost_words))
        ];
    }
    
    /**
     * Enhanced search with stop/boost word processing
     */
    public function enhanced_search($query, $args = []) {
        global $wpdb;
        
        $processed = $this->process_search_query($query);
        $search_terms = $processed['processed_words'];
        
        if (empty($search_terms)) {
            return [];
        }
        
        $indexer = new SearchWiz_Indexer();
        $table_name = $wpdb->prefix . 'searchwiz_index';
        
        // Build dynamic SQL based on processed query
        $where_conditions = [];
        $prepare_values = [];
        $boost_sql = "relevance_score * boost_factor";
        
        foreach ($search_terms as $term_data) {
            $term = '%' . $wpdb->esc_like($term_data['word']) . '%';
            $boost = floatval($term_data['boost']);
            
            $where_conditions[] = "(title LIKE %s OR content LIKE %s)";
            $prepare_values[] = $term;
            $prepare_values[] = $term;
            
            if ($boost > 1.0) {
                $boost_sql .= " * CASE WHEN (title LIKE %s OR content LIKE %s) THEN %f ELSE 1 END";
                $prepare_values[] = $term;
                $prepare_values[] = $term;
                $prepare_values[] = $boost;
            }
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT *, ({$boost_sql}) as final_score 
                    FROM {$table_name} 
                    WHERE {$where_clause}
                    ORDER BY final_score DESC, indexed_date DESC
                    LIMIT 50";
            
        return $wpdb->get_results($wpdb->prepare($sql, ...$prepare_values)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    }
}
