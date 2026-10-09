<?php
/**
 * Review Notice for WP Auto Featured Image
 *
 * @package WP_Auto_Featured_Image
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**

 * Class WPAFI_Review_Notice
 *
 * Handles displaying a value-triggered, two-step review request notice.
 */
class WPAFI_Review_Notice {

	/**
	 * Option name for storing plugin install time.
	 *
	 * @var string
	 */
	private $install_time_option = 'wpafi_install_time';

	/**
	 * Option name for storing dismissed status.
	 *
	 * @var string
	 */
	private $dismissed_option = 'wpafi_review_dismissed';

	/**
	 * Option name for storing remind later timestamp.
	 *
	 * @var string
	 */
	private $remind_later_option = 'wpafi_review_remind_later';

	/**
	 * Option name for total thumbnails assigned by the plugin.
	 *
	 * @var string
	 */
	private static $assigned_count_option = 'wpafi_assigned_count';

	/**
	 * Number of days to wait before showing notice (time-based fallback).
	 *
	 * @var int
	 */
	private $initial_delay_days = 7;

	/**
	 * Number of days to wait after "remind later" is clicked.
	 *
	 * @var int
	 */
	private $remind_later_days = 14;

	/**
	 * Threshold of assigned thumbnails to trigger the review prompt immediately.
	 *
	 * @var int
	 */
	private $assigned_threshold = 5;

	/**
	 * Constructor - sets up hooks.
	 */
	public function __construct() {
		$this->maybe_set_install_time();
		add_action( 'admin_notices', array( $this, 'maybe_display_notice' ) );
		add_action( 'admin_init', array( $this, 'handle_notice_actions' ) );
		add_action( 'wp_ajax_wpafi_review_dismiss', array( $this, 'ajax_dismiss' ) );
		add_action( 'wp_ajax_wpafi_review_snooze', array( $this, 'ajax_snooze' ) );
	}

	/**
	 * Set install time if not already set.
	 *
	 * @return void
	 */
	private function maybe_set_install_time() {
		if ( ! get_option( $this->install_time_option ) ) {
			update_option( $this->install_time_option, time(), false );
		}
	}

	/**
	 * Increment the count of thumbnails assigned by the plugin.
	 *
	 * @param int $count Number of images assigned.
	 * @return void
	 */
	public static function increment_assigned_count( $count = 1 ) {
		$current = (int) get_option( self::$assigned_count_option, 0 );
		update_option( self::$assigned_count_option, $current + max( 1, (int) $count ), false );
	}

	/**
	 * Get total count of thumbnails assigned.
	 *
	 * @return int Total thumbnails assigned.
	 */
	public static function get_assigned_count() {
		return (int) get_option( self::$assigned_count_option, 0 );
	}

