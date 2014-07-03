<?php

namespace MavenAlgolia\Admin\Controllers;
use \MavenAlgolia\Core;
use \MavenAlgolia\Core\Registry;

class Settings {

	private static $instance;
	public $postsPerPageToIndex = 20;
	public $postsPerPageToRemove = 500;
	const settingsField = 'mvnAlgSettings';
	const updateAction = 'mvnAlgUpdateSettings';
	const ajaxIndexAction = 'mvnAlgAjaxIndex';
	const ajaxIndexTaxonomyAction = 'mvnAlgAjaxIndexTaxonomy';
	const ajaxRemoveAction = 'mvnAlgAjaxRemove';
	const ajaxMoveAction = 'mvnAlgAjaxMove';
	const ajaxValidateAction = 'mvnAlgAjaxValidate';
	const ajaxValidateIndexAction = 'mvnAlgAjaxValidate';
	
	/**
	 * Constructor. Initialize all the hooks
	 */
	public function __construct() {
		add_action( 'admin_init', 'MavenAlgolia\Admin\Controllers\Settings::adminInit');
		add_action( 'admin_menu', 'MavenAlgolia\Admin\Controllers\Settings::adminMenu');
		add_action( 'admin_notices', 'MavenAlgolia\Admin\Controllers\Settings::adminNotices' );
		add_action( 'wp_ajax_' . Settings::ajaxIndexAction , array( &$this, 'ajaxIndex' ), 10, 1);
		add_action( 'wp_ajax_' . Settings::ajaxIndexTaxonomyAction , array( &$this, 'ajaxIndexTaxonomy' ), 10, 1);
		add_action( 'wp_ajax_' . Settings::ajaxRemoveAction , array( &$this, 'ajaxRemoveFromIndex' ), 10, 1);
		add_action( 'wp_ajax_' . Settings::ajaxMoveAction , array( &$this, 'ajaxMoveIndex' ), 10, 1);
		add_action( 'wp_ajax_' . Settings::ajaxValidateAction, array( &$this, 'ajaxValidateCredentials' ), 10, 1);
		add_action( 'wp_ajax_' . Settings::ajaxValidateIndexAction, array( &$this, 'ajaxValidateIndex' ), 10, 1);
	}
	
