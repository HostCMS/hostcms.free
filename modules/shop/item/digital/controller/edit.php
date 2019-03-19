<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Digital Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Digital_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$oShopItem = Core_Entity::factory('Shop', Core_Array::getGet('shop_item_id', 0));

		if (!$object->id)
		{
			$object->shop_item_id = $oShopItem->id;
		}

		$this->addSkipColumn('filename');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oImageField = Admin_Form_Entity::factory('File');

		$sFilePath = is_file($this->_object->getFullFilePath())
			? $this->_object->getFullFilePath()
			: '';

		if ($sFilePath)
		{
			$sFilePath = "/admin/shop/item/digital/index.php?download_digital_file={$this->_object->id}";
		}

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oImageField
			->style("width: 400px;")
			->name("image")
			->id("image")
			->largeImage(array(
					'path' => $sFilePath,
					'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteFile', windowId: '{$windowId}'}); return false", 'caption' => Core::_('Shop_Item_Digital.filename')
				))
			->smallImage(array('show' => FALSE));

		$oMainTab->addAfter($oImageField, $this->getField('value'));

		$this->getField('count')->style("width: 110px");

		$title = $this->_object->id
			? Core::_('Shop_Item.eitems_edit_title')
			: Core::_('Shop_Item.eitems_add_title');

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Item_Digital_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		// Обработка картинок
		$param = array();

		$large_image = '';

		$aCore_Config = Core::$mainConfig;

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
				if ($this->_object->filename)
				{
					$this->_object->deleteFile();
				}

				$ext = Core_File::getExtension($aFileData['name']);

				$large_image = $this->_object->id . ($ext == '' ? '' : '.' . $ext);

				$this->_object->createDir();

				try {
					Core_File::moveUploadedFile($aFileData['tmp_name'], $this->_object->getFilePath() . $large_image);
					$this->_object->filename = $large_image;
				} catch (Exception $e) {
				}
			}
			else
			{
				$this->addMessage(Core_Message::get(Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])), 'error'));
			}
		}

		$this->_object->save();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}