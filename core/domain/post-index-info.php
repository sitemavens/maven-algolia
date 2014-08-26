<?php

namespace MavenAlgolia\Core\Domain;


class PostIndexInfo{
	
	private $wpFields = FALSE;
	private $compoundFields = FALSE;
	private $metaFields = FALSE;
	private $taxonomies = FALSE;
	private $thumbnail = FALSE;
	private $mediaType = FALSE;
	
	public function indexWpFields () {
		return $this->wpFields;
	}

	public function indexCompoundFields () {
		return $this->compoundFields;
	}

	public function indexMetaFields () {
		return $this->metaFields;
	}

	public function indexTaxonomies () {
		return $this->taxonomies;
	}

	public function indexThumbnail () {
		return $this->thumbnail;
	}

	public function indexMediaType () {
		return $this->mediaType;
	}

	public function setAllAs ( $all ) {
		$this->compoundFields = $all;
		$this->mediaType = $all;
		$this->metaFields = $all;
		$this->taxonomies = $all;
		$this->thumbnail = $all;
		$this->wpFields = $all;
	}

	public function setWpFields ( $wpFields ) {
		$this->wpFields = $wpFields;
	}

	public function setCompoundFields ( $compoundFields ) {
		$this->compoundFields = $compoundFields;
	}

	public function setMetaFields ( $metaFields ) {
		$this->metaFields = $metaFields;
	}

	public function setTaxonomies ( $taxonomies ) {
		$this->taxonomies = $taxonomies;
	}

	public function setThumbnail ( $thumbnail ) {
		$this->thumbnail = $thumbnail;
	}

	public function setMediaType ( $mediaType ) {
		$this->mediaType = $mediaType;
	}
	
}