<?php
/**
 * Plugin Name:       Cleaning Quality Tracker
 * Plugin URI:        https://github.com/bird-ie/cleaning-quality-tracker
 * Description:       A quality assurance tool for commercial cleaning companies.
 * Version:           1.0.0
 * Author:            Sohaib Hassan
 * Author URI:        https://github.com/bird-ie
 * License:           GPL-2.0+
 * Text Domain:       cleaning-quality-tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CQT_VERSION', '1.0.0' );
define( 'CQT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CQT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CQT_PLUGIN_DIR . 'includes/class-cqt-post-types.php';

function cqt_run_plugin() {
	CQT_Post_Types::init();
}
add_action( 'plugins_loaded', 'cqt_run_plugin' );