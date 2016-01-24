<?php
/**
 * @vendor      BiberLtd
 * @package		Core\Bundles\PhpOrientBundle
 * @subpackage	Services
 * @name	    PhpOrientWrapper
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. www.biberltd.com (C) 2015
 *
 * @version     1.0.2
 * @date        11.06.2015
 *
 */
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
	 * @param Container $container
	 * @param $hostname
	 * @param $port
	 * @param $token
	 *
	 *
	 * @since           1.0.0
	 * @version         1.0.1
	 *
	 * @author			Can Berkol
	 * @author			Ali Cavdar
	 */
	public function __construct(Container $container, $hostname, $port, $token)
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
	 * @name            command()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$query
	 *
	 * @return 			mixed
	 */
	public function command($query)
	{
		return $this->driver->command($query);
	}

	/**
	 * @name            connect()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 * @author			Ali Cavdar
	 *
	 * @param			string		$serializationType
	 *
	 * @return 			mixed
	 */
	public function connect($serializationType = OrientConstants::SERIALIZATION_DOCUMENT2CSV)
	{
		return $this->driver->connect($this->driver->username, $this->driver->password, $serializationType);
	}

	/**
	 * @name            dataClusterAdd()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @$param 			string		$clusterName
	 *                  string		$clusterType
	 *
	 * @return 			integer
	 */
	public function dataClusterAdd($clusterName, $clusterType = OrientConstants::CLUSTER_TYPE_PHYSICAL){
		return $this->driver->dataClusterAdd($clusterName, $clusterType);
	}

	/**
	 * @name            dataClusterCount()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @$param 			array 		$clusterIds
	 *
	 * @return 			array
	 */
	public function dataClusterCount(array $clusterIds = []){
		return $this->driver->dataClusterCount($clusterIds);
	}

	/**
	 * @name            dataClusterDataRange()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @$param 			integer 		$clusterId
	 *
	 * @return 			array
	 */
	public function dataClusterDataRange($clusterId){
		return $this->driver->dataClusterDataRange($clusterId);
	}

	/**
	 * @name            dataClusterDrop()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @$param 			integer 		$clusterId
	 *
	 * @return 			bool
	 */
	public function dataClusterDrop($clusterId){
		return $this->driver->dataClusterDrop($clusterId);
	}

	/**
	 * @name            dbClose()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			integer
	 */
	public function dbClose(){
		return $this->driver->dbClose();
	}

	/**
	 * @name            dbCountRecords()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			integer
	 */
	public function dbCountRecords(){
		return $this->driver->dbCountRecords();
	}

	/**
	 * @name            dbCreate()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param 			string		$database
	 * @param			string		$storageType
	 * @param			string		$databaseType
	 *
	 * @return 			bool
	 */
	public function dbCreate($database, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL, $databaseType = OrientConstants::DATABASE_TYPE_GRAPH){
		return $this->driver->dbCreate($database, $storageType, $databaseType);
	}

	/**
	 * @name            dbDrop()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param 						$database
	 * @param			string		$storageType
	 *
	 * @return 			true
	 */
	public function dbDrop($database, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL){
		return $this->driver->dbDrop($database, $storageType);
	}

	/**
	 * @name            dbExists()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param 			string		$database
	 * @param			string		$databaseType
	 *
	 * @return 			bool
	 */
	public function dbExists($database, $databaseType = OrientConstants::DATABASE_TYPE_GRAPH){
		return $this->driver->dbExists($database, $databaseType);
	}

	/**
	 * @name            dbFreeze()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param 			string		$dbName
	 * @param			string		$storageType
	 *
	 * @return 			bool
	 */
	public function dbFreeze($dbName, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL){
		return $this->driver->dbFreeze($dbName, $storageType);
	}

	/**
	 * @name            dbList()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			array
	 */
	public function dbList(){
		return $this->driver->dbList();
	}

	/**
	 * @name            dbOpen()
	 *
	 * @since           1.0.0
	 * @version         1.0.1
	 *
	 * @author          Can Berkol
	 * @author			Ali Cavdar
	 *
	 * @param			string		$database
	 * @param			array		$params
	 *
	 * @return 			Orient\Protocols\Common\ClusterMap
	 */
	public function dbOpen($database, array $params = [])
	{
		$databaseCredentials = $this->orientParams['database'][$database];
		$username = $databaseCredentials['username'];
		$password = $databaseCredentials['password'];

		return $this->driver->dbOpen($database, $username, $password, $params);
	}

	/**
	 * @name            dbRelease()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$dbName
	 * @param			string		$storageType
	 *
	 * @return 			true
	 */
	public function dbRelease($dbName, $storageType = OrientConstants::STORAGE_TYPE_PLOCAL){
		return $this->driver->dbRelease($dbName, $storageType);
	}

	/**
	 * @name            dbReload()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			Orient\Protocols\Common\ClusterMap
	 */
	public function dbReload(){
		return $this->driver->dbReload();
	}

	/**
	 * @name            dbSize()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			integer|string
	 */
	public function dbSize(){
		return $this->driver->dbSize();
	}
	/**
	 * @name            execute()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$operation
	 * @param			array		$params
	 *
	 * @return 			mixed
	 */

	public function execute($operation, array $params = []){
		return $this->driver->execute($operation, $params);
	}

	/**
	 * @name            getNewInstance()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$hostname
	 * @param			string		$port
	 * @param			string|bool	$token
	 *
	 * @return 			Orient\PhoOrient
	 */
	public function getNewInstance($hostname = '', $port = '', $token = ''){
		$this->driver = new Orient\PhpOrient($hostname, $port, $token);
		return $this->driver;
	}

	/**
	 * @name            getSessionToken()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			string
	 */
	public function getSessionToken(){
		return $this->driver->getessionToken();
	}

	/**
	 * @name            getTransport()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			Orient\Protocols\Binary\SocketTransport
	 */
	public function getTransport(){
		return $this->driver->getTransport();
	}

	/**
	 * @name            getTransactionStatement()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @return 			Orient\Protocols\Binary\Transaction\TxCommit
	 */
	public function getTransactionStatement(){
		return $this->driver->getTransactionStatement();
	}

	/**
	 * @name            setHostname()
	 *
	 * @since           1.0.1
	 * @version         1.0.1
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$hostname
	 *
	 * @return 			Orient\PhoOrient
	 */
	public function setHostname($hostname){
		return $this->driver->hostname = $hostname;
	}

	/**
	 * @name            setPassword()
	 *
	 * @since           1.0.1
	 * @version         1.0.1
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$password
	 *
	 * @return 			Orient\PhoOrient
	 */
	public function setPassword($password){
		return $this->driver->password = $password;
	}

	/**
	 * @name            setPort()
	 *
	 * @since           1.0.1
	 * @version         1.0.1
	 *
	 * @author          Can Berkol
	 *
	 * @param			integer		$port
	 *
	 * @return 			Orient\PhoOrient
	 */
	public function setPort($port){
		return $this->driver->port = $port;
	}

	/**
	 * @name            setTransport()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			Orient\Protocols\Common\TransportInterface		$transport
	 *
	 * @return 			Orient\PhoOrient
	 */
	public function setTransport(Orient\Protocols\Common\TransportInterface $transport){
		return $this->driver->setTransport($transport);
	}

	/**
	 * @name            setUserName()
	 *
	 * @since           1.0.1
	 * @version         1.0.1
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$username
	 *
	 * @return 			Orient\PhoOrient
	 */
	public function setUserName($username){
		return $this->driver->username = $username;
	}

	/**
	 * @name            shutDown()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$username
	 * @param			string		$password
	 */
	public function shutDown($username, $password){
		$this->driver->shutDown($username, $password);
	}

	/**
	 * @name            sqlBatch()
	 *
	 * @since           1.0.0
	 * @version         1.0.2
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$param
	 *
	 * @return 			mixed
	 */
	public function sqlBatch($param){
		return $this->driver->sqlBatch($param);
	}

	/**
	 * @name            query()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$query
	 * @param			integer		$limit
	 * @param			string		$fetchPlan
	 *
	 * @return			mixed
	 */
	public function query($query, $limit = 20, $fetchPlan = '*:0'){
		return $this->driver->query($query, $limit, $fetchPlan);
	}

	/**
	 * @name            queryAsync()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			string		$query
	 * @param			array		$params
	 *
	 * @return			mixed
	 */
	public function queryAsync($query, array $params = []){
		return $this->driver->queryAsync($query, $params);
	}

	/**
	 * @name            recordCreate()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			OrientData\Record		$record
	 *
	 * @return			OrientData\RecordCreate|OrientData\Record
	 */
	public function recordCreate(OrientData\Record $record){
		return $this->driver->recordCreate($record);
	}

	/**
	 * @name            recordDelete()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			OrientData\Id		$rid
	 *
	 * @return			OrientData\RecordCDelete|bool
	 */
	public function recordDelete(OrientData\Id $rid){
		return $this->driver->recordDelete($rid);
	}

	/**
	 * @name            recordLoad()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			OrientData\Id		$rid
	 * @param			array				$params
	 *
	 * @return			OrientData\recordLoad|OrientData\Record
	 */
	public function recordLoad(OrientData\Id $rid, array $params = []){
		return $this->driver->recordLoad($rid, $params);
	}

	/**
	 * @name            recordUpdate()
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @author          Can Berkol
	 *
	 * @param			OrientData\Record		$record
	 *
	 * @return			OrientData\RecordUpdate|OrientData\Record
	 */
	public function recordUpdate(OrientData\Record $record){
		return $this->driver->recordUpdate($record);
	}
}

