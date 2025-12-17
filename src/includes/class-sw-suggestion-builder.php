<?php
/**
 * Smart Search Suggestions Builder
 *
 * Builds and maintains local search suggestions based on site content.
 * Uses local content extraction with fuzzy matching for search suggestions.
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

class SearchWiz_Suggestion_Builder {

	/**
	 * Build suggestions from site content.
	 * Extracts common words from post titles and content.
	 *
	 * @since 1.0.0
	 * @return int Number of suggestions built
	 */
	public static function build_from_content() {
		global $wpdb;

		// Extract common words from post titles
		$title_words = $wpdb->get_results( "
			SELECT
				LOWER(
					SUBSTRING_INDEX(
						SUBSTRING_INDEX(post_title, ' ', numbers.n),
						' ',
						-1
					)
				) as word,
				COUNT(*) as frequency
			FROM {$wpdb->posts}
			CROSS JOIN (
				SELECT 1 as n UNION SELECT 2 UNION SELECT 3
				UNION SELECT 4 UNION SELECT 5
			) numbers
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page', 'product')
			AND CHAR_LENGTH(post_title) - CHAR_LENGTH(REPLACE(post_title, ' ', '')) >= numbers.n - 1
			GROUP BY word
			HAVING LENGTH(word) > 3
			ORDER BY frequency DESC
			LIMIT 500
		" );

		// Also get product names and categories if WooCommerce exists
		$suggestions = array();

		foreach ( $title_words as $word_data ) {
			$word = trim( $word_data->word );
			// Remove special characters
			$word = preg_replace( '/[^a-z0-9\s-]/i', '', $word );

			if ( strlen( $word ) > 3 ) {
				$suggestions[] = array(
					'term'      => $word,
					'frequency' => $word_data->frequency,
					'type'      => 'content',
				);
			}
		}

		// Add WooCommerce product categories if available
		if ( class_exists( 'WooCommerce' ) ) {
			$categories = get_terms( array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
			) );

			if ( ! is_wp_error( $categories ) ) {
				foreach ( $categories as $category ) {
					$suggestions[] = array(
						'term'      => strtolower( $category->name ),
						'frequency' => $category->count,
						'type'      => 'category',
					);
				}
			}
		}

		// Sort by frequency
		usort( $suggestions, function( $a, $b ) {
			return $b['frequency'] - $a['frequency'];
		});

		// Limit to top 1000
		$suggestions = array_slice( $suggestions, 0, 1000 );

		update_option( 'searchwiz_suggestions', $suggestions );

		return count( $suggestions );
	}

	/**
	 * Get suggestions matching a query.
	 * Uses Levenshtein distance for fuzzy matching.
	 *
	 * @since 1.0.0
	 * @param string $query  Search query to match against
	 * @param int    $limit  Maximum number of suggestions to return
	 * @return array Matched suggestions with scores
	 */
	public static function get_suggestions( $query, $limit = 5 ) {
		$query = strtolower( trim( $query ) );

		if ( strlen( $query ) < 2 ) {
			return array();
		}

		$suggestions = get_option( 'searchwiz_suggestions', array() );
		$matches = array();

		foreach ( $suggestions as $suggestion ) {
			$term = strtolower( $suggestion['term'] );

			// Exact prefix match (highest score)
			if ( strpos( $term, $query ) === 0 ) {
				$matches[] = array(
					'term'  => $suggestion['term'],
					'score' => 100,
					'type'  => $suggestion['type'],
				);
				continue;
			}

			// Contains match
			if ( strpos( $term, $query ) !== false ) {
				$matches[] = array(
					'term'  => $suggestion['term'],
					'score' => 80,
					'type'  => $suggestion['type'],
				);
				continue;
			}

			// Fuzzy match using Levenshtein distance
			// Only calculate for similar length strings to save CPU
			if ( abs( strlen( $term ) - strlen( $query ) ) <= 3 ) {
				$distance = levenshtein( $query, substr( $term, 0, strlen( $query ) ) );

				if ( $distance <= 2 ) {
					$score = 60 - ( $distance * 15 );
					$matches[] = array(
						'term'  => $suggestion['term'],
						'score' => $score,
						'type'  => $suggestion['type'],
					);
				}
			}
		}

		// Sort by score
		usort( $matches, function( $a, $b ) {
			return $b['score'] - $a['score'];
		});

		// Return top matches
		return array_slice( $matches, 0, $limit );
	}

	/**
	 * Schedule weekly suggestion rebuild.
	 * Acts as a safety net to complement the immediate rebuild on save.
	 *
	 * @since 1.0.0
	 * @param string $frequency Optional. Cron schedule: 'daily', 'weekly', 'monthly'. Default 'weekly'.
	 * @return void
	 */
	public static function schedule_rebuild( $frequency = 'weekly' ) {
		if ( ! wp_next_scheduled( 'searchwiz_rebuild_suggestions' ) ) {
			// TODO: Make frequency configurable via admin settings (see docs/ADMIN_SCREEN_NEEDS.md)
			wp_schedule_event( time() + 300, $frequency, 'searchwiz_rebuild_suggestions' );
		}
	}

	/**
	 * Reschedule suggestion rebuild with new frequency.
	 * Clears existing schedule and creates new one.
	 * Use this when admin changes rebuild frequency setting.
	 *
	 * @since 1.0.0
	 * @param string $frequency Cron schedule: 'daily', 'weekly', 'monthly', or 'manual'.
	 * @return bool True on success, false on failure.
	 */
	public static function reschedule_rebuild( $frequency = 'weekly' ) {
		// Clear existing schedule
		self::clear_scheduled_rebuild();

		// If manual, don't reschedule
		if ( $frequency === 'manual' ) {
			return true;
		}

		// Validate frequency
		$allowed_frequencies = array( 'daily', 'weekly', 'monthly' );
		if ( ! in_array( $frequency, $allowed_frequencies ) ) {
			return false;
		}

		// Schedule with new frequency
		self::schedule_rebuild( $frequency );

		return true;
	}

	/**
	 * Rebuild suggestions when content is published or updated.
	 * Uses transient to debounce multiple rapid updates.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID
	 * @param object $post    Post object
	 * @return void
	 */
	public static function maybe_rebuild_on_save( $post_id, $post ) {
		// Only rebuild for published posts/pages/products
		if ( $post->post_status !== 'publish' ) {
			return;
		}

		if ( ! in_array( $post->post_type, array( 'post', 'page', 'product' ) ) ) {
			return;
		}

		// Debounce: Only rebuild once per minute to avoid performance issues
		$rebuild_lock = get_transient( 'searchwiz_suggestion_rebuild_lock' );
		if ( $rebuild_lock ) {
			return;
		}

		// Set lock for 1 minute
		set_transient( 'searchwiz_suggestion_rebuild_lock', true, 60 );

		// Rebuild suggestions
		self::build_from_content();
	}

	/**
	 * Initialize auto-rebuild hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init_hooks() {
		// Rebuild when posts/pages/products are published or updated
		add_action( 'save_post', array( __CLASS__, 'maybe_rebuild_on_save' ), 10, 2 );
		add_action( 'save_post_product', array( __CLASS__, 'maybe_rebuild_on_save' ), 10, 2 );
	}

	/**
	 * Clear scheduled suggestion rebuild.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function clear_scheduled_rebuild() {
		$timestamp = wp_next_scheduled( 'searchwiz_rebuild_suggestions' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'searchwiz_rebuild_suggestions' );
		}
	}
}
