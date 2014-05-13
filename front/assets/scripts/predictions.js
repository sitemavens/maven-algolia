var mvnAlgoliaPrediction = (function($) {
	var algolia;
	var that;
	var self = {
		initialize: function() {
			that = this;
			if (typeof AlgoliaSearch !== "undefined") {
				this.initAlgolia();
			}
		},
		initAlgolia: function() {
			algolia = new AlgoliaSearch( mvnAlgSettings.appId, mvnAlgSettings.apiKeySearch ); // public credentials
		},
		searchCallback: function(success, content, response) {
			if ( success && content.results.length > 0 && that.lastQuery === content.results[0].query ) { // do not consider outdated answers
	      var data = [];
				var posts = content.results[0];
				if( posts.hits.length > 0 ){
					for (var i = 0; i < posts.hits.length; ++i) {
						var hit = posts.hits[i];
						data[i] = {
									label: hit.title,
									value: hit.title,
									title: (hit._highlightResult.title && hit._highlightResult.title.value) || hit.title,
									permalink: hit.permalink,
									categories: hit.category,
									tags: hit._tags,
									excerpt: (hit._highlightResult.excerpt && hit._highlightResult.excerpt.value) || hit.excerpt,
									description: (hit._highlightResult.content && hit._highlightResult.content.value) || hit.content,
									date: hit.date,
									featuredImage: hit.featuredImage
									};
					}
				}
        response(data);
			}
		},
		getDisplayPost: function( hit ) {
			var htmlPost = '';
			htmlPost += '			<a href="' + hit.permalink + '" class="mvn-alg-ls-item-title">';
			if( typeof hit.featuredImage !== 'undefined' && hit.featuredImage ){
				html += '	<img src="'+hit.featuredImage.file+'" width="40" height="60" />';
			}
			htmlPost += '				<strong>' + hit.title + '</strong>';

			if( typeof hit.categories !== 'undefined' ){
				htmlPost += '			<br /><span class="mvn-alg-ls-item-cats">' + hit.categories.join() + '</span>';
			}
			if( mvnAlgSearchVars.showExcerpt && typeof hit.excerpt !== 'undefined' && hit.excerpt ){
				htmlPost += '			<br /><span class="mvn-alg-ls-item-desc">' + hit.excerpt + '</span>';
			}
			htmlPost += '			</a>';
			return htmlPost;
		},
		search: function( request, response ) {
			if( typeof algolia !== 'undefined' ){
				algolia.startQueriesBatch();
				algolia.addQueryInBatch( mvnAlgSettings.indexName, request.term, {
					attributesToRetrieve: ['objectID', 'title', 'permalink', 'excerpt', 'content', 'date', 'featuredImage' , 'category', '_tags'],
					hitsPerPage: mvnAlgSearchVars.postsPerPage
				});
				algolia.sendQueriesBatch(function(success, content) {
					// forward 'response' to Algolia's callback in order to call it with up-to-date results
					that.lastQuery = request.term;
					that.searchCallback(success, content, response);
				});
			}
		}
	};
	return self;
})(jQuery);

jQuery(document).ready(function($) {
	mvnAlgoliaPrediction.initialize();
	
	// The autocomplete function is called on the input textbox with id input_element
	$("input[name='" + mvnAlgSearchVars.inputSearchName + "']").each(function(index){
			$(this).autocomplete({
			// minLength is the minimal number of input characters before starting showing
			// the autocomplete
			minLength: 1,
			source: mvnAlgoliaPrediction.search,
			// This function is executed when a suggestion is selected
			select: function(event, ui) {
			  // Sets the text of the input textbox to the title of the object referenced
			  // by the selected list item
			  $(this).val(ui.item.label);
			  return false;
			}
		  // Here we alter the standard behavior when rendering items in the list
		  }).data("ui-autocomplete")._renderItem = function(ul, item) {
			// ul is the unordered suggestion list
			// item is a object in the data object that was send to the response function
			// after the JSON request
			// We append a custom formatted list item to the suggestion list
			return $("<li></li>").data("item.autocomplete", item).append(mvnAlgoliaPrediction.getDisplayPost(item)).appendTo(ul);
		  };
	});
});