	/**
	 * 
	 * @return \MavenAlgolia\Core\Registry
	 */
	static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( );
		}
		return self::$instance;
	}
	
	/**
	 * Method executed on WP admin init hook
	 */
	public static function adminInit(){
		// If we need to update the options
		if ( !empty( $_POST ) && count( $_POST ) > 0 && isset( $_POST['mvnAlg_action'] ) &&  $_POST['mvnAlg_action'] == self::updateAction  ) {
			Settings::instance()->updateOptions();
		}
		add_action( 'admin_enqueue_scripts', array( Settings::instance(), 'enqueueScripts' ) );
	}
	
	/**
	 * Enqueue the admin scripts
	 * @param string $hook Page where it was called
	 * @return void
	 */
	public function enqueueScripts($hook) {
		if( $hook != 'toplevel_page_mvnAlg_general_settings' ){
			return;
		}
			$jspath = Registry::instance()->getPluginUrl() . "admin/assets/scripts/mvn-alg-functions.js";
			$csspath = Registry::instance()->getPluginUrl() . "admin/assets/styles/settings.css";

			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'mavenAlgoliaSettings', $jspath, array( 'jquery', 'jquery-ui-progressbar' ), Registry::instance()->getPluginVersion() );
			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_style( 'mavenAlgoliaSettings', $csspath, array(), Registry::instance()->getPluginVersion() );
			$adminUrl = admin_url();
			$homeUrl = home_url(null, is_ssl() ? 'https' : 'http');
			
			$postTypesToIndex = Core\FieldsHelper::getPostTypesToIndex();
			$postLabels = Core\FieldsHelper::getPostTypesLabels( $postTypesToIndex );
			$taxonomiesToIndex = (Registry::instance()->indexTaxonomies()) ? Core\FieldsHelper::getTaxonomiesToIndex() : array();
			$taxonomyLabels = Core\FieldsHelper::getTaxonomyLabels();
			
			
			$args = array( 
					'siteUrl' => $homeUrl, 
					'ajaxUrl' => sprintf('%s%s',$adminUrl, "admin-ajax.php"),
					'ajaxIndexAction' => Settings::ajaxIndexAction,
					'ajaxIndexNonce' => wp_create_nonce( Settings::ajaxIndexAction ),
					'ajaxIndexTaxonomyAction' => Settings::ajaxIndexTaxonomyAction,
					'ajaxIndexTaxonomyNonce' => wp_create_nonce( Settings::ajaxIndexTaxonomyAction ),
					'ajaxRemoveAction' => Settings::ajaxRemoveAction,
					'ajaxRemoveNonce' => wp_create_nonce( Settings::ajaxRemoveAction ),
					'ajaxMoveAction' => Settings::ajaxMoveAction,
					'ajaxMoveNonce' => wp_create_nonce( Settings::ajaxMoveAction ),
					'ajaxValidateAction' => Settings::ajaxValidateAction ,
					'ajaxValidateNonce' => wp_create_nonce( Settings::ajaxValidateAction  ),
					'ajaxValidateIndexAction' => Settings::ajaxValidateIndexAction ,
					'ajaxValidateIndexNonce' => wp_create_nonce( Settings::ajaxValidateIndexAction  ),
					'postsPerPage' => $this->postsPerPageToIndex,
					'postsPerPageToRemove' => $this->postsPerPageToRemove,
					'postTypesToIndex' => array_keys( $postTypesToIndex ),
					'indexTaxonomies' => (int)Registry::instance()->indexTaxonomies(), // Should be an Integer 0 | 1
					'taxonomyTypesToIndex' => array_keys( $taxonomiesToIndex ),
					'totalPublishedPosts' => self::getTotalPublishedPosts(),
					'totalNonPublishedPosts' => self::getTotalNonPublishedPosts(),
					'labels' => array( 
										'indexationError' => 'There was an error trying to run indexation, please contact to the support team.',
										'starting' => 'Starting...',
										'indexing' => 'Indexing ',
										'complete' => 'Completed!',
										'running' => "We're indexing your content and sending it to Algolia. Hang tight - it could take several minutes!",
										'removing' => 'Removing unpublish posts from the index',
										'postsLabels' => $postLabels,
										'taxonomyLabels' => $taxonomyLabels,
										'indexNameChanged' => 'Index name was changed. Please save changes to index content.'
										),
				);
			wp_localize_script( 'mavenAlgoliaSettings', 'mvnAlgVars', $args, Registry::instance()->getPluginVersion() );
	}
	
	/**
	 * Show maven algolia item in the sidebar menu
	 */
	public static function adminMenu(){
		$registry = Registry::instance();
		$iconUrl = $registry->getPluginUrl()."admin/assets/images/icon.png";
		add_menu_page(__( 'Maven Algolia Settings', 'mvnAlg' ), __( 'Maven Algolia', 'mvnAlg' ), 'manage_options', Registry::instance()->getPluginShortName().'_general_settings', 'MavenAlgolia\Admin\Controllers\Settings::showForm',$iconUrl );
	}

	/**
	 * Load the settings form when settings page is called
	 */
	public function showForm() {
		include Registry::instance()->getPluginDir() . "admin/views/settings.php";
	}

	/**
	 * Set the admin notices if they are neccesary
	 */
	public static function adminNotices() {
		if( !empty($_REQUEST['mvnAlgMessage']) && $_REQUEST['mvnAlgMessage'] === 'settingsUpdated' ):
	?>
		<div class="updated">
			<p><?php _e( 'Settings were updated.', \MavenAlgolia\Core\Registry::instance()->getPluginShortName() ); ?></p>
		</div>
	<?php
		elseif( !empty($_REQUEST['mvnAlgMessage']) && $_REQUEST['mvnAlgMessage'] === 'settingsNotUpdated' ):
	?>
		<div class="error">
			<p><?php _e( 'Settings were NOT updated, please try again.', \MavenAlgolia\Core\Registry::instance()->getPluginShortName() ); ?></p>
		</div>
	<?php
		endif;
		if( 
			Registry::instance()->isValidApp() &&  ! Registry::instance()->isValidAppSearch() ):
	?>
		<div class="error">
			<p><?php _e( 'Please go to the Maven Algolia section and set a valid "Api key for search only" to enable the Maven Algolia search in your site. <a href="admin.php?page=mvnAlg_general_settings">View Details</a>', Registry::instance()->getPluginShortName() ); ?></p>
		</div>
	<?php
		endif;
		if( 
			! Registry::instance()->getAppId() 
				|| ! Registry::instance()->getApiKey() 
				||	( Registry::instance()->getAppId() && Registry::instance()->getApiKey() &&  ! Registry::instance()->isValidApp() ) ):
	?>
		<div class="error">
			<p><?php _e( 'Please go to the Maven Algolia section to enable the Maven Algolia module to work. <a href="admin.php?page=mvnAlg_general_settings">View Details</a>', Registry::instance()->getPluginShortName() ); ?></p>
		</div>
	<?php
		endif;
	}
	
	/**
	 * Run the indexation called from ajax action
	 */
	public function ajaxIndex() {
		check_ajax_referer( Settings::ajaxIndexAction, '_ajax_nonce_index');
		
		if( !empty( $_POST['runIndex'] ) )
		{
			$errorMessage = '';
			$error = FALSE;
			$totalIndexed = 0;
			if( Registry::instance()->isValidApp() ){
				$indexPostType = !empty( $_POST['indexPostType'] ) ? sanitize_text_field( $_POST['indexPostType'] ) : 0;
				$offset = !empty( $_POST['queryOffset'] ) ? (int)$_POST['queryOffset'] : 0;


				
				$postTypesToIndex = Core\FieldsHelper::getPostTypesObject();
				if( $postTypesToIndex && is_array( $postTypesToIndex ) && isset( $postTypesToIndex[$indexPostType] ) ){
	//				while ( !$error && list ( $postTypeKey, $postType) = each ( $postTypesToIndex ) ) {
						$postType = $postTypesToIndex[$indexPostType];
						try {
							// Init the index
							$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
							// indexing in a tmp index to avoid breaking something in the current site functionality
							// Then it will be moved to the correct name with the new info
							$indexName = Registry::instance()->getDefaultIndex();
							$totalIndexed = $indexer->indexData( $indexName, $postType, $this->postsPerPageToIndex, $offset );
//							$tmpIndexName = sprintf( '%s_tmp', $indexName );
//							$totalIndexed = $indexer->indexData( $tmpIndexName, $postType, $this->postsPerPageToIndex, $offset );
						} catch ( Exception $exc ) {
							$errorMessage = $exc->getTraceAsString();
							$error = TRUE;
						}
	//				}
				}
			
			}else{
				$errorMessage = __( 'Looks like your credentials are not valids', Registry::instance()->getPluginShortName() );
				$error = TRUE;
			}
			$result = json_encode( array( "totalIndexed" => $totalIndexed, 'error' => $error, 'mvnAlgErrorMessage' => $errorMessage ) ) ;

			// response output
			header( "Content-Type: application/json" );
			echo $result;
			exit;
		}
	}
	
	/**
	 * Run the indexation called from ajax action
	 */
	public function ajaxIndexTaxonomy() {
		check_ajax_referer( Settings::ajaxIndexTaxonomyAction, '_ajax_nonce_indexTaxonomy');
		
		if( !empty( $_POST['runIndexTaxonomy'] ) )
		{
			$errorMessage = '';
			$error = FALSE;
			$totalIndexed = 0;
			if( Registry::instance()->isValidApp() ){
				$indexTaxonomyType = !empty( $_POST['indexTaxonomyType'] ) ? sanitize_text_field( $_POST['indexTaxonomyType'] ) : 0;
				$offset = !empty( $_POST['queryOffset'] ) ? (int)$_POST['queryOffset'] : 0;
		
				$taxonomyTypeToIndex = Core\FieldsHelper::getTaxonomyObjectByType( $indexTaxonomyType );
//				var_dump($taxonomyTypeToIndex);
//				var_dump($taxonomyTypeToIndex->getIndexName());
				if( $taxonomyTypeToIndex && $taxonomyTypeToIndex->getIndexName() ){
						try {
							// Init the index
							$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
							// indexing in a tmp index to avoid breaking something in the current site functionality
							// Then it will be moved to the correct name with the new info
							$indexName =  $taxonomyTypeToIndex->getIndexName();
							$totalIndexed = $indexer->indexTaxonomyData( $indexName, $taxonomyTypeToIndex, $this->postsPerPageToIndex, $offset );
						} catch ( Exception $exc ) {
							$errorMessage = $exc->getTraceAsString();
							$error = TRUE;
						}
				}else{
					$errorMessage = __( 'There was an error trying to index the taxonomy.', Registry::instance()->getPluginShortName() );
					$error = TRUE;
				}
			
			}else{
				$errorMessage = __( 'Looks like your credentials are not valids', Registry::instance()->getPluginShortName() );
				$error = TRUE;
			}
			$result = json_encode( array( "totalIndexed" => $totalIndexed, 'error' => $error, 'mvnAlgErrorMessage' => $errorMessage ) ) ;

			// response output
			header( "Content-Type: application/json" );
			echo $result;
			exit;
		}
	}
	
	/**
	 * Validate if the user info is valid in Algolia
	 */
	public function ajaxValidateCredentials() {
		check_ajax_referer( Settings::ajaxValidateAction, '_ajax_nonce_validate');
		$appId = !empty( $_POST['appId'] ) ? sanitize_text_field( $_POST['appId'] ) : '';
		$apiKey = !empty( $_POST['apiKey'] ) ? sanitize_text_field( $_POST['apiKey'] ) : '';
		$apiSearchKey = !empty( $_POST['apiSearchKey'] ) ? sanitize_text_field( $_POST['apiSearchKey'] ) : '';
		$errorMessage = '';
		$error = FALSE;
		
		if( !empty( $appId ) && !empty($apiKey) && !empty($apiSearchKey) ){
			$validIndex = Core\UtilsAlgolia::validSettings( $appId, $apiKey );
			if( is_wp_error( $validIndex ) ){
				$errorMessage = $validIndex->get_error_message();
				$error = TRUE;
			}
			// TODO: implement a search key validation
//			else{
//				$validIndex = Core\UtilsAlgolia::validSettings( $appId, $apiSearchKey );
//				if( is_wp_error( $validIndex ) ){
//					$errorMessage = "Search " . $validIndex->get_error_message();
//					$error = TRUE;
//				}
//			}
		}else{
			$errorMessage = __( '"App ID" and "Api Key" are required', Registry::instance()->getPluginShortName() );
			$error = TRUE;
		}

		if( $error ){
			Registry::instance()->updateCredentialValidated( 0 );
		}else{
			Registry::instance()->updateCredentialValidated( 1 );	
		}
		$result = json_encode( array( 'error' => $error, 'mvnAlgErrorMessage' => $errorMessage ) ) ;

		// response output
		header( "Content-Type: application/json" );
		echo $result;
		exit;
	}
	
	/**
	 * Validate if the index exists
	 */
	public function ajaxValidateIndex() {
		check_ajax_referer( Settings::ajaxValidateIndexAction, '_ajax_nonce_validateIndex');
		$appId = !empty( $_POST['appId'] ) ? sanitize_text_field( $_POST['appId'] ) : '';
		$apiKey = !empty( $_POST['apiKey'] ) ? sanitize_text_field( $_POST['apiKey'] ) : '';
		$indexName = !empty( $_POST['indexName'] ) ? sanitize_text_field( $_POST['indexName'] ) : '';
		$errorMessage = '';
		$error = FALSE;
		
		if( !empty( $appId ) && !empty($apiKey) && !empty($indexName) ){
			$validIndex = Core\UtilsAlgolia::validSettings($appId, $apiKey, $indexName);
			if( is_wp_error( $validIndex ) ){
				$errorMessage = $validIndex->get_error_message();
				$error = TRUE;
			}
		}else{
			$errorMessage = __( 'App ID, Api Key and Index Name are required', Registry::instance()->getPluginShortName() );
			$error = TRUE;
		}

		if( $error ){
			Registry::instance()->updateIndexValidated( 0 );
		}else{
			Registry::instance()->updateIndexValidated( 1 );	
		}
		$result = json_encode( array( 'error' => $error, 'mvnAlgErrorMessage' => $errorMessage ) ) ;

		// response output
		header( "Content-Type: application/json" );
		echo $result;
		exit;
	}
	
	/**
	 * Run remove posts from index called from ajax action
	 */
	public function ajaxRemoveFromIndex() {
		check_ajax_referer( Settings::ajaxRemoveAction, '_ajax_nonce_remove');
		if( !empty( $_POST['runRemoveIndex'] ) &&  Registry::instance()->isValidApp() )
		{
			$offset = !empty( $_POST['queryOffset'] ) ? (int)$_POST['queryOffset'] : 0;
			
			
			$errorMessage = '';
			$error = FALSE;
			$postTypesToIndex = array_keys( Core\FieldsHelper::getPostTypesToIndex() );
			$totalRemoved = 0;
			if( $postTypesToIndex && is_array( $postTypesToIndex ) ){
					try {
						// Init the index
						$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
						$totalRemoved = $indexer->removeIndexData( Registry::instance()->getDefaultIndex(), $postTypesToIndex, $this->postsPerPageToRemove, $offset );
					} catch ( Exception $exc ) {
						$errorMessage = $exc->getTraceAsString();
						$error = TRUE;
					}
			}
			
			$result = json_encode( array( 'totalRemoved' => $totalRemoved, 'error' => $error, 'mvnAlgErrorMessage' => $errorMessage ) ) ;

			// response output
			header( "Content-Type: application/json" );
			echo $result;
			exit;
		}
	}
	
	/**
	 * Run move index
	 */
	public function ajaxMoveIndex() {
		check_ajax_referer( Settings::ajaxMoveAction, '_ajax_nonce_move');
		if( !empty( $_POST['runMoveIndex'] ) &&  Registry::instance()->isValidApp() )
		{
			
			$errorMessage = '';
			$error = FALSE;
			$postTypesToIndex = array_keys( Core\FieldsHelper::getPostTypesToIndex() );
			if( $postTypesToIndex && is_array( $postTypesToIndex ) ){
					try {
						// Init the index
						$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
						$indexName = Registry::instance()->getDefaultIndex();
						$tmpIndexName = sprintf( '%s_tmp', $indexName );
						// rename the tempory index to its final name
						$indexer->moveIndex( $tmpIndexName, $indexName );
					} catch ( Exception $exc ) {
						$errorMessage = $exc->getTraceAsString();
						$error = TRUE;
					}
			}
			
			$result = json_encode( array( 'error' => $error, 'mvnAlgErrorMessage' => $errorMessage ) ) ;

			// response output
			header( "Content-Type: application/json" );
			echo $result;
			exit;
		}
	}
	
	public static function getTotalPublishedPosts( ) {
		$postsTypesToIndex = array_keys( Core\FieldsHelper::getPostTypesToIndex() );
		
		$totals = array();
		foreach( $postsTypesToIndex as $type ) {
			$countByType = wp_count_posts( $type );
			$totals[$type] = 0;
			foreach( $countByType as $postStatus => $count) {
				if( 'publish' === $postStatus ) {
					$totals[$type] += $count;
				}
			}
		}
		return $totals;
	}
	
	public static function getTotalNonPublishedPosts( ) {
		$postsTypesToIndex = array_keys( Core\FieldsHelper::getPostTypesToIndex() );
		$statuses = array_diff( get_post_stati( array( 'show_in_admin_status_list' => TRUE ) ), array( 'publish' ) );
		$total = 0;
		foreach( $postsTypesToIndex as $type ) {
			$countByType = wp_count_posts( $type );
			foreach( $countByType as $postStatus => $count) {
				if( in_array( $postStatus, $statuses ) ) {
					$total += $count;
				}
			}
		}
		return $total;
	}
	
	

	/**
	 * Save settings modified by the user
	 */
	
	public function updateOptions( ) {
		if( ! current_user_can( 'manage_options' ) 
				|| ! isset($_REQUEST['_wpnonce']) 
				|| ! wp_verify_nonce( $_REQUEST['_wpnonce'], Settings::updateAction ) ){
			return;
		}
		
		// Get all the settings 
		$options = Registry::instance()->getOptions();
		foreach ( $options as $option => $value ) {
			if ( ! isset( $_POST[self::settingsField] ) || ! isset( $_POST[self::settingsField][ $option ] ) ){
				continue;
			}

			if( is_array( $_POST[self::settingsField][ $option ] ) ){
				$options[$option] = array_map( 'sanitize_text_field', $_POST[self::settingsField][ $option ] );
			}else{
				$options[$option] = sanitize_text_field( $_POST[self::settingsField][ $option ] );
			}
		}

		// If there is an apiKey and an appID validate them
		$options['appValid'] = 0;
		if( !empty( $options['apiKey'] ) && !empty( $options['appId'] ) ){
			$validApp = Core\UtilsAlgolia::validApp( $options['appId'], $options['apiKey'] );
			if( ! is_wp_error( $validApp ) ){
				$options['appValid'] = 1;
			}else{
				$options['appValid'] = 0;
			}
		}
		
		$options['appSearchValid'] = 0;
		if( !empty( $options['apiKeySearch'] ) && !empty( $options['appId'] ) && !empty($options['apiKey']) ){
			$validSearchKey = Core\UtilsAlgolia::validAppKeys( $options['appId'], $options['apiKey'], $options['apiKeySearch'], 'search' );
			if( ! is_wp_error( $validSearchKey ) ){
				$options['appSearchValid'] = 1;
			}else{
				$options['appSearchValid'] = 0;
			}
		}

		Registry::instance()->saveOptions( $options );
		$referer = add_query_arg( array( 'mvnAlgMessage' => 'settingsUpdated' ), wp_get_referer() );
		//Redirect back to the settings page that was submitted
		wp_safe_redirect( $referer );
		die('Failed redirect saving settings');
	}

}