<?php
/**
 * Storefront Theme Integration
 *
 * @package SearchWiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Override Storefront's pluggable search function
 * This must be defined BEFORE Storefront theme loads
 */
if ( ! function_exists( 'storefront_product_search' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Intentionally overriding theme's pluggable function
	function storefront_product_search() {
		// Check if theme integration is enabled
		$theme_integration_option = get_option( 'searchwiz_theme_integration', array( 'enabled' => 'on' ) );
		$theme_integration_enabled = ! isset( $theme_integration_option['enabled'] ) || 'on' === $theme_integration_option['enabled'];

		if ( ! $theme_integration_enabled ) {
			// Theme integration disabled - use Storefront's default search
			// Call parent theme's original function if it exists, otherwise use get_product_search_form
			if ( function_exists( 'storefront_is_woocommerce_activated' ) && storefront_is_woocommerce_activated() ) {
				get_product_search_form();
			}
			return;
		}

		// Theme integration enabled - use SearchWiz
		if ( function_exists( 'storefront_is_woocommerce_activated' ) && storefront_is_woocommerce_activated() ) {
			?>
			<div class="site-search">
				<?php echo do_shortcode( '[searchwiz allow_render="yes"]' ); ?>
			</div>
			<?php
		}
	}
}

/**
 * Override mobile footer bar search function
 */
if ( ! function_exists( 'storefront_handheld_footer_bar_search' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Intentionally overriding theme's pluggable function
	function storefront_handheld_footer_bar_search() {
		echo '<a href="#">' . esc_attr__( 'Search', 'searchwiz' ) . '</a>';
		storefront_product_search();
	}
}

/**
 * Add Storefront-specific CSS via wp_add_inline_style
 */
add_action( 'wp_enqueue_scripts', function() {
	// Only load for Storefront theme
	$theme = wp_get_theme();
	if ( $theme->get_template() !== 'storefront' ) {
		return;
	}

	$css = '
		/* Ensure SearchWiz search box integrates properly with Storefront */
		.site-search {
			display: block;
			position: relative;
		}

		.site-search .searchwiz-container {
			width: 100%;
		}

		/* HIDE popup for sidebar/widget searches - only show for header */
		.widget .site-search .searchwiz-results,
		.sidebar .site-search .searchwiz-results,
		aside .site-search .searchwiz-results {
			display: none !important;
		}

		/* Position the results popup relative to the search input (HEADER ONLY) */
		.site-header .site-search {
			position: relative;
		}

		.site-header .site-search .searchwiz-results {
			position: absolute;
			top: 100%;
			left: auto;
			right: 0;
			z-index: 9999;
			margin-top: 5px;
			min-width: 400px;
			max-width: 600px;
		}

		/* Mobile search styling */
		#page.search-mobile-active .storefront-handheld-footer-bar ul li.search .site-search {
			bottom: 100%;
		}

		/* Ensure proper stacking */
		.site-header {
			position: relative;
			z-index: 999;
		}
	';

	wp_add_inline_style( 'searchwiz-search-styles', $css );
}, 999 );

class SearchWiz_Theme_Storefront extends SearchWiz_Theme_Base {

	public function integrate() {
		// Replace default search form (for any other locations)
		add_filter( 'get_search_form', array( $this, 'replace_search_form' ), 999 );

		// Add footer JavaScript and CSS
		add_action( 'wp_footer', array( $this, 'footer_js' ), 100 );
		add_action( 'wp_head', array( $this, 'inline_css' ), 999 );
	}

	protected function init() {
		// Ensure CSS loads even if integrate() isn't called
		add_action( 'wp_head', array( $this, 'inline_css' ), 999 );
	}

	/**
	 * Add footer JavaScript for Storefront mobile handling
	 */
	public function footer_js() {
		// Add CSS via wp_add_inline_style
		$css = '
			.dgwt-wcas-open .storefront-handheld-footer-bar,
			.dgwt-wcas-focused .storefront-handheld-footer-bar {
				display: none;
			}
		';
		wp_add_inline_style( 'searchwiz-search-styles', $css );

		// Add JS via wp_add_inline_script
		$js = '
		(function ($) {
			$(window).on("load", function () {
				// Handle mobile footer bar search click
				$(document).on("click", ".storefront-handheld-footer-bar .search > a", function (e) {
					var $wrapper = $(this).parent(),
						$searchInput = $wrapper.find(".searchwiz-search-input");
					$wrapper.removeClass("active");

					if ($searchInput.length) {
						setTimeout(function() {
							$searchInput.focus();
						}, 100);
					}

					e.preventDefault();
				});
			});
		}(jQuery));
		';
		wp_add_inline_script( 'searchwiz-search-scripts', $js );
	}

	/**
	 * Add inline CSS for Storefront integration via wp_add_inline_style
	 */
	public function inline_css() {
		$css = '
			/* Ensure SearchWiz search box integrates properly with Storefront */
			.site-search {
				display: block;
				position: relative;
			}

			.site-search .searchwiz-container {
				width: 100%;
			}

			/* HIDE popup for sidebar/widget searches - only show for header */
			.widget .site-search .searchwiz-results,
			.sidebar .site-search .searchwiz-results,
			aside .site-search .searchwiz-results {
				display: none !important;
			}

			/* Position the results popup relative to the search input (HEADER ONLY) */
			.site-header .site-search {
				position: relative;
			}

			.site-header .site-search .searchwiz-results {
				position: absolute;
				top: 100%;
				left: auto;
				right: 0;
				z-index: 9999;
				margin-top: 5px;
				min-width: 400px;
				max-width: 600px;
			}

			/* Mobile search styling */
			#page.search-mobile-active .storefront-handheld-footer-bar ul li.search .site-search {
				bottom: 100%;
			}

			/* Ensure proper stacking */
			.site-header {
				position: relative;
				z-index: 999;
			}
		';
		wp_add_inline_style( 'searchwiz-search-styles', $css );
	}
}
