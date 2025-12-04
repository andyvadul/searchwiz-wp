<?php
/**
 * Index options wrapper class.
 *
 * @package SW
 * @subpackage SW/includes
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Index_Options extends SearchWiz_Base_Options {


	/**
	 * WP Options Key to save this class properties.
	 *
	 * @since 1.0.0
	 */
	public static $ID = 'searchwiz_index';

	/**
	 * Singleton class.
	 *
	 * @since 1.0.0
	 * @var static
	 */
	protected static $_instance;

	/**
	 * Taxonomies indexing options constants.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TAX_OPT_ALL    = 'all';
	const TAX_OPT_SELECT = 'select';

	/**
	 * Custom Fields indexing options constants.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const META_OPT_ALL     = 'all';
	const META_OPT_VISIBLE = 'visible';
	const META_OPT_NONE    = 'none';
	const META_OPT_SELECT  = 'select';

	/**
	 * Punctuation handling options contants.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PUNC_OPT_REPLACE = 'replace';
	const PUNC_OPT_KEEP    = 'keep';
	const PUNC_OPT_REMOVE  = 'remove';

	/**
	 * Enable auto indexing of content.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $auto_index_enabled;

	/**
	 * Selected post types to index.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $post_types;

	/**
	 * Taxononomies index option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $tax_index_opt;

	/**
	 * Taxononomies selected to index.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $tax_selected;

	/**
	 * Meta fields index option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_fields_opt;

	/**
	 * MEta fields selected to index.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $meta_fields_selected;

	/**
	 * Post title index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_title;

	/**
	 * Post content index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_content;

	/**
	 * Post excerpt index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_excerpt;

	/**
	 * Taxonomy title index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_tax_title;

	/**
	 * Taxonomy description index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_tax_desp;

	/**
	 * WC Product SKU index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_product_sku;

	/**
	 * WC Product Variation post type index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_product_variation;

	/**
	 * Post comment index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_comments;

	/**
	 * Author info index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $index_author_info;

	/**
	 * Expand shortcodes before indexing flag.
	 *
	 * It is executed in post content and excerpt.
	 * If not enabled, the shortcode is removed.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $expand_shortcodes;

	/**
	 * Yoast SEO plugin override index flag.
	 *
	 * @since 1.0.0
	 * @var bool Index enabled if it is true.
	 */
	protected $yoast_no_index;

	/**
	 * Minimum word length to index.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $min_word_length;

	/**
	 * Trottle searches by limiting.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $throttle_searches;

	/**
	 * Hyphens handling option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hyphens;

	/**
	 * Quotes handling option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $quotes;

	/**
	 * Ampersands handling option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $ampersands;

	/**
	 * Decimals handling option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $decimals;

	/**
	 * Initializes this class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set default values in this object.
	 *
	 * @since 1.0.0
	 * @param bool $force Force set defaults.
	 */
	public function set_defaults( $force = false ) {

		if ( $force || ! get_option( self::$ID ) ) {

			$defaults = $this->get_defaults();
			$this->set_properties( $defaults );
		}

		do_action( 'searchwiz_options_set_defaults', $this );
	}

	/**
	 * Get default options for properties.
	 *
	 * @since 1.0.0
	 */
	public function get_defaults() {
		$defaults = array(
			'post_types'              => array(
				'post',
				'page',
				'attachment',
			),
			'tax_index_opt'           => self::TAX_OPT_ALL,
			'tax_selected'            => array(
				'category',
				'post_tag',
			),
			'auto_index_enabled'      => 1,
			'meta_fields_opt'         => self::META_OPT_VISIBLE,
			'meta_fields_selected'    => array(),
			'index_title'             => 1,
			'index_content'           => 1,
			'index_excerpt'           => 1,
			'index_tax_title'         => 1,
			'index_tax_desp'          => 0,
			'index_product_sku'       => 1,
			'index_product_variation' => 1,
			'index_comments'          => 1,
			'index_user_comments'     => 0,
			'index_author_info'       => 1,
			'expand_shortcodes'       => 0,
			'yoast_no_index'          => 0,
			'min_word_length'         => 3,
			'throttle_searches'       => 1,
			'hyphens'                 => self::PUNC_OPT_REPLACE,
			'quotes'                  => self::PUNC_OPT_REPLACE,
			'ampersands'              => self::PUNC_OPT_REPLACE,
			'decimals'                => self::PUNC_OPT_REMOVE,
		);

		if ( self::is_woocommerce_active() ) {
			$defaults['post_types'][] = 'product';
		}

		return apply_filters( 'searchwiz_index_options_set_defaults', $defaults );
	}

	/**
	 * Get taxonomies indexing options.
	 *
	 * @since 1.0.0
	 * @return array <string, string> The option key and label.
	 */
	public static function get_taxonomies_index_options() {
		return array(
			self::TAX_OPT_ALL    => esc_html__( 'Index all taxonomies ( of all post types )', 'searchwiz' ),
			self::TAX_OPT_SELECT => esc_html__( 'Index only selected taxonomies below', 'searchwiz' ),
		);
	}

	/**
	 * Get a list of taxonomies to not index.
	 *
	 * @since 1.0.0
	 * @return array An array of taxonomy names.
	 */
	public static function get_ignore_taxonomies() {
		/**
		 * Ignore taxonomies to index filter hook.
		 *
		 * @since 1.0.0
		 * @param array An array of taxonomy names to ignore.
		 */
		return apply_filters(
			'searchwiz_index_ignore_taxonomies',
			array(
				'nav_menu',               // Navigation menus.
				'link_category',          // Link categories.
				'amp_validation_error',   // AMP.
				'product_visibility',     // WooCommerce.
				'wpforms_log_type',       // WP Forms.
				'amp_template',           // AMP.
				'edd_commission_status',  // Easy Digital Downloads.
				'edd_log_type',           // Easy Digital Downloads.
				'elementor_library_type', // Elementor.
				'elementor_library_category', // Elementor.
				'elementor_font_type',    // Elementor.
				'wp_theme',               // WordPress themes.
			)
		);
	}

	/**
	 * Get meta fields indexing options.
	 *
	 * @since 1.0.0
	 * @return array <string, string> The option key and label.
	 */
	public static function get_meta_fields_options() {
		return array(
			self::META_OPT_VISIBLE => esc_html__( 'Index visible custom fields values', 'searchwiz' ),
			self::META_OPT_ALL     => esc_html__( 'Index all custom fields values', 'searchwiz' ),
			self::META_OPT_NONE    => esc_html__( 'Index no custom fields values', 'searchwiz' ),
			self::META_OPT_SELECT  => esc_html__( 'Index selected custom fields values', 'searchwiz' ),
		);
	}

	/**
	 * Gets all public meta keys of post types
	 *
	 * @since 1.0.0
	 * @param string $option The meta field option to fecth: all or visible.
	 * @global Object $wpdb WPDB object
	 * @return array <string> The meta keys found.
	 */
	public function get_meta_keys( $option = null ) {
		global $wpdb;

		if ( empty( $option ) ) {
			$option = $this->meta_fields_opt;
		}

		$meta_keys = array();
		$sql       = null;
		$is_fields = null;
		switch ( $option ) {
			case self::META_OPT_ALL:
				$sql = $wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);
				$sql = "select DISTINCT meta_key from $wpdb->postmeta pt LEFT JOIN $wpdb->posts p ON (pt.post_id = p.ID) ORDER BY meta_key ASC";
				break;
			case self::META_OPT_VISIBLE:
				$sql = "select DISTINCT meta_key from $wpdb->postmeta pt LEFT JOIN $wpdb->posts p ON (pt.post_id = p.ID) where meta_key NOT LIKE '\_%' ORDER BY meta_key ASC";
				break;
			case self::META_OPT_SELECT:
				$meta_keys = $this->meta_fields_selected;
				break;
			case self::META_OPT_NONE:
				$meta_keys = array();
				break;
		}

		$sql = apply_filters( 'searchwiz_get_meta_keys_query', $sql );

		if ( ! empty( $sql ) ) {
			$is_fields = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			foreach ( $is_fields as $field ) {
				if ( isset( $field->meta_key ) ) {
					$meta_keys[] = $field->meta_key;
				}
			}
		}

		if( $this->index_product_sku ) {
			$meta_keys[] = '_sku';
		}

		if ( ! empty( $meta_keys ) ) {
			$skips         = $this->get_skip_meta_fields();
			$skip_prefixed = $this->get_skip_meta_fields_prefixed();

			if ( ! empty( $meta_keys ) ) {
				foreach ( $meta_keys as $i => $key ) {
					if ( in_array( $key, $skips ) ) {
						unset( $meta_keys[ $i ] );
					}
					foreach ( $skip_prefixed as $skip ) {
						if ( 0 === strpos( $key, $skip ) ) {
							unset( $meta_keys[ $i ] );
							break;
						}
					}
				}
			}
		}

		/**
		 * Filter results of SQL query for meta keys
		 */
		return apply_filters( 'searchwiz_get_meta_keys', $meta_keys );
	}

	/**
	 * Get meta fields to skip.
	 *
	 * Apply filter is_index_skip_meta_fields on results.
	 *
	 * @since 1.0.0
	 * @return array The meta fields to skip.
	 */
	protected function get_skip_meta_fields() {
		$skip = array(
			// 'fusion_builder_content_backup',
			'classic-editor-remember',
			'php_everywhere_code',
			// 'wp-smpro-smush-data',
		);

		return apply_filters( 'searchwiz_index_skip_meta_fields', $skip );
	}

	/**
	 * Get meta fields prefixes to skip.
	 *
	 * Apply filter is_index_skip_meta_fields on results.
	 *
	 * @since 1.0.0
	 * @return array The meta fields prefixes to skip.
	 */
	protected function get_skip_meta_fields_prefixed() {
		$skip = array(
			'field_', // acf
			// 'fusion_', // fusion builder
			// '_fusion_', // fusion builder
			// 'avada_', // avada
			// 'pyre_', // avada
		);

		return apply_filters( 'searchwiz_index_skip_meta_fields_prefixed', $skip );
	}

	/**
	 * Get extra indexing options.
	 *
	 * Only show WooCommerce options if plugin is enabled.
	 *
	 * @since 1.0.0
	 * @return array <string, string> The option key and label.
	 */
	public static function get_extra_options() {
		$options = array(
			// translators: %1: Post title %2 File title 
			'index_title'             => sprintf( esc_html__( 'Index post title %1$s( File title )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			// translators: %1: Post content %2 File description
			'index_content'           => sprintf( esc_html__( 'Index post content %1$s( File description )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			// translators: %1: Post excerpt %2 File caption
			'index_excerpt'           => sprintf( esc_html__( 'Index post excerpt %1$s( File caption )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			// translators: %1: Category title %2 Taxonomy title
			'index_tax_title'         => sprintf( esc_html__( 'Index category/tag title %1$s( Taxonomy title )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			// translators: %1: Category decription %2 Taxonomy decription
			'index_tax_desp'          => sprintf( esc_html__( 'Index category/tag description %1$s( Taxonomy descripton )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			// translators: %1: Product SKU %2 Woocommerce title
			'index_product_sku'       => sprintf( esc_html__( 'Index product SKU %1$s( WooCommerce )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			// translators: %1: Product Variation %2 Woocommerce title
			'index_product_variation' => sprintf( esc_html__( 'Index product variations %1$s( WooCommerce )%2$s', 'searchwiz' ), '<i>', '</i>' ),
			'index_comments'          => esc_html__( 'Index approved comment content', 'searchwiz' ),
			'index_author_info'       => esc_html__( 'Index author display name ', 'searchwiz' ),
			'expand_shortcodes'       => esc_html__( 'Expand shortcodes before indexing ', 'searchwiz' ),
			'yoast_no_index'          => esc_html__( "Index posts marked as 'No Index' in Yoast SEO", 'searchwiz' ),
		);

		if ( ! self::is_woocommerce_active() ) {
			unset( $options['index_product_sku'] );
			unset( $options['index_product_variation'] );
		}

		if ( ! self::is_yoast_active() ) {
			unset( $options['yoast_no_index'] );
		}

		return $options;
	}

	/**
	 * Verifies if WooCommerce plugin is active.
	 *
	 * @since 1.0.0
	 * @return bool True if is active.
	 */
	public static function is_woocommerce_active() {
		$active = false;
		if ( class_exists( 'WooCommerce' ) || is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$active = true;
		}
		return $active;
	}

	/**
	 * Verifies if Yoast SEO plugin is active.
	 *
	 * @since 1.0.0
	 * @return bool True if is active.
	 */
	public static function is_yoast_active() {
		$active = false;
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$active = true;
		}
		return $active;
	}

	/**
	 * Get advanced indexing options.
	 *
	 * @since 1.0.0
	 * @return array <string, string> The option key and label.
	 */
	public static function get_advanced_options() {
		return array(
			'min_word_length'   => esc_html__( 'Minimum word length to index.', 'searchwiz' ),
			'throttle_searches' => esc_html__( 'Throttle searches by limiting searches results to 500 per term.', 'searchwiz' ),
			'hyphens'           => esc_html__( 'Hyphens and dashes punctuation.', 'searchwiz' ),
			'quotes'            => esc_html__( 'Apostrophes and quotes punctuation.', 'searchwiz' ),
			'ampersands'        => esc_html__( 'Ampersands punctuation.', 'searchwiz' ),
			'decimals'          => esc_html__( 'Decimal separators punctuation.', 'searchwiz' ),
		);
	}

	/**
	 * Get punctuation options.
	 *
	 * @since 1.0.0
	 * @return array <string, string> The option key and label.
	 */
	public static function get_punctuation_options( $field ) {

		$options = array(
			self::PUNC_OPT_REPLACE => esc_html__( 'Replace with space', 'searchwiz' ),
			self::PUNC_OPT_KEEP    => esc_html__( 'Keep', 'searchwiz' ),
			self::PUNC_OPT_REMOVE  => esc_html__( 'Remove', 'searchwiz' ),
		);

		if ( 'quotes' == $field ) {
			unset( $options[ self::PUNC_OPT_KEEP ] );
		}

		return $options;
	}

	/**
	 * Gets post_types property.
	 *
	 * Adds WC Product Variation if checked in options.
	 *
	 * @since 1.0.0
	 * @return array The post types.
	 */
	public function get_post_types() {
		$value = $this->post_types;

		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		if ( in_array( 'product', $value )
				&& $this->index_product_variation
				&& ! in_array( 'product_variation', $value )
		) {
			$value[] = 'product_variation';
		}

		$value = apply_filters( 'searchwiz_index_post_types', $value );
		return $value;
	}

	/**
	 * Gets index settings page url with section.
	 *
	 * @since 1.0.0
	 * @param string $field Optional. The option/section name to get the url for.
	 * @return string The settings page section url.
	 */
	public function get_index_settings_link( $field = '' ) {
		$section = '';
		switch ( $field ) {
			case 'post_types':
				$section = '#ui-id-2';
				break;
			case 'meta_fields':
				$section = '#ui-id-6';
				break;
			case 'extra':
				$section = '#ui-id-8';
				break;
		}
		$link = sprintf(
			' <a href="%s%s">%s</a>',
			admin_url( 'admin.php?page=searchwiz-search-settings&tab=index' ),
			$section,
			esc_html__( 'Settings', 'searchwiz' )
		);
		return $link;
	}

	/**
	 * Gets existing properties values.
	 *
	 * @since 1.0.0
	 * @param string $property The name of a property.
	 * @return mixed Returns mixed value of a property or NULL if a property doesn't exist.
	 */
	public function __get( $property ) {
		$value = null;
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'min_word_length':
					$value = intval( $this->$property );
					if ( $value < 1 ) {
						$value = 1;
					}
					break;

				case 'auto_index_enabled':
				case 'index_title':
				case 'index_content':
				case 'index_excerpt':
				case 'index_tax_title':
				case 'index_tax_desp':
				case 'index_product_sku':
				case 'index_product_variation':
				case 'index_comments':
				case 'index_author_info':
				case 'expand_shortcodes':
					$value = boolval( $this->$property );
					break;

				case 'post_types':
					$value = $this->get_post_types();
					break;

				case 'meta_fields_selected':
				case 'tax_selected':
					$value = array();
					if ( is_array( $this->$property ) ) {
						$value = $this->$property;
					}
					break;

				case 'tax_index_opt':
					$value = $this->$property;
					if ( ! array_key_exists( $value, self::get_taxonomies_index_options() ) ) {
						$value = $this->get_default( $property );
					}
					break;

				case 'meta_fields_opt':
					$value = $this->$property;
					if ( ! array_key_exists( $value, self::get_meta_fields_options() ) ) {
						$value = $this->get_default( $property );
					}
					break;

				case 'hyphens':
				case 'quotes':
				case 'ampersands':
				case 'decimals':
					$value = $this->$property;
					if ( ! array_key_exists( $value, self::get_punctuation_options( $property ) ) ) {
						$value = $this->get_default( $property );
					}
					break;

				default:
					$value = $this->$property;
					break;
			}
		}
		return $value;
	}

	/**
	 * Magic method to set protected properties.
	 * Sanitize fields before set.
	 *
	 * @since 1.0.0
	 * @param string $property The name of a property to associate.
	 * @param mixed  $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'min_word_length':
					$this->$property = intval( $value );
					break;

				case 'auto_index_enabled':
				case 'index_title':
				case 'index_content':
				case 'index_excerpt':
				case 'index_tax_title':
				case 'index_tax_desp':
				case 'index_product_sku':
				case 'index_product_variation':
				case 'index_comments':
				case 'index_author_info':
				case 'expand_shortcodes':
				case 'throttle_searches':
					$this->$property = boolval( $value );
					break;

				case 'post_types':
				case 'tax_selected':
				case 'meta_fields_selected':
					if ( is_array( $value ) ) {
						$this->$property = $value;
					}
					break;

				case 'tax_index_opt':
				case 'meta_fields_opt':
					$this->$property = sanitize_text_field( $value );
					break;

				case 'hyphens':
				case 'quotes':
				case 'ampersands':
				case 'decimals':
					if ( ! array_key_exists( $value, self::get_punctuation_options( $property ) ) ) {
						$value = $this->get_default( $property );
					}
					$this->$property = $value;
					break;

				default:
					$this->$property = $value;
					break;
			}
		}
	}
}
