<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * tr entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Tr extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'align',
		'bgcolor',
		'bordercolor',
		'char',
		'charoff',
		'valign'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array();

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><tr <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?></tr><?php
	}
}