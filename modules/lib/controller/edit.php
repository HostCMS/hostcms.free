<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		parent::setObject($object);

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		switch ($modelName)
		{
			case 'lib':
				$title = $this->_object->id
					? Core::_('Lib.lib_form_title_edit')
					: Core::_('Lib.lib_form_title_add');

				if (!$this->_object->id)
				{
					$this->_object->lib_dir_id = Core_Array::getGet('lib_dir_id');
				}

				// Настройки типовой дин. страницы
				$oAdmin_Form_Tab_Entity_Lib_Config = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Lib.lib_php_code_config'))
					->name('tab_lib_php_code_config');

				$this->addTabBefore($oAdmin_Form_Tab_Entity_Lib_Config, $oAdditionalTab);

				$oAdmin_Form_Entity_Textarea_Lib_Config = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oAdmin_Form_Entity_Textarea_Lib_Config->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = 'application/x-httpd-php';

				$oAdmin_Form_Entity_Textarea_Lib_Config
					->value(
						$this->_object->loadLibConfigFile()
					)
					->cols(140)
					->rows(30)
					->caption(Core::_('Lib.lib_form_module_config'))
					->name('lib_php_code_config')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions);

				$oAdmin_Form_Tab_Entity_Lib_Config->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainRow3->add($oAdmin_Form_Entity_Textarea_Lib_Config);

				// Код типовой дин. страницы
				$oAdmin_Form_Tab_Entity_Lib = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Lib.lib_php_code'))
					->name('tab_lib_php_code');

				$this->addTabBefore($oAdmin_Form_Tab_Entity_Lib, $oAdditionalTab);

				$oAdmin_Form_Entity_Textarea_Lib = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oAdmin_Form_Entity_Textarea_Lib->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = 'application/x-httpd-php';

				$oAdmin_Form_Entity_Textarea_Lib
					->value(
						$this->_object->loadLibFile()
					)
					->cols(140)
					->rows(30)
					->caption(Core::_('Lib.lib_form_module'))
					->name('lib_php_code')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions);

				$oAdmin_Form_Tab_Entity_Lib->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainRow4->add($oAdmin_Form_Entity_Textarea_Lib);

				// Селектор с группой
				$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select');

				$oAdmin_Form_Entity_Select
					->options(
						array(' … ') + $this->fillLibDir(0)
					)
					->name('lib_dir_id')
					->value($this->_object->lib_dir_id)
					->caption(Core::_('Lib.lib_dir_id'));

				$oAdditionalTab->delete(
					 $this->getField('lib_dir_id') // Удаляем стандартный <input> lib_dir_id
				);

				$oMainRow1->add($oAdmin_Form_Entity_Select);
				$oMainTab->move($this->getField('description'), $oMainRow2);

			break;
			case 'lib_dir':
			default:
				$title = $this->_object->id
					? Core::_('Lib_Dir.lib_form_title_edit_dir')
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
		if (Core::moduleIsActive('revision')  && $this->_object->id)
		{
			$modelName == 'lib'
				&& $this->_object->backupRevision();
		}
		
		parent::_applyObjectProperty();

		switch ($modelName)
		{
			case 'lib':
				$this->_object->saveLibFile(Core_Array::getRequest('lib_php_code'));
				$this->_object->saveLibConfigFile(Core_Array::getRequest('lib_php_code_config'));
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