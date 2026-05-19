/**
 * PaperTrail AI — Admin scripts.
 *
 * @package PaperTrail_AI
 */
(function ($) {
	'use strict';

	var strings = (window.ptaiAdmin && window.ptaiAdmin.strings) || {};

	function openMediaPicker() {
		var frame = wp.media({
			title:    strings.select_file || 'Select or upload a file',
			button:   { text: strings.use_this_file || 'Use this file' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#ptai_file_id').val(attachment.id);

			var $current = $('#ptai-file-meta-current');
			var ext      = (attachment.filename || '').split('.').pop().toUpperCase();
			$current.html(
				'<p><strong>' + $('<div/>').text(attachment.filename || attachment.title || '').html() + '</strong></p>' +
				'<ul style="margin:0 0 0.75em 0;">' +
					'<li>Extension: ' + $('<div/>').text(ext).html() + '</li>' +
					'<li>Type: '      + $('<div/>').text(attachment.mime || '').html() + '</li>' +
					(attachment.filesizeHumanReadable
						? '<li>Size: ' + $('<div/>').text(attachment.filesizeHumanReadable).html() + '</li>'
						: '') +
				'</ul>'
			);

			$('#ptai-attach-file').text(strings.replace_file || 'Replace File');
		});

		frame.open();
	}

	function removeAttachedFile() {
		if (!window.confirm(strings.confirm_remove || 'Remove the attached file?')) {
			return;
		}
		$('#ptai_file_id').val('0');
		$('#ptai-file-meta-current').html(
			'<p><em>' + (strings.no_file_attached || 'No file attached.') + '</em></p>'
		);
		$('#ptai-attach-file').text(strings.select_file || 'Attach File');
		$('#ptai-remove-file').remove();
	}

	$(function () {
		$(document).on('click', '#ptai-attach-file', function (e) {
			e.preventDefault();
			if (typeof wp === 'undefined' || !wp.media) {
				return;
			}
			openMediaPicker();
		});

		$(document).on('click', '#ptai-remove-file', function (e) {
			e.preventDefault();
			removeAttachedFile();
		});
	});
})(window.jQuery);
