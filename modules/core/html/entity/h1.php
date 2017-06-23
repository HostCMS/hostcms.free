<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * h1 entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_H1 extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'align'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'value'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		?><h1 <?php echo implode(' ', $aAttr)?>><?php echo $this->value?><?php
		parent::execute();
		?></h1><?php
	}
}