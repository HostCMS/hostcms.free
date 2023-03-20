<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
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
			case 'printlayout':
				$this->addSkipColumn('file_name');

				parent::setObject($object);

				if (!$this->_object->id)
				{
					$this->_object->printlayout_dir_id = Core_Array::getGet('printlayout_dir_id');
				}

				$title = $this->_object->id
					? Core::_('Printlayout.edit_title', $this->_object->name, FALSE)
					: Core::_('Printlayout.add_title');

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRowFile = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainTab->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('printlayout_dir_id'));

				// Селектор с группой
				$oSelect_Dirs = Admin_Form_Entity::factory('Select')
					->options(
						array(' … ') + $this->fillPrintlayoutDir()
					)
					->name('printlayout_dir_id')
					->value($this->_object->printlayout_dir_id)
					->caption(Core::_('Printlayout.printlayout_dir_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainRow2->add($oSelect_Dirs);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow2);

				Core_Templater::decorateInput($this->getField('file_mask'));
				$oMainTab->move($this->getField('file_mask'), $oMainRow4);

				// Добавляем новое поле типа файл
				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oFilePath = $this->_object->file_name != '' && Core_File::isFile($this->_object->getFilePath())
					? '/admin/printlayout/index.php?downloadFile=' . $this->_object->id
					: '';

				$oImageField = Admin_Form_Entity::factory('File')
					->type('file')
					->divAttr(array('class' => 'input-group col-xs-12 col-sm-6'))
					->name('file')
					->id('file')
					->caption(Core::_('Printlayout.file'))
					->largeImage(
						array(
							'path' => $oFilePath,
							'show_params' => FALSE,
							'originalName' => $this->_object->file_name,
							'delete_onclick' =>
								"$.adminLoad({path: '{$sFormPath}', additionalParams:
								'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteFile', windowId: '{$windowId}'}); return false",
							'caption' => Core::_('Printlayout.file')
						)
					)
					->smallImage(
						array('show' => FALSE)
					);

				$oMainRowFile->add($oImageField);

				$oMainTab
					->move($this->getField('mail_template')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow6);
			break;

			case 'printlayout_dir':
			default:
				parent::setObject($object);

				// Значения директории для добавляемого объекта
				if (!$this->_object->id)
				{
					$this->_object->parent_id = Core_Array::getGet('printlayout_dir_id');
				}

				$title = $this->_object->id
					? Core::_('Printlayout_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Printlayout_Dir.add_title');

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oSelect_Dirs = Admin_Form_Entity::factory('Select')
					->options(
						array(' … ') + $this->fillPrintlayoutDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Printlayout_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainRow1->add($oSelect_Dirs);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1);
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iPrintlayoutDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillPrintlayoutDir($iPrintlayoutDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iPrintlayoutDirParentId = intval($iPrintlayoutDirParentId);
		$iLevel = intval($iLevel);

		$oPrintlayout_Dir = Core_Entity::factory('Printlayout_Dir', $iPrintlayoutDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oPrintlayout_Dir->Printlayout_Dirs->findAll();

		if (count($childrenDirs))
		{
			foreach ($childrenDirs as $childrenDir)
			{
				if ($bExclude != $childrenDir->id)
				{
					$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
					$aReturn += $this->fillPrintlayoutDir($childrenDir->id, $bExclude, $iLevel+1);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Printlayout_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'printlayout'
				&& $this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		$param = array();

		// Обработка файла
		if (!is_null($aFileData = Core_Array::getFiles('file', NULL)) && intval($aFileData['size']) > 0)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'], array('DOCX', 'XLSX')))
			{
				// Удаление файла
				if ($this->_object->file_name != '' && Core_File::isFile($this->_object->getFilePath()))
				{
					$this->_object->deleteFile();
				}

				// Определяем расширение файла
				$ext = Core_File::getExtension($aFileData['name']);

				$this->_object->file_name = $this->_object->id . '.' . $ext;
				$this->_object->save();

				Core_File::moveUploadedFile($aFileData['tmp_name'], $this->_object->getFilePath());
			}
			else
			{
				$this->addMessage(Core_Message::get(Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])), 'error'));
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$modelName = $this->_object->getModelName();

			switch ($modelName)
			{
				case 'printlayout':
					$name = Core_Array::getRequest('name');
					$id = Core_Array::getRequest('id');
					$oSamePrintlayout = Core_Entity::factory('Printlayout')->getByName($name);

					if (!is_null($oSamePrintlayout) && $oSamePrintlayout->id != $id)
					{
						$this->addMessage(
							Core_Message::get(Core::_('Printlayout.printlayout_already_exists'))
						);
						return TRUE;
					}
			}
		}

		return parent::execute($operation);
	}
}