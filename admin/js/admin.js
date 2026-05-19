/* global ptaiAdmin, wp */
( function ( $, ptaiAdmin ) {
	'use strict';

	/* 1. Media picker — file attachment
	--------------------------------------------------------- */
	var strings = ( window.ptaiAdmin && window.ptaiAdmin.strings ) || {};

	function openMediaPicker() {
		var frame = wp.media( {
			title:    strings.select_file || 'Select or upload a file',
			button:   { text: strings.use_this_file || 'Use this file' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$( '#ptai_file_id' ).val( attachment.id );

			var $current = $( '#ptai-file-meta-current' );
			var ext      = ( attachment.filename || '' ).split( '.' ).pop().toUpperCase();
			$current.html(
				'<p><strong>' + $( '<div/>' ).text( attachment.filename || attachment.title || '' ).html() + '</strong></p>' +
				'<ul class="ptai-meta-list">' +
					'<li>Extension: ' + $( '<div/>' ).text( ext ).html() + '</li>' +
					'<li>Type: '      + $( '<div/>' ).text( attachment.mime || '' ).html() + '</li>' +
					( attachment.filesizeHumanReadable
						? '<li>Size: ' + $( '<div/>' ).text( attachment.filesizeHumanReadable ).html() + '</li>'
						: '' ) +
				'</ul>'
			);

			$( '#ptai-attach-file' ).text( strings.replace_file || 'Replace File' );
		} );

		frame.open();
	}

	function removeAttachedFile() {
		if ( ! window.confirm( strings.confirm_remove || 'Remove the attached file?' ) ) {
			return;
		}
		$( '#ptai_file_id' ).val( '0' );
		$( '#ptai-file-meta-current' ).html(
			'<p><em>' + ( strings.no_file_attached || 'No file attached.' ) + '</em></p>'
		);
		$( '#ptai-attach-file' ).text( strings.select_file || 'Attach File' );
		$( '#ptai-remove-file' ).remove();
	}

	$( function () {
		$( document ).on( 'click', '#ptai-attach-file', function ( e ) {
			e.preventDefault();
			if ( typeof wp === 'undefined' || ! wp.media ) {
				return;
			}
			openMediaPicker();
		} );

		$( document ).on( 'click', '#ptai-remove-file', function ( e ) {
			e.preventDefault();
			removeAttachedFile();
		} );
	} );

	/* 2. Character counter — AI Search Summary textarea
	--------------------------------------------------------- */
	var $summary = $( '#ptai_doc_summary' );
	if ( $summary.length ) {
		$summary.on( 'input', function () {
			var len      = $( this ).val().length;
			var $wrap    = $( this ).closest( '.ptai-doc-summary-wrap' );
			var $counter = $wrap.find( '.ptai-char-counter' );
			$wrap.find( '.ptai-char-count' ).text( len );
			$counter
				.toggleClass( 'ptai-char-counter--warning',
					len > 400 && len <= 500 )
				.toggleClass( 'ptai-char-counter--over', len > 500 );
		} ).trigger( 'input' );
	}

	/* 3. Dismiss AI active notice via AJAX
	--------------------------------------------------------- */
	$( document ).on(
		'click',
		'.ptai-ai-active-notice .notice-dismiss',
		function () {
			var nonce = $( this )
				.closest( '.ptai-ai-active-notice' )
				.data( 'nonce' );
			if ( ! nonce ) {
				return;
			}
			$.post( ptaiAdmin.ajax_url, {
				action: 'ptai_dismiss_ai_notice',
				nonce:  nonce
			} );
			// WP's native dismiss handler removes the element.
		}
	);

} ( jQuery, window.ptaiAdmin || {} ) );
