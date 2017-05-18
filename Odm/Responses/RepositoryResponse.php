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
	 * @var mixed
	 */
	public $execution;
	/**
	 * RepositoryResponse constructor.
	 *
	 * @param mixed $result
	 * @param int   $code
	 */
	public function __construct($result = null, $code = 200){
		$this->code = $code;
		$this->result = $result;
		$this->execution = new \stdClass();
		$this->execution->start = microtime(true);
		$this->endExecution();
	}

	/**
	 * @return $this
	 */
	public function endExecution(){
		$this->execution->end = microtime(true);
		$this->execution->duration = ($this->execution->end - $this->execution->start);
		return $this;
	}

	/**
	 * @param $result
	 */
	public function setResult($result){
		$this->result = $result;
	}
}