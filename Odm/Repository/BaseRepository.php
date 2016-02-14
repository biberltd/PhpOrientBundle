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

use BiberLtd\Bundle\PhpOrientBundle\Odm\Entity\BaseEntity;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\UniqueRecordExpected;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Responses\RepositoryResponse;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Types\ORecordId;
use BiberLtd\Bundle\PhpOrientBundle\Services\PhpOrient;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;

abstract class BaseRepository implements RepositoryInterface{
	protected $oService;
	protected $class;

	/**
	 * BaseRepository constructor.
	 *
	 * @param        $container
	 * @param string $hostname
	 * @param int    $port
	 * @param string $token
	 * @param string $dbUsername
	 * @param string $dbPass
	 */
	public function __construct($container, $hostname = 'localhost', $port = 2424, $token = '', $dbUsername = '', $dbPass = ''){
		$this->oService = new PhpOrient($container, $hostname, $port, $token);
		$this->oService->connect($dbUsername, $dbPass);
	}

	/**
	 * @param array $collection
	 *
	 * @return array
	 */
	public final function insert(array $collection){
		$resultSet = [];
		foreach($collection as $anEntity){
			/**
			 * @var BaseEntity $anEntity
			 */
			$query = $this->prepareInsertQuery($anEntity);
			/**
			 * @var Record $insertedRecord
			 */
			$insertedRecord = $this->oService->command($query);
			$anEntity->setRid($insertedRecord->getRid());
			$resultSet[] = $anEntity;
		}

		return new RepositoryResponse($resultSet);
	}

	/**
	 * @param array $collection
	 *
	 * @return array
	 */
	public function update(array $collection){
		$resultSet = [];
		foreach($collection as $anEntity){
			/**
			 * @var BaseEntity $anEntity
			 */
			$query = $this->prepareUpdateQuery($anEntity);
			$result = $this->oService->command($query);
			if(is_array($result) && $result[0] == 1){
				$resultSet[] = $anEntity;
			}
		}
		return new RepositoryResponse($resultSet);
	}

	/**
	 * @param        $query
	 * @param int    $limit
	 * @param string $fetchPlan
	 *
	 * @return mixed
	 */
	public function query($query, $limit = 20, $fetchPlan = '*:0'){
		$resultSet = $this->oService->query($query, $limit, $fetchPlan);

		return new RepositoryResponse($resultSet);
	}

	/**
	 * @param array $collection
	 *
	 * @return array
	 */
	public function delete(array $collection){
		$resultSet = [];
		foreach($collection as $anEntity){
			/**
			 * @var BaseEntity $anEntity
			 */
			$query = 'DELETE FROM '.$this->class.' WHERE @rid = '.$anEntity->getRid('string');
			$result = (bool) $this->oService->command($query);
			if($result){
				$resultSet[] = $anEntity;
			}
		}
		return new RepositoryResponse($resultSet);
	}

	/**
	 * @param $entity
	 *
	 * @return string
	 */
	private function prepareInsertQuery($entity){
		$props = $entity->getProps();
		$query = 'INSERT INTO '.$this->class.' ';
		$propStr = '';
		$valuesStr = '';
		foreach ($props as $aProperty){
			$propName = $aProperty->getName();
			$get = 'get'.ucfirst($propName);
			$value = $entity->$get();
			if($propName == 'rid'){
				continue;
			}
			if(is_null($value) || empty($value)){
				continue;
			}
			$propStr .= $propName.', ';
			$colDef = $entity->getColumnDefinition($propName);
			switch(strtolower($colDef->type)){
				case 'obinary':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'oboolean':
					$valuesStr .= $entity->$get().', ';
					break;
				case 'odate':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'odatetime':
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
				case 'odecimal':
				case 'ofloat':
				case 'ointeger':
				case 'oshort':
				case 'olong':
					$valuesStr .= $entity->$get().', ';
					break;
				case 'oembedded':
				case 'oembeddedlist':
				case 'oembeddedmap':
				case 'oembeddedmap':
					$valuesStr .= json_encode($entity->$get()).', ';
					break;
				case 'olink':
					$valuesStr .= '"'.$entity->$get().'", ';
					break;
				case 'olinkbag':
				case 'olinklist':
				case 'olinkmap':
				case 'olinkset':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'orecordid':
					$valuesStr .= '"'.$entity->$get().'", ';
					break;
				case 'ostring':
					$valuesStr .= '"'.$entity->$get().'", ';
					break;
			}
		}
		$propStr = rtrim($propStr, ', ');
		$valuesStr = rtrim($valuesStr, ', ');
		$query .= '('.$propStr.') VALUES ('.$valuesStr.')';
		return $query;
	}

