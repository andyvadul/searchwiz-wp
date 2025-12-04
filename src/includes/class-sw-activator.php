<?php
/**
 * Fires during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package    SW
 * @subpackage SW/includes
 * @author     SearchWiz Dev<dev@searchwiz.ai>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Activator {

	/**
	 * The code that runs during plugin activation.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		/* Creates default search forms */
		$search_form = get_page_by_path( 'default-search-form', OBJECT, SearchWiz_Search_Form::post_type );

		if ( NULL == $search_form ) {

			$admin = SearchWiz_Admin::getInstance();

			$args['id'] = -1;
			$args['title'] = 'Custom Search Form';
			$args['_is_locale'] = 'en_US';
			$args['_is_includes'] = '';
			$args['_is_excludes'] = '';
			$args['_searchwiz_settings'] = '';
			$admin->save_form( $args );

			$args['title'] = 'Default Search Form';
			$admin->save_form( $args );

			$args['title'] = 'AJAX Search Form';
			$args['_is_ajax'] = array( 
			    'enable_ajax' => 1,
			    'show_description' => 1,
			    'description_source' => 'excerpt',
			    'description_length' => 20,
			    'show_image' => 1,
			    'min_no_for_search' => 1,
			    'result_box_max_height' => 400,
			    'nothing_found_text' => 'Nothing found',
			    'show_more_result' => 1,
			    'more_result_text' => 'More Results..',
			    'search_results' => 'both',
			    'show_price' => 1,
			    'show_matching_categories' => 1,
			    'show_details_box' => 1,
			    'product_list' => 'all',
			    'order_by' => 'date',
			    'order' => 'desc',
			);
			$admin->save_form( $args );

			$args['title'] = 'AJAX Search Form for WooCommerce';
			$args['_is_includes'] = array(
                'post_type' => array( 'product' => 'product' ),
                'search_title'   => 1,
                'search_content' => 1,
                'search_excerpt' => 1,
                'post_status' => array( 'publish' => 'publish', 'inherit' => 'inherit' ),
            );
			$admin->save_form( $args );
		}

		// Build initial suggestions index
		if ( class_exists( 'SearchWiz_Suggestion_Builder' ) ) {
			SearchWiz_Suggestion_Builder::build_from_content();
			SearchWiz_Suggestion_Builder::schedule_rebuild();
		}

		// Create FULLTEXT search index table (10x faster than WP default)
		if ( class_exists( 'SearchWiz_Indexer' ) ) {
			$indexer = new SearchWiz_Indexer();
			$indexer->create_index_table();
			// Index all existing content in background (don't block activation)
			wp_schedule_single_event( time() + 60, 'searchwiz_initial_index' );
		}

		// Flush rewrite rules to register /sw endpoint
		flush_rewrite_rules();
	}
}