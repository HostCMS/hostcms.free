<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * h5 entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Html_Entity_H5 extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'align'
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
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		?><h5 <?php echo implode(' ', $aAttr)?>><?php echo $this->value?><?php
		parent::execute();
		?></h5><?php
	}
}