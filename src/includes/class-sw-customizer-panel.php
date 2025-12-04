<?php
/**
 * Customizer Panel
 *
 * @package SW
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SearchWiz_Customizer_Panel' ) ) :

	/**
	 * Customizer Panel
	 *
	 * @since 1.0.0
	 */
	class SearchWiz_Customizer_Panel {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Panels
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private $panels = array();

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Add panel
		 *
		 * @since 1.0.0
		 *
		 * @param  string $key Unique Panel ID.
		 * @param  array  $data Panel sections and settings.
		 * @return void
		 */
		function add_panel( $key, $data ) {
			if ( ! isset( $this->panels[ $key ] ) ) {
				$this->panels[ $key ] = $data;
			}
		}

		/**
		 * Register Panels
		 *
		 * @since 1.0.0
		 *
		 * @param  object $customizer Customizer object.
		 * @return void
		 */
		function register_panels( $customizer ) {

			// Default priorities.
			$panel_priority   = 150;
			$section_priority = 150;
			$option_priority  = 150;

			// Register all panels.
			foreach ( $this->panels as $panel_key => $panel_data ) {

				// Register panel.
				$customizer->add_panel(
					$panel_key, array(
						'title'    => isset( $panel_data['title'] ) ? $panel_data['title'] : '',
						'priority' => $panel_priority,
					)
				);

				// Update panel priority.
				$panel_priority = $panel_priority + 5;

				// Register all sections.
				if ( isset( $panel_data['sections'] ) ) {

					foreach ( $panel_data['sections'] as $section_key => $section_data ) {

						// Add section.
						$customizer->add_section(
							$section_key, array(
								'panel'    => $panel_key,
								'title'    => $section_data['title'],
								'priority' => $section_priority,
							)
						);

						// Update section priority.
						$section_priority = $section_priority + 5;

						// Register all options.
						if ( isset( $section_data['options'] ) ) {

							foreach ( $section_data['options'] as $option_key => $option_data ) {

								if ( class_exists( $option_data['control']['class'] ) ) {

									// Set setting if not setting found.
									if ( ! isset( $option_data['setting'] ) && empty( $option_data['setting'] ) ) {
										$option_data['setting'] = array(
											'default' => '',
										);
									}

									$customizer->add_setting( $option_key, $option_data['setting'] );

									// Add control.
									$option_data['control']['section']  = $section_key;
									$option_data['control']['settings'] = $option_key;
									$option_data['control']['priority'] = $option_priority;

									$customizer->add_control(
										new $option_data['control']['class']( $customizer, $option_key, $option_data['control'] )
									);

									// Update option priority.
									$option_priority = $option_priority + 5;
								}
							}
							$option_priority = 5;
						}
					}
					$section_priority = 5;
				}
			}
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	SearchWiz_Customizer_Panel::get_instance();

endif;
