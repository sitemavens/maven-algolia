<?php

namespace MavenAlgolia\Core\Domain;


class MetaField{
	
	private $id;
	private $label;
	private $type;
	private $single = false;
	
	/**
	 * 
	 * @param string $id  Key used in WP for fields
	 * @param string $label  Key used in ALGOLIA for fields
	 * @param bool $single  Define if it is a single value
	 * @param string $type  Data type string|int|float
	 */
	public function __construct ($id, $label, $single = TRUE, $type = 'string') {
		$this->id = $id;
		$this->label = $label;
		$this->single = $single;
		$this->type = $type;
	}
	public function getId () {
		return $this->id;
	}

	public function getLabel () {
		return $this->label;
	}

	public function setId ( $id ) {
		$this->id = $id;
	}

	public function setLabel ( $label ) {
		$this->label = $label;
	}
	public function isSingle () {
		return (bool)$this->single;
	}

	public function setSingle ( $single ) {
		$this->single = $single;
	}

	public function getType () {
		return $this->type;
	}

	public function setType ( $type ) {
		$this->type = $type;
	}

}