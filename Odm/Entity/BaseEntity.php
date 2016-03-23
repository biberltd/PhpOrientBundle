<?php
/**
 * 2016 (C) BOdev Office | bodevoffice.com
 *
 * @license MIT
 *
 * Developed by Biber Ltd. (http://www.biberltd.com), a partner of BOdev Office (http://www.bodevoffice.com)
 *
 * Paid Customers ::
 *          
 * Check http://team.bodevoffice.com for technical documentation or consult your representative.
 *
 * Contact support@bodevoffice.com for support requests.
 */
namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Entity;

use BiberLtd\Bundle\PhpOrientBundle\Odm\Types\ORecordId;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Mapping\Column;
use PhpOrient\Protocols\Binary\Data\ID as ID;
use PhpOrient\Protocols\Binary\Data\Record as ORecord;
use Doctrine\Common\Annotations\AnnotationReader as AnnotationReader;

class BaseEntity{
	/**
	 * @ORM\Column(type="ORecordId")
	 */
	public $rid = null;
	/**
	 * @var bool
	 */
	protected $modified = false;
	/** @var  \DateTime */
	protected $dateAdded;
	/** @var  \DateTime */
	protected $dateUpdated;
	/** @var  \DateTime|null */
	protected $dateRemoved = null;
	/** @var  string $version md5 Hash of object serialization */
	protected $versionHash;
	/** @var array Version history, the first element is always the original version */
	protected $versionHistory = [];
	/** @var \PhpOrient\Protocols\Binary\Data\Record Stores the original Orient Record  */
	protected $record;
	/** @var array Holds definition of all public properties of a class for serialization purposes. */
	private $props = [];
	/** @var array Holds annotation definitions. */
	private $propAnnotations = [];

	protected $controller;

	/**
	 * BaseEntity constructor.
	 *
	 * @param null                                         $controller
	 * @param \PhpOrient\Protocols\Binary\Data\Record|null $record
	 * @param string                                       $timezone
	 */
	public function __construct($controller, ORecord $record = null, $timezone = 'Europe/Istanbul'){
		$this->controller = $controller;
		$this->prepareProps()->preparePropAnnotations();
		if(is_null($record)){
			$this->dateAdded = new \DateTime('now', new \DateTimeZone($timezone));
			$this->record = $record;
			$this->dateUpdated = $this->dateAdded;
			$this->setDefaults();
		}
		else{
			$this->convertRecordToOdmObject($record);
		}
		$this->versionHistory[] = $this->output('json');
		$this->versionHash = md5(array_pop($this->versionHistory));
	}

	/**
	 * @return bool
	 */
	final public function isModified(){
		if($this->getUpdatedVersionHash() === $this->versionHash){
			$this->modified = false;
			return false;
		}
		$this->modified = true;
		return true;
	}

