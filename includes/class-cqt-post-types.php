<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Post_Types {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'init', array( __CLASS__, 'register_roles' ) );
	}

	public static function register_post_types() {
		// Register Inspection Sites
		register_post_type(
			'cqt_site',
			array(
				'labels' => array(
					'name'          => 'Inspection Sites',
					'singular_name' => 'Inspection Site',
					'add_new_item'  => 'Add New Site',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-building',
				'supports'            => array( 'title' ),
			)
		);

		// Register Inspections
		register_post_type(
			'cqt_inspection',
			array(
				'labels' => array(
					'name'          => 'Inspections',
					'singular_name' => 'Inspection',
					'add_new_item'  => 'Add New Inspection',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=cqt_site',
				'supports'            => array( 'title' ),
			)
		);
	}

	public static function register_roles() {
		$supervisor_caps = array(
			'read'                      => true,
			'cqt_perform_inspection'    => true,
			'cqt_view_assigned_sites'   => true,
			'cqt_upload_evidence'       => true,
		);

		$supervisor_role = get_role( 'cqt_supervisor' );
		if ( ! $supervisor_role ) {
			add_role( 'cqt_supervisor', 'Cleaning Supervisor', $supervisor_caps );
		}

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( array_keys( $supervisor_caps ) as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}
}