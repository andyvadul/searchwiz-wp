<?php
/**
 * Astra Theme Integration
 *
 * @package SearchWiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Theme_Astra extends SearchWiz_Theme_Base {

	public function integrate() {
		// Replace default search form
		add_filter( 'get_search_form', array( $this, 'replace_search_form' ), 999 );

		// Inject search form into Astra header - do this in footer after DOM is ready
		add_action( 'wp_footer', array( $this, 'inject_search_form' ), 5 );

		// Add footer JavaScript
		add_action( 'wp_footer', array( $this, 'footer_js' ), 999 );

		// Add inline CSS via wp_enqueue_scripts (after styles are registered)
		add_action( 'wp_enqueue_scripts', array( $this, 'inline_css' ), 999 );
	}

	public function inject_search_form() {
		// Build a simple search form HTML directly (bypassing shortcode to avoid render blocking)
		$search_form = sprintf(
			'<form role="search" method="get" action="%s" class="searchwiz-simple-form search-form">
				<input
					type="search"
					class="search-field searchwiz-search-input"
					placeholder="%s"
					name="s"
					autocomplete="off"
				/>
				<input type="hidden" name="isSearchpage" value="true" />
				<button type="submit" class="search-submit">
					%s
				</button>
			</form>',
			esc_url( home_url( '/sw' ) ),
			esc_attr__( 'Search...', 'searchwiz' ),
			esc_html__( 'Search', 'searchwiz' )
		);

		// Add JS via wp_add_inline_script
		$js = '
		(function($) {
			if (typeof jQuery === "undefined") {
				console.error("[SearchWiz Astra] jQuery not found!");
				return;
			}

			$(document).ready(function() {
				console.log("[SearchWiz Astra] Attempting to inject search form...");

				// Find the slide-search container
				var $slideSearch = $(".ast-search-menu-icon.slide-search");
				console.log("[SearchWiz Astra] Found " + $slideSearch.length + " slide-search containers");

				if ($slideSearch.length > 0) {
					// Check if form already exists
					if ($slideSearch.find("form").length > 0) {
						console.log("[SearchWiz Astra] Form already exists in container, skipping injection");
						return;
					}

					console.log("[SearchWiz Astra] Injecting search form into slide-search container");
					// Append the search form HTML
					var formHtml = ' . wp_json_encode( $search_form ) . ';
					$slideSearch.append(formHtml);

					// Verify injection
					var $form = $slideSearch.find("form");
					var $input = $slideSearch.find("input.search-field, .searchwiz-search-input");
					console.log("[SearchWiz Astra] Form injected - Found " + $form.length + " forms, " + $input.length + " inputs");

					if ($form.length > 0) {
						console.log("[SearchWiz Astra] Search form injected successfully!");
					} else {
						console.warn("[SearchWiz Astra] Form injection may have failed");
					}
				} else {
					console.warn("[SearchWiz Astra] No slide-search container found on page");
				}
			});
		})(jQuery);
		';
		wp_add_inline_script( 'searchwiz-search-scripts', $js );
	}

	public function footer_js() {
		$config = $this->get_js_config();
		$breakpoint = absint( $config['breakpoint'] );

		$js = '
		(function($) {
			"use strict";

			var SearchWizAstra = {
				config: {
					breakpoint: ' . $breakpoint . ',
					selectors: {
						searchIcon: ".astra-search-icon, .ast-search-icon",
						searchInput: ".searchwiz-search-input, .search-field",
						searchContainer: ".ast-search-menu-icon, .ast-header-search"
					}
				},

				init: function() {
					// FiboSearch approach: Simple initialization, let Astra handle everything
					this.bindEvents();
				},

				bindEvents: function() {
					var self = this;

					// FiboSearch approach: Just focus the input, let Astra handle the dropdown toggle
					$(document).on("click", ".astra-search-icon", function(event) {
						if ($(window).width() > self.config.breakpoint) {
							setTimeout(function() {
								// Find input within the closest container
								var $input = $(event.target).closest(".ast-search-menu-icon").find(".searchwiz-search-input, .search-field");
								if ($input.length > 0) {
									$input.trigger("focus");
								}
							}, 100);
						}
					});
				}
			};

			// Initialize when DOM is ready AND fully loaded
			$(window).on("load", function() {
				console.log("[SearchWiz Astra] Window loaded, initializing...");
				SearchWizAstra.init();
			});

		}(jQuery));
		';
		wp_add_inline_script( 'searchwiz-search-scripts', $js );
	}

	public function inline_css() {
		$css = '
			/* FiboSearch-inspired simple approach - let Astra handle the dropdown */

			/* Slide Search */
			.ast-dropdown-active .search-form {
				padding-left: 0 !important;
			}

			.ast-dropdown-active .ast-search-icon {
				visibility: hidden;
			}

			.ast-search-menu-icon .search-form {
				padding: 0;
			}

			.search-custom-menu-item .search-field {
				display: none;
			}

			.search-custom-menu-item .search-form {
				background-color: transparent !important;
				border: 0;
			}

			/* Ensure search input is visible and styled */
			.ast-search-menu-icon .searchwiz-search-input,
			.ast-search-menu-icon .search-field {
				width: 100%;
				padding: 8px 12px;
				border: 1px solid #ddd;
				border-radius: 4px;
				font-size: 16px;
				background: #fff;
				color: #333;
			}
		';
		wp_add_inline_style( 'searchwiz-search-styles', $css );
	}
}
