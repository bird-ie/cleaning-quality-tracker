<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Supervisor_Portal {

	public static function init() {
		add_shortcode( 'cqt_supervisor_portal', array( __CLASS__, 'render_portal' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_cqt_submit_inspection', array( __CLASS__, 'ajax_submit_inspection' ) );
		add_action( 'wp_ajax_cqt_get_inspection_report', array( __CLASS__, 'ajax_get_inspection_report' ) );
	}

	public static function enqueue_scripts() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cqt_supervisor_portal' ) ) {
			wp_enqueue_style(
				'cqt-frontend-css',
				CQT_PLUGIN_URL . 'assets/frontend.css',
				array(),
				CQT_VERSION
			);

			wp_enqueue_script(
				'cqt-supervisor-js',
				CQT_PLUGIN_URL . 'assets/supervisor-portal.js',
				array( 'jquery' ),
				CQT_VERSION,
				true
			);

			wp_localize_script(
				'cqt-supervisor-js',
				'cqtAjax',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'cqt_supervisor_nonce' ),
				)
			);
		}
	}

	public static function render_portal() {
		if ( ! is_user_logged_in() || ! current_user_can( 'cqt_perform_inspection' ) ) {
			return '<p>You do not have permission to view this page. Please log in as a Supervisor.</p>';
		}

		$current_user = wp_get_current_user();
		$supervisor_id = $current_user->ID;

		$sites_query = new WP_Query( array(
			'post_type'      => 'cqt_site',
			'posts_per_page' => -1,
			'meta_key'       => '_cqt_assigned_supervisor',
			'meta_value'     => $supervisor_id,
		) );

		ob_start();
		?>
		<div class="cqt-supervisor-portal">
			<h1>Supervisor Portal</h1>
			<p>Welcome, <?php echo esc_html( $current_user->display_name ); ?>. Here are your assigned sites.</p>

			<?php if ( ! $sites_query->have_posts() ) : ?>
				<div style="padding: 20px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
					<p>You have no sites assigned at the moment.</p>
				</div>
			<?php else : ?>
				<?php while ( $sites_query->have_posts() ) : $sites_query->the_post();
					$site_id = get_the_ID();
					$checklist = get_post_meta( $site_id, '_cqt_checklist_items', true );
					if ( ! is_array( $checklist ) ) {
						$checklist = array();
					}

					$existing_inspection = new WP_Query( array(
						'post_type'      => 'cqt_inspection',
						'posts_per_page' => 1,
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'key'   => '_cqt_site_id',
								'value' => $site_id,
							),
							array(
								'key'   => '_cqt_supervisor_id',
								'value' => $supervisor_id,
							),
						),
						'date_query' => array(
							array(
								'year'  => date( 'Y' ),
								'month' => date( 'm' ),
								'day'   => date( 'd' ),
							),
						),
					) );

					$is_done_today = $existing_inspection->have_posts();
					$last_inspection_date = '';
					$last_inspection_id = 0;

					if ( $is_done_today ) {
						$last_inspection_id = $existing_inspection->posts[0]->ID;
						$last_inspection_date = get_the_date( '', $last_inspection_id );
					}
				?>
					<div class="cqt-site-card">
						<div class="cqt-site-header">
							<h2><?php echo esc_html( get_the_title() ); ?></h2>
							<?php if ( $is_done_today ) : ?>
								<span class="cqt-badge-completed">✅ Completed Today</span>
							<?php endif; ?>
						</div>
						<p class="cqt-site-meta">Checklist items: <?php echo count( $checklist ); ?></p>

						<?php if ( $is_done_today ) : ?>
							<p class="cqt-last-inspection">Last inspection: <?php echo esc_html( $last_inspection_date ); ?></p>
							<button type="button" class="cqt-view-report-btn cqt-btn-info" data-inspection-id="<?php echo esc_attr( $last_inspection_id ); ?>">View Last Report</button>
						<?php else : ?>
							<button type="button" class="cqt-start-inspection-btn cqt-btn-primary" data-site-id="<?php echo esc_attr( $site_id ); ?>">Start Inspection</button>
						<?php endif; ?>

						<div class="cqt-inspection-form-container" id="cqt-form-<?php echo esc_attr( $site_id ); ?>">
							<h3>Inspection Checklist</h3>
							<form class="cqt-inspection-form" data-site-id="<?php echo esc_attr( $site_id ); ?>">
								<?php foreach ( $checklist as $index => $item ) : ?>
									<div class="cqt-checklist-row">
										<label><?php echo esc_html( $item ); ?></label>
										<label><input type="radio" name="item_<?php echo esc_attr( $index ); ?>_status" value="pass" checked> Pass</label>
										<label><input type="radio" name="item_<?php echo esc_attr( $index ); ?>_status" value="fail"> Fail</label>
										<input type="hidden" name="item_<?php echo esc_attr( $index ); ?>_label" value="<?php echo esc_attr( $item ); ?>">
									</div>
								<?php endforeach; ?>
								<button type="submit" class="cqt-btn-success">Submit Inspection</button>
								<div class="cqt-inspection-message" style="margin-top: 10px; font-weight: bold;"></div>
							</form>
						</div>
					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			<?php endif; ?>
		</div>

		<!-- Report Modal -->
		<div id="cqt-report-modal">
			<div class="cqt-modal-content">
				<button type="button" id="cqt-close-modal" class="cqt-modal-close">X</button>
				<div id="cqt-modal-content"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function ajax_submit_inspection() {
		check_ajax_referer( 'cqt_supervisor_nonce', 'nonce' );

		if ( ! current_user_can( 'cqt_perform_inspection' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$site_id = isset( $_POST['site_id'] ) ? absint( wp_unslash( $_POST['site_id'] ) ) : 0;
		$results = isset( $_POST['results'] ) ? $_POST['results'] : array();

		if ( ! $site_id ) {
			wp_send_json_error( array( 'message' => 'Invalid site.' ) );
		}

		$parsed_results = array();
		$failed_items = array();

		foreach ( $results as $field ) {
			if ( strpos( $field['name'], '_status' ) !== false ) {
				preg_match( '/item_(\d+)_status/', $field['name'], $matches );
				if ( ! empty( $matches[1] ) ) {
					$index = $matches[1];
					$status = sanitize_text_field( $field['value'] );

					$label_key = 'item_' . $index . '_label';
					$label = '';
					foreach ( $results as $r ) {
						if ( $r['name'] === $label_key ) {
							$label = sanitize_text_field( $r['value'] );
							break;
						}
					}

					$parsed_results[] = array(
						'label'  => $label,
						'status' => $status,
					);

					if ( 'fail' === $status ) {
						$failed_items[] = $label;
					}
				}
			}
		}

		$inspection_id = wp_insert_post( array(
			'post_type'   => 'cqt_inspection',
			'post_title'  => 'Inspection - ' . get_the_title( $site_id ) . ' - ' . date( 'Y-m-d H:i' ),
			'post_status' => 'publish',
		) );

		if ( is_wp_error( $inspection_id ) ) {
			wp_send_json_error( array( 'message' => 'Could not save inspection.' ) );
		}

		update_post_meta( $inspection_id, '_cqt_site_id', $site_id );
		update_post_meta( $inspection_id, '_cqt_supervisor_id', get_current_user_id() );
		update_post_meta( $inspection_id, '_cqt_results', $parsed_results );
		update_post_meta( $inspection_id, '_cqt_failed_items', $failed_items );

		wp_send_json_success( array( 'message' => 'Inspection saved successfully.' ) );
	}

	public static function ajax_get_inspection_report() {
		check_ajax_referer( 'cqt_supervisor_nonce', 'nonce' );

		if ( ! current_user_can( 'cqt_perform_inspection' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$inspection_id = isset( $_POST['inspection_id'] ) ? absint( wp_unslash( $_POST['inspection_id'] ) ) : 0;

		if ( ! $inspection_id ) {
			wp_send_json_error( array( 'message' => 'Invalid inspection.' ) );
		}

		$results = get_post_meta( $inspection_id, '_cqt_results', true );
		$failed_items = get_post_meta( $inspection_id, '_cqt_failed_items', true );

		$html = '<h2 style="margin-top:0;">Inspection Report</h2>';
		$html .= '<p><strong>Date:</strong> ' . esc_html( get_the_date( '', $inspection_id ) ) . '</p>';
		$html .= '<h4>Checklist Results</h4>';
		$html .= '<table style="width:100%; border-collapse: collapse;">';
		$html .= '<tr style="border-bottom: 1px solid #e5e7eb;"><th style="text-align:left; padding:8px;">Item</th><th style="text-align:left; padding:8px;">Status</th></tr>';

		if ( is_array( $results ) ) {
			foreach ( $results as $r ) {
				$color = ( 'pass' === $r['status'] ) ? 'green' : 'red';
				$text = ( 'pass' === $r['status'] ) ? 'Pass' : 'Fail';
				$html .= '<tr style="border-bottom: 1px solid #f3f4f6;">';
				$html .= '<td style="padding:8px;">' . esc_html( $r['label'] ) . '</td>';
				$html .= '<td style="padding:8px; color:' . $color . '; font-weight:bold;">' . $text . '</td>';
				$html .= '</tr>';
			}
		}
		$html .= '</table>';

		if ( ! empty( $failed_items ) ) {
			$html .= '<div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:15px; border-radius:8px; margin-top:15px;">';
			$html .= '<strong>⚠️ Corrective Actions Required:</strong><ul style="margin:10px 0 0 20px;">';
			foreach ( $failed_items as $item ) {
				$html .= '<li>' . esc_html( $item ) . '</li>';
			}
			$html .= '</ul></div>';
		} else {
			$html .= '<div style="background:#ecfdf3; border:1px solid #b7ebc6; color:#166534; padding:15px; border-radius:8px; margin-top:15px;">';
			$html .= '<strong>✅ All items passed.</strong>';
			$html .= '</div>';
		}

		wp_send_json_success( array( 'html' => $html ) );
	}
}