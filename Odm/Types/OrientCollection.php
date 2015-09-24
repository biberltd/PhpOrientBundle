<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Types
 * @name        OrientCollection
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Types;

use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidIndexException;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException;

class OrientCollection extends BaseType{

	/** @var array $value */
	protected $value;

	/**
	 * @param array $value
	 *
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function __construct(array $value = array()){
		parent::__construct('OrientCollection', $value);
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
		return true;
	}

	/**
	 * @return mixed
	 */
	public function getFirstValue(){
		return $this->value[0];
	}

	/**
	 * @return mixed
	 */
	public function getLastValue(){
		return $this->value[count($this->value) - 1];
	}

	/**
	 * @param integer $n
	 *
	 * @return mixed
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidIndexException
	 */
	public function getNthValue($n){
		if (!isset($this->value[$n])){
			throw new InvalidIndexException($n);
		}
		return $this->value[$n];
	}

	/**
	 * @param integer       $currentIdx
	 * @param bool          $circular
	 *
	 * @return mixed
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidIndexException
	 */
	public function getNextValue($currentIdx, $circular = true){
		if (!isset($this->value[$currentIdx])){
			throw new InvalidIndexException($currentIdx);
		}
		if(isset($this->value[$currentIdx + 1])){
			return $this->value[$currentIdx + 1];
		}

		if($circular){
			return $this->getFirstValue();
		}
		throw new InvalidIndexException($currentIdx + 1);
	}

	/**
	 * @param integer       $currentIdx
	 * @param bool          $circular
	 *
	 * @return mixed
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidIndexException
	 */
	public function getPreviousValue($currentIdx, $circular = true){
		if (!isset($this->value[$currentIdx])){
			throw new InvalidIndexException($currentIdx);
		}
		if(isset($this->value[$currentIdx -1])){
			return $this->value[$currentIdx - 1];
		}

		if($circular){
			return $this->getLastValue();
		}
		throw new InvalidIndexException($currentIdx - 1);
	}
}