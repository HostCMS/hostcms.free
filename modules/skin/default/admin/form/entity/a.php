<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_A extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
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
		
		?><a <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		$this->executeChildren();
		?></a><?php
	}
}