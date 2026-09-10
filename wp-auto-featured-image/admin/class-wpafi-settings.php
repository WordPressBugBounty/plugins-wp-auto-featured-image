<?php
/**
 * WP Auto Featured Image Admin Settings
 *
 * @package WP_Auto_Featured_Image
 */

/**
 * Class WPAFI_Settings
 *
 * This file contains the admin settings class for the WP Auto Featured Image plugin.
 *
 * @since   2.0
 * @package WP_Auto_Featured_Image
 */
class WPAFI_Settings {

	/**
	 * Constructor for the admin class.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	/**
	 * Register plugin settings.
	 */
	public function init_settings() {
		register_setting( 'wp_auto_featured_image_options', 'wpafi_options', array( $this, 'sanitize_options' ) );
		add_settings_section( 'wpafi_default_section', 'General Settings', array( $this, 'wpafi_description' ), 'wp_auto_featured_image_options' );
		add_settings_field( 'wpafi_post_type', 'Post Types:', array( $this, 'wpafi_post_types' ), 'wp_auto_featured_image_options', 'wpafi_default_section' );
		add_settings_field( 'wpafi_categories', 'Categories:', array( $this, 'wpafi_categories' ), 'wp_auto_featured_image_options', 'wpafi_default_section' );
		add_settings_field( 'wpafi_tags', 'Tags:', array( $this, 'wpafi_tags' ), 'wp_auto_featured_image_options', 'wpafi_default_section' );
		add_settings_field( 'wpafi_default_thumbnail', 'Default Thumbnail:', array( $this, 'wpafi_default_thumbnail' ), 'wp_auto_featured_image_options', 'wpafi_default_section' );

		// New settings for v2.1.0.
		add_settings_field( 'wpafi_auto_detect', 'Auto-detect Image:', array( $this, 'wpafi_auto_detect_field' ), 'wp_auto_featured_image_options', 'wpafi_default_section' );
		add_settings_field( 'wpafi_overwrite', 'Overwrite Images:', array( $this, 'wpafi_overwrite_field' ), 'wp_auto_featured_image_options', 'wpafi_default_section' );

		// Conditional Rules section (v2.1.0).
		add_settings_section( 'wpafi_rules_section', 'Conditional Rules', array( $this, 'wpafi_rules_description' ), 'wp_auto_featured_image_options' );
		add_settings_field( 'wpafi_rules', 'Rules:', array( $this, 'wpafi_rules_field' ), 'wp_auto_featured_image_options', 'wpafi_rules_section' );

		// Bulk Operations section.
		add_settings_section( 'wpafi_bulk_section', 'Bulk Operations', array( $this, 'wpafi_bulk_description' ), 'wp_auto_featured_image_options' );
		add_settings_field( 'wpafi_bulk_assign', 'Assign to Existing Posts:', array( $this, 'wpafi_bulk_assign_field' ), 'wp_auto_featured_image_options', 'wpafi_bulk_section' );
	}

