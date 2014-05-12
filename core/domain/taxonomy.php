<?php

namespace MavenAlgolia\Core\Domain;


class Taxonomy{
	
	const TaxonomyCategory = 'category';
	const TaxonomyTag = 'post_tag';
	
	private $type;
	
	/**
	 *
	 * @var \MavenAlgolia\Core\Domain\Field[] 
	 */
	private $fields = array();
	private $compoundFields = array();
	private $metaFields = array();
	private $exclude = array();
	private $indexName = '';
	

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
		$this->fields = apply_filters( "ma_tax_fields_{$this->type}", $fields );
	}
	
	
	public function setCompoundFields ( $compoundFields ) {
		$this->compoundFields = apply_filters( "ma_tax_compound_fields_{$this->type}", $compoundFields );
	}

	public function setMetaFields ( $metaFields ) {
		$this->metaFields = apply_filters( "ma_tax_metas_{$this->type}", $metaFields );
	}

	public function setExclude ( $exclude ) {
		$this->exclude = apply_filters( "ma_tax_exclude_{$this->type}", $exclude );
	}

	public function setIndexName ( $indexName ) {
		$this->indexName = $indexName;
	}

	public function setType ( $type ) {
		$this->type = $type;
	}


	public function getFieldsIdsForQuery(){
		$names = "*";
		if ( $this->fields && count($this->fields)>0 ){
			
			
//			foreach ( $this->fields as $field ){
//				if ( ! $names ){
//					$names = $field->getId();
//				}
//				else{
//					$names .= ", ".$field->getId();
//				}
//			}
			
		}
		return $names;
	}
}