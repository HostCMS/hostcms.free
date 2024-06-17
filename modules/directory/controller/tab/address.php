<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab_Address
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Controller_Tab_Address extends Directory_Controller_Tab
{
	protected $_directoryTypeName = 'Directory_Address_Type';

	protected $_titleHeaderColor = 'darkorange';

	protected $_faTitleIcon = 'fa fa-map-marker';

	protected function _execute($oPersonalDataInnerWrapper)
	{
		$aMasDirectoryTypes = $this->_getDirectoryTypes();

		// $oButtons = $this->_buttons('address-buttons');
		$oButtons = $this->_buttons('');

		if (count($this->_aDirectory_Relations))
		{
			foreach ($this->_aDirectory_Relations as $oDirectory_Relation)
			{
				$oRowElements = $this->_addressTemplate($aMasDirectoryTypes, $oDirectory_Relation);

				$oPersonalDataInnerWrapper->add(
					$oRowElements->add($oButtons)
				);
			}
		}
		else
		{
			$oRowElements = $this->_addressTemplate($aMasDirectoryTypes);

			$oPersonalDataInnerWrapper->add(
				$oRowElements->add($oButtons)
			);
		}
	}

	protected function _addressTemplate($aMasDirectoryAddressTypes, $oUser_Directory_Address = NULL)
	{
		$sNameSuffix = $oUser_Directory_Address ? '#' . $oUser_Directory_Address->Directory_Address->id : '[]';

		$oRowElements = Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Select')
					->options($aMasDirectoryAddressTypes)
					->name($this->prefix . 'address_type' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->directory_address_type_id : '')
					->caption(Core::_('Directory_Address.type_address'))
					->divAttr(array('class' => 'form-group col-xs-6 col-lg-3'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'address_country' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->country : '')
					->caption(Core::_('Directory_Address.address_country'))
					// ->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-xs-3' : 'col-lg-5 col-sm-6 col-xs-5')))
					->divAttr(array('class' => 'form-group no-padding-left col-xs-6 col-lg-4'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'address_postcode' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->postcode : '')
					->caption(Core::_('Directory_Address.address_postcode'))
					// ->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-xs-3' : 'col-lg-5 col-sm-6 col-xs-5')))
					->divAttr(array('class' => 'form-group no-padding-left-lg col-xs-6 col-lg-2'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'address_city' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->city : '')
					->caption(Core::_('Directory_Address.address_city'))
					// ->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-xs-3' : 'col-lg-5 col-sm-6 col-xs-5')))
					->divAttr(array('class' => 'form-group no-padding-left col-xs-6 col-lg-3'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'address' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->value : '')
					->caption(Core::_('Directory_Address.address'))
					// ->divAttr(array('class' => 'form-group ' . ($this->showPublicityControlElement ? 'col-sm-8 col-xs-3' : 'col-lg-5 col-sm-6 col-xs-5')))
					->divAttr(array('class' => 'form-group col-xs-12 col-lg-6'))
					->class('form-control bold')
			)->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'address_house' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->house : '')
					->caption(Core::_('Directory_Address.house'))
					->divAttr(array('class' => 'form-group no-padding-left-lg col-xs-6 col-lg-3'))
			)->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'address_flat' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->flat : '')
					->caption(Core::_('Directory_Address.flat'))
					->divAttr(array('class' => 'form-group col-xs-6 col-lg-3 no-padding-left-sm no-padding-left-xs'))
			);

		$oRowElements
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'latitude' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->latitude : '')
					->caption(Core::_('Directory_Address.latitude'))
					->divAttr(array('class' => 'form-group col-xs-6 col-lg-2'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'longitude' . $sNameSuffix)
					->value($oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->longitude : '')
					->caption(Core::_('Directory_Address.longitude'))
					->divAttr(array('class' => 'form-group col-xs-6 col-lg-2 no-padding-left-sm no-padding-left-xs'))
			);

		if ($this->showPublicityControlElement)
		{
			$iAddressPublic = $oUser_Directory_Address ? $oUser_Directory_Address->Directory_Address->public : 0;

			$oRowElements->add(
				Admin_Form_Entity::factory('Checkbox')
					// ->divAttr(array('class' => 'col-xs-6 col-lg-2 margin-top-23-lg address-public'))
					->divAttr(array('class' => 'col-xs-5 col-lg-2 margin-top-23-lg no-padding-lg'))
					->name($this->prefix . 'address_public' . $sNameSuffix)
					->value(1)
					->checked($iAddressPublic ? $iAddressPublic : FALSE)
					->caption(Core::_('Directory_Address.address_public'))
			);

			// Для нового свойства добавляет скрытое поле, хранящее состояние чекбокса
			/*if (!$oUser_Directory_Address)
			{
				$oRowElements2->add(
					Core_Html_Entity::factory('Input')
						->type('hidden')
						->value(0)
						->name($this->prefix . 'address_public_value' . $sNameSuffix)
				);
			}*/
		}

		return $oRowElements;
	}

	protected function _buttons($className = '')
	{
		$margin_top_23 = $this->showPublicityControlElement
			? 'margin-top-23-lg no-padding-left-lg'
			: 'margin-top-23 no-padding-left';

		return Admin_Form_Entity::factory('Div') // div с кноками + и -
			->class('add-remove-property ' . $margin_top_23 . ' pull-right' . (count($this->_aDirectory_Relations) ? ' btn-group' : '') . ($className ? ' ' . $className : ''))
			->add(
				Admin_Form_Entity::factory('Code')
					->html('<div class="btn btn-palegreen inverted" onclick="$.cloneFormRow(this); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div><div class="btn btn-darkorange btn-delete inverted' . (count($this->_aDirectory_Relations) ? '' : ' hide') . '" onclick="$.deleteFormRow(this); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>')
			);
	}

	public function applyObjectProperty($Admin_Form_Controller, $object)
	{
		$windowId = $Admin_Form_Controller->getWindowId();

		$prefix = preg_replace('/[^A-Za-z0-9_-]/', '', $this->prefix);

		// Адреса, установленные значения
		$aDirectory_Addresses = $object->Directory_Addresses->findAll(FALSE);
		foreach ($aDirectory_Addresses as $oDirectory_Address)
		{
			$sAddress = Core_Array::getPost("{$prefix}address#{$oDirectory_Address->id}", NULL, 'trim');
			$sCountry = Core_Array::getPost("{$prefix}address_country#{$oDirectory_Address->id}", NULL, 'string');
			$sPostcode = Core_Array::getPost("{$prefix}address_postcode#{$oDirectory_Address->id}", NULL, 'string');
			$sCity = Core_Array::getPost("{$prefix}address_city#{$oDirectory_Address->id}", NULL, 'string');
			$sHouse = Core_Array::getPost("{$prefix}address_house#{$oDirectory_Address->id}", NULL, 'string');
			$sFlat = Core_Array::getPost("{$prefix}address_flat#{$oDirectory_Address->id}", NULL, 'string');

			if (strlen($sAddress) || strlen($sCountry) || strlen($sPostcode) || strlen($sCity) || strlen($sHouse) || strlen($sFlat))
			{
				$oDirectory_Address
					->directory_address_type_id(Core_Array::getPost("{$prefix}address_type#{$oDirectory_Address->id}", 0, 'int'))
					->public(Core_Array::getPost("{$prefix}address_public#{$oDirectory_Address->id}", 0, 'int'))
					->country($sCountry)
					->postcode($sPostcode)
					->city($sCity)
					->value($sAddress)
					->house($sHouse)
					->flat($sFlat)
					->latitude(Core_Array::getPost("{$prefix}latitude#{$oDirectory_Address->id}", '', 'string'))
					->longitude(Core_Array::getPost("{$prefix}longitude#{$oDirectory_Address->id}", '', 'string'))
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core_Html_Entity::factory('Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='{$prefix}address_type#{$oDirectory_Address->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$Admin_Form_Controller->addMessage(ob_get_clean());

				$oDirectory_Address->delete();
			}
		}

		// Адреса, новые значения
		$aAddress_Types = Core_Array::getPost($prefix . 'address_type', array());
		$aAddresses = Core_Array::getPost($prefix . 'address', array());
		$aAddress_Country = Core_Array::getPost($prefix . 'address_country', array());
		$aAddress_Postcode = Core_Array::getPost($prefix . 'address_postcode', array());
		$aAddress_City = Core_Array::getPost($prefix . 'address_city', array());
		$aAddress_House = Core_Array::getPost($prefix . 'address_house', array());
		$aAddress_Flat = Core_Array::getPost($prefix . 'address_flat', array());
		$aLatitudes = Core_Array::getPost($prefix . 'latitude', array());
		$aLongitudes = Core_Array::getPost($prefix . 'longitude', array());
		$aAddress_Public = Core_Array::getPost($prefix . 'address_public', array());

		if (is_array($aAddresses) && count($aAddresses))
		{
			$i = 0;
			foreach ($aAddresses as $key => $sAddress)
			{
				$sAddress = trim($sAddress);
				$sCountry = Core_Array::get($aAddress_Country, $key, NULL, 'string');
				$sPostcode = Core_Array::get($aAddress_Postcode, $key, NULL, 'string');
				$sCity = Core_Array::get($aAddress_City, $key, NULL, 'string');
				$sHouse = Core_Array::get($aAddress_House, $key, NULL, 'string');
				$sFlat = Core_Array::get($aAddress_Flat, $key, NULL, 'string');

				if (strlen($sAddress) || strlen($sCountry) || strlen($sPostcode) || strlen($sCity) || strlen($sHouse) || strlen($sFlat))
				{
					$oDirectory_Address = Core_Entity::factory('Directory_Address')
						->directory_address_type_id(Core_Array::get($aAddress_Types, $key, 0, 'int'))
						->public(Core_Array::get($aAddress_Public, $key, 0, 'int'))
						->country($sCountry)
						->postcode($sPostcode)
						->city($sCity)
						->value($sAddress)
						->house($sHouse)
						->flat($sFlat)
						->latitude(Core_Array::get($aLatitudes, $key, NULL, 'string'))
						->longitude(Core_Array::get($aLongitudes, $key, NULL, 'string'))
						->save();

					$object->add($oDirectory_Address);

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} select[name='{$prefix}address_type\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_type#{$oDirectory_Address->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='{$prefix}address_type\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_type#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address\\[\\]']\").eq({$i}).prop('name', '{$prefix}address#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address_country\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_country#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address_postcode\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_postcode#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address_city\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_city#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address_house\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_house#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address_flat\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_flat#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}latitude\\[\\]']\").eq({$i}).prop('name', '{$prefix}latitude#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}longitude\\[\\]']\").eq({$i}).prop('name', '{$prefix}longitude#{$oDirectory_Address->id}');
						$(\"#{$windowId} input[name='{$prefix}address_public\\[\\]']\").eq({$i}).prop('name', '{$prefix}address_public#{$oDirectory_Address->id}');
						")
						->execute();

					$Admin_Form_Controller->addMessage(ob_get_clean());
				}
				else
				{
					$i++;
				}
			}
		}

		return $this;
	}
}