/**
 * Change Log
 * **************************************
 * v1.0.1                      11.06.2015
 * Can Berkol
 * **************************************
 * BF :: sqlBatch() method was calling batch() instead of sqlBatch(). Fixed.
 *
 * **************************************
 * v1.0.1                      03.06.2015
 * Can Berkol
 * **************************************
 * BF :: Namespace issues fixed.
 * FR :: setHostname() method added.
 * FR :: setPassword() method added.
 * FR :: setPort() method added.
 * FR :: setUsername() method added.
 *
 * **************************************
 * v1.0.0                      01.06.2015
 * Can Berkol
 * **************************************
 * FR :: __construct()
 * FR :: command()
 * FR :: connect()
 * FR :: dataClusterAdd()
 * FR :: dataClusterCount()
 * FR :: dataClusterDataRange()
 * FR :: dataClusterDrop()
 * FR :: dbClose()
 * FR :: dbCreate()
 * FR :: dbDrop()
 * FR :: dbExists()
 * FR :: dbFreeze()
 * FR :: dbList()
 * FR :: dbOpen()
 * FR :: dbRelease()
 * FR :: dbReload()
 * FR :: dbSize()
 * FR :: execute()
 * FR :: getNewInstance()
 * FR :: getSessionToken()
 * FR :: getTransport()
 * FR :: getTransactionStatement()
 * FR :: setSessionToken()
 * FR :: setTransport()
 * FR :: shutDown()
 * FR :: sqlBatch()
 * FR :: query()
 * FR :: queryAsync()
 * FR :: recordCreate()
 * FR :: recordDelete()
 * FR :: recordLoad()
 * FR :: recordUpdate()
 */