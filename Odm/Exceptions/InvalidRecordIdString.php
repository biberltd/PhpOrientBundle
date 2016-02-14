<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/exceptions
 * @name        InvalidValueException
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions;

class InvalidRecordIdString extends \Exception{

	/**
	 * InvalidRecordIdString constructor.
	 */
	public function __construct(){
		$this->message = 'Record id string is invalid. A valid record id string must start with # followed by cluster id and position seperated witj double colon. Example: #28:82';
	}
}