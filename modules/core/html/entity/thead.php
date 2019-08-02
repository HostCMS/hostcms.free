<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * thead entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Thead extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'align',
		'char',
		'charoff',
		'bgcolor',
		'valign'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><thead <?php echo implode(' ', $aAttr) ?>><?php
		parent::execute();
		?></thead><?php
	}
}