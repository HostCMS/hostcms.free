<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * noscript entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Noscript extends Core_Html_Entity
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