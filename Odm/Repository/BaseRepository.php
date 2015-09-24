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

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Repository;

use BiberLtd\Bundle\PhpOrientBundle\Services\PhpOrient;

abstract class BaseRepository implements RepositoryInterface{
	protected $oService;
	protected $class;

	/**
	 * @param string $hostname
	 * @param int    $port
	 * @param string $token
	 */
	public function __construct($hostname = 'localhost', $port = 2424, $token = '', $dbUsername = '', $dbPass = ''){
		$this->oService = new PhpOrient($hostname, $port, $token);
		$this->oService->connect($dbUsername, $dbPass);
	}

	/**
	 * @param array $collection
	 *
	 * @return array
	 */
	public final function insert(array $collection){
		$resultSet = array();
		foreach($collection as $anEntity){
			$query = $this->prepareInsertQuery($anEntity);
			$resultSet[] = $this->oService->command($query);
		}
		return $resultSet;
	}

	/**
	 * @todo
	 */
	public function update(array $collection){

	}

	/**
	 * @param        $query
	 * @param int    $limit
	 * @param string $fetchPlan
	 *
	 * @todo SQL Injection etc. cleanups
	 */
	public function select($query, $limit = 20, $fetchPlan = '*:0'){
		$resultSet = $this->oService->query($query, $limit, $fetchPlan);
	}

	/**
	 * @todo
	 */
	public function delete(array $collection){

	}

	/**
	 * @param $entity
	 *
	 * @return string
	 */
	private function prepareInsertQuery($entity){
		$props = $entity->getProps();
		$query = 'INSERT INTO '.$this->class;
		$propStr = '';
		$valuesStr = '';
		foreach ($props as $aProperty){
			$propName = $aProperty->getName();
			$get = 'get'.ucfirst($propName);
			$propStr .= $propName.', ';
			$colDef = $entity->getColumnDefinition($propName);
			switch(strtolower($colDef->type)){
				case 'binary':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'boolean':
					$valuesStr .= $entity->$get().', ';
					break;
				case 'date':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'datetime':
					$dateObj = $entity->$get()->format();
					$valuesStr .= '"'
									.mktime(
										$dateObj->format('H'),
										$dateObj->format('i'),
										$dateObj->format('s'),
										$dateObj->format('n'),
										$dateObj->format('j'),
										$dateObj->format('Y')
									)
								.'", ';
					break;
				case 'decimal':
				case 'float':
				case 'integer':
				case 'short':
				case 'long':
					$valuesStr .= $entity->$get().', ';
					break;
				case 'embedded':
				case 'embeddedlist':
				case 'embeddedmap':
				case 'embeddedmap':
					$valuesStr .= json_encode($entity->$get()).', ';
					break;
				case 'link':
					$valuesStr .= '"'.$entity->$get().'", ';
					break;
				case 'linkbag':
				case 'linklist':
				case 'linkmap':
				case 'linkset':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'recordid':
					$valuesStr .= '"'.$entity->$get().'", ';
					break;
				case 'string':
					$valuesStr .= '"'.$entity->$get().'", ';
					break;
			}
		}
		$propStr = rtrim($propStr, ', ');
		$valuesStr = rtrim($valuesStr, ', ');
		$query .= '('.$propStr.') VALUES ('.$valuesStr.')';
		return $query;
	}
}