	/**
	 * Display the description for the General Settings section.
	 */
	public function wpafi_description() {
		echo '<div class="wpafi-description">';
		echo '<p>' . esc_html__( 'SNY Auto Featured Image allows you to streamline the process of setting featured images effortlessly for your posts, pages, or custom post types. Establish default fallback images or conditional rules to ensure a consistent and efficient way to manage featured images across your content.', 'sny-auto-featured-image' ) . '</p>';
		echo '<p>' . esc_html__( 'Please note that the conditions specified below work in conjunction with an AND logical operator. This means that all conditions must be true for the featured image to be set.', 'sny-auto-featured-image' ) . '</p>';
		echo '<p>' . esc_html__( 'The thumbnail will be set when a post is published. For "page" post types, conditions such as category and tags will be ignored, and the default thumbnail will be applied to all pages upon publishing.', 'sny-auto-featured-image' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Sanitize and validate options.
	 *
	 * @param  array $input The un sanitized input.
	 * @return array The sanitized input.
	 */
	public function sanitize_options( $input ) {
		$sanitized_input = array();
		$has_pro         = function_exists( 'wpafi_has_pro_features' ) && wpafi_has_pro_features();

		// List of multi-select fields to sanitize.
		$multi_select_fields = array( 'wpafi_post_type', 'wpafi_categories', 'wpafi_tags' );

		foreach ( $multi_select_fields as $field ) {
			if ( isset( $input[ $field ] ) && is_array( $input[ $field ] ) ) {
				$sanitized_input[ $field ] = array_map( 'sanitize_text_field', $input[ $field ] );
			}
		}

		// Sanitize specific fields.
		$specific_fields = array( 'wpafi_default_thumb_id' );

		foreach ( $specific_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized_input['wpafi_default_thumb_id'] = intval( $input[ $field ] );
			}
		}

		// Sanitize checkbox fields (v2.1.0).
		$sanitized_input['wpafi_auto_detect'] = isset( $input['wpafi_auto_detect'] ) ? 1 : 0;
		$sanitized_input['wpafi_overwrite']   = isset( $input['wpafi_overwrite'] ) ? 1 : 0;

		// Sanitize rules (v2.1.0) - new structure with post_types/categories arrays.
		if ( isset( $input['wpafi_rules'] ) && is_array( $input['wpafi_rules'] ) ) {
			$sanitized_input['wpafi_rules'] = array();
			$rule_count                     = 0;
			$valid_sources                  = array( 'media', 'first_image', 'external' );
			foreach ( $input['wpafi_rules'] as $rule ) {
				if ( $rule_count >= 2 && ! $has_pro ) {
					break; // Free version: max 2 rules.
				}
				$image_source = isset( $rule['image_source'] ) && in_array( $rule['image_source'], $valid_sources, true )
					? $rule['image_source']
					: 'media';

				$sanitized_input['wpafi_rules'][] = array(
					'name'              => isset( $rule['name'] ) ? sanitize_text_field( $rule['name'] ) : '',
					'image_source'      => $image_source,
					'image_id'          => intval( $rule['image_id'] ?? 0 ),
					'external_url'      => isset( $rule['external_url'] ) ? esc_url_raw( $rule['external_url'] ) : '',
					'include_video'     => isset( $rule['include_video'] ) ? 1 : 0,
					'sideload_external' => isset( $rule['sideload_external'] ) ? 1 : 0,
					'post_types'        => isset( $rule['post_types'] ) ? array_map( 'sanitize_text_field', (array) $rule['post_types'] ) : array(),
					'categories'        => isset( $rule['categories'] ) ? array_map( 'sanitize_text_field', (array) $rule['categories'] ) : array(),
					'tags'              => isset( $rule['tags'] ) ? array_map( 'sanitize_text_field', (array) $rule['tags'] ) : array(),
					'post_statuses'     => isset( $rule['post_statuses'] ) ? array_map( 'sanitize_text_field', (array) $rule['post_statuses'] ) : array(),
					'overwrite'         => isset( $rule['overwrite'] ) ? 1 : 0,
					'enabled'           => isset( $rule['enabled'] ) ? 1 : 0,
					'collapsed'         => isset( $rule['collapsed'] ) ? 1 : 0,
				);
				++$rule_count;
			}
		}

		// Sanitize display options (v2.1.0).
		$sanitized_input['wpafi_show_image_column'] = isset( $input['wpafi_show_image_column'] ) ? 1 : 0;
		$sanitized_input['wpafi_column_size']       = isset( $input['wpafi_column_size'] ) ? max( 30, min( 150, intval( $input['wpafi_column_size'] ) ) ) : 60;

		if ( isset( $input['wpafi_column_post_types'] ) && is_array( $input['wpafi_column_post_types'] ) ) {
			$sanitized_input['wpafi_column_post_types'] = array_map( 'sanitize_text_field', $input['wpafi_column_post_types'] );
		}

		return $sanitized_input;
	}


	/**
	 * Render a multiselect dropdown for post types in the plugin settings.
	 */
	public function wpafi_post_types() {
		/**
		 * Options saved in the WordPress database.
		 *
		 * @var array
		 */
		$options = get_option( 'wpafi_options' );

		/**
		 * Array of public post types.
		 *
		 * @var array
		 */
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		echo '<select  class="wpafi-select2" id="wpafi-multiselect" name="wpafi_options[wpafi_post_type][]" multiple="multiple">';
		foreach ( $post_types as $post_type ) {
			if ( 'attachment' !== $post_type ) {
				$selected = '';
				if ( ! empty( $options['wpafi_post_type'] ) ) {
					if ( in_array( $post_type, $options['wpafi_post_type'], true ) ) {
						$selected = " selected='selected'";
					}
				}
				echo '<option value="' . esc_attr( $post_type ) . '"' . esc_attr( $selected ) . '>' . esc_html( preg_replace( '/[-_]/', ' ', $post_type ) ) . '</option>';
			}
		}
		echo '</select>';
	}

	/**
	 * Render a multiselect dropdown for categories in the plugin settings.
	 */
	public function wpafi_categories() {
		$options    = get_option( 'wpafi_options' );
		$wpafi_cats = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		echo '<select class="wpafi-select2" id="wpafi-category-multiselect" name="wpafi_options[wpafi_categories][]" multiple="multiple">';
		foreach ( $wpafi_cats as $wpafi_cat ) {

			$selected = '';
			if ( ! empty( $options['wpafi_categories'] ) && is_array( $options['wpafi_categories'] ) ) {
				$selected = in_array( $wpafi_cat->slug, $options['wpafi_categories'], true ) ? ' selected="selected"' : '';
			}
			echo '<option value="' . esc_attr( $wpafi_cat->slug ) . '"' . esc_attr( $selected ) . '>' . esc_attr( $wpafi_cat->name ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Render a multiselect dropdown for tags in the plugin settings.
	 */
	public function wpafi_tags() {
		$options = get_option( 'wpafi_options' );
		$tags    = get_tags();

		echo '<select id="wpafi-tag-multiselect" class="wpafi-select2"  name="wpafi_options[wpafi_tags][]" multiple="multiple">';
		foreach ( $tags as $tag ) {
			$selected = in_array( $tag->slug, $options['wpafi_tags'], true ) ? ' selected="selected"' : '';
			echo '<option value="' . esc_attr( $tag->slug ) . '"' . esc_attr( $selected ) . '>' . esc_attr( $tag->name ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Renders the HTML for the default thumbnail settings in the admin panel.
	 */
	public function wpafi_default_thumbnail() {
		$options = get_option( 'wpafi_options' );
		?>
		<div class="upload-container">
			<input type="hidden" id="default_thumb_id" name="wpafi_options[wpafi_default_thumb_id]" value="<?php echo esc_attr( $options['wpafi_default_thumb_id'] ?? '' ); ?>" />
			<button id="upload_default_thumb" class="button" type="button"><?php esc_html_e( 'Upload Thumbnail', 'sny-auto-featured-image' ); ?></button>
		<?php if ( ! empty( $options['wpafi_default_thumb_id'] ) ) : ?>
				<button id="delete_thumb" name="delete_thumb" class="button" type="button"><?php esc_html_e( 'Delete Thumbnail', 'sny-auto-featured-image' ); ?></button>
		<?php endif; ?>
			<div id="uploaded_thumb_preview">
		<?php
		if ( ! empty( $options['wpafi_default_thumb_id'] ) ) {
			// Use wp_get_attachment_image to display the image by ID.
			echo wp_get_attachment_image( $options['wpafi_default_thumb_id'], 'full', false, array( 'style' => 'max-width:100%;' ) );
		}
		?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the auto-detect first image checkbox field.
	 */
	public function wpafi_auto_detect_field() {
		$options = get_option( 'wpafi_options' );
		$checked = ! empty( $options['wpafi_auto_detect'] ) ? 'checked' : '';
		?>
		<label>
			<input type="checkbox" name="wpafi_options[wpafi_auto_detect]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Auto-detect first image in post content', 'sny-auto-featured-image' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'If enabled, the plugin will find the first image in your post content and use it as the featured image. Falls back to default image if no image is found.', 'sny-auto-featured-image' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the overwrite existing images checkbox field.
	 */
	public function wpafi_overwrite_field() {
		$options = get_option( 'wpafi_options' );
		$checked = ! empty( $options['wpafi_overwrite'] ) ? 'checked' : '';
		?>
		<label>
			<input type="checkbox" name="wpafi_options[wpafi_overwrite]" value="1" <?php echo esc_attr( $checked ); ?> />
			<?php esc_html_e( 'Overwrite existing featured images', 'sny-auto-featured-image' ); ?>
		</label>
		<p class="description" style="color: #d63638;">
			<?php esc_html_e( '⚠️ Warning: This will replace all existing featured images when posts are updated.', 'sny-auto-featured-image' ); ?>
		</p>
		<?php
	}

	/**
	 * Display description for Bulk Operations section.
	 */
	public function wpafi_bulk_description() {
		echo '<p>' . esc_html__( 'Apply featured images to existing posts that match your settings above.', 'sny-auto-featured-image' ) . '</p>';
	}

	/**
	 * Display description for Conditional Rules section.
	 */
	public function wpafi_rules_description() {
		echo '<p>' . esc_html__( 'Create rules to assign different featured images based on conditions. Rules are checked in order - first matching rule wins.', 'sny-auto-featured-image' ) . '</p>';
		$has_pro = function_exists( 'wpafi_has_pro_features' ) && wpafi_has_pro_features();
		if ( ! $has_pro ) {
			echo '<p class="description"><strong>' . esc_html__( 'Free version: 2 rules maximum.', 'sny-auto-featured-image' ) . '</strong> ';
			echo '<a href="https://sanny.dev/plugins/sny-auto-featured-image-pro/?utm_source=plugin&utm_medium=rules&utm_campaign=roadmap" target="_blank">' . esc_html__( 'More rules coming in Pro', 'sny-auto-featured-image' ) . '</a></p>';
		}
	}

	/**
	 * Render the conditional rules builder field.
	 */
	public function wpafi_rules_field() {
		$options   = get_option( 'wpafi_options' );
		$rules     = isset( $options['wpafi_rules'] ) ? $options['wpafi_rules'] : array();
		$has_pro   = function_exists( 'wpafi_has_pro_features' ) && wpafi_has_pro_features();
		$max_rules = $has_pro ? 999 : 2;

		// Get available categories for dropdown.
		$categories = get_categories( array( 'hide_empty' => false ) );
		?>
		<div id="wpafi-rules-container">
			<?php
			if ( ! empty( $rules ) ) {
				foreach ( $rules as $index => $rule ) {
					$this->render_rule_row( $index, $rule, $categories );
				}
			}
			?>
		</div>

		<button type="button" id="wpafi-add-rule" class="button button-secondary" <?php echo count( $rules ) >= $max_rules ? 'disabled' : ''; ?>>
			<?php esc_html_e( '+ Add Rule', 'sny-auto-featured-image' ); ?>
		</button>

		<?php if ( ! $has_pro && count( $rules ) >= 2 ) : ?>
			<span class="wpafi-pro-notice" style="margin-left: 10px; color: #d63638;">
				<span class="dashicons dashicons-lock" style="vertical-align: middle;"></span>
				<?php esc_html_e( 'Upgrade to Pro for unlimited rules', 'sny-auto-featured-image' ); ?>
			</span>
		<?php endif; ?>

		<!-- Template for new rules (hidden, cloned by JS) -->
		<script type="text/template" id="wpafi-rule-template">
			<?php $this->render_rule_row( '{{INDEX}}', array(), $categories ); ?>
		</script>
		<?php
	}

	/**
	 * Render a single rule row.
	 *
	 * @param int|string $index      Rule index.
	 * @param array      $rule       Rule data.
	 * @param array      $categories Available categories.
	 */
	private function render_rule_row( $index, $rule, $categories ) {
		$condition_type  = isset( $rule['condition_type'] ) ? $rule['condition_type'] : '';
		$condition_value = isset( $rule['condition_value'] ) ? $rule['condition_value'] : '';
		$image_id        = isset( $rule['image_id'] ) ? $rule['image_id'] : 0;
		$post_types      = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<div class="wpafi-rule-card" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="wpafi-rule-card-header">
				<span class="wpafi-rule-number"><?php /* translators: %s is rule number */ printf( esc_html__( 'Rule #%s', 'sny-auto-featured-image' ), '<span class="wpafi-rule-index">' . ( is_numeric( $index ) ? intval( $index ) + 1 : '?' ) . '</span>' ); ?></span>
				<button type="button" class="wpafi-remove-rule" title="<?php esc_attr_e( 'Remove Rule', 'sny-auto-featured-image' ); ?>">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>
			<div class="wpafi-rule-card-body">
				<div class="wpafi-rule-condition">
					<div class="wpafi-rule-field">
						<label><?php esc_html_e( 'When', 'sny-auto-featured-image' ); ?></label>
						<select name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][condition_type]" class="wpafi-condition-type wpafi-select2">
							<option value=""><?php esc_html_e( 'Select condition...', 'sny-auto-featured-image' ); ?></option>
							<option value="category" <?php selected( $condition_type, 'category' ); ?>><?php esc_html_e( 'Category is', 'sny-auto-featured-image' ); ?></option>
							<option value="post_type" <?php selected( $condition_type, 'post_type' ); ?>><?php esc_html_e( 'Post Type is', 'sny-auto-featured-image' ); ?></option>
						</select>
					</div>
					<div class="wpafi-rule-field wpafi-condition-value-container">
						<label><?php esc_html_e( 'Value', 'sny-auto-featured-image' ); ?></label>
						<select name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][condition_value]" class="wpafi-condition-value wpafi-select2">
							<option value=""><?php esc_html_e( 'Select value...', 'sny-auto-featured-image' ); ?></option>
							<?php if ( 'category' === $condition_type || '' === $condition_type ) : ?>
								<?php foreach ( $categories as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $condition_value, $cat->slug ); ?>><?php echo esc_html( $cat->name ); ?></option>
								<?php endforeach; ?>
							<?php else : ?>
								<?php foreach ( $post_types as $pt ) : ?>
									<?php if ( 'attachment' !== $pt->name ) : ?>
										<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $condition_value, $pt->name ); ?>><?php echo esc_html( $pt->label ); ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>
				<div class="wpafi-rule-image">
					<label><?php esc_html_e( 'Use this image', 'sny-auto-featured-image' ); ?></label>
					<div class="wpafi-rule-image-box">
						<input type="hidden" name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][image_id]" class="wpafi-rule-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
						<div class="wpafi-rule-image-preview">
							<?php if ( $image_id ) : ?>
								<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
							<?php else : ?>
								<div class="wpafi-rule-no-image">
									<span class="dashicons dashicons-format-image"></span>
								</div>
							<?php endif; ?>
						</div>
						<button type="button" class="button wpafi-select-image"><?php esc_html_e( 'Select', 'sny-auto-featured-image' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the bulk assign button field.
	 */
	public function wpafi_bulk_assign_field() {
		?>
		<button type="button" id="wpafi-bulk-assign" class="button button-secondary">
			<?php esc_html_e( 'Assign Featured Images to Existing Posts', 'sny-auto-featured-image' ); ?>
		</button>
		<span id="wpafi-bulk-spinner" class="spinner" style="float: none; margin-top: 0;"></span>
		<div id="wpafi-bulk-result" style="margin-top: 10px;"></div>
		<p class="description">
			<?php esc_html_e( 'This will apply featured images to all published posts that match your post type, category, and tag settings above.', 'sny-auto-featured-image' ); ?>
		</p>
		<?php
	}
}
