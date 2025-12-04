<?php
/**
 * This class defines all plugin AJAX functionality for the site front end.
 *
 * @since 1.0.0
 * @package    SW
 * @subpackage SW/public
 * @author     SearchWiz Dev<dev@searchwiz.ai>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Ajax {

	/**
	 * Core singleton class
	 * @var self
	 */
	private static $_instance;

	/**
	 * Initializes this class
	 */
	public function __construct() {
        }

	/**
	 * Gets the instance of this class.
	 *
	 * @return self
	 */
	public static function getInstance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Simple test endpoint (no nonce check)
	 */
	function ajax_test() {
		wp_send_json_success( array( 'message' => 'Test endpoint works!' ) );
	}

	/**
	 * Load AJAX posts for React (JSON response).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function ajax_load_posts_json() {
		// Wrap entire function in try-catch to catch all errors
		try {
			$debug = SearchWiz_Debug::is_debug_mode();

			// DEBUG: Log the incoming request
			if ( $debug ) {
				error_log( '=== SearchWiz AJAX Request ===' );
				error_log( 'POST data: ' . print_r( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ), true ) );
				error_log( 'Action: ' . ( isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : 'not set' ) );
				error_log( 'Security: ' . ( isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : 'not set' ) );
			}

			// Verify nonce for security
			check_ajax_referer( 'searchwiz_nonce', 'security' );
			if ( $debug ) {
				error_log( 'Nonce verification PASSED' );
			}

		$search_post_id = isset( $_POST['id'] ) ? sanitize_text_field( absint( $_POST['id'] ) ) : 0;
		$page = isset( $_POST['page'] ) ? sanitize_text_field( absint( $_POST['page'] ) ) : 1;
		$search_term = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';

		if ( $debug ) {
			error_log( 'Search params - ID: ' . $search_post_id . ', Page: ' . $page . ', Term: ' . $search_term );
		}

		if ( empty( $search_term ) ) {
			wp_send_json_success( array( 'results' => array() ) );
		}

		// Use default settings if form ID is 0 or invalid
		$posts_per_page = 10;
		$allowed_post_types = array( 'post', 'page', 'product' ); // Default: posts, pages, WooCommerce products
		$search_comments = false; // Comments search disabled by default for security

		if ( $search_post_id > 0 ) {
			$search_form = SearchWiz_Search_Form::get_instance( $search_post_id );
			$is_settings = $search_form->prop( '_searchwiz_settings' );
			$is_includes = $search_form->prop( '_is_includes' );

			$posts_per_page = isset( $is_settings['posts_per_page'] ) ? $is_settings['posts_per_page'] : 10;

			// Get allowed post types from admin settings
			if ( isset( $is_includes['post_type'] ) && ! empty( $is_includes['post_type'] ) ) {
				$allowed_post_types = $is_includes['post_type'];
			}

			// Check if comment search is explicitly enabled by admin
			$search_comments = isset( $is_includes['search_comment'] ) && $is_includes['search_comment'];
		}

		// Try indexed search first (10x faster than WP_Query)
		global $wpdb;
		$index_table = $wpdb->prefix . 'searchwiz_index';
		$use_indexed_search = ($wpdb->get_var("SHOW TABLES LIKE '$index_table'") === $index_table);

		// TEMPORARILY DISABLED: Debugging search issues
		if ( false && $use_indexed_search && class_exists('SearchWiz_Indexer') ) {
			// Use FULLTEXT indexed search with relevance scoring
			$indexer = new SearchWiz_Indexer();
			$search_results = $indexer->search( $search_term, array(
				'post_type' => $allowed_post_types,
				'posts_per_page' => $posts_per_page,
				'paged' => $page
			));
			$total_found = $indexer->search_count( $search_term, array(
				'post_type' => $allowed_post_types
			));

			// Convert indexed results to post IDs
			$post_ids = array();
			$relevance_scores = array(); // Store relevance scores for later
			foreach ( $search_results as $result ) {
				$post_ids[] = $result->post_id;
				$relevance_scores[$result->post_id] = $result->final_score;
			}

			// Get post objects in the same order
			if ( ! empty( $post_ids ) ) {
				$args = array(
					'post__in' => $post_ids,
					'post_type' => $allowed_post_types,
					'post_status' => 'publish',
					'posts_per_page' => $posts_per_page,
					'orderby' => 'post__in', // Maintain relevance order
					'ignore_sticky_posts' => true,
				);
				$query = new WP_Query( $args );
			} else {
				// No results from index
				$query = new WP_Query( array( 'post__in' => array(0) ) );
			}
		} else {
			// Fallback to standard WP_Query (slower)
			// TRUE PAGINATION: Fetch one extra item to detect if there's a next page
			// This avoids expensive SQL_CALC_FOUND_ROWS query
			$args = array(
				's' => $search_term,
				'posts_per_page' => $posts_per_page + 1, // Request one extra item
				'paged' => $page,
				'post_status' => 'publish',
				'post_type' => $allowed_post_types,
				'no_found_rows' => true, // Skip SQL_CALC_FOUND_ROWS for performance
			);

			// Add comment search ONLY if explicitly enabled by admin
			if ( $search_comments ) {
				add_filter( 'posts_search', array( $this, 'extend_search_to_comments' ), 500, 2 );
				add_filter( 'posts_join', array( $this, 'join_comments_table' ), 500, 2 );
				add_filter( 'posts_groupby', array( $this, 'group_by_post_id' ), 500, 2 );
			}

			$query = new WP_Query( $args );

			// Remove filters after query execution
			if ( $search_comments ) {
				remove_filter( 'posts_search', array( $this, 'extend_search_to_comments' ), 500 );
				remove_filter( 'posts_join', array( $this, 'join_comments_table' ), 500 );
				remove_filter( 'posts_groupby', array( $this, 'group_by_post_id' ), 500 );
			}
		}

		$results = array();
		$products = array(); // Separate array for WooCommerce products
		$wc_active = class_exists( 'WooCommerce' );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				$post_type = get_post_type();

				// Get thumbnail - try WooCommerce product image first, fallback to post thumbnail
				$thumbnail_url = '';
				if ( $wc_active && $post_type === 'product' ) {
					$product_obj = wc_get_product( $post_id );
					if ( $product_obj ) {
						$thumbnail_url = wp_get_attachment_image_url( $product_obj->get_image_id(), 'thumbnail' );
					}
				}
				// Fallback to regular post thumbnail
				if ( ! $thumbnail_url ) {
					$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
				}

				// Base result data with search term highlighting
				// Use html_entity_decode to prevent double encoding (e.g., &amp; -> & before JSON encoding)
				$result = array(
					'id' => $post_id,
					'title' => $this->highlight_search_terms( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ), $search_term ),
					'excerpt' => $this->highlight_search_terms( html_entity_decode( get_the_excerpt(), ENT_QUOTES, 'UTF-8' ), $search_term ),
					'url' => get_permalink(),
					'type' => $post_type,
					'date' => get_the_date(),
					'thumbnail' => $thumbnail_url,
				);

				// If WooCommerce is active and this is a product, add product data
				if ( $wc_active && $post_type === 'product' ) {
					$product = wc_get_product( $post_id );
					if ( $product ) {
						// Check if product is purchasable and visible in catalog
						// Skip hidden products or products not visible when catalog is disabled
						$catalog_visibility = $product->get_catalog_visibility();
						$is_visible = $product->is_visible();

						// If WooCommerce catalog is disabled (shop page returns 404), don't show products
						$shop_page_id = wc_get_page_id( 'shop' );
						$shop_page_status = get_post_status( $shop_page_id );
						$catalog_enabled = ( $shop_page_status === 'publish' );

						// Check WooCommerce "Coming Soon" mode (site-wide setting)
						// WooCommerce stores this in 'woocommerce_coming_soon' option
						$wc_coming_soon = get_option( 'woocommerce_coming_soon', 'no' );
						$site_is_live = ( $wc_coming_soon === 'no' ); // 'no' means site is live, 'yes' or 'coming_soon' means not live

						// If site is in "Coming Soon" mode, only show products to admin users
						// This allows admins to test/tweak products while site is not live
						$show_products = $site_is_live || current_user_can( 'manage_options' );

						// Only show product if catalog is enabled, user can see products, and product is visible
						if ( $catalog_enabled && $show_products && $is_visible && in_array( $catalog_visibility, array( 'visible', 'catalog', 'search' ) ) ) {
							$result['product'] = array(
								'price_html' => html_entity_decode( $product->get_price_html(), ENT_QUOTES, 'UTF-8' ),
								'regular_price' => $product->get_regular_price(),
								'sale_price' => $product->get_sale_price(),
								'on_sale' => $product->is_on_sale(),
								'stock_status' => $product->get_stock_status(),
								'in_stock' => $product->is_in_stock(),
								'sku' => html_entity_decode( $product->get_sku(), ENT_QUOTES, 'UTF-8' ),
								'rating' => $product->get_average_rating(),
								'review_count' => $product->get_review_count(),
							);
							// Add to products array
							$products[] = $result;
						}
						// If not visible, skip this product entirely (don't add to results or products)
					} else {
						// Product object failed, add to regular results
						$results[] = $result;
					}
				} else {
					// Regular post/page - add to results
					$results[] = $result;
				}
			}
			wp_reset_postdata();
		}

		// TRUE PAGINATION: Check if we have more results
		// We fetched posts_per_page + 1, so if we have more items than posts_per_page, there's another page
		$total_items = count( $results ) + count( $products );
		$has_more = $total_items > $posts_per_page;

		// If we have more than requested, remove the extra item
		if ( $has_more ) {
			// Remove last item from whichever array has items
			if ( count( $products ) > 0 ) {
				array_pop( $products );
			} elseif ( count( $results ) > 0 ) {
				array_pop( $results );
			}
		}

		if ( $debug ) {
			error_log( 'Search completed successfully. Results: ' . count( $results ) . ', Products: ' . count( $products ) );
		}

		wp_send_json_success( array(
			'results' => $results,
			'products' => $products,
			'has_more' => $has_more, // Simple boolean - is there a next page?
			'woocommerce_active' => $wc_active,
			'current_page' => $page, // Send back current page for debugging
		) );

		} catch ( Exception $e ) {
			// Always log errors, even in production
			error_log( '!!! SearchWiz AJAX ERROR: ' . $e->getMessage() );
			error_log( 'Stack trace: ' . $e->getTraceAsString() );
			wp_send_json_error( array(
				'message' => 'Search failed: ' . $e->getMessage(),
				'trace' => $e->getTraceAsString()
			) );
		}
	}

	/**
	 * Get Taxonomies by Search Term
	 *
	 * @since 1.0.0
	 * 
	 * @param  string $taxonomy     Taxonomy Slug.
	 * @param  string $search_term  Search Term.
	 * @return array
	 */
        function get_taxonomies( $taxonomy, $search_term, $strict = false ) {

            $result = array();

			$all_terms = get_terms(array(
				'taxonomy' => $taxonomy, 
				'hierarchical' => false)
			);

            foreach ( $all_terms as $term ) {

                    // Used mb_strtolower() because, If search term is 'product' and actual taxonomy title is 'Product',
                    // Then, it does not match due to its case sensitive test.
                if ( ( $strict && mb_strtolower($term->name) == mb_strtolower($search_term)  ) || ( ! $strict && strpos( mb_strtolower($term->name), mb_strtolower($search_term) ) !== false ) ) {
                    $result[] = array(
                                            'term_id'  => $term->term_id,
                                            'name'     => $term->name,
                                            'slug'     => $term->slug,
                                            'taxonomy' => $term->taxonomy,
                                            'count'    => $term->count,
                                            'url'      => get_term_link( $term, $taxonomy ),
                    );
                }
            }

            return $result;
        }

	// NOTE: The following legacy HTML markup functions are no longer used by the React-based
	// JSON response system (ajax_load_posts_json). They remain for backward compatibility
	// but can be removed in a future cleanup.

	/**
	 * Term Title
	 *
	 * @since 1.0.0
	 * @deprecated No longer used by React JSON response
	 *
	 * @param  array $args      Term Arguments.
	 * @return void
	 */
	function term_title_markup( $args = array() ) {
		$taxonomy      = $args['taxonomy'];
		$search_term   = $args['search_term'];
		$term_title    = $args['title'];
		$wrapper_class = $args['wrapper_class'];

		$tags = $this->get_taxonomies( $taxonomy, $search_term, $args['strict'] );
		$is_term_title = apply_filters( 'searchwiz_term_title_markup', '', $taxonomy, $search_term, $term_title, $wrapper_class, $tags );

		if( ! $is_term_title && $tags ) { ?>
			<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<?php foreach ($tags as $key => $tag) { ?>
				<div data-id="<?php echo esc_attr( $tag['term_id'] ); ?>" class="is-ajax-search-post">
					<span class="is-ajax-term-label"><?php echo esc_html( $term_title ); ?></span>
                    	<div class="is-title">
							<a href="<?php echo esc_url( $tag['url'] ); ?>" data-id="<?php echo esc_attr( $tag['term_id'] ); ?>" data-slug="<?php echo esc_attr( $tag['slug'] ); ?>"><?php echo esc_attr( $tag['name'] ); ?> (<span class="is-term-count"><?php echo esc_attr( $tag['count'] ); ?></span>)</a>
                    	</div>
				</div>
			<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Term Details Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args      Term Arguments.
	 * @return void
	 */
	function product_details_markup( $args = array() ) {
		$taxonomy      = $args['taxonomy'];
		$search_term   = $args['search_term'];
		$field         = $args['field'];
		$wrapper_class = $args['wrapper_class'];

		$terms = $this->get_taxonomies( $taxonomy, $search_term );
		$is_markup = apply_filters( 'searchwiz_product_details_markup', '', $taxonomy, $search_term, $field, $wrapper_class, $terms );

		if ( ! $is_markup && $terms ) {
				ob_start();
				foreach ($terms as $key => $term) {
					$this->get_product_by_tax_id( $field, $term['term_id'], $taxonomy );
				}
				$details = ob_get_clean();
				if ( $details  ) {?>
					<div class="<?php echo esc_attr( $wrapper_class ); ?>">
				<?php
					echo wp_kses_post( $details );
				?>
				</div>
			<?php }
		}
	}

	/**
	 * Get products by taxonomy ID.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  int $cat_id       Term ID.
	 * @param  string $taxonomy  Taxonomy ID.
	 * @return void
	 */
	function get_product_by_tax_id( $field, $cat_id, $taxonomy ) {

                if ( ! class_exists( 'WooCommerce' ) ) {
                    return;
                }
            
		$product_list = isset( $field['product_list'] ) ? $field['product_list'] : 'all';
		$order_by     = isset( $field['order_by'] ) ? $field['order_by'] : 'date';
		$order        = isset( $field['order'] ) ? $field['order'] : 'desc';

		$query_args = array(
			'posts_per_page' => 4,
			'post_status'	 => 'publish',
			'post_type'	 => 'product',
			'no_found_rows'	 => 1,
			'order'		 => $order,
			'meta_query'	 => array(),
			'tax_query'      => array(
                                'relation'       => 'AND',
                            )
		);

		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
			$query_args['lang'] = $lang;
		}

		$query_args = apply_filters( 'searchwiz_get_product_by_tax_id', $query_args );

		switch ( $product_list ) {
			case 'featured' :
				$query_args[ 'tax_query' ][] = array(
					'taxonomy'         => 'product_visibility',
					'field'            => 'name',
					'terms'            => 'featured',
				);
				break;
			case 'onsale' :
				$query_args[ 'post__in' ] = wc_get_product_ids_on_sale();
				break;
		}

		switch ( $order_by ) {
			case 'price' :
				$query_args[ 'meta_key' ]	 = '_price';
				$query_args[ 'orderby' ]	 = 'meta_value_num';
				break;
			case 'rand' :
				$query_args[ 'orderby' ]	 = 'rand';
				break;
			case 'sales' :
				$query_args[ 'meta_key' ]	 = 'total_sales';
				$query_args[ 'orderby' ]	 = 'meta_value_num';
				break;
			default :
				$query_args[ 'orderby' ]	 = 'date';
		}

		$query_args[ 'tax_query' ][] = array(
			'taxonomy'		 => $taxonomy,
			'field'			 => 'id',
			'terms'			 => $cat_id,
			'include_children'	 => true,
		);

		$products = new WP_Query( $query_args );

		if ( $products->have_posts() ) {

			$product_count = 0;
			while ( $products->have_posts() ) {
				$products->the_post();
				$product_count++;
				?>
				<div data-id="<?php echo esc_attr( $cat_id ); ?>" class="is-ajax-search-post-details is-ajax-search-post-details-<?php echo esc_attr( $cat_id ); ?>">

				<?php if( 1 === $product_count ) { ?>
					<div class="is-ajax-term-wrap">
						<?php
						if( 'product_cat' === $taxonomy ) {
							echo '<span class="is-ajax-term-label">'.esc_html__('Category', 'searchwiz').':</span> ';
						} else {
							echo '<span class="is-ajax-term-label">'.esc_html__('Tag', 'searchwiz').':</span> ';
						}
						$term = get_term( $cat_id, $taxonomy );
						echo '<span class="is-ajax-term-name">'.esc_html( $term->name ).'</span>';
						?>
					</div>
				<?php } ?>

					<div class="is-search-sections">
						<?php
						$product = wc_get_product( get_the_ID() );
						global $post;
						$this->image_markup( $field, $product ); ?>

	            		<div class="right-section">
							<?php $this->title_markup( $field, $post, $product ); ?>
					        	<div class="meta">
									<div>
										<?php $this->product_price_markup( $field, $product ); ?>
										<?php $this->product_stock_status_markup( $field, $product ); ?>
										<?php $this->product_sku_markup( $field, $product ); ?>
									</div>
									<?php $this->date_markup( $field, $post ); ?>
				        			<?php $this->author_markup( $field ); ?>
				        			<?php $this->tags_markup( $field, $post ); ?>
				        			<?php $this->categories_markup( $field, $post ); ?>
					        	</div><!-- .meta -->

					        	<!-- Content -->
					        	<div class="is-search-content">
									<?php $this->description_markup( $field, $post ); ?>
								</div>
								<?php $this->product_sale_badge_markup( $field, $product );

							if( $product ) { ?>
								<div class="is-ajax-woocommerce-actions">
									<?php
									if ( function_exists( 'woocommerce_quantity_input' ) ) {
										woocommerce_quantity_input( array('input_name'  => 'is-ajax-search-product-quantity',), $product, true );
										echo esc_html(WC_Shortcodes::product_add_to_cart( array(
											'id'		 => esc_attr(get_the_ID()),
											'show_price' => false,
											'style'		 => '',
										) )); 
									} ?>
								</div>
							<?php } ?>
	            		</div>
					</div>
				</div>
				<?php
			}

		}
		wp_reset_postdata();
	}

	/**
	 * Image Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  object $post      Post object.
	 * @return void
	 */
	function image_markup( $field, $post ) {
		$image = '';
        $image_size = apply_filters( 'searchwiz_ajax_image_size', 'thumbnail' );
		$temp_id = 0;
		if ( 'product' === $post->post_type && ! SearchWiz_Help::is_woocommerce_inactive() ) {
			$_product = wc_get_product( $post );
			$temp_id = $_product->get_id();	
		} else {
			$temp_id = $post->ID;	
		} 
		if( 'attachment' === $post->post_type ) {
			$image = wp_get_attachment_image( $temp_id, $image_size );
		} else if( has_post_thumbnail( $temp_id) ) {
			$image = get_the_post_thumbnail( $temp_id, $image_size );
		}
		$is_markup = apply_filters( 'searchwiz_image_markup', '', $image, $field, $temp_id );
		if ( ! $is_markup && isset( $field['show_image'] ) && $field['show_image'] ) { ?>
                    <div class="left-section">
                        <div class="thumbnail">
                            <a href="<?php echo esc_url(get_the_permalink( $temp_id )); ?>"><?php echo wp_kses_post( $image ); ?></a>
                        </div>
                    </div>
		<?php }
	}

	/**
	 * Title Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  object $post      Post object.
	 * @param  mixed $product    Product or Empty.
	 * @return void
	 */
	function title_markup( $field, $post, $product ) {
		$is_markup = apply_filters( 'searchwiz_title_markup', '', $field, $post, $product );
		if ( ! $is_markup && '' !== get_the_title( $post->ID ) ) {
		?>
                <div class="is-title">
                        <a href="<?php echo esc_url(get_the_permalink( $post->ID )); ?>">
                                <?php if( $product && isset( $field['show_featured_icon'] ) && $field['show_featured_icon'] && $product->is_featured() ) { ?>
                                <svg class="is-featured-icon" focusable="false" aria-label="<?php esc_html_e( "Featured Icon", "searchwiz" ); ?>" version="1.1" viewBox="0 0 20 21" xmlns="http://www.w3.org/2000/svg" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <g fill-rule="evenodd" stroke="none" stroke-width="1"><g transform="translate(-296.000000, -422.000000)"><g transform="translate(296.000000, 422.500000)"><path d="M10,15.273 L16.18,19 L14.545,11.971 L20,7.244 L12.809,6.627 L10,0 L7.191,6.627 L0,7.244 L5.455,11.971 L3.82,19 L10,15.273 Z"></path></g></g></g>
                                </svg>
                                <?php } ?>
                                <?php echo esc_attr(get_the_title( $post->ID )); ?>
                        </a>
                </div>
    	<?php
                }
	}

	/**
	 * Author Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @return void
	 */
	function author_markup( $field ) {
		$is_markup = apply_filters( 'searchwiz_author_markup', '', $field );
		if ( ! $is_markup && isset( $field['show_author'] ) && $field['show_author'] ) { ?>
		    <span class="author vcard">
		        <?php echo sprintf( '<i>%s</i>', esc_html( 'By', 'Article written by', 'searchwiz' ) ); ?>
		        <a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
		            <?php echo esc_html( get_the_author() ); ?>
		        </a>
		    </span>
		<?php }
	}

	/**
	 * Date Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  object $post      Post object.
	 * @return void
	 */
	function date_markup( $field, $post ) {
		$is_markup = apply_filters( 'searchwiz_date_markup', '', $field, $post );
		if ( ! $is_markup && isset( $field['show_date'] ) && $field['show_date'] ) { ?>
		<span class="meta-date">
			<span class="posted-on">
				<?php
				$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
				if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				    $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
				}
				printf( esc_attr($time_string),
				    esc_attr( get_the_gmdate( 'c', $post->ID ) ),
				    esc_html( get_the_gmdate( '', $post->ID ) ),
				    esc_attr( get_the_modified_gmdate( 'c', $post->ID ) ),
				    esc_html( get_the_modified_gmdate( '', $post->ID ) )
				);
				 ?>
			</span>
		</span>
		<?php }
	}

	/**
	 * Tags Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  object $post      Post object.
	 * @return void
	 */
	function tags_markup( $field, $post ) {
		$is_markup = apply_filters( 'searchwiz_tags_markup', '', $field, $post );
		if ( ! $is_markup && isset( $field['show_tags'] ) && $field['show_tags'] ) { ?>
                <?php $terms = get_the_terms( $post->ID, $post->post_type.'_tag' );
                if ( $terms && ! is_wp_error( $terms ) ) { ?>
                <span class="is-meta-tag">
                    <?php echo esc_html(sprintf( '<i>%s</i>', __( 'Tagged with:', 'searchwiz' ) )); ?>
                    <span class="is-tags-links">
                    <?php foreach ( $terms as $key => $term ) { if ( $key ) { echo ', '; }?><a href="<?php echo esc_url(get_term_link( $term->term_id, $post->post_type.'_tag') ); ?> " rel="tag"><?php echo esc_html( $term->name ); ?></a><?php } ?>
                    </span>
                </span>
                <?php }
            }
        }

       /**
        * Categories Markup
        *
        * @since 1.0.0
        *
        * @param  array $field      Current stored values.
        * @param  object $post      Post object.
        * @return void
        */
	function categories_markup( $field, $post ) {
		$is_markup = apply_filters( 'searchwiz_categories_markup', '', $field, $post );
		if ( ! $is_markup && isset( $field['show_categories'] ) && $field['show_categories'] ) { ?>
                <?php 
                $tax_name = ( 'post' === $post->post_type ) ? 'category' : $post->post_type.'_cat';
                $terms = get_the_terms( $post->ID, $tax_name );
                if ( $terms && ! is_wp_error( $terms ) ) { ?>
                <span class="is-meta-category">
                    <?php echo esc_html(sprintf( '<i>%s</i>', __( 'Categories:', 'searchwiz' ) )); ?>
                    <span class="is-cat-links">
                    <?php foreach ( $terms as $key => $term ) { if ( $key ) { echo ', '; } ?><a href="<?php echo esc_attr(get_term_link( $term->term_id, $tax_name )); ?> " rel="tag"><?php echo esc_html( $term->name ); ?></a><?php } ?>
                    </span>
                </span>
                <?php }
            }
        }

	/**
	 * Description Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  mixed $single     Single product or not.
	 * @return void
	 */
	function description_markup( $field, $post, $single = false ) {
		$is_markup = apply_filters( 'searchwiz_description_markup', '', $field, $post, $single );
		if ( ! $is_markup && isset( $field['show_description'] ) && $field['show_description'] ) {		// Description either content or excerpt.

			// ALWAYS use global display settings for excerpt length
			// This ensures consistent behavior across all search results
			$display_settings = get_option( 'searchwiz_display_settings', array() );
			$excerpt_length = isset( $display_settings['excerpt_length'] ) ? absint( $display_settings['excerpt_length'] ) : 20;

			// DEBUG: Output visible debugging info when debug mode is on
			$debug_info = '';
			if ( isset( $_GET['searchwiz_debug'] ) && '1' === $_GET['searchwiz_debug'] ) {
				$debug_info = sprintf(
					'<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 5px; margin: 5px 0; font-size: 11px;">
						<strong>DEBUG excerpt_length:</strong> Global=%s | Using=%d | Post=%d:%s
					</div>',
					isset( $display_settings['excerpt_length'] ) ? $display_settings['excerpt_length'] : 'NOT SET',
					$excerpt_length,
					$post->ID,
					$post->post_type
				);
			}

			// NOTE: Per-form description_length is completely ignored
			// This was causing the global setting to never apply

			$is_post_content = $post->post_content;
			if ( 'product_variation' === $post->post_type ) {

				$_product = wc_get_product( $post->ID );

				$is_post_content =  $_product->description;
			}
            $content = wp_strip_all_tags( strip_shortcodes( $is_post_content ) );

    		// Note: $single parameter removed - always use global excerpt_length setting
                // Previously this forced 100 words for single results, ignoring global setting
                if ( isset( $field['description_source'] ) && 'excerpt' === $field['description_source'] ) {
                    $content = get_the_excerpt( $post->ID );
    		}

                // Removes all shortcodes
                $patterns = "/\[[\/]?[\s\S][^\]]*\]/";
                $replacements = "";
                $content = preg_replace( $patterns, $replacements, $content, -1 );
    		$content = wp_trim_words( $content, $excerpt_length, '...' );
    		?>
    		<?php if ( ! empty( $debug_info ) ) { echo wp_kses_post( $debug_info ); } ?>
    		<div class="is-ajax-result-description">
    			<?php echo wp_kses_post( $content ); ?>
    		</div>
    		<?php
		}
	}

	/**
	 * Product Stock Status Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  mixed $product    Product or Empty.
	 * @return void
	 */
	function product_stock_status_markup( $field, $product ) {
		$is_markup = apply_filters( 'searchwiz_product_stock_status_markup', '', $field, $product );
		if( ! $is_markup && $product ) {
			// Show stock status.
			if( isset( $field['show_stock_status'] ) && $field['show_stock_status'] ) {
				$stock_status = ( $product->is_in_stock() ) ? 'in-stock' : 'out-of-stock';
				$stock_status_text = ( 'in-stock' == $stock_status ) ? __( 'In stock', 'searchwiz' ) : __( 'Out of stock', 'searchwiz' );
				echo '<span class="stock-status is-'. esc_attr( $stock_status ).'">'. esc_html($stock_status_text).'</span>';
			}
		}
	}

	/**
	 * Product SKU Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  mixed $product    Product or Empty.
	 * @return void
	 */
	function product_sku_markup( $field, $product ) {
		$is_markup = apply_filters( 'searchwiz_product_sku_markup', '', $field, $product );
		if ( ! $is_markup && $product ) {
			// Show SKU.
			if( isset( $field['show_sku'] ) && $field['show_sku'] ) {
				$sku = $product->get_sku();
				echo '<span class="sku"><i>'.esc_html__( 'SKU:', 'searchwiz' ).'</i> '.esc_html( $sku ).'</span>';
			}
		}
	}

	/**
	 * Product Price Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  mixed $product    Product or Empty.
	 * @return void
	 */
	function product_price_markup( $field, $product ) {
		$is_markup = apply_filters( 'searchwiz_product_price_markup', '', $field, $product );
		if ( ! $is_markup && $product ) {
			if ( isset( $field['show_price'] ) && $field['show_price'] ) { 
				$hide_price_out_of_stock = isset( $field['hide_price_out_of_stock'] ) && $field['hide_price_out_of_stock'] ? $field['hide_price_out_of_stock'] : false;
				if ( $product->is_in_stock() || false === $hide_price_out_of_stock ) {?>
					<span class="is-prices">
						<?php echo wp_kses_post( $product->get_price_html() ); ?>
					</span>
					<?php
				} 
			}
		}
	}

	/**
	 * Product Sale Badge Markup
	 *
	 * @since 1.0.0
	 *
	 * @param  array $field      Current stored values.
	 * @param  mixed $product    Product or Empty.
	 * @return void
	 */
	function product_sale_badge_markup( $field, $product ) {
		$is_markup = apply_filters( 'searchwiz_product_sale_badge_markup', '', $field, $product );
		if ( ! $is_markup && $product ) {
			// Show sale badge.
			if ( isset( $field['show_sale_badge'] ) && $field['show_sale_badge'] ) {
				$on_sale = ( $product->is_in_stock() ) ? $product->is_on_sale() : '';
				if( $on_sale ) {
					echo '<div class="is-sale-badge">'.esc_html__( 'Sale!', 'searchwiz' ) .'</div>';
				}
			}
		}
	}

	/**
	 * Extend search query to include comments
	 *
	 * @param string $search Search SQL
	 * @param WP_Query $query Query object
	 * @return string Modified search SQL
	 */
	function extend_search_to_comments( $search, $query ) {
		global $wpdb;

		// Only modify search queries with a search term
		if ( empty( $search ) || ! $query->is_search() ) {
			return $search;
		}

		// Get the search term
		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $search;
		}

		// Add comment search to the WHERE clause
		$like = '%' . $wpdb->esc_like( $search_term ) . '%';
		$comment_search = $wpdb->prepare( " OR (cm.comment_content LIKE %s AND cm.comment_approved = '1')", $like );

		// Append comment search to existing search
		$search = str_replace( ')))', ")) OR (cm.comment_content LIKE '{$wpdb->esc_like( $search_term )}'))", $search );

		return $search . $comment_search;
	}

	/**
	 * Join comments table to search query
	 *
	 * @param string $join JOIN SQL
	 * @param WP_Query $query Query object
	 * @return string Modified JOIN SQL
	 */
	function join_comments_table( $join, $query ) {
		global $wpdb;

		// Only join for search queries
		if ( ! $query->is_search() ) {
			return $join;
		}

		// Left join comments table
		$join .= " LEFT JOIN {$wpdb->comments} AS cm ON ({$wpdb->posts}.ID = cm.comment_post_ID)";

		return $join;
	}

	/**
	 * Group results by post ID to avoid duplicates
	 *
	 * @param string $groupby GROUP BY SQL
	 * @param WP_Query $query Query object
	 * @return string Modified GROUP BY SQL
	 */
	function group_by_post_id( $groupby, $query ) {
		global $wpdb;

		// Only group for search queries
		if ( ! $query->is_search() ) {
			return $groupby;
		}

		// Group by post ID to avoid duplicate results when posts have multiple comments
		$groupby = "{$wpdb->posts}.ID";

		return $groupby;
	}

	/**
	 * Highlight search terms in text with <mark> tags
	 *
	 * Wraps matching search terms with <mark> tags for client-side highlighting.
	 * Uses word boundaries for accurate matching and handles multi-word searches.
	 *
	 * @since 1.0.0
	 * @param string $text The text to highlight
	 * @param string $search_term The search term(s) to highlight
	 * @return string Text with <mark> tags around matching terms
	 */
	private function highlight_search_terms( $text, $search_term ) {
		// Return original text if empty or if highlighting would fail
		if ( empty( $text ) || empty( $search_term ) ) {
			return $text;
		}

		// Ensure text is a valid UTF-8 string to prevent JSON encoding errors
		if ( ! mb_check_encoding( $text, 'UTF-8' ) ) {
			$text = mb_convert_encoding( $text, 'UTF-8', 'UTF-8' );
		}

		// Split multi-word searches
		$terms = explode( ' ', $search_term );

		foreach ( $terms as $term ) {
			$term = trim( $term );

			// Skip very short terms (< 2 chars) to avoid false matches
			if ( strlen( $term ) < 2 ) {
				continue;
			}

			// Escape special regex characters
			$term = preg_quote( $term, '/' );

			// Use word boundaries for accurate matching, case-insensitive
			$pattern = '/\b(' . $term . ')\b/iu';

			// Wrap matches with <mark> tags, handle preg_replace errors
			$result = @preg_replace( $pattern, '<mark>$1</mark>', $text );

			// If preg_replace failed, return original text
			if ( $result === null ) {
				return $text;
			}

			$text = $result;
		}

		// Final UTF-8 validation before returning
		if ( ! mb_check_encoding( $text, 'UTF-8' ) ) {
			return $text; // Return original if encoding is broken
		}

		return $text;
	}

	/**
	 * Get inline autocomplete suggestion for search input.
	 * Returns single best matching term for inline display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_inline_suggestion() {
		check_ajax_referer( 'searchwiz_nonce', 'security' );

		$query = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';

		if ( strlen( $query ) < 2 ) {
			wp_send_json_success( array( 'suggestion' => '' ) );
		}

		// Get top suggestion from builder
		if ( ! class_exists( 'SearchWiz_Suggestion_Builder' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sw-suggestion-builder.php';
		}

		$suggestions = SearchWiz_Suggestion_Builder::get_suggestions( $query, 1 );

		$suggestion = '';
		if ( ! empty( $suggestions ) ) {
			// Return only the completion part (remove the typed query)
			$term = $suggestions[0]['term'];
			// Only suggest if it's a prefix match
			if ( stripos( $term, $query ) === 0 ) {
				$suggestion = $term;
			}
		}

		wp_send_json_success( array(
			'suggestion' => $suggestion,
			'query'      => $query,
		) );
	}
}