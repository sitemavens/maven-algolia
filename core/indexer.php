<?php

namespace MavenAlgolia\Core;

class Indexer  {

	private $apiKey = "";
	private $appId = "";
	

	/**
	 * 
	 * @param string $indexName
	 * @param string $apiKey
	 * @param string $appId
	 * @throws \Exception
	 */
	public function __construct ( $appId, $apiKey ) {

	/**
	 * Help documentation
	 * https://github.com/algolia/algoliasearch-client-php#indexing-parameters
	 */
//	self::$index = array(
//						'my_index' => array(
//											'attributesToIndex' => array(),
//											'attributesForFaceting' => array(),
//											'attributeForDistinct' => '',
//											'ranking' => array(),
//											'customRanking' => array(),
//											'queryType' => 'prefixLast',
//											'slaves' => array(),
//											'master_index' => ''
//											)
//						);	
		if ( !$apiKey || !$appId ) {
			throw new \Exception( 'Missing or Invalid credentials' );
		}
		
		$this->apiKey = $apiKey;
		$this->appId = $appId;
		
	}
	
	public function getApiKey () {
		return $this->apiKey;
	}

	public function getAppId () {
		return $this->appId;
	}

	
	/**
     * Move an existing index.
     * @param tmpIndexName the name of index to copy.
     * @param indexName the new index name that will contains a copy of srcIndexName (destination will be overriten if it already exist).
	 * @return boolean
	 * @throws \MavenAlgolia\Core\Exception
	 */
	public function moveIndex( $tmpIndexName, $indexName ) {
		if( !empty( $tmpIndexName ) && !empty( $indexName ) ){
			// initialize API Client & Index
			$client = new \AlgoliaSearch\Client( $this->getAppId(), $this->getApiKey() );
			try {
				$client->moveIndex( $tmpIndexName, $indexName );
				return true;
			} catch ( \Exception $exc ) {
				throw $exc;
			}
		}
		return false;
	}
	
	/**
	 * Index a single object
	 * @param string $indexName
	 * @param array $object
	 * @return boolean
	 * @throws \MavenAlgolia\Core\Exception
	 */
	public function indexObject( $indexName, $object ) {
		
		if( !isset( $object['objectID'] ) ){
			return false;
		}
		
		// initialize API Client & Index
		$client = new \AlgoliaSearch\Client( $this->getAppId(), $this->getApiKey() );
		$index = $client->initIndex( $indexName );
		try {
			// object contains the object to save
			// the object must contains an objectID attribute
			$index->saveObject( $object );
			return true;
		} catch ( \Exception $exc ) {
			throw $exc;
		}
		return false;
	}
	
	/**
	 * Index multiples objects
	 * @param string $indexName
	 * @param array $objects
	 * @return boolean
	 * @throws \MavenAlgolia\Core\Exception
	 */
	public function indexObjects( $indexName, $objects ) {
		
		// initialize API Client & Index
		$client = new \AlgoliaSearch\Client( $this->getAppId(), $this->getApiKey() );
		$index = $client->initIndex( $indexName );
		try {
			// object contains the object to save
			// the object must contains an objectID attribute
			$index->saveObjects( $objects );
			return true;
		} catch ( \Exception $exc ) {
			throw $exc;
		}
		return false;
	}
	
	/**
	 * Remove a single object from the index
	 * @param string $indexName
	 * @param integer $objectId
	 * @return boolean
	 * @throws \MavenAlgolia\Core\Exception
	 */
	public function deleteObject( $indexName, $objectId ) {
		// initialize API Client & Index
		$client = new \AlgoliaSearch\Client( $this->getAppId(), $this->getApiKey() );
		$index = $client->initIndex( $indexName );
		try {
			// Remove objects
			$index->deleteObject( $objectId );
			return true;
		} catch ( \Exception $exc ) {
			throw $exc;
		}
		return false;
	}
	
