<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Types
 * @name        EmbeddedSet
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   bodev-core-bundles (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Types;

use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException;

class EmbeddedSet extends OrientCollection{

	/** @var array $value */
	protected $value;

	/**
	 * @param array $value
	 *
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function __construct(array $value = array()){
		parent::__construct('EmbeddedSet', $value);
	}

	/**
	 * @return array
	 */
	public function getValue(){
		return $this->value;
	}

	/**
	 * @param array $value
	 *
	 * @return $this
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function setValue($value){
		if($this->validateValue($value)){
			$this->value = $value;
		}
		return $this;
	}
	/*
	 * @param mixed $value
	 *
	 * @return bool
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function validateValue($value){
		if(!is_array($value)){
			throw new InvalidValueException($this);
		}
		foreach($value as $key => $item){
			if(!is_object($item) || !is_string($key)){
				throw new InvalidValueException($this);
			}
		}
		return true;
	}
}