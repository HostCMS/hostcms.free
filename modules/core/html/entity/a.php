<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * a entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_A extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'accesskey',
		'charset',
		'coords',
		'href',
		'hreflang',
		'name',
		'rel',
		'rev',
		'shape',
		'tabindex',
		'target',
		'title'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'value' // идет в значение <span>
	);
	
	/**
	 * Object has unlimited number of properties
	 * @var boolean
	 */
	protected $_unlimitedProperties = TRUE;
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><a <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		parent::execute();
		?></a><?php
	}
}