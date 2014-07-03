	/**
	 * Array of posts types to index
	 * @type @exp;mvnAlgVars@pro;postTypesToIndex
	 */
	var postTypesToIndex = mvnAlgVars.postTypesToIndex || {};
	/**
	 * Array of taxonomies types to index
	 * @type @exp;mvnAlgVars@pro;taxonomyTypesToIndex
	 */
	var taxonomyTypesToIndex = mvnAlgVars.taxonomyTypesToIndex || {};
	/**
	 * Array of post types with theirs total posts to index
	 * @type @exp;mvnAlgVars@pro;totalPublishedPosts
	 */
	var totalPublishedPosts =  mvnAlgVars.totalPublishedPosts;
	/**
	 * Number of total non publish posts
	 * @type @exp;mvnAlgVars@pro;totalNonPublishedPosts
	 */
	var totalNonPublishedPosts =  mvnAlgVars.totalNonPublishedPosts;
	/**
	 * Count total posts indexed
	 * @type Number
	 */
	var totalPostsIndexed = 0;
	/**
	 * Count total posts indexed by post type
	 * @type Array
	 */
	var totalPostsPerTypeIndexed = {};
	/**
	 * Count total posts removed
	 * @type Number
	 */
	var totalPostsRemoved = 0;
	/**
	 * Count total terms indexed
	 * @type Number
	 */
	var totalTermsIndexed = 0;
	/**
	 * Count total terms indexed by taxonomy type
	 * @type Array
	 */
	var totalTermsPerTypeIndexed = {};
	
	var indexPosts = function ( postType, offset ){
		offset = offset || 0;
		var data = { indexPostType: postType, action: mvnAlgVars.ajaxIndexAction, queryOffset: offset, runIndex: 1, _ajax_nonce_index: mvnAlgVars.ajaxIndexNonce };
		showProgress( postType, totalPublishedPosts[postType], offset, mvnAlgVars.labels.indexing + mvnAlgVars.labels.postsLabels[postType] );
		jQuery.ajax({
				url: mvnAlgVars.ajaxUrl,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response, textStatus) {
					
					if(typeof(response) === 'object' && response.error === true){
						showError( data.mvnAlgErrorMessage );
					}else if( typeof(response) === 'object' && response !== null )
					{
						if ( response['totalIndexed'] ) {
							totalPostsIndexed += parseInt( response['totalIndexed'] );
							totalPostsPerTypeIndexed[postType] += parseInt( response['totalIndexed'] );
						}
						// If something was indexed try with the next page
						if ( parseInt( response['totalIndexed'] ) > 0 ) {
							indexPosts( postType, parseInt( offset ) + parseInt( mvnAlgVars.postsPerPage ) );
						} else {					
							var nextPostToIndex = getNextPostTypeToIndex( postType );
							if( nextPostToIndex ){
								indexPosts( nextPostToIndex, 0 );
							}else{
								// If there is no more post types to index
								// Move index from the TMP to the Original one
								//moveIndex();
								var taxToIndex = getTaxonomyTypeToIndex( 0 );
								if( taxToIndex ){
									// Continue with Taxonomies indexation
									indexTaxonomies( getTaxonomyTypeToIndex( 0 ), 0 );
								}else{
									// Continue removing posts from the index
									removePosts( 0 );
								}
							}
						}
					}else{
						showError( mvnAlgVars.labels.indexationError );
					}					
				},
				error: function(jqXHR, textStatus, errorThrown) {
					try {
						errorMsg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						errorMsg = jqXHR.responseText;
					}
					showError(errorMsg);
				}
			}
		);
	};
	var indexTaxonomies = function ( taxonomyType, offset ){
		offset = offset || 0;
		var data = { indexTaxonomyType: taxonomyType, action: mvnAlgVars.ajaxIndexTaxonomyAction, queryOffset: offset, runIndexTaxonomy: 1, _ajax_nonce_indexTaxonomy: mvnAlgVars.ajaxIndexTaxonomyNonce };
		showProgress( taxonomyType, 1, offset, mvnAlgVars.labels.taxonomyLabels[taxonomyType] );
		jQuery.ajax({
				url: mvnAlgVars.ajaxUrl,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response, textStatus) {
					
					if(typeof(response) === 'object' && response.error === true){
						showError( data.mvnAlgErrorMessage );
					}else if( typeof(response) === 'object' && response !== null )
					{
						if ( response['totalIndexed'] ) {
							totalTermsIndexed += parseInt( response['totalIndexed'] );
							totalTermsPerTypeIndexed[taxonomyType] += parseInt( response['totalIndexed'] );
						}
						// If something was indexed try with the next page
						if ( parseInt( response['totalIndexed'] ) > 0 ) {
							indexTaxonomies( taxonomyType, parseInt( offset ) + parseInt( mvnAlgVars.postsPerPage ) );
						} else {						
							var nextTaxonomyToIndex = getNextTaxonomyTypeToIndex( taxonomyType );
							if( nextTaxonomyToIndex ){
								// Show the 100% of current taxonomy since we don't have the totals to index
								showProgress( taxonomyType, 100, 100, mvnAlgVars.labels.taxonomyLabels[taxonomyType] );
								indexTaxonomies( nextTaxonomyToIndex, 0 );
							}else{
								// If there is no more post types to index
								// Move index from the TMP to the Original one
								//moveIndex();
								showProgress( taxonomyType, 100, 100, mvnAlgVars.labels.taxonomyLabels[taxonomyType] );
								removePosts( 0 );
							}
						}
					}else{
						showError( mvnAlgVars.labels.indexationError );
					}					
				},
				error: function(jqXHR, textStatus, errorThrown) {
					try {
						errorMsg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						errorMsg = jqXHR.responseText;
					}
					showError(errorMsg);
				}
			}
		);
	};
	var removePosts = function ( offset ){
		offset = offset || 0;
		showProgress( 'remove', totalNonPublishedPosts, offset, mvnAlgVars.labels.removing );
		var data = { action: mvnAlgVars.ajaxRemoveAction, queryOffset: offset, runRemoveIndex: 1, _ajax_nonce_remove: mvnAlgVars.ajaxRemoveNonce };
		jQuery.ajax({
				url: mvnAlgVars.ajaxUrl,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response, textStatus) {
					
					if(typeof(response) === 'object' && response.error === true){
						showError( data.mvnAlgErrorMessage );
					}else if( typeof(response) === 'object' && response !== null )
					{
						
						// If something was indexed try with the next page
						if ( parseInt( response['totalRemoved'] ) > 0 ) {
							totalPostsRemoved += parseInt( response['totalRemoved'] );
							removePosts( parseInt( offset ) + parseInt( mvnAlgVars.postsPerPageToRemove ) );
						} else {
							processFinished();
						}
					}else{
						showError( mvnAlgVars.labels.indexationError );
					}					
				},
				error: function(jqXHR, textStatus, errorThrown) {
					try {
						errorMsg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						errorMsg = jqXHR.responseText;
					}
					showError(errorMsg);
				}
			}
		);
	};
	var moveIndex = function ( ){
		showProgress( 'move', 100, 0, mvnAlgVars.labels.removing );
		var data = { action: mvnAlgVars.ajaxMoveAction, runMoveIndex: 1, _ajax_nonce_move: mvnAlgVars.ajaxMoveNonce };
		jQuery.ajax({
				url: mvnAlgVars.ajaxUrl,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response, textStatus) {
					
					if(typeof(response) === 'object' && response.error === true){
						showError( data.mvnAlgErrorMessage );
					}else if( typeof(response) === 'object' && response !== null )
					{
						showProgress( 'move', 100, 100, mvnAlgVars.labels.removing );
					}else{
						showError( mvnAlgVars.labels.indexationError );
					}					
				},
				error: function(jqXHR, textStatus, errorThrown) {
					try {
						errorMsg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						errorMsg = jqXHR.responseText;
					}
					showError(errorMsg);
				}
			}
		);
	};
	var validateIndex = function ( ){
		var data = { 
					action: mvnAlgVars.ajaxValidateIndexAction, 
					_ajax_nonce_validateIndex: mvnAlgVars.ajaxValidateIndexNonce,
					appId: jQuery('#mvnAlg_appId').val(),
					apiKey: jQuery('#mvnAlg_apiKey').val(),
					indexName: jQuery('#mvnAlg_defaultIndex').val()
				};
		jQuery.ajax({
				url: mvnAlgVars.ajaxUrl,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response, textStatus) {
					if(typeof(response) === 'object' && response.error === true){
						showError( data.mvnAlgErrorMessage );
						indexationSectionHide();
					}else if( typeof(response) === 'object' && response !== null )
					{
						showError( 'OK' );
						indexationSectionShow();
					}			
				},
				error: function(jqXHR, textStatus, errorThrown) {
					try {
						errorMsg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						errorMsg = jqXHR.responseText;
					}
					showError(errorMsg);
					indexationSectionHide();
				}
			}
		);
	};
	var validateCredentials = function ( ){
		var data = { 
					action: mvnAlgVars.ajaxValidateAction, 
					_ajax_nonce_validate: mvnAlgVars.ajaxValidateNonce,
					appId: jQuery('#mvnAlg_appId').val(),
					apiKey: jQuery('#mvnAlg_apiKey').val(),
					apiSearchKey: jQuery('#mvnAlg_apiKeySearch').val()
				};
		jQuery.ajax({
				url: mvnAlgVars.ajaxUrl,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response, textStatus) {
					if(typeof(response) === 'object' && response.error === true){
						showError( data.mvnAlgErrorMessage );
						indexationSectionHide();
					}else if( typeof(response) === 'object' && response !== null )
					{
						showError( 'OK' );
						indexationSectionShow();
					}			
				},
				error: function(jqXHR, textStatus, errorThrown) {
					try {
						errorMsg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						errorMsg = jqXHR.responseText;
					}
					showError(errorMsg);
					indexationSectionHide();
				}
			}
		);
	};
	var processFinished = function ( ){
		// Indexation is finished so we need to enable the index button
		jQuery('#mvnAlg_index').removeAttr("disabled");
		spinnerHide();
	};

