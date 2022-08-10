<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab_Social
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Directory_Controller_Tab_Social extends Directory_Controller_Tab
{
	protected $_directoryTypeName = 'Directory_Social_Type';
	protected $_titleHeaderColor = 'blue';
	// protected $_titleHeaderColor = 'bordered-blue';
	protected $_faTitleIcon = 'fa fa-share-alt';

	protected function _execute($oPersonalDataInnerWrapper)
	{
		// $aDirectory_Relations = $this->relation->findAll();

		$aMasDirectoryTypes = $this->_getDirectoryTypes();

		$oButtons = $this->_buttons();

		if (count($this->_aDirectory_Relations))
		{
			foreach ($this->_aDirectory_Relations as $oDirectory_Relation)
			{
				$oRowElements = $this->_socialTemplate($aMasDirectoryTypes, $oDirectory_Relation);

				$oPersonalDataInnerWrapper->add(
					$oRowElements->add($oButtons)
				);
			}
		}
		else
		{
			$oRowElements = $this->_socialTemplate($aMasDirectoryTypes);

			$oPersonalDataInnerWrapper->add(
				$oRowElements->add($oButtons)
			);
		}
	}

	protected function _socialTemplate($aMasDirectorySocials, $oUser_Directory_Social = NULL)
	{
		$sNameSuffix = $oUser_Directory_Social ? '#' . $oUser_Directory_Social->Directory_Social->id : '[]';

		$oRowElements = Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Select')
					->options($aMasDirectorySocials)
					->name($this->prefix . 'social' . $sNameSuffix)
					->value($oUser_Directory_Social ? $oUser_Directory_Social->Directory_Social->directory_social_type_id : '')
					->caption(Core::_('Directory_Social.social'))
					->divAttr(array('class' => 'form-group col-xs-6 col-lg-4'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'social_address' . ($oUser_Directory_Social ? '#' . $oUser_Directory_Social->Directory_Social->id : '[]'))
					->value($oUser_Directory_Social ? $oUser_Directory_Social->Directory_Social->value : '')
					->caption(Core::_('Directory_Social.social_address'))
					->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-lg-4 col-xs-6' : 'col-lg-5 col-sm-6 col-xs-5')))
			);

		if ($this->showPublicityControlElement)
		{
			$iSocialPublic = $oUser_Directory_Social ? $oUser_Directory_Social->Directory_Social->public : 0;

			$oRowElements->add(
				Admin_Form_Entity::factory('Checkbox')
					->divAttr(array('class' => 'col-xs-6 col-lg-2 no-padding-lg margin-top-23-lg margin-right-5-lg'))
					->name($this->prefix . 'social_public' . $sNameSuffix)
					->value(1)
					->checked($iSocialPublic ? $iSocialPublic : FALSE)
					->caption(Core::_('Directory_Social.social_public'))
			);

			// Для нового свойства добавляет скрытое поле, хранящее состояние чекбокса
			/*if (!$oUser_Directory_Social)
			{
				$oRowElements->add(
					Core_Html_Entity::factory('Input')
						->type('hidden')
						->value(0)
						->name($this->prefix . 'social_public_value' . $sNameSuffix)
				);
			}*/
		}

		return $oRowElements;
	}

	public function applyObjectProperty($Admin_Form_Controller, $object)
	{
		$windowId = $Admin_Form_Controller->getWindowId();

		$prefix = preg_replace('/[^A-Za-z0-9_-]/', '', $this->prefix);

		// Социальные сети, установленные значения
		$aDirectory_Socials = $object->Directory_Socials->findAll(FALSE);
		foreach ($aDirectory_Socials as $oDirectory_Social)
		{
			$sSocial_Address = Core_Array::getPost("{$prefix}social_address#{$oDirectory_Social->id}", NULL, 'trim');

			if (!empty($sSocial_Address))
			{
				$aUrl = @parse_url($sSocial_Address);

				// Если не был указан протокол, или
				// указанный протокол некорректен для url
				!array_key_exists('scheme', $aUrl)
					&& $sSocial_Address = 'https://' . $sSocial_Address;

				$oDirectory_Social
					->directory_social_type_id(Core_Array::getPost("{$prefix}social#{$oDirectory_Social->id}", 0, 'int'))
					->public(Core_Array::getPost("{$prefix}social_public#{$oDirectory_Social->id}", 0, 'int'))
					->value($sSocial_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core_Html_Entity::factory('Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='{$prefix}social#{$oDirectory_Social->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$Admin_Form_Controller->addMessage(ob_get_clean());
				$oDirectory_Social->delete();
			}
		}

		// Социальные сети, новые значения
		$aSocialAddresses = Core_Array::getPost("{$prefix}social_address", array());
		$aSocials = Core_Array::getPost("{$prefix}social", array());
		$aSocialPublic = Core_Array::getPost("{$prefix}social_public", array());

		if (is_array($aSocialAddresses) && count($aSocialAddresses))
		{
			$i = 0;
			foreach ($aSocialAddresses as $key => $sSocial_Address)
			{
				$sSocial_Address = trim($sSocial_Address);

				if (!empty($sSocial_Address))
				{
					$aUrl = @parse_url($sSocial_Address);

					// Если не был указан протокол, или
					// указанный протокол некорректен для url
					!array_key_exists('scheme', $aUrl)
						&& $sSocial_Address = 'https://' . $sSocial_Address;

					$oDirectory_Social = Core_Entity::factory('Directory_Social')
						->directory_social_type_id(Core_Array::get($aSocials, $key, 0, 'int'))
						->public(Core_Array::get($aSocialPublic, $key, 0, 'int'))
						->value($sSocial_Address)
						->save();

					$object->add($oDirectory_Social);

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} select[name='{$prefix}social\\[\\]']\").eq({$i}).prop('name', '{$prefix}social#{$oDirectory_Social->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='{$prefix}social_address\\[\\]']\").eq({$i}).prop('name', '{$prefix}social_address#{$oDirectory_Social->id}');
						$(\"#{$windowId} input[name='{$prefix}social_public\\[\\]']\").eq({$i}).prop('name', '{$prefix}social_public#{$oDirectory_Social->id}');
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
	}
}