<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Types
 * @name        BaseType
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

abstract class BaseType{
	/** @var  string $name Descriptive name of type */
	public $name;
	/** @var  mixed $value Value of type. */
	protected $value;

	/**
	 * @param $name     string
	 * @param $value    mixed
	 *
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function __construct($name, $value){
		$this->name = $name;

		if($this->validateValue($value)){
			throw new InvalidValueException($this);
		}
		$this->value = $value;
	}
	/**
	 * Gets the stored value.
	 * @return mixed
	 */
	abstract public function getValue();

	/**
	 * Sets the stored value.
	 * @param $value
	 *
	 * @return mixed
	 */
	abstract public function setValue($value);

	/**
	 * Checks if the value is valid. Must be used with setValue() method.
	 * @param mixed $value
	 */
	abstract public function validateValue($value);
}