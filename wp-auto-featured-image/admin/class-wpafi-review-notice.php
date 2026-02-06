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
 * Handles displaying a review request notice after 7 days of use.
 */
class WPAFI_Review_Notice {

    private $install_time_option = 'wpafi_install_time';
    private $dismissed_option = 'wpafi_review_dismissed';
    private $remind_later_option = 'wpafi_review_remind_later';
    private $initial_delay_days = 7;
    private $remind_later_days = 3;

    public function __construct() {
        $this->maybe_set_install_time();
        add_action( 'admin_notices', array( $this, 'maybe_display_notice' ) );
        add_action( 'admin_init', array( $this, 'handle_notice_actions' ) );
        add_action( 'admin_head', array( $this, 'notice_styles' ) );
    }

    private function maybe_set_install_time() {
        if ( ! get_option( $this->install_time_option ) ) {
            update_option( $this->install_time_option, time() );
        }
    }

    public function maybe_display_notice() {
        if ( get_option( $this->dismissed_option ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( ! $this->should_show_notice() ) {
            return;
        }
        $this->display_notice();
    }

    private function should_show_notice() {
        $install_time = get_option( $this->install_time_option );
        $remind_later = get_option( $this->remind_later_option );
        $current_time = time();

        if ( $remind_later ) {
            $remind_delay = $this->remind_later_days * DAY_IN_SECONDS;
            return ( $current_time - $remind_later ) >= $remind_delay;
        }

        if ( $install_time ) {
            $initial_delay = $this->initial_delay_days * DAY_IN_SECONDS;
            return ( $current_time - $install_time ) >= $initial_delay;
        }

        return false;
    }

    private function display_notice() {
        $review_url  = 'https://wordpress.org/support/plugin/wp-auto-featured-image/reviews/?filter=5#new-post';
        $dismiss_url = wp_nonce_url( add_query_arg( 'wpafi_review_action', 'dismiss' ), 'wpafi_review_nonce' );
        $later_url   = wp_nonce_url( add_query_arg( 'wpafi_review_action', 'later' ), 'wpafi_review_nonce' );
        ?>
        <div class="notice notice-info wpafi-review-notice is-dismissible">
            <div class="wpafi-review-content">
                <div class="wpafi-review-icon">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="wpafi-review-text">
                    <p><strong><?php esc_html_e( 'Enjoying SNY Auto Featured Image?', 'sny-auto-featured-image' ); ?></strong></p>
                    <p><?php esc_html_e( 'We hope you are finding the plugin useful! If you have a moment, we would really appreciate a quick review.', 'sny-auto-featured-image' ); ?></p>
                    <p class="wpafi-review-actions">
                        <a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-thumbs-up"></span>
                            <?php esc_html_e( 'Leave a Review', 'sny-auto-featured-image' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $later_url ); ?>" class="button button-secondary">
                            <span class="dashicons dashicons-clock"></span>
                            <?php esc_html_e( 'Maybe Later', 'sny-auto-featured-image' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $dismiss_url ); ?>" class="wpafi-review-dismiss-link">
                            <?php esc_html_e( 'Already reviewed', 'sny-auto-featured-image' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    public function handle_notice_actions() {
        if ( ! isset( $_GET['wpafi_review_action'] ) ) {
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
            update_option( $this->dismissed_option, true );
        } elseif ( 'later' === $action ) {
            update_option( $this->remind_later_option, time() );
        }

        wp_safe_redirect( remove_query_arg( array( 'wpafi_review_action', '_wpnonce' ) ) );
        exit;
    }

    public function notice_styles() {
        ?>
        <style>
            .wpafi-review-notice { padding: 15px 12px; border-left-color: #0073aa; }
            .wpafi-review-content { display: flex; align-items: flex-start; gap: 15px; }
            .wpafi-review-icon { flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
            .wpafi-review-icon .dashicons { color: #fff; font-size: 28px; width: 28px; height: 28px; }
            .wpafi-review-text p { margin: 0 0 10px 0; }
            .wpafi-review-text p:last-child { margin-bottom: 0; }
            .wpafi-review-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
            .wpafi-review-actions .button { display: inline-flex; align-items: center; gap: 5px; }
            .wpafi-review-actions .button .dashicons { font-size: 16px; width: 16px; height: 16px; line-height: 1; }
            .wpafi-review-dismiss-link { color: #666; text-decoration: none; font-size: 13px; }
            .wpafi-review-dismiss-link:hover { color: #0073aa; text-decoration: underline; }
            @media screen and (max-width: 782px) {
                .wpafi-review-content { flex-direction: column; }
                .wpafi-review-actions { flex-direction: column; align-items: flex-start; }
            }
        </style>
        <?php
    }
}
