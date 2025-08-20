<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Lib_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			case 'lib':
				$this
					->addSkipColumn('file');
			break;
		}

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

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		switch ($modelName)
		{
			case 'lib':
				$title = $this->_object->id
					? Core::_('Lib.lib_form_title_edit', $this->_object->name, FALSE)
					: Core::_('Lib.lib_form_title_add');

				if (!$this->_object->id)
				{
					$this->_object->lib_dir_id = Core_Array::getGet('lib_dir_id');
				}

				$oMainTab->delete($this->getField('type'));

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oRadio_Type = Admin_Form_Entity::factory('Radiogroup')
					->name('type')
					->id('libType' . time())
					->caption(Core::_('Lib.type'))
					->value($this->_object->type)
					->divAttr(array('id' => 'lib_types', 'class' => 'form-group col-xs-12 rounded-radio-group'))
					->radio(
						array(
							0 => Core::_('Lib.type0'),
							1 => Core::_('Lib.type1')
						)
					)
					->buttonset(TRUE)
					->ico(
						array(
							0 => 'fa-regular fa-file-lines fa-fw',
							1 => 'fa-regular fa-file-lines fa-fw'
						)
					)
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1]); window.dispatchEvent(new Event('resize'));");

				$oMainRow1->add($oRadio_Type);

				$oAdditionalTab->delete(
					$this->getField('lib_dir_id') // Удаляем стандартный <input> lib_dir_id
			   );

				// Селектор с группой
				$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select');

				$oAdmin_Form_Entity_Select
					->options(
						array(' … ') + $this->fillLibDir(0)
					)
					->name('lib_dir_id')
					->value($this->_object->lib_dir_id)
					->caption(Core::_('Lib.lib_dir_id'))
					->divAttr(array('class' => 'col-xs-12 col-md-6'));

				$oMainRow2->add($oAdmin_Form_Entity_Select);

				// $oMainTab->delete($this->getField('file'));

				$sFilePath = Core_File::isFile($this->_object->getFilePath())
					? $this->_object->getFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$oAdmin_Form_Entity_File = Admin_Form_Entity::factory('File')
					->type('file')
					->name('file')
					->caption(Core::_('Lib.file'))
					->largeImage(
						array(
							'path' => $sFilePath,
							'show_params' => FALSE,
							'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1&lib_dir_id={$this->_object->lib_dir_id}', action: 'deleteFile', windowId: '{$windowId}'}); return false",
							'delete_href' => ''
						)
					)->smallImage(
						array('show' => FALSE)
					)
					->divAttr(array('class' => 'col-xs-12 col-md-6 hidden-0'));

				$oMainRow2->add($oAdmin_Form_Entity_File);

				$oMainTab
					->move($this->getField('class')->divAttr(array('class' => 'col-xs-12 col-md-6 hidden-0')), $oMainRow2)
					->move($this->getField('style')->divAttr(array('class' => 'col-xs-12 col-md-6 hidden-0')), $oMainRow2)
					->move($this->getField('description'), $oMainRow2)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

				// Настройки типовой дин. страницы
				$oAdmin_Form_Tab_Entity_Lib_Config = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Lib.lib_php_code_config'))
					->name('tab_lib_php_code_config');

				$this->addTabAfter($oAdmin_Form_Tab_Entity_Lib_Config, $oMainTab);

				$oAdmin_Form_Entity_Textarea_Lib_Config = Admin_Form_Entity::factory('Textarea');

				$oAdmin_Form_Entity_Textarea_Lib_Config
					->value(
						$this->_object->loadLibConfigFile()
					)
					->cols(140)
					->rows(30)
					->caption(Core::_('Lib.lib_form_module_config'))
					->name('lib_php_code_config')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterMode('php');

				$oAdmin_Form_Tab_Entity_Lib_Config->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainRow3->add($oAdmin_Form_Entity_Textarea_Lib_Config);

				// Код типовой дин. страницы
				$oAdmin_Form_Tab_Entity_Lib = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Lib.lib_php_code'))
					->name('tab_lib_php_code');

				$this->addTabAfter($oAdmin_Form_Tab_Entity_Lib, $oAdmin_Form_Tab_Entity_Lib_Config);

				$oAdmin_Form_Entity_Textarea_Lib = Admin_Form_Entity::factory('Textarea');

				$oAdmin_Form_Entity_Textarea_Lib
					->value(
						$this->_object->loadLibFile()
					)
					->cols(140)
					->rows(30)
					->caption(Core::_('Lib.lib_form_module'))
					->name('lib_php_code')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterMode('php');

				$oAdmin_Form_Tab_Entity_Lib->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainRow4->add($oAdmin_Form_Entity_Textarea_Lib);

				$oMainTab->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>radiogroupOnChange('{$windowId}', '{$this->_object->type}', [0,1])</script>")
				);
			break;
			case 'lib_dir':
			default:
				$title = $this->_object->id
					? Core::_('Lib_Dir.lib_form_title_edit_dir', $this->_object->name, FALSE)
					: Core::_('Lib_Dir.lib_form_title_add_dir');

				// Значения директории для добавляемого объекта
				if (!$this->_object->id)
				{
					$this->_object->parent_id = Core_Array::getGet('lib_dir_id');
				}

				$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select')
					->options(
						array(' … ') + $this->fillLibDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Lib_Dir.parent_id'));

				$oAdditionalTab->delete($this->getField('parent_id'));

				$oMainRow1->add($oAdmin_Form_Entity_Select);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Lib_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'lib'
				&& $this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		switch ($modelName)
		{
			case 'lib':
				if (
					// Поле файла существует
					!is_null($aFileData = Core_Array::getFiles('file', NULL))
					// и передан файл
					&& intval($aFileData['size']) > 0)
				{
					if (Core_File::isValidExtension($aFileData['name'], array('jpg', 'jpeg', 'gif', 'png', 'webp', 'swf')))
					{
						$this->_object->saveFile($aFileData['tmp_name'], $aFileData['name']);
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

				$this->_object->saveLibConfigFile(Core_Array::getRequest('lib_php_code_config'));
				$this->_object->saveLibFile(Core_Array::getRequest('lib_php_code'));
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iLibDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillLibDir($iLibDirParentId, $bExclude = FALSE, $iLevel = 0)
	{
		$iLibDirParentId = intval($iLibDirParentId);
		$iLevel = intval($iLevel);

		$oLibDir = Core_Entity::factory('Lib_Dir', $iLibDirParentId);

		$aResult = array();

		// Дочерние разделы
		$aChildrenDirs = $oLibDir->Lib_Dirs->findAll();

		if (count($aChildrenDirs))
		{
			foreach ($aChildrenDirs as $oChildrenDir)
			{
				if ($bExclude != $oChildrenDir->id)
				{
					$aResult[$oChildrenDir->id] = str_repeat('  ', $iLevel) . $oChildrenDir->name;
					$aResult += $this->fillLibDir($oChildrenDir->id, $bExclude, $iLevel+1);
				}
			}
		}

		return $aResult;
	}
}