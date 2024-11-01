jQuery(document).ready(function () {
	if (jQuery(".chosen-select")) {
		woom_generate_chosen(jQuery(".chosen-select"));

		jQuery('input[type="checkbox"]').on('change', (event) => {
			woom_update_button_toggle();
		});
	}

});
function woom_generate_chosen(el) {
	let chosen_select = el.chosen({ max_selected_options: el.data('params_count') });
	chosen_select.on('change', (event, params) => {
		const el = jQuery(event.target);
		if (el.data('params_count')) {
			reorder_multiselect(el, params);
			woom_validate_param_option(el, el.data('params_count'));
		}

	});
	jQuery('.woom-field-chosen-select .chosen-container').css({
		'width': "100%",
		'max-width': "calc(135px)",
		'margin-right': "5px"
	});
}
function reorder_multiselect(el, params) {

	let el_values = el.data('chosen_value');
	if (el_values !== '') {
		el_values = el_values.split(',');
	} else {
		el_values = [];
	}

	if (params.selected) {
		el_values.push(params.selected);
	} else if (params.deselected) {
		el_values = jQuery.grep(el_values, function (value) {
			return value != params.deselected;
		});
	}

	el.data('chosen_value', el_values.join(','));
	const no_sel_options = [];
	el.children().not(':selected').each((index, element) => no_sel_options.push(element.value));
	el.empty();
	el_values.forEach(value => el.append(jQuery('<option value="' + value + '" selected>' + value + '</option>')));
	no_sel_options.forEach(value => el.append(jQuery('<option value="' + value + '">' + value + '</option>')));
	el.trigger("chosen:updated");
}

function woom_update_button_toggle() {
	let save_btn_disabled = false;
	if (jQuery('.woom-switch-checkbox input')) {
		jQuery('.woom-switch-checkbox input').each((index, switchInput) => {

			const id_prefix = switchInput.id.replace('enabled', '');
			const header_params = jQuery('#' + id_prefix + 'header_params');
			const body_params = jQuery('#' + id_prefix + 'body_params');
			const template = jQuery('#' + id_prefix + 'template');
			if (jQuery(switchInput).is(":checked")) {

				template.prop('disabled', false);

				if (header_params.data('params_count') > 0) {
					header_params.prop('disabled', false);
				}
				if (body_params.data('params_count') !== body_params.val().length) {
					if (jQuery(body_params).siblings('p').is(':hidden')) {
						jQuery(body_params).siblings('p').show();
					}

					body_params.prop('disabled', false).trigger('chosen:updated');

				}

				let is_header_params_invalid = header_params.data('params_count') > 0 && header_params.val() === '';
				let is_body_params_invalid = body_params.data('params_count') !== body_params.val().length;
				if (template.val() === '' || is_header_params_invalid || is_body_params_invalid) {
					save_btn_disabled = true;
				}

			} else {
				template.prop('disabled', true);
				header_params.prop('disabled', true);
				body_params.prop('disabled', true).trigger('chosen:updated');
				if (jQuery(body_params).siblings('p').is(':visible')) {
					jQuery(body_params).siblings('p').hide();
				}

			}
			if (template.val()) {
				jQuery(template.parents('tr')).find('.woom-field-actions a').show();
			} else {
				jQuery(template.parents('tr')).find('.woom-field-actions a').hide();
			}
		})
	}
	jQuery('button[name="save"]').prop('disabled', save_btn_disabled);

}

woom_update_button_toggle();



function woom_validate_param_option(el, max_params_count) {
	let error_label = el.siblings('p');

	if (max_params_count > el.val().length) {
		let count = el.val().length;
		let error_msg = jQuery(el).data('error_message').replace("{{count}}", (max_params_count - count));
		error_label.html(error_msg);
		if (error_label.hasClass('woom-success-text')) {
			error_label.removeClass('woom-success-text');
		}
		if (!error_label.hasClass('woom-error-text')) {
			error_label.addClass('woom-error-text');
		}
		if (error_label.is(':hidden')) {
			error_label.show();
		}
	} else if (el.val().length > max_params_count) {
		if (!error_label.hasClass('woom-error-text')) {
			error_label.removeClass('woom-success-text').addClass('woom-error-text');
		}
		if (error_label.is(':hidden')) {
			error_label.show();
		}
		error_label.html("no parameters allowed");
	} else {
		if (error_label.is(':visible')) {
			if (error_label.hasClass('woom-error-text')) {
				error_label.removeClass('woom-error-text');
			}
			if (!error_label.hasClass('woom-success-text')) {
				error_label.addClass('woom-success-text');
			}
			error_label.html('perfect...');
			error_label.hide();
		}

	}
	woom_update_button_toggle();
}

