<?php
/**
 * Back-end Settings - Upgrade Tab
 *
 * Displays premium back-end features with animated GIFs
 *
 * @package SW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exits if accessed directly.
}
?>

<div class="searchwiz-upgrade-tab">
	<div class="searchwiz-upgrade-intro">
		<h2><?php esc_html_e( 'Upgrade to SearchWiz.ai', 'searchwiz' ); ?></h2>
		<p><?php esc_html_e( 'Unlock advanced back-end features for more control, better performance, and deeper insights.', 'searchwiz' ); ?></p>
		<p><strong><?php esc_html_e( 'Supercharge your search engine with these premium features:', 'searchwiz' ); ?></strong></p>
	</div>

	<!-- Feature 1: Advanced Indexing -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Advanced Indexing', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Index PDF, Word, PPT documents along with ACF, Meta Box, and any custom fields. Search repeater fields, flexible content, and nested data structures. Perfect for complex sites with rich content.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Indexing documents and custom fields with search results', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 2: Smart Relevance Engine -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Smart Relevance Engine', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'AI-powered relevance scoring that learns from user behavior. Boost products, prioritize fresh content, and customize ranking algorithms for your specific needs.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Adjusting relevance weights and seeing results reorder in real-time', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 2: Advanced Analytics Dashboard -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Advanced Analytics Dashboard', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Deep insights into search behavior: trending queries, zero-result searches, click-through rates, conversion tracking, and export reports. Understand what your users are really looking for.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Interactive analytics dashboard with charts and trends', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 3: Synonym & Stopword Management -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Advanced Synonym & Stopword Management', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Import synonym libraries, create redirect rules, manage stopwords, and handle misspellings automatically. Make your search smarter and more forgiving.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Adding synonyms and seeing expanded search results', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 4: Multi-Language & Multi-Site -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Multi-Language & Multi-Site Support', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Full WPML, Polylang, and WordPress Multisite compatibility. Index and search across multiple languages and sites with ease.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Switching between languages and searching translated content', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 5: Performance Optimization -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Performance Optimization Tools', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Advanced caching, query optimization, incremental indexing, and server-side rendering. Handle millions of products and posts with lightning-fast search speeds.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Performance metrics showing sub-100ms search times', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- Feature 6: Custom Field Indexing -->
	<div class="searchwiz-feature">
		<h3>
			<?php esc_html_e( 'Advanced Custom Field Indexing', 'searchwiz' ); ?>
			<span class="searchwiz-badge"><?php esc_html_e( 'PRO', 'searchwiz' ); ?></span>
		</h3>
		<p><?php esc_html_e( 'Index ACF, Meta Box, and any custom fields. Search repeater fields, flexible content, and nested data structures. Perfect for complex sites.', 'searchwiz' ); ?></p>
		<div class="searchwiz-feature-image">
			<?php esc_html_e( 'Animated demo: Selecting custom fields to index and seeing them in search results', 'searchwiz' ); ?>
			<br><br>
			<em><?php esc_html_e( '(Animated GIF placeholder - coming soon)', 'searchwiz' ); ?></em>
		</div>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank"><?php esc_html_e( 'Learn More →', 'searchwiz' ); ?></a>
	</div>

	<!-- CTA Section -->
	<div class="searchwiz-upgrade-intro" style="text-align: center;">
		<h2><?php esc_html_e( 'Ready to Upgrade?', 'searchwiz' ); ?></h2>
		<p><?php esc_html_e( 'Join hundreds of sites using SearchWiz.ai to deliver exceptional search experiences.', 'searchwiz' ); ?></p>
		<a href="https://searchwiz.ai/pro/" class="searchwiz-cta" target="_blank" style="font-size: 16px; padding: 15px 30px;">
			<?php esc_html_e( 'View All Pro Features & Pricing →', 'searchwiz' ); ?>
		</a>
	</div>

</div>
