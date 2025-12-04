<?php
/**
 * Represents the view for the plugin settings page.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user to configure plugin settings.
 *
 * @package SW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exits if accessed directly.
}
?>

<div class="wrap">

	<?php
		// Determine which section we're in (frontend or backend)
		$searchwiz_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$searchwiz_section = '';
		$searchwiz_page_title = '';

		if ( 'searchwiz-search' === $searchwiz_page || 'searchwiz-search-frontend' === $searchwiz_page ) {
			// Main menu or explicit frontend page
			$searchwiz_section = 'frontend';
			$searchwiz_page_title = __( 'Front-end Settings', 'searchwiz' );
		} elseif ( 'searchwiz-search-backend' === $searchwiz_page ) {
			$searchwiz_section = 'backend';
			$searchwiz_page_title = __( 'Back-end Settings', 'searchwiz' );
		} else {
			// Fallback for old URLs or direct access - default to frontend
			$searchwiz_section = 'frontend';
			$searchwiz_page_title = __( 'Front-end Settings', 'searchwiz' );
		}
	?>

	<h1 class="wp-heading-inline">
		<span class="is-search-image"></span>
		<?php echo esc_html( $searchwiz_page_title ); ?>
	</h1>

	<hr class="wp-header-end">

	<?php do_action( 'searchwiz_admin_notices' ); ?>
	<?php settings_errors(); ?>

	<!-- Global Auto-save Notification -->
	<div id="searchwiz_global_autosave" style="position: fixed; top: 32px; right: 20px; z-index: 9999; background: #f0f0f1; color: #50575e; padding: 12px 20px; border-left: 4px solid #72aee6; font-size: 13px; opacity: 0; transition: opacity 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
		✓ <?php esc_html_e( 'Changes saved', 'searchwiz' ); ?>
	</div>

		<div id="poststuff">
		<div id="search-body" class="metabox-holder columns-2">
			<form id="searchwiz_search_options" action="options.php" method="post">
			<div id="searchtbox-container-1" class="postbox-container">
			<div id="search-form-editor">
			<?php
				settings_fields( 'searchwiz_search' );

				// Define panels based on section
				if ( 'frontend' === $searchwiz_section ) {
					$searchwiz_panels = array(
						'overview' => array(
							'overview',
							'Overview',
							'Theme integration and quick setup',
						),
						'search-box' => array(
							'search-box',
							'Search Box',
							'Shortcodes, styling, and advanced options',
						),
						'results' => array(
							'results',
							'Search Results',
							'Search results appearance and what content to index',
						),
						// Upgrade tab removed for V1.1 - will be added post-approval
					);
				} else {
					// Backend panels
					$searchwiz_panels = array(
						'content' => array(
							'content',
							'Content',
							'What to search and index, stopwords, and synonyms',
						),
						'performance' => array(
							'performance',
							'Performance',
							'Indexing and search optimization',
						),
						'analytics' => array(
							'analytics',
							'Analytics',
							'Search insights and logs',
						),
						// Upgrade tab removed for V1.1 - will be added post-approval
					);
				}

				// Determine active tab
				$searchwiz_tab = '';
				if ( isset( $_GET['tab'] ) ) {
					$searchwiz_requested_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
					$searchwiz_tab = isset( $searchwiz_panels[$searchwiz_requested_tab] ) ? $searchwiz_requested_tab : '';
				}

				// Default to first tab if none selected
				if ( empty( $searchwiz_tab ) ) {
					$searchwiz_tab = array_key_first( $searchwiz_panels );
				}

				// Build URL for tabs
				$searchwiz_url = menu_page_url( $searchwiz_page, false );
				?>
					<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $searchwiz_panels as $searchwiz_id => $searchwiz_panel ) {
					$searchwiz_class = ( $searchwiz_tab == $searchwiz_id ) ? 'nav-tab-active' : '';
					echo sprintf( '<a href="%1$s" id="%2$s-tab" class="nav-tab %3$s" title="%4$s">%5$s</a>',
						esc_url( $searchwiz_url ) . '&tab=' . esc_attr($searchwiz_panel[0]), esc_attr( $searchwiz_panel[0] ), esc_attr( $searchwiz_class ), esc_attr( $searchwiz_panel[2] ), esc_html( $searchwiz_panel[1] ) );
				}
				?>
					</h2>
				<?php

				$searchwiz_settings_fields = SearchWiz_Settings_Fields::getInstance();

				// Render content based on section and tab
				echo '<div class="search-form-editor-panel">';

				if ( 'frontend' === $searchwiz_section ) {
					if ( 'overview' === $searchwiz_tab ) {
						// Overview - Theme integration and quick setup
						?>
						<div style="background: #fff; padding: 30px; border: 1px solid #ccd0d4; margin-top: 20px;">
							<h2><?php esc_html_e( 'Welcome to SearchWiz', 'searchwiz' ); ?></h2>

							<p style="font-size: 15px; line-height: 1.7; margin-bottom: 20px;">
								<?php esc_html_e( 'SearchWiz is a powerful search plugin that enhances your WordPress site with fast, accurate search results. Its responsive design creates a custom search index for lightning-fast queries, supports autocomplete suggestions, and seamlessly integrates with popular themes.', 'searchwiz' ); ?>
							</p>

							<p style="font-size: 15px; line-height: 1.7; margin-bottom: 30px;">
								<?php esc_html_e( 'SearchWiz Pro adds advanced features including AI-powered conversational search, visual search box editor, WooCommerce product search, advanced analytics, and priority support.', 'searchwiz' ); ?>
								<a href="https://searchwiz.ai/pro/" target="_blank" style="text-decoration: none;"><?php esc_html_e( 'Learn more about Pro', 'searchwiz' ); ?> →</a>
							</p>

							<hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

							<h3><?php esc_html_e( 'Theme Integration', 'searchwiz' ); ?></h3>

							<?php
							// Theme detection
							$searchwiz_theme_integration = new SearchWiz_Theme_Integration();
							$searchwiz_theme_info = $searchwiz_theme_integration->get_theme_info();
							$searchwiz_is_supported = $searchwiz_theme_integration->is_theme_supported();

							// Get current integration setting (default to 'on' for supported themes)
							$searchwiz_integration_options = get_option( 'searchwiz_theme_integration', array( 'enabled' => 'on' ) );
							$searchwiz_integration_enabled = ! isset( $searchwiz_integration_options['enabled'] ) || 'on' === $searchwiz_integration_options['enabled'];
							?>

							<p style="font-size: 15px; margin-bottom: 15px;">
								<strong><?php esc_html_e( 'Current Theme:', 'searchwiz' ); ?></strong>
								<?php echo esc_html( $searchwiz_theme_info['name'] ); ?>
								<?php if ( $searchwiz_is_supported ) : ?>
									<span style="color: #46b450; margin-left: 8px;">✓ <?php esc_html_e( 'Supported!', 'searchwiz' ); ?></span>
								<?php else : ?>
									<span style="color: #f0ad4e; margin-left: 8px;">⚠ <?php esc_html_e( 'Not Supported', 'searchwiz' ); ?></span>
									<?php
									// Create email request for theme support
									$searchwiz_theme_obj = wp_get_theme();
									$searchwiz_theme_uri = $searchwiz_theme_obj->get( 'ThemeURI' );
									$searchwiz_theme_author = $searchwiz_theme_obj->get( 'Author' );
									$searchwiz_theme_version = $searchwiz_theme_obj->get( 'Version' );
									$searchwiz_site_url = get_site_url();

									$searchwiz_email_subject = sprintf( 'Request: Add support for %s theme', $searchwiz_theme_info['name'] );
									$searchwiz_email_body = sprintf(
										"Hi SearchWiz Team,\n\nI would like to request support for my theme:\n\nTheme Name: %s\nTheme Slug: %s\nTheme URI: %s\nTheme Author: %s\nTheme Version: %s\nMy Site: %s\n\nThank you!",
										$searchwiz_theme_info['name'],
										$searchwiz_theme_info['slug'],
										$searchwiz_theme_uri,
										$searchwiz_theme_author,
										$searchwiz_theme_version,
										$searchwiz_site_url
									);
									$searchwiz_mailto_link = 'mailto:support@searchwiz.ai?subject=' . rawurlencode( $searchwiz_email_subject ) . '&body=' . rawurlencode( $searchwiz_email_body );
									?>
									<a href="<?php echo esc_url( $searchwiz_mailto_link ); ?>" style="margin-left: 8px; text-decoration: none; font-size: 13px;">
										<?php esc_html_e( 'Request Support', 'searchwiz' ); ?> →
									</a>
								<?php endif; ?>
							</p>

							<?php if ( $searchwiz_is_supported ) : ?>
								<div style="background: #f7f7f7; padding: 15px; border-left: 4px solid #46b450; margin: 20px 0;">
									<label style="display: flex; align-items: flex-start; cursor: pointer; font-size: 14px;">
										<input type="checkbox"
										       name="searchwiz_theme_integration[enabled]"
										       id="searchwiz_theme_integration_enabled"
										       value="on"
										       <?php checked( $searchwiz_integration_enabled, true ); ?>
										       style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; margin-right: 12px; margin-top: 0px; width: 22px; height: 22px; min-width: 22px; flex-shrink: 0; cursor: pointer; border: 2.5px solid #46b450 !important; border-radius: 4px; background: #fff !important; accent-color: #46b450; transform: scale(1); transition: all 0.2s ease;" />
										<div style="flex: 1;">
											<div style="margin-bottom: 8px;">
												<strong><?php esc_html_e( 'SearchWiz is your theme\'s search default.', 'searchwiz' ); ?></strong>
											</div>
											<div style="color: #666; font-size: 13px; line-height: 1.5;">
												<?php esc_html_e( 'Uncheck this to use WordPress shortcodes for custom search placement.', 'searchwiz' ); ?>
												<a href="https://wordpress.org/documentation/article/shortcode/" target="_blank" style="text-decoration: none; margin-left: 4px;">
													<?php esc_html_e( 'Learn about shortcodes', 'searchwiz' ); ?> →
												</a>
											</div>
										</div>
									</label>
									<!-- Auto-save notification -->
									<div id="searchwiz_theme_integration_saved" style="margin-top: 15px; background: #f0f0f1; color: #50575e; padding: 12px 20px; border-left: 4px solid #72aee6; font-size: 13px; opacity: 0; transition: opacity 0.3s;">
										✓ <?php esc_html_e( 'Changes saved', 'searchwiz' ); ?>
									</div>
								</div>

								<!-- Default WordPress Search Replacement -->
								<div style="background: #f9f9f9; padding: 20px; border: 1px solid #dcdcde; border-radius: 4px; margin-top: 20px;">
									<?php
									// Get current default search setting (default to enabled if not set)
									$searchwiz_admin_settings = get_option( 'searchwiz_settings', array() );
									$searchwiz_use_as_default = ! isset( $searchwiz_admin_settings['default_search'] ) || 1 === (int) $searchwiz_admin_settings['default_search'];
									?>
									<label style="display: flex; align-items: flex-start; cursor: pointer; font-size: 14px;">
										<input type="checkbox"
										       name="searchwiz_default_search[enabled]"
										       id="searchwiz_default_search_enabled"
										       value="on"
										       <?php checked( $searchwiz_use_as_default, true ); ?>
										       style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; margin-right: 12px; margin-top: 0px; width: 22px; height: 22px; min-width: 22px; flex-shrink: 0; cursor: pointer; border: 2.5px solid #2271b1 !important; border-radius: 4px; background: #fff !important; accent-color: #2271b1; transform: scale(1); transition: all 0.2s ease;" />
										<div style="flex: 1;">
											<div style="margin-bottom: 8px;">
												<strong><?php esc_html_e( 'Use SearchWiz as Default WordPress Search', 'searchwiz' ); ?></strong>
											</div>
											<div style="color: #666; font-size: 13px; line-height: 1.5;">
												<?php esc_html_e( 'When checked, SearchWiz replaces WordPress default search functionality sitewide (search widgets, search forms, etc.).', 'searchwiz' ); ?>
											</div>
										</div>
									</label>
									<!-- Auto-save notification -->
									<div id="searchwiz_default_search_saved" style="margin-top: 15px; background: #f0f0f1; color: #50575e; padding: 12px 20px; border-left: 4px solid #72aee6; font-size: 13px; opacity: 0; transition: opacity 0.3s;">
										✓ <?php esc_html_e( 'Changes saved', 'searchwiz' ); ?>
									</div>
								</div>
							<?php else : ?>
								<div style="background: #fff9e6; padding: 15px; border-left: 4px solid #f0ad4e; margin: 20px 0;">
									<p style="margin: 0; color: #666; font-size: 13px; line-height: 1.5;">
										<?php esc_html_e( 'Automatic theme integration is not available for your theme. You can still use SearchWiz with the shortcode [searchwiz] or add it via widgets.', 'searchwiz' ); ?>
									</p>
								</div>
							<?php endif; ?>

							<hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

							<h3><?php esc_html_e( 'Quick Setup Guide', 'searchwiz' ); ?></h3>
							<ol style="line-height: 2; font-size: 14px;">
								<li><?php esc_html_e( 'Visit the Search Box tab to customize the search input appearance', 'searchwiz' ); ?></li>
								<li><?php esc_html_e( 'Visit the Results tab to configure what content to search and how results appear', 'searchwiz' ); ?></li>
								<li><?php esc_html_e( 'Check Back-end Settings for analytics, indexing and performance options', 'searchwiz' ); ?></li>
							</ol>
						</div>
						<?php

					} elseif ( 'search-box' === $searchwiz_tab ) {
						// Search Box - Input styling, autocomplete, icon
						$searchwiz_settings_fields->is_do_settings_sections( 'searchwiz_search', 'searchwiz_search_settings' );
						$searchwiz_settings_fields->is_do_settings_sections( 'searchwiz_search', 'searchwiz_searchbox_appearance' );

						// Upgrade box for Search Box
						require_once __DIR__ . '/upgrade-box.php';
						searchwiz_render_upgrade_box( array(
							'title' => __( 'Upgrade to Visual Search Box Editor', 'searchwiz' ),
							'description' => __( 'Stop editing settings in forms. With Premium, design your search box visually with live preview and instant updates.', 'searchwiz' ),
							'features' => array(
								__( 'Click-to-edit interface - hover and click to customize', 'searchwiz' ),
								__( 'Live color picker on search box edges', 'searchwiz' ),
								__( 'Border radius adjuster on corners', 'searchwiz' ),
								__( 'Inline text editing for placeholders', 'searchwiz' ),
								__( 'Icon library with drag-to-resize', 'searchwiz' ),
								__( 'Real-time preview of all changes', 'searchwiz' ),
							),
							'gif_placeholder' => plugins_url( 'admin/assets/search-box-visual-editor.gif', SEARCHWIZ_PLUGIN_FILE ),
							'source' => 'search-box',
							'section' => 'frontend',
						) );

					} elseif ( 'results' === $searchwiz_tab ) {
						// Results - Search results appearance and indexing
						$searchwiz_settings_fields->is_do_settings_sections( 'searchwiz_search', 'searchwiz_search_display' );

						// Upgrade box for Results
						require_once __DIR__ . '/upgrade-box.php';
						searchwiz_render_upgrade_box( array(
							'title' => __( 'Advanced Results Customization', 'searchwiz' ),
							'description' => __( 'Take control of your search results with visual customization, custom templates, and advanced highlighting.', 'searchwiz' ),
							'features' => array(
								__( 'Drag-and-drop field ordering', 'searchwiz' ),
								__( 'Custom result templates with live preview', 'searchwiz' ),
								__( 'Advanced highlight colors and styles', 'searchwiz' ),
								__( 'Thumbnail size adjuster with real-time preview', 'searchwiz' ),
								__( 'Result card styling (borders, shadows, spacing)', 'searchwiz' ),
								__( 'Mobile-responsive layout options', 'searchwiz' ),
							),
							'gif_placeholder' => '', // Will be filled with actual GIF URL
							'source' => 'results',
							'section' => 'frontend',
						) );

					}
					// Upgrade tab removed for V1.1 - will be added post-approval
				} else {
					// Backend section
					if ( 'content' === $searchwiz_tab ) {
						// Content - What to search and index
						$searchwiz_settings_fields->is_do_settings_sections( 'searchwiz_search', 'searchwiz_search_index' );

						// Upgrade box for Content
						require_once __DIR__ . '/upgrade-box.php';
						searchwiz_render_upgrade_box( array(
							'title' => __( 'Advanced Content Indexing', 'searchwiz' ),
							'description' => __( 'Unlock powerful indexing features including PDFs, custom data sources, and multi-site support.', 'searchwiz' ),
							'features' => array(
								__( 'PDF and document indexing', 'searchwiz' ),
								__( 'Custom database table indexing', 'searchwiz' ),
								__( 'External API data sources', 'searchwiz' ),
								__( 'Multi-site network search', 'searchwiz' ),
								__( 'Custom field weight prioritization', 'searchwiz' ),
								__( 'Scheduled incremental indexing', 'searchwiz' ),
							),
							'gif_placeholder' => '',
							'source' => 'content',
							'section' => 'backend',
						) );

					} elseif ( 'performance' === $searchwiz_tab ) {
						// Performance - Indexing and search optimization
						?>
						<div style="background: #fff; padding: 30px; border: 1px solid #ccd0d4; margin-top: 20px;">
							<h2><?php esc_html_e( 'Performance Settings', 'searchwiz' ); ?></h2>
							<p><?php esc_html_e( 'Configure indexing and search performance options.', 'searchwiz' ); ?></p>

							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Indexing Batch Size', 'searchwiz' ); ?></th>
									<td>
										<input type="number" name="searchwiz_batch_size" value="<?php echo esc_attr( get_option( 'searchwiz_batch_size', 100 ) ); ?>" min="10" max="1000" />
										<p class="description"><?php esc_html_e( 'Number of posts to index per batch. Lower numbers use less memory but take longer.', 'searchwiz' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Auto-Index on Save', 'searchwiz' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="searchwiz_auto_index" value="1" <?php checked( get_option( 'searchwiz_auto_index', 1 ), 1 ); ?> />
											<?php esc_html_e( 'Automatically update index when posts are saved', 'searchwiz' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Cache Search Results', 'searchwiz' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="searchwiz_cache_results" value="1" <?php checked( get_option( 'searchwiz_cache_results', 1 ), 1 ); ?> />
											<?php esc_html_e( 'Enable caching for faster search results', 'searchwiz' ); ?>
										</label>
										<p class="description"><?php esc_html_e( 'Recommended for most sites. Clear cache after major content updates.', 'searchwiz' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
						<?php

					} elseif ( 'analytics' === $searchwiz_tab ) {
						// Analytics - Search insights and logs
						$searchwiz_settings_fields->is_do_settings_sections( 'searchwiz_search', 'searchwiz_search_analytics' );

						// Upgrade box for Analytics
						require_once __DIR__ . '/upgrade-box.php';
						searchwiz_render_upgrade_box( array(
							'title' => __( 'Premium Analytics & Insights', 'searchwiz' ),
							'description' => __( 'Get deep insights into search behavior with advanced analytics, conversion tracking, and custom reports.', 'searchwiz' ),
							'features' => array(
								__( 'Click-through rate (CTR) tracking', 'searchwiz' ),
								__( 'Search-to-purchase conversion tracking', 'searchwiz' ),
								__( 'Failed search alerts', 'searchwiz' ),
								__( 'Custom date range reports', 'searchwiz' ),
								__( 'Export analytics to CSV', 'searchwiz' ),
								__( 'Real-time search monitoring dashboard', 'searchwiz' ),
							),
							'gif_placeholder' => '',
							'source' => 'analytics',
							'section' => 'backend',
						) );

					}
					// Upgrade tab removed for V1.1 - will be added post-approval
				}

				echo '</div><!-- .search-form-editor-panel -->';

			?>
			</div><!-- #search-form-editor -->

			</div><!-- #searchtbox-container-1 -->
			<div id="searchtbox-container-2" class="postbox-container">
				<?php if ( current_user_can( 'is_edit_search_form' ) && 'index' === $searchwiz_tab ) : ?>
				<!-- Keep Reset button for index tab only -->
				<div id="submitdiv" class="searchbox">
					<div class="inside">
						<div class="submitbox" id="submitpost">
							<div id="major-publishing-actions">
								<div id="publishing-action">
									<?php
										$searchwiz_action = 'index-reset';
										$searchwiz_confirm_msg = __( "You are about to reset this index settings.\n  'Cancel' to stop, 'OK' to reset.", 'searchwiz' );
										$searchwiz_data = array(
											'action' => esc_html( $searchwiz_action ),
											'_wpnonce' => wp_create_nonce( $searchwiz_action ),
											'confirm_msg' => esc_html( $searchwiz_confirm_msg ),
										);
										$searchwiz_data = esc_attr( wp_json_encode( $searchwiz_data ) );
									?>
									<p>
										<input
											type="submit"
											id="is-index-reset"
											name="is-index-reset"
											class="reset button"
											value="<?php echo esc_attr( __( 'Reset', 'searchwiz' ) ); ?>"
											data-is="<?php echo esc_attr( $searchwiz_data ); ?>"
										/>
									</p>
								</div>
								<div class="clear"></div>
							</div><!-- #major-publishing-actions -->
						</div><!-- #submitpost -->
					</div>
				</div><!-- #submitdiv -->
				<?php endif; ?>

				<?php
				/**
				 * Bottom links (Documentation, Support, Contact, Rate) removed per issue #41
				 * These will be relocated to a Getting Started page in the future
				 */
				?>
			</div><!-- #searchtbox-container-2 -->
			</form>
		</div><!-- #post-body -->
		<br class="clear" />
		</div><!-- #poststuff -->

</div><!-- .wrap -->

<?php do_action( 'searchwiz_admin_footer' );