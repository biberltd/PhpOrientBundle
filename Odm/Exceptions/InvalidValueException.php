<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/exceptions
 * @name        InvalidValueException
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   bodev-core-bundles (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions;

class InvalidValueException extends \Exception{

	/**
	 * @param \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\object|null $type Type of value.
	 */
	public function __construct(object $type = null){
		$this->message = 'An invalid value provided.';

		if(!is_null($type)){
			$this->message .= ' The value must be a type of '.get_class();
		}
	}
}