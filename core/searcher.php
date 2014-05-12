<?php

namespace MavenAlgolia\Core;

class Searcher  {

	private $apiKey = "";
	private $appId = "";
	private $client;
	private $postsFound = array();
	private $hitsPerPage = 0;
	private $nbHits = 0;
	private $nbPages = 0;
	
	private $searchResults = FALSE;
	

	/**
	 * 
	 * @param string $indexName
	 * @param string $apiKeySearch
	 * @param string $appId
	 * @throws \Exception
	 */
	public function __construct ( $appId = null, $apiKey = null ) {

	/**
	 * Help documentation
	 * https://github.com/algolia/algoliasearch-client-php#indexing-parameters
	 */		

		if ( !$apiKey || !$appId ) {
			throw new \Exception( 'Missing or Invalid credentials' );
		}
		
		$this->apiKey = $apiKey;
		$this->appId = $appId;
		$this->initClient();

		
		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'getPostsAlgolia' ) );
			add_action( 'found_posts', array( $this, 'getFoundPostsAlgolia' ), 1, 2 );
			add_filter( 'posts_search', array( $this, 'removeWPSearchSentence' ) );
			add_filter( 'post_limits', array( $this, 'setAlgoliaLimits' ) );
//			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );

		}
	}
	
	/**
	 * Initialize the Api client and assign it to the attribute
	 */
	function initClient ( ) {
		try {
			// initialize API Client & Index
			$this->client = new \AlgoliaSearch\Client( $this->getAppId(), $this->getApiKey() );
		} catch ( Exception $exc ) {
			// TODO: save this to show to the admin
			$error = $exc->getTraceAsString();
			throw $exc;
		}		
	}
	
	/**
	 * Execute the search in Algolia
	 * @param string $indexName
	 * @param string $query
	 * @param array $queryArgs
	 * @return array Results
	 * @throws \Exception
	 */
	public function search ( $indexName, $query, $queryArgs = array() ) {
		
		if ( ! $indexName  ) {
			throw new \Exception( 'Missing or Invalid Index Name' );
		}
		
		$query = sanitize_text_field( $query );
		
		
		$index = $this->client->initIndex( $indexName );
		
		$result = $index->search( $query , $queryArgs );
		
		return $result;
		
	}
	
	/**
	 * 
	 * @param string $indexName Algolia index name where to search
	 * @param string $query Search parameter
	 * @param array $queryArgs Arguments to filter/modify the search query
	 * @return array Results from Algolia
	 */
	public function frontEndSearch( $indexName, $query, $queryArgs = array() ) {
		
		if (get_query_var('paged')) {
			$paged = get_query_var('paged');
		} elseif (get_query_var('page')) {
			$paged = get_query_var('page');
		} elseif ($_GET && isset($_GET['paged']) && is_numeric($_GET['paged'])) {
			$paged = intval($_GET['paged']);
		}else{
			$paged = 1;
		}
		// Algolia starts in page 0 so we should reduce the page number we get from WP
		if( !empty( $paged ) ){
			$paged--;
		}
		
		$defaults = array( 
			'hitsPerPage' => get_option( 'posts_per_page', 10 ),
			'page' => $paged,
			'attributesToRetrieve' => NULL,
//			'queryType' => 'prefixLast'
			);
		
		$queryArgs = wp_parse_args( $queryArgs, $defaults );
		// initialize Index
		$index = $this->client->initIndex( $indexName );
		
		$result = $index->search( $query , $queryArgs );
		
		
		return $result;
	}
	
	/**
	 * Modify the wp query to search results in Algolia index instead WP database
	 * @param \WP_Query $wp_query
	 * @return void
	 */
	public function getPostsAlgolia( $wp_query ) {
			
		$this->searchResults = array();
		if( function_exists( 'is_main_query' ) && ! $wp_query->is_main_query() ) {
			return;
		}
		if( is_search() && ! is_admin() ) {
			$searchQuery = stripslashes( get_search_query( false ) );

			$args = array( 'attributesToRetrieve' => array( 'objectID' ) );

			try {
				$this->searchResults = $this->frontEndSearch( Registry::instance()->getDefaultIndex(), $searchQuery, $args );
			} catch( \Exception $e ) {
				// TODO: we could send and error by email to the admin or set an error db message and show it in the admin.
				// If we set a db error we should empty it when there are results or at least when the index name is changed in the admin
				$this->searchResults = array();
			}

			if( empty( $this->searchResults ) ) {
				return;
			}

			$algoliaRecords = $this->searchResults['hits'];

			foreach( $algoliaRecords as $postId ) {
				if( !empty( $postId['objectID'] ) ){
					$this->postsFound[] = (int)$postId['objectID'];
				}
			}

			$this->hitsPerPage = $this->searchResults['hitsPerPage'];
			$this->nbHits = $this->searchResults['nbHits'];
			$this->nbPages = $this->searchResults['nbPages'];
			$this->page = $this->searchResults['page'];
			set_query_var( 'post__in', $this->postsFound );
			set_query_var( 'orderby', 'post__in' );
		}

	}
	
	/**
	 * Overwrite WP posts found value by Algolia's one
	 * @param int $foundPosts
	 * @param \WP_Query $query
	 * @return int
	 */
	public function getFoundPostsAlgolia( $foundPosts, $query ) {
		if( is_search() && ! is_admin() ) {
			$foundPosts = $this->nbHits;
		}
		return $foundPosts;
	}
	
	/**
	 * Remove the WP search sentence from the query since we will set Posts Ids to get results
	 * @param string $search Current search sql sentence
	 * @return string New search sql sentence
	 */
	public function removeWPSearchSentence( $search ) {
		if( is_search() && ! is_admin() ) {
			$search = '';
		}
		return $search;
	}
	
	/**
	 * Overwrite the limit of values
	 * @param string $limit Limit sql sentence
	 * @return string New limit sql sentence
	 */
	public function setAlgoliaLimits( $limit ) {
		if( is_search() && ! is_admin() ){
			$limitOfPosts = $this->nbHits;
			if( $limitOfPosts > $this->hitsPerPage ){
				$limitOfPosts = $this->hitsPerPage;
			}
			$limit = "LIMIT 0, {$limitOfPosts}";
		}
		return $limit;
	}
	
	public function getApiKey () {
		return $this->apiKey;
	}

	public function getAppId () {
		return $this->appId;
	}

}
