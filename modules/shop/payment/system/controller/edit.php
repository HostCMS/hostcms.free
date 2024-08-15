<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Payment_System Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Payment_System_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		$this
			->addSkipColumn('image')
			->addSkipColumn('image_height')
			->addSkipColumn('image_width');

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

		$fileContent = $this->_object->loadPaymentSystemFile();

		if (!$this->_object->id || strpos($fileContent, 'Shop_Payment_System_Handler' . $this->_object->id) === FALSE)
		{
			$this->addMessage(
				Core_Message::get(Core::_('Shop_Payment_System.attention'), 'error')
			);
		}

		$oMainTab = $this->getTab('main');

		$oAdditionalTab = $this->getTab('additional');

		$oAdditionalTab->delete($this->getField('shop_id'));

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteuserGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		// Магазин, которому принадлежит данный тип доставки
		$oShop = $this->_object->Shop;

		$oShopField = Admin_Form_Entity::factory('Select')
			->name('shop_id')
			->caption(Core::_('Shop_Payment_System.shop_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			->options(
				Shop_Controller::fillShops(CURRENT_SITE)
			)
			->value($this->_object->shop_id);

		$oMainRow1->add($oShopField);

		$oAdditionalTab->delete($this->getField('shop_currency_id'));

		$oCurrencyField = Admin_Form_Entity::factory('Select')
			->name('shop_currency_id')
			->caption(Core::_('Shop_Payment_System.shop_currency_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			->options(
				Shop_Controller::fillCurrencies()
			)
			->value($this->_object->shop_currency_id);

		$oMainRow1->add($oCurrencyField);

		$oMainTab->delete($this->getField('type'));

		$oTypeField = Admin_Form_Entity::factory('Select')
			->name('type')
			->caption(Core::_('Shop_Payment_System.type'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			->options(array(
				0 => Core::_('Shop_Payment_System.type0'),
				1 => Core::_('Shop_Payment_System.type1'),
				2 => Core::_('Shop_Payment_System.type2'),
				3 => Core::_('Shop_Payment_System.type3')
			))
			->value($this->_object->type);

		$oMainRow1->add($oTypeField);

		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('shop_order_status_id'));

		$oDropdownlistStatuses = Admin_Form_Entity::factory('Dropdownlist')
			->options(Shop_Order_Status_Controller_Edit::getDropdownlistOptions($this->_object->shop_id))
			->name('shop_order_status_id')
			->value($this->_object->shop_order_status_id)
			->caption(Core::_('Shop_Payment_System.shop_order_status_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oDropdownlistStatuses);

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);

		// Добавляем новое поле типа файл
		$oImageField = Admin_Form_Entity::factory('File');

		$oLargeFilePath = $this->_object->image != '' && Core_File::isFile($this->_object->getPaymentSystemImageFilePath())
			? $this->_object->getPaymentSystemImageFileHref()
			: '';

		$sFormPath = $this->_Admin_Form_Controller->getPath();
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oImageField
			// ->divAttr(array('class' => 'input-group col-xs-12'))
			->name("image")
			->id("image")
			->largeImage(array(
				'max_width' => $oShop->image_large_max_width,
				'max_height' => $oShop->image_large_max_height,
				'path' => $oLargeFilePath,
				'show_params' => TRUE,
				'watermark_position_x' => $oShop->watermark_default_position_x,
				'watermark_position_y' => $oShop->watermark_default_position_y,
				'place_watermark_checkbox_checked' => 0,
				'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1&shop_id={$oShop->id}', action: 'deleteImage', windowId: '{$windowId}'}); return false",
				'caption' => Core::_('Shop_Delivery.image'),
				'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio
			))
			->smallImage(array(
				'show' => FALSE
			));

		$oMainRow4->add($oImageField);

		// Группа доступа
		$aSiteuser_Groups = array(0 => Core::_('Shop_Discount.all'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
			$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
		}

		$oSiteuserGroupBlock
			->add(Admin_Form_Entity::factory('Div')
				->class('header bordered-azure')
				->value(Core::_("Shop_Payment_System.siteuser_groups"))
			)
			->add($oSiteuserGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$aTmp = array();

		$aShop_Payment_System_Siteuser_Groups = $this->_object->Shop_Payment_System_Siteuser_Groups->findAll(FALSE);
		foreach ($aShop_Payment_System_Siteuser_Groups as $oShop_Payment_System_Siteuser_Group)
		{
			!in_array($oShop_Payment_System_Siteuser_Group->siteuser_group_id, $aTmp)
				&& $aTmp[] = $oShop_Payment_System_Siteuser_Group->siteuser_group_id;
		}

		foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
		{
			$oSiteuserGroupBlockRow1->add($oCheckbox = Admin_Form_Entity::factory('Checkbox')
				->divAttr(array('class' => 'form-group col-xs-12 col-md-4'))
				->name('siteuser_group_' . $siteuser_group_id)
				->caption(htmlspecialchars($name))
			);

			(!$this->_object->id || in_array($siteuser_group_id, $aTmp))
				&& $oCheckbox->checked('checked');
		}

		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5);

		$Admin_Form_Entity_Textarea = Admin_Form_Entity::factory('Textarea');

		$oTmpOptions = $Admin_Form_Entity_Textarea->syntaxHighlighterOptions;
		$oTmpOptions['mode'] = '"ace/mode/php"';

		$Admin_Form_Entity_Textarea
			->value($fileContent)
			->rows(30)
			->divAttr(array('class' => 'form-group col-xs-12'))
			->caption(Core::_('Shop_Payment_System.system_of_pay_add_form_handler'))
			->name('system_of_pay_add_form_handler')
			->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
			->syntaxHighlighterOptions($oTmpOptions);

		$oMainRow6->add($Admin_Form_Entity_Textarea);

		$this->title($this->_object->id
			? Core::_('Shop_Payment_System.system_of_pay_edit_form_title', $this->_object->name, FALSE)
			: Core::_('Shop_Payment_System.system_of_pay_add_form_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Payment_System_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		$oShop = $this->_object->Shop;

		$fileContent = Core_Array::getRequest('system_of_pay_add_form_handler', '');

		if (strpos($fileContent, 'Shop_Payment_System_Handler' . $this->_object->id) === FALSE)
		{
			$fileContent = preg_replace('/(class Shop_Payment_System_Handler)\d* /i', '${1}' . $this->_object->id . ' ', $fileContent);
		}

		$this->_object->savePaymentSystemFile($fileContent);

		// Обработка картинок
		$param = array();

		$image = '';

		$aCore_Config = Core::$mainConfig;

		$bImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if ($bImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'],
			$aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($this->_object->image)
				{
					$this->_object->deleteImage();
				}

				$file_name = $aFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					$image = $file_name;
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($aFileData['name']);

					$image = 'shop_payment' . $this->_object->id . '.' . ($ext == '' ? '' : $ext);
				}
			}
			else
			{
				$this->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow',
						Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		if ($bImageIsCorrect)
		{
			// Путь к файлу-источнику большого изображения;
			$param['large_image_source'] = $aFileData['tmp_name'];
			// Оригинальное имя файла большого изображения
			$param['large_image_name'] = $aFileData['name'];

			// Путь к создаваемому файлу большого изображения;
			$param['large_image_target'] = !empty($image)
				? $this->_object->getPath() . $image
				: '';

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = FALSE;

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = Core_Array::getPost(
				'large_max_width_image', 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = Core_Array::getPost(
				'large_max_height_image', 0);

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = 0;

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = 0;

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = $oShop->getWatermarkFilePath();

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = Core_Array::getPost(
				'watermark_position_x_image');

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = Core_Array::getPost(
				'watermark_position_y_image');

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = !is_null(
				Core_Array::getPost('large_place_watermark_checkbox_image'));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = FALSE;

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(
				Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = FALSE;

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image = $image;

				$this->_object->setImageSizes();
			}

			$this->_object->save();
		}

		// Группа доступа
		$aSiteuser_Groups = array(0 => Core::_('Structure.all'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
			$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
		}

		$aTmp = array();

		$aShop_Payment_System_Siteuser_Groups = $this->_object->Shop_Payment_System_Siteuser_Groups->findAll(FALSE);
		foreach ($aShop_Payment_System_Siteuser_Groups as $oShop_Payment_System_Siteuser_Group)
		{
			!in_array($oShop_Payment_System_Siteuser_Group->siteuser_group_id, $aTmp)
				&& $aTmp[] = $oShop_Payment_System_Siteuser_Group->siteuser_group_id;
		}

		foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
		{
			$bSiteuserGroupChecked = Core_Array::getPost('siteuser_group_' . $siteuser_group_id);

			if ($bSiteuserGroupChecked)
			{
				if (!in_array($siteuser_group_id, $aTmp))
				{

					$oShop_Payment_System_Siteuser_Group = Core_Entity::factory('Shop_Payment_System_Siteuser_Group');
					$oShop_Payment_System_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
					$this->_object->add($oShop_Payment_System_Siteuser_Group);
				}
			}
			else
			{
				if (in_array($siteuser_group_id, $aTmp))
				{
					$oShop_Payment_System_Siteuser_Group = $this->_object->Shop_Payment_System_Siteuser_Groups->getObject($this->_object, $siteuser_group_id);

					!is_null($oShop_Payment_System_Siteuser_Group)
						&& $oShop_Payment_System_Siteuser_Group->delete();
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}