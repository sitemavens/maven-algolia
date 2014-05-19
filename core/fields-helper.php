<?php

namespace MavenAlgolia\Core;

class FieldsHelper{
	public static function formatFieldValue( $value, $type ) {
		switch ( $type ) {
			case 'integer':
				$value = intval($value);
				break;
			case 'float':
				$value = floatval($value) ;
				break;
			case 'datetime':
				$value = strtotime( $value, current_time( 'timestamp' ) );
				break;

			default:
//				$value = utf8_encode($value);
				break;
		}
		return $value;
	}

	
	/*
	 * ------------------------------------------------------------
	 * POSTS SECTION 
	 * ------------------------------------------------------------
	 */
	
	public static function getWPPostTableFields( $postType = 'post' ){
		$fields = array();
		
		$fieldsNames = array(
				'ID'			=> array ( 'label' => "objectID", 'type' => 'integer' ), 
				'post_author'	=> array ( 'label' => "authorId", 'type' => 'integer' ),
				'post_date'		=> array ( 'label' => "date", 'type' => 'datetime' ), 
				'post_content'	=> array ( 'label' => "content", 'type' => 'string' ), 
				'post_title'	=> array ( 'label' => "title", 'type' => 'string' ), 
				'post_excerpt'	=> array ( 'label' => "excerpt", 'type' => 'string' ), 
				'post_name'		=> array ( 'label' => "slug", 'type' => 'string' ), 
				'post_modified' => array ( 'label' => "modified", 'type' => 'datetime' ), 
				'post_parent'	=> array ( 'label' => "parent", 'type' => 'integer' ), 
				'menu_order'	=> array ( 'label' => "menu_order", 'type' => 'integer' ),
				'post_type'		=> array ( 'label' => "type", 'type' => 'string' )
			);
		
		$fieldsNames = apply_filters( "mvnAlgFields", $fieldsNames );
		$fieldsNames = apply_filters( "mvnAlgFields_{$postType}", $fieldsNames );
		foreach( (array)$fieldsNames as $key => $value) {
			if( isset( $value['label'] ) && isset( $value['type'] ) ){
				$fields[$key] = new Domain\Field( $key, $value['label'], $value['type'] );
			}
		}
		
		return $fields;
	}
	
	public static function getCompoundFields( $postType = 'post' ){
		$fields = array();
		
		$fieldsNames = array(
				'author' => array ( 'label' => "author", 'type' => 'string' ), 
				'permalink' => array ( 'label' => "permalink", 'type' => 'string' ), 
			);
		
		$fieldsNames = apply_filters( "mvnAlgCompoundFields", $fieldsNames );
		$fieldsNames = apply_filters( "mvnAlgCompoundFields_{$postType}", $fieldsNames );
		foreach( (array)$fieldsNames as $key => $value) {
			if( isset( $value['label'] ) && isset( $value['type'] ) ){
				$fields[$key] = new Domain\Field( $key, $value['label'], $value['type'] );
			}
		}
		
		return $fields;
	}
	
	
	
	public static function getCompoundFieldValue( $post, $field, $value = '' ) {
		switch ( $field ) {
			case 'permalink':
				$value = get_permalink( $post->ID );
				break;
			case 'author':
				$value = get_the_author_meta( 'display_name', $post->post_author );
				break;

			default:
				$value = apply_filters( "mvnAlgPostCompoundField" , $value, $post, $field );
				break;
		}
		return $value;
	}
	
	public static function getPostTypesToIndex(){
		$defaultPostsTypes = array( 
								'post' => array( 
											'indexFeaturedImage' => TRUE,
											'taxonomies' => array( 
																'category' => array( 'algoliaName' => 'category', 'isTag' => FALSE, 'forFaceting' => TRUE ),
																'post_tag' => array( 'algoliaName' => '_tags', 'isTag' => TRUE, 'forFaceting' => FALSE ),
															),
											//'metas' => 	array( '{{META_KEY}}' =>  array( 'algoliaName' => '{{FIELD_NAME_IN_ALGOLIA}}', 'isSingle' => TRUE, 'type' => '{{FIELD_TYPE}}' ) )
											),
								'page' => array( 
											'indexFeaturedImage' => TRUE,
											//'metas' => 	array( '{{META_KEY}}' =>  array( 'algoliaName' => '{{FIELD_NAME_IN_ALGOLIA}}', 'isSingle' => TRUE, 'type' => '{{FIELD_TYPE}}' ) )
											),
								);
		return apply_filters( 'mvnAlgPostsTypesToIndex', $defaultPostsTypes );
	}
	
