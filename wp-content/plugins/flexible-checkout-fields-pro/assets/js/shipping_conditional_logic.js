(function($){
	$(document).ready(function add_new_rule_logic() {
		var fcf_shipping_zones = fcf_shipping_conditional_logic.shipping_zones;
		var fcf_no_shipping_zones = fcf_shipping_conditional_logic.no_shipping_zones;
		$(document).on('click','a.add_rule_shipping_fields_button', function add_new_rule() {
			var data_count = $(this).attr('data-count');
			var data_key = $(this).attr('data-key');
			var data_name = $(this).attr('data-name');
			var clone_div = $('div.flexible_checkout_shipping_fields_add_rule').clone();
			clone_div.removeClass('flexible_checkout_shipping_fields_add_rule');
			clone_div.show();
			clone_div.removeClass('add_rule');
			$(clone_div).find('select,input').each(function add_new_rule () {
				var name = $(this).attr('name');
				$(this).attr('name', name.replace('[settings][key][name][conditional_logic_shipping_fields_rules][-1]', '[settings][' + data_key + '][' + data_name + '][conditional_logic_shipping_fields_rules][' + data_count + ']'));
				$(this).attr('data-selected', data_count);
				$(this).removeAttr('disabled');
				if ($(this).hasClass('zones')) {
					var select_field = this;
					$(this).empty();
					$(select_field).append($('<option selected disabled></option>').val('').html(fcf_shipping_conditional_logic.select_shipping_zone));
					$(select_field).append($('<optgroup id="zone" label="'+fcf_shipping_conditional_logic.zones+'">'));
					if ($(JSON.parse(fcf_no_shipping_zones)).length > 0){
						$(select_field).append($('<option class="no-zone" value="no_shipping_zones">'+ fcf_shipping_conditional_logic.no_shipping_zones_or_global_methods +'</option>'));
					}
					$.each(JSON.parse(fcf_shipping_zones), function (val, text) {
						if (val !== data_name) {
							$(select_field).append($("<option data-zone='" + text + "' class='zone'></option>").val(val).html(text));
						}
					});
					$(select_field).append('</optgroup>');

					$(clone_div).find('select.shipping_methods').each(function(index){
						$(this).addClass(data_name + '_' + data_count);
						var method_name = $(this).attr('name');
						$(this).attr('name', method_name.replace('[settings][key][name][conditional_logic_shipping_fields_rules][-1]', '[settings][' + data_key + '][' + data_name + '][conditional_logic_shipping_fields_rules][' + data_count + ']'));


					});
					$(select_field).change(function () {
						var zone_id = $(this).val();
						ajaxRequest(zone_id, data_count, data_name, data_key);
					});
				}
			});

			data_count++;
			$(this).attr('data-count', data_count);
			$(this).parent().before(clone_div);
		});

		return false;
	});

	function ajaxRequest(zone_id, data_count, data_name, data_key) {
		$.ajax({
			type: 'post',
			url: fcf_shipping_conditional_logic.ajax_url,
			data: {
				action: 'retrieve_shipping_methods_by_selected_zone',
				zone_id: zone_id,
			},
			beforeSend: function disable_select_zone(){
				var select_field = $('select.zones');
				$(select_field).on('change',function(){
					var methods = $('select.' + data_name + '_' + data_count);
					if (methods.innerHTML !== '') {
						$(methods).empty();
						$(methods).append($('<option selected disabled></option>').val('').html(fcf_shipping_conditional_logic.shipping_zones_to_select));
					}
				});
			},
			success: function (data) {
				refresh_methods_select(data, data_count, data_name, data_key);
			}
		});

		data_count--;
	}

	function refresh_methods_select(data,data_count,data_name){
		var shipping_methds = $('select.' + data_name + '_' + data_count);
		$(shipping_methds).blur();
		$(shipping_methds).each(function (index) {
			var name = $(this).attr('name');
			if ($(this).hasClass(data_name + '_' + data_count)) {
				if (name !== undefined) {
					$('.option_disabled').removeAttr('disabled');
					var select_fields = $('select.' + data_name + '_' + data_count);
					var response = JSON.parse(data);
					if (response.status === 'success') {
						$(select_fields).append($('<optgroup id="zone" label="'+fcf_shipping_conditional_logic.methods+'">'));
						$.each(response.shipping_methods, function (key, value) {
							if (key !== data_name) {
								if(key.match('^flexible_shipping:')){
									$.each(response.flexible_shipping_methods, function (fs_method_key, fs_method_value) {
										$.each(response.flexible_shipping_ids, function get_flexible_shipping_methods_id(method, method_id){
											if(fs_method_key.match(method_id)){
												if (fs_method_key !== data_name) {
													$(select_fields).append($('<option></option>').val(fs_method_key).html(fs_method_value));
													var duplicate_options = {};

													$(select_fields).children().each(function(){
														var duplicate_values = $(this).attr('value');

														if (duplicate_options[duplicate_values]) {
															$(this).remove();
														} else{
															duplicate_options[duplicate_values] = true;
														}
													});
												}
											}
										});
									});
								}else{
									if(value !== null) {
										$(select_fields).append($('<option class="no-fs-methods"></option>').val(key).html(value));
									}
								}
							}
						});
						$(select_fields).append('</optgroup>');
					}
				}
			}
		});
	}

})(jQuery);
