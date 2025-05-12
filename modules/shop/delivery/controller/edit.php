<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Delivery_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			->addSkipColumn('image_width')
			;

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

		// Главная вкладка
		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteuserGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'));

		$oAdditionalTab->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('guid'), $oAdditionalRow1);

		// Магазин, которому принадлежит данный тип доставки
		$oShop = $this->_object->Shop;

		// Добавляем новое поле типа файл
		$oImageField = Admin_Form_Entity::factory('File');

		$oLargeFilePath = $this->_object->image != '' && Core_File::isFile($this->_object->getDeliveryFilePath())
			? $this->_object->getDeliveryFileHref()
			: '';

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oImageField
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
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
				'delete_onclick' =>
				"$.adminLoad({path: '{$sFormPath}', additionalParams:
				'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1',
				action: 'deleteImage', windowId: '{$windowId}'}); return false",
				'caption' => Core::_('Shop_Delivery.image'),
				'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio
			))
			->smallImage
			(
				array(
					'show' => FALSE
				)
			);

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);

		$oMainRow4->add($oImageField);
		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);
		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow4);

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

		$aShop_Delivery_Siteuser_Groups = $this->_object->Shop_Delivery_Siteuser_Groups->findAll(FALSE);
		foreach ($aShop_Delivery_Siteuser_Groups as $oShop_Delivery_Siteuser_Group)
		{
			!in_array($oShop_Delivery_Siteuser_Group->siteuser_group_id, $aTmp)
				&& $aTmp[] = $oShop_Delivery_Siteuser_Group->siteuser_group_id;
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

		$this->title($this->_object->id
			? Core::_('Shop_Delivery.type_of_delivery_edit_form_title', $this->_object->name, FALSE)
			: Core::_('Shop_Delivery.type_of_delivery_add_form_title')
		);

		// Создаем вкладку
		$oShopDeliveryTabPaymentSystems = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Delivery.payment_systems'))
			->name('PaymentSystems');

		$this->addTabAfter($oShopDeliveryTabPaymentSystems, $oMainTab);

		// Интервалы
		$oShopDeliveryTabIntervals = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Shop_Delivery.intervals'))
			->name('Intervals');

		$this->addTabAfter($oShopDeliveryTabIntervals, $oShopDeliveryTabPaymentSystems);

		$oShopDeliveryTabIntervals
			->add($oIntervalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oShop_Delivery_Interval_Controller_Tab = new Shop_Delivery_Interval_Controller_Tab($this->_Admin_Form_Controller);

		$oDeliveryOption = $oShop_Delivery_Interval_Controller_Tab
			->shop_delivery_id($this->_object->id)
			->execute();

		$oIntervalRow1->add($oDeliveryOption);

		// Заполняем вкладку платежных систем

		// Получаем платежные системы, связанные с доставкой
		$aShop_Delivery_Payment_Systems = $this->_object->Shop_Payment_Systems->findAll();

		// Массив идентификаторов платежных систем, связанных с доставкой
		$aDelivery_Payment_Systems = array();

		foreach ($aShop_Delivery_Payment_Systems as $oShop_Delivery_Payment_System)
		{
			$aDelivery_Payment_Systems[] = $oShop_Delivery_Payment_System->id;
		}

		// Получаем список платежных систем магазина
		$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->findAll();

		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$oShop_Payment_System_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->caption(htmlspecialchars($oShop_Payment_System->name))
				->name('shop_payment_system_' . $oShop_Payment_System->id);
			$oPaymentSystemRow = Admin_Form_Entity::factory('Div')->class('row');

			(!$this->_object->id || in_array($oShop_Payment_System->id, $aDelivery_Payment_Systems))
			&& $oShop_Payment_System_Checkbox->value(1);

			$oShopDeliveryTabPaymentSystems->add(
				$oPaymentSystemRow->add($oShop_Payment_System_Checkbox)
			);
		}

		$oMainTab->delete($this->getField('type'));
		$oTypeRadio = Admin_Form_Entity::factory('Radiogroup');
		$oTypeRadio->radio(array(
				Core::_('Shop_Delivery.option0'),
				Core::_('Shop_Delivery.option1')
			))
			->ico(
				array(
					'fa-list',
					'fa-file-code-o'
				)
			)
			->divAttr(array('class' => 'form-group col-xs-12 col-md-4 margin-top-21 rounded-radio-group'))
			->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1]); window.dispatchEvent(new Event('resize'));")
			->value($this->_object->type)
			->name('type');

		$oMainRow1->add($oTypeRadio);

		$oTextarea = Admin_Form_Entity::factory('Textarea');

		$oTmpOptions = $oTextarea->syntaxHighlighterOptions;
		$oTmpOptions['mode'] = '"ace/mode/php"';

		$oTextarea->caption(Core::_('Shop_Delivery.handler'))
			->name('code')
			->id('code_handler')
			->value($this->_object->loadHandlerFile())
			->divAttr(array('id' => 'code', 'class' => 'form-group col-xs-12 hidden-0'))
			->rows(18)
			->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
			->syntaxHighlighterOptions($oTmpOptions);

		$oMainRow2
			->add($oTextarea)
			->add(
				Core_Html_Entity::factory('Script')
					->value("radiogroupOnChange('{$windowId}', {$this->_object->type}, [0,1])")
			);

		$oMainTab->delete($this->getField('method'));

		$oSelect_Method = Admin_Form_Entity::factory('Select')
			->options(
				array(
					0 => Core::_('Shop_Delivery.pickup'),
					1 => Core::_('Shop_Delivery.post'),
					2 => Core::_('Shop_Delivery.courier')
				)
			)
			->name('method')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->value($this->_object->method)
			->caption(Core::_('Shop_Delivery.method'));

		$oMainRow1->add($oSelect_Method);

		$oMainTab->move($this->getField('days_from')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-2')), $oMainRow1);
		$oMainTab->move($this->getField('days_to')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-2')), $oMainRow1);

		return $this;
	}

	/**
	 * Fill delivery list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	static public function fillDeliveries($iShopId)
	{
		$iShopId = intval($iShopId);

		$oDelivery = Core_Entity::factory('Shop_Delivery');

		$oDelivery->queryBuilder()
			->where('shop_id', '=', $iShopId)
			->orderBy('sorting')
			->orderBy('name');

		$aDeliveries = $oDelivery->findAll();

		$aDeliveryArray = array(' … ');

		foreach ($aDeliveries as $oDelivery)
		{
			$aDeliveryArray[$oDelivery->id] = array('value' => $oDelivery->name);
			!$oDelivery->active && $aDeliveryArray[$oDelivery->id]['attr'] = array(
				'class' => 'gray',
				// 'disabled' => 'disabled'
			);
		}

		return $aDeliveryArray;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Delivery_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$oShop = $this->_object->Shop;

		if (Core_Array::getRequest('type') == 1)
		{
			$fileContent = Core_Array::getRequest('code', '');

			if (strpos($fileContent, 'Shop_Delivery_Handler' . $this->_object->id) === FALSE)
			{
				$fileContent = preg_replace('/(class Shop_Delivery_Handler)\d* /i', '${1}' . $this->_object->id . ' ', $fileContent);
			}

			$this->_object->saveHandlerFile($fileContent);
		}

		// Обработка картинок
		$param = array();

		$image = '';

		$bImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if ($bImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'], Core::$mainConfig['availableExtension']))
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

					$image = 'shop_type_of_delivery_image' . $this->_object->id . '.' . ($ext == '' ? '' : $ext);
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

		// Все платежные
		$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->findAll();

		$aTmp = array();
		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$aTmp[] = $oShop_Payment_System->id;
		}

		// Массив идентификаторов платежных систем, связанных с доставкой
		$aDelivery_Payment_Systems = array();

		// Получаем платежные системы, связанные с доставкой
		$aShop_Delivery_Payment_Systems = $this->_object->Shop_Delivery_Payment_Systems->findAll();
		foreach ($aShop_Delivery_Payment_Systems as $oShop_Delivery_Payment_System)
		{
			// Fix relation with deleted Shop_Payment_System
			if (!in_array($oShop_Delivery_Payment_System->shop_payment_system_id, $aTmp))
			{
				$oShop_Delivery_Payment_System->delete();
			}
			else
			{
				$aDelivery_Payment_Systems[] = $oShop_Delivery_Payment_System->shop_payment_system_id;
			}
		}

		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$bShopPaymentSystemChecked = Core_Array::getPost('shop_payment_system_' . $oShop_Payment_System->id);

			// Платежная система выбрана
			if ($bShopPaymentSystemChecked)
			{
				// Платежная система не связана с доставкой. Добавляем платежную систему доставке
				if (!in_array($oShop_Payment_System->id, $aDelivery_Payment_Systems))
				{
					$this->_object->add($oShop_Payment_System);
				}
			}
			else // Платежная система не выбрана
			{
				// Платежная система связана с доставкой. Удаляем связь платежной системы с доставкой
				if (in_array($oShop_Payment_System->id, $aDelivery_Payment_Systems))
				{
					// $oShop_Delivery_Payment_System = $this->_object->Shop_Delivery_Payment_Systems->getByShop_payment_system_id($oShop_Payment_System->id);
					// !is_null($oShop_Delivery_Payment_System) && $oShop_Delivery_Payment_System->delete();

					$this->_object->remove($oShop_Payment_System);
				}
			}
		}

		// Группа доступа
		$aSiteuser_Groups = array(0 => Core::_('Structure.all'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
			$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
		}

		$aTmp = array();

		$aShop_Delivery_Siteuser_Groups = $this->_object->Shop_Delivery_Siteuser_Groups->findAll(FALSE);
		foreach ($aShop_Delivery_Siteuser_Groups as $oShop_Delivery_Siteuser_Group)
		{
			!in_array($oShop_Delivery_Siteuser_Group->siteuser_group_id, $aTmp)
				&& $aTmp[] = $oShop_Delivery_Siteuser_Group->siteuser_group_id;
		}

		foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
		{
			$bSiteuserGroupChecked = Core_Array::getPost('siteuser_group_' . $siteuser_group_id);

			if ($bSiteuserGroupChecked)
			{
				if (!in_array($siteuser_group_id, $aTmp))
				{
					$oShop_Delivery_Siteuser_Group = Core_Entity::factory('Shop_Delivery_Siteuser_Group');
					$oShop_Delivery_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
					$this->_object->add($oShop_Delivery_Siteuser_Group);
				}
			}
			else
			{
				if (in_array($siteuser_group_id, $aTmp))
				{
					$oShop_Delivery_Siteuser_Group = $this->_object->Shop_Delivery_Siteuser_Groups->getObject($this->_object, $siteuser_group_id);

					!is_null($oShop_Delivery_Siteuser_Group)
						&& $oShop_Delivery_Siteuser_Group->delete();
				}
			}
		}

		// Интервалы
		$oShop_Delivery_Interval_Controller_Tab = new Shop_Delivery_Interval_Controller_Tab($this->_Admin_Form_Controller);
		$oShop_Delivery_Interval_Controller_Tab
			->shop_delivery_id($this->_object->id)
			->applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}