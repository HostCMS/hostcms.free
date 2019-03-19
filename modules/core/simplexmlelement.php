<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SimpleXMLElement
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_SimpleXMLElement extends SimpleXMLElement
{
	/**
	 * Adds a child element to the node and returns a SimpleXMLElement of the child
	 * @param string $name element name
	 * @param string $value element value
	 * @param string $namespace namespace
	 */
	public function addChild($name, $value = null, $namespace = null)
	{
		return parent::addChild(Core_Str::xml($name), Core_Str::xml($value), $namespace);
	}

	/**
	 * Adds an attribute to the SimpleXML element
	 * @param string $name attribute name
	 * @param string $value attribute value
	 * @param string $namespace namespace
	 */
	public function addAttribute($name, $value = null, $namespace = null)
	{
		return parent::addAttribute(Core_Str::xml($name), Core_Str::xml($value), $namespace);
	}
}