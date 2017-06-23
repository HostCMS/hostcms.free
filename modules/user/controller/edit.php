<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			->addSkipColumn('image');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		
		$oAdditionalTab->delete($this->getField('user_group_id'));

		$user_group_id = is_null($this->_object->id)
			? intval(Core_Array::getGet('user_group_id', 0))
			: $this->_object->user_group_id;

		$oMainTab
			->add($oMainRow0 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab->move($this->getField('login'), $oMainRow0);

		$oMainTab
			->move($this->getField('surname')->divAttr(array('class' => 'form-group col-lg-4 col-md-4 col-sm-12')), $oMainRow1)
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-lg-4 col-md-4 col-sm-12')), $oMainRow1)
			->move($this->getField('patronymic')->divAttr(array('class' => 'form-group col-lg-4 col-md-4 col-sm-12')), $oMainRow1);

		// Селектор с группами пользователей
		$oSelect_User_Groups = Admin_Form_Entity::factory('Select')
			->options($this->_fillUserGroup())
			->name('user_group_id')
			->value($user_group_id)
			->caption(Core::_('User.users_type_form'));

		$oMainRow2->add($oSelect_User_Groups);

		$oMainTab->delete($this->getField('password'));

		$aPasswordFormat = array(
			'minlen' => array('value' => 9),
			'maxlen' => array('value' => 255)
		);

		$oPasswordFirst = Admin_Form_Entity::factory('Password')
			->caption(Core::_('User.password'))
			->id('password_first')
			->name('password_first')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6'));

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

		$oMainRow3
			->add($oPasswordFirst)
			->add($oPasswordSecond);

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
					'path' => is_file($this->_object->getImageFilePath())
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
			->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainRow3->add($oImageField);

		$oMainTab->delete($this->getField('settings'));

		$oMainTab
			->move($this->getField('active'), $oMainRow4)
			->move($this->getField('superuser'), $oMainRow5)
			->move($this->getField('only_access_my_own'), $oMainRow6)
			->move($this->getField('read_only'), $oMainRow7);

		$oPersonalDataTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('User.users_type_form_tab_2'))
			->name('tab_personal_data');

		$this->addTabAfter($oPersonalDataTab, $oMainTab);

		$oPersonalDataTab
			->add($oPersonalDataRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oPersonalDataRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('position'), $oPersonalDataRow1)
			->move($this->getField('email'), $oPersonalDataRow2)
			->move($this->getField('icq'), $oPersonalDataRow3)
			->move($this->getField('site'), $oPersonalDataRow4);

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
		
		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill sites list
	 * @return array
	 */
	public function fillSites()
	{
		$aSites = Core_Entity::factory('User')->getCurrent()->getSites();

		$aReturn = array();
		foreach($aSites as $oSite)
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