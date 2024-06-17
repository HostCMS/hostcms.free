<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab_Email
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Controller_Tab_Email extends Directory_Controller_Tab
{
	protected $_directoryTypeName = 'Directory_Email_Type';

	protected $_titleHeaderColor = 'warning';

	protected $_faTitleIcon = 'fa fa-envelope-o';

	protected function _execute($oPersonalDataInnerWrapper)
	{
		// $aDirectory_Relations = $this->relation->findAll();

		$aMasDirectoryTypes = $this->_getDirectoryTypes();

		$oButtons = $this->_buttons();

		if (count($this->_aDirectory_Relations))
		{
			foreach ($this->_aDirectory_Relations as $oDirectory_Relation)
			{
				$oRowElements = $this->_emailTemplate($aMasDirectoryTypes, $oDirectory_Relation);

				$oPersonalDataInnerWrapper->add(
					$oRowElements->add($oButtons)
				);
			}
		}
		else
		{
			$oRowElements = $this->_emailTemplate($aMasDirectoryTypes);

			$oPersonalDataInnerWrapper->add(
				$oRowElements->add($oButtons)
			);
		}
	}

	protected function _emailTemplate($aMasDirectoryEmailTypes, $oUser_Directory_Email = NULL)
	{
		$sNameSuffix = $oUser_Directory_Email ? '#' . $oUser_Directory_Email->Directory_Email->id : '[]';

		$oRowElements = Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Select')
					->options($aMasDirectoryEmailTypes)
					->name($this->prefix . 'email_type' . $sNameSuffix)
					->value($oUser_Directory_Email ? $oUser_Directory_Email->Directory_Email->directory_email_type_id : '')
					->caption(Core::_('Directory_Email.type_email'))
					// ->divAttr(array('class' => 'form-group col-xs-6 col-lg-4'))
					->divAttr(array('class' => 'form-group col-lg-4 ' . ($this->showPublicityControlElement ? 'col-xs-6' : 'col-xs-5')))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'email' . $sNameSuffix)
					->value($oUser_Directory_Email ? $oUser_Directory_Email->Directory_Email->value : '')
					->caption(Core::_('Directory_Email.email'))
					->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-lg-4 col-xs-6' : 'col-lg-5 col-sm-5 col-xs-5')))
					->class('form-control bold')
			);

		if ($this->showPublicityControlElement)
		{
			$iEmailPublic = $oUser_Directory_Email ? $oUser_Directory_Email->Directory_Email->public : 0;

			$oRowElements->add(
				Admin_Form_Entity::factory('Checkbox')
				->divAttr(array('class' => 'col-xs-6 col-lg-2 no-padding-lg margin-top-23-lg margin-right-5-lg'))
					->name($this->prefix . 'email_public' . $sNameSuffix)
					->value(1)
					->checked($iEmailPublic ? $iEmailPublic : FALSE)
					->caption(Core::_('Directory_Email.email_public'))
			);

			// Для нового свойства добавляет скрытое поле, хранящее состояние чекбокса
			/*if (!$oUser_Directory_Email)
			{
				$oRowElements->add(
					Core_Html_Entity::factory('Input')
						->type('hidden')
						->value(0)
						->name($this->prefix . 'email_public_value' . $sNameSuffix)
				);
			}*/
		}

		return $oRowElements;
	}

	public function applyObjectProperty($Admin_Form_Controller, $object)
	{
		$windowId = $Admin_Form_Controller->getWindowId();

		$prefix = preg_replace('/[^A-Za-z0-9_-]/', '', $this->prefix);

		// Электронные адреса, установленные значения
		$aDirectory_Emails = $object->Directory_Emails->findAll(FALSE);
		foreach ($aDirectory_Emails as $oDirectory_Email)
		{
			$sEmail = Core_Array::getPost("{$prefix}email#{$oDirectory_Email->id}", NULL, 'trim');

			if (strlen($sEmail))
			{
				$oDirectory_Email
					->directory_email_type_id(Core_Array::getPost("{$prefix}email_type#{$oDirectory_Email->id}", 0, 'int'))
					->public(Core_Array::getPost("{$prefix}email_public#{$oDirectory_Email->id}", 0, 'int'))
					->value($sEmail)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core_Html_Entity::factory('Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='{$prefix}email_type#{$oDirectory_Email->id}']\").closest('.row').find('.btn-delete111').get(0));")
					->execute();

				$Admin_Form_Controller->addMessage(ob_get_clean());
				$oDirectory_Email->delete();
			}
		}

		// Электронные адреса, новые значения
		$aEmails = Core_Array::getPost($prefix . 'email', array());
		$aEmailTypes = Core_Array::getPost($prefix . 'email_type', array());
		$aEmailPublic = Core_Array::getPost($prefix . 'email_public', array());

		if (is_array($aEmails) && count($aEmails))
		{
			$i = 0;
			foreach ($aEmails as $key => $sEmail)
			{
				$sEmail = trim($sEmail);

				if (strlen($sEmail))
				{
					$oDirectory_Email = Core_Entity::factory('Directory_Email')
						->directory_email_type_id(Core_Array::get($aEmailTypes, $key, 0, 'int'))
						->public(Core_Array::get($aEmailPublic, $key, 0, 'int'))
						->value($sEmail)
						->save();

					$object->add($oDirectory_Email);

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} select[name='{$prefix}email_type\\[\\]']\").eq({$i}).prop('name', '{$prefix}email_type#{$oDirectory_Email->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='{$prefix}email\\[\\]']\").eq({$i}).prop('name', '{$prefix}email#{$oDirectory_Email->id}');
						$(\"#{$windowId} input[name='{$prefix}email_public\\[\\]']\").eq({$i}).prop('name', '{$prefix}email_public#{$oDirectory_Email->id}');
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