function woom_copy_field_val(item) {
	item.preventDefault();
	var input = jQuery(item.target).siblings('input')[0];
	var copyText = document.getElementById(input.id);
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	navigator.clipboard.writeText(copyText.value);
	const inactive_text = jQuery(item.target).text();
	jQuery(item.target).text(jQuery(item.target).attr('data-activeText'));
	setTimeout(() => {
		jQuery(item.target).text(inactive_text);
	}, 3000);
}


function woom_addnew_row(target) {

	const id_prefix = jQuery(target).data('row');
	let table = jQuery(target).parents('table');
	let last_row = table.find('tbody tr:last');
	let row_count = table.find('tbody tr').length;
	let new_row = jQuery(last_row).clone();
	let new_id = '';
	for (const input of jQuery(new_row).find(':input')) {
		let key_end = jQuery(input).attr('id');
		if (key_end) {
			key_end = key_end.replace(id_prefix + '_' + row_count, '');
			new_id = id_prefix + '_' + (row_count + 1) + key_end;
			jQuery(input).attr({
				'name': new_id,
				'id': new_id,
			});
			if (key_end === '_label') {
				if (new_id.includes('hook')) {
					jQuery(input).val('hook_' + (row_count + 1));
				} else if (new_id.includes('action')) {
					jQuery(input).val('Button ' + (row_count + 1));
				} else {
					jQuery(input).val('Option ' + (row_count + 1));
				}
			} else if (input.type) {
				if (jQuery(input).siblings('label').length > 0) {
					jQuery(input).siblings('label').attr('for', new_id)
				}
				switch (input.type) {
					case 'checkbox':
						jQuery(input).prop('checked', false);

						break;
					case 'text':
					case 'select-one':
						jQuery(input).val('');

						break;
					case 'select-multiple':
						jQuery(input).val([]);

						break;
					default:
						if (jQuery(input).prop("tagName").toLowerCase() === 'button' && jQuery(input).attr("disabled")) {
							jQuery(input).attr("disabled", false);
						}
						if (jQuery(input).siblings('a')) {
							jQuery(input).siblings('a').hide();
						}
						if (input.closest('a')) {
						}
						break;
				}
			}
			jQuery.each(input.attributes, function () {
				if (this.value.includes(row_count)) {
					this.value = this.value.replace(row_count, (row_count + 1))
				}
			});
		}
	}
	// jQuery(table).append(new_row);
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: woom_ajax.url,
		data: {
			action: "woom_autosave_manual_trigger_actions",
			data: {
				key: new_id.replace('_remove', '').replace('woom_trigger_', ''),
				woom_nonce: woom_ajax.woom_post_nonce
			}
		},
		success: function (response) {
			if (response?.status === 'success') {
				location.reload();
			}
		}
	});
}
function woom_trigger_button(event, item) {
	event.preventDefault();
	const action_btn_label = jQuery(item).text();
	const action_btn_loader = '<span class="dashicons dashicons-update woom-rotate rotate"></span>';
	jQuery(item).html(action_btn_label + action_btn_loader);
	jQuery(item).parent().find('button').prop('disabled', true);

	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: woom_ajax.url,
		data: {
			action: "woom_manual_trigger_action",
			data: {
				order_id: jQuery(item).data('order-id'),
				slug_prefix: jQuery(item).data('prefix'),
				woom_nonce: woom_ajax.woom_post_nonce
			}
		},
		success: function (response) {
			jQuery(item).parent().find('button').prop('disabled', false);

			jQuery(item).html(action_btn_label);
			if (response?.status === 'success') {
				alert("Success");

			} else {
				console.log(response);
			}
			return;
		}
	});
}

