<?php

namespace MavenAlgolia\Core;
if( ! defined( 'ALG_INVALID_SETTINGS' ) ){  define('ALG_INVALID_SETTINGS', 01); }
if( ! defined( 'ALG_INVALID_INDEX' ) ){  define('ALG_INVALID_INDEX', 02); }
// Exit if accessed directly 
if ( ! defined( 'ABSPATH' ) )
	exit;

class UtilsAlgolia {

	public static function readyToIndex(){
		$registry = Registry::instance();
		return ( $registry->getAppId() && $registry->getApiKey() && $registry->isValidApp() );
	}
	
	public static function readyForSearch(){
		$registry = Registry::instance();
		return ( $registry->getAppId() && $registry->getApiKey() && $registry->getApiKeySearch() && $registry->isValidAppSearch() );
	}
	
	public static function availableForSearch(){
		$registry = Registry::instance();
		return ( $registry->isValidAppSearch() && $registry->getDefaultIndex() );
	}

	/**
	 * 
	 * @param type $appId
	 * @param type $apiKey
	 * @param type $indexName
	 * @return boolean|\WP_Error TRUE if all is OK and WP_Error if there was an error
	 */
	public static function validApp( $appId, $apiKey) {
		$client = new \AlgoliaSearch\Client( $appId, $apiKey );
		$valid = false;
		$error = null;
		try {
			$indexes = $client->listIndexes( );
			
			if( $indexes && isset( $indexes['items'] ) ){
				$valid = true;
			}else{
				$error =  new \WP_Error( ALG_INVALID_SETTINGS, __('Invalid Credentials', 'mvnAlg') );
			}
		} catch ( \Exception $exc ) {
			$valid = false;
			$error =  new \WP_Error( ALG_INVALID_SETTINGS, $exc->getMessage() );
		}
		
		if( !$valid ){
			return $error;
		}
		return $valid;
	}

	
	
	public static function validAppKeys( $appId, $apiKey, $key, $rights = 'search' ) {
		$client = new \AlgoliaSearch\Client( $appId, $apiKey );
		$valid = false;
		$errorMessage = '';
		try {
			$keyValues = $client->getUserKeyACL( $key );
			if( $keyValues && isset( $keyValues['acl'] ) && in_array( $rights, $keyValues['acl'] ) ){
				$valid = true;
			}else{
				$errorMessage = sprintf( __('The key "%s" does not have "%s" permisions in the app ID "%s"'), $key, $rights, $appId );
			}

		} catch ( \Exception $exc ) {
			$errorMessage = $exc->getMessage();
		}
		
		if( !$valid ){
			return new \WP_Error( 'invalidSettings', $errorMessage );
		}
		return $valid;
	}
	
	public static function validIndexKeys( $appId, $apiKey, $key, $indexName, $rights = array('search') ) {
		$client = new \AlgoliaSearch\Client( $appId, $apiKey );
		$valid = false;
		$errorMessage = '';
		try {
			$index = $client->initIndex( $indexName );
			$keyRights = $index->getUserKeyACL( $key );
			var_dump($keyRights);
			if( $keyRights ){
											var_dump($keyRights);

			}

		} catch ( \Exception $exc ) {
			$valid = false;
			$errorMessage = $exc->getMessage();
		}
		
		if( !$valid ){
			return new \WP_Error( 'invalidSettings', $errorMessage );
		}
		var_dump($valid); die;
		return $valid;
	}
	
	/**
	 * 
	 * @param type $appId
	 * @param type $apiKey
	 * @param type $indexName
	 * @return boolean|\WP_Error TRUE if all is OK and WP_Error if there was an error
	 */
	public static function validIndex( $appId, $apiKey, $indexName ) {
		$client = new \AlgoliaSearch\Client( $appId, $apiKey );
		$indexes = array();
		$valid = false;
		$error = null;
		try {
			$indexes = $client->listIndexes( );
			
			if( $indexes && isset( $indexes['items'] ) && wp_list_filter( $indexes['items'], array( 'name' => $indexName ) ) ){
				$valid = true;
			}else{
				$error =  new \WP_Error( ALG_INVALID_INDEX, __('Index does not exist', 'mvnAlg') );
			}
		} catch ( \Exception $exc ) {
			$valid = false;
			$error =  new \WP_Error( ALG_INVALID_SETTINGS, $exc->getMessage() );
		}
		
		if( !$valid ){
			return $error;
		}
		return $valid;
	}
}
