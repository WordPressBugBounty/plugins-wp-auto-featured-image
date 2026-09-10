<?php
/**
 * Plugin Name: SNY Auto Featured Image
 * Plugin URI: https://github.com/sannysri/WordPress-Auto-Featured-Image
 * Description: SNY Auto Featured Image automatically sets featured images from content, external URLs, or defaults. Bulk preview & apply for existing posts.
 * Version: 2.1.0
 * Author: Sanny Srivastava
 * Author URI: https://sanny.dev/
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sny-auto-featured-image
 * Domain Path: /languages
 *
 * @package WP_Auto_Featured_Image
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Constants and Version.
define( 'WPAFI_VERSION', '2.1.0' );
define( 'WPAFI_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'WPAFI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Check if Pro plugin is active (regardless of license status).
 *
 * @return bool True if Pro plugin is active.
 */
function wpafi_is_pro_active() {
	return defined( 'SNY_AFI_PRO_ACTIVE' ) && SNY_AFI_PRO_ACTIVE;
}

/**
 * Check if Pro is active AND licensed (full pro features available).
 *
 * @return bool True if Pro features should be used.
 */
function wpafi_has_pro_features() {
	if ( ! wpafi_is_pro_active() ) {
		return false;
	}
	// Pro plugin exposes this function.
	if ( function_exists( 'sny_afi_is_licensed' ) ) {
		return sny_afi_is_licensed();
	}
	return false;
}

// Include necessary files.
require_once plugin_dir_path( __FILE__ ) . 'admin/class-wpafi-admin.php';

// Initialize the admin class.
if ( class_exists( 'WPAFI_Admin' ) ) {
	$wp_auto_featured_image_admin = new WPAFI_Admin();
}
