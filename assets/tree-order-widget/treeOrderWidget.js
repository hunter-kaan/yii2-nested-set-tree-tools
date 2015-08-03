if (typeof valiant == "undefined" || !valiant) {
	var valiant = {};
}

valiant.treeOrderWidget = (function ($) {
	return {
		registerTreeOrderWidget: function (id, tree) {
			var widget = $('#' + id);
			widget
				.on('change', '#' + widget.data('parent-id'), (function (widget, tree) {
					var order_control = widget.find('#' + widget.data('order-id'));
					var parent_control = widget.find('#' + widget.data('parent-id'));
					var order_values = [];
					order_values[parent_control.val()] = order_control.val();
					parent_control.data('current-value', parent_control.val());

					return function () {
						order_values[parent_control.data('current-value')] = order_control.val();

						var new_value = parent_control.val();
						parent_control.data('current-value', new_value);
						order_control.trigger('clean');

						if(new_value in tree) {
							var selected_order = order_values[new_value];
							$.each(tree[new_value], (function(selected_order) {
								return function(value, label) {
									var option = $('<option></option>')
										.attr('value', value)
										.text(label);
									if(selected_order === value) {
										option.attr('selected', 'selected');
									}
									order_control.append(option)
								}
							}) (selected_order));
						}
					};
				})(widget, tree))
				.on('clean', '#' + widget.data('order-id'), function() {
					$(this).find('option').each(function() {
						if($(this).val() > 0) {
							$(this).remove();
						}
					})
				})
				.find('#' + widget.data('parent-id')).trigger('change');
		}
	};

})(jQuery);
