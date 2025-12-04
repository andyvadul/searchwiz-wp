<?php
/**
 * Help class.
 *
 * @package SW
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Help {

	private $screen;

	public function __construct( WP_Screen $screen ) {
		$this->screen = $screen;
	}

	public function set_help_tabs( $type ) {
		switch ( $type ) {
			case 'list':
				$this->screen->add_help_tab( array(
					'id'	  => 'list_overview',
					'title'   => __( 'Overview', 'searchwiz' ),
					'content' => $this->content( 'list_overview' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'list_available_actions',
					'title'	  => __( 'Available Actions', 'searchwiz' ),
					'content' => $this->content( 'list_available_actions' ) ) );

				$this->sidebar();

				return;
			case 'edit':
				$this->screen->add_help_tab( array(
					'id'	  => 'edit_overview',
					'title'	  => __( 'Overview', 'searchwiz' ),
					'content' => $this->content( 'edit_overview' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'includes',
					'title'	  => __( 'Includes', 'searchwiz' ),
					'content' => $this->content( 'includes' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'excludes',
					'title'   => __( 'Excludes', 'searchwiz' ),
					'content' => $this->content( 'excludes' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'edit_settings',
					'title'   => __( 'Options', 'searchwiz' ),
					'content' => $this->content( 'edit_settings' ) ) );

				$this->sidebar();

				return;
			case 'settings':
				$this->screen->add_help_tab( array(
					'id'	  => 'settings_overview',
					'title'	  => __( 'Overview', 'searchwiz' ),
					'content' => $this->content( 'settings_overview' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'search_to_menu',
					'title'	  => __( 'Menu Search', 'searchwiz' ),
					'content' => $this->content( 'search_to_menu' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'settings',
					'title'   => __( 'Settings', 'searchwiz' ),
					'content' => $this->content( 'settings' ) ) );

				$this->screen->add_help_tab( array(
					'id'	  => 'index',
					'title'   => __( 'Index', 'searchwiz' ),
					'content' => $this->content( 'index' ) ) );	

				$this->sidebar();

				return;
		}
	}

	private function content( $name ) {
		$content = array();

		$content['list_overview'] = '<p>' . __( "On this screen, you can manage search forms provided by SearchWiz plugin. You can create and manage an unlimited number of search forms. Each search form has a unique ID and search form shortcode ([searchwiz-search ...]). To insert a search form into a post or a text widget, insert the shortcode into the target.", 'searchwiz' ) . '</p>';

		$content['list_available_actions'] = '<p>' . __( "Hovering over a row in the search forms list will display action links that allow you to manage your search form. You can perform the following actions:", 'searchwiz' ) . '</p>';
		$content['list_available_actions'] .= '<p>' . sprintf( '<strong>%1$s</strong> - %2$s', __( 'Edit', 'searchwiz' ), __( 'Navigates to the editing screen for that search form. You can also reach that screen by clicking on the search form title.', 'searchwiz' ) ) . '</p>';
		$content['list_available_actions'] .= '<p>' . sprintf( '<strong>%1$s</strong> - %2$s', __( 'Duplicate', 'searchwiz' ), __( 'Clones that search form. A cloned search form inherits all content from the original, but has a different ID.', 'searchwiz' ) ) . '</p>';
		$content['list_available_actions'] .= '<p>' . sprintf( '<strong>%1$s</strong> - %2$s', __( 'Delete', 'searchwiz' ), __( 'Deletes the search form. The search form gets deleted permanently and its shortcode becomes void so you have to remove the shortcode if you have used it anywhere.', 'searchwiz' ) ) . '</p>';

		$content['edit_overview'] = '<p>' . __( "On this screen, you can edit a search form. A search form is comprised of the following components:", 'searchwiz' ) . '</p>';
		$content['edit_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Title', 'searchwiz' ), __( 'is the title of a search form. This title is only used for labeling a search form, and can be edited.', 'searchwiz' ) ) . '</p>';
		$content['edit_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Includes', 'searchwiz' ), __( 'provides options to control which content on the site is searchable.', 'searchwiz' ) ) . '</p>';
		$content['edit_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Excludes', 'searchwiz' ), __( 'provides options to exclude specific content from the search on the site.', 'searchwiz' ) ) . '</p>';
		$content['edit_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Options', 'searchwiz' ), __( 'provides a place where you can customize overall behavior of this search form.', 'searchwiz' ) ) . '</p>';

		$content['includes'] = '<p>' . __( "Control here which content you want to make searchable using this search form.", 'searchwiz' ) . '</p>';

		$content['excludes'] = '<p>' . __( "Configure the options here to exclude specific content from search perfomed using this search form.", 'searchwiz' ) . '</p>';

		$content['edit_settings'] = '<p>' . __( "Control here the overall behaviour of this search form.", 'searchwiz' ) . '</p>';

		$content['settings_overview'] = '<p>' . __( "On this screen, you can manage search added in the site navgation menu and configure settings that will affect all search forms and search functionality on the site. The settings screen comprised of the following sections:", 'searchwiz' ) . '</p>';
		$content['settings_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Menu Search', 'searchwiz' ), __( 'provides a place where you can customize the behavior of search form added in the site navgation menu.', 'searchwiz' ) ) . '</p>';
		$content['settings_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Settings', 'searchwiz' ), __( 'provides options to configure sitewide search functionality.', 'searchwiz' ) ) . '</p>';
		$content['settings_overview'] .= '<p>' . sprintf( '<strong>%1$s</strong> %2$s', __( 'Index', 'searchwiz' ), __( 'provides options to configure sitewide index functionality.', 'searchwiz' ) ) . '</p>';

		$content['search_to_menu'] = '<p>' . __( "Cofigure the options in this section to manage search added in the site navigation menu.", 'searchwiz' ) . '</p>';

		$content['settings'] = '<p>' . __( "Configure options in this section to manage sitewide search functionality.", 'searchwiz' ) . '</p>';

		$content['index'] = '<p>' . __( "Configure options in this section to manage sitewide indexing functionality.", 'searchwiz' ) . '</p>';

		if ( ! empty( $content[$name] ) ) {
			return $content[$name];
		}
	}

	public function sidebar() {
		$content  = '<p><strong>' . __( 'For more information:', 'searchwiz' ) . '</strong></p>';
		$content .= '<p><a href="https://searchwiz.ai/documentation/" target="_blank">' . __( 'Docs', 'searchwiz' ) . '</a></p>';
		$content .= '<p><a href="https://searchwiz.ai/support/" target="_blank">' . __( 'Support', 'searchwiz' ) . '</a></p>';
		$content .= '<p><a href="https://wordpress.org/support/plugin/searchwiz/reviews/#new-post" target="_blank">' . __( 'Give us a rating', 'searchwiz' ) . '</a></p>';

		$this->screen->set_help_sidebar( $content );
	}

	public static function help_info( $content ) { ?>
		<span class="is-help">
			<span class="is-info">
				<?php echo wp_kses_post( $content ); ?>
			</span>
		</span>
	<?php	
	}

	public static function is_woocommerce_inactive() {
		if ( class_exists( 'WooCommerce' ) ) {
			return false;
		}

		return true;
	}

	public static function woocommerce_inactive_field_notice( $echo = true ) {

        $woo_url = '<a href="'.admin_url('plugins.php').'" target="_blank">'.__( "WooCommerce", 'searchwiz' ).'</a>';
		// translators: %s: Name of the plugin.  
		$message = '<span class="notice-sw-info"> ' . sprintf( __( 'Activate %s plugin to use this option.', 'searchwiz' ), $woo_url ) . '</span>';

		if( $echo ) {
			echo wp_kses_post( $message );
		} else {
			return wp_kses_post( $message );
		}
	}
}
