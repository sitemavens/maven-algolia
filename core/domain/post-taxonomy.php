<?php

namespace MavenAlgolia\Core\Domain;


class PostTaxonomy{
	
	private $id;
	private $label;
	private $forFaceting = false;
	private $isTag = true;
	
	/**
	 * 
	 * @param string $id
	 * @param string $label
	 * @param bool $isTag
	 * @param bool $forFaceting
	 */
	public function __construct ( $id, $label, $isTag = TRUE, $forFaceting = FALSE ) {
		$this->id = $id;
		$this->label = $label;
		$this->forFaceting = $forFaceting;
		$this->isTag = $isTag;
	}
	
	public function getId () {
		return $this->id;
	}

	public function getLabel () {
		return $this->label;
	}

	public function forFaceting () {
		return (bool) $this->forFaceting;
	}

	public function isTag () {
		return (bool) $this->isTag;
	}

	public function setId ( $id ) {
		$this->id = $id;
	}

	public function setLabel ( $label ) {
		$this->label = $label;
	}

	public function setForFaceting ( $forFaceting ) {
		$this->forFaceting = (bool) $forFaceting;
	}

	public function setIsTag ( $isTag ) {
		$this->isTag = (bool) $isTag;
	}
}