<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Types
 * @name        RecordId
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Types;

use BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException;
use PhpOrient\Protocols\Binary\Data\ID as ID;

class ORecordId extends BaseType{

	/** @var  $value integer */
	protected $value;

	/**
	 * @param ID $value
	 *
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 */
	public function __construct($value = null){
		parent::__construct('ORecordId', $value);
	}

	/**
	 * @return int
	 */
	public function getValue(){
		return $this->value;
	}

	/**
	 * @param $value
	 *
	 * @return $this
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions\InvalidValueException
	 * @throws \BiberLtd\Bundle\PhpOrientBundle\Odm\Types\InvalidRecordIdString
	 */
	public function setValue($value){
		if(!$this->validateValue($value)){
			throw new InvalidValueException('ORecordId');
		}
		if($value instanceof ID){
			$this->value = $value;
		}
		else if(is_array($value) && count($value) === 2){
			$this->value = new ID($value[0], $value[1]);
		}
		else if (is_string($value)){
			if(strpos($value, '#') !== 0){
				throw new InvalidRecordIdString();
			}
			$value = str_replace('#', '', $value);
			$value = explode(':', $value);
			if(count($value) !== 2){
				throw new InvalidRecordIdString();
			}
			$this->value = new ID($value[0], $value[1]);
		}

		unset($value);
		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function validateValue($value){
		if($value instanceof ID){
			return true;
		}
		else if(is_array($value) && count($value) === 2){
			return true;
		}
		else{
			if(is_string($value)){
				if(strpos($value, '#') !== 0){
					return false;
				}
				$value = str_replace('#', '', $value);
				$value = explode(':', $value);
				if(count($value) !== 2){
					return false;
				}
				return true;
			}
			elseif($value == null){
				return true;
			}
		}
		return true;

	}

}