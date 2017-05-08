<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/exceptions
 * @name        InvalidIndexException
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions;

class ClassMustBeSetException extends \Exception{

	/**
	 * ClassMustBeSetException constructor.
	 */
	public function __construct(){
		$this->message = 'Class name is missing. Either supply a matching string value for argument 2 or set $class property of the object to a matching string value.';
	}
}