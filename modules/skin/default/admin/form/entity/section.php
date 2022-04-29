<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 */
	public function execute()
	{
		//if (count($this->_children) > 1)
		//{
		?><div class="section_title"><?php echo $this->caption?></div><?php
		?><div class="section"><?php

		//}
		$this->executeChildren();
		?></div><?php
	}
}