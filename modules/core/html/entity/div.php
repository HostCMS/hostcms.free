<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * div entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Html_Entity_Div extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'align',
		'title'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value' // идет в значение <div>
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;
		
		?><div <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		parent::execute();
		?></div><?php
	}
}