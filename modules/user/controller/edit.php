<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			->addSkipColumn('guid')
			->addSkipColumn('sound')
			->addSkipColumn('last_activity')
			->addSkipColumn('image')
			->addSkipColumn('user_group_id');

		return parent::setObject($object);
	}

	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');

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

		$oWorktimeTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('User.users_type_form_tab_3'))
			->name('tab_worktime');

		$this->addTabAfter($oWorktimeTab, $oPersonalDataTab);

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
			->divAttr(array('class' => 'input-group col-lg-6 col-md-6 col-sm-12 col-xs-12'));

		$oPersonalDataRow3->add($oImageField);

		// Расписание
		for ($day = 1; $day <= 7; $day++)
		{
			$oWorktimeTab
				->add($oWorktimeRow = Admin_Form_Entity::factory('Div')->class('row'));

			// День недели
			$oDayDiv = Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-md-2 margin-top-10')
				->value(Core::_('Admin_Form.day' . $day));

			$oUser_Worktime = $this->_object->User_Worktimes->getByDay($day);

			$from = !is_null($oUser_Worktime)
				? $oUser_Worktime->from
				: '00 : 00';

			// Начало рабочего дня
			$oDayStart = Admin_Form_Entity::factory('Input')
				->id("timepicker_{$day}_start")
				->name("day_{$day}_from")
				->value($from)
				->divAttr(array('class' => 'form-group col-xs-6 col-md-2'));

			$to = !is_null($oUser_Worktime)
				? $oUser_Worktime->to
				: '00 : 00';

			// Конец рабочего дня
			$oDayEnd = Admin_Form_Entity::factory('Input')
				->id("timepicker_{$day}_end")
				->name("day_{$day}_to")
				->value($to)
				->divAttr(array('class' => 'form-group col-xs-6 col-md-2'));

			// Перерыв
			$oDayBreakDiv = Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-md-2 margin-top-10')
				->value(Core::_('User.break'));

			// Начало перерыва
			$break_from = !is_null($oUser_Worktime)
				? $oUser_Worktime->break_from
				: '00 : 00';

			$oDayBreakStart = Admin_Form_Entity::factory('Input')
				->id("timepicker_break_{$day}_start")
				->name("day_break_{$day}_from")
				->value($break_from)
				->divAttr(array('class' => 'form-group col-xs-6 col-md-2'));

			// Конец перерыва
			$break_to = !is_null($oUser_Worktime)
				? $oUser_Worktime->break_to
				: '00 : 00';

			$oDayBreakEnd = Admin_Form_Entity::factory('Input')
				->id("timepicker_break_{$day}_end")
				->name("day_break_{$day}_to")
				->value($break_to)
				->divAttr(array('class' => 'form-group col-xs-6 col-md-2'));

			$html = "<script>
				$(function(){
					$('#timepicker_{$day}_start').wickedpicker({
						now: '{$from}',
						twentyFour: true,  //Display 24 hour format, defaults to false
						upArrow: 'wickedpicker__controls__control-up',  //The up arrow class selector to use, for custom CSS
						downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
						close: 'wickedpicker__close', //The close class selector to use, for custom CSS
						hoverState: 'hover-state', //The hover state class to use, for custom CSS
						title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
						showSeconds: false, //Whether or not to show seconds,
						timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
						secondsInterval: 1, //Change interval for seconds, defaults to 1,
						minutesInterval: 1, //Change interval for minutes, defaults to 1
						clearable: false //Make the picker's input clearable (has clickable 'x')
					});

					$('#timepicker_{$day}_end').wickedpicker({
						now: '{$to}',
						twentyFour: true,  //Display 24 hour format, defaults to false
						upArrow: 'wickedpicker__controls__control-up',  //The up arrow class selector to use, for custom CSS
						downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
						close: 'wickedpicker__close', //The close class selector to use, for custom CSS
						hoverState: 'hover-state', //The hover state class to use, for custom CSS
						title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
						showSeconds: false, //Whether or not to show seconds,
						timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
						secondsInterval: 1, //Change interval for seconds, defaults to 1,
						minutesInterval: 1, //Change interval for minutes, defaults to 1
						clearable: false //Make the picker's input clearable (has clickable 'x')
					});

					$('#timepicker_break_{$day}_start').wickedpicker({
						now: '{$break_from}',
						twentyFour: true,  //Display 24 hour format, defaults to false
						upArrow: 'wickedpicker__controls__control-up',  //The up arrow class selector to use, for custom CSS
						downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
						close: 'wickedpicker__close', //The close class selector to use, for custom CSS
						hoverState: 'hover-state', //The hover state class to use, for custom CSS
						title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
						showSeconds: false, //Whether or not to show seconds,
						timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
						secondsInterval: 1, //Change interval for seconds, defaults to 1,
						minutesInterval: 1, //Change interval for minutes, defaults to 1
						clearable: false //Make the picker's input clearable (has clickable 'x')
					});

					$('#timepicker_break_{$day}_end').wickedpicker({
						now: '{$break_to}',
						twentyFour: true,  //Display 24 hour format, defaults to false
						upArrow: 'wickedpicker__controls__control-up',  //The up arrow class selector to use, for custom CSS
						downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
						close: 'wickedpicker__close', //The close class selector to use, for custom CSS
						hoverState: 'hover-state', //The hover state class to use, for custom CSS
						title: '" . Core::_('User_Worktime.time') . "', //The Wickedpicker's title,
						showSeconds: false, //Whether or not to show seconds,
						timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
						secondsInterval: 1, //Change interval for seconds, defaults to 1,
						minutesInterval: 1, //Change interval for minutes, defaults to 1
						clearable: false //Make the picker's input clearable (has clickable 'x')
					});
				})
			</script>";

			$oWorktimeRow
				->add($oDayDiv)
				->add($oDayStart)
				->add($oDayEnd)
				->add($oDayBreakDiv)
				->add($oDayBreakStart)
				->add($oDayBreakEnd)
				->add(Admin_Form_Entity::factory('Code')->html($html));
		}

		$name = strlen($this->_object->getFullName())
			? $this->_object->getFullName()
			: $this->_object->login;

		$title = $this->_object->id
			? Core::_('User.ua_edit_user_form_title', $name, FALSE)
			: Core::_('User.ua_add_user_form_title');

		$this->title($title);

		return $this;
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

		$bSave = isset($_POST['hostcms']['operation']) && in_array($_POST['hostcms']['operation'], array('save', 'saveModal'));

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
			$oDirectory_Email = $oUser_Directory_Email->Directory_Email;
			$sEmail = trim(Core_Array::getPost("email#{$oDirectory_Email->id}"));

			if (!empty($sEmail) || $bSave)
			{
				$oDirectory_Email
					->directory_email_type_id(intval(Core_Array::getPost("email_type#{$oDirectory_Email->id}", 0)))
					->value($sEmail)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				/*ob_start();
				Core::factory('Core_Html_Entity_Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='email_type#{$oDirectory_Email->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();

				$this->_Admin_Form_Controller->addMessage(ob_get_clean());*/
				$oUser_Directory_Email->Directory_Email->delete();
			}
		}

		// Электронные адреса, новые значения
		$aEmails = Core_Array::getPost('email', array());
		$aEmail_Types = Core_Array::getPost('email_type', array());

		if (is_array($aEmails) && count($aEmails))
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

					$this->_object->add($oDirectory_Email);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
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
			$oDirectory_Phone = $oUser_Directory_Phone->Directory_Phone;
			$sPhone = trim(Core_Array::getPost("phone#{$oDirectory_Phone->id}"));

			if (!empty($sPhone) || $bSave)
			{

				$oDirectory_Phone
					->directory_phone_type_id(intval(Core_Array::getPost("phone_type#{$oDirectory_Phone->id}", 0)))
					->value($sPhone)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				/*ob_start();
				Core::factory('Core_Html_Entity_Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='phone_type#{$oDirectory_Phone->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());*/

				$oUser_Directory_Phone->Directory_Phone->delete();
			}
		}

		// Телефоны, новые значения
		$aPhones = Core_Array::getPost('phone', array());
		$aPhone_Types = Core_Array::getPost('phone_type', array());

		if (is_array($aPhones) && count($aPhones))
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
			$oDirectory_Social = $oUser_Directory_Social->Directory_Social;

			$sSocial_Address = trim(Core_Array::getPost("social_address#{$oDirectory_Social->id}"));

			if (!empty($sSocial_Address) || $bSave)
			{
				$aUrl = parse_url($sSocial_Address);

				// Если не был указан протокол, или
				// указанный протокол некорректен для url
				!array_key_exists('scheme', $aUrl)
					&& $sSocial_Address = $sSocial_Address;

				$oDirectory_Social
					->directory_social_type_id(intval(Core_Array::getPost("social#{$oDirectory_Social->id}", 0)))
					->value($sSocial_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				/*ob_start();
				Core::factory('Core_Html_Entity_Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='social#{$oDirectory_Social->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());*/

				$oUser_Directory_Social->delete();
			}
		}

		// Социальные сети, новые значения
		$aSocial_Addresses = Core_Array::getPost('social_address', array());
		$aSocials = Core_Array::getPost('social', array());

		if (is_array($aSocial_Addresses) && count($aSocial_Addresses))
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
						&& $sSocial_Address = $sSocial_Address;

					$oDirectory_Social = Core_Entity::factory('Directory_Social')
						->directory_social_type_id(intval(Core_Array::get($aSocials, $key)))
						->value($sSocial_Address)
						->save();

					$this->_object->add($oDirectory_Social);

					ob_start();
					Core::factory('Core_Html_Entity_Script')
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

		// Мессенджеры, установленные значения
		$aUser_Directory_Messengers = $this->_object->User_Directory_Messengers->findAll();
		foreach ($aUser_Directory_Messengers as $oUser_Directory_Messenger)
		{
			$oDirectory_Messenger = $oUser_Directory_Messenger->Directory_Messenger;

			$sMessenger_Address = trim(Core_Array::getPost("messenger_username#{$oDirectory_Messenger->id}"));

			if (!empty($sMessenger_Address) || $bSave)
			{
				$oDirectory_Messenger
					->directory_messenger_type_id(intval(Core_Array::getPost("messenger#{$oDirectory_Messenger->id}", 0)))
					->value($sMessenger_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				/*ob_start();
				Core::factory('Core_Html_Entity_Script')
					->value("$.deleteFormRow($(\"#{$windowId} select[name='messenger#{$oDirectory_Messenger->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());*/

				$oUser_Directory_Messenger->delete();
			}
		}

		// Мессенджеры, новые значения
		$aMessenger_Addresses = Core_Array::getPost('messenger_username', array());
		$aMessengers = Core_Array::getPost('messenger', array());

		if (is_array($aMessenger_Addresses) && count($aMessenger_Addresses))
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

			$sWebsite_Address = trim(Core_Array::getPost("website_address#{$oDirectory_Website->id}"));

			if (!empty($sWebsite_Address) || $bSave)
			{
				$aUrl = parse_url($sWebsite_Address);

				// Если не был указан протокол, или
				// указанный протокол некорректен для url
				!array_key_exists('scheme', $aUrl)
					&& $sWebsite_Address = 'http://' . $sWebsite_Address;

				$oDirectory_Website
					->description(strval(Core_Array::getPost("website_description#{$oDirectory_Website->id}")))
					->value($sWebsite_Address)
					->save();
			}
			else
			{
				// Удаляем пустую строку с полями
				/*ob_start();
				Core::factory('Core_Html_Entity_Script')
					->value("$.deleteFormRow($(\"#{$windowId} input[name='website_address#{$oDirectory_Website->id}']\").closest('.row').find('.btn-delete').get(0));")
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());*/

				$oDirectory_Website->delete();
			}
		}

		// Сайты, новые значения
		$aWebsite_Addresses = Core_Array::getPost('website_address', array());
		$aWebsite_Names = Core_Array::getPost('website_description', array());

		if (is_array($aWebsite_Addresses) && count($aWebsite_Addresses))
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

					ob_start();
					Core::factory('Core_Html_Entity_Script')
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

		// Расписание
		for ($day = 1; $day <= 7; $day++)
		{
			$from = Core_Array::getPost("day_{$day}_from");
			$to = Core_Array::getPost("day_{$day}_to");

			$from = str_replace(' ', '', $from);
			$to = str_replace(' ', '', $to);

			// Перерыв
			$break_from = Core_Array::getPost("day_break_{$day}_from");
			$break_to = Core_Array::getPost("day_break_{$day}_to");

			$break_from = str_replace(' ', '', $break_from);
			$break_to = str_replace(' ', '', $break_to);

			$oUser_Worktime = $this->_object->User_Worktimes->getByDay($day);

			if ($from != '00:00' && $to != '00:00')
			{
				if (is_null($oUser_Worktime))
				{
					$oUser_Worktime = Core_Entity::factory('User_Worktime');
					$oUser_Worktime->user_id = $this->_object->id;
					$oUser_Worktime->day = $day;
				}

				$oUser_Worktime->from = $from . ':00';
				$oUser_Worktime->to = $to . ':00';
				$oUser_Worktime->break_from = $break_from . ':00';
				$oUser_Worktime->break_to = $break_to . ':00';
				$oUser_Worktime->save();
			}
			elseif (!is_null($oUser_Worktime))
			{
				$oUser_Worktime->delete();
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
		$aSites = Core_Auth::getCurrentUser()->getSites();

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