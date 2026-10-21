<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Post_Types {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'init', array( __CLASS__, 'register_roles' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_supervisor_meta_box' ) );
		add_action( 'save_post_cqt_site', array( __CLASS__, 'save_supervisor_meta' ) );
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

	public static function add_supervisor_meta_box() {
		add_meta_box(
			'cqt_supervisor_box',
			'Assigned Supervisor',
			array( __CLASS__, 'render_supervisor_meta_box' ),
			'cqt_site',
			'side',
			'high'
		);
	}

	public static function render_supervisor_meta_box( $post ) {
		wp_nonce_field( 'cqt_save_supervisor', 'cqt_supervisor_nonce' );
		$saved_supervisor = get_post_meta( $post->ID, '_cqt_assigned_supervisor', true );

		$supervisors = get_users( array( 'role' => 'cqt_supervisor' ) );

		echo '<select name="cqt_assigned_supervisor" style="width:100%;">';
		echo '<option value="">— Select Supervisor —</option>';
		foreach ( $supervisors as $supervisor ) {
			echo '<option value="' . esc_attr( $supervisor->ID ) . '" ' . selected( $saved_supervisor, $supervisor->ID, false ) . '>' . esc_html( $supervisor->display_name ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">This supervisor will see this site in their portal.</p>';
	}

	public static function save_supervisor_meta( $post_id ) {
		if ( ! isset( $_POST['cqt_supervisor_nonce'] ) || ! wp_verify_nonce( $_POST['cqt_supervisor_nonce'], 'cqt_save_supervisor' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['cqt_assigned_supervisor'] ) ) {
			update_post_meta( $post_id, '_cqt_assigned_supervisor', absint( wp_unslash( $_POST['cqt_assigned_supervisor'] ) ) );
		}
	}
}