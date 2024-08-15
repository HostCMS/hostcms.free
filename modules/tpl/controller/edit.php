<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * TPL Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Tpl_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
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
		$oSelect_Dirs = Admin_Form_Entity::factory('Select');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		switch ($modelName)
		{
			case 'tpl':
				$title = $this->_object->id
					? Core::_('Tpl.edit_title', $this->_object->name, FALSE)
					: Core::_('Tpl.add_title');

				if (!$this->_object->id)
				{
					$this->_object->tpl_dir_id = Core_Array::getGet('tpl_dir_id');
				}

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('tpl_dir_id'));

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillTplDir()
					)
					->name('tpl_dir_id')
					->value($this->_object->tpl_dir_id)
					->caption(Core::_('Tpl.tpl_dir_id'));

				$oMainRow1->add($oSelect_Dirs);

				$oTextarea_Tpl = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oTextarea_Tpl->syntaxHighlighterOptions;
				// $oTmpOptions['mode'] = 'smarty';
				$oTmpOptions['mode'] = '"ace/mode/smarty"';

				$tplContent = $this->_object->id
					? $this->_object->loadTplFile()
					: '';

				$oTextarea_Tpl
					->value($tplContent)
					->rows(30)
					->caption(Core::_('Tpl.value'))
					->name('tpl_value')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow2->add($oTextarea_Tpl);

				$oMainTab
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6'));

				$oMainTab->move($this->getField('sorting'), $oMainRow3);

				// Комментарий
				$oDescriptionTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Tpl.tab2'))
					->name('tab_tpl_description');

				$this->addTabAfter($oDescriptionTab, $oMainTab);

				$oDescriptionTab
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('description')->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainTab->move($this->getField('description'), $oMainRow6);

				// Config для всех языков
				$aLngs = Tpl_Controller::getLngs();

				foreach ($aLngs as $sLng)
				{
					$oTab_Lng = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Tpl.tab_config', $sLng))
						->name('lng_' . $sLng);

					$this->addTabAfter($oTab_Lng, $oMainTab);

					$oTab_Lng->add($oLng_Tab_Row1 = Admin_Form_Entity::factory('Div')->class('row'));

					$oTextarea_Lng = Admin_Form_Entity::factory('Textarea');

					// $oTmpOptions = $oTextarea_Lng->syntaxHighlighterOptions;
					// $oTmpOptions['mode'] = 'xml';

					$oTextarea_Lng
						->value(
							$this->_object->loadLngConfigFile($sLng)
						)
						->rows(30)
						->caption(Core::_('Tpl.config', $sLng))
						->name('lng_' . $sLng)
						// ->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
						// ->syntaxHighlighterOptions($oTmpOptions)
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oLng_Tab_Row1->add($oTextarea_Lng);
				}

			break;

			case 'tpl_dir':
			default:
				$title = $this->_object->id
					? Core::_('Tpl_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Tpl_Dir.add_title');

				// Значения директории для добавляемого объекта
				if (!$this->_object->id)
				{
					$this->_object->parent_id = Core_Array::getGet('tpl_dir_id');
				}

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillTplDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Tpl_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow1->add($oSelect_Dirs);

				$this->getField('sorting')->divAttr(array('class' => 'form-group col-sm-6 col-md-5 col-lg-4'));
				$oMainTab->move($this->getField('sorting'), $oMainRow2);

			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iTplDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillTplDir($iTplDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iTplDirParentId = intval($iTplDirParentId);
		$iLevel = intval($iLevel);

		$oTpl_Dir = Core_Entity::factory('Tpl_Dir', $iTplDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oTpl_Dir->Tpl_Dirs->findAll();

		if (count($childrenDirs))
		{
			foreach ($childrenDirs as $childrenDir)
			{
				if ($bExclude != $childrenDir->id)
				{
					$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
					$aReturn += $this->fillTplDir($childrenDir->id, $bExclude, $iLevel+1);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Tpl_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'tpl'
				&& $this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		switch ($modelName)
		{
			case 'tpl':
				$tpl_value = Core_Array::getPost('tpl_value');

				$this->_object->saveTplFile($tpl_value);

				// Config для всех языков
				$aLngs = Tpl_Controller::getLngs();

				foreach ($aLngs as $sLng)
				{
					$content = Core_Array::getPost('lng_' . $sLng);

					$this->_object->saveLngConfigFile($sLng, $content);
				}

				// clear entire compile directory
				$oTpl_Processor = Tpl_Processor::instance();
				$oTpl_Processor->clearCompiledTemplate();
			break;
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
				case 'tpl':
					$name = Core_Array::getRequest('name');
					$id = Core_Array::getRequest('id');
					$oSameTpl = Core_Entity::factory('Tpl')->getByName($name);

					if (!is_null($oSameTpl) && $oSameTpl->id != $id)
					{
						$this->addMessage(
							Core_Message::get(Core::_('Tpl.tpl_already_exists'))
						);
						return TRUE;
					}
			}
		}

		return parent::execute($operation);
	}
}