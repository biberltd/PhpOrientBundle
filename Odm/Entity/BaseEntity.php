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

use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidRecordIdString;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Types\ORecordId;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Mapping\Column;
use PhpOrient\Protocols\Binary\Data\ID as ID;
use PhpOrient\Protocols\Binary\Data\Record as ORecord;
use Doctrine\Common\Annotations\AnnotationReader as AnnotationReader;
use BiberLtd\Bundle\PhpOrientBundle\Odm\Repository\BaseRepository;

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
		//$this->versionHistory[] = $this->output('json');
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
     * @param $name
     * @param $arguments
     * @return BaseEntity
     * @throws InvalidRecordIdString
     * @throws \Exception
     */
    public function __call($name, $arguments) {

        //Getting and setting with $this->property($optional);

        if (property_exists(get_class($this), $name)) {


            //Always set the value if a parameter is passed
            if (count($arguments) == 1) {
                /* set */
                $this->$name = $arguments[0];
            } else if (count($arguments) > 1) {
                throw new \Exception("Setter for $name only accepts one parameter.");
            }

            //Always return the value (Even on the set)
            return $this->$name->getValue();
        }

        //If it doesn't chech if its a normal old type setter ot getter
        //Getting and setting with $this->getProperty($optional);
        //Getting and setting with $this->setProperty($optional);

        $prefix = substr($name, 0, 3);
        $property = strtolower($name[3]) . substr($name, 4);
        if (!property_exists(get_class($this), $property)) {
            $property = strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $property));
        }
        switch ($prefix) {
            case 'get':
                $colType = 'BiberLtd\\Bundle\\PhpOrientBundle\\Odm\\Types\\' . $this->getColumnType($property);
                $onrow=false;
                switch ($this->getColumnType($property))
                {
                    case 'OLink':
                        $onrow = true;
                    case 'OLinkList':
                    case 'OLinkSet':
                    case 'OLinkMap':
                        if($this->ifHasLinkedClass($property))
                        {
                            /*$linkedObj = $this->getNameSpace().$this->getColumnOptions($property)['class'];

                            if($this->$property!=null && !$this->$property->getValue() instanceof $linkedObj)
                            {
                                $repoClass = $this->createRepository($property);
                                $obj=[];
                                $data = $onrow ? [$this->$property->getValue()] : $this->$property->getValue();

                                foreach ($data as $item)
                                {
                                    if($item instanceof ID)
                                    {
                                        $response = $repoClass->selectByRid($item);
                                        if($response->code==200)
                                            $obj[] = new $linkedObj($this->controller,$response->result);
                                    }
                                }
                                $obj = $onrow ? $obj[0] : $obj;
                            }else{
                                if(!$this->$property instanceof $linkedObj)
                                $obj = new $linkedObj($this->controller);
                                else
                                    $obj = $this->$property;
                            }

                            return $obj;*/
                            return $this->$property;
                        }else{

                            return $this->$property->getValue();
                        }
                    default:
                        return $this->$property->getValue();

                }
                //return $this->$property!=null ? $this->$property->getValue() : $this->$property;

                break;
            case 'set':


                //Always set the value if a parameter is passed
                if (count($arguments) != 1) {
                    throw new \Exception("Setter for $name requires exactly one parameter.");
                }
                $colType = 'BiberLtd\\Bundle\\PhpOrientBundle\\Odm\\Types\\' . $this->getColumnType($property);
                $onrow=false;

                switch ($this->getColumnType($property)) {
                    case 'OLink':
                        $onrow = true;
                        break;
                    case 'OLinkList':
                    case 'OLinkSet':
                    case 'OLinkMap':
                        if($this->ifHasLinkedClass($property)) {
                            $linkedObj = $this->getNameSpace() . $this->getColumnOptions($property)['class'];

                            $repoClass = $this->createRepository($property);

                            $data = $onrow ? [$arguments[0]] : $arguments[0];
                            $obj=[];

                            foreach ($data as $item)
                            {
                                if($item!=null)
                                {
                                    $response = $repoClass->selectByRid($item);

                                    if($response->code==200)
                                        $obj[] = new $linkedObj($this->controller,$response->result);
                                }else{
                                    $obj[] = new $linkedObj($this->controller);
                                }

                            }

                            $this->$property = $onrow ? $obj[0] : $obj;
                        }else{
                            if(isset($arguments[0]))
                            {

                                $data = $onrow ? [$arguments[0]]  : $arguments[0];
                                $returnData=[];
                                foreach ($data as $item)
                                {

                                    if(!is_null($item) && !is_string($item)) {
                                        if(!($item instanceof ID))
                                            throw new InvalidRecordIdString();
                                    }
                                    if(is_string($item)) {
                                        $id = new ID($item);
                                        $returnData[]  = $id;
                                    }
                                    else if($item instanceof ID) {
                                        $returnData[]= $item;
                                    }

                                }
                                $this->$property = $onrow ? new $colType($returnData[0]) : new $colType($returnData);

                            }

                        }
                        break;
                    default:
                        $this->$property = new $colType($arguments[0]);

                }
                //Always return the value (Even on the set)
                return $this->setVersionHistory();
                break;
            default:
                throw new \Exception("Property $name doesn't exist.");
                break;
        }
    }

    /**
     * @param $property
     * @return BaseRepository|__anonymous@11869
     */
    private function createRepository($property)
    {
        $kernel = $this->controller->getApplication()->getKernel();

        //$repo = str_replace('Entity','Repository',$this->getNameSpace()).$this->getColumnOptions($property)['class'].'Repository';
        //$repoClass = new $repo(['container'=>$kernel->getContainer(),'controller'=>$this->controller],'localhost',2424,'','root','root');

        $className = $this->getColumnOptions($property)['class'];
        $repoClass = new class(['container'=>$kernel->getContainer(),'controller'=>$this->controller],'localhost',2424,'','root','root') extends BaseRepository {
            protected $class;

            public function __construct($container, $hostname, $port, $token, $dbUsername, $dbPass){
                parent::__construct($container, $hostname, $port, $token, $dbUsername, $dbPass);
                $this->oService->dbOpen('ViaApi2', ['username' => $dbUsername, 'password' => $dbPass]);
            }

            public function setClass($class){
                $this->class = $class;

            }
        };
        $repoClass->setClass($className);

        return $repoClass;
    }
    private function ifHasLinkedClass($property)
    {
        $options = $this->getColumnOptions($property);
        if(!is_array($options)) return false;
        if(!array_key_exists('class',$options)) return false;

        return true;
    }
    private function getNameSpace()
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getNamespaceName().'\\';
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
     * @param $propertyName
     *
     * @return mixed
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function getColumnOptions($propertyName){
        $colDef = $this->getColumnDefinition($propertyName);

        return isset($colDef->options) ? $colDef->options : false;
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
				if(method_exists($this->$propName, 'getValue') && is_array($this->$propName->getValue())){
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
				else if(method_exists($this->$propName, 'getValue') && $this->$propName->getValue() instanceOf \DateTime){
					$objRepresentation->$propName = $this->$propName->getValue()->format($dtFormat);
				}
				else if(method_exists($this->$propName, 'getValue') && $this->$propName->getValue() instanceOf ID){
					$idObj = $this->$propName->getValue();
					$objRepresentation->$propName = '#'.$idObj->cluster.':'.$idObj->position;
				}
				elseif(method_exists($this->$propName, 'getValue')){
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