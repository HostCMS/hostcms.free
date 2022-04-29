<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab_Messenger
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Directory_Controller_Tab_Messenger extends Directory_Controller_Tab
{
	protected $_directoryTypeName = 'Directory_Messenger_Type';
	protected $_titleHeaderColor = 'purple';
	// protected $_titleHeaderColor = 'bordered-yellow';
	protected $_faTitleIcon = 'fa fa-comments-o';

	protected function _execute($oPersonalDataInnerWrapper)
	{
		$aDirectory_Relations = $this->relation->findAll();

		$aMasDirectoryTypes = $this->_getDirectoryTypes();

		$oButtons = $this->_buttons();

		if (count($this->_aDirectory_Relations))
		{
			foreach ($this->_aDirectory_Relations as $oDirectory_Relation)
			{
				$oRowElements = $this->_messengerTemplate($aMasDirectoryTypes, $oDirectory_Relation);

				$oPersonalDataInnerWrapper->add(
					$oRowElements->add($oButtons)
				);
			}
		}
		else
		{
			$oRowElements = $this->_messengerTemplate($aMasDirectoryTypes);

			$oPersonalDataInnerWrapper->add(
				$oRowElements->add($oButtons)
			);
		}
	}

	protected function _messengerTemplate($aMasDirectoryMessengers, $oUser_Directory_Messenger = NULL)
	{
		$sNameSuffix = $oUser_Directory_Messenger ? '#' . $oUser_Directory_Messenger->Directory_Messenger->id : '[]';

		$oRowElements = Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Select')
					->options($aMasDirectoryMessengers)
					->name($this->prefix . 'messenger' . $sNameSuffix)
					->value($oUser_Directory_Messenger ? $oUser_Directory_Messenger->Directory_Messenger->directory_messenger_type_id : '')
					->caption(Core::_('Directory_Messenger.messenger'))
					->divAttr(array('class' => 'form-group col-xs-4'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'messenger_username' . $sNameSuffix)
					->value($oUser_Directory_Messenger ? $oUser_Directory_Messenger->Directory_Messenger->value : '')
					->caption(Core::_('Directory_Messenger.messenger_username'))
					->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-sm-4 col-xs-3' : 'col-lg-5 col-sm-6 col-xs-5')))
			);

		if ($this->showPublicityControlElement)
		{
			$iMessengerPublic = $oUser_Directory_Messenger ? $oUser_Directory_Messenger->Directory_Messenger->public : 0;

			$oRowElements->add(
				Admin_Form_Entity::factory('Checkbox')
					->divAttr(array('class' => 'col-xs-3 col-sm-2 no-padding margin-top-23'))
					->name($this->prefix . 'messenger_public' . $sNameSuffix)
					->value(1)
					->checked($iMessengerPublic ? $iMessengerPublic : FALSE)
					->caption(Core::_('Directory_Messenger.messenger_public'))
			);

			// Для нового свойства добавляет скрытое поле, хранящее состояние чекбокса
			/*if (!$oUser_Directory_Messenger)
			{
				$oRowElements->add(
					Core_Html_Entity::factory('Input')
						->type('hidden')
						->value(0)
						->name($this->prefix . 'messenger_public_value' . $sNameSuffix)
				);
			}*/
		}

		return $oRowElements;
	}

	public function applyObjectProperty($Admin_Form_Controller, $object)
	{
		$windowId = $Admin_Form_Controller->getWindowId();

		$prefix = preg_replace('/[^A-Za-z0-9_-]/', '', $this->prefix);

		// Мессенджеры, установленные значения
		$aDirectory_Messenger = $object->Directory_Messengers->findAll(FALSE);
		foreach ($aDirectory_Messenger as $oDirectory_Messenger)
		{
			$sMessenger_Address = Core_Array::getPost("{$prefix}messenger_username#{$oDirectory_Messenger->id}", NULL, 'trim');

			if (!empty($sMessenger_Address))
			{
				$oDirectory_Messenger
					->directory_messenger_type_id(Core_Array::getPost("{$prefix}messenger#{$oDirectory_Messenger->id}", 0, 'int'))
					->public(Core_Array::getPost("{$prefix}messenger_public#{$oDirectory_Messenger->id}", 0, 'int'))
					->value($sMessenger_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core_Html_Entity::factory('Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='{$prefix}messenger#{$oDirectory_Messenger->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$Admin_Form_Controller->addMessage(ob_get_clean());

				$oDirectory_Messenger->delete();
			}
		}

		// Мессенджеры, новые значения
		$aMessengerAddresses = Core_Array::getPost("{$prefix}messenger_username", array());
		$aMessengers = Core_Array::getPost("{$prefix}messenger", array());
		$aMessengerPublic = Core_Array::getPost("{$prefix}messenger_public", array());

		if (is_array($aMessengerAddresses) && count($aMessengerAddresses))
		{
			$i = 0;
			foreach ($aMessengerAddresses as $key => $sMessenger_Address)
			{
				$sMessenger_Address = trim($sMessenger_Address);

				if (!empty($sMessenger_Address))
				{
					$oDirectory_Messenger = Core_Entity::factory('Directory_Messenger')
						->directory_messenger_type_id(Core_Array::get($aMessengers, $key, 0, 'int'))
						->public(Core_Array::get($aMessengerPublic, $key, 0, 'int'))
						->value($sMessenger_Address)
						->save();

					$object->add($oDirectory_Messenger);

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} select[name='{$prefix}messenger\\[\\]']\").eq({$i}).prop('name', '{$prefix}messenger#{$oDirectory_Messenger->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='{$prefix}messenger_username\\[\\]']\").eq({$i}).prop('name', '{$prefix}messenger_username#{$oDirectory_Messenger->id}');
						$(\"#{$windowId} input[name='{$prefix}messenger_public\\[\\]']\").eq({$i}).prop('name', '{$prefix}messenger_public#{$oDirectory_Messenger->id}');
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