<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CQT_Checklist_Meta {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_cqt_site', array( __CLASS__, 'save_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
	}

	public static function add_meta_box() {
		add_meta_box(
			'cqt_checklist_box',
			'Inspection Checklist Items',
			array( __CLASS__, 'render_meta_box' ),
			'cqt_site',
			'normal',
			'high'
		);
	}

	public static function enqueue_admin_scripts( $hook ) {
		global $post_type;
		if ( 'cqt_site' !== $post_type ) {
			return;
		}

		wp_enqueue_script(
			'cqt-admin-checklist',
			CQT_PLUGIN_URL . 'assets/admin-checklist.js',
			array( 'jquery' ),
			CQT_VERSION,
			true
		);
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( 'cqt_save_checklist', 'cqt_checklist_nonce' );

		$items = get_post_meta( $post->ID, '_cqt_checklist_items', true );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		?>
		<div id="cqt-checklist-container">
			<div id="cqt-checklist-items">
				<?php if ( empty( $items ) ) : ?>
					<p class="cqt-no-items">No checklist items yet. Click "Add Item" to start.</p>
				<?php else : ?>
					<?php foreach ( $items as $index => $item ) : ?>
						<div class="cqt-checklist-item" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
							<input type="text" name="cqt_checklist_items[]" value="<?php echo esc_attr( $item ); ?>" class="regular-text" style="flex: 1;" />
							<button type="button" class="button cqt-remove-item">Remove</button>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<button type="button" class="button button-primary" id="cqt-add-item" style="margin-top: 10px;">+ Add Item</button>
		</div>
		<p class="description">These items will appear on the inspection form for this site.</p>
		<?php
	}

	public static function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['cqt_checklist_nonce'] ) || ! wp_verify_nonce( $_POST['cqt_checklist_nonce'], 'cqt_save_checklist' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$items = isset( $_POST['cqt_checklist_items'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cqt_checklist_items'] ) ) : array();

		// Filter out empty items
		$items = array_filter( $items, function( $item ) {
			return ! empty( trim( $item ) );
		} );

		update_post_meta( $post_id, '_cqt_checklist_items', array_values( $items ) );
	}
}