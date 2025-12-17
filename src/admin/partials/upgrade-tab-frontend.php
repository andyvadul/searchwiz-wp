<?php
/**
 * Front-end Settings - Upgrade Tab
 *
 * Displays premium front-end features with animated GIFs
 *
 * @package SW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exits if accessed directly.
}
?>

<div class="searchwiz-upgrade-tab">
	<div class="searchwiz-upgrade-intro">
		<h2><?php esc_html_e( 'Upgrade to SearchWiz Pro', 'searchwiz' ); ?></h2>
		<p><?php esc_html_e( 'Unlock powerful front-end features to create the perfect search experience for your users.', 'searchwiz' ); ?></p>
		<p><strong><?php esc_html_e( 'Take your search interface to the next level with these premium features:', 'searchwiz' ); ?></strong></p>
	</div>

	<!-- Feature 1: Conversational Search -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Conversational Search + Actions', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Advanced conversational search with in-depth knowledge of your site and business. Advanced lead capture and easier search & item selection. Ask questions naturally and get intelligent answers with citations and actions.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: ChatGPT conversational interface answering customer questions', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 2: Custom Result Templates -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Custom Result Templates', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Design beautiful, custom result templates for each post type. Create unique layouts for products, posts, pages, and custom post types.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Drag-and-drop template builder with different layouts', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 3: Faceted Search Filters -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Faceted Search Filters', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Add powerful filters to search results. Let users refine by category, price, date, custom fields, and more. Perfect for WooCommerce stores.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Filtering products by price, category, and attributes', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 4: Visual Search Box Editor -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Visual Search Box Editor', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Customize your search box appearance with live preview - no coding required. Change colors, borders, fonts, sizes, and icons instantly. Perfect match for your brand with our intuitive visual editor.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image" style="padding: 0; background: transparent; border: none;">
			<img src="<?php echo esc_url( plugins_url( 'admin/assets/search-box-visual-editor.gif', SEARCHWIZ_PLUGIN_FILE ) ); ?>"
				 alt="<?php esc_attr_e( 'Visual Search Box Editor Demo', 'searchwiz' ); ?>"
				 style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" />
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- CTA Section -->
	<div class="searchwiz-upgrade-intro" style="text-align: center;">
		<h2><?php esc_html_e( 'Ready to Upgrade?', 'searchwiz' ); ?></h2>
		<p><?php esc_html_e( 'Join hundreds of sites using SearchWiz Pro to deliver exceptional search experiences.', 'searchwiz' ); ?></p>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank" style="font-size: 16px; padding: 15px 30px;">
			<?php esc_html_e( 'View All Pro Features & Pricing →', 'searchwiz' ); ?>
		</a>
	</div>

</div>
