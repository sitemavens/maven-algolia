<?php

namespace MavenAlgolia\Admin\Controllers;
use \MavenAlgolia\Core;
use \MavenAlgolia\Core\Registry;

class Indexer  {

	private static $instance;
	

	/**
	 * 
	 */
	public function __construct ( ) {
		// Delete the post in the index when it was deleted in the site
		add_action( 'deleted_post', array( &$this, 'postDeleted' ) );
		// Delete the post in the index when it was unpublished
		add_action( 'transition_post_status', array( &$this, 'postUnpublished' ), 10, 3 );
		// Update the post in the index when it was updated
		// JUST WHEN IT IS publish
		add_action( 'save_post', array( &$this, 'postUpdated' ), 11, 3 );
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
	 * Remove the post from the index when it was unpublished in WP
	 * Called from transition_post_status action
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	public function postUnpublished( $new_status, $old_status, $post ) {
		$postTypesToIndex = Core\FieldsHelper::getPostTypesToIndex();
		if ( isset( $postTypesToIndex[$post->post_type] ) && $old_status == 'publish' && $new_status != 'publish' && !empty( $post->ID ) ) {
			// Post is unpublished so remove from index
			// Init the index
			$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
			// TODO: remember to add the index by post type when we have it implemented
			$indexer->removeObject( Registry::instance()->getDefaultIndex(), $post->ID );
		}
	}
	
	/**
	 * Update post in the index when it was unpdated in WP
	 * Called from save_post action
	 * @param integer $postID
	 * @param \WP_Post $post
	 */
	public function postUpdated( $postID, $post ) {
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $postID ) ){ return; }
		if ( count( $_POST ) <= 0 && count( $_GET ) <= 0 ) { return $postID; }
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return $postID; }
		if ( !current_user_can('edit_post', $postID) ) { return $postID; }
		if ( $post->post_status !== 'publish' ) { return $postID; }
		
		$postTypesToIndex = Core\FieldsHelper::getPostTypesObject();
		if ( !isset( $postTypesToIndex[$post->post_type] ) ) { return $postID; }
		// Init the index
		$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
		$objectToIndex = $indexer->postToAlgoliaObject( $post,$postTypesToIndex[$post->post_type] );
		if( $objectToIndex ){
			// TODO: remember to add the index by post type when we have it implemented
			$indexer->indexObject( Registry::instance()->getDefaultIndex(), $objectToIndex );
		}
	}
	
	/**
	 * Remove the post from the index when it was deleted in WP
	 * Called from deleted_post action
	 * @param int $postid
	 */
	public function postDeleted( $postid ) {
		if ( !empty( $postid ) ) {
			// Post is unpublished so remove from index
			$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
			// TODO: remember to add the index by post type when we have it implemented
			$indexer->removeObject( Registry::instance()->getDefaultIndex(), $postid );
		}
	}
}