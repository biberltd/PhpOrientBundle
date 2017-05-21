<?php

namespace BiberLtd\Bundle\PhpOrientBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use \PhpOrient as Orient;
use \PhpOrient\Protocols\Binary\Data as OrientData;
use \PhpOrient\Protocols\Common\Constants as OrientConstants;

class PhpOrient
{
	/**
	 * @var Orient\PhpOrient
	 */
	public $driver;

	/**
	 * @var array
	 */
	protected $orientParams;

    /**
     * PhpOrient constructor.
     * @param Container $container
     * @param string $hostname
     * @param int $port
     * @param $token
     */
	public function __construct(Container $container, string $hostname, int $port, $token)
	{
		$this->orientParams = $container->getParameter('orientdb');

		$hostname = isset($this->orientParams['hostname']) ? $this->orientParams['hostname'] : $hostname;
		$port = isset($this->orientParams['port']) ? $this->orientParams['port'] : $port;
		$token = isset($this->orientParams['token']) ? $this->orientParams['token'] : $token;

		// auto connection by parameters.yml
		$this->driver = new Orient\PhpOrient($hostname, $port, $token);
		$this->driver->username = $this->orientParams['root']['username'];
		$this->driver->password = $this->orientParams['root']['password'];
	}

    /**
     * @param string $query
     * @return mixed
     */
	public function command(string $query)
	{
		return $this->driver->command($query);
	}

    /**
     * @param $serializationType
     * @return mixed
     */
	public function connect($serializationType = OrientConstants::SERIALIZATION_DOCUMENT2CSV)
	{
		return $this->driver->connect($this->driver->username, $this->driver->password, $serializationType);
	}

    /**
     * @param $clusterName
     * @param $clusterType
     * @return mixed
     */
	public function dataClusterAdd($clusterName, $clusterType = OrientConstants::CLUSTER_TYPE_PHYSICAL){
		return $this->driver->dataClusterAdd($clusterName, $clusterType);
	}

    /**
     * @param array $clusterIds
     * @return mixed
     */
	public function dataClusterCount(array $clusterIds = []){
		return $this->driver->dataClusterCount($clusterIds);
	}

    /**
     * @param $clusterId
     * @return mixed
     */
	public function dataClusterDataRange($clusterId){
		return $this->driver->dataClusterDataRange($clusterId);
	}

    /**
     * @param $clusterId
     * @return mixed
     */
	public function dataClusterDrop($clusterId){
		return $this->driver->dataClusterDrop($clusterId);
	}

    /**
     * @return mixed
     */
	public function dbClose(){
		return $this->driver->dbClose();
	}

    /**
     * @return mixed
     */
	public function dbCountRecords(){
		return $this->driver->dbCountRecords();
	}

    /**
     * @param $database
     * @param $storageType
     * @param $databaseType
     * @return mixed
     */
	public function dbCreate($database, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL, $databaseType = OrientConstants::DATABASE_TYPE_GRAPH){
		return $this->driver->dbCreate($database, $storageType, $databaseType);
	}

    /**
     * @param $database
     * @param $storageType
     * @return mixed
     */
	public function dbDrop($database, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL){
		return $this->driver->dbDrop($database, $storageType);
	}

    /**
     * @param $database
     * @param $databaseType
     * @return mixed
     */
	public function dbExists($database, $databaseType = OrientConstants::DATABASE_TYPE_GRAPH){
		return $this->driver->dbExists($database, $databaseType);
	}

    /**
     * @param $dbName
     * @param $storageType
     * @return mixed
     */
	public function dbFreeze($dbName, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL){
		return $this->driver->dbFreeze($dbName, $storageType);
	}

    /**
     * @return mixed
     */
	public function dbList(){
		return $this->driver->dbList();
	}

    /**
     * @param $database
     * @param array $params
     * @return mixed
     */
	public function dbOpen($database, array $params = [])
	{
		if(count($params) < 1){
			$databaseCredentials = $this->orientParams['database'][$database];
		}
		else{
			$databaseCredentials = $params;
		}
		$username = $databaseCredentials['username'];
		$password = $databaseCredentials['password'];

		return $this->driver->dbOpen($database, $username, $password, $params);
	}

    /**
     * @param $dbName
     * @param $storageType
     * @return mixed
     */
	public function dbRelease($dbName, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL){
		return $this->driver->dbRelease($dbName, $storageType);
	}

    /**
     * @return mixed
     */
	public function dbReload(){
		return $this->driver->dbReload();
	}

    /**
     * @return mixed
     */
	public function dbSize(){
		return $this->driver->dbSize();
	}

    /**
     * @param $operation
     * @param array $params
     * @return mixed
     */
	public function execute($operation, array $params = []){
		return $this->driver->execute($operation, $params);
	}

    /**
     * @param string $hostname
     * @param string $port
     * @param string $token
     * @return Orient\PhpOrient
     */
	public function getNewInstance($hostname = '', $port = '', $token = ''){
		$this->driver = new Orient\PhpOrient($hostname, $port, $token);
		return $this->driver;
	}

    /**
     * @return mixed
     */
	public function getSessionToken(){
		return $this->driver->getSessionToken();
	}

    /**
     * @return mixed
     */
	public function getTransport(){
		return $this->driver->getTransport();
	}

    /**
     * @return mixed
     */
	public function getTransactionStatement(){
		return $this->driver->getTransactionStatement();
	}

    /**
     * @param $hostname
     * @return mixed
     */
	public function setHostname($hostname){
		return $this->driver->hostname = $hostname;
	}

    /**
     * @param $password
     * @return mixed
     */
	public function setPassword($password){
		return $this->driver->password = $password;
	}

    /**
     * @param $port
     * @return mixed
     */
	public function setPort($port){
		return $this->driver->port = $port;
	}

    /**
     * @param Orient\Protocols\Common\TransportInterface $transport
     * @return mixed
     */
	public function setTransport(Orient\Protocols\Common\TransportInterface $transport){
		return $this->driver->setTransport($transport);
	}

    /**
     * @param $username
     * @return mixed
     */
	public function setUserName($username){
		return $this->driver->username = $username;
	}

    /**
     * @param $username
     * @param $password
     */
	public function shutDown($username, $password){
		$this->driver->shutDown($username, $password);
	}

    /**
     * @param $param
     * @return mixed
     */
	public function sqlBatch($param){
		return $this->driver->sqlBatch($param);
	}

    /**
     * @param $query
     * @param int $limit
     * @param string $fetchPlan
     * @return mixed
     */
	public function query($query, $limit = 20, $fetchPlan = '*:0'){
		return $this->driver->query($query, $limit, $fetchPlan);
	}

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
	public function queryAsync($query, array $params = []){
		return $this->driver->queryAsync($query, $params);
	}

    /**
     * @param OrientData\Record $record
     * @return mixed
     */
	public function recordCreate(OrientData\Record $record){
		return $this->driver->recordCreate($record);
	}

    /**
     * @param OrientData\Id $rid
     * @return mixed
     */
	public function recordDelete(OrientData\Id $rid){
		return $this->driver->recordDelete($rid);
	}

    /**
     * @param OrientData\Id $rid
     * @param array $params
     * @return mixed
     */
	public function recordLoad(OrientData\Id $rid, array $params = []){
		return $this->driver->recordLoad($rid, $params);
	}

    /**
     * @param OrientData\Record $record
     * @return mixed
     */
	public function recordUpdate(OrientData\Record $record){
		return $this->driver->recordUpdate($record);
	}
}