	/**
	 * @return $this
	 */
	final public function setVersionHistory(){
		$this->versionHistory[] = $this->output('json');
		if($this->versionHash !== $this->getUpdatedVersionHash() && !$this->modified){
			$this->modified = true;
		}
		else{
			$this->modified = false;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	final protected function getUpdatedVersionHash(){
		return md5($this->output('json'));
	}

	/**
	 * Alias to getRid() method
	 *
	 * @return ID
	 */
	public function getRecordId($as = 'object'){
		return $this->getRid($as);
	}

	/**
	 * @return ID
	 */
	public function getRid($as = 'object'){
		if($as == 'string'){
			/**
			 * @var ID $id
			 */
			$id = $this->rid->getValue();
			return '#'.$id->cluster.':'.$id->position;
		}
		return $this->rid->getValue();
	}
	/**
	 * Alias to setRid() method.
	 *
	 * @param $rid
	 *
	 * @return $this
	 */
	public function setRecordId($rid){
		return $this->setRid($rid);
	}

	/**
	 * @param $rid
	 *
	 * @return $this
	 */
	public function setRid($rid){
		$this->rid = new ORecordId($rid);
		return $this;
	}
	/**
	 * @param \PhpOrient\Protocols\Binary\Data\Record $record
	 */
	public function convertRecordToOdmObject(ORecord $record){
		$this->rid = new ORecordId($record->getRid());
		$recordData = $record->getOData();
		foreach($this->propAnnotations as $propName => $propAnnotations){
			if($propName == 'rid'){
				continue;
			}
			foreach($propAnnotations as $propAnnotation){
				if($propAnnotation instanceof Column){
					$set = 'set'.ucfirst($propName);
					if(isset($recordData[$propName])){
						$this->$set($recordData[$propName]);
					}
				}
			}
		}
	}

	/**
	 * @return $this
	 */
	final private function prepareProps(){
		$reflectionClass = new \ReflectionClass($this);
		$this->props = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
		return $this;
	}
	/**
	 * @return $this
	 */
	final private function preparePropAnnotations(){
		$annoReader = new AnnotationReader();
		foreach($this->props as $aProperty){
			$aPropertyReflection = new \ReflectionProperty(get_class($this), $aProperty->getName());
			$this->propAnnotations[$aProperty->getName()] = $annoReader->getPropertyAnnotations($aPropertyReflection);
		}
	    return $this;
	}
	/**
	 * @param $propertyName
	 *
	 * @return mixed
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 */
	public function getColumnDefinition($propertyName){
		$aPropertyReflection = new \ReflectionProperty(get_class($this), $propertyName);
		$annoReader = new AnnotationReader();
		$propAnnotations = $annoReader->getPropertyAnnotations($aPropertyReflection);
		foreach($propAnnotations as $aPropAnnotation){
			if($aPropAnnotation instanceof Column){
				return $aPropAnnotation;
			}
		}
		throw new AnnotationException();
	}

	/**
	 * @param $propertyName
	 *
	 * @return mixed
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 */
	public function getColumnType($propertyName){
		$colDef = $this->getColumnDefinition($propertyName);

		return $colDef->type;
	}

	/**
	 * @param string     $to
	 * @param array|null $props
	 *
	 * @return string
	 */
	public function output($to = 'json', array $props = null){
		switch($to){
			case 'json':
				return $this->outputToJson($props);
			case 'xml':
				return $this->outputToXml($props);
		}
	}

	/**
	 * @param array|null $props
	 *
	 * @return \stdClass
	 */
	public function getRepObject(array $props = null){
		$objRepresentation = new \stdClass();
		if(isset($this->controller->dateTimeFormat)){
			$dtFormat = $this->controller->dateTimeFormat;
		}
		else{
			$dtFormat = 'd.m.Y H:i:s';
		}
		foreach($this->props as $aProperty){
			$propName = $aProperty->getName();
			if(!is_null($props) && !in_array($propName, $props)){
				continue;
			}
			if(!is_null($this->$propName)){
				if(is_array($this->$propName->getValue())){
					$collection = [];
					foreach($this->$propName->getValue() as $anItem){
						if($anItem instanceOf ID){
							$collection[] = '#'.$anItem->cluster.':'.$anItem->position;
						}
						else if($anItem instanceOf \DateTime){
							$collection[] = $anItem->format($dtFormat);
						}
						else if(is_object($anItem) && method_exists($anItem, 'getValue')){
							$collection[] = $anItem->getValue();
						}
						else{
							$collection[] = $anItem;
						}
					}
					$objRepresentation->$propName = $collection;
				}
				else if($this->$propName->getValue() instanceOf \DateTime){
					$objRepresentation->$propName = $this->$propName->getValue()->format($dtFormat);
				}
				else if($this->$propName->getValue() instanceOf ID){
					$idObj = $this->$propName->getValue();
					$objRepresentation->$propName = '#'.$idObj->cluster.':'.$idObj->position;
				}
				else{
					$objRepresentation->$propName = $this->$propName->getValue();
				}
			}
			else{
				$objRepresentation->$propName = null;
			}
		}

		return $objRepresentation;
	}

	/**
	 * @param array $props
	 *
	 * @return string
	 */
	final private function outputToJson(array $props = null){
		return json_encode($this->getRepObject($props));
	}

	/**
	 * @param array $props
	 * @return string
	 *
	 * @todo !! BE AWARE !! xmlrpc_encode is an experimental method.
	 */
	final private function outputToXml(array $props = null){
		return xmlrpc_encode($this->getRepObject($props));
	}

	/**
	 * @return array
	 */
	final public function getProps(){
		return $this->props;
	}

	/**
	 * @return $this
	 */
	private function setDefaults(){
		$nsRoot = 'BiberLtd\\Bundle\\PhpOrientBundle\\Odm\\Types\\';
		foreach($this->props as $aProperty){
			/**
			 * @var \ReflectionProperty $aProperty
			 */
			$propName = $aProperty->getName();
			$colType = $this->getColumnType($propName);
			$class = $nsRoot.$colType;
			$this->$propName = new $class();
		}
		return $this;
	}
}