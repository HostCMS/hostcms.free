<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * td entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Td extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'abbr',
		'align',
		'axis',
		'background',
		'bgcolor',
		'bordercolor',
		'char',
		'charoff',
		'colspan',
		'headers',
		'height',
		'nowrap',
		'rowspan',
		'scope',
		'valign',
		'width'
	);

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
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;

		// htmlspecialchars($this->value) - acronym
		?><td <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		parent::execute();
		?></td><?php
	}
}