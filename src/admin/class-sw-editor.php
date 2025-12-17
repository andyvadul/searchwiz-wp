<?php
/**
 * Search Editor class.
 *
 * @package SW
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Search_Editor {
    private $search_form;

    private $panels = array();

    private $index_conflicts;

    public function __construct( SearchWiz_Search_Form $search_form ) {
        $this->search_form = $search_form;
        $this->index_conflicts = $this->get_index_conflicts();
    }

    function is_name( $string ) {
        return preg_match( '/^[A-Za-z][-A-Za-z0-9_:.]*$/', $string );
    }

    public function add_panel(
        $id,
        $title,
        $callback,
        $description
    ) {
        if ( $this->is_name( $id ) ) {
            $this->panels[$id] = array(
                'title'       => $title,
                'callback'    => $callback,
                'description' => $description,
            );
        }
    }

    public function display() {
        if ( empty( $this->panels ) ) {
            return;
        }
        echo '<ul id="search-form-editor-tabs">';
        $url = menu_page_url( 'searchwiz-search-new', false );
        $get_post = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        if ( $get_post > 0 ) {
            $url = menu_page_url( 'searchwiz-search', false ) . '&post=' . $get_post . '&action=edit';
        }
        $tab = 'includes';
        $request_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
        if ( ! empty( $request_tab ) ) {
            switch ( $request_tab ) {
                case 'excludes':
                    $tab = 'excludes';
                    break;
                case 'customize':
                    $tab = 'customize';
                    break;
                case 'ajax':
                    $tab = 'ajax';
                    break;
                case 'options':
                    $tab = 'options';
                    break;
            }
        }
        foreach ( $this->panels as $id => $panel ) {
            $class = ( $tab == $id ? 'active' : '' );
            echo sprintf(
                '<li id="%1$s-tab" class="%2$s"><a href="%3$s" title="%4$s">%5$s</a></li>',
                esc_attr( $id ),
                esc_attr( $class ),
                esc_url( $url . '&tab=' . $id ),
                esc_attr( $panel['description'] ),
                esc_html( $panel['title'] )
            );
        }
        echo '</ul>';
        echo sprintf( '<div class="search-form-editor-panel" id="%1$s">', esc_attr( $tab ) );
        $this->notice( $tab, $tab . '_panel' );
        $callback = $tab . '_panel';
        if ( method_exists( $this, $callback ) ) {
            $this->{$callback}( $this->search_form );
        } else {
            esc_html_e( 'The requested section does not exist.', 'searchwiz' );
        }
        echo '</div>';
    }

    public function notice( $id, $panel ) {
        echo '<div class="config-error"></div>';
    }

    /**
     * Gets all public meta keys of post types
     *
     * @global Object $wpdb WPDB object
     * @return Array array of meta keys
     */
    function is_meta_keys() {
        global $wpdb;
        $is_fields = $wpdb->get_results( apply_filters( 'searchwiz_meta_keys_query', "select DISTINCT meta_key from {$wpdb->postmeta} pt LEFT JOIN {$wpdb->posts} p ON (pt.post_id = p.ID) where meta_key NOT LIKE '\\_%' ORDER BY meta_key ASC" ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $meta_keys = array();
        if ( is_array( $is_fields ) && !empty( $is_fields ) ) {
            foreach ( $is_fields as $field ) {
                if ( isset( $field->meta_key ) ) {
                    $meta_keys[] = $field->meta_key;
                }
            }
        }
        /**
         * Filter results of SQL query for meta keys
         */
        return apply_filters( 'searchwiz_meta_keys', $meta_keys );
    }

    public function inc_exc_url( $section ) {
        $includes_url = '';
        $sec_name = __( "Search", 'searchwiz' );
        if ( 'excludes' === $section ) {
            $sec_name = __( "Exclude", 'searchwiz' );
        }
        $request_post = isset( $_REQUEST['post'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post'] ) ) : '';
        if ( $request_post ) {
            $includes_url = '<a href="' . esc_url( menu_page_url( 'searchwiz-search', false ) . '&post=' . absint( $request_post ) . '&action=edit&tab=' . $section ) . '">' . esc_html( $sec_name ) . '</a>';
        } else {
            $request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
            if ( 'searchwiz-search-new' === $request_page ) {
                $includes_url = '<a href="' . esc_url( menu_page_url( 'searchwiz-search-new', false ) . '&tab=' . $section ) . '">' . esc_html( $sec_name ) . '</a>';
            }
        }
        return $includes_url;
    }

    public function includes_panel( $post ) {
        $id = '_is_includes';
        $includes = $post->prop( $id );
        $excludes = $post->prop( '_is_excludes' );
        $settings = $post->prop( '_is_settings' );
        $default_search = ( NULL == $post->id() ? true : false );
        ?>
		<h4 class="panel-desc">
			<?php 
        esc_text_e( "Configure Searchable Content", 'searchwiz' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo esc_attr( $id );
        ?>">

		<div class="form-table form-table-panel-includes">

			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-post_type"><?php 
        esc_html_e( 'Post Types', 'searchwiz' );
        ?></label>
				<span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'searchwiz' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'searchwiz' );
        ?></a></span>
			</h3>
			<div>
				<?php 
        $content = __( 'Search selected post types.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        ?>
				<div>
	                <?php 
        $post_types = get_post_types( array(
            'public' => true,
        ), 'objects' );
        $post_types2 = array();
        if ( $default_search ) {
            $post_types2 = get_post_types( array(
                'public'              => true,
                'exclude_from_search' => false,
            ) );
        } else {
            if ( isset( $includes['post_type'] ) && !empty( $includes['post_type'] ) && is_array( $includes['post_type'] ) ) {
                $post_types2 = array_values( $includes['post_type'] );
            }
        }
        if ( !empty( $post_types ) ) {
            ?>
	                    <div class="is-cb-dropdown">
	                    	<div class="is-cb-title">
		                    <?php 
            if ( empty( $post_types2 ) ) {
                ?>
		                        <span class="is-cb-select"><?php 
                esc_html_e( 'Select Post Types', 'searchwiz' );
                ?></span>
		                        <span class="is-cb-titles"></span>
		                    <?php 
            } else {
                ?>
		                        <span style="display:none;" class="is-cb-select"><?php 
                esc_html_e( 'Select Post Types', 'searchwiz' );
                ?></span>
		                        <span class="is-cb-titles">
		                        <?php 
                foreach ( $post_types2 as $post_type2 ) {
                    if ( isset( $post_types[$post_type2] ) ) {
                        ?>
		                            	<span title="<?php 
                        echo esc_attr( $post_type2 );
                        ?>"><?php 
                        echo esc_html( $post_types[$post_type2]->labels->name );
                        ?></span>
		                        	<?php 
                    }
                }
                ?>
		                        </span>
		                    <?php 
            }
            ?>
	                    	</div>
		                    <div class="is-cb-multisel">
							<?php 
            foreach ( $post_types as $key => $post_type ) {
                $checked = ( $default_search && in_array( $key, $post_types2 ) || isset( $includes['post_type'][esc_attr( $key )] ) ? esc_attr( $key ) : 0 );
                ?>
								<label for="<?php 
                echo esc_attr( $id );
                ?>-post_type-<?php 
                echo esc_attr( $key );
                ?>">
								<input class="_is_includes-post_type" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-post_type-<?php 
                echo esc_attr( $key );
                ?>" name="<?php 
                echo esc_attr( $id );
                ?>[post_type][<?php 
                echo esc_attr( $key );
                ?>]" value="<?php 
                echo esc_attr( $key );
                ?>" <?php 
                checked( $key, $checked );
                ?>/>
								<span class="toggle-check-text"></span>
								<?php 
                echo esc_html(ucfirst( esc_attr( $post_type->labels->name ) ));
                ?></label>
							<?php 
            }
            ?>
							</div>
						</div>
					<?php 
        } else {
            ?>
						<span class="notice-sw-info">
							<?php 
            esc_html_e( 'No post types registered on the site.', 'searchwiz' );
            ?>
						</span>
					<?php 
        }
        if ( isset( $includes['post_type'] ) && is_array( $includes['post_type'] ) && 1 == count( $includes['post_type'] ) ) {
            $checked = ( isset( $includes['post_type_url'] ) ? 'y' : 'n' );
            ?>
	                            <br />
	                            <p class="check-radio">
	                            	<label for="<?php 
            echo esc_attr( $id );
            ?>-post_type_url">
	                            		<input class="_is_includes-post_type_url" type="checkbox" id="<?php 
            echo esc_attr( $id );
            ?>-post_type_url" name="<?php 
            echo esc_attr( $id );
            ?>[post_type_url]" value="y" <?php 
            checked( 'y', $checked );
            ?>/>
	                            		<span class="toggle-check-text"></span>
	                            		<?php 
            esc_html_e( "Do not display post_type in the search URL", 'searchwiz' );
            ?>
	                            	</label>
	                            </p>
	                 <?php 
        }
        ?>
				</div>
			</div>

            <?php 
        foreach ( $post_types2 as $post_type ) {
            if ( !isset( $post_types[$post_type] ) ) {
                continue;
            }
            ?>

				<h3 scope="row" class="is-p-type post-type-<?php 
            echo esc_attr( $post_type );
            ?>">
					<label for="<?php 
            echo esc_attr( $id );
            ?>-post__in">
						<?php 
            echo esc_html( $post_types[$post_type]->labels->name );
            if ( 'product' == $post_type && !SearchWiz_Help::is_woocommerce_inactive() ) {
                ?>
		                    <i><?php 
                esc_html_e( '( WooCommerce )', 'searchwiz' );
                ?></i>
		                <?php 
            } else {
                if ( 'attachment' == $post_type ) {
                    ?>
		                	<i><?php 
                    esc_html_e( '( Images, Videos, Audios, Docs, PDFs, Files & Attachments  )', 'searchwiz' );
                    ?></i>
		                <?php 
                }
            }
            ?>
					</label>
				</h3>
				<div class="post-type-<?php 
            echo esc_attr( $post_type );
            ?>">
					<div>
					<?php 
            $selected_pt = array();
            $posts_per_page = ( defined( 'DISABLE_SW_LOAD_ALL' ) || isset( $includes['post__in'] ) ? -1 : 100 );
            $posts = get_posts( array(
                'post_type'      => $post_type,
                'posts_per_page' => $posts_per_page,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) );
            if ( !empty( $posts ) ) {
                $tchecked = 'all';
                if ( isset( $includes['post__in'] ) ) {
                    foreach ( $posts as $post1 ) {
                        if ( in_array( $post1->ID, $includes['post__in'] ) ) {
                            array_push( $selected_pt, $post_type );
                            $tchecked = 'selected';
                        }
                    }
                    if ( 'all' === $tchecked ) {
                        // translators: %s: Post type
                        echo '<span class="notice-sw-info" style="margin-bottom: 20px;">' . sprintf( esc_html__( 'The %s are not searchable as the search form is configured to only search specific posts of another post type.', 'searchwiz' ), esc_html(strtolower( esc_attr( $post_types[$post_type]->labels->name ) )) ) . '</span>';
                    }
                }
                echo '<p class="check-radio"><label for="' . esc_attr( $post_type ) . '-post-search_all" ><input class="is-post-select" type="radio" id="' . esc_attr( $post_type ) . '-post-search_all" name="' . esc_attr( $post_type ) . 'i[post_search_radio]" value="all" ' . checked( 'all', $tchecked, false ) . '/>';
                // translators: %s: Post type
                echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search all %s", 'searchwiz' ), esc_html(strtolower( esc_attr( $post_types[$post_type]->labels->name ) ) )) . '</label></p>';
                echo '<p class="check-radio"><label for="' . esc_attr( $post_type ) . '-post-search_selected" ><input class="is-post-select" type="radio" id="' . esc_attr( $post_type ) . '-post-search_selected" name="' . esc_attr( $post_type ) . 'i[post_search_radio]" value="selected" ' . checked( 'selected', $tchecked, false ) . '/>';
                // translators: %s: Posts
                echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search only selected %s", 'searchwiz' ), esc_html(strtolower( esc_attr( $post_types[$post_type]->labels->name ) ) )) . '</label></p>';
            }
            echo '<div class="is-posts">';
            if ( !empty( $posts ) ) {
                echo '<div class="col-wrapper"><div class="col-title">';
                $col_title = '<span>' . esc_html( $post_types[$post_type]->labels->name ) . '</span>';
                $temp = '';
                foreach ( $posts as $post2 ) {
                    $checked = ( isset( $includes['post__in'] ) && in_array( $post2->ID, $includes['post__in'] ) ? $post2->ID : 0 );
                    $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                    $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
                }
                if ( 'selected' === $tchecked ) {
                    $col_title = '<strong>' . $col_title . '</strong>';
                }
                echo esc_attr($col_title) . '<input class="list-search" placeholder="' . esc_html__( "Search..", 'searchwiz' ) . '" type="text"></div>';
                echo '<select class="_is_includes-post__in" name="' . esc_attr( $id ) . '[post__in][]" multiple size="8" >';
                echo esc_attr($temp) . '</select>';
                if ( count( $posts ) >= 100 && !defined( 'DISABLE_SW_LOAD_ALL' ) && !isset( $includes['post__in'] ) ) {
                    echo '<div id="' . esc_attr( $post_type ) . '" class="load-all">' . esc_html__( 'Load All', 'searchwiz' ) . '</div>';
                }
                echo '</div><br />';
                echo '<label for="' . esc_attr( $id ) . '-post__in" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label><br />';
            } else {
                // translators: %s: Label name
                echo '<span class="notice-sw-info">' . sprintf( esc_html__( 'No %s created.', 'searchwiz' ), esc_attr($post_types[$post_type]->labels->name ) ) . '</span>';
            }
            echo '</div>';
            $tax_objs = get_object_taxonomies( $post_type, 'objects' );
            if ( !empty( $tax_objs ) ) {
                $terms_exist = false;
                $html = '<div class="is-taxes">';
                $selected_tax = false;
                foreach ( $tax_objs as $key => $tax_obj ) {
                    $terms = get_terms( array(
                        'taxonomy' => $key,
                        'lang'     => '',
                        'number'   => 1000,
                    ) );
                    if ( !empty( $terms ) && !empty( $tax_obj->labels->name ) ) {
                        $terms_exist = true;
                        $html .= '<div class="col-wrapper"><div class="col-title">';
                        $col_title = ucwords( str_replace( '-', ' ', str_replace( '_', ' ', esc_html( $tax_obj->labels->name ) ) ) );
                        if ( isset( $includes['tax_query'][$key] ) ) {
                            $col_title = '<strong>' . $col_title . '</strong>';
                            $selected_tax = true;
                        }
                        $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text"></div><input type="hidden" id="' . esc_attr( $id ) . '-tax_post_type" name="' . esc_attr( $id ) . '[tax_post_type][' . $key . ']" value="' . implode( ',', $tax_obj->object_type ) . '" />';
                        $html .= '<select class="_is_includes-tax_query" name="' . esc_attr( $id ) . '[tax_query][' . $key . '][]" multiple size="8" >';
                        foreach ( $terms as $key2 => $term ) {
                            $checked = ( isset( $includes['tax_query'][$key] ) && in_array( $term->term_taxonomy_id, $includes['tax_query'][$key] ) ? $term->term_taxonomy_id : 0 );
                            $html .= '<option value="' . esc_attr( $term->term_taxonomy_id ) . '" ' . selected( $term->term_taxonomy_id, $checked, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                        $html .= '</select></div>';
                    }
                }
                if ( $terms_exist ) {
                    $html .= '<br /><label for="' . esc_attr( $id ) . '-tax_query" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label><br />';
                    $html .= '</div>';
                    $checked = ( $selected_tax ? 'selected' : 'all' );
                    echo '<br /><p class="check-radio"><label for="' . esc_attr( $post_type ) . '-tax-search_all" ><input class="is-tax-select" type="radio" id="' . esc_attr( $post_type ) . '-tax-search_all" name="' . esc_attr( $post_type ) . 'i[tax_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>';
                    echo '<span class="toggle-check-text"></span>' . sprintf(
                        // translators: %1: Category name %2: Term 
                        esc_html( "Search %s of all taxonomies (%1\$s categories, tags & terms %2\$s)", 'searchwiz' ),
                        esc_html(strtolower( $post_types[$post_type]->labels->name )),
                        '<i>',
                        '</i>'
                    ) . '</label></p>';
                    echo '<p class="check-radio"><label for="' . esc_attr( $post_type ) . '-tax-search_selected" ><input class="is-tax-select" type="radio" id="' . esc_attr( $post_type ) . '-tax-search_selected" name="' . esc_attr( $post_type ) . 'i[tax_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
                    echo '<span class="toggle-check-text"></span>' . sprintf(
                        // translators: %1: Taxonomy name %2: Category %3: Term and Tag
                        esc_html( "Search %1\$s of only selected taxonomies (%2\$s categories, tags & terms %3\$s)", 'searchwiz' ),
                        esc_html(strtolower( esc_attr( $post_types[$post_type]->labels->name ) )),
                        '<i>',
                        '</i>'
                    ) . '</label></p>';
                    echo esc_attr($html);
                }
            }
            if ( 'product' == $post_type && !SearchWiz_Help::is_woocommerce_inactive() ) {
                $woo_sku_disable = '';
                $checked = ( isset( $includes['woo']['sku'] ) && $includes['woo']['sku'] ? 1 : 0 );
                echo '<br />';
                if ( '' !== $woo_sku_disable ) {
                    echo '<div class="upgrade-parent">';
                }
                echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-sku" ><input class="_is_includes-woocommerce" type="checkbox" ' . esc_attr($woo_sku_disable) . ' id="' . esc_attr( $id ) . '-sku" name="' . esc_attr( $id ) . '[woo][sku]" value="1" ' . checked( 1, $checked, false ) . '/>';
                echo '<span class="toggle-check-text"></span>' . esc_html__( "Search product SKU", 'searchwiz' ) . '</label></p>';
                $checked = ( isset( $includes['woo']['variation'] ) && $includes['woo']['variation'] ? 1 : 0 );
                echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-variation" ><input class="_is_includes-woocommerce" type="checkbox" ' . esc_attr($woo_sku_disable) . ' id="' . esc_attr( $id ) . '-variation" name="' . esc_attr( $id ) . '[woo][variation]" value="1" ' . checked( 1, $checked, false ) . '/>';
                echo '<span class="toggle-check-text"></span>' . esc_html__( "Search product variation", 'searchwiz' ) . '</label>';
                echo esc_url(SearchWiz_Admin::pro_link( 'pro_plus' ));
                if ( '' !== $woo_sku_disable ) {
                    echo '</div>';
                }
                echo '</p>';
                echo '<p class="is-index-conflicts">';
                echo esc_attr($this->get_conflicts_info( 'woo', 'sku' ));
                echo esc_attr($this->get_conflicts_info( 'woo', 'variation' ));
                echo '</p>';
            }
            if ( 'attachment' == $post_type && empty( $selected_pt ) ) {
                global $wp_version;
                if ( 4.9 <= $wp_version ) {
                    if ( !isset( $excludes['post_file_type'] ) ) {
                        echo '<br />';
                        $file_types = get_allowed_mime_types();
                        if ( !empty( $file_types ) ) {
                            $file_type_disable = '';
                            if ( '' !== $file_type_disable ) {
                                echo '<div class="upgrade-parent">';
                            }
                            ksort( $file_types );
                            $html = '<br /><div class="is-mime">';
                            $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text">';
                            $html .= '<select class="_is_includes-post_file_type" name="' . esc_attr( $id ) . '[post_file_type][]" ' . $file_type_disable . ' multiple size="8" >';
                            foreach ( $file_types as $key => $file_type ) {
                                $checked = ( isset( $includes['post_file_type'] ) && in_array( $file_type, $includes['post_file_type'] ) ? $file_type : 0 );
                                $html .= '<option value="' . esc_attr( $file_type ) . '" ' . selected( $file_type, $checked, false ) . '>' . esc_html( $key ) . '</option>';
                            }
                            $html .= '</select>';
                            echo esc_url(SearchWiz_Admin::pro_link( 'pro_plus') );
                            $html .= '<br /><label for="' . esc_attr( $id ) . '-post_file_type" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label><br />';
                            if ( isset( $includes['post_file_type'] ) ) {
                                $html .= __( 'Selected File Types :', 'searchwiz' );
                                foreach ( $includes['post_file_type'] as $post_file_type ) {
                                    $html .= '<br /><span style="font-size: 11px;">' . $post_file_type . '</span>';
                                }
                            }
                            $html .= '</div>';
                            $checked = ( isset( $includes['post_file_type'] ) && !empty( $includes['post_file_type'] ) ? 'selected' : 'all' );
                            echo '<p class="check-radio is-mime-radio"><label for="mime-search_all" ><input class="is-mime-select" type="radio" id="mime-search_all" name="mime_search_radio" value="all" ' . checked( 'all', $checked, false ) . '/>';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search all MIME types", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio is-mime-radio"><label for="mime-search_selected" ><input class="is-mime-select" type="radio" id="mime-search_selected" name="mime_search_radio" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search only selected  MIME types", 'searchwiz' ) . '</label></p>';
                            echo esc_attr($html);
                            echo '<span class="search-attachments-wrapper">';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_images"><input class="search-attachments" type="checkbox" id="' . esc_attr( $id ) . '-search_images" name="search_images" value="1" checked="checked" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search Images", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_videos"><input class="search-attachments" type="checkbox" id="' . esc_attr( $id ) . '-search_videos" name="search_videos" value="1" checked="checked" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search Videos", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_audios"><input class="search-attachments" type="checkbox" id="' . esc_attr( $id ) . '-search_audios" name="search_audios" value="1" checked="checked" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search Audios", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_text"><input class="search-attachments" type="checkbox" id="' . esc_attr( $id ) . '-search_text" name="search_text" value="1" checked="checked" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search Text Files", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_pdfs"><input class="search-attachments" type="checkbox" id="' . esc_attr( $id ) . '-search_pdfs" name="search_pdfs" value="1" checked="checked" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search PDF Files", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_docs"><input class="search-attachments" type="checkbox" id="' . esc_attr( $id ) . '-search_docs" name="search_docs" value="1" checked="checked"/>';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search Document Files", 'searchwiz' ) . '</label></p>';
                            echo '</span>';
                            if ( '' !== $file_type_disable ) {
                                echo '</div>';
                            }
                        }
                    } else {
                        // translators: %s: Section name
                        echo '<br /><span class="notice-sw-info">' . sprintf( esc_html__( "This search form is configured in the %s section to not search specific MIME types.", 'searchwiz' ), esc_url($this->inc_exc_url( 'excludes' )) ) . '</span>';
                    }
                } else {
                    echo '<br /><span class="notice-sw-info">' . esc_html__( 'You are using WordPress version less than 4.9 which does not support searching by MIME type.', 'searchwiz' ) . '</span>';
                }
            }
            ?>
			</div></div>
                        <?php 
        }
        ?>

			<h3 scope="row">
                            <label for="<?php 
        echo esc_attr( $id );
        ?>-extras"><?php 
        echo esc_html( __( 'Extras', 'searchwiz' ) );
        ?></label>
                            <span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'searchwiz' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'searchwiz' );
        ?></a></span>
			</h3>
			<div><div class="includes_extras">
			<h4 scope="row" class="is-first-title">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-search_content"><?php 
        echo esc_html( __( 'Search Content', 'searchwiz' ) );
        ?></label>
			</h4>
			<?php 
        $checked = ( $default_search || isset( $includes['search_title'] ) && $includes['search_title'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_title"><input class="_is_includes-post_type" type="checkbox" id="' . esc_attr( $id ) . '-search_title" name="' . esc_attr( $id ) . '[search_title]" value="1" ' . checked( 1, $checked, false ) . '/>';
        // translators: %1: Post title %2: File title
        echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search post title %1\$s( File title )%2\$s", 'searchwiz' ), '<i>', '</i>' ) . '</label></p>';
        echo esc_attr($this->get_conflicts_info( 'search_title' ));
        $checked = ( $default_search || isset( $includes['search_content'] ) && $includes['search_content'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_content"><input class="_is_includes-post_type" type="checkbox" id="' . esc_attr( $id ) . '-search_content" name="' . esc_attr( $id ) . '[search_content]" value="1" ' . checked( 1, $checked, false ) . '/>';
        // translators: %1: Post content %2: File description
        echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search post content %1\$s( File description )%2\$s", 'searchwiz' ), '<i>', '</i>' ) . '</label></p>';
        echo esc_attr($this->get_conflicts_info( 'search_content' ));
        $checked = ( $default_search || isset( $includes['search_excerpt'] ) && $includes['search_excerpt'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_excerpt"><input class="_is_includes-post_type" type="checkbox" id="' . esc_attr( $id ) . '-search_excerpt" name="' . esc_attr( $id ) . '[search_excerpt]" value="1" ' . checked( 1, $checked, false ) . '/>';
        // translators: %1: File caption %2: File
        echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search post excerpt %1\$s( File caption )%2\$s", 'searchwiz' ), '<i>', '</i>' ) . '</label></p>';
        echo esc_attr($this->get_conflicts_info( 'search_excerpt' ));
        $checked = ( isset( $includes['search_tax_title'] ) && $includes['search_tax_title'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_tax_title" ><input class="_is_includes-tax_query" type="checkbox" id="' . esc_attr( $id ) . '-search_tax_title" name="' . esc_attr( $id ) . '[search_tax_title]" value="1" ' . checked( 1, $checked, false ) . '/>';
        // translators: %1: Tag title %2: Tag
        echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search category/tag title %1\$s( Displays posts of the category/tag )%2\$s", 'searchwiz' ), '<i>', '</i>' ) . '</label></p>';
        echo esc_attr($this->get_conflicts_info( 'search_tax_title' ));
        $checked = ( isset( $includes['search_tax_desp'] ) && $includes['search_tax_desp'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_tax_desp" ><input class="_is_includes-tax_query" type="checkbox" id="' . esc_attr( $id ) . '-search_tax_desp" name="' . esc_attr( $id ) . '[search_tax_desp]" value="1" ' . checked( 1, $checked, false ) . '/>';
        // translators: %1: Category Description %2: Category    
        echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Search category/tag description %1\$s( Displays posts of the category/tag )%2\$s", 'searchwiz' ), '<i>', '</i>' ) . '</label></p>';
        echo esc_attr($this->get_conflicts_info( 'search_tax_desp' ));
        if ( isset( $includes['tax_query'] ) ) {
            $tax_rel_disable = '';
            if ( isset( $includes['tax_post_type'] ) ) {
                $temp = array();
                foreach ( $includes['tax_query'] as $key => $value ) {
                    if ( isset( $includes['tax_post_type'][$key] ) && (empty( $temp ) || !in_array( $includes['tax_post_type'][$key], $temp )) ) {
                        array_push( $temp, $includes['tax_post_type'][$key] );
                    }
                    if ( count( $temp ) > 1 ) {
                        $tax_rel_disable = 'disabled';
                        $includes['tax_rel'] = "OR";
                        break;
                    }
                }
            }
            echo '<p class="check-radio">';
            if ( 'disabled' == $tax_rel_disable ) {
                echo '<br />';
                $content = __( 'Note: The below option is disabled and set to OR as you have configured the search form to search multiple taxonomies.', 'searchwiz' );
                SearchWiz_Help::help_info( $content );
            }
            $checked = ( isset( $includes['tax_rel'] ) && "AND" == $includes['tax_rel'] ? "AND" : "OR" );
            echo '<label for="' . esc_attr( $id ) . '-tax_rel_and" ><input class="_is_includes-tax_query" type="radio" id="' . esc_attr( $id ) . '-tax_rel_and" ' . esc_attr($tax_rel_disable) . ' name="' . esc_attr( $id ) . '[tax_rel]" value="AND" ' . checked( 'AND', $checked, false ) . '/>';
            echo '<span class="toggle-check-text"></span>' . esc_html__( "AND - Search posts having all the above selected category terms", 'searchwiz' ) . '</label></p>';
            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-tax_rel_or" ><input class="_is_includes-tax_query" type="radio" id="' . esc_attr( $id ) . '-tax_rel_or" ' . esc_attr($tax_rel_disable) . ' name="' . esc_attr( $id ) . '[tax_rel]" value="OR" ' . checked( 'OR', $checked, false ) . '/>';
            echo '<span class="toggle-check-text"></span>' . esc_html__( "OR - Search posts having any one of the above selected category terms", 'searchwiz' ) . '</label></p>';
        }
        ?>
			</div>
			<h4 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-custom_field"><?php 
        echo esc_html( __( 'Custom Fields', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
			<?php 
        $meta_keys = $this->is_meta_keys();
        if ( !empty( $meta_keys ) ) {
            $html = '<div class="col-wrapper is-metas">';
            $selected_meta = false;
            $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text">';
            $html .= '<select class="_is_includes-custom_field" name="' . esc_attr( $id ) . '[custom_field][]" multiple size="8" >';
            foreach ( $meta_keys as $meta_key ) {
                $checked = ( isset( $includes['custom_field'] ) && in_array( $meta_key, $includes['custom_field'] ) ? $meta_key : 0 );
                if ( $checked ) {
                    $selected_meta = true;
                }
                $html .= '<option value="' . esc_attr( $meta_key ) . '" ' . selected( $meta_key, $checked, false ) . '>' . esc_html( $meta_key ) . '</option>';
            }
            $html .= '</select>';
            $html .= '<br /><label for="' . esc_attr( $id ) . '-custom_field" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label>';
            $html .= '</div>';
            $checked = ( $selected_meta ? 'selected' : 'all' );
            echo '<span class="check-radio"><label for="is-meta-search_selected" ><input class="is-meta-select" type="checkbox" id="is-meta-search_selected" name="is[meta_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search selected custom fields values", 'searchwiz' ) . '</label></span>';
            echo esc_attr($html);
            echo '<p class="is-index-conflicts">';
            echo esc_html($this->get_conflicts_info( 'custom_field' ));
            echo '</p>';
        }
        ?>
			</div>
			<h4 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-post_status"><?php 
        echo esc_html( __( 'Post Status', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Search posts having selected post statuses.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $post_statuses = get_post_stati();
        $post_status_disable = '';
        if ( !empty( $post_statuses ) ) {
            if ( '' !== $post_status_disable ) {
                echo esc_url(SearchWiz_Admin::pro_link());
            }
            echo '<div class="is-cb-dropdown">';
            echo '<div class="is-cb-title">';
            if ( $default_search || !isset( $includes['post_status'] ) || empty( $includes['post_status'] ) ) {
                $includes = array(
                    'post_status' => array(
                        'publish' => 'publish',
                        'inherit' => 'inherit',
                    ),
                );
            }
            echo '<span style="display:none;" class="is-cb-select">' . esc_html__( 'Select Post Status', 'searchwiz' ) . '</span><span class="is-cb-titles">';
            foreach ( $includes['post_status'] as $post_status2 ) {
                echo '<span title="' . esc_html( $post_status2 ) . '"> ' . esc_html(str_replace( '-', ' ', esc_attr( $post_status2 ))) . '</span>';
            }
            echo '</span>';
            echo '</div>';
            echo '<div class="is-cb-multisel">';
            foreach ( $post_statuses as $key => $post_status ) {
                $checked = ( isset( $includes['post_status'][esc_attr( $key )] ) ? $includes['post_status'][esc_attr( $key )] : 0 );
                $temp = ( 'publish' === $post_status || 'inherit' === $post_status ? '' : $post_status_disable );
                echo '<label for="' . esc_attr( $id ) . '-post_status-' . esc_attr( $key ) . '"><input class="_is_includes-post_status" type="checkbox" ' . esc_attr($temp) . ' id="' . esc_attr( $id ) . '-post_status-' . esc_attr( $key ) . '" name="' . esc_attr( $id ) . '[post_status][' . esc_attr( $key ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $key, $checked, false ) . '/>';
                echo '<span class="toggle-check-text"></span> ' . esc_html(ucwords( str_replace( '-', ' ', esc_attr( $post_status ) )) ) . '</label>';
            }
            echo '</div></div>';
        }
        ?>
			</div></div>
			<h4 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-author"><?php 
        echo esc_html( __( 'Authors', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Search posts created by selected authors.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $author_disable = '';
        if ( !isset( $excludes['author'] ) ) {
            $authors = get_users( array(
                'fields'       => array('ID', 'display_name'),
                'role__not_in' => 'subscriber',
                'orderby'      => 'post_count',
                'order'        => 'DESC',
            ) );
            if ( !empty( $authors ) ) {
                if ( '' !== $author_disable ) {
                    echo '<div class="upgrade-parent">' . esc_url(SearchWiz_Admin::pro_link());
                }
                echo '<div class="is-cb-dropdown">';
                echo '<div class="is-cb-title">';
                if ( !isset( $includes['author'] ) || empty( $includes['author'] ) ) {
                    echo '<span class="is-cb-select">' . esc_html__( 'Searches all author posts', 'searchwiz' ) . '</span><span class="is-cb-titles"></span>';
                } else {
                    echo '<span style="display:none;" class="is-cb-select">' . esc_html__( 'Searches all author posts', 'searchwiz' ) . '</span><span class="is-cb-titles">';
                    foreach ( $includes['author'] as $author2 ) {
                        $display_name = get_userdata( $author2 );
                        if ( $display_name ) {
                            echo '<span title="' . esc_html(ucfirst( esc_attr( $display_name->display_name )) ) . '"> ' . esc_html( $display_name->display_name ) . '</span>';
                        }
                    }
                    echo '</span>';
                }
                echo '</div>';
                echo '<div class="is-cb-multisel">';
                foreach ( $authors as $author ) {
                    $post_count = count_user_posts( $author->ID );
                    // Move on if user has not published a post (yet).
                    if ( !$post_count ) {
                        continue;
                    }
                    $checked = ( isset( $includes['author'][esc_attr( $author->ID )] ) ? $includes['author'][esc_attr( $author->ID )] : 0 );
                    echo '<label for="' . esc_attr( $id ) . '-author-' . esc_attr( $author->ID ) . '"><input class="_is_includes-author" type="checkbox" ' . esc_attr($author_disable) . ' id="' . esc_attr( $id ) . '-author-' . esc_attr( $author->ID ) . '" name="' . esc_attr( $id ) . '[author][' . esc_attr( $author->ID ) . ']" value="' . esc_attr( $author->ID ) . '" ' . checked( $author->ID, $checked, false ) . '/>';
                    echo '<span class="toggle-check-text"></span> ' . esc_html(ucfirst( esc_attr( $author->display_name) ) ) . '</label>';
                }
                echo '</div></div>';
            }
        } else {
            // translators: %s: Section name    
            echo '<br /><span class="notice-sw-info">' . sprintf( esc_html( "This search form is configured in the %s section to not search for specific author posts.", 'searchwiz' ), esc_url($this->inc_exc_url( 'excludes' )) ) . '</span>';
        }
        if ( '' !== $author_disable ) {
            echo '</div>';
        }
        $checked = ( isset( $includes['search_author'] ) && $includes['search_author'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_author" ><input class="_is_includes-author" type="checkbox" id="' . esc_attr( $id ) . '-search_author" name="' . esc_attr( $id ) . '[search_author]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Search author Display Name and display the posts created by that author", 'searchwiz' ) . '</label></p>';
        echo esc_html($this->get_conflicts_info( 'search_author' ));
        ?>
			</div></div>

			<h4 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-comment_count"><?php 
        echo esc_html( __( 'Comments', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
				<?php 
        echo '<div>';
        $comment_count_disable = '';
        if ( '' !== $comment_count_disable ) {
            echo '<div class="upgrade-parent">' . esc_url(SearchWiz_Admin::pro_link());
        }
        echo '<label for="' . esc_attr( $id ) . '-comment_count-compare"> ' . esc_html( __( 'Search posts having number of comments', 'searchwiz' ) ) . '</label><select class="_is_includes-comment_count" name="' . esc_attr( $id ) . '[comment_count][compare]" ' . esc_attr( $comment_count_disable ) . ' style="min-width: 50px;">';
        $checked = ( isset( $includes['comment_count']['compare'] ) ? htmlspecialchars_decode( $includes['comment_count']['compare'] ) : '=' );
        $compare = array(
            '=',
            '!=',
            '>',
            '>=',
            '<',
            '<='
        );
        foreach ( $compare as $d ) {
            echo '<option value="' . esc_attr( htmlspecialchars_decode( $d ) ) . '" ' . selected( $d, $checked, false ) . '>' . esc_html( $d ) . '</option>';
        }
        echo '</select>';
        echo '<select class="_is_includes-comment_count" name="' . esc_attr( $id ) . '[comment_count][value]" ' . esc_attr( $comment_count_disable ) . ' >';
        $checked = ( isset( $includes['comment_count']['value'] ) ? $includes['comment_count']['value'] : 'na' );
        echo '<option value="na" ' . selected( 'na', $checked, false ) . '>' . esc_html( __( 'NA', 'searchwiz' ) ) . '</option>';
        for ($d = 0; $d <= 999; $d++) {
            echo '<option value="' . esc_attr( $d ) . '" ' . selected( $d, $checked, false ) . '>' . esc_html( $d ) . '</option>';
        }
        echo '</select>';
        if ( '' !== $comment_count_disable ) {
            echo '</div>';
        }
        $checked = ( isset( $includes['search_comment'] ) && $includes['search_comment'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_comment" ><input class="_is_includes-comment_count" type="checkbox" id="' . esc_attr( $id ) . '-search_comment" name="' . esc_attr( $id ) . '[search_comment]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Search approved comment content", 'searchwiz' ) . '</label></p>';
        echo esc_html($this->get_conflicts_info( 'search_comment' ));
        ?>
			</div></div>

			<h4 scope="row">
                            <label for="<?php 
        echo esc_attr( $id );
        ?>-has_password"><?php 
        echo esc_html( __( 'Password Protected', 'searchwiz' ) );
        ?></label>
			</h4>
			<div><div>
				<?php 
        $checked = ( isset( $includes['has_password'] ) ? $includes['has_password'] : 'null' );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-has_password" ><input class="_is_includes-has_password" type="radio" id="' . esc_attr( $id ) . '-has_password" name="' . esc_attr( $id ) . '[has_password]" value="null" ' . checked( 'null', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Search posts with or without passwords", 'searchwiz' ) . '</label></p>';
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-has_password_1" ><input class="_is_includes-has_password" type="radio" id="' . esc_attr( $id ) . '-has_password_1" name="' . esc_attr( $id ) . '[has_password]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Search posts with passwords", 'searchwiz' ) . '</label></p>';
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-has_password_0" ><input class="_is_includes-has_password" type="radio" id="' . esc_attr( $id ) . '-has_password_0" name="' . esc_attr( $id ) . '[has_password]" value="0" ' . checked( 0, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Search posts without passwords", 'searchwiz' ) . '</label></p>';
        ?>
			</div></div>
			<h4 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-date_query"><?php 
        echo esc_html( __( 'Date', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Search posts created only in the specified date range.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $range = array('after', 'before');
        foreach ( $range as $value ) {
            $col_title = ( 'after' == $value ? __( 'From', 'searchwiz' ) : __( 'To', 'searchwiz' ) );
            echo '<div class="col-wrapper ' . esc_attr( $value ) . '"><div class="col-title">' . esc_html( $col_title ) . '</div>';
            $checked = ( isset( $includes['date_query'][$value]['date'] ) ? $includes['date_query'][$value]['date'] : '' );
            echo '<input type="text" id="is-' . esc_attr( $value ) . '-datepicker" name="' . esc_attr( $id ) . '[date_query][' . esc_attr( $value ) . '][date]" value="' . esc_attr( $checked ) . '">';
            echo '</div>';
        }
        ?>
			</div></div>
		</div>

		</div>

		</div>

	<?php 
    }

    public function customize_panel( $post ) {
        $id = '_is_customize';
        $settings = $post->prop( $id );
        $enable_customize = ( isset( $settings['enable_customize'] ) ? $settings['enable_customize'] : false );
        $is_ajax = $post->prop( '_is_ajax' );
        ?>

		<h4 class="panel-desc"><?php 
        esc_html_e( "Design Search Form Colors, Text and Style", 'searchwiz' );
        ?></h4>
		<div class="search-form-editor-box" id="<?php 
        echo esc_attr( $id );
        ?>">
			<?php 
        if ( 'default-search-form' == $post->name() && !isset( $is_ajax['enable_ajax'] ) ) {
            ?>
			<p class="check-radio enable-ajax-customize">
				<label for="<?php 
            echo esc_attr( $id );
            ?>-enable_customize">
					<input class="<?php 
            echo esc_attr( $id );
            ?>-enable_customize" type="checkbox" id="<?php 
            echo esc_attr( $id );
            ?>-enable_customize" name="<?php 
            echo esc_attr( $id );
            ?>[enable_customize]" value="1" <?php 
            checked( 1, $enable_customize );
            ?> data-depends="[<?php 
            echo esc_attr( $id );
            ?>-description_source_wrap,<?php 
            echo esc_attr( $id );
            ?>-description_length_wrap]"/>
					<span class="toggle-check-text"></span>
					<?php 
            esc_html_e( 'Enable Search Form Customization', 'searchwiz' );
            ?>
				</label>
			</p>
			<?php 
        } else {
            $enable_customize = true;
        }
        $field_class = ( $enable_customize ? '' : 'is-field-disabled' );
        ?>
			<div class="form-table form-table-panel-customize">

				<!-- Search Results -->
				<h3 scope="row">
					<label for="<?php 
        echo esc_attr( $id );
        ?>-customizer"><?php 
        echo esc_html( __( 'Customizer', 'searchwiz' ) );
        ?></label>
				</h3>
				<div class="is-field-wrap <?php 
        echo esc_attr( $field_class );
        ?>">
					<?php 
        if ( 'default-search-form' == $post->name() && !isset( $is_ajax['enable_ajax'] ) ) {
            ?>
					<span class="is-field-disabled-message"><span class="message"><?php 
            esc_html_e( 'Enable Search Form Customization', 'searchwiz' );
            ?></span></span>
					<?php 
        }
        ?>
                                        <?php 
        SearchWiz_Help::help_info( __( 'Use below customizer to customize search form colors, text and search form style.', 'searchwiz' ) );
        ?>
					<div>
                                            <?php
        if ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
            $customizer_url = admin_url( 'customize.php?autofocus[section]=is_section_' . sanitize_key( $_GET['post'] ) );
            if ( !$enable_customize ) {
                $http_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '';
                $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw($_SERVER['REQUEST_URI']) : '';
                $customizer_url = "//" . $http_host . $request_uri;
            }
            echo '<a style="font-size: 20px;font-weight: 800; padding: 25px 0;display: block;text-align: center;box-shadow:none;"class="is-customize-link" href="' . esc_url( $customizer_url ) . '">' . esc_html__( "Search Form Customizer", "searchwiz" ) . '</a>';
        }
        ?>
					</div>
				</div>
			</div>
		</div>

		<?php 
    }

    public function ajax_panel( $post ) {
        $id = '_is_ajax';
        $settings = $post->prop( $id );
        $includes = $post->prop( '_is_includes' );
        // If not have any settings saved then set default value for fields.
        if ( empty( $settings ) ) {
            $show_description = true;
            $show_details_box = true;
            $show_more_result = true;
            $show_more_func = false;
            $show_price = true;
            $show_matching_categories = true;
            $show_image = true;
            $search_results = 'both';
        } else {
            $show_description = ( isset( $settings['show_description'] ) && $settings['show_description'] ? 1 : 0 );
            $show_details_box = ( isset( $settings['show_details_box'] ) ? $settings['show_details_box'] : false );
            $show_more_result = ( isset( $settings['show_more_result'] ) && $settings['show_more_result'] ? 1 : 0 );
            $show_more_func = ( isset( $settings['show_more_func'] ) && $settings['show_more_func'] ? 1 : 0 );
            $show_price = ( isset( $settings['show_price'] ) && $settings['show_price'] ? 1 : 0 );
            $show_matching_categories = ( isset( $settings['show_matching_categories'] ) && $settings['show_matching_categories'] ? 1 : 0 );
            $show_image = ( isset( $settings['show_image'] ) ? 1 : 0 );
            $search_results = ( isset( $settings['search_results'] ) ? $settings['search_results'] : 'both' );
        }
        $enable_ajax = ( isset( $settings['enable_ajax'] ) ? $settings['enable_ajax'] : false );
        $description_source = ( isset( $settings['description_source'] ) ? $settings['description_source'] : 'excerpt' );
        $description_length = ( isset( $settings['description_length'] ) ? $settings['description_length'] : 20 );
        $hide_price_out_of_stock = ( isset( $settings['hide_price_out_of_stock'] ) && $settings['hide_price_out_of_stock'] ? 1 : 0 );
        $show_sale_badge = ( isset( $settings['show_sale_badge'] ) && $settings['show_sale_badge'] ? 1 : 0 );
        $show_categories = ( isset( $settings['show_categories'] ) && $settings['show_categories'] ? 1 : 0 );
        $show_tags = ( isset( $settings['show_tags'] ) && $settings['show_tags'] ? 1 : 0 );
        $show_sku = ( isset( $settings['show_sku'] ) && $settings['show_sku'] ? 1 : 0 );
        $show_matching_tags = ( isset( $settings['show_matching_tags'] ) && $settings['show_matching_tags'] ? 1 : 0 );
        $show_stock_status = ( isset( $settings['show_stock_status'] ) && $settings['show_stock_status'] ? 1 : 0 );
        $show_featured_icon = ( isset( $settings['show_featured_icon'] ) && $settings['show_featured_icon'] ? 1 : 0 );
        $nothing_found_text = ( isset( $settings['nothing_found_text'] ) ? $settings['nothing_found_text'] : __( 'Nothing found', 'searchwiz' ) );
        $min_no_for_search = ( isset( $settings['min_no_for_search'] ) ? $settings['min_no_for_search'] : 1 );
        $view_all_results = ( isset( $settings['view_all_results'] ) ? $settings['view_all_results'] : false );
        $view_all_text = ( isset( $settings['view_all_text'] ) ? $settings['view_all_text'] : __( 'View All', 'searchwiz' ) );
        // Result Layout.
        $result_box_max_height = ( isset( $settings['result_box_max_height'] ) ? $settings['result_box_max_height'] : 400 );
        $more_result_text = ( isset( $settings['more_result_text'] ) ? $settings['more_result_text'] : __( 'More Results..', 'searchwiz' ) );
        $show_author = ( isset( $settings['show_author'] ) && $settings['show_author'] ? 1 : 0 );
        $show_date = ( isset( $settings['show_date'] ) && $settings['show_date'] ? 1 : 0 );
        // Details Box.
        $product_list = ( isset( $settings['product_list'] ) ? $settings['product_list'] : 'all' );
        $order_by = ( isset( $settings['order_by'] ) ? $settings['order_by'] : 'date' );
        $order = ( isset( $settings['order'] ) ? $settings['order'] : 'desc' );
        $field_class = ( $enable_ajax ? '' : 'is-field-disabled' );
        ?>
		<h4 class="panel-desc"><?php 
        esc_html_e( "Configure AJAX Search", 'searchwiz' );
        ?></h4>
		<div class="search-form-editor-box" id="<?php 
        echo esc_attr( $id );
        ?>">

			<p class="check-radio enable-ajax-customize">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-enable_ajax">
					<input class="<?php 
        echo esc_attr( $id );
        ?>-enable_ajax" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-enable_ajax" name="<?php 
        echo esc_attr( $id );
        ?>[enable_ajax]" value="1" <?php 
        checked( 1, $enable_ajax );
        ?> data-depends="[<?php 
        echo esc_attr( $id );
        ?>-description_source_wrap,<?php 
        echo esc_attr( $id );
        ?>-description_length_wrap]"/>
					<span class="toggle-check-text"></span>
					<?php 
        esc_html_e( 'Enable AJAX Search', 'searchwiz' );
        ?>
				</label>
			</p>

			<div class="form-table form-table-panel-ajax">
				<!-- Search Results -->
				<h3 scope="row">
					<label for="<?php 
        echo esc_attr( $id );
        ?>-search-form-search-results"><?php 
        esc_html_e( 'AJAX Search Results', 'searchwiz' );
        ?></label>
					<span class="is-actions">
						<a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'searchwiz' );
        ?></a>
						<a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'searchwiz' );
        ?></a>
					</span>
				</h3>
				<div class="is-field-wrap <?php 
        echo esc_attr($field_class);
        ?>">
					<span class="is-field-disabled-message"><span class="message"><?php 
        esc_html_e( 'Enable AJAX Search', 'searchwiz' );
        ?></span></span>
                                        <?php 
        SearchWiz_Help::help_info( __( 'Display selected content in the search results.', 'searchwiz' ) );
        ?>
					<!-- Description -->
					<div class="is-field <?php 
        echo esc_attr( $id );
        ?>-description_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_description">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_description" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_description" name="<?php 
        echo esc_attr( $id );
        ?>[show_description]" value="1" <?php 
        checked( 1, $show_description );
        ?> data-depends="[<?php 
        echo esc_attr( $id );
        ?>-description_source_wrap,<?php 
        echo esc_attr( $id );
        ?>-description_length_wrap]"/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Description', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>
					<div class="is-field <?php 
        echo esc_attr( $id );
        ?>-description_source_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-description_source_excerpt" >
								<input class="<?php 
        echo esc_attr( $id );
        ?>-description_source_excerpt" type="radio" id="<?php 
        echo esc_attr( $id );
        ?>-description_source_excerpt" name="<?php 
        echo esc_attr( $id );
        ?>[description_source]" value="excerpt" <?php 
        checked( 'excerpt', $description_source );
        ?>/>
								<span class="toggle-check-text"></span><?php 
        esc_html_e( "Excerpt", 'searchwiz' );
        ?>
							</label>
						</p>
						<p class="check-radio" style="margin-top: .5em;">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-description_source_content" >
								<input class="<?php 
        echo esc_attr( $id );
        ?>-description_source_content" type="radio" id="<?php 
        echo esc_attr( $id );
        ?>-description_source_content" name="<?php 
        echo esc_attr( $id );
        ?>[description_source]" value="content" <?php 
        checked( 'content', $description_source );
        ?>/>
								<span class="toggle-check-text"></span><?php 
        esc_html_e( "Content", 'searchwiz' );
        ?>
							</label>
						</p>
					</div>

					<!-- Description Length -->
					<div class="is-field <?php 
        echo esc_attr( $id );
        ?>-description_length_wrap"><br />
                                            <input class="<?php 
        echo esc_attr( $id );
        ?>-description_length" min="1" type="number" id="<?php 
        echo esc_attr( $id );
        ?>-description_length" name="<?php 
        echo esc_attr( $id );
        ?>[description_length]" value="<?php 
        echo esc_attr( $description_length );
        ?>"/>
                                            <p class="description"><?php 
        esc_html_e( 'Description Length.', 'searchwiz' );
        ?></p>
					</div>
					<!-- Image -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-show_image_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_image">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_image" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_image" name="<?php 
        echo esc_attr( $id );
        ?>[show_image]" value="1" <?php 
        checked( 1, $show_image );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Image', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>

					<!-- Categories -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-categories_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_categories">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_categories" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_categories" name="<?php 
        echo esc_attr( $id );
        ?>[show_categories]" value="1" <?php 
        checked( 1, $show_categories );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Categories', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>

					<!-- Tags -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-tags_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_tags">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_tags" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_tags" name="<?php 
        echo esc_attr( $id );
        ?>[show_tags]" value="1" <?php 
        checked( 1, $show_tags );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Tags', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>

					<!-- Show Author in Results -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-show_author_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_author">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_author" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_author" name="<?php 
        echo esc_attr( $id );
        ?>[show_author]" value="1" <?php 
        checked( 1, $show_author );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Author', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>
	
					<!-- Show Date in Results -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-show_date_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_date">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_date" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_date" name="<?php 
        echo esc_attr( $id );
        ?>[show_date]" value="1" <?php 
        checked( 1, $show_date );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Date', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>
					<!-- Minimum Number of Characters -->
					<br /><div class="<?php 
        echo esc_attr( $id );
        ?>-min_no_for_search_wrap">
                                            <input class="<?php 
        echo esc_attr( $id );
        ?>-min_no_for_search" type="number" id="<?php 
        echo esc_attr( $id );
        ?>-min_no_for_search" name="<?php 
        echo esc_attr( $id );
        ?>[min_no_for_search]" value="<?php 
        echo esc_attr( $min_no_for_search );
        ?>" />
                                            <p class="description"><?php 
        esc_html_e( 'Minimum number of characters required to run ajax search.', 'searchwiz' );
        ?></p>
					</div>
					<!-- Box Max Height -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-result_box_max_height_wrap">
                                            <input class="<?php 
        echo esc_attr( $id );
        ?>-result_box_max_height" type="number" id="<?php 
        echo esc_attr( $id );
        ?>-result_box_max_height" name="<?php 
        echo esc_attr( $id );
        ?>[result_box_max_height]" value="<?php 
        echo esc_attr( $result_box_max_height );
        ?>"/>
                                            <p class="description"><?php 
        esc_html_e( 'Search results box max height.', 'searchwiz' );
        ?></p>
					</div>
                                        <br />
                                        <?php 
        SearchWiz_Help::help_info( __( 'Configure the plugin text displayed in the search results.', 'searchwiz' ) );
        ?>
					<!-- Nothing Found Text -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-nothing_found_text_wrap">
						<p>
                                                    <input class="<?php 
        echo esc_attr( $id );
        ?>-nothing_found_text" type="text" id="<?php 
        echo esc_attr( $id );
        ?>-nothing_found_text" name="<?php 
        echo esc_attr( $id );
        ?>[nothing_found_text]" value="<?php 
        echo esc_attr( $nothing_found_text );
        ?>" />
                                                    <span class="description"><?php 
        esc_html_e( 'Text when there is no search results. HTML tags is allowed.', 'searchwiz' );
        ?></span>
						</p>
					</div>
					<!-- Show More Result -->
					<br /><div class="<?php 
        echo esc_attr( $id );
        ?>-show_more_result_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_more_result">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_more_result" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_more_result" name="<?php 
        echo esc_attr( $id );
        ?>[show_more_result]" value="1" <?php 
        checked( 1, $show_more_result );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Show \'More Results..\' text in the bottom of the search results box', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>
					<!-- More Result Text -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-more_result_text_wrap">
						<p>
							<input class="<?php 
        echo esc_attr( $id );
        ?>-more_result_text" type="text" id="<?php 
        echo esc_attr( $id );
        ?>-more_result_text" name="<?php 
        echo esc_attr( $id );
        ?>[more_result_text]" value="<?php 
        echo esc_attr( $more_result_text );
        ?>"/>
                                                        <span class="description"><?php 
        esc_html_e( 'Text for the "More Results..".', 'searchwiz' );
        ?></span>
						</p>
					</div>
					<!-- Show More Result Functionality  -->
					<div class="<?php 
        echo esc_attr( $id );
        ?>-show_more_func_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-show_more_func">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-show_more_func" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-show_more_func" name="<?php 
        echo esc_attr( $id );
        ?>[show_more_func]" value="1" <?php 
        checked( 1, $show_more_func );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Redirect to search results page clicking on the \'More Results..\' text', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>
					<!-- Show 'View All Results' -->
					<!--<div class="<?php 
        echo esc_attr( $id );
        ?>-view_all_results_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-view_all_results">
								<input class="<?php 
        echo esc_attr( $id );
        ?>-view_all_results" type="checkbox" id="<?php 
        echo esc_attr( $id );
        ?>-view_all_results" name="<?php 
        echo esc_attr( $id );
        ?>[view_all_results]" value="1" <?php 
        checked( 1, $view_all_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'View All Result - Show link to search results page at the bottom of search results block.', 'searchwiz' );
        ?>
							</label>
						</p>
					</div>-->

					<!-- View All Text -->
					<!--<div class="<?php 
        echo esc_attr( $id );
        ?>-view_all_text_wrap">
						<p>
							<input class="<?php 
        echo esc_attr( $id );
        ?>-view_all_text" type="text" id="<?php 
        echo esc_attr( $id );
        ?>-view_all_text" name="<?php 
        echo esc_attr( $id );
        ?>[view_all_text]" value="<?php 
        echo esc_attr( $view_all_text );
        ?>"/>
							<label for="<?php 
        echo esc_attr( $id );
        ?>-view_all_text"><?php 
        esc_html_e( 'Text for the "View All" which shown at the bottom of the search result.', 'searchwiz' );
        ?></label>
						</p>
					</div>-->
                                        <!-- Search Button Functionality -->
                                        <br />
                                        <?php 
        SearchWiz_Help::help_info( __( 'Configure how the search button should work clicking on it.', 'searchwiz' ) );
        ?>
					<div>
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-both" >
								<input class="<?php 
        echo esc_attr( $id );
        ?>-search_results" type="radio" id="<?php 
        echo esc_attr( $id );
        ?>-both" name="<?php 
        echo esc_attr( $id );
        ?>[search_results]" value="both" <?php 
        checked( 'both', $search_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( "Search button displays search results page", 'searchwiz' );
        ?>
							</label>
						</p>
						<p class="check-radio">
							<label for="<?php 
        echo esc_attr( $id );
        ?>-ajax_results" >
								<input class="<?php 
        echo esc_attr( $id );
        ?>-search_results" type="radio" id="<?php 
        echo esc_attr( $id );
        ?>-ajax_results" name="<?php 
        echo esc_attr( $id );
        ?>[search_results]" value="ajax_results" <?php 
        checked( 'ajax_results', $search_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( "Search button displays ajax search results", 'searchwiz' );
        ?>
							</label>
						</p>
					</div>
				</div>

				<!-- WooCommerce -->
				<h3 scope="row">
					<label for="<?php 
        echo esc_attr( $id );
        ?>-search-form-woocommerce"><?php 
        esc_html_e( 'WooCommerce', 'searchwiz' );
        ?></label>
				</h3>
				<div class="is-field-wrap <?php 
        echo esc_attr($field_class);
        ?>">
					<?php 
        if ( SearchWiz_Help::is_woocommerce_inactive() ) {
            SearchWiz_Help::woocommerce_inactive_field_notice();
        } else {
            if ( !isset( $includes['post_type'] ) || !in_array( 'product', $includes['post_type'] ) ) {
                // translators: %s: Section name    
                echo '<span class="notice-sw-info">' . sprintf( esc_html( "Please first configure this search form in the %s section to search WooCommerce product post type.", 'searchwiz' ), esc_url($this->inc_exc_url( 'includes' ) )) . '</span><br />';
            } else {
                ?>
						<span class="is-field-disabled-message"><span class="message"><?php 
                esc_html_e( 'Enable AJAX Search', 'searchwiz' );
                ?></span></span>
                                                <?php 
                SearchWiz_Help::help_info( __( 'Display selected WooCommerce content in the search results.', 'searchwiz' ) );
                ?>
						<!-- Price -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-price_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_price">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_price" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_price" name="<?php 
                echo esc_attr( $id );
                ?>[show_price]" value="1" <?php 
                checked( 1, $show_price );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Price', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Price Out of Stock -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-price_out_of_stock_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-hide_price_out_of_stock">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-hide_price_out_of_stock" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-hide_price_out_of_stock" name="<?php 
                echo esc_attr( $id );
                ?>[hide_price_out_of_stock]" value="1" <?php 
                checked( 1, $hide_price_out_of_stock );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Hide Price for Out of Stock Products', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Sale Badge -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-sale_badge_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_sale_badge">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_sale_badge" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_sale_badge" name="<?php 
                echo esc_attr( $id );
                ?>[show_sale_badge]" value="1" <?php 
                checked( 1, $show_sale_badge );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Sale Badge', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- SKU -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-sku_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_sku">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_sku" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_sku" name="<?php 
                echo esc_attr( $id );
                ?>[show_sku]" value="1" <?php 
                checked( 1, $show_sku );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'SKU', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Stock Status -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-stock_status_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_stock_status">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_stock_status" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_stock_status" name="<?php 
                echo esc_attr( $id );
                ?>[show_stock_status]" value="1" <?php 
                checked( 1, $show_stock_status );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Stock Status', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Featured Icon -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-featured_icon_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_featured_icon">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_featured_icon" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_featured_icon" name="<?php 
                echo esc_attr( $id );
                ?>[show_featured_icon]" value="1" <?php 
                checked( 1, $show_featured_icon );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Featured Icon', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Display Matching Categories -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-matching_categories_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_matching_categories">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_matching_categories" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_matching_categories" name="<?php 
                echo esc_attr( $id );
                ?>[show_matching_categories]" value="1" <?php 
                checked( 1, $show_matching_categories );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Matching Categories', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Display Matching Tags -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-matching_tags_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_matching_tags">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_matching_tags" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_matching_tags" name="<?php 
                echo esc_attr( $id );
                ?>[show_matching_tags]" value="1" <?php 
                checked( 1, $show_matching_tags );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Matching Tags', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>

						<!-- Show Details Box -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-details_box_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo esc_attr( $id );
                ?>-show_details_box">
									<input class="<?php 
                echo esc_attr( $id );
                ?>-show_details_box" type="checkbox" id="<?php 
                echo esc_attr( $id );
                ?>-show_details_box" name="<?php 
                echo esc_attr( $id );
                ?>[show_details_box]" value="1" <?php 
                checked( 1, $show_details_box );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Details Box', 'searchwiz' );
                ?>
								</label>
							</p>
						</div>
						<!-- Products List -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-product_list_wrap">
                                                        <?php 
                SearchWiz_Help::help_info( __( 'Below options only apply to matching categories or tags.', 'searchwiz' ) );
                ?><br />
							<p><label for="<?php 
                echo esc_attr( $id );
                ?>-product_list">
								<?php 
                esc_html_e( 'Product List', 'searchwiz' );
                ?>
							</label>
							<select class="<?php 
                echo esc_attr( $id );
                ?>-product_list" id="<?php 
                echo esc_attr( $id );
                ?>-product_list" name="<?php 
                echo esc_attr( $id );
                ?>[product_list]">
								<option value="all" <?php 
                selected( $product_list, 'all' );
                ?>><?php 
                esc_html_e( 'All Product', 'searchwiz' );
                ?></option>
								<option value="featured" <?php 
                selected( $product_list, 'featured' );
                ?>><?php 
                esc_html_e( 'Featured Products', 'searchwiz' );
                ?></option>
								<option value="onsale" <?php 
                selected( $product_list, 'onsale' );
                ?>><?php 
                esc_html_e( 'On-sale Products</option>', 'searchwiz' );
                ?></option>
							</select></p>
						</div>

						<!-- Order by -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-order_by_wrap">
							<p><label for="<?php 
                echo esc_attr( $id );
                ?>-order_by">
								<?php 
                esc_html_e( 'Order by', 'searchwiz' );
                ?>
							</label>
							<select class="<?php 
                echo esc_attr( $id );
                ?>-order_by" id="<?php 
                echo esc_attr( $id );
                ?>-order_by" name="<?php 
                echo esc_attr( $id );
                ?>[order_by]">
								<option value="date" <?php 
                selected( $order_by, 'date' );
                ?>><?php 
                esc_html_e( 'Date', 'searchwiz' );
                ?></option>
								<option value="price" <?php 
                selected( $order_by, 'price' );
                ?>><?php 
                esc_html_e( 'Price', 'searchwiz' );
                ?></option>
								<option value="rand" <?php 
                selected( $order_by, 'rand' );
                ?>><?php 
                esc_html_e( 'Random', 'searchwiz' );
                ?></option>
								<option value="sales" <?php 
                selected( $order_by, 'sales' );
                ?>><?php 
                esc_html_e( 'Sales', 'searchwiz' );
                ?></option>
							</select></p>
						</div>

						<!-- Order -->
						<div class="<?php 
                echo esc_attr( $id );
                ?>-order_wrap">
							<p><label for="<?php 
                echo esc_attr( $id );
                ?>-order">
								<?php 
                esc_html_e( 'Order', 'searchwiz' );
                ?>
							</label>
							<select class="<?php 
                echo esc_attr( $id );
                ?>-order" id="<?php 
                echo esc_attr( $id );
                ?>-order" name="<?php 
                echo esc_attr( $id );
                ?>[order]">
								<option value="asc" <?php 
                selected( $order, 'asc' );
                ?>><?php 
                esc_html_e( 'ASC', 'searchwiz' );
                ?></option>
								<option value="desc" <?php 
                selected( $order, 'desc' );
                ?>><?php 
                esc_html_e( 'DESC', 'searchwiz' );
                ?></option>
							</select></p>
						</div>

					<?php 
            }
        }
        ?>
				</div>
			</div>
		</div>
		<?php 
    }

    public function excludes_panel( $post ) {
        $id = '_is_excludes';
        $excludes = $post->prop( $id );
        $includes = $post->prop( '_is_includes' );
        $default_search = ( NULL == $post->id() ? true : false );
        ?>
		<h4 class="panel-desc">
			<?php 
        esc_html_e( "Exclude Content From Search", 'searchwiz' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo esc_attr( $id );
        ?>">
		<div class="form-table form-table-panel-excludes">

                    <?php 
        $post_types = get_post_types( array(
            'public'              => true,
            'exclude_from_search' => false,
        ) );
        $post_types2 = get_post_types( '', 'objects' );
        if ( isset( $includes['post_type'] ) && !empty( $includes['post_type'] ) && is_array( $includes['post_type'] ) ) {
            $post_types = array_values( $includes['post_type'] );
        }
        foreach ( $post_types as $key => $post_type ) {
            if ( !isset( $post_types2[$post_type] ) ) {
                continue;
            }
            $accord_title = $post_types2[$post_type]->labels->name;
            if ( 'product' == $post_type && !SearchWiz_Help::is_woocommerce_inactive() ) {
                $accord_title .= ' <i>' . __( '( WooCommerce )', 'searchwiz' ) . '</i>';
            } else {
                if ( 'attachment' == $post_type ) {
                    $accord_title .= ' <i>' . __( '( Images, Videos, Audios, Docs, PDFs, Files & Attachments  )', 'searchwiz' ) . '</i>';
                }
            }
            ?>
			<h3 scope="row">
                            <label for="<?php 
            echo esc_attr( $id );
            ?>-post__not_in"><?php 
            echo wp_kses( $accord_title, array(
                'i' => array(),
            ) );
            ?></label>
                            <?php 
            if ( is_numeric( $key ) && 0 == $key || 'post' === $key ) {
                ?>
                            <span class="is-actions"><a class="expand" href="#"><?php 
                esc_html_e( 'Expand All', 'searchwiz' );
                ?></a><a class="collapse" href="#" style="display:none;"><?php 
                esc_html_e( 'Collapse All', 'searchwiz' );
                ?></a></span>
                            <?php 
            }
            ?>
                        </h3>
			<div>
				<?php 
            echo '<div>';
            if ( 'attachment' != $post_type || !isset( $includes['post_file_type'] ) ) {
                $posts_found = false;
                $posts_per_page = ( defined( 'DISABLE_SW_LOAD_ALL' ) || isset( $excludes['post__not_in'] ) ? -1 : 100 );
                $posts = get_posts( array(
                    'post_type'      => $post_type,
                    'posts_per_page' => $posts_per_page,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ) );
                $html = '<div class="is-posts">';
                $selected_pt = array();
                $selected_pt2 = array();
                if ( !empty( $posts ) ) {
                    $posts_found = true;
                    $html .= '<div class="col-wrapper"><div class="col-title">';
                    $col_title = '<span>' . $post_types2[$post_type]->labels->name . '</span>';
                    $temp = '';
                    foreach ( $posts as $post2 ) {
                        $checked = ( isset( $includes['post__in'] ) && in_array( $post2->ID, $includes['post__in'] ) ? $post2->ID : 0 );
                        if ( $checked ) {
                            array_push( $selected_pt2, $post_type );
                        }
                        $checked = ( isset( $excludes['post__not_in'] ) && in_array( $post2->ID, $excludes['post__not_in'] ) ? $post2->ID : 0 );
                        if ( $checked ) {
                            array_push( $selected_pt, $post_type );
                        }
                        $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                        $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
                    }
                    if ( !empty( $selected_pt ) && in_array( $post_type, $selected_pt ) ) {
                        $col_title = '<strong>' . $col_title . '</strong>';
                    }
                    $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text"></div>';
                    $html .= '<select class="_is_excludes-post__not_in" name="' . esc_attr( $id ) . '[post__not_in][]" multiple size="8" >';
                    $html .= $temp . '</select>';
                    if ( count( $posts ) >= 100 && !defined( 'DISABLE_SW_LOAD_ALL' ) && !isset( $excludes['post__not_in'] ) ) {
                        $html .= '<div id="' . esc_attr( $post_type ) . '" class="load-all">' . __( 'Load All', 'searchwiz' ) . '</div>';
                    }
                    $html .= '</div>';
                }
                if ( !$posts_found ) {
                    // translators: %s: Label name    
                    $html .= '<br /><span class="notice-sw-info">' . sprintf( __( 'No %s created.', 'searchwiz' ), $post_types2[$post_type]->labels->name ) . '</span>';
                } else {
                    $html .= '<br /><label for="' . esc_attr( $id ) . '-post__not_in" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label><br />';
                }
                $html .= '</div>';
                $checked = 'all';
                if ( !empty( $selected_pt ) && in_array( $post_type, $selected_pt ) ) {
                    $checked = 'selected';
                }
                if ( empty( $selected_pt2 ) ) {
                    if ( isset( $includes['post__in'] ) ) {
                        // translators: %s: Section name    
                        echo '<span class="notice-sw-info">' . sprintf( esc_html__( "The search form is configured in the %s section to only search specific posts of another post type.", 'searchwiz' ), esc_url($this->inc_exc_url( 'includes' ) )) . '</span>';
                        echo '</div></div>';
                        continue;
                    }
                    echo '<p class="check-radio"><label for="' . esc_attr( $post_type ) . '-post-search_all" ><input class="is-post-select" type="radio" id="' . esc_attr( $post_type ) . '-post-search_all" name="' . esc_attr( $post_type ) . 'i[post_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>';
                    // translators: %s: Search string 
                    echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Do not exclude any %s from search", 'searchwiz' ), esc_attr(strtolower( $post_types2[$post_type]->labels->singular_name ) )) . '</label></p>';
                    echo '<p class="check-radio"><label for="' . esc_attr( $post_type ) . '-post-search_selected" ><input class="is-post-select" type="radio" id="' . esc_attr( $post_type ) . '-post-search_selected" name="' . esc_attr( $post_type ) . 'i[post_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
                    // translators: %s: Search string    
                    echo '<span class="toggle-check-text"></span>' . sprintf( esc_html( "Exclude selected %s from search", 'searchwiz' ), esc_attr(strtolower( $post_types2[$post_type]->labels->name )) ) . '</label></p>';
                    echo esc_attr($html);
                } else {
                    // translators: %1: Section, %2: Search term    
                    echo '<span class="notice-sw-info">' . sprintf( esc_html( 'The search form is configured in the %1\$s section to only search specific %2\$s.', 'searchwiz' ), esc_url($this->inc_exc_url( 'includes' )), esc_html(strtolower( $post_types2[$post_type]->labels->name )) ) . '</span><br />';
                }
            }
            $tax_objs = get_object_taxonomies( $post_type, 'objects' );
            if ( !empty( $tax_objs ) ) {
                $terms_exist = false;
                $html = '<div class="is-taxes">';
                $selected_tax = false;
                foreach ( $tax_objs as $key => $tax_obj ) {
                    $terms = get_terms( array(
                        'taxonomy' => $key,
                        'lang'     => '',
                        'number'   => 1000,
                    ) );
                    if ( !empty( $terms ) && !empty( $tax_obj->labels->name ) ) {
                        $terms_exist = true;
                        $html .= '<div class="col-wrapper"><div class="col-title">';
                        $col_title = ucwords( str_replace( '-', ' ', str_replace( '_', ' ', esc_html( $tax_obj->labels->name ) ) ) );
                        if ( isset( $excludes['tax_query'][$key] ) ) {
                            $col_title = '<strong>' . $col_title . '</strong>';
                            $selected_tax = true;
                        }
                        $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text"></div><select class="_is_excludes-tax_query" name="' . esc_attr( $id ) . '[tax_query][' . $key . '][]" multiple size="8" >';
                        foreach ( $terms as $key2 => $term ) {
                            $checked = ( isset( $excludes['tax_query'][$key] ) && in_array( $term->term_taxonomy_id, $excludes['tax_query'][$key] ) ? $term->term_taxonomy_id : 0 );
                            $html .= '<option value="' . esc_attr( $term->term_taxonomy_id ) . '" ' . selected( $term->term_taxonomy_id, $checked, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                        $html .= '</select></div>';
                    }
                }
                if ( $terms_exist ) {
                    $html .= '<br /><label for="' . esc_attr( $id ) . '-tax_query" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label><br />';
                    $html .= '</div>';
                    $checked = ( $selected_tax ? 'selected' : 'all' );
                    echo '<br /><p class="check-radio"><label for="' . esc_attr( $post_type ) . '-tax-search_all" ><input class="is-tax-select" type="radio" id="' . esc_attr( $post_type ) . '-tax-search_all" name="' . esc_attr( $post_type ) . 'i[tax_search_radio]" value="all" ' . checked( 'all', $checked, false ) . '/>';
                    echo '<span class="toggle-check-text"></span>' . sprintf(
                        // translators: %1: Search field, %2: Category name, %3: Tag or term name    
                        esc_html( "Do not exclude any %1\$s from search of any taxonomies (%2\$s categories, tags & terms %3\$s)", 'searchwiz' ),
                        esc_attr(strtolower( $post_types2[$post_type]->labels->singular_name )),
                        '<i>',
                        '</i>'
                    ) . '</label></p>';
                    echo '<p class="check-radio"><label for="' . esc_attr( $post_type ) . '-tax-search_selected" ><input class="is-tax-select" type="radio" id="' . esc_attr( $post_type ) . '-tax-search_selected" name="' . esc_attr( $post_type ) . 'i[tax_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
                    echo '<span class="toggle-check-text"></span>' . sprintf(
                        // translators: %1: Search field, %2: Category name, %3: Tag or term name    
                        esc_html( "Exclude %1\$s from search of selected taxonomies (%2\$s categories, tags & terms %3\$s)", 'searchwiz' ),
                        esc_attr(strtolower( $post_types2[$post_type]->labels->name )),
                        '<i>',
                        '</i>'
                    ) . '</label></p>';
                    echo esc_attr($html);
                }
            }
            if ( 'product' == $post_type && !SearchWiz_Help::is_woocommerce_inactive() ) {
                echo '<br />';
                $outofstock_disable = '';
                if ( '' !== $outofstock_disable ) {
                    echo '<br /><div class="upgrade-parent">';
                }
                $checked = ( isset( $excludes['woo']['outofstock'] ) && $excludes['woo']['outofstock'] ? 1 : 0 );
                echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-outofstock" ><input class="_is_excludes-woocommerce" type="checkbox" ' . esc_attr($outofstock_disable) . ' id="' . esc_attr( $id ) . '-outofstock" name="' . esc_attr( $id ) . '[woo][outofstock]" value="1" ' . checked( 1, $checked, false ) . '/>';
                echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude 'Out of Stock' products from search", 'searchwiz' ) . '</label></p>';
                echo esc_url(SearchWiz_Admin::pro_link( 'pro_plus' ));
                if ( '' !== $outofstock_disable ) {
                    echo '</div>';
                }
            }
            if ( 'attachment' == $post_type ) {
                global $wp_version;
                if ( 4.9 <= $wp_version ) {
                    if ( !isset( $includes['post_file_type'] ) ) {
                        echo '<br />';
                        $file_types = get_allowed_mime_types();
                        if ( !empty( $file_types ) ) {
                            $file_type_disable = '';
                            if ( '' !== $file_type_disable ) {
                                echo '<div class="upgrade-parent">';
                            }
                            ksort( $file_types );
                            $html = '<br /><div class="is-mime">';
                            $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text">';
                            $html .= '<select class="_is_excludes-post_file_type" name="' . esc_attr( $id ) . '[post_file_type][]" ' . $file_type_disable . ' multiple size="8" >';
                            foreach ( $file_types as $key => $file_type ) {
                                $checked = ( isset( $excludes['post_file_type'] ) && in_array( $file_type, $excludes['post_file_type'] ) ? $file_type : 0 );
                                $html .= '<option value="' . esc_attr( $file_type ) . '" ' . selected( $file_type, $checked, false ) . '>' . esc_html( $key ) . '</option>';
                            }
                            $html .= '</select>';
                            echo esc_url(SearchWiz_Admin::pro_link( 'pro_plus' ));
                            $html .= '<br /><label for="' . esc_attr( $id ) . '-post_file_type" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label><br />';
                            if ( isset( $excludes['post_file_type'] ) ) {
                                $html .= __( 'Excluded File Types :', 'searchwiz' );
                                foreach ( $excludes['post_file_type'] as $post_file_type ) {
                                    $html .= '<br /><span style="font-size: 11px;">' . $post_file_type . '</span>';
                                }
                            }
                            $html .= '</div>';
                            $checked = ( isset( $excludes['post_file_type'] ) && !empty( $excludes['post_file_type'] ) ? 'selected' : 'all' );
                            echo '<p class="check-radio"><label for="mime-search_all" ><input class="is-mime-select" type="radio" id="mime-search_all" name="mime_search_radio" value="all" ' . checked( 'all', $checked, false ) . '/>';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Search all MIME types", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="mime-search_selected" ><input class="is-mime-select" type="radio" id="mime-search_selected" name="mime_search_radio" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude selected  MIME types from search", 'searchwiz' ) . '</label></p>';
                            echo esc_attr($html);
                            echo '<span class="search-attachments-wrapper">';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_images"><input class="search-attachments exclude" type="checkbox" id="' . esc_attr( $id ) . '-search_images" name="search_images" value="1" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Images", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_videos"><input class="search-attachments exclude" type="checkbox" id="' . esc_attr( $id ) . '-search_videos" name="search_videos" value="1" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Videos", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_audios"><input class="search-attachments exclude" type="checkbox" id="' . esc_attr( $id ) . '-search_audios" name="search_audios" value="1" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Audios", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_text"><input class="search-attachments exclude" type="checkbox" id="' . esc_attr( $id ) . '-search_text" name="search_text" value="1" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Text Files", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_pdfs"><input class="search-attachments exclude" type="checkbox" id="' . esc_attr( $id ) . '-search_pdfs" name="search_pdfs" value="1" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude PDF Files", 'searchwiz' ) . '</label></p>';
                            echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_docs"><input class="search-attachments exclude" type="checkbox" id="' . esc_attr( $id ) . '-search_docs" name="search_docs" value="1" />';
                            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude Document Files", 'searchwiz' ) . '</label></p>';
                            echo '</span>';
                            if ( '' !== $file_type_disable ) {
                                echo '</div>';
                            }
                        }
                    } else {
                        // translators: %s section name
                        echo '<br /><span class="notice-sw-info">' . sprintf( esc_html( "This search form is configured in the %s section to search specific attachments.", 'searchwiz' ), esc_url($this->inc_exc_url( 'includes' )) ) . '</span><br />';
                    }
                } else {
                    echo '<span class="notice-sw-info">' . esc_html__( 'You are using WordPress version less than 4.9 which does not support searching by MIME type.', 'searchwiz' ) . '</span>';
                }
            }
            ?>
			</div></div>

                        <?php 
        }
        ?>
			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-extras"><?php 
        echo esc_html( __( 'Extras', 'searchwiz' ) );
        ?></label>
                <span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'searchwiz' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'searchwiz' );
        ?></a></span>
			</h3>
			<div>
			<h4 scope="row" class="is-first-title">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-custom_field"><?php 
        echo esc_html( __( 'Custom Fields', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
			<?php 
        $meta_keys = $this->is_meta_keys();
        if ( !empty( $meta_keys ) ) {
            $html = '<div class="col-wrapper is-metas">';
            $selected_meta = false;
            $custom_field_disable = '';
            $html .= '<input class="list-search wide" placeholder="' . __( "Search..", 'searchwiz' ) . '" type="text">';
            $html .= '<select class="_is_excludes-custom_field" name="' . esc_attr( $id ) . '[custom_field][]" ' . $custom_field_disable . ' multiple size="8" >';
            foreach ( $meta_keys as $meta_key ) {
                $checked = ( isset( $excludes['custom_field'] ) && in_array( $meta_key, $excludes['custom_field'] ) ? $meta_key : 0 );
                if ( $checked ) {
                    $selected_meta = true;
                }
                $html .= '<option value="' . esc_attr( $meta_key ) . '" ' . selected( $meta_key, $checked, false ) . '>' . esc_html( $meta_key ) . '</option>';
            }
            $html .= '</select>';
            $html .= SearchWiz_Admin::pro_link();
            $html .= '<label for="' . esc_attr( $id ) . '-custom_field" class="ctrl-multi-select">' . esc_html__( "Hold down the control (ctrl) or command button to select multiple options.", 'searchwiz' ) . '</label>';
            $html .= '</div>';
            $checked = ( $selected_meta ? 'selected' : 'all' );
            echo '<span class="check-radio"><label for="is-meta-search_selected" ><input class="is-meta-select" type="checkbox" id="is-meta-search_selected" name="is[meta_search_radio]" value="selected" ' . checked( 'selected', $checked, false ) . '/>';
            echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude from search having selected custom fields", 'searchwiz' ) . '</label></span>';
            echo esc_attr($html);
        }
        ?>
		</div>
			<h4 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-author"><?php 
        echo esc_html( __( 'Authors', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Exclude posts from search created by selected authors.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        if ( !isset( $includes['author'] ) ) {
            $author_disable = '';
            $args = array(
                'fields'       => array('ID', 'display_name'),
                'orderby'      => 'post_count',
                'role__not_in' => 'subscriber',
                'order'        => 'DESC',
            );
            if ( version_compare( $GLOBALS['wp_version'], '5.9', '<' ) ) {
                $args['who'] = 'authors';
            } else {
                $args['capability'] = ['edit_posts'];
            }
            $authors = get_users( $args );
            if ( !empty( $authors ) ) {
                if ( '' !== $author_disable ) {
                    echo esc_url(SearchWiz_Admin::pro_link());
                }
                echo '<div class="is-cb-dropdown">';
                echo '<div class="is-cb-title">';
                if ( !isset( $excludes['author'] ) || empty( $excludes['author'] ) ) {
                    echo '<span class="is-cb-select">' . esc_html__( 'Search all author posts', 'searchwiz' ) . '</span><span class="is-cb-titles"></span>';
                } else {
                    echo '<span style="display:none;" class="is-cb-select">' . esc_html__( 'Search all author posts', 'searchwiz' ) . '</span><span class="is-cb-titles">';
                    foreach ( $excludes['author'] as $author2 ) {
                        $display_name = get_userdata( $author2 );
                        if ( $display_name ) {
                            echo '<span title="' . esc_html(ucfirst( esc_attr( $display_name->display_name ) )) . '"> ' . esc_html( $display_name->display_name ) . '</span>';
                        }
                    }
                    echo '</span>';
                }
                echo '</div>';
                echo '<div class="is-cb-multisel">';
                foreach ( $authors as $author ) {
                    $post_count = count_user_posts( $author->ID );
                    // Move on if user has not published a post (yet).
                    if ( !$post_count ) {
                        continue;
                    }
                    $checked = ( isset( $excludes['author'][esc_attr( $author->ID )] ) ? $excludes['author'][esc_attr( $author->ID )] : 0 );
                    echo '<label for="' . esc_attr( $id ) . '-author-' . esc_attr( $author->ID ) . '"><input class="_is_excludes-author" type="checkbox" ' . esc_attr($author_disable) . ' id="' . esc_attr( $id ) . '-author-' . esc_attr( $author->ID ) . '" name="' . esc_attr( $id ) . '[author][' . esc_attr( $author->ID ) . ']" value="' . esc_attr( $author->ID ) . '" ' . checked( $author->ID, $checked, false ) . '/>';
                    echo '<span class="toggle-check-text"></span> ' . esc_html(ucfirst( esc_attr( $author->display_name ) )) . '</label>';
                }
                echo '</div></div>';
            }
        } else {
            // translators: %s section name
            echo '<br /><span class="notice-sw-info">' . sprintf( esc_html( "This search form is configured in the %s section to search posts created by specific authors.", 'searchwiz' ), esc_url($this->inc_exc_url( 'includes' ) )) . '</span><br />';
        }
        ?>
			</div></div>

			<h4 scope="row">
                            <label for="<?php 
        echo esc_attr( $id );
        ?>-post_status"><?php 
        echo esc_html( __( 'Post Status', 'searchwiz' ) );
        ?></label>
			</h4>
			<div>
				<?php 
        $content = __( 'Exclude posts from search having selected post statuses.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $checked = ( isset( $excludes['ignore_sticky_posts'] ) && $excludes['ignore_sticky_posts'] ? 1 : 0 );
        echo '<label for="' . esc_attr( $id ) . '-ignore_sticky_posts" ><input class="_is_excludes-post_status" type="checkbox" id="' . esc_attr( $id ) . '-ignore_sticky_posts" name="' . esc_attr( $id ) . '[ignore_sticky_posts]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Exclude sticky posts from search", 'searchwiz' ) . '</label>';
        ?>
			</div></div>
		</div>
		</div>
		</div>
	<?php 
    }

    public function options_panel( $post ) {
        $id = '_is_settings';
        $settings = $post->prop( $id );
        ?>
		<h4 class="panel-desc">
			<?php 
        esc_html_e( "Advanced Search Form Options", 'searchwiz' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo esc_attr( $id );
        ?>">
		<div class="form-table form-table-panel-options">

			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-posts_per_page"><?php 
        echo esc_html( __( 'Posts Per Page', 'searchwiz' ) );
        ?></label>
			<span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'searchwiz' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'searchwiz' );
        ?></a></span></h3>
			<div>
			<?php 
        $content = __( 'Display selected number of posts in search results.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        echo '<select class="_is_settings-posts_per_page" name="' . esc_attr( $id ) . '[posts_per_page]" >';
        $default_per_page = get_option( 'posts_per_page', 10 );
        $checked = ( isset( $settings['posts_per_page'] ) ? $settings['posts_per_page'] : $default_per_page );
        for ($d = 1; $d <= 1000; $d++) {
            echo '<option value="' . esc_attr($d) . '" ' . selected( $d, $checked, false ) . '>' . esc_attr($d) . '</option>';
        }
        echo '<option value="9999" ' . selected( 9999, $checked, false ) . '>9999</option>';
        echo '<option value="-1" ' . selected( -1, $checked, false ) . '>-1</option>';
        echo '</select>';
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-order"><?php 
        echo esc_html( __( 'Order Search Results', 'searchwiz' ) );
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Display posts on search results page ordered by selected options.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $orderby_disable = '';
        echo '<select class="_is_settings-order" name="' . esc_attr( $id ) . '[orderby]" ' . esc_attr($orderby_disable) . ' >';
        $checked = ( isset( $settings['orderby'] ) ? $settings['orderby'] : 'date' );
        $orderbys = array(
            'date',
            'relevance',
            'none',
            'ID',
            'author',
            'title',
            'name',
            'type',
            'modified',
            'parent',
            'rand',
            'comment_count',
            'menu_order',
            'meta_value',
            'meta_value_num',
            'post__in',
            'post_name__in',
            'post_parent__in'
        );
        foreach ( $orderbys as $orderby ) {
            echo '<option value="' . esc_attr($orderby) . '" ' . selected( $orderby, $checked, false ) . '>' . esc_html(ucwords( str_replace( '_', ' ', esc_attr( $orderby ) ) )) . '</option>';
        }
        echo '</select><select class="_is_settings-order" name="' . esc_attr( $id ) . '[order]" ' . esc_attr($orderby_disable) . ' >';
        $checked = ( isset( $settings['order'] ) ? $settings['order'] : 'DESC' );
        $orders = array('DESC', 'ASC');
        foreach ( $orders as $order ) {
            echo '<option value="' . esc_attr($order) . '" ' . selected( $order, $checked, false ) . '>' . esc_html(ucwords( str_replace( '_', ' ', esc_attr( $order ) ) )) . '</option>';
        }
        echo '</select>';
        echo esc_url(SearchWiz_Admin::pro_link());
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-highlight_terms"><?php 
        echo esc_html( __( 'Highlight Search Terms', 'searchwiz' ) );
        ?></label>
			</h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['highlight_terms'] ) && $settings['highlight_terms'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-highlight_terms" ><input class="_is_settings-highlight_terms" type="checkbox" id="' . esc_attr( $id ) . '-highlight_terms" name="' . esc_attr( $id ) . '[highlight_terms]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Highlight searched terms on search results page", 'searchwiz' ) . '</label></p>';
        $color = ( isset( $settings['highlight_color'] ) ? $settings['highlight_color'] : '#FFFFB9' );
        echo '<div class="highlight-container"><br /><input style="width: 80px;" class="_is_settings-highlight_terms is-colorpicker" size="5" type="text" id="' . esc_attr( $id ) . '-highlight_color" name="' . esc_attr( $id ) . '[highlight_color]" value="' . esc_attr( $color ) . '" />';
        echo '<br /><i> ' . esc_html__( "Select text highlight color", 'searchwiz' ) . '</i></div>';
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-term_rel"><?php 
        echo esc_html( __( 'Search All Or Any Search Terms', 'searchwiz' ) );
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select whether to search posts having all or any of the words being searched.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $checked = ( isset( $settings['term_rel'] ) && "OR" === $settings['term_rel'] ? "OR" : "AND" );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-term_rel_or" ><input class="_is_settings-term_rel" type="radio" id="' . esc_attr( $id ) . '-term_rel_or" name="' . esc_attr( $id ) . '[term_rel]" value="OR" ' . checked( 'OR', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "OR - Display content having any of the search terms", 'searchwiz' ) . '</label></p>';
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-term_rel_and" ><input class="_is_settings-term_rel" type="radio" id="' . esc_attr( $id ) . '-term_rel_and" name="' . esc_attr( $id ) . '[term_rel]" value="AND" ' . checked( 'AND', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "AND - Display content having all the search terms", 'searchwiz' ) . '</label></p>';
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-fuzzy_match"><?php 
        echo esc_html( __( 'Fuzzy Matching', 'searchwiz' ) );
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Select whether to search posts having whole or partial word being searched.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $checked = ( isset( $settings['fuzzy_match'] ) ? $settings['fuzzy_match'] : '2' );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-whole" ><input class="_is_settings-fuzzy_match" type="radio" id="' . esc_attr( $id ) . '-whole" name="' . esc_attr( $id ) . '[fuzzy_match]" value="1" ' . checked( '1', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Whole - Search posts that include the whole search term", 'searchwiz' ) . '</label></p>';
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-partial" ><input class="_is_settings-fuzzy_match" type="radio" id="' . esc_attr( $id ) . '-partial" name="' . esc_attr( $id ) . '[fuzzy_match]" value="2" ' . checked( '2', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Partial - Also search words in the posts that begins or ends with the search term", 'searchwiz' ) . '</label></p>';
        echo '<p class="check-radio"><label for="' . esc_attr($id) . '-anywhere" ><input class="_is_settings-fuzzy_match" type="radio" id="' . esc_attr($id) . '-anywhere" name="' . esc_attr($id) . '[fuzzy_match]" value="3" ' . checked( '3', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Anywhere - Also search words in the posts that have the search term in any position of the word", 'searchwiz' ) . '</label></p>';
        echo esc_html($this->get_conflicts_info( 'fuzzy_match' ));
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-keyword_stem"><?php 
        echo esc_html( __( 'Keyword Stemming', 'searchwiz' ) );
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select whether to search the base word of a searched keyword.', 'searchwiz' );
        $content .= '<p>' . __( 'For Example: If you search "doing" then it also searches base word of "doing" that is "do" in the specified post types.', 'searchwiz' ) . '</p>';
        $content .= '<p><span class="is-info-warning">' . __( 'Not recommended to use when Fuzzy Matching option is set to Whole.', 'searchwiz' ) . '</span></p>';
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $stem_disable = '';
        $checked = ( isset( $settings['keyword_stem'] ) && $settings['keyword_stem'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-keyword_stem" ><input class="_is_settings-keyword_stem" type="checkbox" id="' . esc_attr( $id ) . '-keyword_stem" ' . esc_attr($stem_disable) . ' name="' . esc_attr( $id ) . '[keyword_stem]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Also search base word of searched keyword", 'searchwiz' ) . '</label></p>';
        echo esc_url(SearchWiz_Admin::pro_link( 'pro_plus' ));
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-search-engine"><?php 
        echo esc_html( __( 'Search Engine', 'searchwiz' ) );
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select which search engine to use.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        echo '<div>';
        $checked = ( isset( $settings['search_engine'] ) && "index" === $settings['search_engine'] ? "index" : "wp" );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_engine_wp" ><input class="_is_settings-search_engine" type="radio" id="' . esc_attr( $id ) . '-search_engine_wp" name="' . esc_attr( $id ) . '[search_engine]" value="wp" ' . checked( 'wp', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Default WordPress Search Engine", 'searchwiz' ) . '</label></p>';
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-search_engine_index" ><input class="_is_settings-search_engine" type="radio" id="' . esc_attr( $id ) . '-search_engine_index" name="' . esc_attr( $id ) . '[search_engine]" value="index" ' . checked( 'index', $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Inverted Index Search Engine", 'searchwiz' ) . '</label></p>';
        ?>
			<p class="is-index-conflicts">
				<?php 
        if ( is_array( $this->index_conflicts ) ) {
            foreach ( $this->index_conflicts as $option => $conflicts ) {
                echo esc_html($this->get_conflicts_info( $option ));
            }
        }
        ?>
			</p>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo esc_attr( $id );
        ?>-extras"><?php 
        echo esc_html( __( 'Others', 'searchwiz' ) );
        ?></label>
				<span class="is-actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'searchwiz' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'searchwiz' );
        ?></a></span>
			</h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['move_sticky_posts'] ) && $settings['move_sticky_posts'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-move_sticky_posts" ><input class="_is_settings-move_sticky_posts" type="checkbox" id="' . esc_attr( $id ) . '-move_sticky_posts" name="' . esc_attr( $id ) . '[move_sticky_posts]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Display sticky posts to the start of the search results page", 'searchwiz' ) . '</label></p>';
        $checked = ( isset( $settings['demo'] ) && $settings['demo'] ? 1 : 0 );
        echo '<p class="check-radio"><label for="' . esc_attr( $id ) . '-demo" ><input class="_is_settings-demo" type="checkbox" id="' . esc_attr( $id ) . '-demo" name="' . esc_attr( $id ) . '[demo]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Display search form only for site administrator", 'searchwiz' ) . '</label></p>';
        echo '<br /><p class="check-radio">';
        $content = __( 'Select whether to display an error when user perform search without any search word.', 'searchwiz' );
        SearchWiz_Help::help_info( $content );
        $checked = ( isset( $settings['empty_search'] ) && $settings['empty_search'] ? 1 : 0 );
        echo '<br /><label for="' . esc_attr( $id ) . '-empty_search" ><input class="_is_settings-empty_search" type="checkbox" id="' . esc_attr( $id ) . '-empty_search" name="' . esc_attr( $id ) . '[empty_search]" value="1" ' . checked( 1, $checked, false ) . '/>';
        echo '<span class="toggle-check-text"></span>' . esc_html__( "Display an error for empty search query", 'searchwiz' ) . '</label></p>';
        ?>
			</div></div>


		</div>
		</div>
		<?php 
    }

    /**
     * Get index settings conflict info.
     * 
     * @since 1.0.0
     * @param string $option The option key to show conflict info for.
     * @param string $post_type Optional. The post type when option key allows it.
     * @return string The conflict notice to print.
     */
    public function get_conflicts_info( $option, $post_type = null ) {
        $search_engine = $this->search_form->group_prop( '_is_settings', 'search_engine' );
        $conflict = null;
        if ( 'index' == $search_engine && !empty( $this->index_conflicts[$option] ) ) {
            if ( !empty( $post_type ) ) {
                if ( !empty( $this->index_conflicts[$option][$post_type] ) ) {
                    $conflict = $this->index_conflicts[$option][$post_type];
                }
            } else {
                if ( is_array( $this->index_conflicts[$option] ) ) {
                    $conflict = implode( '<br />', $this->index_conflicts[$option] );
                } else {
                    $conflict = $this->index_conflicts[$option];
                }
            }
            if ( $conflict ) {
                return sprintf( '<span class="notice-sw-info">%s</span><br />', wp_kses_post( $conflict ) );
            }
        }
    }

    /**
     * Get index settings conflicts with this search form.
     * 
     * @since 1.0.0
     * @return array {
     * 		string $key The option key
     * 		string $notice The html escaped notice to show.
     * } 	
     */
    public function get_index_conflicts() {
        $conflicts = [];
        $index_opt = SearchWiz_Index_Options::getInstance();
        if ( SearchWiz_Index_Model::is_index_empty() ) {
            $conflicts['index'] = esc_html( 'The Index should be created to use this option in the Index ', 'searchwiz' ) . $index_opt->get_index_settings_link();
        }
        $props = $this->search_form->get_properties();
        foreach ( $props as $key => $group ) {
            if ( !empty( $group ) && is_array( $group ) ) {
                foreach ( $group as $prop => $val ) {
                    $diff = array();
                    switch ( $prop ) {
                        case 'post_type':
                            $diff = array_diff( $val, $index_opt->get_post_types() );
                            if ( !empty( $diff ) ) {
                                // translators: %s post type
                                $conflicts[$prop] = sprintf( esc_html( _n(
                                    'The %s post type is not selected in Index',
                                    'The %s post types are not selected in Index',
                                    count( $diff ),
                                    'searchwiz'
                                ) ), implode( ', ', $diff ) ) . $index_opt->get_index_settings_link( 'post_types' );
                            }
                            break;
                        case 'custom_field':
                            $indexed_meta_fields = $index_opt->get_meta_keys();
                            $meta_keys = $this->is_meta_keys();
                            $selected = array_intersect( $meta_keys, $val );
                            $diff = array_diff( $selected, $indexed_meta_fields );
                            if ( !empty( $diff ) ) {
                                // translators: %s meta field
                                $conflicts[$prop] = sprintf( esc_html( _n(
                                    'The %s meta field is not selected in Index',
                                    'The %s meta fields are not selected in Index',
                                    count( $diff ),
                                    'searchwiz'
                                ) ), implode( ', ', $diff ) ) . $index_opt->get_index_settings_link( 'meta_fields' );
                            }
                            break;
                        case 'search_title':
                            if ( $val && !$index_opt->index_title ) {
                                $conflicts[$prop] = esc_html__( 'The post title is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'search_content':
                            if ( $val && !$index_opt->index_content ) {
                                $conflicts[$prop] = esc_html__( 'The post content is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'search_excerpt':
                            if ( $val && !$index_opt->index_excerpt ) {
                                $conflicts[$prop] = esc_html__( 'The post excerpt is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'search_tax_title':
                            if ( $val && !$index_opt->index_tax_title ) {
                                $conflicts[$prop] = esc_html__( 'The taxonomy title is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'search_tax_desp':
                            if ( $val && !$index_opt->index_tax_desp ) {
                                $conflicts[$prop] = esc_html__( 'The taxonomy description is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'search_author':
                            if ( $val && !$index_opt->index_author_info ) {
                                $conflicts[$prop] = esc_html__( 'The Author info is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'search_comment':
                            if ( $val && !$index_opt->index_comments ) {
                                $conflicts[$prop] = esc_html__( 'The comments are not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'woo':
                            if ( !empty( $val['sku'] ) && !$index_opt->index_product_sku ) {
                                $conflicts[$prop]['sku'] = esc_html__( 'The product SKU is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            if ( !empty( $val['variation'] ) && !$index_opt->index_product_variation ) {
                                $conflicts[$prop]['variation'] = esc_html__( 'The product variation is not selected in Index', 'searchwiz' ) . $index_opt->get_index_settings_link( 'extra' );
                            }
                            break;
                        case 'fuzzy_match':
                            if ( 3 == $val && $this->search_form->is_index_search() ) {
                                $link = sprintf( ' <a href="#ui-id-9" class="%s" data-is="#ui-id-9">%s</a>', 'is-option-link', esc_html__( 'Anywhere Fuzzy Matching', 'searchwiz' ) );
                                $link1 = sprintf( ' <a href="#ui-id-13" class="%s" data-is="#ui-id-13">%s</a>', 'is-option-link', esc_html__( 'Inverted Index Search Engine', 'searchwiz' ) );
                                $conflicts[$prop] = sprintf(
                                    '%s %s %s %s',
                                    esc_html__( 'It is not recommended to use the', 'searchwiz' ),
                                    $link,
                                    esc_html__( 'option along with', 'searchwiz' ),
                                    $link1
                                );
                            }
                            break;
                    }
                }
            }
        }
        return $conflicts;
    }

}
