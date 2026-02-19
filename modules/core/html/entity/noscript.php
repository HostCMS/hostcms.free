<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * noscript entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Html_Entity_Noscript extends Core_Html_Entity
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
		'value' // идет в значение <noscript>
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		echo PHP_EOL;

		?><noscript><?php echo $this->value?><?php
			parent::execute();
		?></noscript><?php
	}
}