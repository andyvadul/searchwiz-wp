<?php
/**
 * Defines plugin index settings fields.
 *
 * @package SW
 * @subpackage SW/admin
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchWiz_Settings_Index_Fields {
    /**
     * Stores plugin index options.
     *
     * @since 1.0.0
     * @var SW_Index_Option
     */
    protected $index_opt;

    /**
     * Index Manager.
     *
     * @since 1.0.0
     * @var SearchWiz_Index_Manager
     */
    protected $index_manager;

    /**
     * Core singleton class.
     *
     * @var self
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Instantiates the plugin by setting up the core properties and loading
     * all necessary dependencies and defining the hooks.
     *
     * The constructor uses internal functions to import all the
     * plugin dependencies, and will leverage the SearchWiz for
     * registering the hooks and the callback functions used throughout the plugin.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->index_opt = SearchWiz_Index_Options::getInstance();
        $this->index_manager = SearchWiz_Index_Manager::getInstance();
    }

    /**
     * Gets the instance of this class.
     *
     * @since 1.0.0
     * @return self
     */
    public static function getInstance() {
        if ( !self::$_instance instanceof self ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Displays index section description text.
     *
     * @since 1.0.0
     */
    public function index_section_desc() {
        ?>
		<h4 class="panel-desc">
			<?php
        esc_html_e( 'Search Index Configuration', 'searchwiz' );
        ?>
		</h4>
		<p style="text-align: center; color: #666;">
			<?php
        esc_html_e( 'Control what content is indexed and searchable on your site.', 'searchwiz' );
        ?>
		</p>
		<?php
        $this->build_settings();
    }

    /**
     * Build index settings panel.
     *
     * @since 1.0.0
     */
    public function build_settings() {
        $id = SearchWiz_Index_Options::$ID;
        $option = 'auto_index_enabled';
        $saved_opt = $this->index_opt->{$option};
        $opt_id = "{$id}_{$option}";
        $opt_name = "{$id}[{$option}]";
        $checked = !empty( $saved_opt );
        $label = __( 'Automatically index content, when posts or comments are created (or updated).', 'searchwiz' );
        ?>
			<div id="post-body-content-0" style="position: relative;">
				<div class="postbox">
					<h2 class="postbox-header"><?php 
        esc_html_e( 'Build Index', 'searchwiz' );
        ?></h2>
					<div class="inside">
					<p class="check-radio">
						<label for="<?php 
        echo esc_attr( $opt_id );
        ?>">
							<input 
								class="is_auto_index" 
								type="checkbox" 
								id="<?php 
        echo esc_attr( $opt_id );
        ?>" 
								name="<?php 
        echo esc_attr( $opt_name );
        ?>" 
								value="1" 
								<?php 
        checked( 1, $checked );
        ?>
							/>
							<span class="toggle-check-text"></span>
							<?php 
        echo wp_kses_post( $label );
        ?>
						</label>
					</p>
					<?php 
        $this->build_index_panel();
        ?>
					</div>
				</div>
			</div>
			<h4 class="panel-desc">
				<?php 
        esc_html_e( 'Index Configurations', 'searchwiz' );
        ?>
			</h4>
		<?php 
    }

    /**
     * Build index panel.
     *
     * @since 1.0.0
     */
    protected function build_index_panel() {
        $status = $this->index_manager->get_index_status();
        $create_data = $this->index_manager->get_ajax_create_index_data();
        $btn_label = $create_data['btn_labels'][$create_data['idx_status']];
        $create_data = wp_json_encode( $create_data );
        $delete_data = $this->index_manager->get_ajax_delete_index_data();
        $delete_data = wp_json_encode( $delete_data );
        ?>
			<div>
				<p class="is-index-build-panel">
					<input 
						class="is_index_create button button-primary" 
						type="button" 
						id="is_index_create_btn" 
						value="<?php 
        echo esc_attr( $btn_label );
        ?>"
						data-is="<?php 
        echo esc_attr( $create_data );
        ?>"
					/>
					<input 
						class="is_index_delete button" 
						type="button" 
						id="is_index_delete_btn" 
						value="<?php 
        esc_attr_e( 'Delete Index', 'searchwiz' );
        ?>"
						data-is="<?php 
        echo esc_attr( $delete_data );
        ?>"
					/>
					<?php 
        $this->index_debug_panel();
        ?>
					<span class="is-loader" style="display:none;"></span>
				</p>
				<div id="is_progress" style="display: none;">
					<span id="is_indicator"></span>
				</div>
				<div id="is_time_elapsed_wrap" style="display: none;">
					<?php 
        esc_html_e( 'Time Elapsed:', 'searchwiz' );
        ?> 
					<span id="is_time_elapsed"></span>
				</div>
				<p class="is-index-status-wrap">
					<textarea 
						id="is-index-status" 
						rows="10" 
						cols="80" 
						class="is_index_status"
						readonly
					><?php 
        echo esc_html( $status );
        ?></textarea>
				</p>
			</div>
		<?php
    }

    /**
     * Index Debug panel.
     * Index individual post.
     *
     * Only active in debug mode.
     *
     * @since 1.0.0
     */
    public function index_debug_panel() {
        if ( !SearchWiz_Debug::is_debug_mode() ) {
            return;
        }
        $index_data = $this->index_manager->get_ajax_index_post_data();
        $index_data = wp_json_encode( $index_data );
        ?>
			<input 
				class="is_index_post_txt" 
				style="width: 4em; height: 2.1em; text-align: right;"
				type="numeric" 
				id="is_index_post_txt" 
				value="0"
			/>
			<input 
				class="is_index_post_btn button button-primary" 
				type="button" 
				id="is_index_post_btn" 
				value="<?php 
        esc_attr_e(  'Index Post', 'searchwiz'  );
        ?>"
				data-is="<?php 
        echo esc_attr( $index_data );
        ?>"
			/>
		<?php 
    }

    /**
     * Show Post Types to Index panel.
     *
     * @since 1.0.0
     */
    public function post_types_settings() {
        $id = SearchWiz_Index_Options::$ID;
        $option = 'post_types';
        $post_types = get_post_types( array(
            'public' => true,
        ), 'objects' );
        $saved_opt = $this->index_opt->{$option};
        ?>
		<div>
			<?php 
        $content = __( 'Index selected post types.', 'searchwiz' );
        SearchWiz_Help::help_info( esc_html( $content ) );
        if ( !empty( $post_types ) ) {
            ?>
			<div class="is-cb-dropdown">
				<div class="is-cb-title">
					<?php 
            if ( empty( $saved_opt ) ) {
                ?>
						<span class="is-cb-select"> 
							<?php 
                esc_html_e( 'Select Post Types', 'searchwiz' );
                ?>
						</span>
						<span class="is-cb-titles"></span>
					<?php 
            } else {
                ?>
						<span style="display:none;" class="is-cb-select">
							<?php 
                esc_html_e( 'Select Post Types', 'searchwiz' );
                ?> 
						</span>
						<span class="is-cb-titles">
						<?php 
                foreach ( $saved_opt as $post_type ) {
                    ?>
							<?php 
                    if ( isset( $post_types[$post_type] ) ) {
                        ?>
								<span title="<?php 
                        echo esc_attr( $post_type );
                        ?>"> 
									<?php 
                        echo esc_html( $post_types[$post_type]->labels->name );
                        ?> 
								</span>
							<?php 
                    }
                    ?>
						<?php 
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
                $checked = ( in_array( $key, $saved_opt ) ? esc_attr( $key ) : 0 );
                $id_attr = "{$id}-{$option}-{$key}";
                $name_attr = "{$id}[{$option}][]";
                $label = ucfirst( $post_type->labels->name );
                ?>
									
					<label for="<?php 
                echo esc_attr( $id_attr );
                ?>">
						<input 
							class="is_index-post_type" 
							type="checkbox" 
							id="<?php 
                echo esc_attr( $id_attr );
                ?>"
							name="<?php 
                echo esc_attr( $name_attr );
                ?>" 
							value="<?php 
                echo esc_attr( $key );
                ?>" 
							<?php 
                checked( $key, $checked, true );
                ?>
						/>
						<span class="toggle-check-text"></span>
						<?php 
                echo wp_kses_post( $label );
                ?> 
					</label>
				<?php 
            }

            // Add User Comments toggle
            $comments_checked = ( isset( $this->index_opt->{'index_user_comments'} ) && $this->index_opt->{'index_user_comments'} ? '1' : 0 );
            $comments_id_attr = "{$id}-{$option}-user_comments";
            $comments_name_attr = "{$id}[index_user_comments]";
            ?>
					<input type="hidden" name="<?php echo esc_attr( $comments_name_attr ); ?>" value="0" />
					<label for="<?php echo esc_attr( $comments_id_attr ); ?>" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
						<input
							class="is_index-post_type"
							type="checkbox"
							id="<?php echo esc_attr( $comments_id_attr ); ?>"
							name="<?php echo esc_attr( $comments_name_attr ); ?>"
							value="1"
							<?php checked( '1', $comments_checked, true ); ?>
						/>
						<span class="toggle-check-text"></span>
						<?php esc_html_e( 'User Comments', 'searchwiz' ); ?>
						<span style="color: #d63638; font-size: 11px;">(⚠️ <?php esc_html_e( 'Guests can post', 'searchwiz' ); ?>)</span>
					</label>
			<?php
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
        ?>
		</div>
		<?php 
    }

    /**
     * Show taxonomies settings panel.
     *
     * @since 1.0.0
     */
    public function taxonomies_settings() {
        $this->taxonomies_options();
        $this->taxonomies_select();
    }

    /**
     * Show taxonomies indexing options.
     *
     * @since 1.0.0
     */
    protected function taxonomies_options() {
        $id = SearchWiz_Index_Options::$ID;
        $opt = 'tax_index_opt';
        $saved_opt = $this->index_opt->{$opt};
        $options = $this->index_opt->get_taxonomies_index_options();
        $content = __( 'Index selected taxonomies.', 'searchwiz' );
        SearchWiz_Help::help_info( esc_html( $content ) );
        foreach ( $options as $option => $label ) {
            $opt_id = "{$id}-{$opt}-{$option}";
            $opt_name = "{$id}[{$opt}]";
            ?>
		<p class="check-radio">
			<label for="<?php 
            echo esc_attr( $opt_id );
            ?>">
				<input 
					class="is_index_taxonomies_opt" 
					type="radio" 
					id="<?php 
            echo esc_attr( $opt_id );
            ?>" 
					name="<?php 
            echo esc_attr( $opt_name );
            ?>" 
					value="<?php 
            echo esc_attr( $option );
            ?>" 
					<?php 
            checked( $option, $saved_opt );
            ?>
				/>
				<span class="toggle-check-text"></span>
				<?php 
            echo wp_kses_post( $label );
            ?>
			</label>
		</p>
			<?php 
        }
    }

    /**
     * Show the taxonomies to select for indexing.
     *
     * @since 1.0.0
     */
    protected function taxonomies_select() {
        // Only get public taxonomies to avoid duplicates and internal taxonomies
        $tax_objs = get_taxonomies( array( 'public' => true ), 'objects' );
        $id = SearchWiz_Index_Options::$ID;
        $option = 'tax_selected';
        $saved_opt = $this->index_opt->{$option};
        ?>
		<div class="is-index-tax-select">
			<?php 
        if ( !empty( $tax_objs ) ) {
            ?>
			<div class="is-cb-dropdown">
				<div class="is-cb-title">
					<?php 
            if ( empty( $saved_opt ) ) {
                ?>
						<span class="is-cb-select"> 
							<?php 
                esc_html_e( 'Select Taxonomies', 'searchwiz' );
                ?>
						</span>
						<span class="is-cb-titles"></span>
					<?php 
            } else {
                ?>
						<span style="display:none;" class="is-cb-select">
							<?php 
                esc_html_e( 'Select Taxonomies', 'searchwiz' );
                ?> 
						</span>
						<span class="is-cb-titles">
						<?php 
                foreach ( $saved_opt as $tax ) {
                    ?>
							<?php 
                    if ( isset( $tax_objs[$tax] ) ) {
                        ?>
								<span title="<?php 
                        echo esc_attr( $tax );
                        ?>"> 
									<?php 
                        echo esc_html( $tax_objs[$tax]->labels->name );
                        ?> 
								</span>
							<?php 
                    }
                    ?>
						<?php 
                }
                ?>
						</span>
					<?php 
            }
            ?>
				</div>
				<div class="is-cb-multisel">
				<?php 
            foreach ( $tax_objs as $key => $tax ) {
                $checked = ( in_array( $key, $saved_opt ) ? esc_attr( $key ) : 0 );
                $id_attr = "{$id}-{$option}-{$key}";
                $name_attr = "{$id}[{$option}][]";
                $label = ucfirst( $tax->labels->name );
                ?>
									
					<label for="<?php 
                echo esc_attr( $id_attr );
                ?>">
						<input 
							class="is_index-taxonomies" 
							type="checkbox" 
							id="<?php 
                echo esc_attr( $id_attr );
                ?>"
							name="<?php 
                echo esc_attr( $name_attr );
                ?>" 
							value="<?php 
                echo esc_attr( $key );
                ?>" 
							<?php 
                checked( $key, $checked );
                ?>
						/>
						<span class="toggle-check-text"></span>
						<?php 
                echo wp_kses_post( $label );
                ?> 
					</label>
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
            esc_html_e( 'No taxonomies registered on the site.', 'searchwiz' );
            ?> 
				</span>
			<?php 
        }
        ?>
		</div>
		<?php 
    }

    /**
     * Show custom fields settings panel.
     *
     * @since 1.0.0
     */
    public function meta_fields_settings() {
        $meta_keys = $this->index_opt->get_meta_keys( SearchWiz_Index_Options::META_OPT_ALL );
        if ( !empty( $meta_keys ) ) {
            $this->meta_fields_options();
            $this->meta_fields_select();
        }
    }

    /**
     * Show meta fields indexing options.
     *
     * @since 1.0.0
     */
    public function meta_fields_options() {
        $id = SearchWiz_Index_Options::$ID;
        $meta_options = $this->index_opt->get_meta_fields_options();
        $option = 'meta_fields_opt';
        $saved_opt = $this->index_opt->{$option};
        $content = __( 'Index selected custom fields.', 'searchwiz' );
        SearchWiz_Help::help_info( esc_html( $content ) );
        ?>
			<?php 
        foreach ( $meta_options as $key => $title ) {
            ?>
				<?php 
            $opt_id = "index-meta-option_{$key}";
            ?>
				<?php 
            $opt_name = "{$id}[{$option}]";
            ?>
				<p class="check-radio">
					<label for="<?php 
            echo esc_attr( $opt_id );
            ?>" >
						<input 
							class="is_index_meta_fields_opt" 
							type="radio" 
							id="<?php 
            echo esc_attr( $opt_id );
            ?>" 
							name='<?php 
            echo esc_attr( $opt_name );
            ?>' 
							value="<?php 
            echo esc_attr( $key );
            ?>" 
							<?php 
            checked( $saved_opt, $key );
            ?> 
						/>
						<span class="toggle-check-text"></span>
						<?php 
            echo esc_html( $title );
            ?>
					</label>
				</p>
			<?php 
        }
        ?>
		<?php 
    }

    /**
     * Show the meta fields to select for indexing.
     *
     * @since 1.0.0
     */
    public function meta_fields_select() {
        $id = SearchWiz_Index_Options::$ID;
        $option = 'meta_fields_selected';
        $saved_opt = $this->index_opt->{$option};
        $name_attr = "{$id}[{$option}][]";
        $meta_keys = $this->index_opt->get_meta_keys( SearchWiz_Index_Options::META_OPT_VISIBLE );
        ?>
			<div class="col-wrapper is-index-metas">							
				<input 
					class="list-search wide" 
					placeholder="<?php 
        esc_attr_e(  'Search..', 'searchwiz'  );
        ?>" 
					type="text"
				/>
				<select 
					class="is_index_meta_fields" 
					name="<?php 
        echo esc_attr( $name_attr );
        ?>" 
					multiple size="8" 
				>
				<?php 
        foreach ( $meta_keys as $meta_key ) {
            $checked = ( in_array( $meta_key, $saved_opt ) ? $meta_key : 0 );
            ?>
					<option 
						value="<?php 
            echo esc_attr( $meta_key );
            ?>" 
						<?php 
            selected( $meta_key, $checked );
            ?> 
					>
						<?php 
            echo esc_html( $meta_key );
            ?>
					</option>
				<?php 
        }
        ?>
				</select>
				<br />
				<label for="<?php 
        echo esc_attr( $id . '-custom_field' );
        ?>" class="ctrl-multi-select">
					<?php 
        esc_html_e( 'Hold down the control (ctrl) or command button to select multiple options.', 'searchwiz' );
        ?>
				</label>
				<br />
			</div>
		<?php 
    }

    /**
     * Show extra settings panel.
     *
     * @since 1.0.0
     */
    public function extra_settings() {
        $id = SearchWiz_Index_Options::$ID;
        $options = $this->index_opt->get_extra_options();
        $content = __( 'Index selected content.', 'searchwiz' );
        SearchWiz_Help::help_info( esc_html( $content ) );

        foreach ( $options as $option => $label ) {
            $opt_id = "{$id}_{$option}";
            $opt_name = "{$id}[{$option}]";
            $checked = $this->index_opt->{$option};
            ?>
		<p class="check-radio">
			<label for="<?php
            echo esc_attr( $opt_id );
            ?>">
				<input
					class="is_index_extra"
					type="checkbox"
					id="<?php
            echo esc_attr( $opt_id );
            ?>"
					name="<?php
            echo esc_attr( $opt_name );
            ?>"
					value="1"
					<?php
            checked( 1, $checked );
            ?>
				/>
				<span class="toggle-check-text"></span>
				<?php
            echo wp_kses_post( $label );
            ?>
			</label>
		</p>
			<?php
        }
    }

    /**
     * Show advanced indexing settings panel.
     *
     * @since 1.0.0
     */
    public function advanced_settings() {
        $id = SearchWiz_Index_Options::$ID;
        $fields = $this->index_opt->get_advanced_options();
        ?>

		<?php 
        foreach ( $fields as $field => $title ) {
            if ( 'min_word_length' == $field ) {
                ?>
				<input
					class="is_index_advanced_min_len"
					type="number"
					id="<?php
                echo esc_attr( $field );
                ?>"
					name="<?php
                echo esc_attr( "{$id}[{$field}]" );
                ?>"
					value="<?php
                echo esc_attr( $this->index_opt->{$field} );
                ?>"
					min="1"
					max="40"
				>
				<span class="word-min-len-text"><?php 
                echo esc_html( $title );
                ?></span>
				<br />
				<br />
				<?php 
                continue;
            }
            if ( 'throttle_searches' == $field ) {
                ?>
				<div>
					<strong><?php 
                esc_html( 'Search Performance', 'searchwiz' );
                ?></strong>
				</div>
				<p class="check-radio">
					<label for="<?php 
                echo esc_attr( $field , 'searchwiz');
                ?>">
						<input 
							class="is_index_trottle" 
							type="checkbox" 
							id="<?php 
                echo esc_attr( $field , 'searchwiz');
                ?>" 
							name="<?php 
                echo esc_attr( "{$id}[{$field}]", 'searchwiz' );
                ?>" 
							value="1" 
							<?php 
                checked( 1, $this->index_opt->{$field} );
                ?>
						/>
						<span class="toggle-check-text"></span>
						<?php 
                echo wp_kses_post( $title );
                ?>
					</label>
				</p>
				<div>
					<strong><?php 
                esc_html_e( 'Punctuation control', 'searchwiz' );
                ?></strong>
				</div>
				<br />
				<?php 
                continue;
            }
            ?>
            <div style="margin-bottom: 10px;">
                <strong style="display: block; margin-bottom: 5px;"><?php echo esc_html( $title ); ?></strong>
            </div>
            <?php
            $punc_options = SearchWiz_Index_Options::get_punctuation_options( $field );
            foreach ( $punc_options as $option => $label ) {
                $opt_id = "{$id}-{$field}-{$option}";
                $opt_name = "{$id}[{$field}]";
                ?>
				<p class="check-radio">
					<label for="<?php 
                echo esc_attr( $opt_id );
                ?>">
						<input 
							class="is_index_extra" 
							type="radio" 
							id="<?php 
                echo esc_attr( $opt_id );
                ?>" 
							name="<?php 
                echo esc_attr( $opt_name );
                ?>" 
							value="<?php 
                echo esc_attr( $option );
                ?>" 
							<?php 
                checked( $option, $this->index_opt->{$field} );
                ?>
						/>
						<span class="toggle-check-text"></span>
						<?php 
                echo wp_kses_post( $label );
                ?>
					</label>
				</p>
				<?php 
            }
        }
    }

}
