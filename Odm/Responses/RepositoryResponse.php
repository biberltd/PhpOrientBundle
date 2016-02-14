<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Repository
 * @name        BaseRepository
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Responses;


class RepositoryResponse{
	/**
	 * @var int
	 */
	public $code;
	/**
	 * @var mixed
	 */
	public $result;

	/**
	 * RepositoryResponse constructor.
	 *
	 * @param mixed $result
	 * @param int   $code
	 */
	public function __construct($result, $code = 200){
		$this->code = $code;
		$this->result = $result;
	}
}