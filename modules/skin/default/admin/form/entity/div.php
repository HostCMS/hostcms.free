<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Div extends Admin_Form_Entity
{
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'divAttr',
		'value'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		?><div <?php echo implode(' ', $aAttr)?>><?php echo htmlspecialchars($this->value)?><?php
		$this->executeChildren();
		?></div><?php
	}
}