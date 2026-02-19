<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	protected $_skipProperties = array(
		'value'
	);

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_A.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_A.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$aAttr = $this->getAttrsString();

		echo PHP_EOL;

		?><a <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		$this->executeChildren();
		?></a><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}