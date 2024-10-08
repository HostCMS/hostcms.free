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
class Skin_Default_Property_Controller_Tab extends Property_Controller_Tab {

	public function imgBox($oAdmin_Form_Entity, $oProperty, $addFunction = '$.cloneProperty', $deleteOnclick = '$.deleteNewProperty(this)')
	{
		$oAdmin_Form_Entity
			->add(
				Admin_Form_Entity::factory('Div')
					->class('input-group-addon property')
					->add(
						Admin_Form_Entity::factory('Div')
							->class('no-padding-' . ($oProperty->type == 2 || $oProperty->type == 5 || $oProperty->type == 12 ? 'left' : 'right') . ' col-xs-12')
							->add($this->getImgAdd($oProperty, $addFunction))
							->add($this->getImgDelete($deleteOnclick))
					)
			);

		return $this;
	}
		
}