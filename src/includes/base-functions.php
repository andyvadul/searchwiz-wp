<?php
/**
 * Declares Global Functions
 *
 * @package SW
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Checks whether current request is a JSON request, or is expecting a JSON response. */
function searchwiz_search_is_json_request() {

    if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ), 'application/json' ) ) {
        return true;
    }

    if ( isset( $_SERVER['CONTENT_TYPE'] ) && 'application/json' === sanitize_text_field( wp_unslash( $_SERVER['CONTENT_TYPE'] ) ) ) {
        return true;
    }

    return false;

}

/**
 * Case-insensitive in_array() wrapper.
 *
 * @param  mixed $needle   Value to seek.
 * @param  array $haystack Array to seek in.
 *
 * @return bool
 */
function searchwiz_in_arrayi($needle, $haystack)
{
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}