function progress( percent, elementId ) {
	percent =  Math.floor( percent );
	jQuery( elementId ).progressbar( "value", percent );
}

function showProgress( type, totalItems, offset, label ) {
	var pbarId = '#progressBar_' + type;
	var pbarHtmlId = 'progressBar_' + type;
	if( offset === 0 ){
		var progressBar = '<li>' + label + '<br> <div id="'+pbarHtmlId+'"><div id="'+pbarHtmlId+'Label" class="progress-label">' + mvnAlgVars.labels.starting + '</div></div></li>';
		jQuery( "#mvn-alg-index-result" ).append( progressBar );
		var progressBarObj = jQuery( pbarId );
		var progressLabel = jQuery( pbarId + 'Label' );

		progressBarObj.progressbar({
									value: false,
									change: function() {
										progressLabel.text( progressBarObj.progressbar( "value" ) + "%" );
									},
									complete: function() {
										progressLabel.text( mvnAlgVars.labels.complete );
									}
								});
	}
	var percetange =  0;
	
	if( offset >= totalItems ){
		percetange = 100; 
	}else{
		percetange =  ( ( offset * 100 ) / totalItems );
	}
	progress( percetange, pbarId );
}

function showError( message , postType ){
	jQuery( "#mvn-alg-index-error" ).empty().append( message );
}