	/**
	 * Remove multiple objects from the index
	 * @param string $indexName
	 * @param integer $objectIds
	 * @return boolean
	 * @throws \MavenAlgolia\Core\Exception
	 */
	public function deleteObjects( $indexName, $objectIds ) {
		// initialize API Client & Index
		$client = new \AlgoliaSearch\Client( $this->getAppId(), $this->getApiKey() );
		$index = $client->initIndex( $indexName );
		try {
			// Remove objects
			$index->deleteObjects( $objectIds );
			return true;
		} catch ( \Exception $exc ) {
			throw $exc;
		}
		return false;
	}
	
	/*
	 * ------------------------------------------------------------
	 * POSTS SECTION 
	 * ------------------------------------------------------------
	 */
	
		
	/**
	 * Convert WP post object to Algolia format
	 * @global \MavenAlgolia\Core\type $wpdb
	 * @param \WP_Post $post
	 * @param Domain\PostType|string $type
	 * @return array
	 */
	public function postToAlgoliaObject( $post, $type = null ) {
		global $wpdb;
		
		if( empty( $type ) && !empty( $post->post_type ) ){
			$type = $post->post_type;
		}
		if(  is_string( $type ) ){
			// TODO: Implement a better way to do this, maybe setting the post objects as a class attribute
			$postObjects = FieldsHelper::getPostTypesObject();
			if( isset( $postObjects[$type] ) ){
				$type = $postObjects[$type];
			}else{
				// If the post type object doesn't exist return an empty array
				return array();
			}
		}
		
		// select the identifier of this row
		$row = array();

		// Index WP Post table fields
		$fields = $type->getFields();
		if( is_array( $fields ) && !empty( $fields ) ){
			foreach( $fields as $field ){
				if( isset( $post->{$field->getId()} ) ){
					$row[ $field->getLabel() ] = FieldsHelper::formatFieldValue( $post->{$field->getId()}, $field->getType() );
				}
			}
			unset( $field );
		}
		unset( $fields );

		// Index WP Compound fields
		$compoundFields = $type->getCompoundFields();
		if( is_array( $compoundFields ) && !empty( $compoundFields ) ){
			foreach( $compoundFields as $compoundField ){					
				$row[ $compoundField->getLabel() ] = FieldsHelper::getCompoundFieldValue( $post, $compoundField->getId() );
			}
			unset( $compoundField );
		}
		unset( $compoundFields );

		// Index WP Post meta fields
		$metaFields = $type->getMetaFields();
		if( is_array( $metaFields ) && !empty( $metaFields ) ){
			foreach( $metaFields as $metaField ){
				$metaValue = get_post_meta( $post->ID, $metaField->getId(), $metaField->isSingle() );
				if( $metaValue !== FALSE ){
					if( !is_array( $metaValue ) ){
						$metaValue = FieldsHelper::formatFieldValue( $metaValue, $metaField->getType() );
					}
					$row[ $metaField->getLabel() ] = $metaValue;
				}
			}
			unset( $metaValue );					
			unset( $metaField );					
		}
		unset( $metaFields );

		// Index WP Taxonomies
		$taxonomies = $type->getTaxonomies();
		if( is_array( $taxonomies ) && !empty( $taxonomies ) ){
			$tags = array();
			$termNames = array();
			foreach( $taxonomies as $taxonomy ){

				$termNames = wp_get_post_terms( $post->ID, $taxonomy->getId(), array('fields' => 'names') );
				if( is_array( $termNames ) && !empty( $termNames ) ){
//							$termNames = array_map( 'utf8_encode', $termNames );
					if( $taxonomy->isTag() ){
						$tags = array_unique(array_merge($tags, $termNames));
					}
					if( $taxonomy->forFaceting() ){
						$row[ $taxonomy->getLabel() ] = $termNames;
					}
				}
				// if there is no terms create the field with empty values
				elseif( $taxonomy->forFaceting() ){
					$row[ $taxonomy->getLabel() ] = array();
				}
			}
			// we need to update the _tags field here since they are a single list and they are not divided by "group/type"
			if( is_array( $tags ) && !empty( $tags ) ){
				$row[ '_tags' ] = $tags;
			}
			unset( $termNames );
			unset( $taxonomy );
			unset( $tags );
		}
		unset( $taxonomies );


		// Index featured image if it was configured so
		$postThumbId = 0;
		if( $type->indexFeaturedImage() ){
			if( has_post_thumbnail( $post->ID ) ){
				$postThumbId = get_post_thumbnail_id( $post->ID );
				$row['featuredImage'] = $this->getImage( $postThumbId );
			} else {
				$row['featuredImage'] = '';
			}
		}

		// Index WP media
		$mediaTypes = $type->getMediaTypes();
		if( is_array( $mediaTypes ) && !empty( $mediaTypes ) ){
			$tags = array();
			//TODO: implement different methods or ways to index audio, videos and other files
			foreach( $mediaTypes as $mediaType ){

				// For now we just support images
				if( $mediaType !== 'image' ){
					continue;
				}

				// Index WP media
				$whereExclude = '';
				// Exclude featured image if it is indexed separately
				if( !empty( $postThumbId ) ){
					$whereExclude = " AND ID != {$postThumbId} ";
				}
				$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = %d AND post_mime_type LIKE %s {$whereExclude} ORDER BY menu_order ASC", $post->ID, $mediaType . '%' );
				$children = $wpdb->get_col( $query );

				if( is_array( $children ) && !empty( $children ) ){
					$mediaFields = array();
					foreach ( $children as $childId ) {
						$mediaFields[] = $this->getImage( $childId );
					}
					$row[ $mediaType ] = $mediaFields;

					unset($childId);
				}else{
					$row[ $mediaType ] = array();
				}
			}
			unset($mediaFields);
			unset( $children );
			unset( $query );
			unset( $mediaType );
		}
		unset( $mediaTypes );

		return $row;
	}
	
