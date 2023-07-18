<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Producer_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Groups tree
	 * @var array
	 */
	protected $_aGroupTree = array();

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'shop_producer':

				if (!$object->id)
				{
					$object->shop_id = Core_Array::getGet('shop_id');
					$object->shop_producer_dir_id = Core_Array::getGet('producer_dir_id');
				}

				$this
					->addSkipColumn('image_large')
					->addSkipColumn('image_small');

				parent::setObject($object);

				$this->getField('description')
					->rows(15)
					->wysiwyg(Core::moduleIsActive('wysiwyg'));

				$oMainTab = $this->getTab('main');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oTabBlock = Admin_Form_Entity::factory('Div')->id('shop_tabs')->class('well with-header'))
					;

				$oAdditionalTab = $this->getTab('additional');

				$oContactsTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Producer.tab2'))
					->name('Contacts');

				$oContactsTab
					->add($oContactsTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oContactsTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oContactsTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oContactsTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oContactsTabRow5 = Admin_Form_Entity::factory('Div')->class('row'));

				$oBankContactsTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Producer.tab3'))
					->name('BankContacts');

				$oBankContactsTab
					->add($oBankContactsTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow8 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow9 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oBankContactsTabRow10 = Admin_Form_Entity::factory('Div')->class('row'));

				$oSEOTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Producer.tab4'))
					->name('SEO');

				$oSEOTab
					->add($oSEOTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSEOTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSEOTabRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$this
					->addTabAfter($oContactsTab, $oMainTab)
					->addTabAfter($oBankContactsTab, $oContactsTab)
					->addTabAfter($oSEOTab, $oBankContactsTab);

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

				$oMainTab->move($this->getField('address')->divAttr(array('class' => 'form-group col-xs-12')), $oContactsTabRow1);
				$oMainTab->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-12')), $oContactsTabRow2);
				$oMainTab->move($this->getField('fax')->divAttr(array('class' => 'form-group col-xs-12')), $oContactsTabRow3);
				$oMainTab->move($this->getField('site')->divAttr(array('class' => 'form-group col-xs-12')), $oContactsTabRow4);
				$oMainTab->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12')), $oContactsTabRow5);

				$oMainTab->move($this->getField('tin')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('kpp')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('psrn')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('okpo')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('okved')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('bik')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('current_account')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('correspondent_account')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('bank_name')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);
				$oMainTab->move($this->getField('bank_address')->divAttr(array('class' => 'form-group col-xs-12')), $oBankContactsTabRow1);

				$oMainTab->move($this->getField('seo_title')->divAttr(array('class' => 'form-group col-xs-12')), $oSEOTabRow1);
				$oMainTab->move($this->getField('seo_description')->divAttr(array('class' => 'form-group col-xs-12')), $oSEOTabRow2);
				$oMainTab->move($this->getField('seo_keywords')->divAttr(array('class' => 'form-group col-xs-12')), $oSEOTabRow3);

				$oShop = $this->_object->Shop;

				// Добавляем новое поле типа файл
				$oImageField = Admin_Form_Entity::factory('File');

				$oLargeFilePath = $this->_object->image_large != '' && Core_File::isFile($this->_object->getLargeFilePath())
					? $this->_object->getLargeFileHref()
					: '';

				$oSmallFilePath = $this->_object->image_small != '' && Core_File::isFile($this->_object->getSmallFilePath())
					? $this->_object->getSmallFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oImageField
					->divAttr(array('class' => 'form-group col-xs-12'))
					->name("image")
					->id("image")
					->largeImage(array(
						'max_width' => $oShop->producer_image_large_max_width,
						'max_height' => $oShop->producer_image_large_max_height,
						'path' => $oLargeFilePath,
						'show_params' => TRUE,
						'watermark_position_x' => 0,
						'watermark_position_y' => 0,
						'place_watermark_checkbox_checked' => 0,
						'delete_onclick' =>
						"$.adminLoad({path: '{$sFormPath}', additionalParams:
						'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1',
						action: 'deleteLargeImage', windowId: '{$windowId}'}); return false",
						'caption' => Core::_('Shop_Producer.image_large'),
						'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio
					))
					->smallImage(array(
						'max_width' => $oShop->producer_image_small_max_width,
						'max_height' => $oShop->producer_image_small_max_height,
						'path' => $oSmallFilePath,
						'create_small_image_from_large_checked' =>
						$this->_object->image_small == '',
						'place_watermark_checkbox_checked' =>
						$oShop->watermark_default_use_small_image,
						'delete_onclick' => "$.adminLoad({path: '{$sFormPath}',
						additionalParams:
						'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1',
						action: 'deleteSmallImage', windowId: '{$windowId}'}); return false",
						'caption' => Core::_('Shop_Producer.image_small'),
						'show_params' => TRUE,
						'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio_small
					));

				$oMainRow6->add($oImageField);

				// Удаляем группу товаров
				$oAdditionalTab->delete($this->getField('shop_producer_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');
				$oGroupSelect->caption(Core::_('Shop_Producer_Dir.parent_id'))
					->options(array(' … ') + $this->fillGroupList($this->_object->shop_id))
					->name('shop_producer_dir_id')
					->value($this->_object->shop_producer_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12'))
					->filter(TRUE);

				// Добавляем группу товаров
				$oMainRow2->add($oGroupSelect);

				$oTabBlock
					->add(Admin_Form_Entity::factory('Div')
							->class('header bordered-warning')
							->value(Core::_('Shop_Item.shop_tab_header'))
						)
					->add($oTabRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAdditionalItemsSelect = Admin_Form_Entity::factory('Select')
					->options($this->_fillShopTabs())
					->name('shop_tab_id[]')
					->class('shop-tabs')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$this->addField($oAdditionalItemsSelect);
				$oTabRow1->add($oAdditionalItemsSelect);

				$html = '<script>
				$(function(){
					$("#' . $windowId . ' .shop-tabs").select2({
						dropdownParent: $("#' . $windowId . '"),
						language: "' . Core_I18n::instance()->getLng() . '",
						minimumInputLength: 1,
						placeholder: "' . Core::_('Shop_Tab.select_tab') . '",
						tags: true,
						allowClear: true,
						multiple: true,
						ajax: {
							url: "/admin/shop/tab/index.php?autocomplete&shop_id=' . $this->_object->shop_id .'",
							dataType: "json",
							type: "GET",
							processResults: function (data) {
								var aResults = [];
								$.each(data, function (index, item) {
									aResults.push({
										"id": item.id,
										"text": item.text
									});
								});
								return {
									results: aResults
								};
							}
						}
					});
				});</script>';

				$oTabRow1->add(Admin_Form_Entity::factory('Code')->html($html));

				$oMainTab
					->move($this->getField('path')->divAttr(array('class' => 'form-group col-xs-12 col-sm-8')), $oMainRow3)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow4)
					->move($this->getField('indexing')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow4)
					->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5')), $oMainRow4)
					->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5);

				$this->title($this->_object->id
					? Core::_('Shop_Producer.producer_edit_form_title', $this->_object->name, FALSE)
					: Core::_('Shop_Producer.producer_add_form_title')
				);

			break;
			case 'shop_producer_dir':

				if (!$object->id)
				{
					$object->shop_id = Core_Array::getGet('shop_id');
					$object->parent_id = Core_Array::getGet('producer_dir_id');
				}

				parent::setObject($object);

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				// Удаляем группу товаров
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');
				$oGroupSelect->caption(Core::_('Shop_Producer_Dir.parent_id'))
					->options(array(' … ') + $this->fillGroupList($this->_object->shop_id, 0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12'))
					->filter(TRUE);

				// Добавляем группу товаров
				$oMainTab->addAfter($oGroupSelect, $this->getField('name'));

				$this->title($this->_object->id
					? Core::_('Shop_Producer_Dir.edit', $this->_object->name, FALSE)
					: Core::_('Shop_Producer_Dir.add')
				);

			break;
		}

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $shop_id shop ID
	 * @param int $parent_id parent directory ID
	 * @param array $aExclude exclude group IDs array
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillGroupList($shop_id, $parent_id = 0, $aExclude = array(), $iLevel = 0)
	{
		$shop_id = intval($shop_id);
		$parent_id = intval($parent_id);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_producer_dirs')
				->where('shop_id', '=', $shop_id)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				$this->_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset($this->_aGroupTree[$parent_id]))
		{
			$countExclude = count($aExclude);
			foreach ($this->_aGroupTree[$parent_id] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'];
					$aReturn += $this->fillGroupList($shop_id, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && $this->_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Producer_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_producer':
				$this->_object->default
					&& $this->_object->changeDefaultStatus();

				// Вкладки
				$aShopTabIds = Core_Array::getPost('shop_tab_id', array());
				!is_array($aShopTabIds) && $aShopTabIds = array();

				$aTmp = array();

				$aShop_Tabs = $this->_object->Shop_Tabs->findAll(FALSE);
				foreach ($aShop_Tabs as $oShop_Tab)
				{
					if (!in_array($oShop_Tab->id, $aShopTabIds))
					{
						$oShop_Tab_Producer = $oShop_Tab->Shop_Tab_Producers->getByShop_producer_id($this->_object->id);
						!is_null($oShop_Tab_Producer)
							&& $oShop_Tab_Producer->delete();
					}
					else
					{
						$aTmp[] = $oShop_Tab->id;
					}
				}

				// Новые вкладки
				$aNewShopTabIds = array_diff($aShopTabIds, $aTmp);
				foreach ($aNewShopTabIds as $iNewShopTabId)
				{
					$oShop_Tab_Producer = Core_Entity::factory('Shop_Tab_Producer');
					$oShop_Tab_Producer->shop_id = $this->_object->shop_id;
					$oShop_Tab_Producer->shop_producer_id = $this->_object->id;
					$oShop_Tab_Producer->shop_tab_id = $iNewShopTabId;
					$oShop_Tab_Producer->save();
				}
			break;
		}

		$param = array();

		$large_image = '';
		$small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = Core_Array::getPost(
		'create_small_image_from_large_small_image');

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
					$this->_object->deleteLargeImage();
				}

				$file_name = $aFileData['name'];

				$ext = Core_File::getExtension($file_name);

				$large_image = 'shop_producer_image' . $this->_object->id . '.' . $ext;
			}
			else
			{
				$this->addMessage(	Core_Message::get(		Core::_('Core.extension_does_not_allow',
						Core_File::getExtension($aFileData['name'])),
						'error'
					)
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
				$file_name = $aSmallFileData['name'];

				// Определяем расширение файла
				$ext = Core_File::getExtension($file_name);

				$small_image = 'small_shop_producer_image' . $this->_object->id . '.' . $ext;
			}
			elseif ($create_small_image_from_large && $bLargeImageIsCorrect)
			{
				$small_image = 'small_' . $large_image;
			}
			// Тип загружаемого файла является недопустимым для загрузки файла
			else
			{
				$this->addMessage(Core_Message::get(Core::_('Core.extension_does_not_allow',
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
				? $this->_object->getProducerPath() . $large_image
				: '';

			// Путь к создаваемому файлу малого изображения;
			$param['small_image_target'] = !empty($small_image)
				? $this->_object->getProducerPath() . $small_image
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
			$param['watermark_file_path'] = "";

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = 0;

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = 0;

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = FALSE;

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = FALSE;

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('small_preserve_aspect_ratio_small_image'));

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image_large = $large_image;

				// WARNING: Закомментировано до добавления полей для хранения
				// размеров изображений производителя
				//$this->_object->setLargeImageSizes();
			}

			if ($result['small_image'])
			{
				$this->_object->image_small = $small_image;
				//$this->_object->setSmallImageSizes();
			}
		}

		$this->_object->save();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill shortcut groups list
	 * @return array
	 */
	protected function _fillShopTabs()
	{
		$aReturn = array();

		$aShop_Tabs = $this->_object->Shop_Tabs->findAll(FALSE);
		foreach ($aShop_Tabs as $oShop_Tab)
		{
			$sParents = $oShop_Tab->shop_tab_dir_id
				? $oShop_Tab->Shop_Tab_Dir->pathWithSeparator() . ' → '
				: '';

			$aReturn[$oShop_Tab->id] = array(
				'value' => $sParents . $oShop_Tab->name . ' [' . $oShop_Tab->id . ']',
				'attr' => array('selected' => 'selected')
			);
		}

		return $aReturn;
	}
}