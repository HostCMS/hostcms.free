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
class Skin_Default_Admin_Form_Entity_Section extends Admin_Form_Entity
{
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'caption'
	);

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Section.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Section.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		//if (count($this->_children) > 1)
		//{
		?><div class="section_title"><?php echo $this->caption?></div><?php
		?><div class="section"><?php

		//}
		$this->executeChildren();
		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}