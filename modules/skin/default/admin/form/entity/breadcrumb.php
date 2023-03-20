<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Breadcrumb extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'name',
		'href',
		'onclick'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		?><a href="<?php echo htmlspecialchars((string) $this->href)?>" onclick="<?php echo htmlspecialchars((string) $this->onclick)?>"><?php echo htmlspecialchars((string) $this->name)?></a><?php
	}
}