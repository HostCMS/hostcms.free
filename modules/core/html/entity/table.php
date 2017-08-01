<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * table entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Table extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'align',
		'background',
		'bgcolor',
		'border',
		'bordercolor',
		'cellpadding',
		'cellspacing',
		'cols',
		'frame',
		'height',
		'rules',
		'summary',
		'width'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array();

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><table <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?></table><?php
	}
}