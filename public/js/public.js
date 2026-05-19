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

	var $forms = $( '.ptai-search-form' );
	if ( ! $forms.length ) {
		return;
	}

	/* 1. Per-form AJAX submit handler
	------------------------------------------------------------ */
	// Use a delegated handler so each submission is scoped to the form
	// that fired it. This matters when a page renders multiple
	// [papertrail] shortcode instances — each library must search and
	// update only its own list.
	$forms.on( 'submit', function ( e ) {
		var $form = $( this );
		var $btn  = $form.find( '.ptai-search-btn' );
		var $list = findListForForm( $form );

		if ( ! $list || ! $list.length ) {
			// Nothing JS can update — let the browser submit normally.
			return;
		}

		e.preventDefault();

		var originalBtn = $btn.data( 'ptai-original-text' );
		if ( typeof originalBtn === 'undefined' ) {
			originalBtn = $btn.text();
			$btn.data( 'ptai-original-text', originalBtn );
		}

		var query    = $form.find( '#ptai-search-input, .ptai-search-input' ).first().val() || '';
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
					renderResults( $list, response.data.posts );
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

	/* 2. Locate the result list belonging to a given form
	------------------------------------------------------------ */
	function findListForForm( $form ) {
		var $library = $form.closest( '.ptai-document-library' );
		if ( $library.length ) {
			var $list = $library.find( '.ptai-file-list' );
			if ( ! $list.length ) {
				$list = $( '<ul class="ptai-file-list ptai-columns-1"></ul>' ).appendTo( $library );
			}
			return $list;
		}
		// Form is not inside a library wrapper — no JS render target.
		return $();
	}

	/* 3. Render results into the given list
	------------------------------------------------------------ */
	function renderResults( $list, posts ) {
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

	/* 4. Minimal XSS guard for JS-rendered output
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
