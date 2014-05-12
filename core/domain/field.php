<?php

namespace MavenAlgolia\Core\Domain;


class Field{
	
	private $id;
	private $label;
	private $type;
	
	/**
	 * 
	 * @param string $id  Key used in WP for fields
	 * @param string $label  Key used in ALGOLIA for fields
	 */
	public function __construct ($id, $label, $type = 'string') {
		$this->id = $id;
		$this->label = $label;
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
	
	public function getType () {
		return $this->type;
	}

	public function setType ( $type ) {
		$this->type = $type;
	}


}