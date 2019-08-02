<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XML entity
 *
 * @package HostCMS
 * @subpackage Core\Xml
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Xml_Entity extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'value'
	);

	/**
	 * Children entities
	 * @var array
	 */
	protected $_childrenEntities = array();

	/**
	 * Add a children entity
	 *
	 * @param Core_Entity $oChildrenEntity
	 * @return Core_Xml_Entity
	 */
	public function addEntity($oChildrenEntity)
	{
		$this->_childrenEntities[] = $oChildrenEntity;
		return $this;
	}

	/**
	 * Add children entities
	 *
	 * @param array $aChildrenEntities
	 * @return Core_Xml_Entity
	 */
	public function addEntities(array $aChildrenEntities)
	{
		foreach ($aChildrenEntities AS $oChildrenEntity)
		{
			$this->addEntity($oChildrenEntity);
		}
		return $this;
	}

	/**
	 * Get children entities
	 * @return array
	 */
	public function getEntities()
	{
		return $this->_childrenEntities;
	}

	/**
	 * Clear enities
	 * @return self
	 */
	public function clearEntities()
	{
		$this->_childrenEntities = array();
		return $this;
	}

	/**
	 * Attributes
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * Add attribute
	 *
	 * @param string $name
	 * @param string $value
	 * @return Core_Xml_Entity
	 */
	public function addAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;
		return $this;
	}

	/**
	 * Build entity XML
	 *
	 * @return string
	 */
	public function getXml()
	{
		$xml = '<' . $this->name;

		foreach ($this->_attributes as $attributeName => $attributeValue)
		{
			$xml .= ' ' . $attributeName . '="' . Core_Str::xml($attributeValue) . '"';
		}

		$xml .= '>';

		// Children entities
		if (!empty($this->_childrenEntities))
		{
			$xml .= "\n";

			foreach ($this->_childrenEntities as $oChildEntity)
			{
				$xml .= $oChildEntity->getXml();
			}
		}

		if (!is_null($this->value))
		{
			$xml .= Core_Str::xml($this->value);
		}

		$xml .= "</" . $this->name . ">\n";

		return $xml;
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		$oRetrun = new stdClass();

		foreach ($this->_attributes as $attributeName => $attributeValue)
		{
			$properttName = $attributePrefix . $attributeName;
			$oRetrun->$properttName = $attributeValue;
		}

		// Children entities
		if (!empty($this->_childrenEntities))
		{
			foreach ($this->_childrenEntities as $oChildEntity)
			{
				//$xml .= $oChildEntity->getXml();
				$childName = $oChildEntity instanceof Core_ORM
					? $oChildEntity->getModelName()
					: $oChildEntity->name;

				$childArray = $oChildEntity->getStdObject($attributePrefix);

				if (!isset($oRetrun->$childName))
				{
					$oRetrun->$childName = $childArray;
				}
				else
				{
					// Convert to array
					!is_array($oRetrun->$childName) && $oRetrun->$childName = array($oRetrun->$childName);

					// array_push($oRetrun->$childName, $childArray);
					$oRetrun->{$childName}[] = $childArray;
				}
			}
		}

		if (!is_null($this->value))
		{
			count(get_object_vars($oRetrun))
				? $oRetrun->value = $this->value
				: $oRetrun = $this->value;
		}

		return $oRetrun;
	}
}