/* global jQuery, ptaiPublic */
/**
 * PaperTrail AI — Public scripts.
 *
 * Handles AJAX search submission for the front-end search bar.
 *
 * @package PaperTrail_AI
 */
( function ( $, ptaiPublic ) {
	'use strict';

	if ( typeof ptaiPublic === 'undefined' || ! ptaiPublic ) {
		return;
	}

	/* 1. AJAX search form handler
	------------------------------------------------------------ */
	var $form = $( '.ptai-search-form' );
	if ( ! $form.length ) {
		return;
	}

	var $btn          = $form.find( '.ptai-search-btn' );
	var originalBtn   = $btn.text();
	var $library      = $form.closest( '.ptai-document-library' );
	var $list;

	if ( $library.length ) {
		$list = $library.find( '.ptai-file-list' );
		if ( ! $list.length ) {
			$list = $( '<ul class="ptai-file-list ptai-columns-1"></ul>' ).appendTo( $library );
		}
	} else {
		$list = $( '.ptai-file-list' ).first();
	}

	$form.on( 'submit', function ( e ) {
		if ( ! $list || ! $list.length ) {
			return; // Let the form submit normally — no JS target.
		}

		e.preventDefault();

		var query    = $form.find( '#ptai-search-input' ).val() || '';
		var category = $form.find( '[name="ptai_category"]' ).val() || 0;
		var mode     = $form.find( '[name="ptai_mode"]' ).val() || 'auto';

		$btn.prop( 'disabled', true ).text( ptaiPublic.strings.searching );

		$.post( ptaiPublic.ajax_url, {
			action:   'ptai_search',
			nonce:    ptaiPublic.nonce,
			query:    query,
			category: category,
			page:     1,
			mode:     mode
		} )
			.done( function ( response ) {
				if ( response && response.success && response.data && response.data.posts && response.data.posts.length ) {
					renderResults( response.data.posts );
				} else {
					$list.html(
						'<li class="ptai-no-results">' +
						escHtml( ptaiPublic.strings.no_results ) +
						'</li>'
					);
				}
			} )
			.fail( function () {
				$list.html(
					'<li class="ptai-no-results">' +
					escHtml( ptaiPublic.strings.error ) +
					'</li>'
				);
			} )
			.always( function () {
				$btn.prop( 'disabled', false ).text( originalBtn || ptaiPublic.strings.search );
			} );
	} );

	/* 2. Render results
	------------------------------------------------------------ */
	function renderResults( posts ) {
		var html = '';
		$.each( posts, function ( i, post ) {
			html +=
				'<li class="ptai-file-card">' +
					'<div class="ptai-file-icon"></div>' +
					'<div class="ptai-file-info">' +
						'<a class="ptai-file-title" href="' + escHtml( post.permalink ) + '">' +
							escHtml( post.title ) +
						'</a>' +
						'<span class="ptai-file-meta">' +
							escHtml( post.file_type || '' ) + ' &middot; ' +
							escHtml( post.file_size || '' ) + ' &middot; ' +
							escHtml( String( post.downloads || 0 ) ) + ' ' +
							escHtml( ptaiPublic.strings.download.toLowerCase() ) + 's' +
						'</span>' +
						'<a class="ptai-download-link" href="' + escHtml( post.download_url ) + '">' +
							escHtml( ptaiPublic.strings.download ) +
						'</a>' +
					'</div>' +
				'</li>';
		} );
		$list.html( html );
	}

	/* 3. Minimal XSS guard for JS-rendered output
	------------------------------------------------------------ */
	function escHtml( str ) {
		if ( typeof str !== 'string' ) {
			return '';
		}
		return str
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' );
	}

}( jQuery, window.ptaiPublic || null ) );
