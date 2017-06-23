<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * li entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Li extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'type',
		'value'
	); 
	
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'liValue' // идет в значение <li>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		// htmlspecialchars($this->value) - acronym
		?><li <?php echo implode(' ', $aAttr) ?>><?php echo $this->liValue?><?php
		parent::execute();
		?></li><?php
	}
}