	/**
	 * @param $entity
	 *
	 * @return string
	 */
	private function prepareUpdateQuery($entity){
		$props = $entity->getProps();
		$query = 'UPDATE '.$this->class.' SET ';
		$propStr = '';
		foreach ($props as $aProperty){
			$propName = $aProperty->getName();
			$get = 'get'.ucfirst($propName);
			$value = $entity->$get();
			if($propName == 'rid'){
				continue;
			}
			if(is_null($value) || empty($value)){
				continue;
			}
			$propStr .= $propName.' = ';
			$colDef = $entity->getColumnDefinition($propName);
			$valuesStr = '';
			switch(strtolower($colDef->type)){
				case 'obinary':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'oboolean':
					$valuesStr .= $entity->$get();
					break;
				case 'odate':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'odatetime':
					$dateObj = $entity->$get()->format();
					$valuesStr .= '"'
						.mktime(
							$dateObj->format('H'),
							$dateObj->format('i'),
							$dateObj->format('s'),
							$dateObj->format('n'),
							$dateObj->format('j'),
							$dateObj->format('Y')
						).'"';
					break;
				case 'odecimal':
				case 'ofloat':
				case 'ointeger':
				case 'oshort':
				case 'olong':
					$valuesStr .= $entity->$get();
					break;
				case 'oembedded':
				case 'oembeddedlist':
				case 'oembeddedmap':
				case 'oembeddedmap':
					$valuesStr .= json_encode($entity->$get());
					break;
				case 'olink':
					$valuesStr .= '"'.$entity->$get().'"';
					break;
				case 'olinkbag':
				case 'olinklist':
				case 'olinkmap':
				case 'olinkset':
					/**
					 * @todo to be implemented
					 */
					break;
				case 'orecordid':
					$valuesStr .= '"'.$entity->$get().'"';
					break;
				case 'ostring':
					$valuesStr .= '"'.$entity->$get().'"';
					break;
			}
			$propStr .= $valuesStr.', ';
		}
		$propStr = rtrim($propStr, ', ');
		$query .= $propStr.' WHERE @rid = '.$entity->getRecordId('string');
		return $query;
	}

	/**
	 * @param $rid
	 *
	 * @return mixed
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\UniqueRecordExpected
	 */
	public function selectByRId($rid, $class){
		if($rid instanceof ID){
			$rid = $rid;
		}
		elseif($rid instanceof ORecordId){
			$rid = $rid->getValue();
		}
		else{
			$oRid = new ORecordId($rid);
			$rid = $oRid->getValue();
		}
		/**
		 * @var ID $rid
		 */
		$q = 'SELECT FROM '.$this->class.' WHERE @rid = #'.$rid->cluster.':'.$rid->position;
		$result = $this->query($q, 1);

		if(count($result) > 1){
			throw new UniqueRecordExpected($class, $rid, 'ORecordId');
		}
		if(count($result) <= 0){
			return null;
		}
		$collection = [];

		foreach($result as $item){
			$collection[] = new $class($item);
		}
		return new RepositoryResponse($collection[0]);
	}
}