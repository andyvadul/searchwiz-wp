<?php
/**
 * Upgrade Box Component
 *
 * Reusable upgrade prompt box with GIF placeholder and tracking
 *
 * @package SW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render upgrade box
 *
 * @param array $args Arguments for the upgrade box
 * @return void
 */
function searchwiz_render_upgrade_box( $args = array() ) {
	// Get parameters
	$title = isset( $args['title'] ) ? $args['title'] : __( 'Upgrade to Premium', 'searchwiz' );
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$features = isset( $args['features'] ) ? $args['features'] : array();
	$gif_placeholder = isset( $args['gif_placeholder'] ) ? $args['gif_placeholder'] : '';
	$source_tab = isset( $args['source'] ) ? $args['source'] : 'unknown';
	$section = isset( $args['section'] ) ? $args['section'] : 'frontend';

	// Build upgrade URL with tracking
	$upgrade_url = admin_url( 'admin.php?page=searchwiz-search-' . $section . '&tab=upgrade&source=' . urlencode( $source_tab ) );

	?>
<div class="searchwiz-upgrade-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 40px; border-radius: 8px; margin: 30px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
	<!-- Title -->
	<div style="text-align: center; margin-bottom: 30px;">
		<div style="display: inline-flex; align-items: center; gap: 10px;">
			<span style="font-size: 28px;">⭐</span>
			<h3 style="margin: 0; color: #fff; font-size: 24px; font-weight: 600;"><?php echo esc_html( $title ); ?></h3>
		</div>
	</div>

	<!-- GIF - Hero Element -->
	<div style="text-align: center; margin-bottom: 35px;">
		<?php if ( $gif_placeholder ) : ?>
			<img src="<?php echo esc_url( $gif_placeholder ); ?>"
			     alt="<?php echo esc_attr( $title ); ?>"
			     style="max-width: 600px; width: 100%; height: auto; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.2);" />
		<?php else : ?>
			<div style="max-width: 600px; margin: 0 auto; background: rgba(255,255,255,0.1); border: 2px dashed rgba(255,255,255,0.3); border-radius: 8px; padding: 60px 20px; text-align: center;">
				<div style="font-size: 64px; margin-bottom: 15px; opacity: 0.5;">🎬</div>
				<div style="font-size: 14px; opacity: 0.7; line-height: 1.6;">
					<?php esc_html_e( 'Animated demo coming soon', 'searchwiz' ); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Features in 3 Columns -->
	<?php if ( ! empty( $features ) ) : ?>
		<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 35px; max-width: 900px; margin-left: auto; margin-right: auto;">
			<?php foreach ( $features as $feature ) : ?>
				<div style="display: flex; align-items: start; gap: 8px;">
					<span style="flex-shrink: 0; margin-top: 2px; font-size: 16px;">✓</span>
					<span style="font-size: 13px; line-height: 1.5; opacity: 0.95;">
						<?php echo esc_html( $feature ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- Call to Action -->
	<div style="text-align: center; display: flex; gap: 20px; align-items: center; justify-content: center;">
		<a href="<?php echo esc_url( $upgrade_url ); ?>"
		   class="button button-primary sw-upgrade-cta"
		   data-source="<?php echo esc_attr( $source_tab ); ?>"
		   data-section="<?php echo esc_attr( $section ); ?>"
		   style="background: #fff; color: #667eea; border: none; padding: 14px 40px; font-weight: 600; text-decoration: none; border-radius: 6px; display: inline-block; box-shadow: 0 4px 8px rgba(0,0,0,0.15); font-size: 16px; transition: all 0.2s;">
			<?php esc_html_e( 'Upgrade Now', 'searchwiz' ); ?>
		</a>
		<a href="https://searchwiz.ai/pro/"
		   target="_blank"
		   style="color: #fff; text-decoration: none; border-bottom: 2px solid rgba(255,255,255,0.5); opacity: 0.95; font-size: 14px; font-weight: 500; padding-bottom: 2px; transition: all 0.2s;">
			<?php esc_html_e( 'Learn More', 'searchwiz' ); ?>
		</a>
	</div>
</div>
	<?php
}