function woom_remove_field_trigger(event, target,) {
	event.preventDefault();
	let data_options = jQuery(target).data();
	data_options.woom_nonce = woom_ajax.woom_post_nonce;
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: woom_ajax.url, //URL to your wordpress install's admin-ajax.php file
		data: {
			action: "woom_clear_option",
			data: data_options
		},
		success: function (response) {
			if (response?.status === 'success') {
				jQuery(target).closest('tr').remove();
			}
		}
	});
}

function woom_regenerate_templates(event, target) {
	event.preventDefault();
	jQuery(target).append(' <span class="dashicons rotate dashicons-update"></span>');
	const token = jQuery(target).data('access-token');
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: woom_ajax.url, //URL to your wordpress install's admin-ajax.php file
		data: {
			action: "woom_regenerate_wa_templates",
			data: { woom_nonce: woom_ajax.woom_post_nonce, woom_access_token: token }
		},
		success: function (response) {
			jQuery(target).children(jQuery('.dashicon.rotate')).remove();
			if (response.success) {
				jQuery('#woom-ajax-result').html(response.data);
			} else {
				if (response.data) {
					jQuery('#woom-ajax-result').html(response.data);
				}

			}
		}
	});
}

function woom_toggle_template_popup(event, target, show = true) {
	event.preventDefault();
	if (show) {
		jQuery("#" + 'woom-wa-templates').show();
		if (jQuery(target).data('id')) {
			let item = jQuery('#' + jQuery(target).data('id'));
			item.show();
			item.siblings().hide();
		}
	} else {
		jQuery("#" + 'woom-wa-templates').hide();
		jQuery('#' + jQuery(target).data('id')).hide();
	}
}

function woom_prevent_element(event, trigger = false) {
	if (!trigger) {
		event.stopPropagation();
	}

}

function woom_update_template_preview(target, params_counts) {

	let link = jQuery(target).parents().closest('tr').find('.woom-field-actions a.link');
	link.data('id', 'woom_template_table_' + jQuery(target).val());
	if (jQuery(link).is(':hidden')) {
		jQuery(link).show();
	}
	let params_count = {
		header: 0, body: 0, footer: 0
	};
	if (jQuery(target).val()) {
		params_count = params_counts[jQuery(target).val()].params_count;
	}
	let header_params_disabled = (params_count.header === 0);
	let body_params_disabled = (params_count.body === 0);
	let prefix = (target.id).replace('_template', '');

	let header_params = jQuery('#' + prefix + '_header_params');
	header_params.prop('disabled', header_params_disabled);
	header_params.val('');
	header_params.data('params_count', params_count.header);
	let body_params = jQuery('#' + prefix + '_body_params');
	body_params.prop('disabled', body_params_disabled);
	body_params.data('params_count', params_count.body);
	body_params.data('chosen_value', '');
	body_params.data('chosen').max_selected_options = params_count.body;
	body_params.val([]).trigger('chosen:updated');
	let header_empty_label = body_params.data('params_label_empty');
	let header_avail_label = body_params.data('params_label');

	header_params.find('option').each((index, item) => {
		if (item.value == '') {
			jQuery(item).html((params_count.header > 0) ? header_avail_label : header_empty_label);
		}
	})

	woom_update_button_toggle();
	woom_validate_param_option(body_params, params_count.body);
}

function woom_handle_templates() {
	woom_update_button_toggle();
}
function woom_doc_download(event) {
	event.preventDefault();
	var btn = event.target;
	let textarea = jQuery(btn).parents('.woom-info-container').children('.woom_support_diagnostic_info')[0];
	textarea = jQuery(textarea).html();
	textarea = textarea.replace(/<br>/g, "\n");
	var blob = new Blob([textarea], { type: 'text/plain' });

	var link = document.createElement('a');
	link.download = 'wc-messaging-status.txt';
	link.href = window.URL.createObjectURL(blob);
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
}
function woom_doc_copy(event) {
	event.preventDefault();
	var btn = event.target;
	let textarea = jQuery(btn).parents('.woom-info-container').children('.woom_support_diagnostic_info')[0];
	textarea = jQuery(textarea).html();
	textarea = textarea.replace(/<br>/g, "\n");
	console.log(textarea);
	navigator.clipboard.writeText(textarea).then(function () {
	}).catch(function (err) {
		console.error('Failed to copy text: ', err);
	});
}