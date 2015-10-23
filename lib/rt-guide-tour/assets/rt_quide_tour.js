(function ($, MAP) {

	$(document).on('MyAdminPointers.setup_done', function (e, data) {
		e.stopImmediatePropagation();
		MAP.setPlugin(data); // open first popup
	});
	$(document).on('MyAdminPointers.current_ready', function (e) {
		e.stopImmediatePropagation();
		MAP.openPointer(); // open a popup
	});
	MAP.js_pointers = {}; // contain js-parsed pointer objects
	MAP.first_pointer = false; // contain first pointer anchor jQuery object
	MAP.current_pointer = false; // contain current pointer jQuery object
	MAP.visible_pointers = []; // contain ids of pointers whose anchors are visible

	MAP.hasPrev = function (data) { // check if a given pointer object has valid next property
		return typeof data.prev === 'string' && data.prev !== '' && typeof MAP.js_pointers[data.prev].data !== 'undefined' && typeof MAP.js_pointers[data.prev].data.id === 'string';
	};

	MAP.hasNext = function (data) { // check if a given pointer object has valid next property
		return typeof data.next === 'string' && data.next !== '' && typeof MAP.js_pointers[data.next].data !== 'undefined' && typeof MAP.js_pointers[data.next].data.id === 'string';
	};

	MAP.isVisible = function (data) { // check if a anchor of a given pointer object is visible
		return $.inArray(data.id, MAP.visible_pointers) !== -1;
	};

	// given a pointer object, return its the anchor jQuery object if available
	// otherwise return first available, lookin at next property of subsequent pointers
	MAP.getPointerData = function (data) {
		var $target = $(data.anchor_id);
		if ($.inArray(data.id, MAP.visible_pointers) !== -1) {
			return {target: $target, data: data};
		}
		$target = false;
		while (MAP.hasNext(data) && !MAP.isVisible(data)) {
			data = MAP.js_pointers[data.next].data;
			if (MAP.isVisible(data)) {
				$target = $(data.anchor_id);
			}
		}
		return MAP.isVisible(data) ? {target: $target, data: data} : {target: false, data: false};
	};

	// take pointer data and setup pointer plugin for anchor element
	MAP.setPlugin = function (data) {
		MAP.current_pointer = false;
		var pointer_data = MAP.getPointerData(data);
		if (!pointer_data.target || !pointer_data.data) {
			return;
		}
		$target = pointer_data.target;
		data = pointer_data.data;
		$pointer = $target.pointer({
			content: data.title + data.content,
			position: {edge: data.edge, align: data.align},
			close: function () {
			}
		});
		MAP.current_pointer = {pointer: $pointer, data: data};
		$(document).trigger('MyAdminPointers.current_ready');
	};

	// scroll the page to current pointer then open it
	MAP.openPointer = function () {
		var $pointer = MAP.current_pointer.pointer;
		if (typeof $pointer !== 'object') {
			return;
		}
		$('html, body').animate({ // scroll page to pointer
			scrollTop: $pointer.offset().top - 300
		}, 300, function () { // when scroll complete
			var $widget = $pointer.pointer('widget');
			if (MAP.current_pointer.data.edge === 'top' && MAP.current_pointer.data.align === 'right') {
				var $arrow = $widget.find('.wp-pointer-arrow').eq(0);
				$arrow.attr('style', 'left:85%');
			}
			MAP.setPrev($widget, MAP.current_pointer.data);
			MAP.setDismiss($widget, MAP.current_pointer.data);
			MAP.setNext($widget, MAP.current_pointer.data);
			$pointer.pointer('open'); // open
		});
	};

	MAP.setDismiss = function ($widget, data) {
		if (typeof $widget === 'object') {
			var $buttons = $widget.find('.wp-pointer-buttons').eq(0);
			var $close = $buttons.find('a.close').eq(0);
			$button = $close.clone(true, true).removeClass('close');
			$button.addClass('button').addClass('button-primary');
			$button.click(function () {
				jQuery.each(MAP.js_pointers, function (key, value) {
					$.post(ajaxurl, {pointer: value.data.id, action: 'dismiss-wp-pointer'});
				});
			});
			var label = MAP.close_label;
			$button.html(label).appendTo($buttons);
		}
	};

	// if there is a next pointer set button label to "Next", to "Close" otherwise
	MAP.setPrev = function ($widget, data) {
		if (typeof $widget === 'object') {
			var $buttons = $widget.find('.wp-pointer-buttons').eq(0);
			var $close = $buttons.find('a.close').eq(0);
			$button = $close.clone(true, true).removeClass('close');
			$button.addClass('button').addClass('button-primary alignleft');
			$button.addClass('button').attr('style', 'float:left;');
			$button.click(function () {
				// open next pointer if it exists
				if (MAP.hasPrev(data)) {
					if (typeof data.prevurl === 'string' && data.prevurl !== '') {
						window.location = data.prevurl;
					}
					MAP.setPlugin(MAP.js_pointers[data.prev].data);
				}
			});
			has_prev = false;
			if (MAP.hasPrev(data)) {
				has_prev_data = MAP.getPointerData(MAP.js_pointers[data.prev].data);
				has_prev = has_prev_data.target && has_prev_data.data;
			}
			if (has_prev) {
				$button.html(MAP.prev_label).appendTo($buttons);
			}
		}
	};

	// if there is a next pointer set button label to "Next", to "Close" otherwise
	MAP.setNext = function ($widget, data) {
		if (typeof $widget === 'object') {
			var $buttons = $widget.find('.wp-pointer-buttons').eq(0);
			var $close = $buttons.find('a.close').eq(0);
			$button = $close.clone(true, true).removeClass('close');
			$buttons.find('a.close').remove();
			$button.addClass('button').addClass('button-primary');
			$button.attr('style', 'margin-right:5px;');
			$button.click(function () {
				if (typeof data.nexturl === 'string' && data.nexturl !== '') {
					window.location = data.nexturl;
				}
				MAP.setPlugin(MAP.js_pointers[data.next].data);
			});
			has_next = false;
			if (MAP.hasNext(data)) {
				has_next_data = MAP.getPointerData(MAP.js_pointers[data.next].data);
				has_next = has_next_data.target && has_next_data.data;
			}
			if (has_next) {
				$button.html(MAP.next_label).appendTo($buttons);
			}
		}
	};

	$(MAP.pointers).each(function (index, pointer) { // loop pointers data
		if (!$().pointer) {
			return;
		} // do nothing if pointer plugin isn't available
		MAP.js_pointers[pointer.id] = {data: pointer};
		var $target = $(pointer.anchor_id);
		if (( $target.length && $target.is(':visible') ) || ( typeof pointer.where === 'string' && pointer.where !== '' )) { // anchor exists and is visible?
			MAP.visible_pointers.push(pointer.id);
			if (!MAP.first_pointer && ( $target.length && $target.is(':visible') )) {
				MAP.first_pointer = pointer;
			}
		} else {
			if (index !== ( MAP.pointers.length - 1 )) {
				MAP.pointers[index - 1].next = MAP.pointers[index + 1].id;
				MAP.pointers[index + 1].prev = MAP.pointers[index - 1].id;
			} else {
				MAP.pointers[index - 1].next = '';
			}
		}
		if (index === ( MAP.pointers.length - 1 ) && MAP.first_pointer) {
			$(document).trigger('MyAdminPointers.setup_done', MAP.first_pointer);
		}
	});

})(jQuery, RtGuideTourList); // MyAdminPointers is passed by `wp_localize_script`
