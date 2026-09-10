<?php
/**
 * Admin settings file for the Auto Featured Image plugin.
 *
 * Single-page UI with repeatable image + condition boxes.
 * This template is included within a method, so variables are local scope.
 *
 * @package WP_Auto_Featured_Image
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are local scope.

$options          = get_option( 'wpafi_options' );
$rules            = isset( $options['wpafi_rules'] ) ? $options['wpafi_rules'] : array();
$is_pro_active    = function_exists( 'wpafi_is_pro_active' ) && wpafi_is_pro_active();
$has_pro          = function_exists( 'wpafi_has_pro_features' ) && wpafi_has_pro_features();
$show_pro_teasers = ! $has_pro;
$upgrade_url      = 'https://sanny.dev/plugins/sny-auto-featured-image-pro/';
$max_rules        = $has_pro ? 999 : 2;
$categories       = get_categories( array( 'hide_empty' => false ) );
$tags             = get_tags( array( 'hide_empty' => false ) );
$post_types       = get_post_types( array( 'public' => true ), 'objects' );
$post_statuses    = array(
	'publish' => __( 'Published', 'sny-auto-featured-image' ),
	'draft'   => __( 'Draft', 'sny-auto-featured-image' ),
	'pending' => __( 'Pending', 'sny-auto-featured-image' ),
	'future'  => __( 'Scheduled', 'sny-auto-featured-image' ),
	'private' => __( 'Private', 'sny-auto-featured-image' ),
);
?>
<div class="wrap wpafi-settings-wrap">
	<!-- WordPress injects admin_notices after first h1/h2 in .wrap -->
	<h1 class="wp-heading-inline" style="display:none;"></h1>

	<!-- Hidden settings errors - will be converted to toast -->
	<div id="wpafi-settings-messages" style="display:none;">
		<?php settings_errors( 'wpafi_options' ); ?>
	</div>

	<div class="wpafi-header">
		<div class="wpafi-header-content">
			<h1>
				<span class="wpafi-logo dashicons dashicons-format-image"></span>
				<?php esc_html_e( 'SNY Auto Featured Image', 'sny-auto-featured-image' ); ?>
			</h1>
			<span
				class="wpafi-version">v<?php echo esc_html( defined( 'WPAFI_VERSION' ) ? WPAFI_VERSION : '2.1.0' ); ?></span>
		</div>
		<?php if ( $show_pro_teasers ) : ?>
		<a href="<?php echo esc_url( $upgrade_url ); ?>"
			class="wpafi-upgrade-btn" target="_blank">
			<span class="dashicons dashicons-star-filled"></span>
			<?php esc_html_e( 'Pro Coming Soon', 'sny-auto-featured-image' ); ?>
		</a>
		<?php endif; ?>
	</div>

	<!-- Toast Container -->
	<div id="wpafi-toast-container"></div>

	<div class="wpafi-content">
		<div class="wpafi-main">
			<!-- Tabs Navigation -->
			<div class="wpafi-tabs">
				<button type="button" class="wpafi-tab-btn active" data-tab="rules">
					<span class="dashicons dashicons-format-image"></span>
					<?php esc_html_e( 'Image Rules', 'sny-auto-featured-image' ); ?>
				</button>
				<button type="button" class="wpafi-tab-btn" data-tab="bulk">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Bulk Operations', 'sny-auto-featured-image' ); ?>
				</button>
				<button type="button" class="wpafi-tab-btn" data-tab="settings">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Settings', 'sny-auto-featured-image' ); ?>
				</button>
				<button type="button" class="wpafi-tab-btn" data-tab="help">
					<span class="dashicons dashicons-editor-help"></span>
					<?php esc_html_e( 'Help', 'sny-auto-featured-image' ); ?>
				</button>
			</div>

			<form method="post" action="options.php" id="wpafi-settings-form">
				<?php settings_fields( 'wp_auto_featured_image_options' ); ?>

				<!-- Tab: Image Rules -->
				<div id="wpafi-tab-rules" class="wpafi-tab-panel active">

					<p class="wpafi-intro">
						<?php esc_html_e( 'Set up featured images that automatically apply to your posts based on conditions. Each rule can have its own image and apply to specific post types or categories.', 'sny-auto-featured-image' ); ?>
					</p>

					<!-- Rules Container -->
					<div id="wpafi-rules-container">
						<?php
						if ( ! empty( $rules ) ) {
							// Find the first active rule index to expand it.
							$first_active_index = null;
							foreach ( $rules as $i => $r ) {
								if ( ! isset( $r['enabled'] ) || $r['enabled'] ) {
									$first_active_index = $i;
									break;
								}
							}

							foreach ( $rules as $index => $rule ) {
								wpafi_render_image_rule_card( $index, $rule, $categories, $tags, $post_types, $post_statuses, $first_active_index );
							}
						} else {
							// Show one empty card by default.
							wpafi_render_image_rule_card( 0, array(), $categories, $tags, $post_types, $post_statuses, 0 );
						}
						?>
					</div>

					<!-- Add Rule Button -->
					<div
						class="wpafi-add-rule-container <?php echo ( ! $has_pro && count( $rules ) >= 2 ) ? 'wpafi-add-rule-locked' : ''; ?>">
						<button type="button" id="wpafi-add-rule" class="wpafi-add-rule-btn"
							<?php echo ( ! $has_pro && count( $rules ) >= 2 ) ? 'disabled' : ''; ?>>
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Add New Rule', 'sny-auto-featured-image' ); ?>
						</button>
						<?php if ( ! $has_pro ) : ?>
							<?php
							$rule_count  = max( count( $rules ), 1 );
							$is_at_limit = $rule_count >= $max_rules;
							?>
						<span class="wpafi-rule-limit <?php echo $is_at_limit ? 'wpafi-rule-limit-locked' : ''; ?>">
							<?php if ( $is_at_limit ) : ?>
							<span class="dashicons dashicons-lock"></span>
							<span>
								<?php
								/* translators: 1: Current rule count, 2: Maximum rules allowed. */
								printf( esc_html__( '%1$d of %2$d rules used', 'sny-auto-featured-image' ), (int) $rule_count, (int) $max_rules );
								?>
							</span>
								<?php if ( $show_pro_teasers ) : ?>
							<span class="wpafi-rule-limit-upgrade">
								— <a href="<?php echo esc_url( $upgrade_url ); ?>"
									target="_blank"><?php esc_html_e( 'More rules in Pro (Coming Soon)', 'sny-auto-featured-image' ); ?></a>
							</span>
							<?php endif; ?>
							<?php else : ?>
								<?php
								/* translators: 1: Current rule count, 2: Maximum rules allowed. */
								printf( esc_html__( '%1$d of %2$d rules used', 'sny-auto-featured-image' ), (int) $rule_count, (int) $max_rules );
								?>
							<?php endif; ?>
						</span>
						<?php endif; ?>
					</div>

					<!-- Save Button -->
					<div class="wpafi-save-btn">
						<button type="submit" class="button button-primary button-hero">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Save Settings', 'sny-auto-featured-image' ); ?>
						</button>
					</div>

					<!-- Pro Features Showcase -->
					<?php if ( $show_pro_teasers ) : ?>
					<div class="wpafi-pro-showcase">
						<div class="wpafi-pro-showcase-header">
							<span class="dashicons dashicons-star-filled"></span>
							<h3><?php esc_html_e( 'Coming Soon in Pro', 'sny-auto-featured-image' ); ?></h3>
						</div>
						<div class="wpafi-pro-showcase-grid">
							<div class="wpafi-pro-showcase-item">
								<span class="dashicons dashicons-undo"></span>
								<h4><?php esc_html_e( 'Full Dry Run & Undo', 'sny-auto-featured-image' ); ?></h4>
								<p><?php esc_html_e( 'Full-library dry run + 30-day undo log.', 'sny-auto-featured-image' ); ?></p>
							</div>
							<div class="wpafi-pro-showcase-item">
								<span class="dashicons dashicons-plus-alt2"></span>
								<h4><?php esc_html_e( 'Unlimited Rules', 'sny-auto-featured-image' ); ?></h4>
								<p><?php esc_html_e( 'Create as many conditional rules as you need with drag-and-drop priority.', 'sny-auto-featured-image' ); ?></p>
							</div>
							<div class="wpafi-pro-showcase-item">
								<span class="dashicons dashicons-update"></span>
								<h4><?php esc_html_e( 'Unlimited Bulk Processing', 'sny-auto-featured-image' ); ?></h4>
								<p><?php esc_html_e( 'Process hundreds or thousands of posts with background batch processing.', 'sny-auto-featured-image' ); ?></p>
							</div>
						</div>
						<div class="wpafi-pro-showcase-cta">
							<a href="<?php echo esc_url( $upgrade_url ); ?>"
								class="button button-primary button-hero" target="_blank">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'View Pro Roadmap', 'sny-auto-featured-image' ); ?>
							</a>
						</div>
					</div>
					<?php endif; ?>

				</div><!-- End Tab: Image Rules -->

				<!-- Tab: Bulk Operations -->
				<div id="wpafi-tab-bulk" class="wpafi-tab-panel">
					<div class="wpafi-card wpafi-bulk-card">
						<div class="wpafi-card-header">
							<h2>
								<span class="dashicons dashicons-update"></span>
								<?php esc_html_e( 'Bulk Apply Featured Images', 'sny-auto-featured-image' ); ?>
							</h2>
						</div>
						<div class="wpafi-card-body">
							<div class="wpafi-danger-warning">
								<span class="dashicons dashicons-warning"></span>
								<div>
									<strong><?php esc_html_e( 'Warning: This action is irreversible!', 'sny-auto-featured-image' ); ?></strong>
									<p><?php esc_html_e( 'Please backup your database before proceeding. You are fully responsible for any data changes.', 'sny-auto-featured-image' ); ?>
									</p>
								</div>
							</div>

							<p class="wpafi-description">
								<?php esc_html_e( 'Apply featured images to existing posts that don\'t have one. Choose which rule to apply.', 'sny-auto-featured-image' ); ?>
							</p>

							<div class="wpafi-bulk-rule-select">
								<label
									for="wpafi-bulk-rule"><?php esc_html_e( 'Apply Rule:', 'sny-auto-featured-image' ); ?></label>
								<select id="wpafi-bulk-rule" class="wpafi-select2">
									<option value="all">
										<?php esc_html_e( 'All Rules (in order)', 'sny-auto-featured-image' ); ?>
									</option>
									<?php
									if ( ! empty( $rules ) ) {
										foreach ( $rules as $idx => $r ) {
											/* translators: %d: Rule number. */
											$rule_name = ! empty( $r['name'] ) ? $r['name'] : sprintf( __( 'Rule #%d', 'sny-auto-featured-image' ), $idx + 1 );
											echo '<option value="' . esc_attr( $idx ) . '">' . esc_html( $rule_name ) . '</option>';
										}
									}
									?>
								</select>
							</div>

							<div class="wpafi-progress-container" id="wpafi-progress-container" style="display: none;">
								<div class="wpafi-progress-bar">
									<div class="wpafi-progress-fill" id="wpafi-progress-fill"></div>
								</div>
								<div class="wpafi-progress-text" id="wpafi-progress-text">0%</div>
							</div>

							<div class="wpafi-bulk-actions" style="display: flex; gap: 10px; align-items: center; margin: 20px 0;">
								<button type="button" id="wpafi-bulk-preview-btn" class="button button-secondary button-hero">
									<span class="dashicons dashicons-visibility"></span>
									<?php esc_html_e( 'Preview Changes', 'sny-auto-featured-image' ); ?>
								</button>
								<button type="button" id="wpafi-bulk-assign" class="button button-primary button-hero">
									<span class="dashicons dashicons-update"></span>
									<?php esc_html_e( 'Apply (Up to 50 Posts)', 'sny-auto-featured-image' ); ?>
								</button>
								<span class="spinner" id="wpafi-bulk-spinner"></span>
							</div>

							<!-- Bulk Preview Table Container -->
							<div id="wpafi-preview-container" style="display: none; margin: 20px 0;">
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
									<h3 style="margin: 0;"><?php esc_html_e( 'Proposed Changes (Preview)', 'sny-auto-featured-image' ); ?></h3>
									<span id="wpafi-preview-count-badge" class="wpafi-badge" style="background: #e0e0e0; padding: 4px 8px; border-radius: 4px; font-size: 12px;"></span>
								</div>
								<p class="description"><?php esc_html_e( 'Preview showing up to 50 matching posts. Pro unlocks unlimited bulk operations and undo.', 'sny-auto-featured-image' ); ?></p>
								<div class="wpafi-preview-table-wrapper" style="max-height: 400px; overflow-y: auto; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 15px;">
									<table class="wp-list-table widefat fixed striped" id="wpafi-preview-table">
										<thead>
											<tr>
												<th style="width: 50px;"><?php esc_html_e( 'ID', 'sny-auto-featured-image' ); ?></th>
												<th><?php esc_html_e( 'Post Title', 'sny-auto-featured-image' ); ?></th>
												<th style="width: 95px; text-align: center; white-space: nowrap;"><?php esc_html_e( 'Current', 'sny-auto-featured-image' ); ?></th>
												<th style="width: 95px; text-align: center; white-space: nowrap;"><?php esc_html_e( 'Proposed', 'sny-auto-featured-image' ); ?></th>
												<th><?php esc_html_e( 'Matched Rule / Reason', 'sny-auto-featured-image' ); ?></th>
												<th style="width: 80px; text-align: center;"><?php esc_html_e( 'Action', 'sny-auto-featured-image' ); ?></th>
											</tr>
										</thead>
										<tbody id="wpafi-preview-tbody">
											<!-- Dynamically populated via AJAX -->
										</tbody>
									</table>
								</div>
							</div>

							<!-- Pro Feature Teasers -->
							<?php if ( $show_pro_teasers ) : ?>
							<div class="wpafi-pro-teasers">
								<div class="wpafi-pro-teaser-option">
									<span class="dashicons dashicons-undo"></span>
									<span><?php esc_html_e( '30-Day Undo Log & Full Rollback', 'sny-auto-featured-image' ); ?></span>
									<span class="wpafi-pro-lock-badge">
										<span class="dashicons dashicons-lock"></span>
										<?php esc_html_e( 'PRO', 'sny-auto-featured-image' ); ?>
									</span>
								</div>
								<div class="wpafi-pro-teaser-option">
									<span class="dashicons dashicons-database"></span>
									<span><?php esc_html_e( 'Unlimited Bulk Processing (50+ Posts)', 'sny-auto-featured-image' ); ?></span>
									<span class="wpafi-pro-lock-badge">
										<span class="dashicons dashicons-lock"></span>
										<?php esc_html_e( 'PRO', 'sny-auto-featured-image' ); ?>
									</span>
								</div>
							</div>
							<?php endif; ?>

							<div id="wpafi-bulk-result"></div>
						</div>
					</div>
				</div><!-- End Tab: Bulk Operations -->

				<!-- Tab: Settings -->
				<div id="wpafi-tab-settings" class="wpafi-tab-panel">

					<div class="wpafi-card">
						<div class="wpafi-card-header">
							<h2>
								<span class="dashicons dashicons-format-gallery"></span>
								<?php esc_html_e( 'Posts List Image Column', 'sny-auto-featured-image' ); ?>
							</h2>
						</div>
						<div class="wpafi-card-body">
							<p class="wpafi-description">
								<?php esc_html_e( 'Show a thumbnail column in your posts list for quick visibility of featured images.', 'sny-auto-featured-image' ); ?>
							</p>

							<div class="wpafi-display-options">
								<label class="wpafi-checkbox wpafi-checkbox-large">
									<input type="checkbox" name="wpafi_options[wpafi_show_image_column]" value="1"
										<?php checked( ! empty( $options['wpafi_show_image_column'] ) ); ?> />
									<span><?php esc_html_e( 'Show featured image column in posts list', 'sny-auto-featured-image' ); ?></span>
								</label>

								<div class="wpafi-column-settings" id="wpafi-column-settings">
									<div class="wpafi-condition-row">
										<label><?php esc_html_e( 'Show in post types:', 'sny-auto-featured-image' ); ?></label>
										<?php
										$column_post_types = ! empty( $options['wpafi_column_post_types'] ) ? $options['wpafi_column_post_types'] : array( 'post' );
										?>
										<select name="wpafi_options[wpafi_column_post_types][]"
											class="wpafi-select2" multiple="multiple">
											<?php foreach ( $post_types as $pt ) : ?>
												<?php if ( 'attachment' !== $pt->name ) : ?>
											<option value="<?php echo esc_attr( $pt->name ); ?>"
													<?php echo in_array( $pt->name, $column_post_types, true ) ? 'selected' : ''; ?>>
													<?php echo esc_html( $pt->label ); ?></option>
											<?php endif; ?>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="wpafi-condition-row wpafi-size-row">
										<label><?php esc_html_e( 'Thumbnail size:', 'sny-auto-featured-image' ); ?></label>
										<?php $column_size = ! empty( $options['wpafi_column_size'] ) ? intval( $options['wpafi_column_size'] ) : 60; ?>
										<div class="wpafi-size-input">
											<input type="number" name="wpafi_options[wpafi_column_size]"
												value="<?php echo esc_attr( $column_size ); ?>" min="30" max="150"
												step="10" />
											<span>px</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div><!-- End Card: Posts List Image Column -->

					<!-- Save Button for Settings -->
					<div class="wpafi-save-btn">
						<button type="submit" class="button button-primary button-hero">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Save Settings', 'sny-auto-featured-image' ); ?>
						</button>
					</div>
				</div><!-- End Tab: Settings -->

				<!-- Tab: Help -->
				<div id="wpafi-tab-help" class="wpafi-tab-panel">
					<div class="wpafi-help-content">

						<!-- Getting Started -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<?php esc_html_e( 'Getting Started', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card">
								<p><?php esc_html_e( 'SNY Auto Featured Image automatically sets featured images for your posts based on rules you define. Here\'s how to get started:', 'sny-auto-featured-image' ); ?></p>
								<ol>
									<li><?php esc_html_e( 'Go to the Image Rules tab', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Configure Image Rule #1 (already visible by default)', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Select a default image from your media library', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Set conditions (optional) to target specific posts', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Click "Add Rule" to create additional rules if needed', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Save your settings', 'sny-auto-featured-image' ); ?></li>
								</ol>
							</div>
						</div>

						<!-- Image Rules -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-format-image"></span>
								<?php esc_html_e( 'Image Rules', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card">
								<h3><?php esc_html_e( 'What are Image Rules?', 'sny-auto-featured-image' ); ?></h3>
								<p><?php esc_html_e( 'Image Rules let you define which featured image should be used for posts matching specific conditions. Rules are processed in order from top to bottom - the first matching rule wins.', 'sny-auto-featured-image' ); ?></p>

								<h3><?php esc_html_e( 'Rule Priority', 'sny-auto-featured-image' ); ?></h3>
								<p><?php esc_html_e( 'Rules at the top of the list have higher priority. Drag and drop rules to reorder them. When a post is saved, the plugin checks each rule in order and applies the first one that matches.', 'sny-auto-featured-image' ); ?></p>

								<h3><?php esc_html_e( 'Conditions', 'sny-auto-featured-image' ); ?></h3>
								<p><?php esc_html_e( 'Each rule can have multiple conditions that must ALL be met (AND logic). Available conditions:', 'sny-auto-featured-image' ); ?></p>
								<ul>
									<li><strong><?php esc_html_e( 'Post Type:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Apply to specific post types (Posts, Pages, Products, etc.)', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Category:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Apply to posts in specific categories', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Tag:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Apply to posts with specific tags', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Post Status:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Apply to published, draft, pending, or scheduled posts', 'sny-auto-featured-image' ); ?></li>
								</ul>

								<h3><?php esc_html_e( 'Rule Management', 'sny-auto-featured-image' ); ?></h3>
								<ul>
									<li><strong><?php esc_html_e( 'Enable/Disable:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Use the toggle switch in the rule header to quickly turn a rule on or off without deleting it. Disabled rules are skipped during processing.', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Collapsible Cards:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Click on a rule header to collapse it. This helps keep your workspace organized when managing multiple rules.', 'sny-auto-featured-image' ); ?></li>
								</ul>

								<h3><?php esc_html_e( 'Example Use Cases', 'sny-auto-featured-image' ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'Set a "News" category image for all news posts', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Use a product placeholder for WooCommerce products', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Apply a default blog image for all posts without conditions', 'sny-auto-featured-image' ); ?></li>
								</ul>
							</div>
						</div>

						<!-- Bulk Operations -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-update"></span>
								<?php esc_html_e( 'Bulk Operations', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card">
								<h3><?php esc_html_e( 'Generate Featured Images', 'sny-auto-featured-image' ); ?></h3>
								<p><?php esc_html_e( 'This feature scans your existing posts and automatically assigns featured images based on your defined rules. Perfect for:', 'sny-auto-featured-image' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Migrating from another theme or plugin', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Applying rules to older posts that don\'t have featured images', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Updating images after changing your rules', 'sny-auto-featured-image' ); ?></li>
								</ul>

								<h3><?php esc_html_e( 'Bulk Options', 'sny-auto-featured-image' ); ?></h3>
								<ul>
									<li><strong><?php esc_html_e( 'Apply Rule:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Choose a specific rule to apply or process all active rules in order.', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Preview Changes:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Safely test your rules before modifying your database. Displays a preview table of up to 50 matching posts with match reasons.', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Apply (Up to 50 Posts):', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Executes the assignment for matching posts. Free processes up to 50 posts per batch.', 'sny-auto-featured-image' ); ?></li>
								</ul>

								<div class="wpafi-help-warning">
									<span class="dashicons dashicons-warning"></span>
									<p><?php esc_html_e( 'Warning: Bulk operations can modify many posts at once. Consider backing up your database before running bulk operations if your rules have "Overwrite existing featured images" enabled.', 'sny-auto-featured-image' ); ?></p>
								</div>
							</div>
						</div>

						<!-- Settings -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-admin-settings"></span>
								<?php esc_html_e( 'Settings Explained', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card">
								<h3><?php esc_html_e( 'Post List Display', 'sny-auto-featured-image' ); ?></h3>
								<ul>
									<li><strong><?php esc_html_e( 'Show Featured Image Column:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Adds a thumbnail column to the post list in the admin, making it easy to see which posts have featured images.', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Show in post types:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Select which post types display the thumbnail column.', 'sny-auto-featured-image' ); ?></li>
									<li><strong><?php esc_html_e( 'Thumbnail size:', 'sny-auto-featured-image' ); ?></strong> <?php esc_html_e( 'Adjust the thumbnail preview size in the post list (30-150 pixels).', 'sny-auto-featured-image' ); ?></li>
								</ul>
							</div>
						</div>

						<!-- How Auto-Assignment Works -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-info"></span>
								<?php esc_html_e( 'How Auto-Assignment Works', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card">
								<p><?php esc_html_e( 'When a post is saved, the plugin follows this process:', 'sny-auto-featured-image' ); ?></p>
								<ol>
									<li><?php esc_html_e( 'Check if the post already has a featured image (skip if it does, unless overwrite is enabled)', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Loop through Image Rules from top to bottom', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'For each rule, check if ALL conditions match the post', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'If a rule matches, apply its image and stop', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'If no rules match, no featured image is set, leaving your post untouched', 'sny-auto-featured-image' ); ?></li>
								</ol>

								<h3><?php esc_html_e( 'Tips for Best Results', 'sny-auto-featured-image' ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'Put more specific rules at the top (e.g., "Category: News" before "All Posts")', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Use a catch-all rule at the bottom with no conditions as a fallback', 'sny-auto-featured-image' ); ?></li>
									<li><?php esc_html_e( 'Test your rules with a draft post before applying bulk operations', 'sny-auto-featured-image' ); ?></li>
								</ul>
							</div>
						</div>

						<!-- FAQ -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-editor-help"></span>
								<?php esc_html_e( 'Frequently Asked Questions', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card wpafi-faq">
								<div class="wpafi-faq-item">
									<h4><?php esc_html_e( 'Why isn\'t my featured image being set?', 'sny-auto-featured-image' ); ?></h4>
									<p><?php esc_html_e( 'Check that: 1) Your Image Rule is enabled (toggle switch is ON), 2) Your rules have valid conditions, 3) The post matches at least one rule\'s conditions, 4) The post doesn\'t already have a featured image (unless "Overwrite existing featured images" is enabled on the matching rule).', 'sny-auto-featured-image' ); ?></p>
								</div>

								<div class="wpafi-faq-item">
									<h4><?php esc_html_e( 'Can I use different images for different categories?', 'sny-auto-featured-image' ); ?></h4>
									<p><?php esc_html_e( 'Yes! Create multiple rules, each with a different category condition and image. Remember to order them by priority.', 'sny-auto-featured-image' ); ?></p>
								</div>

								<div class="wpafi-faq-item">
									<h4><?php esc_html_e( 'Does this work with WooCommerce?', 'sny-auto-featured-image' ); ?></h4>
									<p><?php esc_html_e( 'Yes, the plugin works with any public post type including WooCommerce Products. Select "Products" as the post type in your rule conditions.', 'sny-auto-featured-image' ); ?></p>
								</div>

								<div class="wpafi-faq-item">
									<h4><?php esc_html_e( 'Will this overwrite my existing featured images?', 'sny-auto-featured-image' ); ?></h4>
									<p><?php esc_html_e( 'By default, no. The plugin only sets featured images on posts that don\'t have one. If you want to replace existing images, check "Overwrite existing featured images" inside the specific rule.', 'sny-auto-featured-image' ); ?></p>
								</div>

								<div class="wpafi-faq-item">
									<h4><?php esc_html_e( 'What happens if I delete a rule?', 'sny-auto-featured-image' ); ?></h4>
									<p><?php esc_html_e( 'Existing featured images are not affected. Only new posts (or posts processed through Bulk Operations) will use the updated rules.', 'sny-auto-featured-image' ); ?></p>
								</div>
							</div>
						</div>

						<?php if ( $show_pro_teasers ) : ?>
						<!-- Pro Priority Support (Locked) -->
						<div class="wpafi-help-section wpafi-help-pro-locked">
							<h2>
								<span class="dashicons dashicons-lock"></span>
								<?php esc_html_e( 'Priority Support', 'sny-auto-featured-image' ); ?>
								<span class="wpafi-pro-badge"><?php esc_html_e( 'PRO', 'sny-auto-featured-image' ); ?></span>
							</h2>
							<div class="wpafi-help-card wpafi-pro-support-card">
								<div class="wpafi-pro-support-overlay">
									<div class="wpafi-pro-support-features">
										<div class="wpafi-pro-support-feature">
											<span class="dashicons dashicons-email-alt"></span>
											<div>
												<strong><?php esc_html_e( 'Direct Email Support', 'sny-auto-featured-image' ); ?></strong>
												<p><?php esc_html_e( 'Get personal responses within 24 hours from our development team.', 'sny-auto-featured-image' ); ?></p>
											</div>
										</div>
										<div class="wpafi-pro-support-feature">
											<span class="dashicons dashicons-admin-tools"></span>
											<div>
												<strong><?php esc_html_e( 'Setup Assistance', 'sny-auto-featured-image' ); ?></strong>
												<p><?php esc_html_e( 'We\'ll help configure the plugin for your specific use case.', 'sny-auto-featured-image' ); ?></p>
											</div>
										</div>
										<div class="wpafi-pro-support-feature">
											<span class="dashicons dashicons-update"></span>
											<div>
												<strong><?php esc_html_e( 'Priority Bug Fixes', 'sny-auto-featured-image' ); ?></strong>
												<p><?php esc_html_e( 'Your issues are prioritized in our development queue.', 'sny-auto-featured-image' ); ?></p>
											</div>
										</div>
										<div class="wpafi-pro-support-feature">
											<span class="dashicons dashicons-video-alt3"></span>
											<div>
												<strong><?php esc_html_e( 'Video Tutorials', 'sny-auto-featured-image' ); ?></strong>
												<p><?php esc_html_e( 'Access exclusive step-by-step video guides.', 'sny-auto-featured-image' ); ?></p>
											</div>
										</div>
									</div>
									<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="button button-primary button-hero wpafi-unlock-btn">
										<span class="dashicons dashicons-unlock"></span>
										<span><?php esc_html_e( 'View Pro Roadmap', 'sny-auto-featured-image' ); ?></span>
									</a>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<!-- Support -->
						<div class="wpafi-help-section">
							<h2>
								<span class="dashicons dashicons-sos"></span>
								<?php esc_html_e( 'Need More Help?', 'sny-auto-featured-image' ); ?>
							</h2>
							<div class="wpafi-help-card wpafi-help-support">
								<p class="wpafi-help-support-intro">
									<?php esc_html_e( 'Visit our website for detailed tutorials, video guides, and the latest updates.', 'sny-auto-featured-image' ); ?>
								</p>
								<a href="https://sanny.dev/plugins/auto-featured-image/?utm_source=plugin&amp;utm_medium=help-tab&amp;utm_campaign=docs" target="_blank" class="button button-primary button-hero wpafi-docs-btn">
									<span class="dashicons dashicons-book"></span>
									<span><?php esc_html_e( 'View Full Documentation', 'sny-auto-featured-image' ); ?></span>
								</a>
								<div class="wpafi-support-options">
									<a href="https://wordpress.org/support/plugin/wp-auto-featured-image/" target="_blank" class="wpafi-support-link">
										<span class="dashicons dashicons-format-chat"></span>
										<span><?php esc_html_e( 'Support Forum', 'sny-auto-featured-image' ); ?></span>
									</a>
									<a href="https://wordpress.org/plugins/wp-auto-featured-image/#reviews" target="_blank" class="wpafi-support-link">
										<span class="dashicons dashicons-star-filled"></span>
										<span><?php esc_html_e( 'Leave a Review', 'sny-auto-featured-image' ); ?></span>
									</a>
									<a href="https://github.com/sannysri/WordPress-Auto-Featured-Image/issues" target="_blank" class="wpafi-support-link">
										<span class="dashicons dashicons-flag"></span>
										<span><?php esc_html_e( 'Report a Bug', 'sny-auto-featured-image' ); ?></span>
									</a>
								</div>
							</div>
						</div>

					</div>
				</div><!-- End Tab: Help -->

			</form>
		</div>

		<!-- Sidebar -->
		<aside class="wpafi-sidebar">
			<?php if ( $show_pro_teasers ) : ?>
			<div class="wpafi-card wpafi-pro-card">
				<div class="wpafi-card-header">
					<h2>
						<span class="dashicons dashicons-star-filled"></span>
						<?php esc_html_e( 'Pro Coming Soon', 'sny-auto-featured-image' ); ?>
					</h2>
				</div>
				<div class="wpafi-card-body">
					<ul class="wpafi-pro-features">
						<li>
							<span class="dashicons dashicons-undo"></span>
							<?php esc_html_e( 'Full Dry Run & 30-Day Undo Log', 'sny-auto-featured-image' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Unlimited Rules', 'sny-auto-featured-image' ); ?>
						</li>
						<li>
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e( 'Unlimited Bulk Processing', 'sny-auto-featured-image' ); ?>
						</li>
					</ul>
					<a href="<?php echo esc_url( $upgrade_url ); ?>"
						class="button button-primary wpafi-pro-cta" target="_blank">
						<?php esc_html_e( 'Pro Features Coming Soon', 'sny-auto-featured-image' ); ?>
					</a>
				</div>
			</div>
			<?php endif; ?>

			<div class="wpafi-card">
				<div class="wpafi-card-header">
					<h2>
						<span class="dashicons dashicons-sos"></span>
						<?php esc_html_e( 'Need Help?', 'sny-auto-featured-image' ); ?>
					</h2>
				</div>
				<div class="wpafi-card-body">
					<p><?php esc_html_e( 'Check documentation or contact support:', 'sny-auto-featured-image' ); ?></p>
					<a href="https://wordpress.org/support/plugin/wp-auto-featured-image/" class="wpafi-link"
						target="_blank">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'Support Forum', 'sny-auto-featured-image' ); ?>
					</a>
					<a href="https://wordpress.org/plugins/wp-auto-featured-image/#reviews" class="wpafi-link"
						target="_blank">
						<span class="dashicons dashicons-star-empty"></span>
						<?php esc_html_e( 'Leave a Review', 'sny-auto-featured-image' ); ?>
					</a>
				</div>
			</div>
		</aside>
	</div>
</div>

<!-- Custom Warning Modal -->
<div id="wpafi-warning-modal" class="wpafi-modal" style="display: none;">
	<div class="wpafi-modal-overlay"></div>
	<div class="wpafi-modal-content">
		<div class="wpafi-modal-header">
			<span class="dashicons dashicons-warning"></span>
			<h3><?php esc_html_e( 'Warning: Irreversible Action', 'sny-auto-featured-image' ); ?></h3>
		</div>
		<div class="wpafi-modal-body">
			<p><strong><?php esc_html_e( 'This action cannot be undone!', 'sny-auto-featured-image' ); ?></strong></p>
			<p><?php esc_html_e( 'This will modify featured images on your existing posts. Please ensure you have:', 'sny-auto-featured-image' ); ?>
			</p>
			<ul>
				<li><?php esc_html_e( 'Created a full database backup', 'sny-auto-featured-image' ); ?></li>
				<li><?php esc_html_e( 'Reviewed your rule settings', 'sny-auto-featured-image' ); ?></li>
				<li><?php esc_html_e( 'Understood that YOU are responsible for any data changes', 'sny-auto-featured-image' ); ?>
				</li>
			</ul>
		</div>
		<div class="wpafi-modal-footer">
			<button type="button" id="wpafi-modal-cancel"
				class="button button-secondary"><?php esc_html_e( 'Cancel', 'sny-auto-featured-image' ); ?></button>
			<button type="button" id="wpafi-modal-confirm"
				class="button wpafi-btn-danger"><?php esc_html_e( 'I Understand, Proceed', 'sny-auto-featured-image' ); ?></button>
		</div>
	</div>
</div>

<!-- Template for new rules (cloned by JS) -->
<script type="text/template" id="wpafi-rule-template">
	<?php wpafi_render_image_rule_card( '{{INDEX}}', array(), $categories, $tags, $post_types, $post_statuses, null ); ?>
</script>

<?php
/**
 * Render a complete image rule card with image selector and conditions.
 *
 * @param int|string $index              Rule index.
 * @param array      $rule               Rule data.
 * @param array      $categories         Available categories.
 * @param array      $tags               Available tags.
 * @param array      $post_types         Available post types.
 * @param array      $post_statuses      Available post statuses.
 * @param int|null   $first_active_index Index of the first active rule (to expand).
 */
function wpafi_render_image_rule_card( $index, $rule, $categories, $tags, $post_types, $post_statuses, $first_active_index = null ) {
	// Determine if Pro teasers should show (recalculate since we're in a separate function).
	$has_pro          = function_exists( 'wpafi_has_pro_features' ) && wpafi_has_pro_features();
	$show_pro_teasers = ! $has_pro;

	$image_id          = isset( $rule['image_id'] ) ? $rule['image_id'] : 0;
	$image_source      = isset( $rule['image_source'] ) ? $rule['image_source'] : 'media';
	$external_url      = isset( $rule['external_url'] ) ? $rule['external_url'] : '';
	$condition_type    = isset( $rule['condition_type'] ) ? $rule['condition_type'] : '';
	$condition_value   = isset( $rule['condition_value'] ) ? $rule['condition_value'] : '';
	$selected_posts    = isset( $rule['post_types'] ) ? (array) $rule['post_types'] : array();
	$selected_cats     = isset( $rule['categories'] ) ? (array) $rule['categories'] : array();
	$selected_tags     = isset( $rule['tags'] ) ? (array) $rule['tags'] : array();
	$selected_status   = isset( $rule['post_statuses'] ) ? (array) $rule['post_statuses'] : array();
	$auto_detect       = isset( $rule['auto_detect'] ) ? $rule['auto_detect'] : 0;
	$overwrite         = isset( $rule['overwrite'] ) ? $rule['overwrite'] : 0;
	$include_video     = isset( $rule['include_video'] ) ? $rule['include_video'] : 1;
	$sideload_external = isset( $rule['sideload_external'] ) ? $rule['sideload_external'] : 1;
	$rule_number       = is_numeric( $index ) ? intval( $index ) + 1 : '?';
	$rule_name         = isset( $rule['name'] ) ? $rule['name'] : '';
	$is_enabled        = isset( $rule['enabled'] ) ? (bool) $rule['enabled'] : true;

	// Collapse logic: expand only the first active rule, collapse all others.
	// - Disabled rules are always collapsed.
	// - If this is the first active rule (matches $first_active_index), expand it.
	// - All other active rules are collapsed.
	if ( ! $is_enabled ) {
		$is_collapsed = true;
	} elseif ( null !== $first_active_index && $index === $first_active_index ) {
		$is_collapsed = false; // First active rule is expanded.
	} else {
		$is_collapsed = true; // Other active rules are collapsed.
	}

	$card_classes = array( 'wpafi-rule-card' );
	if ( ! $is_enabled ) {
		$card_classes[] = 'is-disabled';
	}
	if ( $is_collapsed ) {
		$card_classes[] = 'is-collapsed';
	}
	?>
<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
	<input type="hidden" name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][collapsed]"
		class="wpafi-rule-collapsed-state" value="<?php echo $is_collapsed ? '1' : '0'; ?>" />

	<div class="wpafi-rule-card-header">
		<div class="wpafi-rule-header-left">
			<button type="button" class="wpafi-rule-collapse-btn"
				title="<?php esc_attr_e( 'Toggle Collapse', 'sny-auto-featured-image' ); ?>">
				<span class="dashicons dashicons-arrow-down-alt2"></span>
			</button>
			<span class="wpafi-rule-number">
				<span class="dashicons dashicons-format-image"></span>
				<?php
				/* translators: %s is rule number */
				printf( esc_html__( 'Image Rule #%s', 'sny-auto-featured-image' ), '<span class="wpafi-rule-index">' . esc_html( $rule_number ) . '</span>' );
				?>
			</span>
			<input type="text" name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][name]"
				class="wpafi-rule-name" value="<?php echo esc_attr( $rule_name ); ?>"
				placeholder="<?php esc_attr_e( 'Rule name (optional)', 'sny-auto-featured-image' ); ?>" />
		</div>
		<div class="wpafi-rule-header-actions">
			<label class="wpafi-toggle" title="<?php esc_attr_e( 'Enable/Disable Rule', 'sny-auto-featured-image' ); ?>">
				<input type="checkbox" name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][enabled]"
					class="wpafi-rule-enabled-toggle" value="1" <?php checked( $is_enabled ); ?> />
				<span class="wpafi-toggle-slider"></span>
			</label>
			<?php if ( 0 !== $index && '0' !== $index ) : ?>
			<button type="button" class="wpafi-remove-rule"
				title="<?php esc_attr_e( 'Remove Rule', 'sny-auto-featured-image' ); ?>">
				<span class="dashicons dashicons-trash"></span>
			</button>
			<?php endif; ?>
		</div>
	</div>
	<div class="wpafi-rule-card-body">
		<!-- Image Source Selection -->
		<div class="wpafi-image-source-section">
			<h4><?php esc_html_e( 'Image Source', 'sny-auto-featured-image' ); ?></h4>
			<div class="wpafi-image-source-options">
				<label class="wpafi-radio-option <?php echo 'media' === $image_source ? 'active' : ''; ?>">
					<input type="radio"
						name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][image_source]" value="media"
						<?php checked( $image_source, 'media' ); ?> />
					<span class="dashicons dashicons-format-image"></span>
					<span><?php esc_html_e( 'Media Library', 'sny-auto-featured-image' ); ?></span>
				</label>
				<label class="wpafi-radio-option <?php echo 'first_image' === $image_source ? 'active' : ''; ?>">
					<input type="radio"
						name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][image_source]"
						value="first_image" <?php checked( $image_source, 'first_image' ); ?> />
					<span class="dashicons dashicons-images-alt2"></span>
					<span><?php esc_html_e( 'First Image/Video', 'sny-auto-featured-image' ); ?></span>
				</label>
				<label class="wpafi-radio-option <?php echo 'external' === $image_source ? 'active' : ''; ?>">
					<input type="radio"
						name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][image_source]"
						value="external" <?php checked( $image_source, 'external' ); ?> />
					<span class="dashicons dashicons-admin-links"></span>
					<span><?php esc_html_e( 'External URL', 'sny-auto-featured-image' ); ?></span>
				</label>
			</div>
		</div>

		<!-- Media Library Panel -->
		<div class="wpafi-source-panel wpafi-source-media"
			<?php echo 'media' !== $image_source ? 'style="display:none;"' : ''; ?>>
			<div class="wpafi-rule-image-box">
				<input type="hidden" name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][image_id]"
					class="wpafi-rule-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
				<div class="wpafi-rule-image-preview">
					<?php if ( $image_id ) : ?>
						<?php echo wp_get_attachment_image( $image_id, 'medium' ); ?>
					<?php else : ?>
					<div class="wpafi-rule-no-image">
						<span class="dashicons dashicons-plus-alt2"></span>
						<span><?php esc_html_e( 'Select Image', 'sny-auto-featured-image' ); ?></span>
					</div>
					<?php endif; ?>
				</div>
				<div class="wpafi-rule-image-actions">
					<button type="button" class="button button-primary wpafi-select-image">
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Choose Image', 'sny-auto-featured-image' ); ?>
					</button>
					<button type="button" class="button wpafi-remove-image"
						<?php echo ! $image_id ? 'style="display:none;"' : ''; ?>>
						<span class="dashicons dashicons-trash"></span>
					</button>
				</div>
			</div>
		</div>

		<!-- First Image/Video Panel -->
		<div class="wpafi-source-panel wpafi-source-first-image"
			<?php echo 'first_image' !== $image_source ? 'style="display:none;"' : ''; ?>>
			<div class="wpafi-source-info">
				<span class="dashicons dashicons-info"></span>
				<div>
					<p><?php esc_html_e( 'Uses the first image found in post content as the featured image.', 'sny-auto-featured-image' ); ?>
					</p>
					<div class="wpafi-source-options">
						<label class="wpafi-checkbox">
							<input type="checkbox"
								name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][include_video]"
								value="1" <?php checked( $include_video ); ?> />
							<span><?php esc_html_e( 'Include YouTube/Vimeo thumbnails', 'sny-auto-featured-image' ); ?></span>
						</label>
						<label class="wpafi-checkbox">
							<input type="checkbox"
								name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][sideload_external]"
								value="1" <?php checked( $sideload_external ); ?> />
							<span><?php esc_html_e( 'Download external images to Media Library', 'sny-auto-featured-image' ); ?></span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<!-- External URL Panel -->
		<div class="wpafi-source-panel wpafi-source-external"
			<?php echo 'external' !== $image_source ? 'style="display:none;"' : ''; ?>>
			<div class="wpafi-external-url-input">
				<label><?php esc_html_e( 'Image URL:', 'sny-auto-featured-image' ); ?></label>
				<input type="url" name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][external_url]"
					value="<?php echo esc_url( $external_url ); ?>" placeholder="https://example.com/image.jpg"
					class="regular-text" />
				<p class="description">
					<?php esc_html_e( 'The image will be downloaded and saved to your Media Library.', 'sny-auto-featured-image' ); ?>
				</p>
			</div>
		</div>

		<!-- Conditions -->
		<!-- Conditions -->
		<div class="wpafi-rule-conditions">
			<h4><?php esc_html_e( 'Apply to:', 'sny-auto-featured-image' ); ?></h4>

			<div class="wpafi-conditions-grid">
				<div class="wpafi-condition-row">
					<label><?php esc_html_e( 'Post Types', 'sny-auto-featured-image' ); ?></label>
					<select name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][post_types][]"
						class="wpafi-select2" multiple="multiple">
						<?php foreach ( $post_types as $pt ) : ?>
							<?php if ( 'attachment' !== $pt->name ) : ?>
						<option value="<?php echo esc_attr( $pt->name ); ?>"
								<?php echo in_array( $pt->name, $selected_posts, true ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $pt->label ); ?></option>
						<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="wpafi-condition-row">
					<label><?php esc_html_e( 'Categories', 'sny-auto-featured-image' ); ?></label>
					<select name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][categories][]"
						class="wpafi-select2" multiple="multiple">
						<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat->slug ); ?>"
							<?php echo in_array( $cat->slug, $selected_cats, true ) ? 'selected' : ''; ?>>
							<?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="wpafi-condition-row">
					<label><?php esc_html_e( 'Tags', 'sny-auto-featured-image' ); ?></label>
					<select name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][tags][]"
						class="wpafi-select2" multiple="multiple">
						<?php foreach ( $tags as $tag ) : ?>
						<option value="<?php echo esc_attr( $tag->slug ); ?>"
							<?php echo in_array( $tag->slug, $selected_tags, true ) ? 'selected' : ''; ?>>
							<?php echo esc_html( $tag->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="wpafi-condition-row">
					<label><?php esc_html_e( 'Post Status', 'sny-auto-featured-image' ); ?></label>
					<select name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][post_statuses][]"
						class="wpafi-select2" multiple="multiple">
						<?php foreach ( $post_statuses as $status_key => $status_label ) : ?>
						<option value="<?php echo esc_attr( $status_key ); ?>"
							<?php echo in_array( $status_key, $selected_status, true ) ? 'selected' : ''; ?>>
							<?php echo esc_html( $status_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<p class="wpafi-condition-hint">
				<?php esc_html_e( 'Leave empty to apply to all posts. Multiple selections use AND logic.', 'sny-auto-featured-image' ); ?>
			</p>

			<!-- Behavior Options -->
			<div class="wpafi-rule-behavior">
				<label class="wpafi-checkbox">
					<input type="checkbox"
						name="wpafi_options[wpafi_rules][<?php echo esc_attr( $index ); ?>][overwrite]" value="1"
						<?php checked( $overwrite ); ?> />
					<span><?php esc_html_e( 'Overwrite existing featured images', 'sny-auto-featured-image' ); ?></span>
				</label>
				<p class="wpafi-behavior-hint">
					<?php esc_html_e( 'When enabled, this rule will replace any existing featured image. Leave unchecked to only set images on posts without one.', 'sny-auto-featured-image' ); ?>
				</p>
			</div>
		</div>
	</div>
</div>
	<?php
}