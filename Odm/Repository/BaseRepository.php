<?php
namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Repository;

use BiberLtd\Bundle\PhpOrientBundle\Odm\Entity\BaseEntity;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\ClassMustBeSetException;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\UniqueRecordExpected;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Responses\RepositoryResponse;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Types\ORecordId;
use BiberLtd\Bundle\PhpOrientBundle\Services\PhpOrient;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;

abstract class BaseRepository implements RepositoryInterface {
    protected $oService;
    protected $class;
    protected $controller;
    private $fetchPlan = false;

    /**
     * BaseRepository constructor.
     * @param array $internals
     * @param string $hostname
     * @param int $port
     * @param string $token
     * @param string $dbUsername
     * @param string $dbPass
     */
    public function __construct(array $internals, $hostname = 'localhost', $port = 2424, $token = '', $dbUsername = '', $dbPass = ''){
        $this->oService = new PhpOrient($internals['container'], $hostname, $port, $token);
        $this->oService->connect($dbUsername, $dbPass);
        $this->controller = $internals['controller'];
        unset($internals);
    }

    /**
     * @param array $collection
     * @param bool  $batch
     *
     * @return \BiberLtd\Bundle\PhpOrientBundle\Odm\Responses\RepositoryResponse
     */
    public final function insert(array $collection, bool $batch = false){
        $resultSet = [];
        if($batch){
            $query = $this->prepareBatchInsertQuery($collection);
            $insertedRecords = $this->oService->command($query);
            $resultSet = $collection;
        }
        else{

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
            if(!$anEntity->isModified()){
                continue;
            }
            $query = $this->prepareUpdateQuery($anEntity);
            $result = $this->oService->command($query);
            if($result instanceof Record){
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

    public function queryAsync($query, $fetchPlan = '*:0'){

        $return = new Record();
        $myFunction=function(Record $record) use ($return){
            $return=$record;
        };
        $resultSet =$this->oService->queryAsync( $query, [ 'fetch_plan' => $fetchPlan, '_callback' => $myFunction ]);

        return new RepositoryResponse($return);
    }

    public function setFetchPlan($fetchString='*:0')
    {
        $this->fetchPlan = $fetchString;
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
     * @param array $collection
     *
     * @return string
     */
    private function prepareBatchInsertQuery(array $collection){
        $props = $collection[0]->getProps();
        $query = 'INSERT INTO '.$this->class.' ';
        $propStr = '';
        $valueCollectionStr = '';

        foreach($props as $aProperty){
            $propName = $aProperty->getName();
            $propStr .= $propName.', ';
        }
        $propStr = ' ('.rtrim(', ', $propStr).') ';
        foreach($collection as $entity){
            $valuesStr = '';
            foreach($props as $aProperty){
                $propName = $aProperty->getName();
                $get = 'get'.ucfirst($propName);
                $value = $entity->$get();
                if($propName == 'rid'){
                    continue;
                }
                if(is_null($value) || empty($value)){
                    continue;
                }
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
                    case 'odatetime':
                        $dateStr = $entity->$get()->format('Y-m-d H:i:s');
                        $valuesStr .= '"'.$dateStr.'", ';
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
                        $valuesStr .= '"'.$entity->$get()->getRid('string').'", ';
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
            $valueCollectionStr .= ' ('.rtrim($valuesStr, ', ').'), ';
        }
        $valueCollectionStr =  rtrim(', ', $valueCollectionStr);

        $query .= '('.$propStr.') VALUES '.$valueCollectionStr;
        return $query;
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
            //$get = 'get'.ucfirst($propName);
            $get = $propName;
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
                case 'odatetime':
                    $dateStr = $entity->$get()->format('Y-m-d H:i:s');
                    $valuesStr .= '"'.$dateStr.'", ';
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
                    $valuesStr .= '"'.$entity->$get()->getRid('string').'", ';
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
        $count = 0;
        foreach ($props as $aProperty){
            $count++;
            $propName = $aProperty->getName();
            $get = 'get'.ucfirst($propName);
            $value = $entity->$get();
            if($propName == 'rid'){
                continue;
            }
            if(is_null($value) || empty($value)){
                continue;
            }

            $colDef = $entity->getColumnDefinition($propName);
            $options = $colDef->options;
            if(isset($options) && isset($options['readOnly']) && $options['readOnly'] === true){
                continue;
            }

            $valuesStr = '';
            $propStr .= $propName.' = ';
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
                case 'odatetime':
                    $dateStr = $entity->$get()->format('Y-m-d H:i:s');
                    $valuesStr .= '"'.$dateStr.'"';
                    break;
                case 'odecimal':
                case 'ofloat':
                case 'ointeger':
                case 'oshort':
                case 'olong':
                    $valuesStr .= $entity->$get();
                    break;
                case 'oembeddedlist':
                case 'oembedded':
                case 'oembeddedmap':
                    $valuesStr .= json_encode($entity->$get());
                    break;
                case 'olink':
                    $valuesStr .= '"'.$entity->$get()->getRid('string').'"';
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
        $query .= $propStr.' RETURN AFTER'.' WHERE @rid = '.$entity->getRecordId('string');
        return $query;
    }

    /**
     * @param mixed $rid
     *
     * @return mixed
     * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\UniqueRecordExpected
     */
    public function selectByRid($rid, $class=null){
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
        $response = $this->query($q, 1);
        if(count($response->result) > 1){
            throw new UniqueRecordExpected($class, $rid, 'ORecordId');
        }
        if(count($response->result) <= 0){
            return new RepositoryResponse(false);
        }
        if($class!=null)
        {
            $collection = [];

            foreach($response->result as $item){
                $collection[] = new $class($item);
            }
            return new RepositoryResponse($collection[0]);
        }else{
            return new RepositoryResponse($response->result[0]);
        }

    }

    /**
     * @param array       $rids
     * @param string|null $class
     *
     * @return \BiberLtd\Bundle\PhpOrientBundle\Odm\Responses\RepositoryResponse
     * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\ClassMustBeSetException
     */
    public function listByRids(array $rids, string $class = null){
        if(count($rids) < 1){
            return new RepositoryResponse([]);
        }
        $class = $class ?? ($this->entityClass ?? null);
        if(is_null($class) || empty($class)){
            throw new ClassMustBeSetException();
        }
        $convertdRids = [];
        foreach($rids as $rid){
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
            $convertdRids[] = '#'.$rid->cluster.':'.$rid->position;
        }
        $ridStr = implode(',', $convertdRids);
        unset($rids, $convertdRids);

        $q = 'SELECT FROM '.$this->class.' WHERE @rid IN ['.$ridStr.']';
        $response = $this->query($q, 1);
        if(count($response->result) <= 0){
            return new RepositoryResponse([]);
        }
        $collection = [];
        foreach($response->result as $item){
            $collection[] = new $class($this->controller, $item);
        }
        return new RepositoryResponse($collection);
    }

    public function setClass($class)
    {
        $this->class = $class;
    }
}