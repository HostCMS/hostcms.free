<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Wallpaper_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Wallpaper_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('image_large')
			->addSkipColumn('image_small');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab->move($this->getField('name'), $oMainRow1);

		$sFormPath = $this->_Admin_Form_Controller->getPath();

		// Изображение
		$oImageField = Admin_Form_Entity::factory('File');
		$oImageField
			->type('file')
			->caption(Core::_('User_Wallpaper.image'))
			->name('image')
			->id('image')
			->largeImage(
				array(
					'max_width' => 3840,
					'max_height' => 2160,
					'path' => is_file($this->_object->getLargeImageFilePath())
						? $this->_object->getLargeImageFileHref()
						: '',
					'show_params' => FALSE,
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

		$oMainRow2->add($oImageField);

		$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow3);

		$title = $this->_object->id
			? Core::_('User_Wallpaper.edit_title', $this->_object->name)
			: Core::_('User_Wallpaper.add_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event User_Wallpaper_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
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
				$sImageName = 'wallpaper_' . $this->_object->id . '.' . $fileExtension;
				$sSmallImageName = 'wallpaper_' . $this->_object->id . '_30x30.' . $fileExtension;

				$param = array();

				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];
				// Путь к создаваемому файлу большого изображения;
				$param['large_image_target'] = $this->_object->getPath() . $sImageName;
				// Значение максимальной ширины большого изображения
				$param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);
				// Значение максимальной высоты большого изображения
				$param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

				// Использовать большое изображение для создания малого
				$param['create_small_image_from_large'] = TRUE;

				$param['small_image_target'] = $this->_object->getPath() . $sSmallImageName;
				// Значение максимальной ширины малого изображения;
				$param['small_image_max_width'] = 30;
				// Значение максимальной высоты малого изображения;
				$param['small_image_max_height'] = 30;

				// Сохранять пропорции изображения для малого изображения
				$param['small_image_preserve_aspect_ratio'] = FALSE;

				$result = Core_File::adminUpload($param);

				if ($result['large_image'])
				{
					$this->_object->image_large = $sImageName;
					$this->_object->save();
				}

				if ($result['small_image'])
				{
					$this->_object->image_small = $sSmallImageName;
					$this->_object->save();
				}

				$this->addMessage('<script>$.loadWallpaper(' . $this->_object->id . ')</script>');
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