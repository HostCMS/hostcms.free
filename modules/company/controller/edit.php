<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Load object's fields when object has been set
	 * @param object $object
	 * @return Company_Controller_Edit
	 */
	public function setObject($object)
	{
		/*$this
			->addSkipColumn('~address')
			->addSkipColumn('~phone')
			->addSkipColumn('~fax')
			->addSkipColumn('~site')
			->addSkipColumn('~email');*/

		$this
			->addSkipColumn('image');

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		// Основная вкладка
		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Добавляем вкладки
		$this
			->addTabAfter($oTabBankingDetails = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Company.tabBankingDetails'))
				->name('BankingDetails'),
			$oMainTab);

		$oMainTab
			->add($oMainTabRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oTabBankingDetails
			->add($oTabBankingDetailsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oTabBankingDetailsRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab
			->add($oAdditionalTabRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			// BankingDetails
			->move($this->getField('tin'), $oTabBankingDetails)
			->move($this->getField('kpp'), $oTabBankingDetails)
			->move($this->getField('psrn'), $oTabBankingDetails)
			->move($this->getField('okpo'), $oTabBankingDetails)
			->move($this->getField('okved'), $oTabBankingDetails)
			->move($this->getField('bic'), $oTabBankingDetails)
			->move($this->getField('current_account'), $oTabBankingDetails)
			->move($this->getField('correspondent_account'), $oTabBankingDetails)
			->move($this->getField('bank_name'), $oTabBankingDetails)
			->move($this->getField('bank_address'), $oTabBankingDetails)
			// GUID
			->move($this->getField('guid'), $oAdditionalTab);

		$oMainTab->move($this->getField('legal_name')->divAttr(array('class' => 'form-group col-xs-12 col-md-6 col-lg-3')),$oMainTabRow1);
		$oMainTab->move($this->getField('accountant_legal_name')->divAttr(array('class' => 'form-group col-xs-12 col-md-6 col-lg-3')),$oMainTabRow1);

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$aConfig = Core_Config::instance()->get('company_config', array()) + array(
			'max_height' => 130,
			'max_width' => 130
		);

		// Изображение
		$oImageField = Admin_Form_Entity::factory('File');
		$oImageField
			->type('file')
			->caption(Core::_('Company.image'))
			->name('image')
			->id('image')
			->class('') // form-group col-xs-12 col-md-6 col-lg-3
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
					'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$object->id}]=1', action: 'deleteImageFile', windowId: '{$windowId}'}); return false",
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
			->divAttr(array('class' => 'form-group col-xs-12 col-md-6 col-lg-3'))
			;

		$oMainTabRow1->add($oImageField);

		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-md-6 col-lg-3')),$oMainTabRow1);

		// Адреса
		$oCompanyAddressesRow = Directory_Controller_Tab::instance('address')
			->title(Core::_('Directory_Address.addresses'))
			->relation($this->_object->Company_Directory_Addresses)
			->execute();

		$oMainTab->add($oCompanyAddressesRow);

		// Телефоны
		$oCompanyPhonesRow = Directory_Controller_Tab::instance('phone')
			->title(Core::_('Directory_Phone.phones'))
			->relation($this->_object->Company_Directory_Phones)
			->execute();

		$oMainTab->add($oCompanyPhonesRow);

		// Email'ы
		$oCompanyEmailsRow = Directory_Controller_Tab::instance('email')
			->title(Core::_('Directory_Email.emails'))
			->relation($this->_object->Company_Directory_Emails)
			->execute();

		$oMainTab->add($oCompanyEmailsRow);

		// Социальные сети
		$oCompanySocialRow = Directory_Controller_Tab::instance('social')
			->title(Core::_('Directory_Social.socials'))
			->relation($this->_object->Company_Directory_Socials)
			->execute();

		$oMainTab->add($oCompanySocialRow);

		// Мессенджеры
		$oCompanyMessengerRow = Directory_Controller_Tab::instance('messenger')
			->title(Core::_('Directory_Messenger.messengers'))
			->relation($this->_object->Company_Directory_Messengers)
			->execute();

		$oMainTab->add($oCompanyMessengerRow);

		// Сайты
		$oCompanyWebsitesRow = Directory_Controller_Tab::instance('website')
			->title(Core::_('Directory_Website.sites'))
			->relation($this->_object->Company_Directory_Websites)
			->execute();

		$oMainTab->add($oCompanyWebsitesRow);

		$oAdmin_Form_Entity_Section = Admin_Form_Entity::factory('Section')
			->caption(Core::_('Company.sites'))
			->id('accordion_' . $object->id);

		$oMainTab->add($oAdmin_Form_Entity_Section);

		// Sites
		$aTmp = array();
		$aCompany_Sites = $object->Company_Sites->findAll(FALSE);
		foreach ($aCompany_Sites as $oCompany_Site)
		{
			$aTmp[] = $oCompany_Site->site_id;
		}

		$aSites = Core_Entity::factory('Site')->findAll();
		foreach ($aSites as $oSite)
		{
			$oAdmin_Form_Entity_Section->add($oCheckbox = Admin_Form_Entity::factory('Checkbox')
				->divAttr(array('class' => 'form-group col-xs-12 col-md-6 no-padding-left'))
				->name('site_' . $oSite->id)
				->caption($oSite->name)
			);

			in_array($oSite->id, $aTmp) && $oCheckbox->checked('checked');
		}

		$oTabBankingDetails->move($this->getField('tin')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow1);
		$oTabBankingDetails->move($this->getField('kpp')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow1);

		$oTabBankingDetails->move($this->getField('psrn')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow2);
		$oTabBankingDetails->move($this->getField('okpo')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow2);

		$oTabBankingDetails->move($this->getField('okved')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow3);
		$oTabBankingDetails->move($this->getField('bic')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow3);

		$oTabBankingDetails->move($this->getField('current_account')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow4);
		$oTabBankingDetails->move($this->getField('correspondent_account')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow4);

		$oTabBankingDetails->move($this->getField('bank_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow5);
		$oTabBankingDetails->move($this->getField('bank_address')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oTabBankingDetailsRow5);

		$oAdditionalTab->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')),$oAdditionalTabRow1);

		$title = $this->_object->id
			? Core::_('Company.company_form_edit_title', $this->_object->name)
			: Core::_('Company.company_form_add_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Company_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this
			->addSkipColumn('phone')
			->addSkipColumn('fax')
			->addSkipColumn('site')
			->addSkipColumn('email');

		parent::_applyObjectProperty();

		$aTmp = array();

		$aCompany_Sites = $this->_object->Company_Sites->findAll(FALSE);
		foreach ($aCompany_Sites as $oCompany_Site)
		{
			if (Core_Array::getPost('site_' . $oCompany_Site->site_id))
			{
				$aTmp[] = $oCompany_Site->site_id;
			}
			else
			{
				$oCompany_Site->delete();
			}
		}

		$aSites = Core_Entity::factory('Site')->findAll(FALSE);
		foreach ($aSites as $oSite)
		{
			if (Core_Array::getPost('site_' . $oSite->id) && !in_array($oSite->id, $aTmp))
			{
				$oCompany_Site = Core_Entity::factory('Company_Site');
				$oCompany_Site->site_id = $oSite->id;
				$oCompany_Site->company_id = $this->_object->id;
				$oCompany_Site->save();
			}
		}

		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		Directory_Controller_Tab::instance('address')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('email')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('phone')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('website')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('social')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('messenger')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);

		if (
			// Поле файла существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0)
		{
			if (Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
			{
				$fileExtension = Core_File::getExtension($aFileData['name']);
				$sImageName = 'image.' . $fileExtension;

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
}