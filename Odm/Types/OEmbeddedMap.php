<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Types
 * @name        EmbeddedMap
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Types;

use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException;

class OEmbeddedMap extends BaseType{

	/** @var array $value */
	protected $value;

	/**
	 * @param object $value
	 */
	public function __construct($value = null){
		parent::__construct('OEmbeddedMap', $value);
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
			if($value instanceof OEmbeddedMap){
				$this->value = $value->getValue();
			}
			else if(is_object($value)){
				$this->value = $value;
			}
			else if(is_string($value)){
				$jDecodable = json_decode($value);
				if($jDecodable instanceof \stdClass){
					$this->value = $jDecodable;
				}
			}
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
		if(!is_object($value) && $value != null){
			throw new InvalidValueException($this);
		}
		if(is_null($value)){
			return true;
		}
		if($value instanceof OEmbeddedMap){
			return true;
		}
		foreach($value as $key => $item){
			if(!is_string($item) || !is_string($key)){
				throw new InvalidValueException($this);
			}
		}
		return true;
	}
}