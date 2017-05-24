<?php

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Types;

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
	public function __construct($name, $value = null){
		$this->name = $name;

		$this->setValue($value);
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