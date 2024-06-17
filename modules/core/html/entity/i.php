<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * i entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_I extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value' // идет в значение <span>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><i <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		parent::execute();
		?></i><?php
	}
}