<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Activator {

	public static function activate() {
		self::create_pages();
	}

	private static function create_pages() {
		$pages = array(
			'supervisor-portal' => array(
				'title'   => 'Supervisor Portal',
				'content' => '[cqt_supervisor_portal]',
			),
			'inspection-reports' => array(
				'title'   => 'Inspection Reports',
				'content' => '[cqt_inspection_reports]',
			),
		);

		foreach ( $pages as $slug => $page_data ) {
			$page_exists = get_page_by_path( $slug );

			if ( ! $page_exists ) {
				wp_insert_post( array(
					'post_title'   => $page_data['title'],
					'post_content' => $page_data['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_name'    => $slug,
				) );
			}
		}
	}
}