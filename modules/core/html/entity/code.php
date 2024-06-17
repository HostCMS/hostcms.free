<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HTML
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Code extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		echo $this->value;
	}
}