	/**
	 * 
	 * @global type $wpdb
	 * @param int $attachId Attachment Post ID
	 * @return array  Image information
	 */
	public function getImage( $attachId ) {
		global $wpdb;
		if( empty($attachId) ){
			return array();
		}
		$uploadDir = wp_upload_dir();
		$uploadBaseUrl = $uploadDir['baseurl'];
		$image['ID'] = $attachId;
		//we will need to get the ALT info from Metas
		$image['alt'] = get_post_meta( $attachId, '_wp_attachment_image_alt', TRUE );
		
		$query = $wpdb->prepare( "SELECT post_title, post_content, post_excerpt, post_mime_type FROM {$wpdb->posts} WHERE ID = %d", $attachId );
		$attachment = $wpdb->get_row( $query );
		if( $attachment ){
			$image['title'] = $attachment->post_title;
			$image['description'] = $attachment->post_content;
			$image['caption'] = $attachment->post_excerpt;
			$image['mime_type'] = $attachment->post_mime_type;
		}
		unset( $query );
		unset( $attachment );
		
		$attachmentMeta = get_post_meta( $attachId, '_wp_attachment_metadata', TRUE );		
		
		if( is_array($attachmentMeta) && !empty( $attachmentMeta ) ){
			$image['width'] = $attachmentMeta['width'];
			$image['height'] =	$attachmentMeta['height'];
			$image['file'] = sprintf('%s/%s', $uploadBaseUrl, $attachmentMeta['file'] );
			$image['sizes'] = $attachmentMeta['sizes'];
			if( isset( $image['sizes'] ) && is_array( $image['sizes'] ) ){
				$sizesToIndex = apply_filters( 'ma_image_sizes_to_index', array('thumbnail', 'medium', 'large') );
				foreach ( $image['sizes'] as $size => &$sizeAttrs ) {
					if( !in_array( $size, $sizesToIndex ) ){
						unset( $image['sizes'][$size] );
						continue;
					}
					if( isset( $sizeAttrs['file'] ) && $sizeAttrs['file'] ){
						$baseFileUrl = str_replace( wp_basename($attachmentMeta['file']), '', $attachmentMeta['file']);
						
						$sizeAttrs['file'] = sprintf( '%s/%s%s', $uploadBaseUrl, $baseFileUrl, $sizeAttrs['file']);
					}
				}
			}
			unset($attachmentMeta);
		}
		return $image;
	}

