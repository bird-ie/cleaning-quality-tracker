<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Reports_Page {

	public static function init() {
		add_shortcode( 'cqt_inspection_reports', array( __CLASS__, 'render_reports_page' ) );
	}

	public static function render_reports_page() {
		// Only allow admins to view this page
		if ( ! current_user_can( 'manage_options' ) ) {
			return '<p>You do not have permission to view this page.</p>';
		}

		$inspections = new WP_Query( array(
			'post_type'      => 'cqt_inspection',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		ob_start();
		?>
		<div class="cqt-reports-page" style="max-width: 1000px; margin: 40px auto; font-family: sans-serif;">
			<h1>Inspection Reports</h1>
			<p>View all submitted inspections and their results.</p>

			<?php if ( ! $inspections->have_posts() ) : ?>
				<div style="padding: 20px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
					<p>No inspections have been submitted yet.</p>
				</div>
			<?php else : ?>
				<table style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
					<thead style="background: #f9fafb;">
						<tr>
							<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Date</th>
							<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Site</th>
							<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Supervisor</th>
							<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Passed</th>
							<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Failed</th>
							<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php while ( $inspections->have_posts() ) : $inspections->the_post(); 
							$inspection_id = get_the_ID();
							$site_id = get_post_meta( $inspection_id, '_cqt_site_id', true );
							$supervisor_id = get_post_meta( $inspection_id, '_cqt_supervisor_id', true );
							$results = get_post_meta( $inspection_id, '_cqt_results', true );
							$failed_items = get_post_meta( $inspection_id, '_cqt_failed_items', true );

							$passed_count = 0;
							$failed_count = 0;

							if ( is_array( $results ) ) {
								foreach ( $results as $r ) {
									if ( 'pass' === $r['status'] ) {
										$passed_count++;
									} else {
										$failed_count++;
									}
								}
							}

							$site_title = $site_id ? get_the_title( $site_id ) : 'Unknown Site';
							$supervisor_name = $supervisor_id ? get_user_by( 'id', $supervisor_id )->display_name : 'Unknown';
						?>
							<tr>
								<td style="padding: 12px; border-bottom: 1px solid #f3f4f6;"><?php echo esc_html( get_the_date() ); ?></td>
								<td style="padding: 12px; border-bottom: 1px solid #f3f4f6;"><?php echo esc_html( $site_title ); ?></td>
								<td style="padding: 12px; border-bottom: 1px solid #f3f4f6;"><?php echo esc_html( $supervisor_name ); ?></td>
								<td style="padding: 12px; border-bottom: 1px solid #f3f4f6; color: green; font-weight: bold;"><?php echo esc_html( $passed_count ); ?></td>
								<td style="padding: 12px; border-bottom: 1px solid #f3f4f6; color: red; font-weight: bold;"><?php echo esc_html( $failed_count ); ?></td>
								<td style="padding: 12px; border-bottom: 1px solid #f3f4f6;">
									<a href="<?php echo esc_url( get_edit_post_link( $inspection_id ) ); ?>" style="color: #3b82f6; text-decoration: none; font-weight: bold;">View Details</a>
								</td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}