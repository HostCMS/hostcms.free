<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Controller_Tab_Website
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Controller_Tab_Website extends Directory_Controller_Tab
{
	protected $_directoryTypeName = 'Directory_Website_Type';
	protected $_titleHeaderColor = 'gray';
	// protected $_titleHeaderColor = 'bordered-gray';
	protected $_faTitleIcon = 'fa fa-globe';

	protected function _execute($oPersonalDataInnerWrapper)
	{
		// $aDirectory_Relations = $this->relation->findAll();

		$oButtons = $this->_buttons();

		if (count($this->_aDirectory_Relations))
		{
			foreach ($this->_aDirectory_Relations as $oDirectory_Relation)
			{
				$oRowElements = $this->_websiteTemplate($oDirectory_Relation);

				$oPersonalDataInnerWrapper->add(
					$oRowElements->add($oButtons)
				);
			}
		}
		else
		{
			$oRowElements = $this->_websiteTemplate();

			$oPersonalDataInnerWrapper->add(
				$oRowElements->add($oButtons)
			);
		}
	}

	protected function _websiteTemplate($oUser_Directory_Website = NULL)
	{
		$sNameSuffix = $oUser_Directory_Website ? '#' . $oUser_Directory_Website->Directory_Website->id : '[]';

		$oRowElements = Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'website_address' . $sNameSuffix)
					->value($oUser_Directory_Website ? $oUser_Directory_Website->Directory_Website->value : '')
					->caption(Core::_('Directory_Website.site'))
					->divAttr(array('class' => 'form-group ' . ($this->showPublicityControlElement ? 'col-xs-5 col-lg-4' : 'col-lg-4 col-xs-5')))
					->placeholder('https://')
					->class('form-control bold')
					->add(
						Admin_Form_Entity::factory('A')
							->id('pathLink')
							->class('input-group-addon blue')
							->value('<i class="fa fa-external-link"></i>')
							->target('_blank')
							->href($oUser_Directory_Website ? $oUser_Directory_Website->Directory_Website->value : '/')
					)
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name($this->prefix . 'website_description' . $sNameSuffix)
					->value($oUser_Directory_Website ? $oUser_Directory_Website->Directory_Website->description : '')
					->caption(Core::_('Directory_Website.name'))
					->divAttr(array('class' => 'form-group no-padding-left ' . ($this->showPublicityControlElement ? 'col-lg-4 col-xs-6' : 'col-lg-5 col-sm-5 col-xs-5')))
			);

		if ($this->showPublicityControlElement)
		{
			$iWebsitePublic = $oUser_Directory_Website ? $oUser_Directory_Website->Directory_Website->public : 0;

			$oRowElements->add(
				Admin_Form_Entity::factory('Checkbox')
					->divAttr(array('class' => 'col-xs-5 col-lg-2 no-padding-lg margin-top-23-lg margin-right-5-lg'))
					->name($this->prefix . 'website_public' . $sNameSuffix)
					->value(1)
					->checked($iWebsitePublic ? $iWebsitePublic : FALSE)
					->caption(Core::_('Directory_Website.website_public'))
			);

			// Для нового свойства добавляет скрытое поле, хранящее состояние чекбокса
			/*if (!$oUser_Directory_Website)
			{
				$oRowElements->add(
					Core_Html_Entity::factory('Input')
						->type('hidden')
						->value(0)
						->name($this->prefix . 'website_public_value' . $sNameSuffix)
				);
			}*/
		}

		return $oRowElements;
	}

	public function applyObjectProperty($Admin_Form_Controller, $object)
	{
		$windowId = $Admin_Form_Controller->getWindowId();

		$prefix = preg_replace('/[^A-Za-z0-9_-]/', '', $this->prefix);

		// Cайты, установленные значения
		$aDirectory_Websites = $object->Directory_Websites->findAll(FALSE);
		foreach ($aDirectory_Websites as $oDirectory_Website)
		{
			$sWebsite_Address = Core_Array::getPost("{$prefix}website_address#{$oDirectory_Website->id}", NULL, 'trim');

			if (!empty($sWebsite_Address))
			{
				$aUrl = @parse_url($sWebsite_Address);

				// Если не был указан протокол, или
				// указанный протокол некорректен для url
				!array_key_exists('scheme', $aUrl)
					&& $sWebsite_Address = 'http://' . $sWebsite_Address;

				$oDirectory_Website
					->description(Core_Array::getPost("{$prefix}website_description#{$oDirectory_Website->id}", NULL, 'string'))
					->public(Core_Array::getPost("{$prefix}website_public#{$oDirectory_Website->id}", 0, 'int'))
					->value($sWebsite_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core_Html_Entity::factory('Script')
					->value("$.deleteFormRow($(\"#{$windowId} input[name='{$prefix}website_address#{$oDirectory_Website->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();

				$Admin_Form_Controller->addMessage(ob_get_clean());
				$oDirectory_Website->delete();
			}
		}

		// Сайты, новые значения
		$aWebsiteAddresses = Core_Array::getPost("{$prefix}website_address", array());
		$aWebsiteNames = Core_Array::getPost("{$prefix}website_description", array());
		$aWebsitePublic = Core_Array::getPost("{$prefix}website_public", array());

		if (is_array($aWebsiteAddresses) && count($aWebsiteAddresses))
		{
			$i = 0;
			foreach ($aWebsiteAddresses as $key => $sWebsite_Address)
			{
				$sWebsite_Address = trim($sWebsite_Address);

				if (!empty($sWebsite_Address))
				{
					$aUrl = @parse_url($sWebsite_Address);

					// Если не был указан протокол, или
					// указанный протокол некорректен для url
					!array_key_exists('scheme', $aUrl)
						&& $sWebsite_Address = 'http://' . $sWebsite_Address;

					$oDirectory_Website = Core_Entity::factory('Directory_Website')
						->public(Core_Array::get($aWebsitePublic, $key, 0, 'int'))
						->description(Core_Array::get($aWebsiteNames, $key, NULL, 'string'))
						->value($sWebsite_Address);

					$object->add($oDirectory_Website);

					ob_start();
					Core_Html_Entity::factory('Script')
						->value("$(\"#{$windowId} input[name='{$prefix}website_address\\[\\]']\").eq({$i}).prop('name', '{$prefix}website_address#{$oDirectory_Website->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='{$prefix}website_description\\[\\]']\").eq({$i}).prop('name', '{$prefix}website_description#{$oDirectory_Website->id}');
						$(\"#{$windowId} input[name='{$prefix}website_public\\[\\]']\").eq({$i}).prop('name', '{$prefix}website_public#{$oDirectory_Website->id}');
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

		$aDirectory_Websites = $object->Directory_Websites->findAll(FALSE);
		foreach ($aDirectory_Websites as $oDirectory_Website)
		{
			$Admin_Form_Controller->addMessage(
				Core_Html_Entity::factory('Script')
					->value("$(\"#{$windowId} input[name='" . $prefix . 'website_address#' . $oDirectory_Website->id . "']\").parent().find('a#pathLink').attr('href', '" . Core_Str::escapeJavascriptVariable($oDirectory_Website->value) . "').attr('target', '_blank')")
				->execute()
			);
		}
	}
}