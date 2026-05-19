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

	/* 4. Settings page tabs
	--------------------------------------------------------- */
	$( function () {
		var $wrap    = $( '.ptai-settings-wrap' );
		var $tabNav  = $wrap.find( '.ptai-tab-nav' );
		var $form    = $wrap.find( '.ptai-settings-main form' );
		var $tabBtns = $tabNav.find( '.ptai-tab-btn' );
		if ( ! $tabNav.length || ! $form.length || ! $tabBtns.length ) {
			return;
		}

		var $headings      = $form.children( 'h2' );
		var $staticPanels  = $wrap.find( '.ptai-tab-panel' );

		// If the count of form-generated sections plus any pre-existing
		// static panels doesn't equal the number of tab buttons (e.g.
		// another plugin injected a settings section), bail out: the nav
		// stays hidden via CSS and the form renders flat.
		if ( $headings.length + $staticPanels.length !== $tabBtns.length ) {
			return;
		}

		// Wrap each section heading + its following siblings (up to the
		// next h2 or the .submit row) into a tab panel whose id matches
		// the corresponding tab button's data-tab. The .submit row is
		// intentionally left outside so the save button stays visible.
		$headings.each( function ( i ) {
			var $btn   = $tabBtns.eq( i );
			var tabId  = $btn.data( 'tab' );
			if ( ! tabId ) {
				return;
			}

			var $h     = $( this );
			var $group = $h.nextUntil( 'h2, .submit' );

			// Give the button a stable id so aria-labelledby can point at it.
			var btnId = $btn.attr( 'id' );
			if ( ! btnId ) {
				btnId = tabId + '-tab';
				$btn.attr( 'id', btnId );
			}

			var $panel = $( '<div></div>' )
				.attr( 'id', tabId )
				.attr( 'role', 'tabpanel' )
				.attr( 'aria-labelledby', btnId )
				.addClass( 'ptai-tab-panel' );

			$h.before( $panel );
			$panel.append( $h ).append( $group );

			if ( 0 === i ) {
				$panel.addClass( 'ptai-tab-panel--active' );
			}
		} );

		// Roving tabindex — only the active tab is in the tab order.
		$tabBtns.attr( 'tabindex', '-1' );
		$tabBtns.filter( '.ptai-tab-btn--active' ).attr( 'tabindex', '0' );

		// Reveal the nav now that the panels are wired up.
		$wrap.addClass( 'ptai-tabs-ready' );

		function activateTab( $btn, focus ) {
			if ( ! $btn || ! $btn.length ) {
				return;
			}
			var target = $btn.data( 'tab' );
			if ( ! target ) {
				return;
			}

			$tabBtns
				.removeClass( 'ptai-tab-btn--active' )
				.attr( 'aria-selected', 'false' )
				.attr( 'tabindex', '-1' );
			$btn.addClass( 'ptai-tab-btn--active' )
				.attr( 'aria-selected', 'true' )
				.attr( 'tabindex', '0' );

			$wrap.find( '.ptai-tab-panel' )
				.removeClass( 'ptai-tab-panel--active' );
			$wrap.find( '#' + target )
				.addClass( 'ptai-tab-panel--active' );

			if ( focus ) {
				$btn.trigger( 'focus' );
			}
		}

		$tabNav.on( 'click', '.ptai-tab-btn', function ( e ) {
			e.preventDefault();
			activateTab( $( this ), false );
		} );

		// WAI-ARIA tablist keyboard support: Left/Right cycle, Home/End jump.
		$tabNav.on( 'keydown', '.ptai-tab-btn', function ( e ) {
			var key = e.key;
			if (
				'ArrowLeft'  !== key &&
				'ArrowRight' !== key &&
				'Home'       !== key &&
				'End'        !== key
			) {
				return;
			}
			e.preventDefault();
			var index = $tabBtns.index( this );
			var last  = $tabBtns.length - 1;
			var next;
			if ( 'ArrowLeft' === key ) {
				next = 0 === index ? last : index - 1;
			} else if ( 'ArrowRight' === key ) {
				next = index === last ? 0 : index + 1;
			} else if ( 'Home' === key ) {
				next = 0;
			} else {
				next = last;
			}
			activateTab( $tabBtns.eq( next ), true );
		} );
	} );

} ( jQuery, window.ptaiAdmin || {} ) );
