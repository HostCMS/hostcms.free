<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * br entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Br extends Core_Html_Entity
{
	/**
	 * Use common attributes
	 * @var boolean
	 */
	protected $_useAttrCommon = FALSE;
	
	/**
	 * Use common events
	 * @var boolean
	 */
	protected $_useAttrEvent = FALSE;
	
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'clear'
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;
		
		?><br <?php echo implode(' ', $aAttr) ?>/><?php
	}
}