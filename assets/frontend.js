jQuery(document).ready(function($) {
	// Filter Products Button
	$('#vcf_filter_btn').on('click', function(e) {
		e.preventDefault();

		var makeId  = $('#vcf_make_filter').val();
		var modelId = $('#vcf_model_filter').val();
		var yearId  = $('#vcf_year_filter').val();
		var btn     = $(this);
		var msg     = $('#vcf_filter_message');

		if (!makeId && !modelId && !yearId) {
			msg.css('color', 'red').text('Please select at least one vehicle detail.');
			return;
		}

		btn.text('Filtering...').prop('disabled', true);
		msg.text('');

		$.ajax({
			url: vcfAjax.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'vcf_filter_products',
				nonce: vcfAjax.nonce,
				make_id: makeId,
				model_id: modelId,
				year_id: yearId
			},
			success: function(response) {
				btn.text('Filter Products').prop('disabled', false);

				if (response.success) {
					$('#vcf-product-grid').html(response.data.html);
					msg.css('color', 'green').text('Showing ' + response.data.count + ' compatible products.');
				} else {
					msg.css('color', 'red').text(response.data.message);
				}
			},
			error: function() {
				btn.text('Filter Products').prop('disabled', false);
				msg.css('color', 'red').text('Something went wrong. Please try again.');
			}
		});
	});

	// Reset Button
	$('#vcf_reset_btn').on('click', function(e) {
		e.preventDefault();
		$('#vcf_make_filter').val('');
		$('#vcf_model_filter').val('');
		$('#vcf_year_filter').val('');
		$('#vcf_filter_message').text('');
		location.reload();
	});

	// Save Vehicle Button
	$('#vcf_save_vehicle_btn').on('click', function(e) {
		e.preventDefault();
		var btn = $(this);
		var msg = $('#vcf_filter_message');

		var makeId  = $('#vcf_make_filter').val();
		var modelId = $('#vcf_model_filter').val();
		var yearId  = $('#vcf_year_filter').val();

		if (!makeId || !modelId || !yearId) {
			msg.css('color', 'red').text('Please select Make, Model, and Year before saving.');
			return;
		}

		btn.text('Saving...').prop('disabled', true);

		$.ajax({
			url: vcfAjax.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'vcf_save_vehicle',
				nonce: vcfAjax.nonce,
				make_id: makeId,
				model_id: modelId,
				year_id: yearId
			},
			success: function(response) {
				btn.text('Save My Vehicle').prop('disabled', false);
				if (response.success) {
					msg.css('color', 'green').text(response.data.message);
					// Reload to show the saved vehicle banner
					setTimeout(function() { location.reload(); }, 1000);
				} else {
					msg.css('color', 'red').text(response.data.message);
				}
			},
			error: function() {
				btn.text('Save My Vehicle').prop('disabled', false);
				msg.css('color', 'red').text('Could not save vehicle.');
			}
		});
	});
});