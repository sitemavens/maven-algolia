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
		
		if( Registry::instance()->isEnabled() ){
			// Delete the post in the index when it was deleted in the site
			add_action( 'deleted_post', array( &$this, 'postDeleted' ) );
			// Delete the post in the index when it was unpublished
			add_action( 'transition_post_status', array( &$this, 'postUnpublished' ), 10, 3 );
			// Update the post in the index when it was updated
			// JUST WHEN IT IS publish
			add_action( 'save_post', array( &$this, 'postUpdated' ), 11, 3 );

			// Update the term in the index when the counter was updated in WP
			add_action( "edited_term_taxonomy", array( &$this, 'termTaxonomyUpdated' ), 10, 2 );
			// Insert the term in the index when it was created
			add_action( "created_term", array( &$this, 'termCreated' ), 10, 3 );
			// Delete the term in the index when it was deleted in the site
			add_action( 'delete_term', array( &$this, 'termDeleted' ), 10, 4 );
		}
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
			try {
				// Init the index
				$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
				// TODO: remember to add the index by post type when we have it implemented
				$indexer->deleteObject( Registry::instance()->getDefaultIndex(), $post->ID );
			} catch ( Exception $exc ) {
				
			}
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
//		if ( count( $_POST ) <= 0 && count( $_GET ) <= 0 ) { return $postID; }
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return $postID; }
//		if ( !current_user_can('edit_post', $postID) ) { return $postID; }
		if ( $post->post_status !== 'publish' ) { return $postID; }
		
		$postTypesToIndex = Core\FieldsHelper::getPostTypesObject();
		if ( !isset( $postTypesToIndex[$post->post_type] ) ) { return $postID; }
		try {
			// Init the index
			$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
			$objectToIndex = $indexer->postToAlgoliaObject( $post, $postTypesToIndex[$post->post_type] );
			if( $objectToIndex ){
				// TODO: remember to add the index by post type when we have it implemented
				$indexer->indexObject( Registry::instance()->getDefaultIndex(), $objectToIndex );
				do_action( 'mvnAlgObjectIndexedOnPostUpdate', $post, $objectToIndex );
			}
		} catch ( Exception $exc ) {

		}
	}
	
	/**
	 * Remove the post from the index when it was deleted in WP
	 * Called from deleted_post action
	 * @param int $postId
	 */
	public function postDeleted( $postId ) {
		if ( !empty( $postId ) ) {
			try {
				// Post is unpublished so remove from index
				$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
				// TODO: remember to add the index by post type when we have it implemented
				$indexer->deleteObject( Registry::instance()->getDefaultIndex(), $postId );
			} catch ( Exception $exc ) {

			}
		}
	}
	
	/**
	 * Update term in the index when it was unpdated in WP
	 * Called from edited_term and created_term actions
	 * @param integer $ttId
	 * @param object $taxonomy
	 */
	public function termTaxonomyUpdated( $ttId, $taxonomy ) {
		if( empty( $taxonomy->name ) ){ return $ttId; }
		
		$taxonomyToIndex = Core\FieldsHelper::getTaxonomyObjectByType( $taxonomy->name );
		
		if ( empty( $taxonomyToIndex ) || ! $taxonomyToIndex->getIndexName() ) { return $ttId; }
		
		// Get the object before deletion so we can pass to actions below
		$termUpdated = get_term_by( 'term_taxonomy_id', $ttId, $taxonomy->name );
		if( !is_wp_error( $termUpdated ) && $termUpdated ){
			try {
				// Init the index
				$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
				// Convert the term in a algolia object
				$objectToIndex = $indexer->termToAlgoliaObject( $termUpdated, $taxonomyToIndex );
				if( $objectToIndex ){
					$indexer->indexObject( $taxonomyToIndex->getIndexName(), $objectToIndex );
				}
			} catch ( Exception $exc ) {

			}
		}
	}
	
	/**
	 * Update term in the index when it was unpdated in WP
	 * Called from edited_term and created_term actions
	 * @param integer $termId
	 * @param integer $ttId
	 * @param string $taxonomy
	 */
	public function termCreated( $termId, $ttId, $taxonomy ) {
		$taxonomyToIndex = Core\FieldsHelper::getTaxonomyObjectByType( $taxonomy );
		if ( empty( $taxonomyToIndex ) || ! $taxonomyToIndex->getIndexName() ) { return $termId; }
		
		// Get the object before deletion so we can pass to actions below
		$termUpdated = get_term( $termId, $taxonomy );
		if( !is_wp_error( $termUpdated ) && $termUpdated ){
			try {
				// Init the index
				$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
				// Convert the term in a algolia object
				$objectToIndex = $indexer->termToAlgoliaObject( $termUpdated, $taxonomyToIndex );
				if( $objectToIndex ){
					$indexer->indexObject( $taxonomyToIndex->getIndexName(), $objectToIndex );
				}
			} catch ( Exception $exc ) {

			}
		}
	}
	
	/**
	 * Remove the term from the index when it was deleted in WP
	 * Called from delete_term action
	 * @param integer $termId
	 * @param integer $ttId
	 * @param string $taxonomy
	 * @param object $deleted_term
	 */
	public function termDeleted( $termId, $ttId, $taxonomy, $deleted_term ) {
		$taxonomyToIndex = Core\FieldsHelper::getTaxonomyObjectByType( $taxonomy );
		if ( empty( $ttId ) || empty( $taxonomyToIndex ) || ! $taxonomyToIndex->getIndexName() ) { return $termId; }
		try {
				// Term was removed so remove it from index
			$indexer = new Core\Indexer( Registry::instance()->getAppId(), Registry::instance()->getApiKey() );
			$indexer->deleteObject( $taxonomyToIndex->getIndexName(), $ttId );
		} catch ( Exception $exc ) {

		}
	}
}