	/**
	 * Maybe display the review notice.
	 *
	 * @return void
	 */
	public function maybe_display_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_option( $this->dismissed_option ) ) {
			return;
		}

		if ( ! $this->should_show_notice() ) {
			return;
		}

		$this->display_notice();
	}

	/**
	 * Check if current admin screen is allowed for showing the notice.
	 *
	 * @return bool True if screen is allowed.
	 */
	public function is_allowed_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return true;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return true;
		}

		// Never show on post editing screens to prevent distracting authors.
		if ( in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			return false;
		}

		// Allowed on: Plugin settings, Dashboard, and Posts list.
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'wp_auto_featured_image', 'wpafi-settings' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		$allowed_bases = array( 'settings_page_wp_auto_featured_image', 'toplevel_page_wpafi-settings', 'dashboard', 'edit' );
		return in_array( $screen->base, $allowed_bases, true );
	}

	/**
	 * Check if the notice should be shown based on value delivered or timing.
	 *
	 * @return bool True if notice should be shown.
	 */
	public function should_show_notice() {
		if ( ! $this->is_allowed_screen() ) {
			return false;
		}

		$remind_later = get_option( $this->remind_later_option );
		$current_time = time();

		if ( $remind_later ) {
			$remind_delay = $this->remind_later_days * DAY_IN_SECONDS;
			if ( ( $current_time - (int) $remind_later ) < $remind_delay ) {
				return false;
			}
		}

		// Value Trigger: User has successfully assigned >= 5 thumbnails.
		$assigned = self::get_assigned_count();
		if ( $assigned >= $this->assigned_threshold ) {
			return true;
		}

		// Time Fallback Trigger: Active for >= 7 days with at least 1 rule or default setting.
		$install_time = get_option( $this->install_time_option );
		if ( $install_time ) {
			$initial_delay = $this->initial_delay_days * DAY_IN_SECONDS;
			if ( ( $current_time - (int) $install_time ) >= $initial_delay ) {
				$options = get_option( 'wpafi_options', array() );
				if ( ! empty( $options['wpafi_rules'] ) || ! empty( $options['wpafi_default_thumb_id'] ) || ! empty( $options['wpafi_auto_detect'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Display the two-step review notice HTML.
	 *
	 * @return void
	 */
	public function display_notice() {
		$this->notice_styles();
		$assigned_count = self::get_assigned_count();
		$review_url     = 'https://wordpress.org/support/plugin/wp-auto-featured-image/reviews/?filter=5#new-post';
		$support_url    = 'https://wordpress.org/support/plugin/wp-auto-featured-image/#new-topic-0';
		$dismiss_url    = wp_nonce_url( add_query_arg( 'wpafi_review_action', 'dismiss' ), 'wpafi_review_nonce' );
		$later_url      = wp_nonce_url( add_query_arg( 'wpafi_review_action', 'later' ), 'wpafi_review_nonce' );
		$ajax_nonce     = wp_create_nonce( 'wpafi_review_nonce' );
		?>
		<div class="notice notice-info wpafi-review-notice is-dismissible" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>">
			<div class="wpafi-review-content">
				<div class="wpafi-review-icon">
					<span class="dashicons dashicons-star-filled"></span>
				</div>

				<!-- Step 1: Sentiment Check -->
				<div class="wpafi-review-step" id="wpafi-review-step-1">
					<p><strong><?php esc_html_e( 'Are you enjoying SNY Auto Featured Image?', 'sny-auto-featured-image' ); ?></strong></p>
					<p>
						<?php
						if ( $assigned_count > 0 ) {
							printf(
								/* translators: %d: number of images automatically assigned */
								esc_html__( 'SNY Auto Featured Image has automatically assigned %d featured images for your posts! How has your experience been so far?', 'sny-auto-featured-image' ),
								(int) $assigned_count
							);
						} else {
							esc_html_e( 'We hope the plugin is saving you time! How has your experience been so far?', 'sny-auto-featured-image' );
						}
						?>
					</p>
					<p class="wpafi-review-actions">
						<button type="button" class="button button-primary wpafi-step-yes">
							<span class="dashicons dashicons-smiley"></span>
							<?php esc_html_e( 'Yes, I love it! 😊', 'sny-auto-featured-image' ); ?>
						</button>
						<button type="button" class="button button-secondary wpafi-step-no">
							<span class="dashicons dashicons-flag"></span>
							<?php esc_html_e( 'Not really / Having issues 🙁', 'sny-auto-featured-image' ); ?>
						</button>
						<button type="button" class="wpafi-link-btn wpafi-action-snooze" style="margin-left: 8px;">
							<?php esc_html_e( 'Remind me later ⏰', 'sny-auto-featured-image' ); ?>
						</button>
					</p>
				</div>

				<!-- Step 2A: Happy User -> Ask for 5-star review -->
				<div class="wpafi-review-step" id="wpafi-review-step-2a" style="display: none;">
					<p><strong><?php esc_html_e( 'Thank you! That makes our day! 🎉', 'sny-auto-featured-image' ); ?></strong></p>
					<p><?php esc_html_e( 'Could you do us a quick favor? A 5-star review on WordPress.org takes less than 30 seconds and helps us keep maintaining and improving the plugin for free.', 'sny-auto-featured-image' ); ?></p>
					<p class="wpafi-review-actions">
						<a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary wpafi-action-reviewed" target="_blank" rel="noopener">
							<span class="dashicons dashicons-star-filled" style="color: #f59e0b;"></span>
							<?php esc_html_e( '⭐⭐⭐⭐⭐ Leave a 5-Star Review', 'sny-auto-featured-image' ); ?>
						</a>
						<button type="button" class="button button-secondary wpafi-action-dismiss">
							<?php esc_html_e( 'I already did', 'sny-auto-featured-image' ); ?>
						</button>
						<button type="button" class="wpafi-link-btn wpafi-action-dismiss" style="margin-left: 6px;">
							<?php esc_html_e( 'No, thanks', 'sny-auto-featured-image' ); ?>
						</button>
					</p>
				</div>

				<!-- Step 2B: Unhappy User -> Offer private support / forum -->
				<div class="wpafi-review-step" id="wpafi-review-step-2b" style="display: none;">
					<p><strong><?php esc_html_e( 'We’re sorry to hear that! 🛠️', 'sny-auto-featured-image' ); ?></strong></p>
					<p><?php esc_html_e( 'We want to make it right for you. Would you mind telling us what isn’t working on the WordPress.org support forum so we can help fix it?', 'sny-auto-featured-image' ); ?></p>
					<p class="wpafi-review-actions">
						<a href="<?php echo esc_url( $support_url ); ?>" class="button button-primary wpafi-action-support" target="_blank" rel="noopener">
							<span class="dashicons dashicons-sos"></span>
							<?php esc_html_e( 'Get Help / Open Support Topic', 'sny-auto-featured-image' ); ?>
						</a>
						<button type="button" class="button button-secondary wpafi-action-dismiss">
							<?php esc_html_e( 'Dismiss', 'sny-auto-featured-image' ); ?>
						</button>
					</p>
				</div>

			</div>
		</div>

		<script>
		(function($) {
			function removeNotice($notice) {
				$notice.fadeTo(100, 0, function() {
					$notice.slideUp(150, function() {
						$notice.remove();
					});
				});
			}

			function dismissNotice($notice) {
				var nonce = $notice.data('nonce');
				if (typeof ajaxurl !== 'undefined' && nonce) {
					$.post(ajaxurl, {
						action: 'wpafi_review_dismiss',
						nonce: nonce
					});
				}
				removeNotice($notice);
			}

			function snoozeNotice($notice) {
				var nonce = $notice.data('nonce');
				if (typeof ajaxurl !== 'undefined' && nonce) {
					$.post(ajaxurl, {
						action: 'wpafi_review_snooze',
						nonce: nonce
					});
				}
				removeNotice($notice);
			}

			// Step 1: Yes
			$(document).on('click', '.wpafi-step-yes', function(e) {
				e.preventDefault();
				var $notice = $(this).closest('.wpafi-review-notice');
				$notice.find('#wpafi-review-step-1').slideUp(150, function() {
					$notice.find('#wpafi-review-step-2a').slideDown(150);
				});
			});

			// Step 1: No / Issues
			$(document).on('click', '.wpafi-step-no', function(e) {
				e.preventDefault();
				var $notice = $(this).closest('.wpafi-review-notice');
				$notice.find('#wpafi-review-step-1').slideUp(150, function() {
					$notice.find('#wpafi-review-step-2b').slideDown(150);
				});
			});

			// Dismiss buttons (I already did, No thanks, Dismiss)
			$(document).on('click', '.wpafi-review-notice .wpafi-action-dismiss', function(e) {
				e.preventDefault();
				dismissNotice($(this).closest('.wpafi-review-notice'));
			});

			// Reviewed button (opens review tab & dismisses notice)
			$(document).on('click', '.wpafi-review-notice .wpafi-action-reviewed', function() {
				dismissNotice($(this).closest('.wpafi-review-notice'));
			});

			// Support button (opens topic tab & dismisses notice)
			$(document).on('click', '.wpafi-review-notice .wpafi-action-support', function() {
				dismissNotice($(this).closest('.wpafi-review-notice'));
			});

			// Snooze button
			$(document).on('click', '.wpafi-review-notice .wpafi-action-snooze', function(e) {
				e.preventDefault();
				snoozeNotice($(this).closest('.wpafi-review-notice'));
			});

			// Standard WP notice close (x) button
			$(document).on('click', '.wpafi-review-notice .notice-dismiss', function() {
				var $notice = $(this).closest('.wpafi-review-notice');
				var nonce = $notice.data('nonce');
				if (typeof ajaxurl !== 'undefined' && nonce) {
					$.post(ajaxurl, {
						action: 'wpafi_review_dismiss',
						nonce: nonce
					});
				}
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX handler to dismiss notice permanently.
	 *
	 * @return void
	 */
	public function ajax_dismiss() {
		check_ajax_referer( 'wpafi_review_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}
		update_option( $this->dismissed_option, true, false );
		wp_send_json_success();
	}

	/**
	 * AJAX handler to snooze notice for 14 days.
	 *
	 * @return void
	 */
	public function ajax_snooze() {
		check_ajax_referer( 'wpafi_review_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}
		update_option( $this->remind_later_option, time(), false );
		wp_send_json_success();
	}

	/**
	 * Handle fallback notice action requests (dismiss or remind later via GET).
	 *
	 * @return void
	 */
	public function handle_notice_actions() {
		if ( ! isset( $_GET['wpafi_review_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpafi_review_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = sanitize_text_field( wp_unslash( $_GET['wpafi_review_action'] ) );

		if ( 'dismiss' === $action ) {
			update_option( $this->dismissed_option, true, false );
		} elseif ( 'later' === $action ) {
			update_option( $this->remind_later_option, time(), false );
		}

		wp_safe_redirect( remove_query_arg( array( 'wpafi_review_action', '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Output inline styles for the review notice.
	 *
	 * @return void
	 */
	public function notice_styles() {
		?>
		<style>
			.wpafi-review-notice { box-sizing: border-box; overflow: hidden; padding: 16px 14px; border-left: 4px solid #0284c7; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
			.wpafi-review-content { display: flex; align-items: flex-start; gap: 16px; }
			.wpafi-review-icon { flex-shrink: 0; width: 44px; height: 44px; background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2); }
			.wpafi-review-icon .dashicons { color: #fff; font-size: 24px; width: 24px; height: 24px; line-height: 24px; }
			.wpafi-review-step p { margin: 0 0 10px 0; font-size: 14px; line-height: 1.5; }
			.wpafi-review-step p:last-child { margin-bottom: 0; }
			.wpafi-review-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px !important; }
			.wpafi-link-btn { background: none !important; border: none !important; padding: 0 !important; margin: 0 !important; color: #64748b !important; cursor: pointer !important; text-decoration: underline !important; font-size: 13px !important; line-height: inherit !important; vertical-align: middle; }
			.wpafi-link-btn:hover, .wpafi-link-btn:focus { color: #0f172a !important; }
			.wpafi-review-actions .button { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; height: auto; line-height: 2; font-weight: 500; }
			.wpafi-review-actions .button .dashicons { font-size: 16px; width: 16px; height: 16px; line-height: 16px; }
			@media screen and (max-width: 782px) {
				.wpafi-review-content { flex-direction: column; }
				.wpafi-review-actions { flex-direction: column; align-items: flex-start; }
			}
		</style>
		<?php
	}
}
