<?php
/**
 * Generic Theme Integration
 *
 * @package SearchWiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Theme_Generic extends SearchWiz_Theme_Base {

	public function integrate() {
		// Replace default search form
		add_filter( 'get_search_form', array( $this, 'replace_search_form' ), 999 );
	}
}
