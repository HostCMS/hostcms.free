<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XML entity
 *
 * @package HostCMS
 * @subpackage Core\Xml
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	 * @return self
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
	 * @return self
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
	 * @return self
	 */
	public function addAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;
		return $this;
	}

	/**
	 * External XML tags for entity.
	 *
	 * @var array
	 */
	protected $_xmlTags = array();

	/**
	 * Add external tag for entity
	 * @param string $tagName tag name
	 * @param string $tagValue tag value
	 * @param array $attributes attributes
	 * @return self
	 */
	public function addXmlTag($tagName, $tagValue, array $attributes = array())
	{
		//if (!isset($this->_forbiddenTags[$tagName]))
		//{
		$this->_xmlTags[] = array($tagName, $tagValue, $attributes);
		//}
		return $this;
	}

	/**
	 * Clear external XML tags for entity.
	 */
	public function clearXmlTags()
	{
		$this->_xmlTags = array();
		return $this;
	}

	/**
	 * Get external XML tags for entity.
	 * @return array
	 */
	public function getXmlTags()
	{
		return $this->_xmlTags;
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

		// External tags
		foreach ($this->_xmlTags as $aTag)
		{
			$xml .= "<{$aTag[0]}";

			if (isset($aTag[2]))
			{
				foreach ($aTag[2] as $tagName => $tagValue)
				{
					$xml .= " {$tagName}=\"" . Core_Str::xml($tagValue) . "\"";
				}
			}

			$xml .= ">" . Core_Str::xml($aTag[1]) . "</{$aTag[0]}>\n";
		}

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

		// External tags
		foreach ($this->_xmlTags as $aTag)
		{
			$sTmp = $aTag[0];
			if (empty($aTag[2]))
			{
				$oRetrun->$sTmp = $aTag[1];
			}
			else
			{
				$stdClass = new stdClass();
				$stdClass->value = $aTag[1];

				foreach ($aTag[2] as $tagName => $tagValue)
				{
					$properttName = $attributePrefix . $tagName;
					$stdClass->$properttName = $tagValue;
				}

				$oRetrun->$sTmp = $stdClass;
			}
		}

		// Children entities
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

		if (!is_null($this->value))
		{
			count(get_object_vars($oRetrun))
				? $oRetrun->value = $this->value
				: $oRetrun = $this->value;
		}

		return $oRetrun;
	}
}