jQuery(document).ready(function($) {
	// Toggle the inspection form visibility
	$('.cqt-start-inspection-btn').on('click', function(e) {
		e.preventDefault();

		var siteId = $(this).data('site-id');
		var formContainer = $('#cqt-form-' + siteId);

		formContainer.slideToggle(300);
		$(this).text( $(this).text() === 'Start Inspection' ? 'Hide Inspection' : 'Start Inspection' );
	});

	// Handle the form submission
	$('.cqt-inspection-form').on('submit', function(e) {
		e.preventDefault();
		var form = $(this);
		var btn = form.find('button[type="submit"]');
		var siteId = form.data('site-id');
		var msg = form.find('.cqt-inspection-message');

		var formData = form.serializeArray();
		var data = {
			action: 'cqt_submit_inspection',
			nonce: cqtAjax.nonce,
			site_id: siteId,
			results: formData
		};

		btn.text('Submitting...').prop('disabled', true);
		msg.text('');

		$.ajax({
			url: cqtAjax.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				if (response.success) {
					msg.css('color', 'green').text(response.data.message);
					setTimeout(function() { location.reload(); }, 1500);
				} else {
					msg.css('color', 'red').text(response.data.message);
					btn.text('Submit Inspection').prop('disabled', false);
				}
			},
			error: function() {
				msg.css('color', 'red').text('Something went wrong. Please try again.');
				btn.text('Submit Inspection').prop('disabled', false);
			}
		});
	});

	// View Report Modal
	$('.cqt-view-report-btn').on('click', function() {
		var inspectionId = $(this).data('inspection-id');

		$('#cqt-modal-content').html('<p>Loading...</p>');
		$('#cqt-report-modal').css('display', 'flex');

		$.post(cqtAjax.ajaxUrl, {
			action: 'cqt_get_inspection_report',
			nonce: cqtAjax.nonce,
			inspection_id: inspectionId
		}, function(response) {
			if (response.success) {
				$('#cqt-modal-content').html(response.data.html);
			} else {
				$('#cqt-modal-content').html('<p>Error loading report.</p>');
			}
		});
	});

	// Close Modal
	$('#cqt-close-modal').on('click', function() {
		$('#cqt-report-modal').css('display', 'none');
	});
});