	public static function getPostTypesObject(){
		$postTypesObjects = array();
		$postTypes = self::getPostTypesToIndex();
		foreach ( $postTypes as $postType => $fields ) {
			$posts = new Domain\PostType();
			$posts->setType( $postType );
			$posts->setFields( FieldsHelper::getWPPostTableFields( $postType ) );
			$posts->setCompoundFields( FieldsHelper::getCompoundFields( $postType ) );
			if( isset( $fields['metas'] ) && is_array( $fields['metas'] ) ){
				$metaFields = array();
				foreach ( $fields['metas'] as $metaKey => $metaValue) {
					$metaFields[$metaKey] = new Domain\MetaField( $metaKey, $metaValue['algoliaName'], $metaValue['isSingle'], $metaValue['type'] );
				}
				$posts->setMetaFields( $metaFields );
			}
			if( isset( $fields['taxonomies'] ) && is_array( $fields['taxonomies'] ) ){
				$taxonomies = array();
				foreach ( $fields['taxonomies'] as $taxonomyKey => $taxonomyValue) {
					$taxonomies[$taxonomyKey] = new Domain\PostTaxonomy( $taxonomyKey, $taxonomyValue['algoliaName'], $taxonomyValue['isTag'], $taxonomyValue['forFaceting'] );
				}
				$posts->setTaxonomies( $taxonomies );
			}
			if( isset( $fields['indexFeaturedImage'] ) ){
				// True to index the featured image
				$posts->setIndexFeaturedImage( (bool) $fields['indexFeaturedImage'] );
			}
			//			$posts->setMediaTypes( array( 'image' ) ); // Array with media type as key and sizes to index as values, use empty to index all
			//			$posts->setComments( FALSE );
			$postTypesObjects[$postType] = $posts;
		}
		
		return $postTypesObjects;
	}
	
