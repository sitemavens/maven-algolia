<?php

/*
  Plugin Name: Maven Algolia
  Plugin URI:
  Description: Fully customise WordPress search implementing algolia API
  Version: 0.4
  Author: gtenaschuk, mustela
  Author URI: http://www.sitemavens.com
  Copyright: sitemavens.com
 */

namespace MavenAlgolia;

// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) )
	exit;

//These are the only require_once needed. Then you should use the Loader class
require_once plugin_dir_path( __FILE__ ) . '/core/loader.php';
Core\Loader::load( plugin_dir_path( __FILE__ ), array( 'core/registry', 'core/utils',  'core/utils-algolia', 'lib/algoliasearch' ) );

$registry = Core\Registry::instance();
$registry->setPluginDir( plugin_dir_path( __FILE__ ) );
$registry->setPluginUrl( defined( 'DEV_ENV' ) && DEV_ENV ? WP_PLUGIN_URL . "/maven-algolia/" : plugin_dir_url( __FILE__ )  );
$registry->setPluginVersion( "0.4" );
$registry->setPluginName( 'Maven Algolia' );
$registry->setPluginShortName( 'mvnAlg' );
$registry->init();

/**
 * We need to register the namespace of the plugin. It will be used for autoload function to add the required files. 
 */
Core\Loader::registerType( "MavenAlgolia", $registry->getPluginDir() );
Core\Initializer::init();

if( is_admin() ){
	$settings = Admin\Controllers\Settings::instance();
	$adminIndexer = Admin\Controllers\Indexer::instance();
}else{
	// TODO: Check if we should do this here or if would be better to call it just in search pages
	if( $registry->isEnabled() ){
		try {
			$searcher = new Core\Searcher( $registry->getAppId() , $registry->getApiKey() );
		} catch ( Exception $exc ) {
			// TODO: save this to show to the admin
			$error = $exc->getTraceAsString();
		}
	}
}