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
class Skin_Default_Admin_Form_Entity_Code extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'html'
	);

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Code.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Code.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		echo $this->html;

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}