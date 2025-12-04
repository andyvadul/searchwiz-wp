<?php
/**
 * Upgrade Click Tracking
 *
 * Tracks which tabs users click upgrade from
 *
 * @package SW
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Upgrade_Tracking {

	/**
	 * Core singleton class
	 * @var self
	 */
	private static $_instance;

	/**
	 * Gets the instance of this class.
	 *
	 * @return self
	 */
	public static function getInstance() {
		if ( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register AJAX handlers
		add_action( 'wp_ajax_searchwiz_track_upgrade_click', array( $this, 'track_upgrade_click' ) );
	}

	/**
	 * Track upgrade button clicks
	 */
	public function track_upgrade_click() {
		// Verify nonce - sanitize before verification
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'searchwiz_track_upgrade' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Get data
		$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'unknown';
		$section = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : 'unknown';

		// Get current stats
		$stats = get_option( 'searchwiz_upgrade_click_stats', array() );

		// Initialize if needed
		if ( ! isset( $stats[ $section ] ) ) {
			$stats[ $section ] = array();
		}
		if ( ! isset( $stats[ $section ][ $source ] ) ) {
			$stats[ $section ][ $source ] = array(
				'count' => 0,
				'first_click' => current_time( 'timestamp' ),
				'last_click' => current_time( 'timestamp' ),
			);
		}

		// Update stats
		$stats[ $section ][ $source ]['count']++;
		$stats[ $section ][ $source ]['last_click'] = current_time( 'timestamp' );

		// Save stats
		update_option( 'searchwiz_upgrade_click_stats', $stats );

		// Return success
		wp_send_json_success( array(
			'source' => $source,
			'section' => $section,
			'count' => $stats[ $section ][ $source ]['count'],
		) );
	}

	/**
	 * Get upgrade click statistics
	 *
	 * @return array
	 */
	public static function get_stats() {
		return get_option( 'searchwiz_upgrade_click_stats', array() );
	}

	/**
	 * Get total upgrade clicks
	 *
	 * @return int
	 */
	public static function get_total_clicks() {
		$stats = self::get_stats();
		$total = 0;

		foreach ( $stats as $section => $sources ) {
			foreach ( $sources as $source => $data ) {
				$total += $data['count'];
			}
		}

		return $total;
	}

	/**
	 * Get top upgrade sources
	 *
	 * @param int $limit Number of top sources to return
	 * @return array
	 */
	public static function get_top_sources( $limit = 5 ) {
		$stats = self::get_stats();
		$sources = array();

		foreach ( $stats as $section => $section_sources ) {
			foreach ( $section_sources as $source => $data ) {
				$sources[] = array(
					'section' => $section,
					'source' => $source,
					'count' => $data['count'],
					'last_click' => $data['last_click'],
				);
			}
		}

		// Sort by count descending
		usort( $sources, function( $a, $b ) {
			return $b['count'] - $a['count'];
		} );

		return array_slice( $sources, 0, $limit );
	}
}

// Initialize
SearchWiz_Upgrade_Tracking::getInstance();
