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
class Skin_Default_Admin_Form_Entity_Stars extends Skin_Default_Admin_Form_Entity_Select
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_A.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_A.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$this
			->options(
				array(
					1 => 'Poor',
					2 => 'Fair',
					3 => 'Average',
					4 => 'Good',
					5 => 'Excellent',
				)
			);

		return parent::execute();

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}