	/**
	 * 
	 * @global type $wpdb
	 * @param string $indexName
	 * @param \Maven\Core\Domain\PostType[] $types
	 * @param int $postsPerPage How many posts per page
	 * @param int $offset Where to start
	 * @return void
	 * @throws \Exception
	 */
	public function removeIndexData ( $indexName, $types, $postsPerPage = -1, $offset = 0 ) {
		// WE will use $wpdb to make the calls faster
		global $wpdb;
		
		if ( ! $indexName  ) {
			throw new \Exception( 'Missing or Invalid Index Name' );
		}
		
		if( !is_array( $types ) ){
			$types = array( $types );
		}
		
		$postTypes = implode( "','", $types );
		
		$postStatuses = implode( "','", array_diff( get_post_stati( array( 'show_in_admin_status_list' => TRUE ) ), array( 'publish' ) ) );
		
			$limit = '';
			if( (int)$postsPerPage > 0 ){
				$limit = sprintf( "LIMIT %d, %d", $offset, $postsPerPage );
			}
			$join = apply_filters('mvnAlgRemoveIndexDataJoin', '');
			$where = apply_filters('mvnAlgRemoveIndexDataWhere', " AND ( post_status IN ('{$postStatuses}') AND post_type IN ( '{$postTypes}' ) ) ");
			$query = "SELECT DISTINCT ID FROM {$wpdb->posts} {$join} WHERE 1 = 1 {$where} {$limit}";
			$posts = $wpdb->get_results( $query );
			$totalRemoved = 0;
			if ( $posts ) {
			
				$batch = array();
				// iterate over results and send them by batch of 10000 elements
				foreach ( $posts as $post ) {
					// select the identifier of this row
					array_push( $batch, $post->ID );
					$totalRemoved++;
				}
				unset( $post );
				unset( $posts );
				try {
					// Remove objects
					$this->deleteObjects( $indexName, $batch );
				} catch ( \Exception $exc ) {
					throw $exc;
				}
				unset( $batch );
			}
	
		return $totalRemoved;
	}
	
	/**
	 * 
	 * @global type $wpdb
	 * @param string $indexName
	 * @param \Maven\Core\Domain\PostType[] $types
	 * @param int $postsPerPage How many posts per page
	 * @param int $offset Where to start
	 * @return void
	 * @throws \Exception
	 */
	public function indexData ( $indexName, $types, $postsPerPage = -1, $offset = 0 ) {
		// WE will use $wpdb to make the calls faster
		global $wpdb;
		
		if ( ! $indexName  ) {
			throw new \Exception( 'Missing or Invalid Index Name' );
		}
		
		
			$limit = '';
			if( (int)$postsPerPage > 0 ){
				$limit = sprintf( "LIMIT %d, %d", $offset, $postsPerPage );
			}
			$postFields = $types->getFieldsIdsForQuery();
			$join = apply_filters('mvnAlgIndexDataJoin', '');
			$where = apply_filters('mvnAlgIndexDataWhere', " AND ( post_status IN ('publish') AND post_type = '{$types->getType()}' )");
			$query = "SELECT {$postFields} FROM {$wpdb->posts} {$join} WHERE 1 = 1 {$where} {$limit}";
			$posts = $wpdb->get_results( $query );
			$totalIndexed = 0;
			if ( $posts ) {
			
				$batch = array();
				// iterate over results and send them by batch of 10000 elements
				foreach ( $posts as $post ) {
					// select the identifier of this row
					$row = $this->postToAlgoliaObject( $post, $types );
					array_push( $batch, $row );
					$totalIndexed++;
				}
				unset($row);
				unset( $post );
				unset( $posts );

				try {
					$this->indexObjects($indexName, $batch);
				} catch ( \Exception $exc ) {
					throw $exc;
				}

				unset( $batch );
			}
	
		unset( $postFields );
		return $totalIndexed;
	}
		
	/*
	 * ------------------------------------------------------------
	 * END POSTS SECTION 
	 * ------------------------------------------------------------
	 */
	
	
	
