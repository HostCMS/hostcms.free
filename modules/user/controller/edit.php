<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('sound')
			->addSkipColumn('last_activity')
			->addSkipColumn('image')
			->addSkipColumn('user_group_id')

			->addSkipColumn('~email')
			->addSkipColumn('~icq')
			->addSkipColumn('~site')
			->addSkipColumn('~position')
			;

		return parent::setObject($object);
	}

	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'));

		/*$oMainTab
			->delete($this->getField('email'))
			->delete($this->getField('position'))
			->delete($this->getField('icq'))
			->delete($this->getField('site'))
			;*/

		$oMainTab->move($this->getField('login'), $oMainRow1);

		$oMainTab->delete($this->getField('password'));

		$aPasswordFormat = array(
			'minlen' => array('value' => 9),
			'maxlen' => array('value' => 255)
		);

		$oPasswordFirst = Admin_Form_Entity::factory('Password')
			->caption(Core::_('User.password'))
			->id('password_first')
			->name('password_first')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6'))
			->generatePassword(TRUE);

		!$this->_object->id && $oPasswordFirst->format($aPasswordFormat);

		$oPasswordSecond = Admin_Form_Entity::factory('Password')
			->caption(Core::_('User.password_second'))
			->name('password_second')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6'));

		$aPasswordFormatSecond = array(
			'fieldEquality' => array(
				'value' => 'password_first',
				'message' => Core::_('user.ua_add_edit_user_form_password_second_message')
			)
		);

		!$this->_object->id && $aPasswordFormatSecond += $aPasswordFormat;

		$oPasswordSecond->format($aPasswordFormatSecond);

		$oMainRow2
			->add($oPasswordFirst)
			->add($oPasswordSecond);

		$oMainTab->delete($this->getField('settings'));

		$oMainTab
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('superuser')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('only_access_my_own')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('freelance')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4)
			->move($this->getField('dismissed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4)
			->move($this->getField('read_only')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4)
			->move($this->getField('root_dir'), $oMainRow5);

		$oPersonalDataTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('User.users_type_form_tab_2'))
			->name('tab_personal_data');

		$this->addTabAfter($oPersonalDataTab, $oMainTab);

		// Email'ы сотрудника
		$oPersonalDataEmailsRow = Directory_Controller_Tab::instance('email')
			->title(Core::_('Directory_Email.emails'))
			->relation($this->_object->User_Directory_Emails)
			->execute();

		// Телефоны
		$oPersonalDataPhonesRow = Directory_Controller_Tab::instance('phone')
			->title(Core::_('Directory_Phone.phones'))
			->relation($this->_object->User_Directory_Phones)
			->execute();

		// Социальные сети
		$oPersonalDataSocialsRow = Directory_Controller_Tab::instance('social')
			->title(Core::_('Directory_Social.socials'))
			->relation($this->_object->User_Directory_Socials)
			->execute();

		// Мессенджеры
		$oPersonalDataMessengersRow = Directory_Controller_Tab::instance('messenger')
			->title(Core::_('Directory_Messenger.messengers'))
			->relation($this->_object->User_Directory_Messengers)
			->execute();

		// Сайты
		$oPersonalDataWebsitesRow = Directory_Controller_Tab::instance('website')
			->title(Core::_('Directory_Website.sites'))
			->relation($this->_object->User_Directory_Websites)
			->execute();

		$oPersonalDataTab
			->add($oPersonalDataRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataEmailsRow)
			->add($oPersonalDataPhonesRow)
			->add($oPersonalDataSocialsRow)
			->add($oPersonalDataMessengersRow)
			->add($oPersonalDataWebsitesRow);

		$oMainTab
			->move($this->getField('surname')->divAttr(array('class' => 'form-group col-xs-12 col-md-4')), $oPersonalDataRow1)
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12 col-md-4')), $oPersonalDataRow1)
			->move($this->getField('patronymic')->divAttr(array('class' => 'form-group col-xs-12 col-md-4')), $oPersonalDataRow1)
			->move($this->getField('address'), $oPersonalDataRow2);

		$oMainTab
			->move($this->getField('birthday')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-2')), $oPersonalDataRow3)
			->move($this->getField('description'), $oPersonalDataRow5);

		$oMainTab->delete($this->getField('sex'));

		// Добавляем пол сотрудника
		$oUserSex = Admin_Form_Entity::factory('Radiogroup')
			->name('sex')
			->id('sex' . time())
			->caption(Core::_('User.sex'))
			->value($this->_object->sex)
			->divAttr(array('class' => 'form-group col-lg-4 col-md-3 col-sm-6 col-xs-12'))
			->radio(array(
				0 => Core::_('User.male'),
				1 => Core::_('User.female')
			))
			->ico(
				array(
					0 => 'fa-mars',
					1 => 'fa-venus',
			))
			->colors(
				array(
					0 => 'btn-sky',
					1 => 'btn-pink'
				)
			);

		$oPersonalDataRow3->add($oUserSex);

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$aConfig = Core_Config::instance()->get('user_config', array()) + array (
			'max_height' => 130,
			'max_width' => 130
		);

		// Изображение
		$oImageField = Admin_Form_Entity::factory('File');
		$oImageField
			->type('file')
			->caption(Core::_('User.image'))
			->name('image')
			->id('image')
			->largeImage(
				array(
					'max_width' => $aConfig['max_width'],
					'max_height' => $aConfig['max_height'],
					'path' => $this->_object->image != ''
						? $this->_object->getImageFileHref()
						: '',
					'show_params' => TRUE,
					'preserve_aspect_ratio_checkbox_checked' => FALSE,
					// deleteWatermarkFile
					'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteImageFile', windowId: '{$windowId}'}); return false",
					'place_watermark_checkbox' => FALSE,
					'place_watermark_x_show' => FALSE,
					'place_watermark_y_show' => FALSE
				)
			)
			->smallImage(
				array(
					'show' => FALSE
				)
			)
			->divAttr(array('class' => 'form-group col-lg-6 col-md-6 col-sm-12 col-xs-12'));

		$oPersonalDataRow3->add($oImageField);

		$title = $this->_object->id
			? Core::_('User.ua_edit_user_form_title')
			: Core::_('User.ua_add_user_form_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Fill user groups list
	 * @return array
	 */
	protected function _fillUserGroup()
	{
		$oSite = Core_Entity::factory('site', CURRENT_SITE);

		$aUserGroups = $oSite->User_Groups->findAll();

		$aReturnUserGroups = array();
		foreach ($aUserGroups as $oUserGroup)
		{
			$aReturnUserGroups[$oUserGroup->id] = $oUserGroup->name;
		}

		return $aReturnUserGroups;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event User_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this
			->addSkipColumn('email')
			->addSkipColumn('image')
			->addSkipColumn('password')
			->addSkipColumn('settings')
			->addSkipColumn('last_activity');

		$password = Core_Array::getPost('password_first');

		if ($password != '' || is_null($this->_object->id))
		{
			$this->_object->password = Core_Hash::instance()->hash($password);
		}

		parent::_applyObjectProperty();

		if (
			// Поле файла существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0)
		{
			if (Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
			{
				$fileExtension = Core_File::getExtension($aFileData['name']);
				$sImageName = 'avatar.' . $fileExtension;

				$param = array();
				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];

				// Путь к создаваемому файлу большого изображения;
				$param['large_image_target'] = $this->_object->getPath() . $sImageName;

				// Использовать большое изображение для создания малого
				$param['create_small_image_from_large'] = FALSE;

				// Значение максимальной ширины большого изображения
				$param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);

				// Значение максимальной высоты большого изображения
				$param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

				// Сохранять пропорции изображения для большого изображения
				$param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

				$this->_object->createDir();

				$result = Core_File::adminUpload($param);

				if ($result['large_image'])
				{
					$this->_object->image = $sImageName;
					$this->_object->save();
				}
			}
			else
			{
				$this->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Электронные адреса, установленные значения
		$aUser_Directory_Emails = $this->_object->User_Directory_Emails->findAll();
		foreach ($aUser_Directory_Emails as $oUser_Directory_Email)
		{
			$sEmail = trim(Core_Array::getPost("email#{$oUser_Directory_Email->id}"));

			//if (!is_null(Core_Array::getPost("email_type#{$oUser_Directory_Email->id}")))
			if (!empty($sEmail))
			{
				$oDirectory_Email = $oUser_Directory_Email->Directory_Email;
				$oDirectory_Email
					->directory_email_type_id(intval(Core_Array::getPost("email_type#{$oUser_Directory_Email->id}", 0)))
					->value($sEmail)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core::factory('Core_Html_Entity_Script')
					->type("text/javascript")
					->value("$.deleteFormRow($(\"#{$windowId} select[name='email_type#{$oUser_Directory_Email->id}']\").closest('.row').find('.btn-delete111').get(0));")
					->execute();

				$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				$oUser_Directory_Email->Directory_Email->delete();
			}
		}

		// Электронные адреса, новые значения
		$aEmails = Core_Array::getPost('email');
		$aEmail_Types = Core_Array::getPost('email_type');

		if (count($aEmails))
		{
			$i = 0;
			foreach ($aEmails as $key => $sEmail)
			{
				$sEmail = trim($sEmail);

				if (!empty($sEmail))
				{
					$oDirectory_Email = Core_Entity::factory('Directory_Email')
						->directory_email_type_id(intval(Core_Array::get($aEmail_Types, $key)))
						->value($sEmail)
						->save();

					/*$oUser_Directory_Email = Core_Entity::factory('Directory_Email_User')
						->directory_email_id($oDirectory_Email->id);*/

					$this->_object->add($oDirectory_Email);

					//$this->_object->add($oDirectory_Email);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
						->type("text/javascript")
						->value("$(\"#{$windowId} select[name='email_type\\[\\]']\").eq({$i}).prop('name', 'email_type#{$oDirectory_Email->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='email\\[\\]']\").eq({$i}).prop('name', 'email#{$oDirectory_Email->id}');")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
				else
				{
					$i++;
				}
			}
		}

		// Телефоны, установленные значения
		$aUser_Directory_Phones = $this->_object->User_Directory_Phones->findAll();
		foreach ($aUser_Directory_Phones as $oUser_Directory_Phone)
		{
			$sPhone = trim(Core_Array::getPost("phone#{$oUser_Directory_Phone->id}"));

			if (!empty($sPhone))
			{
				$oDirectory_Phone = $oUser_Directory_Phone->Directory_Phone;
				$oDirectory_Phone
					->directory_phone_type_id(intval(Core_Array::getPost("phone_type#{$oUser_Directory_Phone->id}", 0)))
					->value($sPhone)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core::factory('Core_Html_Entity_Script')
					->type("text/javascript")
					->value("$.deleteFormRow($(\"#{$windowId} select[name='phone_type#{$oUser_Directory_Phone->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());

				$oUser_Directory_Phone->Directory_Phone->delete();
			}
		}

		// Телефоны, новые значения
		$aPhones = Core_Array::getPost('phone');
		$aPhone_Types = Core_Array::getPost('phone_type');

		if (count($aPhones))
		{
			$i = 0;
			foreach ($aPhones as $key => $sPhone)
			{
				$sPhone = trim($sPhone);

				if (!empty($sPhone))
				{
					$oDirectory_Phone = Core_Entity::factory('Directory_Phone')
						->directory_phone_type_id(intval(Core_Array::get($aPhone_Types, $key)))
						->value($sPhone)
						->save();

					$this->_object->add($oDirectory_Phone);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
						->type("text/javascript")
						->value("$(\"#{$windowId} select[name='phone_type\\[\\]']\").eq({$i}).prop('name', 'phone_type#{$oDirectory_Phone->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='phone\\[\\]']\").eq({$i}).prop('name', 'phone#{$oDirectory_Phone->id}');
						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
				else
				{
					$i++;
				}
			}
		}

		// Социальные сети, установленные значения
		$aUser_Directory_Socials = $this->_object->User_Directory_Socials->findAll();
		foreach ($aUser_Directory_Socials as $oUser_Directory_Social)
		{
			$sSocial_Address = trim(Core_Array::getPost("social_address#{$oUser_Directory_Social->id}"));

			//if (!is_null(Core_Array::getPost("social_address#{$oUser_Directory_Social->id}")))
			if (!empty($sSocial_Address))
			{
				$aUrl = parse_url($sSocial_Address);

				// Если не был указан протокол, или
				// указанный протокол некорректен для url
				!array_key_exists('scheme', $aUrl)
					&& $sSocial_Address = /*'http://' .*/ $sSocial_Address;

				$oDirectory_Social = $oUser_Directory_Social->Directory_Social;
				$oDirectory_Social
					->directory_social_type_id(intval(Core_Array::getPost("social#{$oUser_Directory_Social->id}", 0)))
					//->directory_social_id(intval(Core_Array::getPost("social#{$oUser_Directory_Social->id}", 0)))
					->value($sSocial_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core::factory('Core_Html_Entity_Script')
					->type("text/javascript")
					->value("$.deleteFormRow($(\"#{$windowId} select[name='social#{$oUser_Directory_Social->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());

				$oUser_Directory_Social->delete();
			}
		}

		// Социальные сети, новые значения
		$aSocial_Addresses = Core_Array::getPost('social_address');
		$aSocials = Core_Array::getPost('social');

		if (count($aSocial_Addresses))
		{
			$i = 0;
			foreach ($aSocial_Addresses as $key => $sSocial_Address)
			{
				$sSocial_Address = trim($sSocial_Address);

				if (!empty($sSocial_Address))
				{
					$aUrl = parse_url($sSocial_Address);

					// Если не был указан протокол, или
					// указанный протокол некорректен для url
					!array_key_exists('scheme', $aUrl)
						&& $sSocial_Address = /*'http://' .*/ $sSocial_Address;

					$oDirectory_Social = Core_Entity::factory('Directory_Social')
						->directory_social_type_id(intval(Core_Array::get($aSocials, $key)))
						->value($sSocial_Address)
						->save();

					$this->_object->add($oDirectory_Social);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
						->type("text/javascript")
						->value("$(\"#{$windowId} select[name='social\\[\\]']\").eq({$i}).prop('name', 'social#{$oDirectory_Social->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='social_address\\[\\]']\").eq({$i}).prop('name', 'social_address#{$oDirectory_Social->id}');
						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
				else
				{
					$i++;
				}
			}
		}

		///////////////
		// Мессенджеры, установленные значения
		$aUser_Directory_Messengers = $this->_object->User_Directory_Messengers->findAll();
		foreach ($aUser_Directory_Messengers as $oUser_Directory_Messenger)
		{
			$sMessenger_Address = trim(Core_Array::getPost("messenger_username#{$oUser_Directory_Messenger->id}"));
			//if (!is_null(Core_Array::getPost("messenger_username#{$oUser_Directory_Messenger->id}")))
			if (!empty($sMessenger_Address))
			{
				$oDirectory_Messenger = $oUser_Directory_Messenger->Directory_Messenger;

				var_dump(intval(Core_Array::getPost("messenger#{$oDirectory_Messenger->id}")));

				$oDirectory_Messenger
					->directory_messenger_type_id(intval(Core_Array::getPost("messenger#{$oUser_Directory_Messenger->id}", 0)))
					->value($sMessenger_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core::factory('Core_Html_Entity_Script')
					->type("text/javascript")
					->value("$.deleteFormRow($(\"#{$windowId} select[name='messenger#{$oUser_Directory_Messenger->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());

				$oUser_Directory_Messenger->delete();
			}
		}

		// Мессенджеры, новые значения
		$aMessenger_Addresses = Core_Array::getPost('messenger_username');
		$aMessengers = Core_Array::getPost('messenger');

		if (count($aMessenger_Addresses))
		{
			$i = 0;
			foreach ($aMessenger_Addresses as $key => $sMessenger_Address)
			{
				$sMessenger_Address = trim($sMessenger_Address);

				if (!empty($sMessenger_Address))
				{
					$oDirectory_Messenger = Core_Entity::factory('Directory_Messenger')
						->directory_messenger_type_id(intval(Core_Array::get($aMessengers, $key)))
						->value($sMessenger_Address)
						->save();

					$this->_object->add($oDirectory_Messenger);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
						->type("text/javascript")
						->value("$(\"#{$windowId} select[name='messenger\\[\\]']\").eq({$i}).prop('name', 'messenger#{$oDirectory_Messenger->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='messenger_username\\[\\]']\").eq({$i}).prop('name', 'messenger_username#{$oDirectory_Messenger->id}');
						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
				else
				{
					$i++;
				}
			}
		}

		// Cайты, установленные значения
		$aUser_Directory_Websites = $this->_object->User_Directory_Websites->findAll();
		foreach ($aUser_Directory_Websites as $oUser_Directory_Website)
		{
			$oDirectory_Website = $oUser_Directory_Website->Directory_Website;

			$sWebsite_Address = trim(Core_Array::getPost("website_address#{$oUser_Directory_Website->id}"));

			//if (!is_null(Core_Array::getPost("website_address#{$oUser_Directory_Website->id}")))
			if (!empty($sWebsite_Address))
			{
				//$oDirectory_Website->description = Core_Array::getPost("website_name#{$oUser_Directory_Website->id}");

				$aUrl = parse_url($sWebsite_Address);

				// Если не был указан протокол, или
				// указанный протокол некорректен для url
				!array_key_exists('scheme', $aUrl)
					&& $sWebsite_Address = 'http://' . $sWebsite_Address;

				$oDirectory_Website
					->description(strval(Core_Array::getPost("website_description#{$oUser_Directory_Website->id}")))
					->value($sWebsite_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				ob_start();
				Core::factory('Core_Html_Entity_Script')
					->type("text/javascript")
					->value("$.deleteFormRow($(\"#{$windowId} input[name='website_address#{$oUser_Directory_Website->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();

				$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				$oDirectory_Website->delete();
			}
		}

		// Сайты, новые значения
		$aWebsite_Addresses = Core_Array::getPost('website_address');
		$aWebsite_Names = Core_Array::getPost('website_description');

		if (count($aWebsite_Addresses))
		{
			$i = 0;

			foreach ($aWebsite_Addresses as $key => $sWebsite_Address)
			{
				$sWebsite_Address = trim($sWebsite_Address);

				if (!empty($sWebsite_Address))
				{
					$aUrl = parse_url($sWebsite_Address);

					// Если не был указан протокол, или
					// указанный протокол некорректен для url
					!array_key_exists('scheme', $aUrl)
						&& $sWebsite_Address = 'http://' . $sWebsite_Address;

					$oDirectory_Website = Core_Entity::factory('Directory_Website')
						->description(Core_Array::get($aWebsite_Names, $key))
						->value($sWebsite_Address);

					$this->_object->add($oDirectory_Website);

					$oUser_Directory_Website = $oDirectory_Website->User_Directory_Websites->getByUser_Id($this->_object->id);

					//$this->_object->add($oDirectory_Email);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
						->type("text/javascript")
						->value("$(\"#{$windowId} input[name='website_address\\[\\]']\").eq({$i}).prop('name', 'website_address#{$oUser_Directory_Website->id}').closest('.row').find('.btn-delete').removeClass('hide');
						$(\"#{$windowId} input[name='website_description\\[\\]']\").eq({$i}).prop('name', 'website_description#{$oUser_Directory_Website->id}');

						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
				else
				{
					$i++;
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill sites list
	 * @return array
	 */
	public function fillSites()
	{
		$aSites = Core_Entity::factory('User')->getCurrent()->getSites();

		$aReturn = array();
		foreach ($aSites as $oSite)
		{
			$aReturn[$oSite->id] = $oSite->name;
		}

		return $aReturn;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$login = Core_Array::getRequest('login');
			$id = Core_Array::getRequest('id');
			$oSameUser = Core_Entity::factory('User')->getByLogin($login);

			if (!is_null($oSameUser) && $oSameUser->id != $id)
			{
				$this->addMessage(
					Core_Message::get(Core::_('User.user_has_already_registered'), 'error')
				);
				return TRUE;
			}
		}

		return parent::execute($operation);
	}
}