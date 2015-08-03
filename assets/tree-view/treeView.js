+function ($) {
	$('body')
		.on('click', 'a.tree-anchor', function () {
			var isOpen = $(this).find('.node').hasClass('node-open'),
				maxLeft = $(this).data('left'),
				maxRight = $(this).data('right'),
				skipLeft = 0,
				skipRight = 0;

			var node = $(this).closest('tr');
			while(node.next().length) {
				node = node.next();

				if(node.data('right') > maxRight) {
					break;
				}

				if(node.data('left') > skipLeft && node.data('right') < skipRight) {
					continue;
				}

				if(node.data('right') - node.data('left') > 1) {
					skipLeft = node.data('left');
					skipRight = node.data('right');
					if(node.find('.node').hasClass('node-open')) {
						node.find('a.tree-anchor').trigger('click');
					}
				}

				node.toggle(!isOpen);
			}

			$(this).find('.node')
				.toggleClass('node-open', !isOpen)
				.toggleClass('node-close', isOpen);
		});
}(jQuery);