function clearMessages(){
	jQuery( "#mvn-alg-index-error" ).empty();
	jQuery( "#mvn-alg-index-result" ).empty();
}

function indexationSectionShow(){
	jQuery( ".index-action-row" ).show();
}
function indexationSectionHide(){
	jQuery( ".index-action-row" ).hide();
}

function spinnerShow(){
	jQuery( ".spinner" ).show();
}
function spinnerHide(){
	jQuery( ".spinner" ).hide();
}


function getNextPostTypeToIndex( currentType ){
	var index = jQuery.inArray( currentType, postTypesToIndex );
	if( index !== -1 ){
		index += 1;
		if( index < postTypesToIndex.length ){
			return getPostTypeToIndex( index );
		}
	}
	return '';
}


function getPostTypeToIndex( index ){
	if( index >= 0 ){
		if( postTypesToIndex.length > 0 && typeof postTypesToIndex[index] !== 'undefined' ){
			return postTypesToIndex[index];
		}
	}
	return '';
}

function getNextTaxonomyTypeToIndex( currentType ){
	var index = jQuery.inArray( currentType, taxonomyTypesToIndex );
	if( index !== -1 ){
		index += 1;
		if( index < taxonomyTypesToIndex.length ){
			return getTaxonomyTypeToIndex( index );
		}
	}
	return '';
}

function getTaxonomyTypeToIndex( index ){
	if( index >= 0 ){
		if( taxonomyTypesToIndex.length > 0 && typeof taxonomyTypesToIndex[index] !== 'undefined' ){
			return taxonomyTypesToIndex[index];
		}
	}
	return '';
}

(function($) {
	
	$(document).ready(function(){
		
		jQuery("#mvnAlgtabs").tabs();
		
		jQuery('#mvnAlg_validate' ).on('click', function(e){
			validateCredentials();
			jQuery('.algolia-validate-spinner').show();
		});
		
		jQuery('#mvnAlg_validateIndex' ).on('click', function(e){
			validateIndex();
			jQuery('.algolia-validate-index-spinner').show();
		});
		
		jQuery('#mvnAlg_defaultIndex' ).on('change', function(e){
			clearMessages();
			indexationSectionHide();
			if( jQuery( '.mvn-alg-set-index-name' ).length <= 0 ){
				jQuery( '.index-action-row.index-messages' ).after('<tr class="mvn-alg-set-index-name"><td colspan="2"><p>' + mvnAlgVars.labels.indexNameChanged + '</p></td></tr>');
			}
		});
		
		jQuery('#mvnAlg_index').on( 'click', function(e){
			e.preventDefault();
			clearMessages();
			
			jQuery('#mvnAlg_index').attr("disabled", true);
			jQuery("#mvn-alg-index-result").append(
				'<li><strong>' + mvnAlgVars.labels.running + '</strong></li>'
			);
			jQuery('.algolia-action-button').width( jQuery('#mvnAlg_index').outerWidth() + 30 );
			spinnerShow();
			
			var firstIndex = getPostTypeToIndex( 0 );
		
			if( firstIndex ){
				indexPosts( firstIndex );
			}else{
				firstIndex = getTaxonomyTypeToIndex( 0 );
				if( firstIndex ){
					indexTaxonomies( firstIndex );
				}else{
					removePosts( 0 );
				}
			}
		} );
	});
})(jQuery);