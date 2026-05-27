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
class Skin_Bootstrap_Field_Controller_Tab extends Field_Controller_Tab {

	public function imgBox($oAdmin_Form_Entity, $oField, $addFunction = '$.cloneField', $deleteOnclick = '$.deleteNewField(this)')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oAdmin_Form_Entity
			->add(
				Admin_Form_Entity::factory('Div')
					->class('input-group-addon add-remove-property')
					->add(
						Admin_Form_Entity::factory('Div')
						->class('btn-group')
						->add(
							Admin_Form_Entity::factory('Div')
								->class('btn btn-palegreen btn-clone inverted')
								->add(Admin_Form_Entity::factory('Code')->html('<i class="fa-solid fa-circle-plus close"></i>'))
								->onclick("{$addFunction}('{$windowId}', '{$oField->id}'); event.stopPropagation();")
						)
						->add(
							Admin_Form_Entity::factory('Div')
								->class('btn btn-darkorange btn-delete inverted')
								->add(Admin_Form_Entity::factory('Code')->html('<i class="fa-solid fa-circle-minus close"></i>'))
								->onclick($deleteOnclick . '; event.stopPropagation();')
						)
					)
			);

		return $this;
	}
}