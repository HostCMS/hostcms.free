<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Lefttabs extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'formId'
	);

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Lefttabs.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Lefttabs.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$tab_id = 0;
		foreach ($this->_children as $oAdmin_Form_Tab_Entity)
		{
			?><div id="tab_page_<?php echo $oAdmin_Form_Tab_Entity->active ? $tab_id : ''?>"><?php
			$oAdmin_Form_Tab_Entity->execute();
			?></div><?php
			$oAdmin_Form_Tab_Entity->active && $tab_id++;
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}