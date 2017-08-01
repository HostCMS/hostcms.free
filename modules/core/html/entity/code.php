<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HTML
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Code extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array();

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'value' // идет в значение <span>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		echo $this->value;
	}
}