	/*
	 * ------------------------------------------------------------
	 * TAXONOMIES SECTION 
	 * ------------------------------------------------------------
	 */
	
	
	/**
	 * Convert WP post object to Algolia format
	 * @global \MavenAlgolia\Core\type $wpdb
	 * @param object $term
	 * @param Domain\Taxonomy|string $taxonomy
	 * @return array
	 */
	public function termToAlgoliaObject( $term, $taxonomy = null ) {
		
		if( empty( $taxonomy ) && !empty( $term->taxonomy ) ){
			$taxonomy = $term->taxonomy;
		}
		if(  is_string( $taxonomy ) ){
			$taxonomy = FieldsHelper::getTaxonomyObjectByType( $taxonomy );
		}
		
		// select the identifier of this row
		$row = array();

		// Index WP Tert and Taxonomy tables fields
		$fields = $taxonomy->getFields();
		if( is_array( $fields ) && !empty( $fields ) ){
			foreach( $fields as $field ){
				if( isset( $term->{$field->getId()} ) ){
					$row[ $field->getLabel() ] = FieldsHelper::formatFieldValue( $term->{$field->getId()}, $field->getType() );
				}
			}
			unset( $field );
		}
		unset( $fields );

		// Index Taxonomy Compound fields
		$compoundFields = $taxonomy->getCompoundFields();
		if( is_array( $compoundFields ) && !empty( $compoundFields ) ){
			foreach( $compoundFields as $compoundField ){					
				$row[ $compoundField->getLabel() ] = FieldsHelper::getTaxCompoundFieldValue( $term, $compoundField->getId() );
			}
			unset( $compoundField );
		}
		unset( $compoundFields );

		// Index Term meta fields
//		$metaFields = $taxonomy->getMetaFields();
//		if( is_array( $metaFields ) && !empty( $metaFields ) ){
//			foreach( $metaFields as $metaField ){
//				$metaValue = get_post_meta( $taxonomy->ID, $metaField->getId(), $metaField->isSingle() );
//				if( $metaValue !== FALSE ){
//					if( !is_array( $metaValue ) ){
//						$metaValue = FieldsHelper::formatFieldValue( $metaValue, $metaField->getType() );
//					}
//					$row[ $metaField->getLabel() ] = $metaValue;
//				}
//			}
//			unset( $metaValue );					
//			unset( $metaField );					
//		}
//		unset( $metaFields );

		return $row;
	}

	/**
	 * 
	 * @global type $wpdb
	 * @param string $indexName
	 * @param \Maven\Core\Domain\PostType[] $types
	 * @param int $postsPerPage How many posts per page
	 * @param int $offset Where to start
	 * @return void
	 * @throws \Exception
	 */
	public function indexTaxonomyData ( $indexName, $taxonomy, $postsPerPage = -1, $offset = 0 ) {
		// WE will use $wpdb to make the calls faster
		global $wpdb;
		
		if ( ! $indexName  ) {
			throw new \Exception( 'Missing or Invalid Index Name' );
		}
		
		
			$limit = '';
			if( (int)$postsPerPage > 0 ){
				$limit = sprintf( "LIMIT %d, %d", $offset, $postsPerPage );
			}
			$termFields = $taxonomy->getFieldsIdsForQuery();
			
			$showEmpty = 0;
			$query = $wpdb->prepare( "SELECT {$termFields} FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id WHERE {$wpdb->term_taxonomy}.taxonomy = %s AND {$wpdb->term_taxonomy}.count >= %d {$limit}", $taxonomy->getType(), $showEmpty );
			$terms = $wpdb->get_results( $query );
			$totalIndexed = 0;
			if ( $terms ) {
			
				$batch = array();
				// iterate over results and send them by batch of 10000 elements
				foreach ( $terms as $term ) {
					// select the identifier of this row
					$row = $this->termToAlgoliaObject( $term, $taxonomy );
					array_push( $batch, $row );
					$totalIndexed++;
				}
				unset($row);
				unset( $term );
				unset( $terms );
	//			echo json_encode( $batch );
	//				die;
	//			
				try {
					$this->indexObjects($indexName, $batch);
				} catch ( \Exception $exc ) {
					throw $exc;
				}

				unset( $batch );
			}
	
		unset( $termFields );
		return $totalIndexed;
	}

	/*
	 * ------------------------------------------------------------
	 * END TAXONOMIES SECTION 
	 * ------------------------------------------------------------
	 */
	
}