	public static function getPostTypesLabels( $postTypes ) {
		$postTypesLabels = array();
		if( empty( $postTypes ) ){
			return $postTypesLabels;
		}
		foreach ( array_keys( $postTypes ) as $type ) {
			$postType = get_post_type_object( $type );
			if( $postType && !empty( $postType->labels ) && !empty( $postType->labels->name ) ){
				$postTypesLabels[$type] = $postType->labels->name;
			}
		}

		return $postTypesLabels;
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
	
	public static function getWPTaxonomyFields( $taxType = 'category' ){
		$fields = array();
		
		$fieldsNames = array(
				'term_taxonomy_id'	=> array ( 'label' => "objectID", 'type' => 'integer' ), 
				'term_id'			=> array ( 'label' => "termId", 'type' => 'integer' ),
				'name'				=> array ( 'label' => "title", 'type' => 'string' ), 
				'slug'				=> array ( 'label' => "slug", 'type' => 'string' ), 
				'description'		=> array ( 'label' => "content", 'type' => 'string' ), 
				'parent'			=> array ( 'label' => "parent", 'type' => 'integer' ), 
				'count'				=> array ( 'label' => "postsRelated", 'type' => 'integer' ), 
				'taxonomy'			=> array ( 'label' => "taxonomy", 'type' => 'string' ),
			);
		
		$fieldsNames = apply_filters( "mvnAlgTaxFields", $fieldsNames );
		$fieldsNames = apply_filters( "mvnAlgTaxFields_{$taxType}", $fieldsNames );
		foreach( (array)$fieldsNames as $key => $value) {
			if( isset( $value['label'] ) && isset( $value['type'] ) ){
				$fields[$key] = new Domain\Field( $key, $value['label'], $value['type'] );
			}
		}
		
		return $fields;
	}
	
	public static function getTaxCompoundFields( $taxType = 'category' ){
		$fields = array();
		
		$fieldsNames = array(
				'permalink' => array ( 'label' => "permalink", 'type' => 'string' ), 
			);
		
		$fieldsNames = apply_filters( "mvnAlgTaxCompoundFields", $fieldsNames );
		$fieldsNames = apply_filters( "mvnAlgTaxCompoundFields_{$taxType}", $fieldsNames );
		foreach( (array)$fieldsNames as $key => $value) {
			if( isset( $value['label'] ) && isset( $value['type'] ) ){
				$fields[$key] = new Domain\Field( $key, $value['label'], $value['type'] );
			}
		}
		
		return $fields;
	}
	
	
	/**
	 * 
	 * @param object $term
	 * @param string $field
	 * @param mixed $value
	 * @return mixed
	 */
	public static function getTaxCompoundFieldValue( $term, $field, $value = '' ) {
		switch ( $field ) {
			case 'permalink':
				$value = get_term_link( $term );
				break;
			default:
				$value = apply_filters( "mvnAlgTaxCompoundField" , $value, $term, $field );
				break;
		}
		return $value;
	}
	
	/**
	 * Get the domain name
	 * @return string
	 */
	public static function getDomainName ( ) {
		$host =  parse_url ( site_url(), PHP_URL_HOST );
		$domainName = array_shift( explode( '.', str_replace( 'www.', '', $host ) ) );
		return ( $domainName ) ? $domainName : '' ;
	}


	/**
	 * Return the array of taxonomies to index with their fields
	 * @return array
	 */
	public static function getTaxonomiesToIndex(){
		$indexPrefix = self::getDomainName();
		if( !empty( $indexPrefix ) ){
			$indexPrefix = sprintf( '%s-', $indexPrefix );
		}
		
		$defaultTaxonomies =  array( 
								'category' => array( 
											'indexName' => sprintf( '%sWP-Categories', $indexPrefix ),
											//'metas' => 	array( '{{META_KEY}}' =>  array( 'algoliaName' => '{{FIELD_NAME_IN_ALGOLIA}}', 'isSingle' => TRUE, 'type' => '{{FIELD_TYPE}}' ) )
											),
								'post_tag' => array( 
											'indexName' => sprintf( '%sWP-Tags', $indexPrefix ),
											//'metas' => 	array( '{{META_KEY}}' =>  array( 'algoliaName' => '{{FIELD_NAME_IN_ALGOLIA}}', 'isSingle' => TRUE, 'type' => '{{FIELD_TYPE}}' ) )
											),
								);
		return apply_filters( 'mvnAlgTaxonomiesToIndex', $defaultTaxonomies, $indexPrefix );
	}
	
	/**
	 * 
	 * @return array
	 */
	public static function getTaxonomyObjects( ){
		$taxonomyObjects = array();
		$taxonomies = self::getTaxonomiesToIndex();
		foreach ( $taxonomies as $taxonomyType => $fields ) {
			$taxonomyObjects[$taxonomyType] = self::getTaxonomyObject( $taxonomyType, $fields );
		}
		
		return $taxonomyObjects;
	}
	
	/**
	 * 
	 * @param string $taxonomyType
	 * @return \MavenAlgolia\Core\Domain\Taxonomy|null
	 */
	public static function getTaxonomyObjectByType( $taxonomyType ){
		$taxonomies = self::getTaxonomiesToIndex();
		if( isset( $taxonomies[$taxonomyType] ) ){
			return self::getTaxonomyObject( $taxonomyType, $taxonomies[$taxonomyType] );
		}
		return;
	}
	
	/**
	 * 
	 * @param strings $taxonomyType
	 * @param array $fields
	 * @return \MavenAlgolia\Core\Domain\Taxonomy
	 */
	public static function getTaxonomyObject( $taxonomyType, $fields ){
		$taxObj = new Domain\Taxonomy();
		$taxObj->setType( $taxonomyType );
		$taxObj->setFields( FieldsHelper::getWPTaxonomyFields( $taxonomyType ) );
		$taxObj->setCompoundFields( FieldsHelper::getTaxCompoundFields( $taxonomyType ) );
		if( !empty( $fields['indexName'] ) ){
			$taxObj->setIndexName( $fields['indexName'] );
		}
		if( isset( $fields['metas'] ) && is_array( $fields['metas'] ) ){
			$metaFields = array();
			foreach ( $fields['metas'] as $metaKey => $metaValue) {
				$metaFields[$metaKey] = new Domain\MetaField( $metaKey, $metaValue['algoliaName'], $metaValue['isSingle'], $metaValue['type'] );
			}
			$taxObj->setMetaFields( $metaFields );
		}
		
		return $taxObj;
	}
	
	public static function getTaxonomyLabels( ) {
		
		$taxonomyLabels = array();
		$taxonomies = self::getTaxonomiesToIndex();

		foreach ( array_keys( $taxonomies ) as $type ) {
			$taxonomy = get_taxonomies( array( 'name' => $type ), 'objects' );
			$taxonomyLabel = get_taxonomy_labels( $taxonomy[$type] );

			if( $taxonomyLabel && !empty( $taxonomyLabel->name ) ){
				$taxonomyLabels[$type] = $taxonomyLabel->name;
			}
		}

		return $taxonomyLabels;
	}
	
}