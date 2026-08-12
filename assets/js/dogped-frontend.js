/**
 * Dog Pedigree - Frontend JavaScript.
 *
 * Auto-submits the catalog filter form on select change.
 */
(function () {
	'use strict';

	document.querySelectorAll('.dogped-filters__select').forEach(function (select) {
		select.addEventListener('change', function () {
			this.closest('form').submit();
		});
	});
})();
