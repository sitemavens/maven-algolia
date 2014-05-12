<?php

namespace MavenAlgolia\Core\Domain;


class PostType{
	
	const PostTypePage = 'page';
	const PostTypePost = 'post';
	
	private $type;
	
	/**
	 *
	 * @var \MavenAlgolia\Core\Domain\Field[] 
	 */
	private $fields = array();
	private $compoundFields = array();
	private $mediaTypes = array();
	private $indexFeaturedImage = true;
	private $comments = false;
	private $taxonomies = array();
	private $metaFields = array();
	private $exclude = array();
	private $indexName;
	
	public function indexFeaturedImage () {
		return $this->indexFeaturedImage;
	}

	public function setIndexFeaturedImage ( $indexFeaturedImage ) {
		$this->indexFeaturedImage = $indexFeaturedImage;
	}

	public function getCompoundFields () {
		return $this->compoundFields;
	}

	/**
	 * Return fields
	 * @return \MavenAlgolia\Core\Domain\Field[]
	 */
	public function getFields () {
		return $this->fields;
	}
	
	/**
	 * Return meta fields
	 * @return \MavenAlgolia\Core\Domain\MetaField[]
	 */
	public function getMetaFields () {
		return $this->metaFields;
	}

	public function getMediaTypes () {
		return $this->mediaTypes;
	}

	public function getComments () {
		return $this->comments;
	}

	public function getTaxonomies () {
		return $this->taxonomies;
	}

	public function getExclude () {
		return $this->exclude;
	}

	public function getIndexName () {
		return $this->indexName;
	}
	
	
	public function getType () {
		return $this->type;
	}


	public function setFields ( $fields ) {
		$this->fields = apply_filters( "ma_post_fields_{$this->type}", $fields );
	}
	
	
	public function setCompoundFields ( $compoundFields ) {
		$this->compoundFields = apply_filters( "ma_compound_fields_{$this->type}", $compoundFields );
	}

	public function setMediaTypes ( $mediaTypes ) {
		$this->mediaTypes = apply_filters( "ma_media_types_{$this->type}", $mediaTypes );
	}

	public function setComments ( $comments ) {
		$this->comments = $comments;
	}

	public function setTaxonomies ( $taxonomies ) {
		$this->taxonomies = apply_filters( "ma_post_taxonomies_{$this->type}", $taxonomies );
	}

	public function setMetaFields ( $metaFields ) {
		$this->metaFields = apply_filters( "ma_post_metas_{$this->type}", $metaFields );
	}

	public function setExclude ( $exclude ) {
		$this->exclude = apply_filters( "ma_post_exclude_{$this->type}", $exclude );
	}

	public function setIndexName ( $indexName ) {
		$this->indexName = $indexName;
	}

	public function setType ( $type ) {
		$this->type = $type;
	}


	public function getFieldsIdsForQuery(){
		if ( $this->fields && count($this->fields)>0 ){
			
			$names = "";
			foreach ( $this->fields as $field ){
				if ( ! $names ){
					$names = $field->getId();
				}
				else{
					$names .= ", ".$field->getId();
				}
			}
			
			return $names;
		}
		
		return "";
	}
}