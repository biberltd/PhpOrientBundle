<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Types
 * @name        String
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

class String extends BaseType{

	/** @var string $value */
	protected $value;

	/**
	 * @param string $value
	 *
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function __construct($value){
		parent::__construct('String', $value);
	}

	/**
	 * @return string
	 */
	public function getValue(){
		return $this->value;
	}

	/**
	 * @param $value
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
		if(!is_string($value)){
			throw new InvalidValueException($this);
		}
		return true;
	}

}