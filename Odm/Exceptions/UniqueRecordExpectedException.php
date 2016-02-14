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

class UniqueRecordExpected extends \Exception{

	/**
	 * UniqueRecordExpected constructor.
	 *
	 * @param string     $class
	 * @param int        $value
	 * @param \Exception $column
	 */
	public function __construct($class, $value, $column){
		$this->message = 'The class '.$class.' has multiple entries with value '. $value.' for column '.$column.'. Unique record expected.';
	}
}