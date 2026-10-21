<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Inspection_Report {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
	}

	public static function add_meta_box() {
		add_meta_box(
			'cqt_report_box',
			'Inspection Results',
			array( __CLASS__, 'render_meta_box' ),
			'cqt_inspection',
			'normal',
			'high'
		);
	}

	public static function render_meta_box( $post ) {
		$site_id = get_post_meta( $post->ID, '_cqt_site_id', true );
		$supervisor_id = get_post_meta( $post->ID, '_cqt_supervisor_id', true );
		$results = get_post_meta( $post->ID, '_cqt_results', true );
		$failed_items = get_post_meta( $post->ID, '_cqt_failed_items', true );

		if ( ! is_array( $results ) ) {
			$results = array();
		}

		$site_title = $site_id ? get_the_title( $site_id ) : 'Unknown Site';
		$supervisor_name = $supervisor_id ? get_user_by( 'id', $supervisor_id )->display_name : 'Unknown Supervisor';

		echo '<div style="margin-bottom: 20px;">';
		echo '<p><strong>Site:</strong> ' . esc_html( $site_title ) . '</p>';
		echo '<p><strong>Supervisor:</strong> ' . esc_html( $supervisor_name ) . '</p>';
		echo '<p><strong>Date:</strong> ' . esc_html( get_the_date() ) . '</p>';
		echo '</div>';

		if ( empty( $results ) ) {
			echo '<p>No checklist data found for this inspection.</p>';
			return;
		}

		echo '<h3>Checklist Results</h3>';
		echo '<table class="widefat striped">';
		echo '<thead><tr><th>Item</th><th>Status</th></tr></thead>';
		echo '<tbody>';

		foreach ( $results as $result ) {
			$status = $result['status'];
			$label = $result['label'];
			$color = ( 'pass' === $status ) ? 'green' : 'red';
			$text = ( 'pass' === $status ) ? 'Pass' : 'Fail';

			echo '<tr>';
			echo '<td>' . esc_html( $label ) . '</td>';
			echo '<td style="color: ' . $color . '; font-weight: bold;">' . $text . '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';

		if ( ! empty( $failed_items ) ) {
			echo '<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 20px;">';
			echo '<strong>⚠️ Corrective Actions Required:</strong>';
			echo '<ul style="margin: 10px 0 0 20px;">';
			foreach ( $failed_items as $item ) {
				echo '<li>' . esc_html( $item ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		} else {
			echo '<div style="background: #ecfdf3; border: 1px solid #b7ebc6; color: #166534; padding: 15px; border-radius: 8px; margin-top: 20px;">';
			echo '<strong>✅ All items passed. No corrective actions required.</strong>';
			echo '</div>';
		}
	}
}