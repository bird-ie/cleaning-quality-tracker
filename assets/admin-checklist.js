jQuery(document).ready(function($) {
	// Add Item
	$('#cqt-add-item').on('click', function(e) {
		e.preventDefault();

		$('.cqt-no-items').remove();

		var itemHtml = '<div class="cqt-checklist-item" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">' +
			'<input type="text" name="cqt_checklist_items[]" value="" class="regular-text" style="flex: 1;" placeholder="e.g. Empty all bins" />' +
			'<button type="button" class="button cqt-remove-item">Remove</button>' +
			'</div>';

		$('#cqt-checklist-items').append(itemHtml);
	});

	// Remove Item
	$(document).on('click', '.cqt-remove-item', function(e) {
		e.preventDefault();
		$(this).closest('.cqt-checklist-item').remove();

		if ( $('#cqt-checklist-items .cqt-checklist-item').length === 0 ) {
			$('#cqt-checklist-items').html('<p class="cqt-no-items">No checklist items yet. Click "Add Item" to start.</p>');
		}
	});
});