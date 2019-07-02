<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Seller Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Seller_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		$this
			->addSkipColumn('image_large')
			->addSkipColumn('image_small')
			->addSkipColumn('image_large_width')
			->addSkipColumn('image_large_height')
			->addSkipColumn('image_small_width')
			->addSkipColumn('image_small_height');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oDescriptionField = $this->getField('description');

		$oDescriptionField->wysiwyg = TRUE;

		$oMainTab->move($this->getField('path')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow1);
		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('siteuser_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = $this->_object->Siteuser;

			$options = !is_null($oSiteuser->id)
				? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
				: array(0);

			$oSiteuserSelect = Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Seller.siteuser_id'))
				->id('object_siteuser_id')
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				->divAttr(array('class' => 'form-group col-xs-12'));

			$oMainRow1
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12 col-sm-4 no-padding')
						->add($oSiteuserSelect)
				);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
		}

		$oMainTab->move($this->getField('contact_person')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow2);
		$oMainTab->move($this->getField('address')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow2);

		$oMainTab->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow3);
		$oMainTab->move($this->getField('fax')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow3);

		$oMainTab->move($this->getField('site')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);
		$oMainTab->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);
		$oMainTab->move($this->getField('tin')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);

		// Добавляем новое поле типа файл
		$oImageField = Admin_Form_Entity::factory('File');

		$oLargeFilePath = is_file($this->_object->getLargeFilePath())
			? $this->_object->getLargeFileHref()
			: '';

		$oSmallFilePath = is_file($this->_object->getSmallFilePath())
			? $this->_object->getSmallFileHref()
			: '';

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oImageField
			->divAttr(array('class' => 'form-group col-xs-12'))
			->name("image")
			->id("image")
			->largeImage(
				array('max_width' => $oShop->image_large_max_width,
					'max_height' => $oShop->image_large_max_height,
					'path' => $oLargeFilePath,
					'show_params' => TRUE,
					'watermark_position_x' => $oShop->watermark_default_position_x,
					'watermark_position_y' => $oShop->watermark_default_position_y,
					'place_watermark_checkbox_checked' => $oShop->watermark_default_use_large_image,
					'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteLargeImage', windowId: '{$windowId}'}); return false",
					'caption' => Core::_('Shop_Producer.image_large'),
					'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio
				)
			)
			->smallImage
			(
				array('max_width' => $oShop->image_small_max_width,
					'max_height' => $oShop->image_small_max_height,
					'path' => $oSmallFilePath,
					'create_small_image_from_large_checked' => $this->_object->image_small == '',
					'place_watermark_checkbox_checked' => $oShop->watermark_default_use_small_image,
					'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteSmallImage', windowId: '{$windowId}'}); return false",
					'caption' => Core::_('Shop_Producer.image_small'),
					'show_params' => TRUE,
					'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio_small
				)
			);

		$oMainTab->addAfter($oImageField, $oDescriptionField);

		$title = $this->_object->id
			? Core::_('Shop_Seller.form_sellers_edit_title', $this->_object->name)
			: Core::_('Shop_Seller.form_sellers_add_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Seller_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));

		parent::_applyObjectProperty();

		$oShop = $this->_object->Shop;

		// Обработка картинок
		$param = array();

		$large_image = '';
		$small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = Core_Array::getPost('create_small_image_from_large_small_image');

		$bLargeImageIsCorrect =
		// Поле файла большого изображения существует
		!is_null($aFileData = Core_Array::getFiles('image', NULL))
		// и передан файл
		&& intval($aFileData['size']) > 0;

		if ($bLargeImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'],
			$aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($this->_object->image_large)
				{
					// !! дописать метод
					$this->_object->deleteLargeImage();
				}

				$file_name = $aFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					$large_image = $file_name;
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($aFileData['name']);

					$large_image = 'seller_' . $this->_object->id . ($ext == '' ? '' : ".$ext");
				}
			}
			else
			{
				$this->addMessage(
					Core_Message::get(Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])), 'error')
				);
			}
		}

		$aSmallFileData = Core_Array::getFiles('small_image', NULL);
		$bSmallImageIsCorrect =
		// Поле файла малого изображения существует
		!is_null($aSmallFileData)
		&& $aSmallFileData['size'];

		// Задано малое изображение и при этом не задано создание малого изображения
		// из большого или задано создание малого изображения из большого и
		// при этом не задано большое изображение.

		if ($bSmallImageIsCorrect
		|| $create_small_image_from_large
		&& $bLargeImageIsCorrect)
		{
			// Удаление файла малого изображения
			if ($this->_object->image_small)
			{
				$this->_object->deleteSmallImage();
			}

			// Явно указано малое изображение
			if ($bSmallImageIsCorrect
			&& Core_File::isValidExtension($aSmallFileData['name'],
			$aCore_Config['availableExtension']))
			{
				// Для инфогруппы ранее задано изображение
				if ($this->_object->image_large != '')
				{
					// Существует ли большое изображение
					$param['large_image_isset'] = true;
					$create_large_image = false;
				}
				else // Для информационной группы ранее не задано большое изображение
				{
					$create_large_image = empty($large_image);
				}


				$file_name = $aSmallFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					if ($create_large_image)
					{
						$large_image = $file_name;
						$small_image = 'small_' . $large_image;
					}
					else
					{
						$small_image = $file_name;
					}
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($file_name);

					$small_image = 'small_seller_' . $this->_object->id . ($ext == '' ? '' : ".$ext");
				}
			}
			elseif ($create_small_image_from_large && $bLargeImageIsCorrect)
			{
				$small_image = 'small_' . $large_image;
			}
			// Тип загружаемого файла является недопустимым для загрузки файла
			else
			{
				$this->addMessage(	Core_Message::get(	Core::_('Core.extension_does_not_allow',
					Core_File::getExtension($aSmallFileData['name'])),
									'error'
					)
				);
			}
		}

		if ($bLargeImageIsCorrect || $bSmallImageIsCorrect)
		{
			if ($bLargeImageIsCorrect)
			{
				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];
			}

			if ($bSmallImageIsCorrect)
			{
				// Путь к файлу-источнику малого изображения;
				$param['small_image_source'] = $aSmallFileData['tmp_name'];
				// Оригинальное имя файла малого изображения
				$param['small_image_name'] = $aSmallFileData['name'];
			}

			// Путь к создаваемому файлу большого изображения;
			$param['large_image_target'] = !empty($large_image)
				? $this->_object->getSellerPath() . $large_image
				: '';

			// Путь к создаваемому файлу малого изображения;
			$param['small_image_target'] = !empty($small_image)
				? $this->_object->getSellerPath() . $small_image
				: '' ;

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = !is_null(Core_Array::getPost('create_small_image_from_large_small_image'));

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = Core_Array::getPost('small_max_width_small_image');

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = Core_Array::getPost('small_max_height_small_image');

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = $oShop->getWatermarkFilePath();

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = Core_Array::getPost('watermark_position_x_image');

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = Core_Array::getPost('watermark_position_y_image');

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = !is_null(Core_Array::getPost('large_place_watermark_checkbox_image'));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = !is_null(Core_Array::getPost('small_place_watermark_checkbox_small_image'));

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('small_preserve_aspect_ratio_small_image'));

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image_large = $large_image;

				$this->_object->setLargeImageSizes();
			}

			if ($result['small_image'])
			{
				$this->_object->image_small = $small_image;

				$this->_object->setSmallImageSizes();
			}
		}

		$this->_object->save();

		if (Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(array($this->_object->indexing()));
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}