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

class InvalidIndexException extends \Exception{

	/**
	 * @param \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\object|null $type Type of value.
	 */
	public function __construct($idx){
		$this->message = 'The collection does not have an index named "'.$idx.'".';
	}
}