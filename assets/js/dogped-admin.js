/**
 * Dog Pedigree - Admin JavaScript.
 *
 * Handles repeater fields, media uploader, sortable sections, parent search,
 * and the custom dropdown field manager.
 */
(function ($) {
	'use strict';

	// =========================================================================
	// Repeater Fields
	// =========================================================================

	function syncRepeater($repeater) {
		var values = [];
		$repeater.find('.dogped-repeater-list .dogped-repeater-item input[type="text"]').each(function () {
			var v = $(this).val().trim();
			if (v) values.push(v);
		});
		$repeater.find('.dogped-repeater-value').val(JSON.stringify(values));
	}

	$('.dogped-repeater-list').sortable({
		handle: '.dogped-repeater-handle',
		placeholder: 'dogped-sortable-placeholder',
		update: function () {
			syncRepeater($(this).closest('.dogped-repeater'));
		}
	});

	$(document).on('click', '.dogped-repeater-add', function () {
		var $repeater = $(this).closest('.dogped-repeater');
		var $list = $repeater.find('.dogped-repeater-list');
		var placeholder = $list.data('placeholder') || '';

		var $item = $(
			'<li class="dogped-repeater-item">' +
				'<span class="dashicons dashicons-menu dogped-repeater-handle"></span>' +
				'<input type="text" value="" placeholder="' + placeholder + '" class="regular-text" />' +
				'<button type="button" class="button dogped-repeater-remove" title="Remove">&times;</button>' +
			'</li>'
		);

		$list.append($item);
		$item.find('input').focus();
		syncRepeater($repeater);
	});

	$(document).on('click', '.dogped-repeater-remove', function () {
		var $repeater = $(this).closest('.dogped-repeater');
		$(this).closest('.dogped-repeater-item').fadeOut(200, function () {
			$(this).remove();
			syncRepeater($repeater);
		});
	});

	$(document).on('input', '.dogped-repeater-item input[type="text"]', function () {
		syncRepeater($(this).closest('.dogped-repeater'));
	});

	// =========================================================================
	// Media Uploader
	// =========================================================================

	$(document).on('click', '#dogped-photo-upload', function (e) {
		e.preventDefault();
		var frame = wp.media({
			title: (typeof dogpedAdmin !== 'undefined' && dogpedAdmin.selectImage) || 'Select Dog Photo',
			button: { text: (typeof dogpedAdmin !== 'undefined' && dogpedAdmin.useImage) || 'Use this image' },
			multiple: false,
			library: { type: 'image' }
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$('#dogped_photo').val(att.id);
			$('#dogped-photo-preview').attr('src', att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url).show();
			$('#dogped-photo-remove').show();
		});
		frame.open();
	});

	$(document).on('click', '#dogped-photo-remove', function (e) {
		e.preventDefault();
		$('#dogped_photo').val('');
		$('#dogped-photo-preview').hide().attr('src', '');
		$(this).hide();
	});

	// =========================================================================
	// Sortable Sections
	// =========================================================================

	if ($('#dogped-sortable-sections').length) {
		$('#dogped-sortable-sections').sortable({
			placeholder: 'dogped-sortable-placeholder',
			update: function () {
				var order = [];
				$('#dogped-sortable-sections li').each(function () {
					order.push($(this).data('section'));
				});
				$('#dogped_section_order').val(JSON.stringify(order));
			}
		});
	}

	// =========================================================================
	// Parent Search Autocomplete
	// =========================================================================

	$('.dogped-parent-select').each(function () {
		var $select = $(this);
		var sex = $select.data('sex');
		var exclude = $select.data('exclude') || '';
		var searchTimeout;

		var $wrapper = $('<div class="dogped-parent-search-wrapper"></div>');
		var $input = $('<input type="text" class="regular-text dogped-parent-search-input" />')
			.attr('placeholder', dogpedAdmin.searchPlaceholder || 'Type to search for another dog');
		var $results = $('<ul class="dogped-parent-search-results"></ul>');

		$wrapper.append($input).append($results);
		$select.after($wrapper);

		function renderResults(data) {
			$results.empty();
			if (data.length) {
				data.forEach(function (item) {
					$results.append($('<li>').text(item.text).data('id', item.id).data('name', item.text));
				});
				$results.show();
			} else {
				$results.hide();
			}
		}

		// The dropdown above already lists the most recent dogs; this box exists to
		// reach the ones outside that window, so it searches the whole catalogue
		// from two characters on. The dog being edited is excluded server-side so
		// it can never be picked as its own parent.
		function runSearch(query) {
			$.ajax({
				url: dogpedAdmin.restUrl + 'search-parents',
				data: { s: query, sex: sex, exclude: exclude },
				beforeSend: function (xhr) { xhr.setRequestHeader('X-WP-Nonce', dogpedAdmin.searchNonce); },
				success: renderResults
			});
		}

		$input.on('input', function () {
			var query = $(this).val();
			clearTimeout(searchTimeout);
			if (query.length < 2) { $results.empty().hide(); return; }
			searchTimeout = setTimeout(function () { runSearch(query); }, 300);
		});

		// Bind to mousedown (fires before the input's blur) and preventDefault so
		// the search input keeps focus. A click binding would race against the
		// blur handler below: on a slower click or browser the delayed hide could
		// remove the result before the click registered, leaving the select on
		// "- None -" with the typed text stranded. This mirrors the Pro frontend
		// parent search, which already selects on mousedown.
		$results.on('mousedown', 'li', function (e) {
			e.preventDefault();

			var id = String($(this).data('id'));
			var name = $(this).data('name');

			// Add the dog only when it is not already one of the listed options,
			// so picking from the search box never empties the dropdown.
			var $existing = $select.find('option').filter(function () { return this.value === id; });
			if (! $existing.length) {
				$select.append($('<option>').val(id).text(name));
			}

			$select.val(id).trigger('change');
			$input.val('');
			$results.empty().hide();
		});

		// Clearing a parent is done by picking "- None -" in the dropdown; the
		// old double-click-to-clear gesture on this box would now wipe the
		// selection without any visible cue.
		$input.on('blur', function () { setTimeout(function () { $results.hide(); }, 200); });
	});

	// =========================================================================
	// Custom Dropdown Fields Manager
	// =========================================================================

	$(document).on('click', '#dogped-add-custom-field', function () {
		var label = $('#dogped-new-field-label').val().trim();
		if (!label) return;

		var slug = label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
		if (!slug) return;

		var $hidden = $('#dogped_custom_dropdown_fields');
		var fields = JSON.parse($hidden.val() || '[]');

		for (var i = 0; i < fields.length; i++) {
			if (fields[i].slug === slug) { alert('Field already exists.'); return; }
		}

		fields.push({ label: label, slug: slug });
		$hidden.val(JSON.stringify(fields));

		var $list = $('#dogped-custom-fields-list');
		if (!$list.length) {
			$('#dogped-custom-fields-manager').find('.description').first().before('<ul id="dogped-custom-fields-list" style="margin:8px 0;"></ul>');
			$list = $('#dogped-custom-fields-list');
		}

		var safe = $('<span>').text(label).html();
		var safeslug = $('<span>').text(slug).html();
		$list.append(
			'<li class="dogped-repeater-item" style="display:flex;align-items:center;gap:8px;margin:4px 0;">' +
			'<span class="dashicons dashicons-menu" style="color:#999;"></span>' +
			'<strong>' + safe + '</strong> ' +
			'<code style="font-size:11px;">dogped_custom_' + safeslug + '</code> ' +
			'<button type="button" class="button-link dogped-remove-custom-field" data-index="' + (fields.length - 1) + '" style="color:#b32d2e;">Remove</button>' +
			'</li>'
		);

		$('#dogped-new-field-label').val('');
	});

	$(document).on('click', '.dogped-remove-custom-field', function () {
		var idx = $(this).data('index');
		var $hidden = $('#dogped_custom_dropdown_fields');
		var fields = JSON.parse($hidden.val() || '[]');
		fields.splice(idx, 1);
		$hidden.val(JSON.stringify(fields));
		$(this).closest('li').remove();

		$('#dogped-custom-fields-list li').each(function (i) {
			$(this).find('.dogped-remove-custom-field').data('index', i);
		});
	});

})(jQuery);
