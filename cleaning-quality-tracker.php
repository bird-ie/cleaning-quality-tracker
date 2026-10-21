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

require_once CQT_PLUGIN_DIR . 'includes/class-cqt-activator.php';
require_once CQT_PLUGIN_DIR . 'includes/class-cqt-post-types.php';
require_once CQT_PLUGIN_DIR . 'includes/class-cqt-checklist-meta.php';
require_once CQT_PLUGIN_DIR . 'frontend/class-cqt-supervisor-portal.php';
require_once CQT_PLUGIN_DIR . 'frontend/class-cqt-reports-page.php';
require_once CQT_PLUGIN_DIR . 'admin/class-cqt-inspection-report.php';

register_activation_hook( __FILE__, array( 'CQT_Activator', 'activate' ) );

function cqt_run_plugin() {
	CQT_Post_Types::init();
	CQT_Checklist_Meta::init();
	CQT_Supervisor_Portal::init();
	CQT_Reports_Page::init();
	CQT_Inspection_Report::init();
}
add_action( 'plugins_loaded', 'cqt_run_plugin' );